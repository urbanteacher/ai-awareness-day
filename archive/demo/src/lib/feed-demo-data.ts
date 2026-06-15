import type { LucideIcon } from 'lucide-react'
import { Building2, Calendar, FileText, Megaphone, Star, Users } from 'lucide-react'

import type { CoverFallback } from '@/lib/feed-cover-styles'

export type UpdateType =
  | 'announcement'
  | 'resource'
  | 'partner'
  | 'milestone'
  | 'media'
  | 'event'
  | 'signup'

export type FeedUpdate = {
  id: string
  type: UpdateType
  badge: string
  title: string
  content: string
  timeAgo: string
  image?: string
  /** Used when no image — auto picks gradient vs tech by type. */
  coverFallback?: CoverFallback
  pinned?: boolean
  likes: number
}

const img = (seed: string) => `https://picsum.photos/seed/${seed}/640/400`

export const DEMO_UPDATES: FeedUpdate[] = [
  {
    id: '1',
    type: 'partner',
    badge: 'Partner',
    title: 'Accenture joined as Tech Company',
    content: 'Accenture is supporting AI Awareness Day with workshops and school outreach.',
    timeAgo: '2h ago',
    image: img('accenture-partner'),
    pinned: true,
    likes: 12,
  },
  {
    id: '2',
    type: 'resource',
    badge: 'New resource',
    title: 'AI Relationships: Easier Than the Real Thing?',
    content:
      'A 5-minute discussion starter using a short viral clip — ideal for tutor time or assemblies.',
    timeAgo: '1d ago',
    image: img('ai-relationships'),
    likes: 28,
  },
]

/** Matches WP `aiad_timeline_icon_options()` + All. */
export const FILTER_OPTIONS: { value: UpdateType | 'all'; label: string }[] = [
  { value: 'all', label: 'All' },
  { value: 'announcement', label: 'Announcement' },
  { value: 'resource', label: 'New Resource' },
  { value: 'partner', label: 'New Partner' },
  { value: 'signup', label: 'Sign-up / Submission' },
  { value: 'milestone', label: 'Milestone' },
  { value: 'media', label: 'Press / Media' },
  { value: 'event', label: 'Event' },
]

export const TYPE_TONE: Record<
  UpdateType,
  { dot: string; chip: string; icon: LucideIcon; bar: string }
> = {
  announcement: {
    dot: 'bg-violet-500',
    chip: 'bg-violet-500/15 text-violet-700 dark:text-violet-300',
    icon: Megaphone,
    bar: 'bg-violet-500',
  },
  resource: {
    dot: 'bg-sky-500',
    chip: 'bg-sky-500/15 text-sky-700 dark:text-sky-300',
    icon: FileText,
    bar: 'bg-sky-500',
  },
  partner: {
    dot: 'bg-emerald-500',
    chip: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
    icon: Building2,
    bar: 'bg-emerald-500',
  },
  milestone: {
    dot: 'bg-amber-500',
    chip: 'bg-amber-500/15 text-amber-800 dark:text-amber-300',
    icon: Star,
    bar: 'bg-amber-500',
  },
  media: {
    dot: 'bg-rose-500',
    chip: 'bg-rose-500/15 text-rose-700 dark:text-rose-300',
    icon: Calendar,
    bar: 'bg-rose-500',
  },
  event: {
    dot: 'bg-indigo-500',
    chip: 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300',
    icon: Calendar,
    bar: 'bg-indigo-500',
  },
  signup: {
    dot: 'bg-teal-500',
    chip: 'bg-teal-500/15 text-teal-700 dark:text-teal-300',
    icon: Users,
    bar: 'bg-teal-500',
  },
}

export function filterUpdates(items: FeedUpdate[], filter: UpdateType | 'all') {
  if (filter === 'all') return items
  return items.filter((u) => u.type === filter)
}
