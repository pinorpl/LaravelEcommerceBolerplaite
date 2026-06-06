/**
 * Server-side translation helper for Server Components (SSR/ISR pages).
 * Reads the 'locale' cookie from the incoming request headers.
 * Client Components use useLocale() from LocaleContext instead.
 */

import { cookies } from 'next/headers'
import en from '@/messages/en.json'
import es from '@/messages/es.json'

type Locale = 'en' | 'es'
const MESSAGES = { en, es } as Record<Locale, Record<string, unknown>>

function getNestedValue(obj: Record<string, unknown>, key: string): string {
  return key.split('.').reduce<unknown>((acc, k) => {
    if (acc && typeof acc === 'object') return (acc as Record<string, unknown>)[k]
    return undefined
  }, obj) as string ?? key
}

function interpolate(str: string, params?: Record<string, string | number>): string {
  if (!params) return str
  return Object.entries(params).reduce(
    (acc, [k, v]) => acc.replace(new RegExp(`\\{${k}\\}`, 'g'), String(v)),
    str
  )
}

export function getServerLocale(): Locale {
  const cookieStore = cookies()
  const val = cookieStore.get('locale')?.value
  return (val === 'es' ? 'es' : 'en') as Locale
}

export function getServerT() {
  const locale = getServerLocale()
  const messages = MESSAGES[locale]
  return function t(key: string, params?: Record<string, string | number>): string {
    return interpolate(getNestedValue(messages, key), params)
  }
}
