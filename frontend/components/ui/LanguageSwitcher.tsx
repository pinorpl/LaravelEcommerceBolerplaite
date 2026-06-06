'use client'

import { useLocale, type Locale } from '@/context/LocaleContext'

const LABELS: Record<Locale, string> = {
  en: '🇺🇸 EN',
  es: '🇲🇽 ES',
}

export default function LanguageSwitcher() {
  const { locale, setLocale } = useLocale()

  return (
    <div style={{ display: 'flex', gap: '0.25rem' }}>
      {(['en', 'es'] as Locale[]).map((lang) => (
        <button
          key={lang}
          onClick={() => setLocale(lang)}
          title={lang === 'en' ? 'English' : 'Español'}
          style={{
            padding: '0.25rem 0.5rem',
            borderRadius: 4,
            border: 'none',
            cursor: 'pointer',
            fontSize: '0.8rem',
            fontWeight: locale === lang ? 700 : 400,
            background: locale === lang ? 'rgba(255,255,255,0.2)' : 'transparent',
            color: 'white',
            opacity: locale === lang ? 1 : 0.65,
            transition: 'all .15s',
          }}
        >
          {LABELS[lang]}
        </button>
      ))}
    </div>
  )
}
