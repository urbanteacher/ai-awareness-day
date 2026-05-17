import { ARCHIVE_POSTS, CURRENT_WP_TIMELINE_ENTRY } from '@/lib/blog-demo-data'
import {
  BlogSectionBody,
  EntryTimelineTags,
  RelatedPosts,
  timelineTagItems,
} from '@/components/blog/BlogShared'

import '@/styles/wp-current-preview.css'

const NAV_ITEMS = [
  { label: 'Campaign', href: '#' },
  { label: 'Reach', href: '#' },
  { label: 'Aim', href: '#' },
  { label: 'Resources', href: '#' },
  { label: 'AI Tools', href: '#' },
] as const

function WpSiteHeader() {
  return (
    <header className="site-header" id="site-header">
      <div className="container header-inner">
        <a href="/" className="site-logo" aria-label="AI Awareness Day">
          <span className="site-logo__text">
            AI Awareness Day<span className="site-logo__dot">.</span>
          </span>
        </a>
        <nav className="main-navigation" id="main-nav" role="navigation" aria-label="Main Navigation">
          <ul>
            {NAV_ITEMS.map((item) => (
              <li key={item.label}>
                <a href={item.href}>{item.label}</a>
              </li>
            ))}
            <li>
              <a href="#" className="nav-cta">
                Get involved
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </header>
  )
}

/**
 * Live timeline single — stacked article order, AIAD colours & fonts.
 * Source: single-timeline.php + header.php
 */
export function BlogWpTimelineSingleCurrent() {
  const entry = CURRENT_WP_TIMELINE_ENTRY

  return (
    <div className="wp-current-preview">
      <p className="border-b border-amber-200 bg-amber-50 px-3 py-2 font-mono text-[10px] uppercase tracking-wider text-amber-900">
        single-timeline.php · header.php · post #{entry.id}
      </p>
      <WpSiteHeader />
      <main id="main" role="main" className="single-timeline">
        <article
          id={`post-${entry.id}`}
          className="single-timeline-entry single-timeline-entry--stacked post type-aiad_timeline status-publish"
        >
          <div className="single-timeline-entry__container">
            <span className="single-timeline-entry__badge single-timeline-entry__badge--outline">
              {entry.badge}
            </span>
            <h1 className="single-timeline-entry__title">{entry.title}</h1>
            <div className="single-timeline-entry__meta-row">
              <div className="single-timeline-entry__author">
                <img
                  className="single-timeline-entry__author-avatar"
                  src={entry.author.avatar}
                  alt=""
                  width={44}
                  height={44}
                />
                <div className="single-timeline-entry__author-meta">
                  <p className="single-timeline-entry__author-name">{entry.author.name}</p>
                  <p className="single-timeline-entry__author-role">{entry.author.role}</p>
                </div>
              </div>
              <time className="single-timeline-entry__date" dateTime={entry.dateTime}>
                {entry.date}
              </time>
            </div>
            <div className="single-timeline-entry__media">
              <figure className="resource-activity-figure resource-activity-figure--timeline-single">
                <img
                  className="resource-activity-figure__img"
                  src={entry.heroImage}
                  alt=""
                  loading="lazy"
                  width={1200}
                  height={630}
                  style={{ objectPosition: entry.heroObjectPosition }}
                />
              </figure>
            </div>
            {entry.excerpt ? (
              <p className="single-timeline-entry__excerpt">{entry.excerpt}</p>
            ) : null}
            <div className="single-timeline-entry__content entry-content entry-content--timeline">
              <BlogSectionBody sections={entry.sections} variant="timeline" omitTags />
            </div>
            <div className="single-timeline-entry__cta">
              <a
                href={entry.ctaUrl}
                className="single-timeline-entry__cta-btn"
                target="_blank"
                rel="noopener noreferrer"
              >
                {entry.ctaLabel}
                <span className="single-timeline-entry__cta-icon" aria-hidden="true">
                  →
                </span>
              </a>
            </div>
            <RelatedPosts currentSlug={entry.slug} limit={3} variant="timeline" />
            <EntryTimelineTags items={timelineTagItems(entry.sections)} />
            <div className="single-timeline-entry__footer">
              <a href={entry.backHref} className="single-timeline-entry__back">
                <span aria-hidden="true">←</span>
                {entry.backLabel}
              </a>
              <button type="button" className="single-timeline-entry__share" aria-label="Share this update">
                <span className="single-timeline-entry__share-icon" aria-hidden="true">
                  ⎘
                </span>
                Share
              </button>
            </div>
          </div>
        </article>
      </main>
    </div>
  )
}

/** Minimal posts index — index.php fallback */
export function BlogWpArchiveCurrent() {
  return (
    <div className="wp-current-preview">
      <p className="border-b border-amber-200 bg-amber-50 px-3 py-2 font-mono text-[10px] uppercase tracking-wider text-amber-900">
        index.php · standard post loop
      </p>
      <main role="main" style={{ padding: '100px 1.25rem 3rem' }}>
        <div className="container">
          {ARCHIVE_POSTS.map((post) => (
            <article key={post.id} id={`post-${post.id}`} className="post" style={{ marginBottom: '3rem' }}>
              <h2 style={{ marginBottom: '0.5rem' }}>
                <a href={`#${post.slug}`}>{post.title}</a>
              </h2>
              <p style={{ color: '#737373', fontSize: '0.9rem', marginBottom: '1rem' }}>{post.date}</p>
              <div>
                <p>{post.excerpt}</p>
              </div>
            </article>
          ))}
        </div>
      </main>
    </div>
  )
}

export function BlogWpSingleCurrent() {
  return <BlogWpTimelineSingleCurrent />
}
