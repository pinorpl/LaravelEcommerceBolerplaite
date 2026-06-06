'use client'

import Link from 'next/link'
import { useAuth } from '@/context/AuthContext'
import { useLocale } from '@/context/LocaleContext'
import { useRouter } from 'next/navigation'
import LanguageSwitcher from './LanguageSwitcher'

export default function Navbar() {
  const { user, isAuthenticated, hasRole, logout } = useAuth()
  const { t } = useLocale()
  const router = useRouter()

  const handleLogout = async () => {
    await logout()
    router.push('/')
  }

  return (
    <nav className="navbar">
      <Link href="/" className="brand">🛒 Ecommerce</Link>
      <nav style={{ display: 'flex', alignItems: 'center', gap: '0.25rem' }}>
        <Link href="/products">{t('nav.products')}</Link>
        {isAuthenticated && hasRole('buyer') && (
          <Link href="/cart">{t('nav.cart')}</Link>
        )}
        {isAuthenticated && hasRole('admin') && (
          <Link href="/admin">{t('nav.admin')}</Link>
        )}
        {isAuthenticated ? (
          <>
            <Link href="/profile">{user?.name}</Link>
            <button
              onClick={handleLogout}
              className="btn btn-outline"
              style={{ color: 'white', borderColor: 'white', marginLeft: '0.25rem' }}
            >
              {t('nav.logout')}
            </button>
          </>
        ) : (
          <>
            <Link href="/login">{t('nav.login')}</Link>
            <Link href="/register" className="btn btn-accent" style={{ marginLeft: '0.25rem' }}>
              {t('nav.register')}
            </Link>
          </>
        )}

        {/* Language switcher — always visible */}
        <div style={{ marginLeft: '0.75rem', borderLeft: '1px solid rgba(255,255,255,0.2)', paddingLeft: '0.75rem' }}>
          <LanguageSwitcher />
        </div>
      </nav>
    </nav>
  )
}
