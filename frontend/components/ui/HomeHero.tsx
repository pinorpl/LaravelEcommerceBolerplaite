'use client'

/**
 * HomeHero — Client Component que contiene el texto traducible del home.
 *
 * Por qué existe: app/page.tsx es un Server Component (fetch de productos SSR).
 * Los Server Components no pueden usar useLocale() ni reaccionar al cambio de
 * idioma en el cliente. Extrayendo el texto a este Client Component, el título
 * y el CTA se actualizan inmediatamente cuando el usuario cambia el idioma,
 * sin necesidad de recargar la página.
 */

import Link from 'next/link'
import { useLocale } from '@/context/LocaleContext'

export default function HomeHero() {
  const { t } = useLocale()

  return (
    <div style={{ textAlign: 'center', padding: '3rem 0 2rem' }}>
      <h1 style={{ fontSize: '2.5rem', marginBottom: '2rem' }}>
        {t('home.title')}
      </h1>
      <Link href="/products" className="btn btn-primary" style={{ fontSize: '1rem', padding: '0.75rem 2rem' }}>
        {t('home.cta')}
      </Link>
    </div>
  )
}
