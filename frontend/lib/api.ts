/**
 * Centralised API client.
 *
 * - Server-side: uses INTERNAL_API_URL (Docker internal network)
 * - Client-side: uses NEXT_PUBLIC_API_URL (through browser)
 * - Sends X-App-Locale header so Laravel returns validation messages
 *   in the user's selected language.
 */

import type { ApiError } from './errors'

const getBaseUrl = () =>
  typeof window === 'undefined'
    ? process.env.INTERNAL_API_URL ?? 'http://nginx/api'
    : process.env.NEXT_PUBLIC_API_URL ?? 'http://localhost/api'

/** Read the locale cookie (client-side only) */
function getLocaleFromCookie(): string {
  if (typeof document === 'undefined') return 'en'
  const match = document.cookie.match(/(?:^|; )locale=([^;]*)/)
  return match ? decodeURIComponent(match[1]) : 'en'
}

export async function apiFetch<T>(
  path: string,
  options: RequestInit & { token?: string } = {}
): Promise<T> {
  const { token, ...fetchOptions } = options
  const url = `${getBaseUrl()}${path}`

  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    // Tell Laravel which locale to use for validation / error messages
    'X-App-Locale': getLocaleFromCookie(),
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...(fetchOptions.headers ?? {}),
  }

  const response = await fetch(url, { ...fetchOptions, headers })

  if (!response.ok) {
    const error: ApiError = await response.json().catch(() => ({ message: response.statusText }))
    throw { status: response.status, ...error }
  }

  if (response.status === 204) return {} as T

  return response.json()
}
