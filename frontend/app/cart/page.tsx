'use client'

import { useEffect, useState } from 'react'
import { useAuth } from '@/context/AuthContext'
import { useLocale } from '@/context/LocaleContext'
import { useRouter } from 'next/navigation'
import Image from 'next/image'
import type { Cart, Order } from '@/types'

const API = process.env.NEXT_PUBLIC_API_URL

export default function CartPage() {
  const { accessToken, isAuthenticated, isLoading } = useAuth()
  const { t } = useLocale()
  const router = useRouter()

  const [cart, setCart] = useState<Cart | null>(null)
  const [loading, setLoading] = useState(true)
  const [checkoutAddress, setCheckoutAddress] = useState('')
  const [order, setOrder] = useState<Order | null>(null)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!isLoading && !isAuthenticated) { router.push('/login?from=/cart'); return }
    if (!isLoading && accessToken) fetchCart()
  }, [isLoading, isAuthenticated, accessToken])

  const fetchCart = async () => {
    try {
      const res = await fetch(`${API}/cart`, { headers: { Authorization: `Bearer ${accessToken}`, Accept: 'application/json' } })
      if (res.ok) setCart(await res.json())
    } finally { setLoading(false) }
  }

  const removeItem = async (itemId: number) => {
    await fetch(`${API}/cart/items/${itemId}`, { method: 'DELETE', headers: { Authorization: `Bearer ${accessToken}` } })
    await fetchCart()
  }

  const updateQuantity = async (itemId: number, quantity: number) => {
    if (quantity < 1) return
    await fetch(`${API}/cart/items/${itemId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${accessToken}` },
      body: JSON.stringify({ quantity }),
    })
    await fetchCart()
  }

  const checkout = async () => {
    setError(null)
    if (!checkoutAddress.trim()) { setError(t('cart.addressRequired')); return }
    const res = await fetch(`${API}/cart/checkout`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${accessToken}`, Accept: 'application/json' },
      body: JSON.stringify({ shipping_address: checkoutAddress }),
    })
    if (res.ok) { setOrder(await res.json()); setCart(null) }
    else { const err = await res.json(); setError(err.message) }
  }

  if (loading) return <div className="container" style={{ paddingTop: '2rem' }}>{t('cart.loading')}</div>

  if (order) return (
    <div className="container" style={{ paddingTop: '2rem', maxWidth: 600 }}>
      <div className="alert alert-success">
        <strong>{t('cart.orderSuccess', { id: order.id })}</strong>
      </div>
      <p>{t('cart.total')} <strong>${order.total_amount.toFixed(2)}</strong></p>
      <p>{t('cart.status')} <strong>{order.status}</strong></p>
      <button onClick={() => router.push('/products')} className="btn btn-primary" style={{ marginTop: '1rem' }}>
        {t('cart.continue')}
      </button>
    </div>
  )

  return (
    <div className="container" style={{ paddingTop: '2rem' }}>
      <h1 style={{ marginBottom: '1.5rem' }}>{t('cart.title')}</h1>
      {!cart || cart.items.length === 0 ? (
        <div>
          <p>{t('cart.empty')}</p>
          <button onClick={() => router.push('/products')} className="btn btn-primary" style={{ marginTop: '1rem' }}>
            {t('cart.browse')}
          </button>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '2rem', alignItems: 'start' }}>
          <div>
            {cart.items.map(item => (
              <div key={item.id} className="cart-item card" style={{ marginBottom: '1rem' }}>
                {item.product?.image && (
                  <Image src={item.product.image} alt={item.product.name ?? ''} width={80} height={80}
                    style={{ objectFit: 'cover', borderRadius: 4 }} />
                )}
                <div style={{ flex: 1 }}>
                  <p style={{ fontWeight: 600 }}>{item.product?.name}</p>
                  <p style={{ color: '#e94560' }}>${item.price.toFixed(2)}</p>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                  <button onClick={() => updateQuantity(item.id, item.quantity - 1)} className="btn btn-outline" style={{ padding: '0.2rem 0.6rem' }}>−</button>
                  <span>{item.quantity}</span>
                  <button onClick={() => updateQuantity(item.id, item.quantity + 1)} className="btn btn-outline" style={{ padding: '0.2rem 0.6rem' }}>+</button>
                </div>
                <p style={{ fontWeight: 600, minWidth: 70, textAlign: 'right' }}>${item.subtotal.toFixed(2)}</p>
                <button onClick={() => removeItem(item.id)} className="btn btn-danger" style={{ padding: '0.3rem 0.7rem' }}>✕</button>
              </div>
            ))}
          </div>
          <div className="cart-summary card">
            <div className="card-body">
              <h3 style={{ marginBottom: '1rem' }}>{t('cart.summary')}</h3>
              <p style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '1rem' }}>
                <span>{t('cart.total')}</span><strong>${cart.total.toFixed(2)}</strong>
              </p>
              {error && <div className="alert alert-error">{error}</div>}
              <div className="form-group">
                <label>{t('cart.shippingAddress')}</label>
                <textarea value={checkoutAddress} onChange={e => setCheckoutAddress(e.target.value)}
                  rows={3} placeholder={t('cart.shippingPlaceholder')}
                  style={{ width: '100%', padding: '0.6rem', border: '1px solid #e2e8f0', borderRadius: 6 }} />
              </div>
              <button onClick={checkout} className="btn btn-primary" style={{ width: '100%' }}>
                {t('cart.placeOrder')}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
