'use client'

import { useRouter } from 'next/navigation'
import { useState } from 'react'
import { useLocale } from '@/context/LocaleContext'

export default function ProductSearch({ defaultValue }: { defaultValue?: string }) {
  const [search, setSearch] = useState(defaultValue ?? '')
  const { t } = useLocale()
  const router = useRouter()

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const params = new URLSearchParams()
    if (search.trim()) params.set('search', search.trim())
    router.push(`/products?${params.toString()}`)
  }

  return (
    <form onSubmit={handleSubmit} className="search-bar">
      <input
        type="text"
        placeholder={t('products.search')}
        value={search}
        onChange={(e) => setSearch(e.target.value)}
      />
      <button type="submit" className="btn btn-primary">{t('products.searchBtn')}</button>
      {defaultValue && (
        <button type="button" className="btn btn-outline"
          onClick={() => { setSearch(''); router.push('/products') }}>
          {t('products.clear')}
        </button>
      )}
    </form>
  )
}
