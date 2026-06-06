/**
 * GET /api/auth/me
 *
 * Reads the httpOnly access_token cookie and fetches the authenticated user
 * from Laravel. Used by AuthContext on mount to restore session state.
 */

import { NextRequest, NextResponse } from 'next/server'

const INTERNAL_API = process.env.INTERNAL_API_URL ?? 'http://nginx/api'

export async function GET(request: NextRequest) {
  const accessToken = request.cookies.get('access_token')?.value

  if (!accessToken) {
    return NextResponse.json({ user: null }, { status: 401 })
  }

  const userRes = await fetch(`${INTERNAL_API}/user`, {
    headers: {
      Authorization: `Bearer ${accessToken}`,
      Accept: 'application/json',
    },
  })

  if (!userRes.ok) {
    return NextResponse.json({ user: null }, { status: 401 })
  }

  const user = await userRes.json()
  return NextResponse.json({ user, access_token: accessToken })
}
