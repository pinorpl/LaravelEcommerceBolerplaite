'use client'

import { useAuth } from '@/context/AuthContext'
import { useLocale } from '@/context/LocaleContext'
import { useEffect } from 'react'
import { useRouter } from 'next/navigation'

export default function ProfilePage() {
  const { user, isAuthenticated, isLoading, logout } = useAuth()
  const { t } = useLocale()
  const router = useRouter()

  useEffect(() => {
    if (!isLoading && !isAuthenticated) router.push('/login?from=/profile')
  }, [isLoading, isAuthenticated])

  if (isLoading || !user) return <div className="container" style={{ paddingTop: '2rem' }}>{t('profile.loading')}</div>

  return (
    <div className="container" style={{ paddingTop: '2rem', maxWidth: 600 }}>
      <h1 style={{ marginBottom: '1.5rem' }}>{t('profile.title')}</h1>
      <div className="card">
        <div className="card-body">
          <p><strong>{t('profile.name')}:</strong> {user.name}</p>
          <p style={{ margin: '0.75rem 0' }}><strong>{t('profile.email')}:</strong> {user.email}</p>
          <p>
            <strong>{t('profile.roles')}:</strong>{' '}
            {user.roles.map(r => (
              <span key={r} className={`badge badge-${r}`} style={{ marginLeft: '0.25rem' }}>{r}</span>
            ))}
          </p>
          <p style={{ marginTop: '0.75rem' }}>
            <strong>{t('profile.memberSince')}:</strong> {new Date(user.created_at).toLocaleDateString()}
          </p>
        </div>
      </div>
      <button onClick={async () => { await logout(); router.push('/') }}
        className="btn btn-danger" style={{ marginTop: '1.5rem' }}>
        {t('profile.signOut')}
      </button>
    </div>
  )
}
