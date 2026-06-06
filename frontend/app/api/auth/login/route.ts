/**
 * POST /api/auth/login — Server-side Passport Password Grant proxy.
 *
 * [SEC] The client_secret never reaches the browser — only this server-side
 * handler reads PASSPORT_CLIENT_SECRET from the container environment.
 * [SEC] Cookies set with httpOnly=true, SameSite=strict, Secure in production.
 */

import { NextRequest, NextResponse } from 'next/server'

const INTERNAL_API = process.env.INTERNAL_API_URL ?? 'http://nginx/api'
const PASSPORT_TOKEN_URL = INTERNAL_API.replace('/api', '/oauth/token')
const IS_PROD = process.env.NODE_ENV === 'production'

export async function POST(request: NextRequest) {
  let email: string, password: string

  try {
    const body = await request.json()
    email = body.email
    password = body.password
  } catch {
    return NextResponse.json({ error: 'INVALID_REQUEST', message: 'Invalid request body.' }, { status: 400 })
  }

  // [SEC] Basic input presence check before hitting Passport
  if (!email || !password) {
    return NextResponse.json({ error: 'VALIDATION_ERROR', message: 'Email and password are required.' }, { status: 422 })
  }

  // Exchange credentials for token — server-to-server only
  const tokenRes = await fetch(PASSPORT_TOKEN_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' },
    body: new URLSearchParams({
      grant_type: 'password',
      client_id: process.env.PASSPORT_CLIENT_ID!,
      client_secret: process.env.PASSPORT_CLIENT_SECRET!,  // NEVER sent to browser
      username: email,
      password,
      scope: '',
    }).toString(),
  }).catch(() => null)

  if (!tokenRes?.ok) {
    // [SEC] Return normalized error code, not raw Passport message
    return NextResponse.json({ error: 'invalid_grant', message: 'Invalid credentials.' }, { status: 401 })
  }

  const { access_token, refresh_token, expires_in } = await tokenRes.json()

  const userRes = await fetch(`${INTERNAL_API}/user`, {
    headers: { Authorization: `Bearer ${access_token}`, Accept: 'application/json' },
  })
  const user = userRes.ok ? await userRes.json() : null

  // [SEC] access_token returned in body so React state can attach it to API calls.
  // It is ALSO stored in httpOnly cookie for SSR session restoration.
  const response = NextResponse.json({ user, access_token })

  // [SEC] SameSite=strict prevents CSRF token theft via cross-site requests
  response.cookies.set('access_token', access_token, {
    httpOnly: true,
    secure: IS_PROD,
    sameSite: 'strict',
    maxAge: expires_in,
    path: '/',
  })

  // [SEC] Refresh token strictly httpOnly — never readable by JS
  response.cookies.set('refresh_token', refresh_token, {
    httpOnly: true,
    secure: IS_PROD,
    sameSite: 'strict',
    maxAge: 60 * 60 * 24 * 30,
    path: '/api/auth',   // [SEC] Scope to auth routes only, not all routes
  })

  return response
}
