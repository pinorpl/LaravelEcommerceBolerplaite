'use client'

import { useEffect, useState } from 'react'
import { useAuth } from '@/context/AuthContext'
import { useLocale } from '@/context/LocaleContext'
import { useRouter } from 'next/navigation'
import type { Product, User, PaginatedResponse } from '@/types'

const API = process.env.NEXT_PUBLIC_API_URL

export default function AdminPage() {
  const { isAuthenticated, hasRole, isLoading, accessToken } = useAuth()
  const { t } = useLocale()
  const router = useRouter()
  const [tab, setTab] = useState<'products' | 'users'>('products')

  useEffect(() => {
    if (!isLoading && (!isAuthenticated || !hasRole('admin'))) router.push('/')
  }, [isLoading, isAuthenticated, hasRole])

  if (isLoading) return <div className="container" style={{ paddingTop: '2rem' }}>{t('common.loading')}</div>
  if (!isAuthenticated || !hasRole('admin')) return null

  return (
    <div className="container" style={{ paddingTop: '2rem' }}>
      <h1 style={{ marginBottom: '1.5rem' }}>{t('admin.title')}</h1>
      <div style={{ display: 'flex', gap: '0.5rem', marginBottom: '2rem' }}>
        <button onClick={() => setTab('products')} className={`btn ${tab === 'products' ? 'btn-primary' : 'btn-outline'}`}>
          {t('admin.tabs.products')}
        </button>
        <button onClick={() => setTab('users')} className={`btn ${tab === 'users' ? 'btn-primary' : 'btn-outline'}`}>
          {t('admin.tabs.users')}
        </button>
      </div>
      {tab === 'products' && <AdminProducts accessToken={accessToken!} />}
      {tab === 'users' && <AdminUsers accessToken={accessToken!} />}
    </div>
  )
}

function AdminProducts({ accessToken }: { accessToken: string }) {
  const { t } = useLocale()
  const [products, setProducts] = useState<Product[]>([])
  const [loading, setLoading] = useState(true)
  const [showForm, setShowForm] = useState(false)
  const [editProduct, setEditProduct] = useState<Product | null>(null)

  const fetchProducts = async () => {
    const res = await fetch(`${API}/admin/products`, { headers: { Authorization: `Bearer ${accessToken}`, Accept: 'application/json' } })
    if (res.ok) { const data: PaginatedResponse<Product> = await res.json(); setProducts(data.data) }
    setLoading(false)
  }
  useEffect(() => { fetchProducts() }, [])

  const deleteProduct = async (id: number) => {
    if (!confirm(t('admin.products.deleteConfirm'))) return
    await fetch(`${API}/admin/products/${id}`, { method: 'DELETE', headers: { Authorization: `Bearer ${accessToken}` } })
    await fetchProducts()
  }

  if (loading) return <p>{t('admin.products.loading')}</p>

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '1rem' }}>
        <h2>{t('admin.products.title', { count: products.length })}</h2>
        <button className="btn btn-primary" onClick={() => { setEditProduct(null); setShowForm(true) }}>
          {t('admin.products.newProduct')}
        </button>
      </div>
      {showForm && (
        <ProductForm product={editProduct} accessToken={accessToken}
          onSaved={() => { setShowForm(false); fetchProducts() }} onCancel={() => setShowForm(false)} />
      )}
      <table className="table">
        <thead>
          <tr>
            <th>{t('admin.products.name')}</th>
            <th>{t('admin.products.price')}</th>
            <th>{t('admin.products.stock')}</th>
            <th>{t('admin.products.active')}</th>
            <th>{t('admin.products.actions')}</th>
          </tr>
        </thead>
        <tbody>
          {products.map(p => (
            <tr key={p.id}>
              <td>{p.name}</td>
              <td>${p.price.toFixed(2)}</td>
              <td>{p.stock}</td>
              <td>{p.is_active ? '✓' : '✗'}</td>
              <td style={{ display: 'flex', gap: '0.5rem' }}>
                <button className="btn btn-outline" style={{ padding: '0.2rem 0.6rem' }}
                  onClick={() => { setEditProduct(p); setShowForm(true) }}>{t('admin.products.edit')}</button>
                <button className="btn btn-danger" style={{ padding: '0.2rem 0.6rem' }}
                  onClick={() => deleteProduct(p.id)}>{t('admin.products.delete')}</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

function ProductForm({ product, accessToken, onSaved, onCancel }: {
  product: Product | null; accessToken: string; onSaved: () => void; onCancel: () => void
}) {
  const { t } = useLocale()
  const [form, setForm] = useState({
    name: product?.name ?? '', price: product?.price ?? 0,
    stock: product?.stock ?? 0, description: product?.description ?? '', is_active: product?.is_active ?? true,
  })
  const [error, setError] = useState<string | null>(null)
  const [saving, setSaving] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault(); setSaving(true); setError(null)
    const url = product ? `${API}/admin/products/${product.id}` : `${API}/admin/products`
    const res = await fetch(url, {
      method: product ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${accessToken}`, Accept: 'application/json' },
      body: JSON.stringify(form),
    })
    if (res.ok) onSaved()
    else { const err = await res.json(); setError(err.message ?? t('common.error')) }
    setSaving(false)
  }

  return (
    <div className="card" style={{ padding: '1.5rem', marginBottom: '1.5rem' }}>
      <h3>{product ? t('admin.products.editTitle') : t('admin.products.newTitle')}</h3>
      {error && <div className="alert alert-error">{error}</div>}
      <form onSubmit={handleSubmit} style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
        <div className="form-group" style={{ gridColumn: '1 / -1' }}>
          <label>{t('admin.products.name')}</label>
          <input value={form.name} onChange={e => setForm(p => ({ ...p, name: e.target.value }))} required />
        </div>
        <div className="form-group">
          <label>{t('admin.products.price')}</label>
          <input type="number" step="0.01" min="0" value={form.price}
            onChange={e => setForm(p => ({ ...p, price: parseFloat(e.target.value) }))} required />
        </div>
        <div className="form-group">
          <label>{t('admin.products.stock')}</label>
          <input type="number" min="0" value={form.stock}
            onChange={e => setForm(p => ({ ...p, stock: parseInt(e.target.value) }))} required />
        </div>
        <div className="form-group" style={{ gridColumn: '1 / -1' }}>
          <label>{t('admin.products.description')}</label>
          <textarea value={form.description} onChange={e => setForm(p => ({ ...p, description: e.target.value }))}
            rows={3} style={{ width: '100%', padding: '0.6rem', border: '1px solid #e2e8f0', borderRadius: 6 }} />
        </div>
        <div className="form-group">
          <label>
            <input type="checkbox" checked={form.is_active}
              onChange={e => setForm(p => ({ ...p, is_active: e.target.checked }))} style={{ marginRight: '0.5rem' }} />
            {t('admin.products.activeLabel')}
          </label>
        </div>
        <div style={{ gridColumn: '1 / -1', display: 'flex', gap: '0.5rem' }}>
          <button type="submit" disabled={saving} className="btn btn-primary">
            {saving ? t('admin.products.saving') : t('admin.products.save')}
          </button>
          <button type="button" onClick={onCancel} className="btn btn-outline">{t('admin.products.cancel')}</button>
        </div>
      </form>
    </div>
  )
}

function AdminUsers({ accessToken }: { accessToken: string }) {
  const { t } = useLocale()
  const [users, setUsers] = useState<User[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetch(`${API}/admin/users`, { headers: { Authorization: `Bearer ${accessToken}`, Accept: 'application/json' } })
      .then(r => r.json()).then(data => { setUsers(data.data ?? []); setLoading(false) })
  }, [])

  if (loading) return <p>{t('admin.users.loading')}</p>

  return (
    <div>
      <h2 style={{ marginBottom: '1rem' }}>{t('admin.users.title', { count: users.length })}</h2>
      <table className="table">
        <thead>
          <tr>
            <th>{t('admin.users.name')}</th>
            <th>{t('admin.users.email')}</th>
            <th>{t('admin.users.roles')}</th>
            <th>{t('admin.users.joined')}</th>
          </tr>
        </thead>
        <tbody>
          {users.map(u => (
            <tr key={u.id}>
              <td>{u.name}</td>
              <td>{u.email}</td>
              <td>{u.roles?.map(r => <span key={r} className={`badge badge-${r}`} style={{ marginRight: '0.25rem' }}>{r}</span>)}</td>
              <td>{new Date(u.created_at).toLocaleDateString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
