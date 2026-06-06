'use client'

import { useLocale } from '@/context/LocaleContext'

/** Renders a translated section heading. Used by Server Component pages. */
export default function SectionTitle({ messageKey }: { messageKey: string }) {
  const { t } = useLocale()
  return <h2 style={{ marginBottom: '1rem' }}>{t(messageKey)}</h2>
}
