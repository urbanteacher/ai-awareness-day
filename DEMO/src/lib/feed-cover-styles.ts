import type { UpdateType } from '@/lib/feed-demo-data'
import { TYPE_TONE } from '@/lib/feed-demo-data'

/** Shown when no featured image — editor can override per post. */
export type CoverFallback = 'auto' | 'gradient' | 'tech'

export const COVER_FALLBACK_OPTIONS: { value: CoverFallback; label: string }[] = [
  { value: 'auto', label: 'Auto (category gradient)' },
  { value: 'gradient', label: 'Category gradient' },
  { value: 'tech', label: 'Tech pattern' },
]

const GRADIENTS: Record<UpdateType, string> = {
  announcement: 'from-violet-600 via-fuchsia-700 to-indigo-950',
  resource: 'from-sky-500 via-cyan-600 to-slate-900',
  partner: 'from-emerald-500 via-teal-600 to-gray-900',
  milestone: 'from-amber-500 via-orange-600 to-stone-900',
  media: 'from-rose-500 via-red-600 to-zinc-900',
  event: 'from-indigo-500 via-blue-700 to-slate-950',
  signup: 'from-teal-500 via-cyan-600 to-gray-900',
}

export const TYPE_COVER: Record<
  UpdateType,
  { gradient: string; icon: (typeof TYPE_TONE)[UpdateType]['icon'] }
> = {
  announcement: { gradient: GRADIENTS.announcement, icon: TYPE_TONE.announcement.icon },
  resource: { gradient: GRADIENTS.resource, icon: TYPE_TONE.resource.icon },
  partner: { gradient: GRADIENTS.partner, icon: TYPE_TONE.partner.icon },
  milestone: { gradient: GRADIENTS.milestone, icon: TYPE_TONE.milestone.icon },
  media: { gradient: GRADIENTS.media, icon: TYPE_TONE.media.icon },
  event: { gradient: GRADIENTS.event, icon: TYPE_TONE.event.icon },
  signup: { gradient: GRADIENTS.signup, icon: TYPE_TONE.signup.icon },
}

export function resolveCoverFallback(
  fallback: CoverFallback | undefined,
  type: UpdateType,
): 'gradient' | 'tech' {
  if (fallback === 'tech') return 'tech'
  if (fallback === 'gradient') return 'gradient'
  if (type === 'milestone' || type === 'signup' || type === 'resource') return 'tech'
  return 'gradient'
}
