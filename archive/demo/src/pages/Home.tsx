import { Link } from 'react-router-dom'

const DEMOS = [
  {
    to: '/cards',
    name: 'Activity cards',
    blurb: 'Ten card style variants for free resources — baseline, editorial, brand badges, and more.',
  },
  {
    to: '/blog',
    name: 'Blog page layouts',
    blurb: 'Six single-article and three archive/index layouts for WordPress posts — presentation, not cards.',
  },
  {
    to: '/feeds',
    name: 'Timeline feeds',
    blurb: 'Campaign update feed layouts — production timeline, lists, grids, and full #timeline shell.',
  },
  {
    to: '/metal',
    name: 'Metal FX',
    blurb: 'WebGL liquid metal ring for buttons and UI.',
  },
  {
    to: '/styleui',
    name: 'StyleUI',
    blurb: 'Handmade full-site templates — live homepage embedded.',
  },
  {
    to: '/skiper',
    name: 'Skiper UI',
    blurb: 'shadcn registry — animated link treatments.',
  },
  {
    to: '/aliimam',
    name: 'Ali Imam',
    blurb: 'Registry dot pattern in-app + embedded docs.',
  },
  {
    to: '/watermelon',
    name: 'Watermelon UI',
    blurb: 'Premium blocks registry — live site embedded.',
  },
  {
    to: '/cult',
    name: 'Cult UI',
    blurb: 'Motion-rich niche components for shadcn projects.',
  },
  {
    to: '/dotmatrix',
    name: 'Dot Matrix',
    blurb: 'Matrix dot loaders via shadcn registry.',
  },
  {
    to: '/componentry',
    name: 'Componentry',
    blurb: 'Animated primitives via @componentry registry.',
  },
  {
    to: '/balloons',
    name: 'balloons-js',
    blurb: 'Celebration balloon bursts in the DOM.',
  },
  {
    to: '/certificate',
    name: 'Participation certificate',
    blurb:
      'Teachers upload evidence and a school logo; generate a branded PDF certificate in the browser.',
  },
] as const

export function Home() {
  return (
    <div className="mx-auto max-w-4xl px-4 py-12">
      <header className="mb-10">
        <h1 className="mb-2 text-3xl font-semibold tracking-tight text-foreground">
          UI library demos
        </h1>
        <p className="text-muted-foreground">
          Each card opens an in-app demo: React components from npm or shadcn registries, plus
          full-page iframes for template and catalog sites where that is the practical preview.
        </p>
      </header>
      <ul className="grid gap-4 sm:grid-cols-2">
        {DEMOS.map((d) => (
          <li key={d.to}>
            <Link
              to={d.to}
              className="flex h-full flex-col rounded-xl border border-border bg-card p-5 shadow-sm transition hover:border-primary/40 hover:shadow-md"
            >
              <span className="text-lg font-medium text-card-foreground">{d.name}</span>
              <span className="mt-1 flex-1 text-sm text-muted-foreground">{d.blurb}</span>
              <span className="mt-4 text-sm font-medium text-primary">Open demo →</span>
            </Link>
          </li>
        ))}
      </ul>
    </div>
  )
}
