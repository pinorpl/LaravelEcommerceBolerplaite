/**
 * Home page — Server Component.
 * Fetches featured products server-side (SSR) para SEO y rendimiento.
 *
 * Texto traducible (título, CTA, sección "Featured") se delega a Client
 * Components (HomeHero, SectionTitle) para que reaccionen al cambio de idioma
 * sin recargar la página.
 */

import { apiFetch } from '@/lib/api'
import { getServerT } from '@/lib/getServerT'
import type { PaginatedResponse, Product } from '@/types'
import Image from 'next/image'
import Link from 'next/link'
import HomeHero from '@/components/ui/HomeHero'
import SectionTitle from '@/components/ui/SectionTitle'

async function getFeaturedProducts(): Promise<Product[]> {
  try {
    const data = await apiFetch<PaginatedResponse<Product>>('/products?per_page=4')
    return data.data
  } catch {
    return []
  }
}

export default async function HomePage() {
  const products = await getFeaturedProducts()
  const t = getServerT()

  return (
    <div className="container">
      {/* Client Component: reacciona al cambio de idioma sin recargar */}
      <HomeHero />

      {products.length > 0 && (
        <section>
          {/* Client Component: título de sección traducido */}
          <SectionTitle messageKey="home.featured" />

          <div className="product-grid">
            {products.map((product) => (
              <Link key={product.id} href={`/products/${product.slug}`}
                style={{ textDecoration: 'none', color: 'inherit' }}>
                <div className="card product-card">
                  {product.image && (
                    <Image src={product.image} alt={product.name} width={640} height={200}
                      style={{ width: '100%', height: 200, objectFit: 'cover' }} />
                  )}
                  <div className="card-body">
                    <h3>{product.name}</h3>
                    <p className="price">${product.price.toFixed(2)}</p>
                    <p style={{ color: '#666', fontSize: '0.85rem', marginTop: '0.25rem' }}>
                      {product.stock > 0
                        ? t('home.inStock', { count: product.stock })
                        : t('home.outOfStock')}
                    </p>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        </section>
      )}
    </div>
  )
}
