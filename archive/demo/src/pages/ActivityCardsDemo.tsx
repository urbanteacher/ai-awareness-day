import { Link } from 'react-router-dom'
import {
  ArrowRight,
  ArrowUpRight,
  BookOpen,
  Brain,
  Clock,
  Play,
  Shield,
  Sparkles,
} from 'lucide-react'

type Category = 'CREATIVE' | 'SAFE' | 'SMART'

type Activity = {
  category: Category
  title: string
  description: string
  duration: string
  image: string
}

const img = (seed: string, w = 800, h = 500) =>
  `https://picsum.photos/seed/${seed}/${w}/${h}`

const ACTIVITIES: Activity[] = [
  {
    category: 'CREATIVE',
    title: 'AI as Your Creative Partner',
    description: 'Using AI to amplify human creativity, not replace it.',
    duration: '5 min',
    image: img('creative-partner'),
  },
  {
    category: 'SAFE',
    title: 'AI Relationships: Easier Than the Real Thing?',
    description:
      'A discussion starter using a short viral clip: 20% of boys aged 12–16 are seeing peers enter relationships with AI chatbots. Why? And what does that mean for us?',
    duration: '5 min',
    image: img('ai-relationships'),
  },
  {
    category: 'SMART',
    title: 'How Does AI Actually ‘Think’?',
    description:
      'Quick 5-minute starter: understand that AI predicts patterns rather than "thinking", and why hallucinations occur.',
    duration: '5 min',
    image: img('how-ai-thinks'),
  },
]

const CATEGORY_TONE: Record<Category, { chip: string; bar: string; soft: string; ring: string; accent: string; icon: typeof Sparkles }> = {
  CREATIVE: {
    chip: 'bg-violet-500 text-white',
    bar: 'bg-violet-500',
    soft: 'bg-violet-100',
    ring: 'ring-violet-400',
    accent: 'text-violet-600',
    icon: Sparkles,
  },
  SAFE: {
    chip: 'bg-sky-500 text-white',
    bar: 'bg-sky-500',
    soft: 'bg-sky-100',
    ring: 'ring-sky-400',
    accent: 'text-sky-600',
    icon: Shield,
  },
  SMART: {
    chip: 'bg-amber-500 text-white',
    bar: 'bg-amber-500',
    soft: 'bg-amber-100',
    ring: 'ring-amber-400',
    accent: 'text-orange-600',
    icon: Brain,
  },
}

function Section({ id, title, blurb, children }: { id: string; title: string; blurb: string; children: React.ReactNode }) {
  return (
    <section id={id} className="scroll-mt-8 border-t border-border pt-12">
      <div className="mb-6 flex items-baseline justify-between gap-4">
        <div>
          <h2 className="text-xl font-semibold tracking-tight">{title}</h2>
          <p className="mt-1 text-sm text-muted-foreground">{blurb}</p>
        </div>
        <span className="text-xs font-mono text-muted-foreground">#{id}</span>
      </div>
      <div className="grid gap-6 md:grid-cols-3">{children}</div>
    </section>
  )
}

// ----- VARIANT 1: Current (baseline) — image replaces STARTER SLIDE block -----
function VariantBaseline({ a }: { a: Activity }) {
  return (
    <article className="flex flex-col">
      <div className="relative flex h-44 items-center justify-center overflow-hidden bg-black">
        <img src={a.image} alt="" className="absolute inset-0 h-full w-full object-cover opacity-60" />
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/40" />
        <span className="absolute left-3 top-3 rounded bg-white/95 px-2 py-1 text-[10px] font-bold tracking-wide text-black">
          LESSON STARTER<br />{a.duration.toUpperCase()}
        </span>
        <span className={`absolute right-3 top-3 rounded px-2 py-0.5 text-[10px] font-bold tracking-wider ${CATEGORY_TONE[a.category].chip}`}>
          {a.category}
        </span>
        <span className="relative text-2xl font-black tracking-wider text-white drop-shadow-lg">STARTER SLIDE</span>
      </div>
      <div className="pt-4">
        <h3 className="text-base font-bold leading-snug">{a.title}</h3>
        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{a.description}</p>
        <a className="mt-4 inline-block text-sm font-semibold">View resource →</a>
      </div>
    </article>
  )
}

// ----- VARIANT 2: Soft tinted card, no black block -----
function VariantSoftTint({ a }: { a: Activity }) {
  const t = CATEGORY_TONE[a.category]
  const Icon = t.icon
  return (
    <article className={`flex flex-col overflow-hidden ${t.soft} ring-1 ${t.ring} transition hover:-translate-y-0.5 hover:shadow-lg`}>
      <div className="relative h-32">
        <img src={a.image} alt="" className="h-full w-full object-cover" />
        <span className={`absolute right-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider ${t.chip}`}>{a.category}</span>
        <span className={`absolute -bottom-5 left-5 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-md ring-1 ring-black/5 ${t.accent}`}>
          <Icon size={20} />
        </span>
      </div>
      <div className="px-6 pb-6 pt-8">
      <div className="mb-2 flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
        <Clock size={12} /> Lesson starter · {a.duration}
      </div>
      <h3 className="text-lg font-bold leading-snug">{a.title}</h3>
      <p className="mt-2 flex-1 text-sm leading-relaxed text-muted-foreground">{a.description}</p>
      <a className={`mt-5 inline-flex items-center gap-1 text-sm font-semibold ${t.accent}`}>
        View resource <ArrowRight size={14} />
      </a>
      </div>
    </article>
  )
}

// ----- VARIANT 3: Editorial / magazine -----
function VariantEditorial({ a }: { a: Activity }) {
  const t = CATEGORY_TONE[a.category]
  return (
    <article className="flex flex-col">
      <img src={a.image} alt="" className="mb-5 aspect-[16/10] w-full object-cover grayscale" />
      <div className="border-t-2 border-black pt-5" />
      <div className="mb-3 flex items-center gap-3 text-[11px] uppercase tracking-[0.18em] text-muted-foreground">
        <span className={`px-2 py-0.5 ${t.chip} font-bold`}>{a.category}</span>
        <span>·</span>
        <span>{a.duration} read</span>
      </div>
      <h3 className="font-serif text-2xl leading-tight">{a.title}</h3>
      <p className="mt-3 flex-1 text-[15px] leading-relaxed text-foreground/80">{a.description}</p>
      <a className="mt-5 inline-flex items-center gap-1 text-sm font-semibold underline underline-offset-4">
        Read resource <ArrowUpRight size={14} />
      </a>
    </article>
  )
}

// ----- VARIANT 4: Bold gradient header -----
function VariantGradient({ a }: { a: Activity }) {
  const gradient =
    a.category === 'CREATIVE'
      ? 'from-violet-500 via-fuchsia-500 to-pink-500'
      : a.category === 'SAFE'
        ? 'from-sky-500 via-cyan-500 to-teal-500'
        : 'from-amber-500 via-orange-500 to-red-500'
  return (
    <article className="flex flex-col overflow-hidden border border-border bg-card shadow-sm transition hover:shadow-xl">
      <div className={`relative flex h-44 items-end justify-center bg-gradient-to-br ${gradient} p-5`}>
        <img src={a.image} alt="" className="absolute inset-0 h-full w-full object-cover mix-blend-overlay opacity-90" />
        <div className={`absolute inset-0 bg-gradient-to-br ${gradient} opacity-60`} />
        <span className="absolute right-3 top-3 z-10 rounded-full bg-black/40 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white backdrop-blur">
          {a.category}
        </span>
        <span className="absolute inset-0 z-10 flex items-center justify-center text-2xl font-black tracking-wider text-white drop-shadow-lg">
          STARTER SLIDE
        </span>
        <div className="relative z-10 flex items-center gap-2 text-white">
          <Play size={14} className="fill-white" />
          <span className="text-xs font-semibold uppercase tracking-wide">Lesson starter · {a.duration}</span>
        </div>
      </div>
      <div className="flex flex-1 flex-col p-5">
        <h3 className="text-lg font-bold leading-snug">{a.title}</h3>
        <p className="mt-2 flex-1 text-sm leading-relaxed text-muted-foreground">{a.description}</p>
        <a className="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-foreground">
          View resource <ArrowRight size={14} />
        </a>
      </div>
    </article>
  )
}

// ----- VARIANT 5: Minimal monochrome with side rule -----
function VariantMinimal({ a }: { a: Activity }) {
  const t = CATEGORY_TONE[a.category]
  return (
    <article className="group relative flex overflow-hidden border border-border bg-card transition hover:border-foreground">
      <img src={a.image} alt="" className="aspect-square w-1/3 flex-shrink-0 self-stretch object-cover" />
      <div className="relative flex flex-1 flex-col p-5">
        <span className={`absolute left-0 top-5 h-8 w-1 ${t.bar}`} aria-hidden />
        <div className="mb-2 flex items-center justify-between">
          <span className="text-[11px] font-bold uppercase tracking-[0.16em] text-muted-foreground">{a.category}</span>
          <span className="inline-flex items-center gap-1 text-[11px] text-muted-foreground">
            <Clock size={11} /> {a.duration}
          </span>
        </div>
        <h3 className="text-base font-bold leading-snug">{a.title}</h3>
        <p className="mt-1.5 line-clamp-3 flex-1 text-xs leading-relaxed text-muted-foreground">{a.description}</p>
        <a className="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold">
          View resource
          <ArrowRight size={14} className="transition group-hover:translate-x-0.5" />
        </a>
      </div>
    </article>
  )
}

// ----- VARIANT 6: Dark mode, glowing accent -----
function VariantDark({ a }: { a: Activity }) {
  const t = CATEGORY_TONE[a.category]
  const Icon = t.icon
  const glow =
    a.category === 'CREATIVE' ? 'shadow-[0_0_60px_-15px_rgba(167,139,250,0.6)]'
      : a.category === 'SAFE' ? 'shadow-[0_0_60px_-15px_rgba(56,189,248,0.6)]'
        : 'shadow-[0_0_60px_-15px_rgba(251,191,36,0.6)]'
  return (
    <article className={`relative flex flex-col overflow-hidden bg-zinc-950 p-6 text-zinc-100 ${glow}`}>
      <img src={a.image} alt="" className="absolute inset-0 h-full w-full object-cover opacity-25" />
      <div className="absolute inset-0 bg-gradient-to-b from-zinc-950/70 via-zinc-950/80 to-zinc-950" />
      <div className="relative mb-5 flex items-center justify-between">
        <div className="flex items-center gap-2">
          <span className={`inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 ring-1 ring-white/10 ${t.accent}`}>
            <Icon size={16} />
          </span>
          <span className="text-[11px] font-bold uppercase tracking-[0.16em] text-zinc-400">
            Lesson starter
          </span>
        </div>
        <span className={`rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider ${t.chip}`}>{a.category}</span>
      </div>
      <h3 className="relative text-lg font-bold leading-snug">{a.title}</h3>
      <p className="relative mt-2 flex-1 text-sm leading-relaxed text-zinc-400">{a.description}</p>
      <div className="relative mt-5 flex items-center justify-between border-t border-white/10 pt-4">
        <span className="inline-flex items-center gap-1 text-xs text-zinc-500">
          <Clock size={11} /> {a.duration}
        </span>
        <a className={`inline-flex items-center gap-1 text-sm font-semibold ${t.accent}`}>
          View resource <ArrowRight size={14} />
        </a>
      </div>
    </article>
  )
}

// ----- VARIANT 7: Chamfered brand badge -----
const CHAMFER = {
  clipPath:
    'polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))',
}

function VariantChamfered({ a }: { a: Activity }) {
  const t = CATEGORY_TONE[a.category]
  const fill =
    a.category === 'CREATIVE' ? 'bg-violet-500'
      : a.category === 'SAFE' ? 'bg-sky-500'
        : 'bg-orange-500'
  return (
    <article className="flex flex-col">
      <div className="relative aspect-square w-full" style={CHAMFER}>
        <div className={`absolute inset-0 ${fill}`} />
        <img src={a.image} alt="" className="absolute inset-0 h-full w-full object-cover mix-blend-multiply opacity-90" />
        <div
          className="absolute inset-0 bg-black"
          style={{
            clipPath:
              'polygon(0 60%, 40% 60%, 40% 100%, 50px 100%, 0 calc(100% - 50px))',
          }}
        />
        <div
          className="absolute inset-0 bg-black"
          style={{
            clipPath:
              'polygon(60% 0, calc(100% - 50px) 0, 100% 50px, 100% 40%, 60% 40%)',
          }}
        />
        <span className="absolute left-4 top-4 text-[11px] font-bold uppercase tracking-wider text-white">
          AI Awareness<br />2026
        </span>
        <span className="absolute bottom-4 right-4 text-right text-[11px] font-bold uppercase tracking-wider text-white">
          {a.category}<br />{a.duration}
        </span>
        <span className="absolute inset-0 flex items-center justify-center text-xl font-black uppercase tracking-wider text-white drop-shadow">
          Starter Slide
        </span>
      </div>
      <div className="pt-5">
        <h3 className="text-base font-bold leading-snug">{a.title}</h3>
        <p className="mt-2 text-sm leading-relaxed text-muted-foreground">{a.description}</p>
        <a className={`mt-4 inline-flex items-center gap-1 text-sm font-semibold ${t.accent}`}>
          View resource <ArrowRight size={14} />
        </a>
      </div>
    </article>
  )
}

// ----- VARIANT 8: Brand badge system (faithful port of PolygonCard + SplitImageCard) -----
type BrandTheme = 'safe' | 'smart' | 'creative' | 'responsible' | 'future'

const ACTIVITY_BRAND_MAP: Record<Category, {
  theme: BrandTheme
  difficulty: 'beginner' | 'intermediate' | 'advanced'
  tags: string[]
  formats: string[]
}> = {
  CREATIVE: {
    theme: 'creative',
    difficulty: 'beginner',
    tags: ['workshop', '5 min', 'creativity'],
    formats: ['Workshop', 'Game'],
  },
  SAFE: {
    theme: 'safe',
    difficulty: 'beginner',
    tags: ['discussion', '5 min', 'wellbeing'],
    formats: ['Slide', 'Discussion'],
  },
  SMART: {
    theme: 'smart',
    difficulty: 'intermediate',
    tags: ['concept', '5 min', 'literacy'],
    formats: ['Slide', 'Starter'],
  },
}

const BRAND_THEMES: Record<BrandTheme, {
  gradient: string
  badgeGradient: string
  dotColor: string
  label: string
  difficultyColor: string
}> = {
  safe: {
    gradient: 'from-sky-500 to-sky-600',
    badgeGradient: 'linear-gradient(135deg, #0ea5e9, #0284c7)',
    dotColor: 'bg-sky-500',
    label: 'SAFE',
    difficultyColor: 'bg-sky-100 text-sky-800',
  },
  smart: {
    gradient: 'from-orange-500 to-orange-600',
    badgeGradient: 'linear-gradient(135deg, #f97316, #ea580c)',
    dotColor: 'bg-orange-500',
    label: 'SMART',
    difficultyColor: 'bg-orange-100 text-orange-800',
  },
  creative: {
    gradient: 'from-purple-500 to-purple-600',
    badgeGradient: 'linear-gradient(135deg, #a855f7, #9333ea)',
    dotColor: 'bg-purple-500',
    label: 'CREATIVE',
    difficultyColor: 'bg-purple-100 text-purple-800',
  },
  responsible: {
    gradient: 'from-green-500 to-green-600',
    badgeGradient: 'linear-gradient(135deg, #22c55e, #16a34a)',
    dotColor: 'bg-green-500',
    label: 'RESPONSIBLE',
    difficultyColor: 'bg-green-100 text-green-800',
  },
  future: {
    gradient: 'from-pink-500 to-pink-600',
    badgeGradient: 'linear-gradient(135deg, #ec4899, #db2777)',
    dotColor: 'bg-pink-500',
    label: 'FUTURE',
    difficultyColor: 'bg-blue-100 text-blue-800',
  },
}

const POLYGON_CARD_CLIP =
  'polygon(0% 0%, 75% 0%, 100% 25%, 100% 100%, 25% 100%, 0% 75%)'

/** Left inset so footer copy clears the polygon’s bottom-left chamfer (25%,100%)–(0%,75%). */
const POLYGON_ACTIVITY_FOOTER_PAD_LEFT = 'max(1.25rem, min(28%, 9rem))'


const SPLIT_CARD_CLIP =
  'polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))'

function SplitImageCard({
  theme,
  title,
  description,
  duration,
  formats,
  imageUrl,
  backgroundImageUrl,
}: {
  theme: BrandTheme
  title: string
  description: string
  duration: string
  formats: string[]
  imageUrl: string
  backgroundImageUrl?: string
}) {
  const config = BRAND_THEMES[theme]
  return (
    <div
      className="group relative flex h-full min-h-0 cursor-pointer flex-col overflow-hidden border border-gray-600 bg-gray-800 p-0"
      style={{ clipPath: SPLIT_CARD_CLIP }}
    >
      {/* Extra inset keeps copy clear of the 50px chamfers (top-right + bottom-left of whole card). */}
      <div
        className={`relative flex min-h-0 flex-1 flex-col overflow-hidden bg-gradient-to-br px-7 pb-7 pt-8 sm:px-8 sm:pb-8 sm:pt-9 ${config.gradient}`}
      >
        {backgroundImageUrl && (
          <div
            className="absolute inset-0 bg-cover bg-center opacity-20"
            style={{ backgroundImage: `url('${backgroundImageUrl}')` }}
          />
        )}
        <div className="absolute inset-0 bg-black/30" />
        <div
          className="absolute right-0 top-0 size-8 bg-white/20"
          style={{ clipPath: 'polygon(100% 0, 0 0, 100% 100%)' }}
        />
        <div className="relative z-10 mb-4 flex min-w-0 items-center justify-between gap-3">
          <div className="flex min-w-0 items-center space-x-2">
            <div className={`h-3 w-3 shrink-0 rounded-full ${config.dotColor}`} />
            <span className="truncate text-sm font-medium text-white">{config.label}</span>
          </div>
          <span className="shrink-0 rounded-full bg-white/20 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white backdrop-blur">
            {duration}
          </span>
        </div>
        <div className="relative z-10 mb-4 shrink-0">
          <div className="h-24 w-full overflow-hidden rounded-lg bg-white/20 backdrop-blur-sm">
            <img src={imageUrl} alt={title} className="h-full w-full object-cover" />
          </div>
        </div>
        <div className="relative z-10 mt-auto min-w-0 pb-1">
          <div className="mb-3 flex flex-wrap gap-1.5">
            {formats.map((fmt) => (
              <span
                key={fmt}
                className="rounded-sm bg-white/95 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-gray-900 shadow-sm"
              >
                {fmt}
              </span>
            ))}
          </div>
          <h4 className="break-words text-xl font-bold leading-snug text-white">
            <span className="line-clamp-2">{title}</span>
          </h4>
        </div>
      </div>
      <div className="flex shrink-0 flex-col bg-gray-800 px-7 pb-9 pt-7 sm:px-8 sm:pb-10 sm:pt-8">
        <p className="min-h-[5.75rem] line-clamp-4 text-sm leading-relaxed text-gray-200">{description}</p>
        <div className="mt-4 flex justify-end">
          <a className="inline-flex items-center gap-1.5 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-900 transition hover:bg-gray-100">
            View resource <ArrowRight size={14} />
          </a>
        </div>
      </div>
    </div>
  )
}


// ----- VARIANT 9: Hybrid — PolygonCard geometry scaled to a full activity card -----
function VariantPolygonActivity({
  theme,
  title,
  description,
  duration,
  imageUrl,
  formats,
}: {
  theme: BrandTheme
  title: string
  description: string
  duration: string
  imageUrl: string
  formats: string[]
}) {
  const config = BRAND_THEMES[theme]
  return (
    <article
      className="group relative flex aspect-[4/5] min-h-0 flex-col overflow-hidden bg-gray-800"
      style={{ clipPath: POLYGON_CARD_CLIP }}
    >
      <div
        className={`relative flex min-h-0 flex-1 flex-col overflow-hidden bg-gradient-to-br ${config.gradient}`}
      >
        <img
          src={imageUrl}
          alt=""
          className="absolute inset-0 h-full w-full object-cover mix-blend-overlay opacity-80 transition group-hover:scale-105"
        />
        <div className="pointer-events-none absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-black/25 to-transparent" />
        <div className="relative z-10 flex shrink-0 items-center justify-between gap-3 p-5">
          <div className="flex min-w-0 items-center gap-2">
            <span className={`h-2.5 w-2.5 shrink-0 rounded-full ${config.dotColor}`} />
            <span className="truncate text-[11px] font-bold uppercase tracking-[0.18em] text-white">
              {config.label}
            </span>
          </div>
          <span className="shrink-0 rounded-full bg-white/20 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white backdrop-blur">
            {duration}
          </span>
        </div>
        <div className="relative z-10 mt-auto flex w-full flex-col items-start gap-2.5 px-5 pb-5 pt-2">
          <div className="flex flex-wrap gap-1.5">
            {formats.map((fmt) => (
              <span
                key={fmt}
                className="rounded-sm bg-white/95 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-gray-900 shadow-sm"
              >
                {fmt}
              </span>
            ))}
          </div>
          <h3 className="max-w-[min(100%,22rem)] text-left text-2xl font-black leading-tight tracking-wider text-white drop-shadow line-clamp-3">
            {title}
          </h3>
        </div>
      </div>

      <div
        className="shrink-0 space-y-3 bg-gray-800 pr-5 pb-5 pt-3 text-white"
        style={{ paddingLeft: POLYGON_ACTIVITY_FOOTER_PAD_LEFT }}
      >
        <p className="line-clamp-3 text-sm leading-relaxed text-gray-200">{description}</p>
        <div className="flex justify-end">
          <a className="inline-flex items-center gap-1.5 bg-white px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-900 transition hover:bg-gray-100">
            View resource <ArrowRight size={14} />
          </a>
        </div>
      </div>
    </article>
  )
}

const POLYGON_ACTIVITIES: {
  theme: BrandTheme
  title: string
  description: string
  duration: string
  imageUrl: string
  formats: string[]
}[] = [
  {
    theme: 'safe',
    title: 'AI Relationships',
    description:
      'Discussion starter: 20% of boys aged 12–16 are entering relationships with AI chatbots. Why, and what does it mean?',
    duration: '5 MIN',
    imageUrl: img('ai-relationships'),
    formats: ['Slide', 'Discussion'],
  },
  {
    theme: 'smart',
    title: 'How Does AI Think?',
    description:
      'A 5-minute starter on pattern prediction — and why hallucinations happen when the prediction fails.',
    duration: '5 MIN',
    imageUrl: img('how-ai-thinks'),
    formats: ['Slide', 'Starter'],
  },
  {
    theme: 'creative',
    title: 'AI as a Creative Partner',
    description: 'Using AI to amplify human creativity, not replace it — a hands-on creative starter.',
    duration: '5 MIN',
    imageUrl: img('creative-partner'),
    formats: ['Workshop', 'Game'],
  },
  {
    theme: 'responsible',
    title: 'Bias in the Machine',
    description:
      'Examine how training data shapes outputs — short clip plus a structured discussion on fairness.',
    duration: '10 MIN',
    imageUrl: img('bias-in-ai'),
    formats: ['Video', 'Discussion'],
  },
  {
    theme: 'future',
    title: 'Jobs That Don’t Exist Yet',
    description:
      'Speculative-design starter: sketch a role that will exist in 2035 because of AI — and one that won’t.',
    duration: '15 MIN',
    imageUrl: img('future-jobs'),
    formats: ['Game', 'Worksheet'],
  },
]

// ----- VARIANT 10: Pointed chamfer — single black wedge with bold theme word -----
function VariantPointed({ a }: { a: Activity }) {
  const meta = ACTIVITY_BRAND_MAP[a.category]
  const config = BRAND_THEMES[meta.theme]
  const themeWord = config.label.replace('BE ', '')
  return (
    <article className="flex flex-col">
      <div
        className={`relative aspect-square w-full overflow-hidden bg-gradient-to-br ${config.gradient}`}
        style={CHAMFER}
      >
        <img
          src={a.image}
          alt=""
          className="absolute inset-0 h-full w-full object-cover mix-blend-overlay opacity-70"
        />
        <div className="absolute inset-0 bg-black/15" />

        {/* Top-left black wedge — covers theme word corner */}
        <div
          className="absolute inset-0 bg-black"
          style={{
            clipPath:
              'polygon(0 0, 55% 0, 0 45%)',
          }}
        />

        {/* Theme — top-right */}
        <span className="absolute left-5 top-4 text-left text-xs font-medium uppercase tracking-[0.18em] text-white drop-shadow">
          {themeWord}
        </span>

        {/* Duration with clock icon — bottom-right */}
        <span className="absolute bottom-4 right-5 inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-white drop-shadow">
          <Clock size={12} className="opacity-90" />
          {a.duration.toUpperCase()}
        </span>

        {/* Title inside black wedge */}
        <h3 className="absolute bottom-12 left-6 max-w-[55%] min-h-[2lh] text-left text-base font-bold leading-snug text-white drop-shadow line-clamp-2">
          {a.title}
        </h3>
      </div>

      <div className="pt-4">
        <h3 className={`min-h-[2lh] text-base font-bold leading-snug line-clamp-2 ${CATEGORY_TONE[a.category].accent}`}>{a.title}</h3>
        <p className="mt-1 text-sm leading-relaxed text-muted-foreground">{a.description}</p>
        <a className={`mt-4 inline-flex items-center gap-1 text-sm font-semibold ${CATEGORY_TONE[a.category].accent}`}>
          View resource <ArrowRight size={14} />
        </a>
      </div>
    </article>
  )
}

// ----- VARIANT 7c: Pointed chamfer — theme-colour wedge, photo with dark gradient -----
function VariantPointedColor({ a }: { a: Activity }) {
  const meta = ACTIVITY_BRAND_MAP[a.category]
  const config = BRAND_THEMES[meta.theme]
  const themeWord = config.label
  return (
    <article className="flex h-full flex-col">
      <div
        className="relative aspect-square w-full shrink-0 overflow-hidden bg-black"
        style={CHAMFER}
      >
        {/* Photo */}
        <img
          src={a.image}
          alt=""
          className="absolute inset-0 h-full w-full object-cover opacity-60"
        />

        {/* Top-left theme-colour wedge — blends directly with photo before any darkening */}
        <div
          className={`absolute inset-0 bg-gradient-to-br ${config.gradient} mix-blend-overlay`}
          style={{ clipPath: 'polygon(0 0, 55% 0, 0 45%)' }}
        />

        {/* Dark gradient only at the bottom for text legibility */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />

        {/* Centre — activity title (hero) */}
        <div className="pointer-events-none absolute inset-0 z-[1] flex items-center justify-center px-4 text-center">
          <h3
            aria-hidden="true"
            className="line-clamp-4 max-w-[min(90%,34ch)] text-center text-lg font-medium leading-snug text-white drop-shadow-lg sm:text-xl"
          >
            {a.title}
          </h3>
        </div>

        {/* Theme word — top-left inside coloured wedge */}
        <span className="absolute left-5 top-4 z-[2] text-left text-xs font-medium uppercase tracking-[0.18em] text-white drop-shadow">
          {themeWord}
        </span>

        {/* Duration — bottom-right */}
        <span className="absolute bottom-4 right-5 z-[2] inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-white drop-shadow">
          <Clock size={12} className="opacity-90" />
          {a.duration.toUpperCase()}
        </span>

        {/* Wedge corner — Starter Slide label (swapped from centre) */}
        <p className="absolute bottom-12 left-6 z-[2] max-w-[min(85%,18ch)] text-left text-[11px] font-bold uppercase tracking-[0.2em] text-white/95 drop-shadow">
          Starter Slide
        </p>
      </div>

      <div className="flex min-h-0 flex-1 flex-col pt-4">
        <h3 className={`min-h-[2lh] text-base font-bold leading-snug line-clamp-2 ${CATEGORY_TONE[a.category].accent}`}>{a.title}</h3>
        <p className="mt-1 line-clamp-4 text-sm leading-relaxed text-black dark:text-foreground">{a.description}</p>
        <a className={`mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold ${CATEGORY_TONE[a.category].accent}`}>
          View resource <ArrowRight size={14} />
        </a>
      </div>
    </article>
  )
}

export function ActivityCardsDemo() {
  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <header className="mb-10">
        <p className="text-xs font-bold uppercase tracking-[0.18em] text-muted-foreground">
          Free Resources · style sandbox
        </p>
        <h1 className="mt-2 text-4xl font-bold tracking-tight">AI Awareness Activities</h1>
        <p className="mt-3 max-w-2xl text-muted-foreground">
          Side-by-side card style experiments for the WordPress block. Each row uses the same three
          activities so visual differences are isolated to the card itself.
        </p>
        <div className="mt-4 flex flex-wrap gap-2 text-xs">
          {['baseline', 'soft-tint', 'editorial', 'gradient', 'minimal', 'dark', 'chamfered', 'pointed', 'pointed-color', 'shapes', 'polygon-activity'].map((id) => (
            <a key={id} href={`#${id}`} className="rounded-full border border-border px-3 py-1 hover:border-foreground">
              {id}
            </a>
          ))}
        </div>
        <Link to="/feeds" className="mt-4 mr-4 inline-block text-sm text-primary hover:underline">
          Timeline feed styles →
        </Link>
        <Link to="/" className="mt-4 inline-block text-sm text-muted-foreground hover:text-foreground">
          ← Back to demos
        </Link>
      </header>

      <Section id="baseline" title="1. Baseline (current)" blurb="The WordPress site's existing style — black STARTER SLIDE block, coloured chip, plain link.">
        {ACTIVITIES.map((a) => <VariantBaseline key={a.title} a={a} />)}
      </Section>

      <Section id="soft-tint" title="2. Soft tinted card" blurb="Category-tinted background, icon tile in place of the black header, friendlier and lighter.">
        {ACTIVITIES.map((a) => <VariantSoftTint key={a.title} a={a} />)}
      </Section>

      <Section id="editorial" title="3. Editorial" blurb="No card frame — just a rule, serif headline, generous typography. Reads like an article index.">
        {ACTIVITIES.map((a) => <VariantEditorial key={a.title} a={a} />)}
      </Section>

      <Section id="gradient" title="4. Bold gradient header" blurb="Replaces the flat black block with a per-category gradient. Higher visual energy.">
        {ACTIVITIES.map((a) => <VariantGradient key={a.title} a={a} />)}
      </Section>

      <Section id="minimal" title="5. Minimal monochrome" blurb="Just a thin coloured rule + clean typography. Quietest option, most content-forward.">
        {ACTIVITIES.map((a) => <VariantMinimal key={a.title} a={a} />)}
      </Section>

      <Section id="dark" title="6. Dark with glow" blurb="Inverted palette, subtle category-coloured glow. Useful as a contrast section on the page.">
        {ACTIVITIES.map((a) => <VariantDark key={a.title} a={a} />)}
      </Section>

      <Section id="chamfered" title="7. Chamfered brand badge" blurb="Brand-inspired clipped-corner shape (polygon clip-path). Two-tone block with brand text top-left and category bottom-right.">
        {ACTIVITIES.map((a) => <VariantChamfered key={a.title} a={a} />)}
      </Section>

      <Section id="pointed" title="7b. Pointed chamfer (theme-led)" blurb="Triangular black wedges with sharp inner points; theme label top-left, title on the image and repeated below.">
        {ACTIVITIES.map((a) => <VariantPointed key={a.title} a={a} />)}
      </Section>

      <Section id="pointed-color" title="7c. Pointed chamfer (theme colour wedge)" blurb="Top-left wedge in brand colour; activity title centred on the image; Starter Slide label bottom-left. Photo with dark gradient.">
        {ACTIVITIES.map((a) => <VariantPointedColor key={a.title} a={a} />)}
      </Section>

      <Section id="shapes" title="8. Brand badge system (SplitImageCard)" blurb="Faithful port of SplitImageCard from the AIAD site — 50px chamfered card with gradient hero, BE-X label, image tile, and dark content panel.">
        {ACTIVITIES.map((a) => {
          const meta = ACTIVITY_BRAND_MAP[a.category]
          return (
            <SplitImageCard
              key={a.title}
              theme={meta.theme}
              title={a.title}
              description={a.description}
              duration={a.duration.toUpperCase()}
              formats={meta.formats}
              imageUrl={a.image}
            />
          )
        })}
      </Section>

      <Section id="polygon-activity" title="9. Polygon activity card (hybrid)" blurb="PolygonCard chamfer + hero with format chips (slide, game, …) and title bottom-left; compact footer (description + CTA).">
        {POLYGON_ACTIVITIES.map((a) => (
          <VariantPolygonActivity
            key={a.title}
            theme={a.theme}
            title={a.title}
            description={a.description}
            duration={a.duration}
            imageUrl={a.imageUrl}
            formats={a.formats}
          />
        ))}
      </Section>

      <footer className="mt-16 border-t border-border pt-6 text-sm text-muted-foreground">
        <BookOpen size={14} className="mr-1 inline" />
        Edit <code className="rounded bg-muted px-1.5 py-0.5 text-xs">src/pages/ActivityCardsDemo.tsx</code> to tweak variants. HMR is on.
      </footer>
    </div>
  )
}
