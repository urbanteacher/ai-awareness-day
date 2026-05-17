import { ARCHIVE_POSTS, FEATURED_POST } from '@/lib/blog-demo-data'
import type { BlogPost } from '@/lib/blog-demo-data'
import {
  ARTICLE_MEASURE,
  AuthorBox,
  AuthorChip,
  BlogSectionBody,
  EngagementRow,
  PostMetaRow,
  PostNavigation,
  PublicationMasthead,
  SubscribeStrip,
  TagList,
  TocNav,
} from '@/components/blog/BlogShared'
import {
  BlogSingleEndmatter,
  EntryBadge,
  EntryBody,
  EntryDateBadge,
  EntryExcerpt,
  EntryHeroMedia,
} from '@/components/blog/BlogEntryFeatures'

type SingleProps = { post?: BlogPost }

const post = FEATURED_POST

/** TT5 text-blog · Medium · Björk (720px reading measure) */
export function BlogSingleTextBlog({ post: p = post }: SingleProps) {
  return (
    <article className="bg-background px-4 py-10 sm:px-8">
      <div className={`mx-auto ${ARTICLE_MEASURE}`}>
        <EntryBadge post={p} variant="minimal" className="!text-xs tracking-[0.2em]" />
        <h1 className="mt-3 text-[2.25rem] font-bold leading-[1.15] tracking-tight sm:text-[2.75rem]">
          {p.title}
        </h1>
        <EntryExcerpt post={p} className="mt-4 text-lg leading-relaxed text-muted-foreground" />
        <div className="mt-6 flex flex-wrap items-center justify-between gap-4">
          <AuthorChip post={p} />
          <EntryDateBadge post={p} />
        </div>
        <PostMetaRow post={p} />
        <EngagementRow />
        <EntryHeroMedia post={p} frame="rounded" className="mt-8" />
        <EntryBody post={p} className="mt-10" />
        <AuthorBox post={p} />
        <TagList tags={p.tags} />
        <PostNavigation currentSlug={p.slug} />
        <BlogSingleEndmatter post={p} showRelated className="mt-10" />
      </div>
    </article>
  )
}

/** Substack · TT5 news-blog — publication chrome + subscribe */
const PUBLICATION_DROP_CAP =
  'mt-8 [&_.blog-prose__lead]:first-letter:float-left [&_.blog-prose__lead]:first-letter:mr-2 [&_.blog-prose__lead]:first-letter:font-serif [&_.blog-prose__lead]:first-letter:text-5xl [&_.blog-prose__lead]:first-letter:leading-none [&_.blog-prose__lead]:first-letter:text-stone-800'

export function BlogPublicationArticle({ post: p }: { post: BlogPost }) {
  return (
    <article className="bg-[#fffdf8] px-4 py-8 sm:px-8">
      <div className={`mx-auto ${ARTICLE_MEASURE}`}>
        <PublicationMasthead />
        <EntryBadge post={p} variant="outline" className="mt-8" />
        <h1 className="mt-4 font-serif text-[2.5rem] font-medium leading-[1.12] tracking-tight text-stone-900">
          {p.title}
        </h1>
        <div className="mt-4 flex flex-wrap items-center gap-3 text-sm text-stone-600">
          <AuthorChip post={p} />
          <EntryDateBadge post={p} />
        </div>
        <EntryHeroMedia post={p} frame="rounded" className="mt-8" />
        <EntryExcerpt post={p} className="mt-6 text-stone-600" />
        <div className={PUBLICATION_DROP_CAP}>
          <EntryBody post={p} />
        </div>
        <SubscribeStrip className="my-10" />
        <AuthorBox post={p} />
        <BlogSingleEndmatter post={p} showRelated relatedLimit={2} />
      </div>
    </article>
  )
}

/** Substack · TT5 news-blog — publication chrome + subscribe */
export function BlogSinglePublication({ post: p = post }: SingleProps) {
  return <BlogPublicationArticle post={p} />
}

/** TT5 vertical-header · Notion · Björk sidebar */
export function BlogSingleVertical({ post: p = post }: SingleProps) {
  return (
    <article className="flex min-h-[520px] bg-background">
      <aside className="hidden w-56 shrink-0 border-r border-border bg-muted/20 p-5 lg:block">
        <p className="text-sm font-bold text-foreground">AI Awareness Day</p>
        <p className="mt-1 text-xs text-muted-foreground">Blog</p>
        <nav className="mt-8 space-y-1 text-sm" aria-label="Recent posts">
          {ARCHIVE_POSTS.map((item) => (
            <a
              key={item.id}
              href={`#${item.slug}`}
              className={`block rounded-md px-2 py-1.5 ${
                item.slug === p.slug
                  ? 'bg-foreground font-medium text-background'
                  : 'text-muted-foreground hover:bg-muted'
              }`}
            >
              <span className="line-clamp-2">{item.title}</span>
            </a>
          ))}
        </nav>
        <div className="mt-8 border-t border-border pt-6">
          <TocNav sections={p.sections} />
        </div>
      </aside>
      <div className="min-w-0 flex-1 px-6 py-8 sm:px-10">
        <EntryBadge post={p} variant="minimal" />
        <div className="mt-3 flex flex-wrap items-start justify-between gap-3">
          <h1 className="text-3xl font-bold tracking-tight">{p.title}</h1>
          <EntryDateBadge post={p} />
        </div>
        <EntryExcerpt post={p} className="mt-3 text-muted-foreground" />
        <PostMetaRow post={p} compact />
        <EntryHeroMedia post={p} frame="plain" className="mt-6" />
        <EntryBody post={p} className="mt-8 max-w-prose" />
        <BlogSingleEndmatter post={p} className="mt-10" />
      </div>
    </article>
  )
}

/** Classic — enhanced WP default */
export function BlogSingleClassic({ post: p = post }: SingleProps) {
  return (
    <article className="bg-background px-4 py-10 sm:px-8">
      <div className={`mx-auto ${ARTICLE_MEASURE}`}>
        <EntryBadge post={p} variant="dark" />
        <PostMetaRow post={p} />
        <h1 className="mt-4 font-serif text-3xl font-bold leading-tight tracking-tight sm:text-4xl">{p.title}</h1>
        <EntryExcerpt post={p} className="mt-3 text-muted-foreground" />
        <div className="mt-6">
          <AuthorChip post={p} />
        </div>
        <EntryHeroMedia post={p} frame="rounded" className="mt-8" />
        <EntryBody post={p} className="mt-8" />
        <TagList tags={p.tags} />
        <BlogSingleEndmatter post={p} showRelated relatedLimit={2} className="mt-8" />
      </div>
    </article>
  )
}

/** Editorial — Medium long-read + drop cap */
export function BlogSingleEditorial({ post: p = post }: SingleProps) {
  return (
    <article className="bg-[#faf9f7] px-4 py-10 sm:px-8">
      <div className="mx-auto grid max-w-5xl gap-10 lg:grid-cols-[1fr_220px]">
        <div>
          <EntryBadge post={p} variant="minimal" className="!text-violet-600" />
          <h1 className="mt-3 font-serif text-4xl font-medium leading-[1.1] text-stone-900 sm:text-5xl">
            {p.title}
          </h1>
          <PostMetaRow post={p} />
          <EntryExcerpt post={p} className="mt-2 text-stone-600" />
          <EntryHeroMedia post={p} frame="rounded" className="mt-8 shadow-md [&_img]:rounded-sm" />
          <div className="mt-10 [&_.blog-prose__lead]:first-letter:float-left [&_.blog-prose__lead]:first-letter:mr-3 [&_.blog-prose__lead]:first-letter:text-6xl [&_.blog-prose__lead]:first-letter:font-serif [&_.blog-prose__lead]:first-letter:leading-none [&_.blog-prose__lead]:first-letter:text-violet-700">
            <EntryBody post={p} />
          </div>
          <AuthorBox post={p} />
          <BlogSingleEndmatter post={p} className="mt-8" />
        </div>
        <aside className="hidden lg:block">
          <div className="sticky top-6 space-y-6 border-l border-stone-200 pl-6">
            <AuthorChip post={p} />
            <EntryDateBadge post={p} />
            <TocNav sections={p.sections} />
            <TagList tags={p.tags} />
          </div>
        </aside>
      </div>
    </article>
  )
}

/** Magazine — Substack feature hero */
export function BlogSingleMagazine({ post: p = post }: SingleProps) {
  return (
    <article className="bg-stone-950 text-stone-100">
      <header className="relative min-h-[300px] sm:min-h-[380px]">
        <img
          src={p.heroImage}
          alt=""
          className="absolute inset-0 size-full object-cover opacity-70"
          style={p.heroObjectPosition ? { objectPosition: p.heroObjectPosition } : undefined}
        />
        <div className="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/60 to-transparent" />
        <div className="relative mx-auto flex max-w-4xl flex-col justify-end px-6 pb-10 pt-28">
          <EntryBadge post={p} variant="accent" className="!bg-lime-400 !text-stone-950" />
          <h1 className="mt-4 max-w-3xl text-3xl font-bold leading-tight sm:text-5xl">{p.title}</h1>
          <div className="mt-4 flex flex-wrap items-center gap-4 text-sm text-stone-300">
            <EntryDateBadge post={p} className="!border-stone-600 !bg-stone-800/80 !text-stone-200" />
            <span>{p.readTime}</span>
            <span>{p.author.name}</span>
          </div>
        </div>
      </header>
      <div className={`mx-auto px-6 py-12 ${ARTICLE_MEASURE}`}>
        <EntryExcerpt post={p} className="text-stone-300" />
        <div className="mt-8 text-stone-200 [&_.blog-prose__h2]:text-white [&_.blog-prose__lead]:text-stone-100">
          <EntryBody post={p} />
        </div>
        <BlogSingleEndmatter post={p} ctaVariant="light" showRelated className="mt-10" />
      </div>
    </article>
  )
}

/** Split meta — Frost author pattern */
export function BlogSingleSplit({ post: p = post }: SingleProps) {
  return (
    <article className="bg-background px-4 py-10 sm:px-8">
      <div className="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[240px_1fr] lg:gap-12">
        <aside className="lg:sticky lg:top-6 lg:self-start">
          <EntryBadge post={p} variant="minimal" className="!text-primary" />
          <PostMetaRow post={p} compact />
          <div className="mt-6 border-t border-border pt-6">
            <AuthorChip post={p} />
          </div>
          <div className="mt-6">
            <TagList tags={p.tags} />
          </div>
        </aside>
        <div>
          <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">{p.title}</h1>
          <EntryExcerpt post={p} className="mt-2 text-muted-foreground" />
          <EntryHeroMedia post={p} frame="plain" className="mt-6 border border-border [&_figure]:rounded-lg" />
          <EntryBody post={p} className="mt-8" />
          <PostNavigation currentSlug={p.slug} />
          <BlogSingleEndmatter post={p} className="mt-8" />
        </div>
      </div>
    </article>
  )
}

/** Docs / Notion — technical guide */
export function BlogSingleDocs({ post: p = post }: SingleProps) {
  return (
    <article className="flex min-h-[480px] bg-white text-stone-800">
      <nav className="hidden w-52 shrink-0 border-r border-stone-200 bg-stone-50 p-4 md:block">
        <TocNav sections={p.sections} />
      </nav>
      <div className="flex-1 px-6 py-8 sm:px-10">
        <EntryBadge post={p} variant="outline" />
        <p className="mt-3 font-mono text-xs text-stone-500">
          {p.date} · {p.readTime}
        </p>
        <h1 className="mt-2 text-2xl font-semibold">{p.title}</h1>
        <EntryExcerpt post={p} className="mt-4 text-sm text-stone-600" />
        <EntryHeroMedia post={p} frame="compact" className="mt-6" />
        <hr className="my-8 border-stone-200" />
        <EntryBody post={p} className="max-w-prose text-[15px] leading-7" />
        <BlogSingleEndmatter post={p} className="mt-10" />
      </div>
    </article>
  )
}

/** Wide — photo-led magazine body */
export function BlogSingleWide({ post: p = post }: SingleProps) {
  return (
    <article className="bg-background">
      <div className="border-b border-border bg-muted/30 px-6 py-12">
        <div className="mx-auto max-w-5xl">
          <EntryBadge post={p} variant="dark" />
          <PostMetaRow post={p} />
          <h1 className="mt-3 max-w-4xl text-4xl font-bold tracking-tight sm:text-5xl">{p.title}</h1>
          <EntryExcerpt post={p} className="mt-4 max-w-2xl text-lg text-muted-foreground" />
        </div>
      </div>
      <EntryHeroMedia post={p} frame="wide" />
      <div className="mx-auto grid max-w-5xl gap-10 px-6 py-12 lg:grid-cols-2">
        <BlogSectionBody sections={p.sections.slice(0, 3)} />
        <BlogSectionBody sections={p.sections.slice(3)} />
      </div>
      <div className={`mx-auto border-t border-border px-6 py-8 ${ARTICLE_MEASURE}`}>
        <AuthorChip post={p} />
        <BlogSingleEndmatter post={p} className="mt-8" />
      </div>
    </article>
  )
}
