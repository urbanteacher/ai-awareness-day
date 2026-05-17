import { useCallback, useEffect, useRef, useState } from 'react'
import {
  ArrowRight,
  ChevronRight,
  ExternalLink,
  Heart,
  Share2,
} from 'lucide-react'

import { FeedSlideBackground } from '@/components/feed/FeedSlideBackground'
import { type FeedUpdate } from '@/lib/feed-demo-data'

type FeedSwipeDeckProps = {
  items: FeedUpdate[]
  className?: string
  /** Portrait = image on top (mobile). Landscape = image left. Both swipe horizontally. */
  orientation?: 'portrait' | 'landscape'
  heightClass?: string
}

/** Blog-post panel matching WP timeline-entry__body (white card, square badge, black text). */
function SwipeSlide({
  u,
  orientation,
}: {
  u: FeedUpdate
  orientation: 'portrait' | 'landscape'
}) {
  const landscape = orientation === 'landscape'

  return (
    <article
      className={`relative flex h-full min-h-full w-full min-w-full flex-shrink-0 snap-start snap-always overflow-hidden bg-white ${
        landscape ? 'flex-row' : 'flex-col'
      }`}
      aria-roledescription="slide"
    >
      <div
        className={
          landscape
            ? 'relative min-h-0 w-[52%] shrink-0 overflow-hidden bg-zinc-200 sm:w-[55%]'
            : 'relative min-h-[42%] flex-[1.35] shrink-0 overflow-hidden bg-zinc-200'
        }
      >
        <FeedSlideBackground
          type={u.type}
          image={u.image}
          title={u.title}
          coverFallback={u.coverFallback}
        />
      </div>

      <div
        className={`relative z-10 flex min-h-0 flex-col border-gray-200 bg-white ${
          landscape
            ? 'min-w-0 flex-1 border-l px-3 py-2.5 sm:px-4 sm:py-3'
            : 'flex-[1] border-t px-4 pb-4 pt-3'
        }`}
      >
        <div className="mb-1.5 flex items-center justify-between gap-2 sm:mb-2">
          <span className="inline-block bg-gray-900 px-2 py-0.5 font-mono text-[10px] font-bold uppercase tracking-[0.08em] text-white">
            {u.pinned ? 'Pinned' : u.badge}
          </span>
          <time className="shrink-0 text-[10px] font-medium text-gray-500 sm:text-xs">{u.timeAgo}</time>
        </div>

        <h3
          className={`font-bold leading-snug text-gray-900 ${
            landscape ? 'line-clamp-2 text-sm sm:text-base' : 'text-base'
          }`}
        >
          {u.title}
        </h3>

        <div
          className={`mt-1.5 min-h-0 flex-1 overflow-y-auto text-gray-800 [scrollbar-width:thin] sm:mt-2 ${
            landscape ? 'text-xs leading-relaxed sm:text-sm' : 'text-sm leading-relaxed'
          }`}
        >
          <p className={landscape ? 'line-clamp-4 sm:line-clamp-5' : undefined}>{u.content}</p>
        </div>

        <div className="mt-2 flex items-center gap-0.5 border-t border-gray-100 pt-2 sm:mt-3 sm:gap-1 sm:pt-3">
          <button
            type="button"
            className="inline-flex items-center gap-1 border border-transparent px-1.5 py-1 text-xs text-gray-600 transition hover:border-gray-200 hover:bg-gray-50 sm:px-2 sm:py-1.5"
            aria-label="Like"
          >
            <Heart size={14} strokeWidth={1.75} className="sm:h-4 sm:w-4" />
            <span className="font-medium tabular-nums">{u.likes}</span>
          </button>
          <button
            type="button"
            className="inline-flex items-center border border-transparent p-1 text-gray-600 transition hover:border-gray-200 hover:bg-gray-50 sm:p-1.5"
            aria-label="Share"
          >
            <Share2 size={14} strokeWidth={1.75} className="sm:h-4 sm:w-4" />
          </button>
          <button
            type="button"
            className="inline-flex items-center border border-transparent p-1 text-gray-600 transition hover:border-gray-200 hover:bg-gray-50 sm:p-1.5"
            aria-label="Open link"
          >
            <ExternalLink size={14} strokeWidth={1.75} className="sm:h-4 sm:w-4" />
          </button>
          <button
            type="button"
            className="ml-auto inline-flex items-center border border-transparent p-1 text-gray-600 transition hover:border-gray-200 hover:bg-gray-50 sm:p-1.5"
            aria-label="View post"
          >
            <ArrowRight size={14} strokeWidth={1.75} className="sm:h-4 sm:w-4" />
          </button>
        </div>

        {!landscape && (
          <a
            href="#"
            className="mt-3 inline-flex w-full items-center justify-center gap-1.5 border border-gray-900 bg-gray-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-gray-800"
            onClick={(e) => e.preventDefault()}
          >
            Learn more <ArrowRight size={14} />
          </a>
        )}
      </div>
    </article>
  )
}

export function FeedSwipeDeck({
  items,
  className = '',
  orientation = 'portrait',
  heightClass,
}: FeedSwipeDeckProps) {
  const landscape = orientation === 'landscape'
  const resolvedHeight =
    heightClass ??
    (landscape ? 'aspect-[16/10] w-full max-h-[min(42dvh,340px)]' : 'h-[min(65dvh,520px)]')

  const containerRef = useRef<HTMLDivElement>(null)
  const [active, setActive] = useState(0)
  const [hintVisible, setHintVisible] = useState(true)

  const syncActiveFromScroll = useCallback(() => {
    const el = containerRef.current
    if (!el || items.length === 0) return
    const slideWidth = el.clientWidth
    if (slideWidth <= 0) return
    const index = Math.min(items.length - 1, Math.max(0, Math.round(el.scrollLeft / slideWidth)))
    setActive(index)
    if (index > 0) setHintVisible(false)
  }, [items.length])

  useEffect(() => {
    const el = containerRef.current
    if (!el) return
    syncActiveFromScroll()
    el.addEventListener('scroll', syncActiveFromScroll, { passive: true })
    return () => el.removeEventListener('scroll', syncActiveFromScroll)
  }, [syncActiveFromScroll])

  const scrollToIndex = (index: number) => {
    const el = containerRef.current
    if (!el) return
    el.scrollTo({ left: index * el.clientWidth, behavior: 'smooth' })
  }

  if (items.length === 0) {
    return (
      <p className="border border-dashed border-border p-8 text-center text-sm text-muted-foreground">
        No updates to show.
      </p>
    )
  }

  return (
    <div className={className}>
      <div className={`relative ${resolvedHeight}`}>
        <div
          ref={containerRef}
          className="flex h-full snap-x snap-mandatory overflow-x-auto overflow-y-hidden overscroll-x-contain border border-gray-300 bg-white touch-pan-x shadow-2xl [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden"
          aria-label="Campaign updates, swipe horizontally"
          role="region"
        >
          {items.map((u) => (
            <SwipeSlide key={u.id} u={u} orientation={orientation} />
          ))}
        </div>

        <div
          className="pointer-events-none absolute bottom-2 left-1/2 z-20 flex -translate-x-1/2 flex-row gap-2"
          aria-hidden
        >
          {items.map((_, i) => (
            <button
              key={i}
              type="button"
              className={`pointer-events-auto h-2 w-2 transition ${
                i === active ? 'scale-125 bg-gray-900' : 'bg-gray-300'
              }`}
              onClick={() => scrollToIndex(i)}
              aria-label={`Go to update ${i + 1}`}
            />
          ))}
        </div>

        {hintVisible && active === 0 && items.length > 1 && (
          <div
            className="pointer-events-none absolute right-3 top-1/2 z-20 flex -translate-y-1/2 items-center gap-1 text-gray-700"
            aria-hidden
          >
            <span className="bg-white/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest shadow-sm">
              Swipe
            </span>
            <ChevronRight size={20} className="animate-pulse" />
          </div>
        )}
      </div>

      <p className="mt-2 text-center text-xs text-muted-foreground">
        {active + 1} / {items.length}
        <span className="mt-1 block text-[10px] text-muted-foreground/80">
          Swipe sideways for more · scroll down for the rest of the page
        </span>
      </p>
    </div>
  )
}
