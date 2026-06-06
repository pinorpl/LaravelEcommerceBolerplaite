import type { Metadata } from 'next'
import { AuthProvider } from '@/context/AuthContext'
import { LocaleProvider } from '@/context/LocaleContext'
import Navbar from '@/components/ui/Navbar'
import './globals.css'

export const metadata: Metadata = {
  title: 'Ecommerce Boilerplate',
  description: 'A full-stack ecommerce boilerplate built with Laravel 11 and Next.js 14',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body>
        {/* LocaleProvider wraps everything so any component can call useLocale() */}
        <LocaleProvider>
          <AuthProvider>
            <Navbar />
            <main className="main-content">
              {children}
            </main>
          </AuthProvider>
        </LocaleProvider>
      </body>
    </html>
  )
}
