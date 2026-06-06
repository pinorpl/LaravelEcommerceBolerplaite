'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import { useAuth } from '@/context/AuthContext'
import { useLocale } from '@/context/LocaleContext'
import type { Product } from '@/types'

export default function AddToCartButton({ product }: { product: Product }) {
  const { isAuthenticated, accessToken } = useAuth()
  const { t } = useLocale()
  const [quantity, setQuantity] = useState(1)
  const [loading, setLoading] = useState(false)
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null)
  const router = useRouter()

  const handleAddToCart = async () => {
    if (!isAuthenticated) {
      router.push(`/login?from=/products/${product.slug}`)
      return
    }
    setLoading(true)
    setMessage(null)
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/cart/items`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${accessToken}` },
        body: JSON.stringify({ product_id: product.id, quantity }),
      })
      if (!res.ok) throw new Error()
      setMessage({ type: 'success', text: t('product.added') })
    } catch {
      setMessage({ type: 'error', text: t('product.addError') })
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      {message && <div className={`alert alert-${message.type}`} style={{ marginBottom: '1rem' }}>{message.text}</div>}
      <div style={{ display: 'flex', gap: '1rem', alignItems: 'center', marginBottom: '1rem' }}>
        <label htmlFor="qty" style={{ fontWeight: 500 }}>{t('product.qty')}</label>
        <input id="qty" type="number" min={1} max={product.stock} value={quantity}
          onChange={(e) => setQuantity(Math.max(1, parseInt(e.target.value) || 1))}
          style={{ width: 80, padding: '0.4rem 0.6rem', border: '1px solid #e2e8f0', borderRadius: 6 }} />
      </div>
      <button onClick={handleAddToCart} disabled={loading || product.stock === 0}
        className="btn btn-primary" style={{ width: '100%', padding: '0.75rem', fontSize: '1rem' }}>
        {loading ? t('product.adding') : product.stock === 0 ? t('product.outOfStock') : t('product.addToCart')}
      </button>
    </div>
  )
}
