import { apiFetch } from '@/lib/api'
import { getServerT } from '@/lib/getServerT'
import type { Product } from '@/types'
import Image from 'next/image'
import { notFound } from 'next/navigation'
import AddToCartButton from '@/components/products/AddToCartButton'

interface PageProps { params: { slug: string } }

export const revalidate = 60

async function getProduct(slug: string): Promise<Product | null> {
  try {
    return await apiFetch<Product>(`/products/${slug}`)
  } catch (e: any) {
    if (e.status === 404) return null
    throw e
  }
}

export default async function ProductDetailPage({ params }: PageProps) {
  const product = await getProduct(params.slug)
  const t = getServerT()

  if (!product) notFound()

  return (
    <div className="container" style={{ paddingTop: '2rem' }}>
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '2rem', alignItems: 'start' }}>
        <div>
          {product.image ? (
            <Image src={product.image} alt={product.name} width={640} height={480}
              style={{ width: '100%', borderRadius: 8 }} />
          ) : (
            <div style={{ background: '#e2e8f0', height: 400, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              No image
            </div>
          )}
        </div>
        <div>
          <h1 style={{ fontSize: '1.8rem', marginBottom: '0.5rem' }}>{product.name}</h1>
          <p style={{ fontSize: '2rem', color: '#e94560', fontWeight: 700, marginBottom: '1rem' }}>
            ${product.price.toFixed(2)}
          </p>
          <p style={{ marginBottom: '1rem', color: product.stock > 0 ? '#16a34a' : '#dc2626', fontWeight: 500 }}>
            {product.stock > 0
              ? `✓ ${t('products.inStock', { count: product.stock })}`
              : `✗ ${t('products.outOfStock')}`}
          </p>
          {product.description && (
            <p style={{ color: '#555', lineHeight: 1.7, marginBottom: '1.5rem' }}>
              {product.description}
            </p>
          )}
          <AddToCartButton product={product} />
        </div>
      </div>
    </div>
  )
}
