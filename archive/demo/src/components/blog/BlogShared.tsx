import type { ReactNode } from 'react'
import { ArrowLeft, ArrowRight, Clock, Mail, Share2, Tag } from 'lucide-react'

import {
  ARCHIVE_POSTS,
  ARTICLE_MEASURE,
  PUBLICATION,
  slugifyHeading,
  type BlogPost,
  type BlogSection,
} from '@/lib/blog-demo-data'

type SectionBodyVariant = 'default' | 'timeline'

function renderSectionBlock(block: BlogSection, i: number, variant: SectionBodyVariant) {
  const isTimeline = variant === 'timeline'

  if (block.type === 'p') {
    return (
      <p key={i} className={!isTimeline && i === 0 ? 'blog-prose__lead text-[1.125rem] text-foreground' : undefined}>
        {block.text}
      </p>
    )
  }
  if (block.type === 'h2') {
    return (
      <h2
        key={i}
        id={slugifyHeading(block.text)}
        className={
          isTimeline
            ? 'scroll-mt-4 pt-6 text-[1.35rem] font-bold text-[var(--gray-900,#1e1e1e)]'
            : 'blog-prose__h2 scroll-mt-4 pt-6 text-[1.75rem] font-bold tracking-tight text-foreground'
        }
      >
        {block.text}
      </h2>
    )
  }
  if (block.type === 'pullquote') {
    if (isTimeline) return null
    return (
      <blockquote
        key={i}
        className="blog-prose__quote my-8 border-l-4 border-violet-500 bg-violet-50/80 px-6 py-4 text-xl font-medium italic text-foreground"
      >
        <p>&ldquo;{block.text}&rdquo;</p>
        {block.cite ? (
          <footer className="mt-3 text-sm font-normal not-italic text-muted-foreground">
            — {block.cite}
          </footer>
        ) : null}
      </blockquote>
    )
  }
  if (block.type === 'callout') {
    if (isTimeline) return null
    const tone =
      block.variant === 'tip'
        ? 'border-amber-300 bg-amber-50 text-amber-950'
        : 'border-sky-300 bg-sky-50 text-sky-950'
    return (
      <aside
        key={i}
        className={`rounded-lg border-l-4 px-4 py-3 text-[0.95rem] leading-relaxed ${tone}`}
      >
        {block.text}
      </aside>
    )
  }
  if (block.type === 'list') {
    return (
      <ul
        key={i}
        className={isTimeline ? 'list-disc space-y-1 pl-6' : 'list-disc space-y-2 pl-6 marker:text-violet-500'}
      >
        {block.items.map((item) => (
          <li key={item}>{item}</li>
        ))}
      </ul>
    )
  }
  if (block.type === 'link') {
    return (
      <p key={i}>
        <a href={block.href} className={isTimeline ? 'entry-content__link' : 'text-primary underline'}>
          {block.label}
        </a>
      </p>
    )
  }
  if (block.type === 'tags') {
    return (
      <p key={i} className={isTimeline ? 'entry-content__tags' : 'text-sm text-muted-foreground'}>
        {block.items.join(' ')}
      </p>
    )
  }
  return null
}

export function timelineTagItems(sections: BlogSection[]): string[] {
  const block = sections.find((s): s is Extract<BlogSection, { type: 'tags' }> => s.type === 'tags')
  return block?.items ?? []
}

/** Hashtags block — rendered at end of timeline singles, outside main body */
export function EntryTimelineTags({
  items,
  className = '',
}: {
  items: string[]
  className?: string
}) {
  if (items.length === 0) return null
  return <p className={`entry-content__tags single-timeline-entry__tags ${className}`.trim()}>{items.join(' ')}</p>
}

export function BlogSectionBody({
  sections,
  variant = 'default',
  omitTags = false,
}: {
  sections: BlogSection[]
  variant?: SectionBodyVariant
  /** When true, tags blocks are skipped (render via EntryTimelineTags at article end) */
  omitTags?: boolean
}) {
  const blocks = omitTags ? sections.filter((s) => s.type !== 'tags') : sections

  if (variant === 'timeline') {
    return <>{blocks.map((block, i) => renderSectionBlock(block, i, variant))}</>
  }

  return (
    <div className="blog-prose space-y-5 text-[1.0625rem] leading-[1.65] text-foreground/90">
      {blocks.map((block, i) => renderSectionBlock(block, i, variant))}
    </div>
  )
}

export function LayoutRefs({ refs }: { refs: string[] }) {
  return (
    <div className="flex flex-wrap gap-1.5">
      {refs.map((ref) => (
        <span
          key={ref}
          className="rounded-md border border-border bg-muted/60 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground"
        >
          {ref}
        </span>
      ))}
    </div>
  )
}

export function PublicationMasthead({ compact }: { compact?: boolean }) {
  return (
    <div
      className={`flex flex-wrap items-center justify-between gap-4 border-b border-border ${compact ? 'pb-4' : 'pb-6'}`}
    >
      <div>
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-violet-600">
          {PUBLICATION.name}
        </p>
        {!compact ? (
          <p className="mt-1 text-sm text-muted-foreground">{PUBLICATION.tagline}</p>
        ) : null}
      </div>
      <button
        type="button"
        className="inline-flex items-center gap-2 rounded-full bg-foreground px-4 py-2 text-sm font-semibold text-background hover:opacity-90"
      >
        <Mail className="size-4" aria-hidden />
        Subscribe
      </button>
    </div>
  )
}

export function SubscribeStrip({ className = '' }: { className?: string }) {
  return (
    <div
      className={`rounded-xl border border-border bg-muted/40 px-5 py-6 text-center ${className}`}
    >
      <p className="text-sm font-semibold text-foreground">{PUBLICATION.subscribeLabel}</p>
      <p className="mt-1 text-xs text-muted-foreground">Free · schools &amp; educators · unsubscribe anytime</p>
      <div className="mx-auto mt-4 flex max-w-sm gap-2">
        <input
          type="email"
          readOnly
          placeholder="you@school.org.uk"
          className="min-w-0 flex-1 rounded-lg border border-border bg-background px-3 py-2 text-sm"
        />
        <button
          type="button"
          className="shrink-0 rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white"
        >
          Join
        </button>
      </div>
    </div>
  )
}

export function PostMetaRow({ post, compact }: { post: BlogPost; compact?: boolean }) {
  return (
    <div
      className={`flex flex-wrap items-center gap-x-4 gap-y-2 ${compact ? 'text-xs' : 'text-sm'} text-muted-foreground`}
    >
      <time dateTime="2026-05-12">{post.date}</time>
      <span className="flex items-center gap-1">
        <Clock className="size-3.5" aria-hidden />
        {post.readTime}
      </span>
      <span className="rounded-full bg-muted px-2.5 py-0.5 font-medium text-foreground">{post.category}</span>
    </div>
  )
}

export function AuthorChip({ post }: { post: BlogPost }) {
  return (
    <div className="flex items-center gap-3">
      <img
        src={post.author.avatar}
        alt=""
        className="size-11 rounded-full object-cover ring-2 ring-background"
        width={44}
        height={44}
      />
      <div>
        <p className="text-sm font-semibold text-foreground">{post.author.name}</p>
        <p className="text-xs text-muted-foreground">{post.author.role}</p>
      </div>
    </div>
  )
}

export function AuthorBox({ post }: { post: BlogPost }) {
  return (
    <aside className="mt-10 flex gap-4 rounded-xl border border-border bg-muted/30 p-5">
      <img
        src={post.author.avatar}
        alt=""
        className="size-16 shrink-0 rounded-full object-cover"
        width={64}
        height={64}
      />
      <div>
        <p className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Written by</p>
        <p className="mt-1 font-semibold text-foreground">{post.author.name}</p>
        <p className="text-sm text-muted-foreground">{post.author.role}</p>
        <p className="mt-2 text-sm leading-relaxed text-foreground/80">
          Leads AI literacy resources for Urban Teacher and contributes practice notes to AI Awareness Day.
        </p>
      </div>
    </aside>
  )
}

export function TagList({ tags }: { tags: string[] }) {
  return (
    <ul className="flex flex-wrap gap-2">
      {tags.map((tag) => (
        <li
          key={tag}
          className="inline-flex items-center gap-1 rounded-md border border-border bg-muted/50 px-2 py-1 text-xs text-muted-foreground"
        >
          <Tag className="size-3" aria-hidden />
          {tag}
        </li>
      ))}
    </ul>
  )
}

export function PostNavigation({ currentSlug }: { currentSlug: string }) {
  const idx = ARCHIVE_POSTS.findIndex((p) => p.slug === currentSlug)
  const prev = idx > 0 ? ARCHIVE_POSTS[idx - 1] : null
  const next = idx >= 0 && idx < ARCHIVE_POSTS.length - 1 ? ARCHIVE_POSTS[idx + 1] : null

  return (
    <nav className="mt-10 grid gap-4 border-t border-border pt-8 sm:grid-cols-2" aria-label="Post navigation">
      {prev ? (
        <a href={`#${prev.slug}`} className="group rounded-lg border border-border p-4 hover:bg-muted/40">
          <span className="flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-muted-foreground">
            <ArrowLeft className="size-3.5" aria-hidden />
            Previous
          </span>
          <span className="mt-2 block text-sm font-semibold text-foreground group-hover:text-primary">
            {prev.title}
          </span>
        </a>
      ) : (
        <span />
      )}
      {next ? (
        <a
          href={`#${next.slug}`}
          className="group rounded-lg border border-border p-4 text-right hover:bg-muted/40 sm:col-start-2"
        >
          <span className="flex items-center justify-end gap-1 text-xs font-medium uppercase tracking-wider text-muted-foreground">
            Next
            <ArrowRight className="size-3.5" aria-hidden />
          </span>
          <span className="mt-2 block text-sm font-semibold text-foreground group-hover:text-primary">
            {next.title}
          </span>
        </a>
      ) : null}
    </nav>
  )
}

export function RelatedPosts({
  currentSlug,
  limit = 3,
  variant = 'default',
}: {
  currentSlug: string
  limit?: number
  variant?: 'default' | 'timeline'
}) {
  const related = ARCHIVE_POSTS.filter((p) => p.slug !== currentSlug).slice(0, limit)
  if (related.length === 0) return null

  const isTimeline = variant === 'timeline'

  return (
    <section
      className={
        isTimeline
          ? 'single-timeline-entry__related'
          : 'mt-12 border-t border-border pt-10'
      }
      aria-labelledby={isTimeline ? 'related-heading-timeline' : 'related-heading'}
    >
      <h2
        id={isTimeline ? 'related-heading-timeline' : 'related-heading'}
        className={
          isTimeline
            ? 'single-timeline-entry__related-title'
            : 'text-sm font-bold uppercase tracking-wider text-muted-foreground'
        }
      >
        More to read
      </h2>
      <ul className={isTimeline ? 'single-timeline-entry__related-list' : 'mt-4 space-y-4'}>
        {related.map((item) => (
          <li key={item.id}>
            <a
              href={`#${item.slug}`}
              className={isTimeline ? 'single-timeline-entry__related-link' : 'group flex gap-4'}
            >
              <img
                src={item.heroImage}
                alt=""
                className={
                  isTimeline
                    ? 'single-timeline-entry__related-thumb'
                    : 'size-20 shrink-0 rounded-md object-cover'
                }
                width={80}
                height={80}
              />
              <div className={isTimeline ? 'single-timeline-entry__related-meta' : 'min-w-0'}>
                <p
                  className={
                    isTimeline
                      ? 'single-timeline-entry__related-date'
                      : 'text-xs text-muted-foreground'
                  }
                >
                  {item.date} · {item.badge ?? item.category}
                </p>
                <p
                  className={
                    isTimeline
                      ? 'single-timeline-entry__related-headline'
                      : 'mt-1 font-medium leading-snug text-foreground group-hover:text-primary'
                  }
                >
                  {item.title}
                </p>
              </div>
            </a>
          </li>
        ))}
      </ul>
    </section>
  )
}

export function EngagementRow() {
  return (
    <div className="flex flex-wrap items-center gap-4 border-y border-border py-4 text-sm text-muted-foreground">
      <button type="button" className="inline-flex items-center gap-1.5 hover:text-foreground">
        <span aria-hidden>♥</span>
        <span>128</span>
      </button>
      <button type="button" className="hover:text-foreground">
        12 responses
      </button>
      <button type="button" className="ml-auto inline-flex items-center gap-1.5 hover:text-foreground">
        <Share2 className="size-4" aria-hidden />
        Share
      </button>
    </div>
  )
}

export function ArticleFooter({ backLabel = 'Back to blog' }: { backLabel?: string }) {
  return (
    <footer className="mt-8 flex flex-wrap items-center justify-between gap-4">
      <button
        type="button"
        className="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground"
      >
        <ArrowLeft className="size-4" aria-hidden />
        {backLabel}
      </button>
      <button
        type="button"
        className="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium shadow-sm hover:bg-muted/50"
      >
        <Share2 className="size-4" aria-hidden />
        Share article
      </button>
    </footer>
  )
}

export function TocNav({ sections }: { sections: BlogSection[] }) {
  const items = sections
    .filter((s): s is { type: 'h2'; text: string } => s.type === 'h2')
    .map((s) => ({ id: slugifyHeading(s.text), label: s.text }))

  if (items.length === 0) return null

  return (
    <nav aria-label="Table of contents">
      <p className="text-xs font-bold uppercase tracking-wider text-muted-foreground">On this page</p>
      <ul className="mt-3 space-y-2 text-sm">
        {items.map((item) => (
          <li key={item.id}>
            <a href={`#${item.id}`} className="text-muted-foreground hover:text-foreground">
              {item.label}
            </a>
          </li>
        ))}
      </ul>
    </nav>
  )
}

export { ARTICLE_MEASURE }

export function LayoutPreview({
  label,
  children,
  className = '',
}: {
  label: string
  children: ReactNode
  className?: string
}) {
  return (
    <div className={`overflow-hidden rounded-xl border border-border bg-background shadow-sm ${className}`}>
      <div className="flex items-center justify-between border-b border-border bg-muted/40 px-4 py-2">
        <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
        <span className="font-mono text-[10px] text-muted-foreground">preview</span>
      </div>
      <div className="max-h-[min(780px,85vh)] overflow-y-auto">{children}</div>
    </div>
  )
}
