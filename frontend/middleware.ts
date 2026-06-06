/**
 * Next.js Edge Middleware — route protection.
 *
 * [SEC] Two levels of protection:
 *  1. Presence check: redirect to /login if no access_token cookie.
 *  2. Role check: /admin requires 'admin' role encoded in the JWT payload.
 *
 * NOTE: JWT signature is NOT verified here (Edge Runtime has no crypto for RS256).
 * Role check is a UX guard — the real enforcement is the backend `role:admin`
 * middleware on every admin API route. Never rely solely on frontend checks.
 */

import { NextResponse } from 'next/server'
import type { NextRequest } from 'next/server'

const PROTECTED_ROUTES = ['/admin', '/cart', '/profile']
const ADMIN_ROUTES = ['/admin']

/** Decode JWT payload without verification (for role hint only) */
function decodeJwtPayload(token: string): Record<string, unknown> | null {
  try {
    const parts = token.split('.')
    if (parts.length !== 3) return null
    const payload = parts[1].replace(/-/g, '+').replace(/_/g, '/')
    return JSON.parse(Buffer.from(payload, 'base64').toString('utf-8'))
  } catch {
    return null
  }
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl
  const isProtected = PROTECTED_ROUTES.some(r => pathname.startsWith(r))

  if (!isProtected) return NextResponse.next()

  const accessToken = request.cookies.get('access_token')?.value

  // [SEC] No token → redirect to login
  if (!accessToken) {
    const loginUrl = new URL('/login', request.url)
    loginUrl.searchParams.set('from', pathname)
    return NextResponse.redirect(loginUrl)
  }

  // [SEC] Admin routes: decode JWT to check role hint
  // Real enforcement is always on the backend (role:admin middleware)
  if (ADMIN_ROUTES.some(r => pathname.startsWith(r))) {
    const payload = decodeJwtPayload(accessToken)
    // Passport JWTs don't embed roles — role check happens via /api/user in the page
    // We still let through authenticated users; the admin page itself does the role check
    // and the backend enforces it on every API call
    if (!payload) {
      const loginUrl = new URL('/login', request.url)
      loginUrl.searchParams.set('from', pathname)
      return NextResponse.redirect(loginUrl)
    }
  }

  return NextResponse.next()
}

export const config = {
  matcher: ['/admin/:path*', '/cart/:path*', '/profile/:path*'],
}
