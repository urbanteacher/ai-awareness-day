import { useState } from 'react'
import { Link } from 'react-router-dom'

import { FeedSwipeDeck } from '@/components/feed/FeedSwipeDeck'
import { TimelineProductionShell } from '@/components/feed/TimelineProductionShell'
import {
  FeedFilters,
  FeedGrid,
  FeedMagazine,
  FeedMinimal,
  FeedSplitMedia,
  StatsBar,
} from '@/components/feed/FeedVariants'
import {
  DEMO_UPDATES,
  filterUpdates,
  type FeedUpdate,
  type UpdateType,
} from '@/lib/feed-demo-data'

function FeedSection({
  id,
  title,
  blurb,
  dark,
  children,
}: {
  id: string
  title: string
  blurb: string
  dark?: boolean
  children: React.ReactNode
}) {
  return (
    <section
      id={id}
      className={`scroll-mt-8 border-t pt-12 ${dark ? 'border-white/10' : 'border-border'}`}
    >
      <div className="mb-6 flex items-baseline justify-between gap-4">
        <div>
          <h2 className={`text-xl font-semibold tracking-tight ${dark ? 'text-white' : ''}`}>{title}</h2>
          <p className={`mt-1 text-sm ${dark ? 'text-zinc-400' : 'text-muted-foreground'}`}>{blurb}</p>
        </div>
        <span className={`text-xs font-mono ${dark ? 'text-zinc-500' : 'text-muted-foreground'}`}>#{id}</span>
      </div>
      {children}
    </section>
  )
}

function CampaignFeedChrome({
  dark,
  showHeading = true,
  lead,
  children,
}: {
  dark?: boolean
  showHeading?: boolean
  lead?: React.ReactNode
  children: (items: FeedUpdate[]) => React.ReactNode
}) {
  const [filter, setFilter] = useState<UpdateType | 'all'>('all')
  const filtered = filterUpdates(DEMO_UPDATES, filter)

  return (
    <>
      {lead}
      {showHeading && !lead && (
        <div className="mb-4">
          <span
            className={`text-xs font-bold uppercase tracking-[0.2em] ${
              dark ? 'text-lime-400' : 'text-muted-foreground'
            }`}
          >
            Live
          </span>
          <h3 className={`mt-1 text-lg font-bold ${dark ? 'text-white' : 'text-foreground'}`}>
            Campaign Updates
          </h3>
        </div>
      )}
      <StatsBar dark={dark} />
      <FeedFilters active={filter} onChange={setFilter} dark={dark} />
      {filtered.length === 0 ? (
        <p
          className={`mt-4 rounded-lg border px-4 py-8 text-center text-sm ${
            dark
              ? 'border-white/10 bg-white/5 text-zinc-400'
              : 'border-border bg-muted/30 text-muted-foreground'
          }`}
        >
          No updates match this filter.
        </p>
      ) : (
        children(filtered)
      )}
    </>
  )
}

export function FeedStylesDemo() {
  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <header className="mb-10">
        <p className="text-xs font-bold uppercase tracking-[0.18em] text-muted-foreground">
          Campaign · feed style sandbox
        </p>
        <h1 className="mt-2 text-4xl font-bold tracking-tight">Timeline feed layouts</h1>
        <p className="mt-3 max-w-2xl text-muted-foreground">
          Side-by-side feed experiments for the WordPress Live Timeline (#timeline). Each section
          includes campaign stats, type filters, and two mock updates.
        </p>
        <div className="mt-4 flex flex-wrap gap-2 text-xs">
          {['swipe', 'production-shell', 'split', 'grid', 'minimal', 'magazine'].map((id) => (
            <a
              key={id}
              href={`#${id}`}
              className="rounded-full border border-border px-3 py-1 hover:border-foreground"
            >
              {id}
            </a>
          ))}
        </div>
        <Link to="/cards" className="mt-2 mr-4 inline-block text-sm text-primary hover:underline">
          Activity card styles →
        </Link>
        <Link to="/" className="mt-2 inline-block text-sm text-muted-foreground hover:text-foreground">
          ← All demos
        </Link>
      </header>

      <FeedSection
        id="swipe"
        title="Mobile swipe feed"
        blurb="Horizontal swipe across updates (portrait cards). Scroll down the page to leave the feed — same as production mobile."
        dark
      >
        <CampaignFeedChrome dark>
          {(filtered) => (
            <div className="mx-auto mt-4 w-full max-w-3xl">
              <FeedSwipeDeck items={filtered} orientation="portrait" />
            </div>
          )}
        </CampaignFeedChrome>
        <p className="mt-3 text-center text-xs text-zinc-500">
          Swipe sideways between updates, then scroll down for other sections.
        </p>
      </FeedSection>

      <FeedSection
        id="production-shell"
        title="1. Full section shell"
        blurb="Matches live #timeline: dark band, flashing Live label, stats bar, filters, mobile swipe + desktop magazine."
      >
        <TimelineProductionShell />
      </FeedSection>

      <FeedSection
        id="split"
        title="2. Alternating split (hybrid)"
        blurb="Zig-zag image + copy rows. Square badge and timeline type; layout stays split, not full timeline cards."
      >
        <CampaignFeedChrome>
          {(filtered) => <FeedSplitMedia items={filtered} />}
        </CampaignFeedChrome>
      </FeedSection>

      <FeedSection
        id="grid"
        title="3. Grid cards (hybrid)"
        blurb="Two-column cards with cover on top. Site badge and actions; card shell is grid-style, not timeline-entry."
      >
        <CampaignFeedChrome>
          {(filtered) => <FeedGrid items={filtered} />}
        </CampaignFeedChrome>
      </FeedSection>

      <FeedSection
        id="minimal"
        title="4. Minimal log (hybrid)"
        blurb="Compact changelog rows — monospace meta, sans title. Square badges only; no actions or card chrome."
      >
        <CampaignFeedChrome>
          {(filtered) => <FeedMinimal items={filtered} />}
        </CampaignFeedChrome>
      </FeedSection>

      <FeedSection
        id="magazine"
        title="5. Magazine (hybrid)"
        blurb="Magazine layout: large hero + border accent + index list below. Site badge, type, and actions on the hero only."
      >
        <CampaignFeedChrome>
          {(filtered) => <FeedMagazine items={filtered} />}
        </CampaignFeedChrome>
      </FeedSection>

      <footer className="mt-16 border-t border-border pt-6 text-sm text-muted-foreground">
        Edit <code className="rounded bg-muted px-1.5 py-0.5 text-xs">src/pages/FeedStylesDemo.tsx</code>{' '}
        and{' '}
        <code className="rounded bg-muted px-1.5 py-0.5 text-xs">src/components/feed/FeedVariants.tsx</code>
      </footer>
    </div>
  )
}
