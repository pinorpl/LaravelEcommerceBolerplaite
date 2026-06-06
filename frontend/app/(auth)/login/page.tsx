'use client'

import { useState } from 'react'
import { useRouter, useSearchParams } from 'next/navigation'
import Link from 'next/link'
import { useAuth } from '@/context/AuthContext'
import { useLocale } from '@/context/LocaleContext'
import { resolveErrorMessage } from '@/lib/errors'

export default function LoginPage() {
  const { login } = useAuth()
  const { t, locale } = useLocale()
  const router = useRouter()
  const searchParams = useSearchParams()
  const from = searchParams.get('from') ?? '/'

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError(null)
    setLoading(true)
    try {
      await login(email, password)
      router.push(from)
    } catch (err) {
      // Normalize: never show raw backend messages
      setError(resolveErrorMessage(err, locale))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-page">
      <div className="auth-card">
        <h2>{t('login.title')}</h2>
        {error && <div className="alert alert-error">{error}</div>}
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="email">{t('login.email')}</label>
            <input id="email" type="email" value={email}
              onChange={(e) => setEmail(e.target.value)} required autoComplete="email" />
          </div>
          <div className="form-group">
            <label htmlFor="password">{t('login.password')}</label>
            <input id="password" type="password" value={password}
              onChange={(e) => setPassword(e.target.value)} required autoComplete="current-password" />
          </div>
          <button type="submit" disabled={loading} className="btn btn-primary"
            style={{ width: '100%', marginTop: '0.5rem' }}>
            {loading ? t('login.loading') : t('login.submit')}
          </button>
        </form>
        <p style={{ textAlign: 'center', marginTop: '1.5rem', color: '#666' }}>
          {t('login.noAccount')}{' '}
          <Link href="/register" style={{ color: '#1a1a2e', fontWeight: 600 }}>
            {t('login.registerLink')}
          </Link>
        </p>
        <p style={{ textAlign: 'center', marginTop: '0.5rem', fontSize: '0.8rem', color: '#999' }}>
          {t('login.demo')}
        </p>
      </div>
    </div>
  )
}
