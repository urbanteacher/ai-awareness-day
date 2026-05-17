export type BlogAuthor = {
  name: string
  role: string
  avatar: string
}

export const DEFAULT_ENTRY_CTA = {
  label: 'Join the campaign',
  url: '#join',
} as const

export const DEFAULT_ENTRY_ACTIONS = {
  backLabel: 'Back to timeline',
  backHref: '/#timeline',
} as const

export type BlogPost = {
  id: string
  slug: string
  title: string
  excerpt: string
  date: string
  /** ISO date for <time datetime> */
  dateTime?: string
  readTime: string
  category: string
  /** Overrides category in entry badge when set (timeline singles) */
  badge?: string
  tags: string[]
  heroImage: string
  heroObjectPosition?: string
  author: BlogAuthor
  sections: BlogSection[]
  ctaLabel?: string
  ctaUrl?: string
  backLabel?: string
  backHref?: string
}

export type BlogSection =
  | { type: 'p'; text: string }
  | { type: 'h2'; text: string }
  | { type: 'pullquote'; text: string; cite?: string }
  | { type: 'list'; items: string[] }
  | { type: 'callout'; text: string; variant?: 'info' | 'tip' }
  | { type: 'link'; label: string; href: string }
  | { type: 'tags'; items: string[] }

export const PUBLICATION = {
  name: 'AI Awareness Day',
  tagline: 'Practice notes for schools, families, and educators',
  subscribeLabel: 'Get updates for AI Awareness Day',
} as const

/** Matches Björk / our theme.json contentSize */
export const ARTICLE_MEASURE = 'max-w-[720px]'

const img = (seed: string, w = 1400, h = 780) =>
  `https://picsum.photos/seed/${seed}/${w}/${h}`

export const DEMO_AUTHOR: BlogAuthor = {
  name: 'Dr Sam Rivera',
  role: 'AI Literacy Lead, Urban Teacher',
  avatar: img('author-sam', 120, 120),
}

export const FEATURED_POST: BlogPost = {
  id: '1',
  slug: 'classroom-ai-without-the-hype',
  title: 'Classroom AI without the hype: what actually works on AI Awareness Day',
  excerpt:
    'Practical patterns from schools that ran short, honest activities — not product demos — and left students with better questions than answers.',
  date: '12 May 2026',
  dateTime: '2026-05-12',
  readTime: '8 min read',
  category: 'Practice',
  badge: 'Practice',
  ctaLabel: DEFAULT_ENTRY_CTA.label,
  ctaUrl: DEFAULT_ENTRY_CTA.url,
  backLabel: DEFAULT_ENTRY_ACTIONS.backLabel,
  backHref: DEFAULT_ENTRY_ACTIONS.backHref,
  tags: ['classroom', 'literacy', 'activities'],
  heroImage: img('blog-hero-main'),
  author: DEMO_AUTHOR,
  sections: [
    {
      type: 'p',
      text: 'When we talk about “AI in schools”, the conversation often jumps straight to tools. AI Awareness Day is deliberately different: it starts with principles — safe, smart, creative, responsible, future-focused — and asks what those look like in a real classroom on a real Thursday.',
    },
    {
      type: 'h2',
      text: 'Start with a question, not a login screen',
    },
    {
      type: 'p',
      text: 'The most effective sessions we saw lasted between five and twenty minutes. Teachers opened with a single provocation (“How do you know an image is real?”) and let students argue before any technology appeared. That ordering matters: literacy before automation.',
    },
    {
      type: 'pullquote',
      text: 'Students don’t need another app on day one. They need language for doubt.',
      cite: 'Head of Computing, Manchester',
    },
    {
      type: 'callout',
      variant: 'tip',
      text: 'Try opening with one slide and no devices. If the room is arguing in the first three minutes, you’re on track.',
    },
    {
      type: 'p',
      text: 'Where schools did introduce tools, they framed them as collaborators with limits. A Year 9 class compared two draft paragraphs — one human, one model — and marked up where the model sounded confident but was vague.',
    },
    {
      type: 'h2',
      text: 'Three layouts that travelled well',
    },
    {
      type: 'list',
      items: [
        'Whole-class discussion with a single shared slide (no devices).',
        'Gallery walk of student questions on sticky notes, photographed for the display board.',
        'Optional extension: try one prompt at home with a parent/carer conversation guide.',
      ],
    },
    {
      type: 'p',
      text: 'None of these require a specialist lab. You are building habits of checking, asking, and revising — the same habits good writing teachers already teach.',
    },
  ],
}

export const ARCHIVE_POSTS: BlogPost[] = [
  FEATURED_POST,
  {
    id: '2',
    slug: 'parent-conversation-starters',
    title: 'Five conversation starters for parents on AI at home',
    excerpt: 'Short, non-technical prompts you can send home without overwhelming families.',
    date: '3 May 2026',
    readTime: '4 min read',
    category: 'Families',
    tags: ['parents', 'home'],
    heroImage: img('blog-parents'),
    author: {
      name: 'Aisha Khan',
      role: 'Family engagement, AIAD',
      avatar: img('author-aisha', 120, 120),
    },
    sections: [
      {
        type: 'p',
        text: 'Parents told us they want clarity, not jargon. These prompts assume no special apps — just curiosity at the kitchen table.',
      },
    ],
  },
  {
    id: '3',
    slug: 'display-board-blueprint',
    title: 'How one school turned a corridor into an AI literacy gallery',
    excerpt: 'Photos, student quotes, and a printable blueprint you can adapt.',
    date: '28 April 2026',
    readTime: '6 min read',
    category: 'Showcase',
    tags: ['display board', 'whole-school'],
    heroImage: img('blog-display'),
    author: {
      name: 'James Okafor',
      role: 'Assistant Head, South London',
      avatar: img('author-james', 120, 120),
    },
    sections: [
      {
        type: 'p',
        text: 'The display board lived outside the IT suite for three weeks. It was refreshed twice — once after student interviews, once after a staff briefing.',
      },
    ],
  },
  {
    id: '4',
    slug: 'policy-in-plain-english',
    title: 'Drafting an acceptable-use line students can actually read',
    excerpt: 'A before/after rewrite of a typical school AI policy paragraph.',
    date: '19 April 2026',
    readTime: '5 min read',
    category: 'Policy',
    tags: ['safeguarding', 'governance'],
    heroImage: img('blog-policy'),
    author: DEMO_AUTHOR,
    sections: [
      {
        type: 'p',
        text: 'Policies fail when only adults understand them. We tested a plain-English version with Year 8 and cut follow-up questions by half.',
      },
    ],
  },
]

export const AIAD_TEAM_AUTHOR: BlogAuthor = {
  name: 'AI Awareness Day Team',
  role: 'Resources & campaign updates',
  avatar: img('aiad-team', 120, 120),
}

/** Live timeline single (post #453) — same section model as other blog demos */
export const CURRENT_WP_TIMELINE_ENTRY: BlogPost & {
  id: string
  badge: string
  dateTime: string
  heroObjectPosition: string
} = {
  id: '453',
  slug: 'ai-awareness-activities',
  badge: 'Updated resources page',
  title: 'AI Awareness Activities',
  excerpt:
    'Free classroom activities for AI Awareness Day — starters, deep-dives, and whole-school assemblies.',
  date: '7 May 2026',
  dateTime: '2026-05-07',
  readTime: '4 min read',
  category: 'Resources',
  tags: [
    'AIAwarenessDay',
    'EdTech',
    'TeachingResources',
    'AIinEducation',
    'DigitalLiteracy',
    'ClassroomAI',
    'SchoolResources',
    'TeacherTips',
  ],
  heroImage:
    'https://aiawarenessday.co.uk/wp-content/uploads/2026/05/Your-paragraph-text-1024x1024.png',
  heroObjectPosition: '53% 81%',
  author: AIAD_TEAM_AUTHOR,
  ctaLabel: 'Join the campaign',
  ctaUrl: '#',
  backLabel: 'Back to timeline',
  backHref: '/#timeline',
  sections: [
    {
      type: 'p',
      text: 'AI Awareness Day is just around the corner on 4th June! 🎂',
    },
    {
      type: 'p',
      text: 'Teachers, we know how fast the tech landscape is shifting and how hard it can be to find reliable, plug-and-play resources to talk about AI with students.',
    },
    {
      type: 'p',
      text: 'That’s why we’ve pulled together a growing set of free activities — from 5-minute morning starters to 20-minute whole-school assemblies.',
    },
    {
      type: 'h2',
      text: 'What’s in the pack',
    },
    {
      type: 'list',
      items: [
        '5-Minute Morning Starters',
        'How Does AI Actually ‘Think’?',
        'Who’s Really Behind the Screen?',
        'AI Relationships?',
        'The Hidden Costs of AI',
        '15-Minute Deep-Dive Video Activities',
        '20-Minute Whole-School Assemblies',
      ],
    },
    {
      type: 'tags',
      items: [
        '#AIAwarenessDay',
        '#EdTech',
        '#TeachingResources',
        '#AIinEducation',
        '#DigitalLiteracy',
        '#ClassroomAI',
        '#SchoolResources',
        '#TeacherTips',
      ],
    },
  ],
}

export function slugifyHeading(text: string) {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
}

export function tocFromSections(sections: BlogSection[]) {
  return sections
    .filter((s): s is { type: 'h2'; text: string } => s.type === 'h2')
    .map((s) => ({ id: slugifyHeading(s.text), label: s.text }))
}
