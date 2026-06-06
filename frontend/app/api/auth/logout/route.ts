/**
 * POST /api/auth/logout
 * Revokes the Passport token on Laravel side and clears cookies.
 */

import { NextRequest, NextResponse } from 'next/server'

const INTERNAL_API = process.env.INTERNAL_API_URL ?? 'http://nginx/api'

export async function POST(request: NextRequest) {
  const accessToken = request.cookies.get('access_token')?.value

  if (accessToken) {
    // Revoke token on Laravel (best-effort – don't fail if Laravel is down)
    await fetch(`${INTERNAL_API}/logout`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${accessToken}`,
        Accept: 'application/json',
      },
    }).catch(() => {})
  }

  const response = NextResponse.json({ message: 'Logged out' })
  response.cookies.delete('access_token')
  response.cookies.delete('refresh_token')
  return response
}
