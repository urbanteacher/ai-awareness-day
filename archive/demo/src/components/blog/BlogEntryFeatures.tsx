import { ArrowLeft, Share2 } from 'lucide-react'

import {
  DEFAULT_ENTRY_ACTIONS,
  DEFAULT_ENTRY_CTA,
  type BlogPost,
} from '@/lib/blog-demo-data'
import { BlogSectionBody, RelatedPosts } from '@/components/blog/BlogShared'

export function entryBadgeLabel(post: BlogPost) {
  return post.badge ?? post.category
}

/** Category / timeline badge — same data as live singles, layout-specific styling */
export function EntryBadge({
  post,
  className = '',
  variant = 'dark',
}: {
  post: BlogPost
  className?: string
  variant?: 'dark' | 'accent' | 'outline' | 'minimal'
}) {
  const label = entryBadgeLabel(post)
  const styles = {
    dark: 'bg-neutral-900 text-white',
    accent: 'bg-violet-600 text-white',
    outline: 'border border-border bg-background text-foreground',
    minimal: 'text-violet-600',
  }[variant]

  return (
    <span
      className={`inline-block text-[0.65rem] font-bold uppercase tracking-[0.1em] ${styles} ${
        variant === 'minimal' ? '' : 'px-2.5 py-1'
      } ${className}`}
    >
      {label}
    </span>
  )
}

/** Date pill — matches timeline single meta without dictating title layout */
export function EntryDateBadge({ post, className = '' }: { post: BlogPost; className?: string }) {
  return (
    <time
      dateTime={post.dateTime}
      className={`inline-flex shrink-0 items-center whitespace-nowrap rounded-full border border-border bg-muted/50 px-3 py-1 text-xs font-medium text-muted-foreground ${className}`}
    >
      {post.date}
    </time>
  )
}

export function EntryExcerpt({ post, className = '' }: { post: BlogPost; className?: string }) {
  if (!post.excerpt) return null
  return <p className={className}>{post.excerpt}</p>
}

type HeroFrame = 'timeline' | 'rounded' | 'plain' | 'wide' | 'compact'

/** Hero / cover — focal point supported; frame controls aspect only */
export function EntryHeroMedia({
  post,
  frame = 'rounded',
  className = '',
  hide = false,
}: {
  post: BlogPost
  frame?: HeroFrame
  className?: string
  hide?: boolean
}) {
  if (hide || !post.heroImage) return null

  const frameClass = {
    timeline: 'aspect-[3/2] overflow-hidden bg-neutral-900',
    rounded: 'aspect-[16/10] overflow-hidden rounded-xl',
    plain: 'aspect-video overflow-hidden',
    wide: 'max-h-[400px] overflow-hidden',
    compact: 'aspect-[2/1] max-h-48 overflow-hidden rounded-lg border border-border',
  }[frame]

  const imgClass =
    frame === 'wide'
      ? 'h-full max-h-[400px] w-full object-cover'
      : 'size-full object-cover'

  return (
    <figure className={`${frameClass} ${className}`}>
      <img
        src={post.heroImage}
        alt=""
        loading="lazy"
        width={1200}
        height={630}
        className={imgClass}
        style={post.heroObjectPosition ? { objectPosition: post.heroObjectPosition } : undefined}
      />
    </figure>
  )
}

export function EntryBody({
  post,
  className = '',
}: {
  post: BlogPost
  className?: string
}) {
  return (
    <div className={className}>
      <BlogSectionBody sections={post.sections} />
    </div>
  )
}

/** Primary CTA — same as single-timeline.php link block */
export function EntryCta({
  post,
  className = '',
  variant = 'solid',
}: {
  post: BlogPost
  className?: string
  variant?: 'solid' | 'outline' | 'light'
}) {
  const label = post.ctaLabel ?? DEFAULT_ENTRY_CTA.label
  const url = post.ctaUrl ?? DEFAULT_ENTRY_CTA.url

  const btnClass = {
    solid: 'bg-neutral-900 text-white hover:bg-neutral-800',
    outline: 'border-2 border-neutral-900 bg-transparent text-neutral-900 hover:bg-neutral-900 hover:text-white',
    light: 'bg-white text-neutral-900 ring-1 ring-white/20 hover:bg-white/90',
  }[variant]

  return (
    <div className={className}>
      <a
        href={url}
        className={`inline-flex items-center gap-2 px-6 py-3 text-sm font-bold uppercase tracking-wide no-underline transition-colors ${btnClass}`}
      >
        {label}
        <span aria-hidden="true">→</span>
      </a>
    </div>
  )
}

/** Back link + share — same as single-timeline-entry__footer */
export function EntryActionsFooter({
  post,
  className = '',
}: {
  post: BlogPost
  className?: string
}) {
  const backLabel = post.backLabel ?? DEFAULT_ENTRY_ACTIONS.backLabel
  const backHref = post.backHref ?? DEFAULT_ENTRY_ACTIONS.backHref

  return (
    <footer
      className={`flex flex-wrap items-center justify-between gap-4 border-t border-border pt-6 ${className}`}
    >
      <a
        href={backHref}
        className="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground no-underline hover:text-foreground"
      >
        <ArrowLeft className="size-3.5" aria-hidden />
        {backLabel}
      </a>
      <button
        type="button"
        className="inline-flex items-center gap-1.5 border-0 bg-transparent p-0 text-sm font-semibold text-muted-foreground hover:text-foreground"
        aria-label="Share this article"
      >
        <Share2 className="size-4" aria-hidden />
        Share
      </button>
    </footer>
  )
}

/** Standard end-of-article features present on live timeline singles */
export function BlogSingleEndmatter({
  post,
  ctaVariant = 'solid',
  showRelated = false,
  relatedLimit = 2,
  className = '',
}: {
  post: BlogPost
  ctaVariant?: 'solid' | 'outline' | 'light'
  showRelated?: boolean
  relatedLimit?: number
  className?: string
}) {
  return (
    <div className={`space-y-8 ${className}`}>
      <EntryCta post={post} variant={ctaVariant} />
      {showRelated ? <RelatedPosts currentSlug={post.slug} limit={relatedLimit} /> : null}
      <EntryActionsFooter post={post} />
    </div>
  )
}

/** Archive card meta — badge, date, read time, author (listing features, not layout) */
export function ArchiveEntryMeta({
  post,
  showAuthor = true,
  className = '',
}: {
  post: BlogPost
  showAuthor?: boolean
  className?: string
}) {
  return (
    <div className={`flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted-foreground ${className}`}>
      <EntryBadge post={post} variant="minimal" className="!p-0 !text-xs" />
      <span>{post.date}</span>
      <span aria-hidden>·</span>
      <span>{post.readTime}</span>
      {showAuthor ? (
        <>
          <span aria-hidden>·</span>
          <span>{post.author.name}</span>
        </>
      ) : null}
    </div>
  )
}

export function ArchiveEntryThumb({
  post,
  className = '',
  size = 'md',
}: {
  post: BlogPost
  className?: string
  size?: 'sm' | 'md' | 'lg'
}) {
  const sizeClass = { sm: 'size-24', md: 'size-28', lg: 'aspect-[4/3] w-full' }[size]
  return (
    <img
      src={post.heroImage}
      alt=""
      className={`shrink-0 object-cover ${sizeClass} ${size === 'lg' ? 'rounded-xl' : 'rounded-md'} ${className}`}
      width={size === 'lg' ? 800 : 112}
      height={size === 'lg' ? 600 : 112}
      style={post.heroObjectPosition ? { objectPosition: post.heroObjectPosition } : undefined}
    />
  )
}
