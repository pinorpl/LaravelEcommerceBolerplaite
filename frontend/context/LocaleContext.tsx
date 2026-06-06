'use client'

/**
 * LocaleContext — lightweight i18n without external dependencies.
 *
 * - Supports 'en' and 'es' locales.
 * - Persists selection in a cookie ('locale') so it survives page reloads.
 * - Provides t(key, params?) for interpolated translations.
 *   e.g. t('home.inStock', { count: 5 }) → "5 in stock" / "5 en stock"
 * - Reading order on mount: cookie → browser language → 'en'
 */

import React, {
  createContext, useContext, useState, useEffect, useCallback, useMemo
} from 'react'
import en from '@/messages/en.json'
import es from '@/messages/es.json'

export type Locale = 'en' | 'es'

const MESSAGES: Record<Locale, Record<string, unknown>> = { en, es }
const COOKIE_NAME = 'locale'
const SUPPORTED: Locale[] = ['en', 'es']

// ── helpers ──────────────────────────────────────────────────────────────────

/** Deep key lookup: 'nav.products' → messages.nav.products */
function getNestedValue(obj: Record<string, unknown>, key: string): string {
  return key.split('.').reduce<unknown>((acc, k) => {
    if (acc && typeof acc === 'object') return (acc as Record<string, unknown>)[k]
    return undefined
  }, obj) as string ?? key
}

/** Replace {param} placeholders */
function interpolate(str: string, params?: Record<string, string | number>): string {
  if (!params) return str
  return Object.entries(params).reduce(
    (acc, [k, v]) => acc.replace(new RegExp(`\\{${k}\\}`, 'g'), String(v)),
    str
  )
}

function getCookieLocale(): Locale | null {
  if (typeof document === 'undefined') return null
  const match = document.cookie.match(new RegExp(`(?:^|; )${COOKIE_NAME}=([^;]*)`))
  const val = match ? decodeURIComponent(match[1]) : null
  return SUPPORTED.includes(val as Locale) ? (val as Locale) : null
}

function getBrowserLocale(): Locale {
  if (typeof navigator === 'undefined') return 'en'
  const lang = navigator.language.slice(0, 2).toLowerCase()
  return SUPPORTED.includes(lang as Locale) ? (lang as Locale) : 'en'
}

// ── context ───────────────────────────────────────────────────────────────────

interface LocaleContextValue {
  locale: Locale
  setLocale: (locale: Locale) => void
  t: (key: string, params?: Record<string, string | number>) => string
}

const LocaleContext = createContext<LocaleContextValue | null>(null)

export function LocaleProvider({ children }: { children: React.ReactNode }) {
  const [locale, setLocaleState] = useState<Locale>('en')

  // On mount: read from cookie or browser language
  useEffect(() => {
    const saved = getCookieLocale() ?? getBrowserLocale()
    setLocaleState(saved)
  }, [])

  const setLocale = useCallback((newLocale: Locale) => {
    setLocaleState(newLocale)
    // Persist for 1 year
    document.cookie = `${COOKIE_NAME}=${newLocale}; path=/; max-age=${60 * 60 * 24 * 365}; SameSite=Lax`
  }, [])

  const t = useCallback(
    (key: string, params?: Record<string, string | number>): string => {
      const messages = MESSAGES[locale] as Record<string, unknown>
      const raw = getNestedValue(messages, key)
      return interpolate(raw, params)
    },
    [locale]
  )

  const value = useMemo(() => ({ locale, setLocale, t }), [locale, setLocale, t])

  return <LocaleContext.Provider value={value}>{children}</LocaleContext.Provider>
}

export function useLocale() {
  const ctx = useContext(LocaleContext)
  if (!ctx) throw new Error('useLocale must be used within LocaleProvider')
  return ctx
}
