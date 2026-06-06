import { apiFetch } from '@/lib/api'
import { getServerT } from '@/lib/getServerT'
import type { PaginatedResponse, Product } from '@/types'
import Image from 'next/image'
import Link from 'next/link'
import ProductSearch from '@/components/products/ProductSearch'

interface PageProps {
  searchParams: { search?: string; page?: string }
}

async function getProducts(search?: string, page = 1): Promise<PaginatedResponse<Product> | null> {
  try {
    const params = new URLSearchParams({ per_page: '12', page: String(page) })
    if (search) params.set('search', search)
    return await apiFetch<PaginatedResponse<Product>>(`/products?${params}`)
  } catch {
    return null
  }
}

export default async function ProductsPage({ searchParams }: PageProps) {
  const { search, page } = searchParams
  const data = await getProducts(search, page ? parseInt(page) : 1)
  const t = getServerT()

  return (
    <div className="container">
      <div className="page-header">
        <h1>{t('products.title')}</h1>
      </div>

      <ProductSearch defaultValue={search} />

      {!data ? (
        <p>{t('products.loadError')}</p>
      ) : data.data.length === 0 ? (
        <p>{search ? t('products.noResultsSearch', { search }) : t('products.noResults')}</p>
      ) : (
        <>
          <p style={{ color: '#666', marginBottom: '1rem' }}>
            {t('products.showing', { from: data.meta.from, to: data.meta.to, total: data.meta.total })}
          </p>
          <div className="product-grid">
            {data.data.map((product) => (
              <Link key={product.id} href={`/products/${product.slug}`} style={{ textDecoration: 'none', color: 'inherit' }}>
                <div className="card product-card">
                  {product.image && (
                    <Image src={product.image} alt={product.name} width={640} height={200}
                      style={{ width: '100%', height: 200, objectFit: 'cover' }} />
                  )}
                  <div className="card-body">
                    <h3>{product.name}</h3>
                    <p className="price">${product.price.toFixed(2)}</p>
                    <p style={{ fontSize: '0.85rem', color: '#666' }}>
                      {product.stock > 0
                        ? t('products.inStock', { count: product.stock })
                        : t('products.outOfStock')}
                    </p>
                  </div>
                </div>
              </Link>
            ))}
          </div>

          {data.meta.last_page > 1 && (
            <div style={{ display: 'flex', gap: '0.5rem', justifyContent: 'center', marginTop: '2rem' }}>
              {Array.from({ length: data.meta.last_page }, (_, i) => i + 1).map((p) => (
                <Link key={p}
                  href={`/products?${new URLSearchParams({ ...(search ? { search } : {}), page: String(p) })}`}
                  className="btn btn-outline"
                  style={{ padding: '0.4rem 0.8rem', fontWeight: p === data.meta.current_page ? 700 : 400 }}>
                  {p}
                </Link>
              ))}
            </div>
          )}
        </>
      )}
    </div>
  )
}
