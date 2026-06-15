import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'

import {
  BlogArchiveFeaturedLead,
  BlogArchiveList,
  BlogArchiveNewspaper,
  BlogArchiveNewsSidebar,
  BlogArchivePhotoGrid,
} from '@/components/blog/BlogArchiveLayouts'
import { LayoutPreview, LayoutRefs } from '@/components/blog/BlogShared'
import { BlogWpArchiveCurrent, BlogWpTimelineSingleCurrent } from '@/components/blog/BlogWordPressCurrent'
import {
  BlogSingleClassic,
  BlogSingleDocs,
  BlogSingleEditorial,
  BlogSingleMagazine,
  BlogSinglePublication,
  BlogSingleSplit,
  BlogSingleTextBlog,
  BlogSingleVertical,
  BlogSingleWide,
} from '@/components/blog/BlogSingleLayouts'

function Section({
  id,
  title,
  blurb,
  refs,
  recommended,
  children,
}: {
  id: string
  title: string
  blurb: string
  refs?: string[]
  recommended?: boolean
  children: ReactNode
}) {
  return (
    <section id={id} className="scroll-mt-8 border-t border-border pt-12 first:border-t-0 first:pt-0">
      <div className="mb-6">
        <div className="flex flex-wrap items-center gap-2">
          <h2 className="text-xl font-semibold tracking-tight">{title}</h2>
          {recommended ? (
            <span className="rounded-full bg-violet-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
              Recommended
            </span>
          ) : null}
        </div>
        <p className="mt-1 max-w-2xl text-sm text-muted-foreground">{blurb}</p>
        {refs?.length ? (
          <div className="mt-3">
            <LayoutRefs refs={refs} />
          </div>
        ) : null}
        <span className="mt-2 inline-block font-mono text-xs text-muted-foreground">#{id}</span>
      </div>
      {children}
    </section>
  )
}

const RECOMMENDED = {
  single: 'single-text-blog',
  archive: 'archive-featured',
} as const

const SINGLE_LAYOUTS = [
  {
    id: 'single-text-blog',
    label: 'Single · Text blog',
    blurb:
      '720px reading column, engagement row, author box — closest to TT5 text-blog and our theme.json contentSize (Björk). Best default for AIAD practice posts.',
    refs: ['TT5 text-blog', 'Medium', 'Björk', '720px'],
    recommended: true,
    Component: BlogSingleTextBlog,
  },
  {
    id: 'single-publication',
    label: 'Single · Publication',
    blurb: 'Substack-style masthead, mid-article subscribe strips, drop-cap lead. Good if we add email capture.',
    refs: ['Substack', 'TT5 news-blog'],
    recommended: false,
    Component: BlogSinglePublication,
  },
  {
    id: 'single-vertical',
    label: 'Single · Vertical nav',
    blurb: 'Fixed left rail with recent posts + TOC — TT5 vertical-header-blog / Notion docs hybrid.',
    refs: ['TT5 vertical-header', 'Notion', 'Björk'],
    recommended: false,
    Component: BlogSingleVertical,
  },
  {
    id: 'single-classic',
    label: 'Single · Classic',
    blurb: 'Serif title, centered column, tags + related — safe WordPress single.php baseline.',
    refs: ['WP default', 'Frost'],
    recommended: false,
    Component: BlogSingleClassic,
  },
  {
    id: 'single-editorial',
    label: 'Single · Editorial',
    blurb: 'Long-read with sticky TOC rail and drop cap — Medium-style essays.',
    refs: ['Medium', 'Frost'],
    recommended: false,
    Component: BlogSingleEditorial,
  },
  {
    id: 'single-magazine',
    label: 'Single · Magazine',
    blurb: 'Full-bleed hero, dark body band — feature stories and campaign launches.',
    refs: ['Substack feature', 'Magazine'],
    recommended: false,
    Component: BlogSingleMagazine,
  },
  {
    id: 'single-split',
    label: 'Single · Split meta',
    blurb: 'Sticky left column for author, date, tags; content on the right.',
    refs: ['Frost author', 'Ollie'],
    recommended: false,
    Component: BlogSingleSplit,
  },
  {
    id: 'single-docs',
    label: 'Single · Docs',
    blurb: 'Left contents nav, monospace meta — policy explainers and how-tos.',
    refs: ['Notion', 'TT5'],
    recommended: false,
    Component: BlogSingleDocs,
  },
  {
    id: 'single-wide',
    label: 'Single · Wide',
    blurb: 'Page header band, full-width hero, two-column body — photo-heavy showcases.',
    refs: ['TT5 photo-blog', 'Magazine'],
    recommended: false,
    Component: BlogSingleWide,
  },
] as const

const ARCHIVE_LAYOUTS = [
  {
    id: 'archive-featured',
    label: 'Archive · Featured lead',
    blurb: 'One hero story with image; remaining posts compact — strong blog home for mixed audiences.',
    refs: ['TT5', 'Substack home', 'WP home.php'],
    recommended: true,
    Component: BlogArchiveFeaturedLead,
  },
  {
    id: 'archive-news-sidebar',
    label: 'Archive · News + sidebar',
    blurb: 'Publication masthead, lead story, subscribe + topics in sticky rail.',
    refs: ['TT5 news-blog', 'Substack'],
    recommended: false,
    Component: BlogArchiveNewsSidebar,
  },
  {
    id: 'archive-photo-grid',
    label: 'Archive · Photo grid',
    blurb: 'Image-first masonry with captions — display boards, galleries, visual case studies.',
    refs: ['TT5 photo-blog', 'Instagram grid'],
    recommended: false,
    Component: BlogArchivePhotoGrid,
  },
  {
    id: 'archive-list',
    label: 'Archive · List',
    blurb: 'Minimal stacked titles — mirrors today’s basic index.php.',
    refs: ['WP index', 'Minimal'],
    recommended: false,
    Component: BlogArchiveList,
  },
  {
    id: 'archive-newspaper',
    label: 'Archive · Newspaper',
    blurb: 'Multi-column gazette — whole-school newsletter or print-style digest.',
    refs: ['Print', 'Newsletter'],
    recommended: false,
    Component: BlogArchiveNewspaper,
  },
] as const

const WP_CURRENT_IDS = ['wp-current-timeline', 'wp-current-archive'] as const

const NAV = [...WP_CURRENT_IDS, ...SINGLE_LAYOUTS.map((l) => l.id), ...ARCHIVE_LAYOUTS.map((l) => l.id)]

export function BlogLayoutsDemo() {
  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <header className="mb-10">
        <p className="text-xs font-bold uppercase tracking-[0.18em] text-muted-foreground">
          Blog · page layout sandbox
        </p>
        <h1 className="mt-2 text-4xl font-bold tracking-tight">Blog page layouts</h1>
        <p className="mt-3 max-w-2xl text-muted-foreground">
          Single-article and archive/index presentations for WordPress posts — informed by TT5 block
          templates, Medium/Substack reading patterns, and our 720px content measure. Not activity
          cards or timeline feeds.
        </p>
      </header>

      <section id="wp-current" className="scroll-mt-8 mb-12">
        <div className="mb-6">
          <h2 className="text-xl font-semibold tracking-tight">Current WordPress theme</h2>
          <p className="mt-1 max-w-2xl text-sm text-muted-foreground">
            Live page uses <code className="text-xs">single-timeline.php</code> with{' '}
            <code className="text-xs">.container.header-inner</code> — not the minimal{' '}
            <code className="text-xs">index.php</code> post loop. See first preview below.
          </p>
          <span className="mt-2 inline-block font-mono text-xs text-muted-foreground">#wp-current</span>
        </div>
        <div className="space-y-10">
          <div id="wp-current-timeline">
            <h3 className="mb-3 text-sm font-semibold">Timeline / blog single (live)</h3>
            <LayoutPreview label="single-timeline.php · AI Awareness Activities">
              <BlogWpTimelineSingleCurrent />
            </LayoutPreview>
          </div>
          <div id="wp-current-archive">
            <h3 className="mb-3 text-sm font-semibold">Standard posts index (fallback)</h3>
            <LayoutPreview label="index.php · post loop">
              <BlogWpArchiveCurrent />
            </LayoutPreview>
          </div>
        </div>
      </section>

      <div className="mb-10">
        <div className="rounded-lg border border-violet-300 bg-violet-50/80 px-4 py-4 text-sm text-violet-950 dark:border-violet-800 dark:bg-violet-950/50 dark:text-violet-100">
          <p className="font-semibold">Suggested v1 for WordPress</p>
          <ul className="mt-2 list-inside list-disc space-y-1 text-violet-900/90 dark:text-violet-200/90">
            <li>
              <strong>single.php</strong> →{' '}
              <a href={`#${RECOMMENDED.single}`} className="underline">
                Text blog
              </a>{' '}
              (or Editorial for long essays)
            </li>
            <li>
              <strong>home.php / index</strong> →{' '}
              <a href={`#${RECOMMENDED.archive}`} className="underline">
                Featured lead
              </a>{' '}
              (or News + sidebar if we add subscribe)
            </li>
            <li>
              Reuse shared pieces: author box, TOC from headings, related posts, 720px measure (
              <code className="text-xs">theme.json</code> contentSize).
            </li>
          </ul>
        </div>

        <div className="mt-4 flex flex-wrap gap-2 text-xs">
          {NAV.map((id) => (
            <a
              key={id}
              href={`#${id}`}
              className={`rounded-full border px-3 py-1 hover:border-foreground ${
                id.startsWith('wp-current')
                  ? 'border-amber-400 bg-amber-100 font-medium text-amber-950 dark:bg-amber-950/50 dark:text-amber-100'
                  : id === RECOMMENDED.single || id === RECOMMENDED.archive
                    ? 'border-violet-400 bg-violet-100 font-medium dark:bg-violet-900/40'
                    : 'border-border'
              }`}
            >
              {id.replace(/^wp-current-/, 'wp · ').replace(/^(single|archive)-/, '')}
            </a>
          ))}
        </div>
        <div className="mt-4 flex flex-wrap gap-4 text-sm">
          <Link to="/cards" className="text-primary hover:underline">
            Activity cards →
          </Link>
          <Link to="/feeds" className="text-primary hover:underline">
            Timeline feeds →
          </Link>
          <Link to="/" className="text-muted-foreground hover:text-foreground">
            ← All demos
          </Link>
        </div>
      </div>

      <div className="mb-8 mt-16 rounded-lg border border-border bg-muted/40 px-4 py-3 text-sm">
        <strong>Proposed layouts</strong> — options below to replace the baseline above.
      </div>

      <div className="mb-8 rounded-lg border border-border bg-muted/40 px-4 py-3 text-sm">
        <strong>Single post pages</strong> — nine layout variants; each includes the same entry features as
        the live timeline single (badge, hero, body, CTA, back + share) plus layout-specific extras.
      </div>

      <div className="space-y-16">
        {SINGLE_LAYOUTS.map(({ id, label, blurb, refs, recommended, Component }) => (
          <Section
            key={id}
            id={id}
            title={label}
            blurb={blurb}
            refs={[...refs]}
            recommended={recommended || undefined}
          >
            <LayoutPreview label={label}>
              <Component />
            </LayoutPreview>
          </Section>
        ))}
      </div>

      <div className="mt-16 mb-8 rounded-lg border border-border bg-muted/40 px-4 py-3 text-sm">
        <strong>Archive / index pages</strong> — five listing patterns; each shows badge, thumb, date,
        read time, author, and excerpt on every card.
      </div>

      <div className="space-y-16 pb-8">
        {ARCHIVE_LAYOUTS.map(({ id, label, blurb, refs, recommended, Component }) => (
          <Section
            key={id}
            id={id}
            title={label}
            blurb={blurb}
            refs={[...refs]}
            recommended={recommended || undefined}
          >
            <LayoutPreview label={label}>
              <Component />
            </LayoutPreview>
          </Section>
        ))}
      </div>

      <footer className="mt-16 border-t border-border pt-6 text-sm text-muted-foreground">
        Research notes: TT5 ships text/photo/news/vertical-header blog templates; Frost (~627★) and
        Ollie favour author-forward singles; Substack patterns map to Publication + subscribe sidebar.
        Edit{' '}
        <code className="rounded bg-muted px-1.5 py-0.5 text-xs">src/pages/BlogLayoutsDemo.tsx</code> and{' '}
        <code className="rounded bg-muted px-1.5 py-0.5 text-xs">src/components/blog/</code>
      </footer>
    </div>
  )
}
