// Shared TypeScript types mirroring the Laravel API responses

export interface User {
  id: number
  name: string
  email: string
  roles: string[]
  created_at: string
}

export interface Product {
  id: number
  name: string
  slug: string
  description: string | null
  price: number
  stock: number
  image: string | null
  is_active: boolean
  created_at: string
}

export interface CartItem {
  id: number
  quantity: number
  price: number
  subtotal: number
  product: {
    id: number
    name: string
    slug: string
    image: string | null
  } | null
}

export interface Cart {
  id: number
  total: number
  items: CartItem[]
}

export interface OrderItem {
  id: number
  product_name: string
  quantity: number
  unit_price: number
  subtotal: number
}

export interface Order {
  id: number
  status: 'pending' | 'paid' | 'shipped' | 'cancelled'
  total_amount: number
  shipping_address: string
  created_at: string
  items: OrderItem[]
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

/** Token response from Passport /oauth/token */
export interface TokenResponse {
  token_type: string
  expires_in: number
  access_token: string
  refresh_token: string
}
