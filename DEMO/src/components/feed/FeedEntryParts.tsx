import { ArrowRight, ExternalLink, Heart, Share2 } from 'lucide-react'

import type { FeedUpdate } from '@/lib/feed-demo-data'

/** Tailwind tokens aligned with WP `timeline-entry__*` (white card, square badge, gray type). */
export const feedEntry = {
  cardShell:
    'overflow-hidden rounded-none border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 hover:shadow-md',
  body: 'box-border w-full border border-gray-200 bg-white transition hover:border-gray-300 hover:shadow-[0_4px_16px_rgba(0,0,0,0.04)]',
  bodyPadding: 'px-4 py-3',
  badge:
    'inline-block bg-gray-900 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-[0.06em] text-white',
  date: 'shrink-0 text-[0.8rem] font-medium text-gray-500',
  title: 'min-w-0 flex-1 text-[0.95rem] font-bold leading-[1.35] text-gray-900',
  content: 'text-sm leading-relaxed text-gray-800',
  mediaBleed: 'border-t border-gray-100 bg-gray-50',
  actionsRow: 'mt-3 flex items-center gap-1 border-t border-gray-100 pt-3',
  actionBtn:
    'inline-flex items-center gap-1 border border-transparent px-2 py-1.5 text-xs text-gray-600 transition hover:border-gray-200 hover:bg-gray-50',
  actionBtnIcon:
    'inline-flex items-center border border-transparent p-1.5 text-gray-600 transition hover:border-gray-200 hover:bg-gray-50',
} as const

export function FeedEntryBadge({ u }: { u: FeedUpdate }) {
  return (
    <span className={feedEntry.badge}>{u.pinned ? 'Pinned' : u.badge}</span>
  )
}

export function FeedEntryTitleRow({ u }: { u: FeedUpdate }) {
  return (
    <div className="flex items-baseline justify-between gap-3">
      <h3 className={feedEntry.title}>{u.title}</h3>
      <time className={feedEntry.date}>{u.timeAgo}</time>
    </div>
  )
}

export function FeedEntryText({ u, showBadge = true }: { u: FeedUpdate; showBadge?: boolean }) {
  return (
    <div className="min-w-0 flex-1">
      {showBadge && (
        <div className="mb-2">
          <FeedEntryBadge u={u} />
        </div>
      )}
      <FeedEntryTitleRow u={u} />
      <p className={`mt-2 ${feedEntry.content}`}>{u.content}</p>
    </div>
  )
}

/** Badge on image (magazine sub-cards). */
export function FeedEntryImageOverlay({ u }: { u: FeedUpdate }) {
  return (
    <>
      <div
        className="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-transparent"
        aria-hidden
      />
      <div className="absolute left-2 top-2 z-10">
        <FeedEntryBadge u={u} />
      </div>
    </>
  )
}

export function FeedEntryActions({ likes }: { likes: number }) {
  return (
    <div className={feedEntry.actionsRow} aria-label="Actions">
      <button type="button" className={feedEntry.actionBtn} aria-label="Like">
        <Heart size={16} strokeWidth={1.75} />
        <span className="font-medium tabular-nums">{likes}</span>
      </button>
      <button type="button" className={feedEntry.actionBtn} aria-label="Share">
        <Share2 size={16} strokeWidth={1.75} />
      </button>
      <button type="button" className={feedEntry.actionBtnIcon} aria-label="Open link">
        <ExternalLink size={16} strokeWidth={1.75} />
      </button>
      <button type="button" className={`ml-auto ${feedEntry.actionBtnIcon}`} aria-label="View post">
        <ArrowRight size={16} strokeWidth={1.75} />
      </button>
    </div>
  )
}

export function FeedEntryMedia({
  src,
  alt = '',
  className = '',
}: {
  src: string
  alt?: string
  className?: string
}) {
  return (
    <figure className={`m-0 line-height-0 overflow-hidden ${feedEntry.mediaBleed} ${className}`}>
      <img src={src} alt={alt} className="block h-auto max-h-[480px] w-full object-cover" />
    </figure>
  )
}
