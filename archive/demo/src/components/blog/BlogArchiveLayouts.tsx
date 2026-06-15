import { ARCHIVE_POSTS, PUBLICATION } from '@/lib/blog-demo-data'
import type { BlogPost } from '@/lib/blog-demo-data'
import { ArchiveEntryMeta, ArchiveEntryThumb, EntryBadge } from '@/components/blog/BlogEntryFeatures'
import { PublicationMasthead, SubscribeStrip } from '@/components/blog/BlogShared'

type ArchiveProps = { posts?: BlogPost[] }

/** Index — simple stacked list (matches basic WP index.php) */
export function BlogArchiveList({ posts = ARCHIVE_POSTS }: ArchiveProps) {
  return (
    <div className="bg-background px-6 py-10">
      <header className="mx-auto max-w-2xl border-b border-border pb-8">
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-muted-foreground">Blog</p>
        <h1 className="mt-2 text-3xl font-bold tracking-tight">News &amp; practice notes</h1>
        <p className="mt-2 text-muted-foreground">Updates from schools, families, and the AIAD team.</p>
      </header>
      <ul className="mx-auto mt-8 max-w-2xl divide-y divide-border">
        {posts.map((item) => (
          <li key={item.id} className="py-8 first:pt-0">
            <article className="flex flex-col gap-4 sm:flex-row sm:gap-6">
              <ArchiveEntryThumb post={item} size="sm" className="hidden sm:block" />
              <div className="min-w-0 flex-1">
                <ArchiveEntryMeta post={item} />
                <h2 className="mt-2 text-xl font-semibold leading-snug text-foreground hover:text-primary">
                  <a href={`#${item.slug}`}>{item.title}</a>
                </h2>
                <p className="mt-3 text-foreground/80">{item.excerpt}</p>
              </div>
            </article>
          </li>
        ))}
      </ul>
    </div>
  )
}

/** Index — featured lead story + compact list */
export function BlogArchiveFeaturedLead({ posts = ARCHIVE_POSTS }: ArchiveProps) {
  const [lead, ...rest] = posts
  return (
    <div className="bg-background px-6 py-10">
      <div className="mx-auto max-w-4xl">
        <header className="mb-10">
          <h1 className="text-3xl font-bold tracking-tight">From the blog</h1>
        </header>
        {lead ? (
          <article className="grid gap-6 border-b border-border pb-10 md:grid-cols-2 md:gap-10">
            <ArchiveEntryThumb post={lead} size="lg" />
            <div className="flex flex-col justify-center">
              <EntryBadge post={lead} variant="minimal" />
              <h2 className="mt-2 text-2xl font-bold leading-tight sm:text-3xl">
                <a href={`#${lead.slug}`}>{lead.title}</a>
              </h2>
              <p className="mt-3 text-muted-foreground">{lead.excerpt}</p>
              <ArchiveEntryMeta post={lead} className="mt-4" />
            </div>
          </article>
        ) : null}
        <ul className="mt-8 space-y-6">
          {rest.map((item) => (
            <li key={item.id} className="flex gap-4 border-b border-border pb-6 last:border-0">
              <ArchiveEntryThumb post={item} size="md" className="hidden sm:block" />
              <div className="min-w-0 flex-1">
                <ArchiveEntryMeta post={item} />
                <h3 className="mt-1 font-semibold leading-snug text-foreground">
                  <a href={`#${item.slug}`}>{item.title}</a>
                </h3>
                <p className="mt-2 line-clamp-2 text-sm text-muted-foreground">{item.excerpt}</p>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  )
}

/** Index — newspaper columns */
export function BlogArchiveNewspaper({ posts = ARCHIVE_POSTS }: ArchiveProps) {
  return (
    <div className="bg-[#f4f1ea] px-6 py-10 font-serif text-stone-900">
      <div className="mx-auto max-w-5xl border-4 border-double border-stone-800 p-6 sm:p-10">
        <header className="border-b-2 border-stone-800 pb-4 text-center">
          <p className="text-xs uppercase tracking-[0.35em]">AI Awareness Day</p>
          <h1 className="mt-2 text-4xl font-bold">The Literacy Gazette</h1>
          <p className="mt-1 text-sm italic text-stone-600">{posts[0]?.date ?? ''} · Schools edition</p>
        </header>
        <div className="mt-8 columns-1 gap-8 md:columns-2 lg:columns-3">
          {posts.map((item, i) => (
            <article key={item.id} className={`mb-8 break-inside-avoid ${i === 0 ? 'md:column-span-all' : ''}`}>
              {i === 0 ? (
                <>
                  <EntryBadge post={item} variant="outline" className="mb-2 !border-stone-800" />
                  <h2 className="text-2xl font-bold leading-tight">
                    <a href={`#${item.slug}`}>{item.title}</a>
                  </h2>
                  <p className="mt-2 text-sm text-stone-600">{item.excerpt}</p>
                  <p className="mt-2 text-xs text-stone-500">
                    {item.date} · {item.readTime}
                  </p>
                  <hr className="my-4 border-stone-400" />
                </>
              ) : (
                <>
                  <h3 className="text-lg font-bold leading-snug">
                    <a href={`#${item.slug}`}>{item.title}</a>
                  </h3>
                  <p className="mt-1 text-xs text-stone-500">{item.category}</p>
                  <p className="mt-2 text-sm leading-relaxed">{item.excerpt}</p>
                  <p className="mt-1 text-xs text-stone-400">{item.date}</p>
                </>
              )}
            </article>
          ))}
        </div>
      </div>
    </div>
  )
}

/** TT5 photo-blog — image-forward masonry grid */
export function BlogArchivePhotoGrid({ posts = ARCHIVE_POSTS }: ArchiveProps) {
  return (
    <div className="bg-stone-100 px-4 py-10 sm:px-6">
      <header className="mx-auto mb-8 max-w-6xl">
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-stone-500">Photo blog</p>
        <h1 className="mt-2 text-3xl font-bold tracking-tight text-stone-900">Stories in pictures</h1>
      </header>
      <ul className="mx-auto grid max-w-6xl gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {posts.map((item, i) => (
          <li
            key={item.id}
            className={`overflow-hidden rounded-xl bg-white shadow-sm ${i === 0 ? 'sm:col-span-2 sm:row-span-2' : ''}`}
          >
            <a href={`#${item.slug}`} className="group block">
              <figure className="relative overflow-hidden">
                <img
                  src={item.heroImage}
                  alt=""
                  className={`w-full object-cover transition duration-300 group-hover:scale-[1.02] ${
                    i === 0 ? 'aspect-[16/10]' : 'aspect-[4/3]'
                  }`}
                  style={item.heroObjectPosition ? { objectPosition: item.heroObjectPosition } : undefined}
                />
                <figcaption className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-4 pt-12">
                  <p className="text-xs font-medium uppercase tracking-wider text-white/80">
                    {item.badge ?? item.category}
                  </p>
                  <h2
                    className={`mt-1 font-bold leading-snug text-white ${i === 0 ? 'text-xl sm:text-2xl' : 'text-base'}`}
                  >
                    {item.title}
                  </h2>
                  {i === 0 ? (
                    <p className="mt-2 line-clamp-2 text-sm text-white/85">{item.excerpt}</p>
                  ) : null}
                  <p className="mt-2 text-xs text-white/70">{item.date}</p>
                </figcaption>
              </figure>
            </a>
          </li>
        ))}
      </ul>
    </div>
  )
}

/** TT5 news-blog — main feed + sticky sidebar */
export function BlogArchiveNewsSidebar({ posts = ARCHIVE_POSTS }: ArchiveProps) {
  const [lead, ...rest] = posts
  const categories = [...new Set(posts.map((p) => p.badge ?? p.category))]

  return (
    <div className="bg-background px-4 py-10 sm:px-8">
      <PublicationMasthead compact />
      <div className="mx-auto mt-10 grid max-w-6xl gap-10 lg:grid-cols-[1fr_280px]">
        <div>
          {lead ? (
            <article className="border-b border-border pb-10">
              <ArchiveEntryThumb post={lead} size="lg" className="!aspect-[2/1] !max-h-none w-full" />
              <EntryBadge post={lead} variant="minimal" className="mt-4" />
              <h2 className="mt-2 text-2xl font-bold leading-tight sm:text-3xl">
                <a href={`#${lead.slug}`}>{lead.title}</a>
              </h2>
              <p className="mt-3 text-muted-foreground">{lead.excerpt}</p>
              <ArchiveEntryMeta post={lead} className="mt-3" />
            </article>
          ) : null}
          <ul className="mt-8 divide-y divide-border">
            {rest.map((item) => (
              <li key={item.id} className="flex gap-5 py-8 first:pt-0">
                <ArchiveEntryThumb post={item} size="md" className="hidden sm:block" />
                <div className="min-w-0 flex-1">
                  <ArchiveEntryMeta post={item} />
                  <h3 className="mt-1 text-lg font-semibold leading-snug">
                    <a href={`#${item.slug}`}>{item.title}</a>
                  </h3>
                  <p className="mt-2 line-clamp-2 text-sm text-muted-foreground">{item.excerpt}</p>
                </div>
              </li>
            ))}
          </ul>
        </div>
        <aside className="space-y-8 lg:sticky lg:top-6 lg:self-start">
          <SubscribeStrip />
          <div>
            <p className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Topics</p>
            <ul className="mt-3 flex flex-wrap gap-2">
              {categories.map((cat) => (
                <li key={cat}>
                  <span className="rounded-full border border-border px-3 py-1 text-xs font-medium">{cat}</span>
                </li>
              ))}
            </ul>
          </div>
          <div>
            <p className="text-xs font-bold uppercase tracking-wider text-muted-foreground">About</p>
            <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{PUBLICATION.tagline}</p>
          </div>
        </aside>
      </div>
    </div>
  )
}
