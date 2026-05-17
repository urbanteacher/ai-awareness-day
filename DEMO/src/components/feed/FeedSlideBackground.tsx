import { TYPE_COVER, resolveCoverFallback, type CoverFallback } from '@/lib/feed-cover-styles'
import type { UpdateType } from '@/lib/feed-demo-data'

type FeedSlideBackgroundProps = {
  type: UpdateType
  image?: string
  title: string
  coverFallback?: CoverFallback
  showEditorHint?: boolean
  className?: string
}

export function FeedSlideBackground({
  type,
  image,
  title,
  coverFallback = 'auto',
  showEditorHint = true,
  className = '',
}: FeedSlideBackgroundProps) {
  const cover = TYPE_COVER[type]
  const Icon = cover.icon
  const mode = image ? 'image' : resolveCoverFallback(coverFallback, type)

  return (
    <div className={`relative h-full w-full overflow-hidden ${className}`}>
      {image ? (
        <img src={image} alt="" className="absolute inset-0 h-full w-full object-cover" />
      ) : mode === 'tech' ? (
        <>
          <div
            className={`absolute inset-0 bg-gradient-to-br ${cover.gradient}`}
            aria-hidden
          />
          <svg
            className="absolute inset-0 h-full w-full opacity-30"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden
          >
            <defs>
              <pattern id={`grid-${type}`} width="28" height="28" patternUnits="userSpaceOnUse">
                <path
                  d="M 28 0 L 0 0 0 28"
                  fill="none"
                  stroke="white"
                  strokeWidth="0.6"
                  opacity="0.35"
                />
              </pattern>
            </defs>
            <rect width="100%" height="100%" fill={`url(#grid-${type})`} />
          </svg>
          <div
            className="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.18),transparent_45%),radial-gradient(circle_at_80%_70%,rgba(0,0,0,0.35),transparent_50%)]"
            aria-hidden
          />
          <div className="absolute left-4 top-1/2 -translate-y-1/2 opacity-20" aria-hidden>
            <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
              <circle cx="60" cy="60" r="48" stroke="white" strokeWidth="1" />
              <circle cx="60" cy="60" r="8" fill="white" />
              <path d="M60 12v16M60 92v16M12 60h16M92 60h16" stroke="white" strokeWidth="1" />
            </svg>
          </div>
        </>
      ) : (
        <div
          className={`absolute inset-0 bg-gradient-to-br ${cover.gradient}`}
          aria-hidden
        />
      )}

      {!image && (
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 p-6 text-center">
          <span className="flex h-14 w-14 items-center justify-center border border-white/25 bg-black/20 text-white backdrop-blur-sm">
            <Icon size={28} strokeWidth={1.5} />
          </span>
          <p className="max-w-[14rem] text-xs font-bold uppercase tracking-[0.2em] text-white/90 line-clamp-2">
            {title}
          </p>
        </div>
      )}

      {!image && showEditorHint && (
        <p className="absolute bottom-3 left-3 right-3 z-10 border border-white/20 bg-black/40 px-2 py-1 text-center text-[10px] font-medium uppercase tracking-wide text-white/80 backdrop-blur-sm">
          Add a featured image in WordPress to replace this background
        </p>
      )}

      <div
        className="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-white via-white/85 to-transparent"
        aria-hidden
      />
    </div>
  )
}
