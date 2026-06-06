'use client'

/**
 * AuthContext – manages authentication state across the Next.js client.
 *
 * Design decisions:
 * - The actual Passport token exchange happens in the Next.js API Route
 *   /api/auth/login (server-side), which keeps the client_secret out of
 *   the browser entirely.
 * - access_token and user data are stored in React state (memory) for
 *   client components. The httpOnly cookie set by the API route provides
 *   persistence across page reloads (read via /api/auth/me on mount).
 * - We deliberately avoid NextAuth.js to keep the integration explicit
 *   and educational.
 */

import React, { createContext, useContext, useState, useEffect, useCallback } from 'react'
import type { User } from '@/types'

interface AuthContextValue {
  user: User | null
  accessToken: string | null
  isLoading: boolean
  isAuthenticated: boolean
  hasRole: (role: string) => boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>
  logout: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [accessToken, setAccessToken] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  // On mount, try to restore session from the httpOnly cookie via a server-side check
  useEffect(() => {
    fetchCurrentUser()
  }, [])

  const fetchCurrentUser = async () => {
    try {
      const res = await fetch('/api/auth/me')
      if (res.ok) {
        const data = await res.json()
        setUser(data.user)
        setAccessToken(data.access_token)
      }
    } catch {
      // No active session
    } finally {
      setIsLoading(false)
    }
  }

  const login = useCallback(async (email: string, password: string) => {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })

    if (!res.ok) {
      const error = await res.json()
      throw new Error(error.message ?? 'Login failed')
    }

    const data = await res.json()
    setUser(data.user)
    setAccessToken(data.access_token)
  }, [])

  const register = useCallback(async (
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string
  ) => {
    const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation }),
    })

    if (!res.ok) {
      const error = await res.json()
      throw error
    }

    // After registration, automatically log in
    await login(email, password)
  }, [login])

  const logout = useCallback(async () => {
    await fetch('/api/auth/logout', { method: 'POST' })
    setUser(null)
    setAccessToken(null)
  }, [])

  const hasRole = useCallback((role: string) => {
    return user?.roles.includes(role) ?? false
  }, [user])

  return (
    <AuthContext.Provider value={{
      user,
      accessToken,
      isLoading,
      isAuthenticated: !!user,
      hasRole,
      login,
      register,
      logout,
    }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
