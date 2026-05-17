import { Heart, Share2 } from 'lucide-react'

import {
  feedEntry,
  FeedEntryActions,
  FeedEntryBadge,
  FeedEntryImageOverlay,
  FeedEntryText,
} from '@/components/feed/FeedEntryParts'
import { FeedSlideBackground } from '@/components/feed/FeedSlideBackground'
import {
  FILTER_OPTIONS,
  type FeedUpdate,
  TYPE_TONE,
  type UpdateType,
} from '@/lib/feed-demo-data'

export { FeedEntryActions as EntryActions }

function EntryActions({ likes, dark }: { likes: number; dark?: boolean }) {
  if (!dark) return <FeedEntryActions likes={likes} />
  const btn = 'text-zinc-400 hover:bg-white/10 hover:text-white'
  return (
    <div className="mt-3 flex items-center gap-2 border-t border-white/10 pt-3">
      <button type="button" className={`inline-flex items-center gap-1 px-2 py-1 text-xs transition ${btn}`}>
        <Heart size={14} />
        <span>{likes}</span>
      </button>
      <button type="button" className={`inline-flex items-center gap-1 px-2 py-1 text-xs transition ${btn}`}>
        <Share2 size={14} />
        Share
      </button>
    </div>
  )
}

function EntryTextBlock({ u, dark }: { u: FeedUpdate; dark?: boolean }) {
  if (!dark) return <FeedEntryText u={u} />
  return (
    <>
      <span
        className={`inline-block px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-[0.06em] ${
          u.pinned ? 'bg-lime-400/20 text-lime-300' : 'bg-gray-900 text-white'
        }`}
      >
        {u.pinned ? 'Pinned' : u.badge}
      </span>
      <div className="mt-2 flex flex-wrap items-baseline justify-between gap-2">
        <h3 className="text-[0.95rem] font-bold leading-[1.35] text-white">{u.title}</h3>
        <time className="shrink-0 text-[0.8rem] font-medium text-zinc-500">{u.timeAgo}</time>
      </div>
      <p className="mt-2 text-sm leading-relaxed text-zinc-300">{u.content}</p>
    </>
  )
}

export function FeedProductionTimeline({ items, dark = true }: { items: FeedUpdate[]; dark?: boolean }) {
  return (
    <div
      className={`relative rounded-lg border p-4 sm:p-6 ${
        dark ? 'border-white/10 bg-black/30' : 'border-border bg-muted/30'
      }`}
    >
      <div
        className={`absolute bottom-6 left-[1.65rem] top-6 w-px ${dark ? 'bg-white/15' : 'bg-border'}`}
        aria-hidden
      />
      <ul className="relative space-y-6">
        {items.map((u) => {
          const tone = TYPE_TONE[u.type]
          const Icon = tone.icon
          return (
            <li key={u.id} className="grid grid-cols-[2.5rem_1fr] gap-3 sm:gap-4">
              <div className="relative z-10 flex justify-center pt-1">
                <span
                  className={`flex h-9 w-9 items-center justify-center rounded-full ring-2 ${
                    dark ? 'bg-zinc-800 ring-zinc-700' : 'bg-card ring-border'
                  } ${u.pinned ? 'ring-lime-400' : ''}`}
                >
                  <Icon size={16} className={dark ? 'text-white' : 'text-foreground'} />
                </span>
              </div>
              <article
                className={`overflow-hidden rounded-lg border shadow-lg transition ${
                  dark
                    ? 'border-zinc-600/80 bg-zinc-800 hover:border-zinc-400'
                    : 'border-border bg-card hover:border-foreground/20'
                }`}
              >
                <div className="flex flex-col md:flex-row">
                  <div className="min-w-0 flex-1 p-4 sm:p-5">
                    <EntryTextBlock u={u} dark={dark} />
                    <EntryActions likes={u.likes} dark={dark} />
                  </div>
                  {u.image && (
                    <figure className="md:w-44 lg:w-52 shrink-0 border-t md:border-l md:border-t-0 border-white/10">
                      <img src={u.image} alt="" className="h-40 w-full object-cover md:h-full" />
                    </figure>
                  )}
                </div>
              </article>
            </li>
          )
        })}
      </ul>
    </div>
  )
}

export function FeedCompactList({ items }: { items: FeedUpdate[] }) {
  return (
    <ul className="divide-y divide-border rounded-lg border border-border bg-card">
      {items.map((u) => {
        const tone = TYPE_TONE[u.type]
        return (
          <li key={u.id} className="flex gap-3 px-4 py-3 transition hover:bg-muted/40">
            <span className={`mt-1.5 h-2 w-2 shrink-0 rounded-full ${tone.dot}`} />
            <div className="min-w-0 flex-1">
              <div className="flex flex-wrap items-baseline justify-between gap-2">
                <p className="font-semibold leading-snug">{u.title}</p>
                <span className="text-xs text-muted-foreground">{u.timeAgo}</span>
              </div>
              <p className="mt-0.5 line-clamp-2 text-sm text-muted-foreground">{u.content}</p>
              <span className={`mt-1.5 inline-block text-[10px] font-bold uppercase tracking-wider ${tone.chip} rounded px-1.5 py-0.5`}>
                {u.badge}
              </span>
            </div>
          </li>
        )
      })}
    </ul>
  )
}

export function FeedCardStack({ items }: { items: FeedUpdate[] }) {
  return (
    <ul className="space-y-3">
      {items.map((u) => {
        const tone = TYPE_TONE[u.type]
        return (
          <li
            key={u.id}
            className="overflow-hidden rounded-lg border border-border bg-card shadow-sm transition hover:shadow-md"
          >
            <div className={`h-1 ${tone.bar}`} />
            <div className="p-4">
              <EntryTextBlock u={u} />
              {u.image && (
                <img src={u.image} alt="" className="mt-3 h-32 w-full rounded-md object-cover" />
              )}
              <div className="mt-3">
                <EntryActions likes={u.likes} />
              </div>
            </div>
          </li>
        )
      })}
    </ul>
  )
}

export function FeedSocialThread({ items }: { items: FeedUpdate[] }) {
  return (
    <ul className="space-y-5">
      {items.map((u) => {
        const tone = TYPE_TONE[u.type]
        const Icon = tone.icon
        return (
          <li key={u.id} className="flex gap-3">
            <span className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white ${tone.bar}`}>
              <Icon size={18} />
            </span>
            <div className="min-w-0 flex-1 rounded-2xl border border-border bg-muted/30 px-4 py-3">
              <div className="mb-1 flex items-center gap-2 text-xs text-muted-foreground">
                <span className="font-semibold text-foreground">{u.badge}</span>
                <span>·</span>
                <span>{u.timeAgo}</span>
              </div>
              <h3 className="font-semibold leading-snug">{u.title}</h3>
              <p className="mt-1 text-sm text-muted-foreground">{u.content}</p>
              {u.image && (
                <img src={u.image} alt="" className="mt-3 max-h-48 w-full rounded-lg object-cover" />
              )}
              <div className="mt-2">
                <EntryActions likes={u.likes} />
              </div>
            </div>
          </li>
        )
      })}
    </ul>
  )
}

export function FeedSplitMedia({ items }: { items: FeedUpdate[] }) {
  return (
    <ul className="space-y-6">
      {items.map((u, i) => (
        <li
          key={u.id}
          className={`flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:shadow-md md:flex-row ${
            i % 2 === 1 ? 'md:flex-row-reverse' : ''
          }`}
        >
          <div className="relative min-h-[12rem] shrink-0 md:w-2/5">
            {u.image ? (
              <img src={u.image} alt="" className="h-full min-h-[12rem] w-full object-cover" />
            ) : (
              <FeedSlideBackground
                type={u.type}
                title={u.title}
                coverFallback={u.coverFallback}
                showEditorHint={false}
                className="min-h-[12rem]"
              />
            )}
          </div>
          <div className="flex min-w-0 flex-1 flex-col justify-center px-5 py-4 md:px-6">
            <div className="mb-2 flex flex-wrap items-center gap-2">
              <FeedEntryBadge u={u} />
              <time className={feedEntry.date}>{u.timeAgo}</time>
            </div>
            <h3 className="text-lg font-bold leading-snug text-gray-900">{u.title}</h3>
            <p className={`mt-2 ${feedEntry.content}`}>{u.content}</p>
            <FeedEntryActions likes={u.likes} />
          </div>
        </li>
      ))}
    </ul>
  )
}


export function FeedGrid({ items }: { items: FeedUpdate[] }) {
  return (
    <ul className="grid gap-4 sm:grid-cols-2">
      {items.map((u) => (
        <li
          key={u.id}
          className="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:border-gray-300 hover:shadow-md"
        >
          {u.image ? (
            <img src={u.image} alt="" className="aspect-[16/10] w-full object-cover" />
          ) : (
            <div className="relative aspect-[16/10] w-full overflow-hidden">
              <FeedSlideBackground
                type={u.type}
                title={u.title}
                coverFallback={u.coverFallback}
                showEditorHint={false}
                className="h-full"
              />
            </div>
          )}
          <div className="flex flex-1 flex-col px-4 py-3">
            <div className="mb-2 flex flex-wrap items-center gap-2">
              <FeedEntryBadge u={u} />
              <time className={feedEntry.date}>{u.timeAgo}</time>
            </div>
            <h3 className={`${feedEntry.title} line-clamp-2`}>{u.title}</h3>
            <p className={`mt-2 line-clamp-3 ${feedEntry.content}`}>{u.content}</p>
            <FeedEntryActions likes={u.likes} />
          </div>
        </li>
      ))}
    </ul>
  )
}

export function FeedMinimal({ items }: { items: FeedUpdate[] }) {
  return (
    <ul className="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white font-mono text-sm">
      {items.map((u) => (
        <li
          key={u.id}
          className="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:flex-wrap sm:items-baseline sm:gap-x-3"
        >
          <span className={feedEntry.badge}>{u.badge}</span>
          <time className={`${feedEntry.date} tabular-nums`}>{u.timeAgo}</time>
          <span className="min-w-0 flex-1 font-sans font-bold leading-snug text-gray-900">{u.title}</span>
          <p className={`w-full font-sans ${feedEntry.content} line-clamp-1`}>{u.content}</p>
        </li>
      ))}
    </ul>
  )
}

function MagazineSubCard({ u }: { u: FeedUpdate }) {
  return (
    <li className="group list-none">
      <article className={`flex h-full flex-col sm:flex-row ${feedEntry.cardShell}`}>
        <div className="relative min-h-[8.5rem] shrink-0 overflow-hidden bg-gray-100 sm:min-h-[7.25rem] sm:w-[42%]">
          {u.image ? (
            <img
              src={u.image}
              alt=""
              className="h-full min-h-[8.5rem] w-full object-cover transition duration-300 group-hover:scale-[1.02] sm:min-h-[7.25rem]"
            />
          ) : (
            <FeedSlideBackground
              type={u.type}
              title={u.title}
              coverFallback={u.coverFallback}
              showEditorHint={false}
              className="min-h-[8.5rem] sm:min-h-[7.25rem]"
            />
          )}
          <FeedEntryImageOverlay u={u} />
        </div>
        <div className="flex min-h-0 min-w-0 flex-1 flex-col px-4 py-3.5 sm:px-5 sm:py-4">
          <time className={`mb-2 block ${feedEntry.date}`}>{u.timeAgo}</time>
          <h4 className={`${feedEntry.title} line-clamp-2 transition-colors group-hover:text-gray-700`}>
            {u.title}
          </h4>
          <p className={`mt-1.5 line-clamp-2 ${feedEntry.content}`}>{u.content}</p>
          <div className="mt-auto pt-2">
            <FeedEntryActions likes={u.likes} />
          </div>
        </div>
      </article>
    </li>
  )
}

export function FeedMagazine({ items }: { items: FeedUpdate[] }) {
  const [hero, ...rest] = items
  if (!hero) return null
  return (
    <div className="space-y-6">
      <article className="grid gap-6 md:grid-cols-2 md:gap-8">
        {hero.image ? (
          <img src={hero.image} alt="" className="aspect-[4/3] w-full object-cover" />
        ) : (
          <div className="relative aspect-[4/3] w-full overflow-hidden">
            <FeedSlideBackground
              type={hero.type}
              title={hero.title}
              coverFallback={hero.coverFallback}
              showEditorHint={false}
              className="h-full"
            />
          </div>
        )}
        <div className="flex flex-col justify-center border-l-0 md:border-l-2 md:border-gray-900 md:pl-8">
          <div className="mb-3 flex flex-wrap items-center gap-2">
            <FeedEntryBadge u={hero} />
            <time className={feedEntry.date}>{hero.timeAgo}</time>
          </div>
          <h3 className="text-2xl font-bold leading-tight text-gray-900 md:text-3xl">{hero.title}</h3>
          <p className={`mt-3 ${feedEntry.content}`}>{hero.content}</p>
          <FeedEntryActions likes={hero.likes} />
        </div>
      </article>

      {rest.length > 0 && (
        <ul className="grid list-none gap-4 border-t border-gray-200 pt-6 sm:grid-cols-2 sm:gap-5">
          {rest.map((u) => (
            <MagazineSubCard key={u.id} u={u} />
          ))}
        </ul>
      )}
    </div>
  )
}


export function StatsBar({ dark }: { dark?: boolean }) {
  return (
    <div
      className={`mb-4 flex flex-wrap items-center gap-x-3 gap-y-1 overflow-x-auto px-4 py-3 text-xs font-bold uppercase tracking-wider ${
        dark ? 'border border-white/10 bg-black/35 text-white' : 'border border-border bg-zinc-900 text-white'
      }`}
      role="status"
    >
      <span>⏱ 18 days to go</span>
      <span className="opacity-40">·</span>
      <span>0 schools registered</span>
      <span className="opacity-40">·</span>
      <span>8 free resources</span>
    </div>
  )
}

export function FeedFilters({
  active,
  onChange,
  dark,
  production,
}: {
  active: UpdateType | 'all'
  onChange: (v: UpdateType | 'all') => void
  dark?: boolean
  production?: boolean
}) {
  const base =
    'timeline-filter-btn inline-flex shrink-0 items-center rounded border px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.04em] transition focus:outline focus:outline-2 focus:outline-offset-2'

  const inactive = production
    ? ''
    : dark
      ? 'border-white/15 bg-white/[0.06] text-zinc-200 hover:border-white/20 hover:bg-white/10 hover:text-white focus:outline-lime-400'
      : 'border-gray-300 bg-white text-gray-600 hover:border-gray-400 hover:bg-gray-50 hover:text-gray-900 focus:outline-green-600'

  const activeCls = production
    ? 'timeline-filter-btn--active'
    : dark
      ? 'timeline-filter-btn--active border-white bg-white text-gray-900 hover:border-gray-100 hover:bg-gray-100 focus:outline-lime-400'
      : 'timeline-filter-btn--active border-gray-900 bg-gray-900 text-white hover:border-gray-800 hover:bg-gray-800 focus:outline-green-600'

  return (
    <div
      className={`timeline-filters flex flex-wrap gap-2 max-md:flex-nowrap max-md:overflow-x-auto max-md:pb-1 [scrollbar-width:none] md:gap-2 [&::-webkit-scrollbar]:hidden ${production ? '' : 'mb-4'}`}
      role="group"
      aria-label="Filter updates"
    >
      {FILTER_OPTIONS.map((opt) => (
        <button
          key={opt.value}
          type="button"
          onClick={() => onChange(opt.value)}
          className={`${base} ${active === opt.value ? activeCls : inactive}`}
        >
          {opt.label}
        </button>
      ))}
    </div>
  )
}
