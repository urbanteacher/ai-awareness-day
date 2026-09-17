import {
  BookOpenCheck,
  ClipboardList,
  GraduationCap,
  Home,
  Maximize2,
  School,
  Users,
} from 'lucide-react'
import { AnimatePresence, motion } from 'framer-motion'
import { useMemo, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'

import '@/styles/focus-areas.css'
import '@/styles/benchmark-deck.css'
import {
  AUDITS,
  AUDIENCE_WEIGHTS,
  DOMAIN_LABELS,
  LIVE_BENCHMARK_URL,
  WEIGHT_SCALES,
  auditKeyForRole,
  questionCount,
  questionsBySection,
  weightScaleLabel,
  type AuditAudienceKey,
  type AuditQuestion,
} from '@/lib/benchmark-questions'

type RoleKey = 'teacher' | 'student' | 'parent' | 'leader' | 'support' | 'public'
type DashboardTabKey = 'intro' | 'questions' | 'score' | 'signals' | 'domains' | 'simulator' | 'quiz'
type Tone = 'secure' | 'practice' | 'attention'

type Domain = {
  label: string
  value: number
  tone: Tone
  prompt: string
}

type FocusArea = {
  label: string
  pct: number
  summary: string
  likely_impact?: string[]
  actions?: string[]
}

type ResourceLink = {
  label: string
  url: string
  image?: string
  external?: boolean
  kicker?: string
  description?: string
}

type Strength = {
  title: string
  detail?: string
}

type PeerBenchmark = {
  comparisonLabel: string
  averageScore: number
  topQuartile: number
}

type RoleModel = {
  label: string
  audience: string
  scene: string
  headline: string
  scoreLabel: string
  score: number
  risk: number
  motif: string
  accent: string
  soft: string
  ink: string
  metricA: { label: string; value: string; note: string }
  metricB: { label: string; value: string; note: string }
  priority: string
  nextAction: string
  domains: Domain[]
  focusAreas: FocusArea[]
  strengths: Strength[]
  resources: ResourceLink[]
  peer: PeerBenchmark
}

const DEMO_SITE = 'http://localhost:8888'

function timelineResource(slug: string, label: string): ResourceLink {
  return { label, url: `${DEMO_SITE}/timeline/${slug}/` }
}

function pageResource(slug: string, label: string): ResourceLink {
  return { label, url: `${DEMO_SITE}/${slug}/` }
}

function externalResource(url: string, label: string): ResourceLink {
  return { label, url, external: true }
}

function peerGapAverageText(yourScore: number, averageScore: number) {
  const gap = averageScore - yourScore
  if (gap > 0) return `${gap} points below average`
  if (gap < 0) return `${Math.abs(gap)} points above average`
  return 'In line with average'
}

function peerGapTopShort(yourScore: number, topQuartile: number) {
  const gap = topQuartile - yourScore
  if (gap > 0) return `${gap} below top quartile`
  if (gap < 0) return `${Math.abs(gap)} above top quartile`
  return 'In line with top quartile'
}

function peerYourScoreColor(yourScore: number, averageScore: number, topQuartile: number) {
  const avgGap = averageScore - yourScore
  const topGap = topQuartile - yourScore
  if (avgGap > 0) return '#a32d2d'
  if (topGap <= 0) return '#1d9e75'
  return undefined
}

const roles: Record<RoleKey, RoleModel> = {
  teacher: {
    label: 'Teacher',
    audience: 'Classroom practice',
    scene: 'Lesson planning table',
    headline: 'Your classroom AI habits are useful, but verification needs to become routine.',
    scoreLabel: 'Readiness',
    score: 55,
    risk: 38,
    motif: 'Verify before pupils see it',
    accent: '#2563eb',
    soft: '#dbeafe',
    ink: '#172554',
    metricA: { label: 'AI Dependency Index', value: '47%', note: 'Feedback drafting is the first pressure point.' },
    metricB: { label: 'Human Oversight Ratio', value: '54%', note: 'Moderate checking, not yet a habit.' },
    priority: 'Redesign one AI-assisted task so pupils must show thinking beyond the first generated answer.',
    nextAction: 'Open verification framework',
    domains: [
      { label: 'Safe adoption', value: 100, tone: 'secure', prompt: 'Assess tools first' },
      { label: 'Human oversight', value: 54, tone: 'practice', prompt: 'Check before use' },
      { label: 'Independent practice', value: 53, tone: 'practice', prompt: 'Try before AI' },
      { label: 'Privacy', value: 72, tone: 'secure', prompt: 'Keep pupil data out' },
      { label: 'Assessment design', value: 46, tone: 'attention', prompt: 'Require thinking evidence' },
      { label: 'AI literacy', value: 82, tone: 'secure', prompt: 'Strong explanation base' },
      { label: 'Safeguarding', value: 48, tone: 'attention', prompt: 'Respond to AI harms' },
      { label: 'Bias & equality', value: 42, tone: 'attention', prompt: 'Check unfair outputs' },
    ],
    focusAreas: [
      {
        label: 'Bias & equality',
        pct: 42,
        summary:
          'AI outputs can be unfair or discriminatory. Build a simple bias spot-check into your verify-before-use routine.',
        likely_impact: [
          'Stereotypes or unfair assumptions may reach pupils unchecked',
          'Equality and safeguarding risks may be missed in everyday AI use',
          'Pupils may not see bias challenged clearly by adults',
        ],
        actions: [
          'Check AI examples for protected characteristics and stereotypes',
          'Add bias review to your normal output verification habit',
          'Use one lesson example to show pupils how AI can be unfair',
        ],
      },
      {
        label: 'Assessment design',
        pct: 46,
        summary:
          'AI rules in assessed work may not be clearly understood or consistently applied. Pupils may be unclear on what is and is not acceptable.',
        likely_impact: [
          'Uneven understanding of permitted AI use in assessed work',
          'Difficulty detecting undisclosed AI assistance',
          'JCQ declaration gaps in controlled conditions',
        ],
        actions: [
          "Read your school's current AI policy on assessed work",
          'Clarify one assignment where AI use must be declared',
          'Model how you check AI-generated content before use',
        ],
      },
      {
        label: 'Safeguarding',
        pct: 48,
        summary:
          'AI-specific harms such as deepfakes, impersonation and biased outputs need to be recognised and reported consistently.',
        likely_impact: [
          'AI-enabled incidents may be treated as ordinary behaviour issues',
          'Pupils may not know when fake media should be reported',
          'Classroom AI use may miss the online safety connection',
        ],
        actions: [
          "Complete your school's AI safeguarding briefing",
          'Know how to report AI-generated harmful content',
          'Discuss deepfakes and impersonation with pupils',
        ],
      },
    ],
    strengths: [
      {
        title: 'Thoughtful assessment of AI tools before use',
        detail: 'safe adoption 100%.',
      },
      {
        title: 'Strong base in explaining AI limits to pupils',
        detail: 'ai literacy 82%.',
      },
    ],
    resources: [
      {
        ...pageResource('for-teachers', 'AI Awareness Day: information for teachers'),
        kicker: 'Teacher CPD',
        description: 'A practical starting point for staff confidence, classroom routines and safe AI use.',
      },
      {
        ...pageResource('ai-confident-award', 'AI Confident Award for schools'),
        kicker: 'School recognition',
        description: 'A visible route for schools that want to evidence responsible AI adoption.',
      },
      {
        ...externalResource(
          'https://www.aqa.org.uk/subjects/computer-science-and-it',
          'AQA AI pathways and qualifications for students',
        ),
        kicker: 'Student qualifications',
        description: 'A next-step signpost for learners who want AI, computing and digital skills routes.',
      },
    ],
    peer: {
      comparisonLabel: 'How you compare to other teachers',
      averageScore: 54,
      topQuartile: 61,
    },
  },
  student: {
    label: 'Student',
    audience: 'Learning habits',
    scene: 'Study skills passport',
    headline: 'You can use AI well, but the next step is proving the thinking is yours.',
    scoreLabel: 'AI skills',
    score: 58,
    risk: 42,
    motif: 'Think first, prompt second',
    accent: '#0f766e',
    soft: '#ccfbf1',
    ink: '#134e4a',
    metricA: { label: 'Independent Thinking', value: '51%', note: 'Try-first habit needs practice.' },
    metricB: { label: 'Verification Skills', value: '63%', note: 'Checks happen when stakes are high.' },
    priority: 'Spend five minutes attempting the work before asking AI for help or explanation.',
    nextAction: 'Start Think First, Prompt Second',
    domains: [
      { label: 'Independent thinking', value: 51, tone: 'practice', prompt: 'Make first attempt' },
      { label: 'Verification', value: 63, tone: 'practice', prompt: 'Check the answer' },
      { label: 'Assessment integrity', value: 47, tone: 'attention', prompt: 'Make it your own' },
      { label: 'Privacy awareness', value: 74, tone: 'secure', prompt: 'Protect identity' },
      { label: 'AI literacy', value: 68, tone: 'secure', prompt: 'Know limits' },
      { label: 'Safeguarding', value: 49, tone: 'attention', prompt: 'Report fake media' },
      { label: 'Bias & fairness', value: 44, tone: 'attention', prompt: 'Spot unfair outputs' },
    ],
    focusAreas: [
      {
        label: 'Bias & fairness',
        pct: 44,
        summary: 'AI can produce unfair or stereotyped answers. Learning to spot this helps you use AI safely and fairly.',
        likely_impact: [
          'Unfair assumptions may appear in answers that sound confident',
          'Schoolwork may repeat stereotypes without you noticing',
        ],
        actions: ['Check one AI answer for stereotypes or unfair language', 'Tell a teacher if an AI answer feels harmful'],
      },
      {
        label: 'Assessment integrity',
        pct: 47,
        summary: 'AI-assisted work still needs to be your thinking, your words and your evidence.',
        likely_impact: ['You may struggle to explain answers if AI did the thinking', 'Teachers may not see what you really understand'],
        actions: ['Rewrite one AI answer in your own words', 'Add a sentence explaining how you checked it'],
      },
      {
        label: 'Safeguarding',
        pct: 49,
        summary: 'Know what to do if you see AI-generated fake images or harmful content shared in school.',
        likely_impact: ['Uncertainty about reporting routes', 'Risk of sharing unverified AI content'],
        actions: ['Tell a trusted adult if you see suspicious AI media', 'Check whether an image could be AI-generated'],
      },
      {
        label: 'Independent thinking',
        pct: 51,
        summary: 'Try the work yourself before asking AI — it builds the thinking schools need to see.',
        actions: ['Spend five minutes on the task before opening AI', 'Write what you tried before asking for help'],
      },
    ],
    strengths: [
      {
        title: 'You protect your identity when using AI tools',
        detail: 'privacy awareness 74%.',
      },
      {
        title: 'You understand that AI can be wrong or biased',
        detail: 'ai literacy 68%.',
      },
    ],
    resources: [
      timelineResource(
        'stop-asking-if-students-should-use-ai-start-asking-how-students-perspective',
        'Stop asking if students should use AI — start asking how',
      ),
      timelineResource(
        'ai-mental-health-student-perspective-student-voice',
        'AI and mental health — a student perspective',
      ),
      timelineResource(
        'the-future-of-ai-through-a-students-perspective',
        "The future of AI through a student's perspective",
      ),
    ],
    peer: {
      comparisonLabel: 'How you compare to other students',
      averageScore: 52,
      topQuartile: 65,
    },
  },
  parent: {
    label: 'Parent',
    audience: 'Home support',
    scene: 'Kitchen table conversation',
    headline: 'Home AI boundaries are forming, but homework oversight needs a simpler ritual.',
    scoreLabel: 'Awareness',
    score: 55,
    risk: 45,
    motif: 'Ask them to explain it back',
    accent: '#9333ea',
    soft: '#f3e8ff',
    ink: '#581c87',
    metricA: { label: 'Homework Oversight', value: '48%', note: 'Explain-in-own-words habit is uneven.' },
    metricB: { label: 'School Partnership', value: '66%', note: 'Good base for shared expectations.' },
    priority: 'Create a simple home agreement for AI-assisted homework and talk through one example together.',
    nextAction: 'Open parent conversation guide',
    domains: [
      { label: 'Awareness', value: 60, tone: 'practice', prompt: 'Know what they use' },
      { label: 'Home AI safety', value: 58, tone: 'practice', prompt: 'Set boundaries' },
      { label: 'Homework oversight', value: 48, tone: 'attention', prompt: 'Explain in own words' },
      { label: 'Balanced AI use', value: 52, tone: 'practice', prompt: 'Try first at home' },
      { label: 'Deepfake awareness', value: 42, tone: 'attention', prompt: 'Know what to do' },
      { label: 'School partnership', value: 66, tone: 'secure', prompt: 'Ask for expectations' },
    ],
    focusAreas: [
      {
        label: 'Homework oversight',
        pct: 48,
        summary: 'Ask your child to explain homework in their own words — not just read AI output back.',
        likely_impact: ['Hard to spot undisclosed AI use', 'Weaker conversation about school expectations'],
        actions: ['Ask one “explain it back” question after homework', 'Check the school AI policy together'],
      },
      {
        label: 'Balanced AI use',
        pct: 52,
        summary: 'Use AI to check or explain after a first attempt, not to produce homework answers for your child.',
        likely_impact: ['Children may miss the thinking practice homework is meant to build', 'Parents may accidentally model over-reliance'],
        actions: ['Try the first step together before opening AI', 'Ask AI for hints or checks rather than a finished answer'],
      },
      {
        label: 'Deepfake awareness',
        pct: 42,
        summary: 'Know what to do if your child sees AI-generated harmful or fake content.',
      },
    ],
    strengths: [
      {
        title: 'You are building a shared picture with school expectations',
        detail: 'school partnership 66%.',
      },
      {
        title: 'You set boundaries for AI use at home',
        detail: 'home AI safety 58%.',
      },
    ],
    resources: [
      timelineResource('parent-tips', 'Parent tips'),
      timelineResource(
        'bbc-bitesize-ai-awareness-day-teaching-resources',
        'BBC Bitesize AI Awareness Day teaching resources',
      ),
      timelineResource('parent-zone', 'Parent Zone'),
    ],
    peer: {
      comparisonLabel: 'How you compare to other parents',
      averageScore: 51,
      topQuartile: 63,
    },
  },
  leader: {
    label: 'School Leader',
    audience: 'Governance view',
    scene: 'Governor evidence pack',
    headline: 'Your foundations are credible; the next leap is consistent practice across the school.',
    scoreLabel: 'DfE alignment',
    score: 67,
    risk: 33,
    motif: 'Turn scores into governance evidence',
    accent: '#475569',
    soft: '#e2e8f0',
    ink: '#0f172a',
    metricA: { label: 'Governance Maturity', value: '61%', note: 'Policy exists, practice varies.' },
    metricB: { label: 'Safeguarding Readiness', value: '72%', note: 'Procedures partly updated.' },
    priority: 'Assign owners, evidence, and review dates to the two weakest domains before the next SLT meeting.',
    nextAction: 'Open policy generator',
    domains: [
      { label: 'Governance', value: 61, tone: 'practice', prompt: 'Assign ownership' },
      { label: 'Safe adoption', value: 57, tone: 'practice', prompt: 'Assess new tools' },
      { label: 'Safeguarding', value: 72, tone: 'secure', prompt: 'Refresh procedures' },
      { label: 'Bias & equality', value: 46, tone: 'attention', prompt: 'Check unfair outputs' },
      { label: 'Privacy', value: 64, tone: 'practice', prompt: 'Check tool approvals' },
      { label: 'Assessment controls', value: 58, tone: 'practice', prompt: 'Evidence JCQ alignment' },
      { label: 'Staff CPD', value: 69, tone: 'secure', prompt: 'Target training' },
      { label: 'Pupil AI literacy', value: 54, tone: 'practice', prompt: 'Build curriculum' },
    ],
    focusAreas: [
      {
        label: 'Bias & equality',
        pct: 46,
        summary: 'Bias checks need to be visible in safeguarding, approved-tool review and classroom guidance.',
        likely_impact: ['Uneven protection for pupils with protected characteristics', 'AI outputs may be trusted without equality review'],
        actions: ['Add a bias-check step to tool approval', 'Brief staff on unfair or stereotyped AI outputs'],
      },
      {
        label: 'Assessment controls',
        pct: 58,
        summary: 'Evidence JCQ alignment and consistent staff understanding of AI in assessed work.',
        likely_impact: ['Uneven departmental practice', 'Governor scrutiny on academic integrity'],
        actions: ['Assign an assessment integrity owner', 'Audit one faculty’s AI declaration practice'],
      },
      {
        label: 'Governance',
        pct: 61,
        summary: 'Policy exists but practice varies — assign owners, evidence and review dates.',
      },
    ],
    strengths: [
      {
        title: 'Safeguarding procedures are partly updated for AI risks',
        detail: 'safeguarding 72%.',
      },
      {
        title: 'Staff readiness for AI CPD is developing well',
        detail: 'staff readiness 69%.',
      },
    ],
    resources: [
      timelineResource('ai-micro-credentials-and-short-courses', 'AI micro-credentials and short courses'),
      timelineResource('beyond-the-holy-grail', 'Beyond the Holy Grail'),
      timelineResource(
        'misinformation-detector-teachers',
        'How good are you at detecting misinformation? The teacher challenge',
      ),
    ],
    peer: {
      comparisonLabel: 'How you compare to similar schools',
      averageScore: 59,
      topQuartile: 72,
    },
  },
  support: {
    label: 'Support Staff',
    audience: 'Operational practice',
    scene: 'Office workflow check',
    headline: 'Everyday AI use is practical; make data rules and reporting routes unmistakable.',
    scoreLabel: 'Readiness',
    score: 64,
    risk: 36,
    motif: 'Know the route before the risk',
    accent: '#b45309',
    soft: '#fef3c7',
    ink: '#78350f',
    metricA: { label: 'Operational Dependency', value: '39%', note: 'AI used mainly for communications.' },
    metricB: { label: 'Data Protection', value: '71%', note: 'Rules known, approval routes less clear.' },
    priority: 'Put approved-tool guidance and reporting routes next to the tasks where AI is most often used.',
    nextAction: 'Open data protection checklist',
    domains: [
      { label: 'AI literacy', value: 62, tone: 'practice', prompt: 'Spot limits' },
      { label: 'Human oversight', value: 59, tone: 'practice', prompt: 'Review before sending' },
      { label: 'Operational dependency', value: 61, tone: 'practice', prompt: 'Keep manual route' },
      { label: 'Data protection', value: 71, tone: 'secure', prompt: 'Know what not to enter' },
      { label: 'Safe adoption', value: 66, tone: 'secure', prompt: 'Check approval' },
      { label: 'Safeguarding awareness', value: 58, tone: 'practice', prompt: 'Know reporting route' },
    ],
    focusAreas: [
      {
        label: 'Safeguarding awareness',
        pct: 58,
        summary: 'Know how to report AI-related safeguarding or data protection concerns using your school route.',
        likely_impact: ['Unclear escalation if AI exposes sensitive information', 'AI-enabled harm may not reach the right safeguarding lead quickly'],
        actions: ['Save the school reporting route where support staff can see it', 'Ask who handles AI-related data or safeguarding incidents'],
      },
      {
        label: 'Data protection',
        pct: 71,
        summary: 'Rules are known but approval routes for AI tools are less clear in daily workflows.',
        likely_impact: ['Accidental use of unapproved tools', 'Sensitive data entered into public AI'],
        actions: ['Post approved-tool list near copiers and desks', 'Use the school reporting route for AI concerns'],
      },
      {
        label: 'Human oversight',
        pct: 59,
        summary: 'Review AI-drafted communications before sending to parents or staff.',
      },
    ],
    strengths: [
      {
        title: 'You know what must not be entered into public AI tools',
        detail: 'data protection 71%.',
      },
      {
        title: 'You check approval routes before adopting new AI tools',
        detail: 'safe adoption 66%.',
      },
    ],
    resources: [
      pageResource('dfe-ai-compliance-checklist', 'DfE AI Compliance Checklist'),
      pageResource('teacher-ai-privacy-guide', 'AI Privacy Guide for Schools'),
      pageResource('teacher-ai-verification-framework', 'Verify Before You Trust Framework'),
    ],
    peer: {
      comparisonLabel: 'How you compare to other support staff',
      averageScore: 58,
      topQuartile: 70,
    },
  },
  public: {
    label: 'Public',
    audience: 'Personal AI use',
    scene: 'Personal AI habits',
    headline: 'Your verification habits are promising; privacy is the place to tighten up.',
    scoreLabel: 'AI readiness',
    score: 69,
    risk: 31,
    motif: 'Use AI without giving too much away',
    accent: '#dc2626',
    soft: '#fee2e2',
    ink: '#7f1d1d',
    metricA: { label: 'Verification', value: '74%', note: 'Strong source-checking habits.' },
    metricB: { label: 'Data & Privacy', value: '57%', note: 'Occasional personal data exposure.' },
    priority: 'Remove names, addresses, health details, and workplace data before using public AI tools.',
    nextAction: 'Open personal AI safety checklist',
    domains: [
      { label: 'Personal AI use', value: 73, tone: 'secure', prompt: 'Useful habits' },
      { label: 'Verification', value: 74, tone: 'secure', prompt: 'Check sources' },
      { label: 'Data & privacy', value: 57, tone: 'practice', prompt: 'Remove details' },
      { label: 'Workplace AI', value: 64, tone: 'practice', prompt: 'Disclose use' },
      { label: 'Emotional & social', value: 76, tone: 'secure', prompt: 'Keep perspective' },
    ],
    focusAreas: [
      {
        label: 'Data & privacy',
        pct: 57,
        summary: 'Remove names, addresses, health details and workplace data before using public AI tools.',
        likely_impact: ['Occasional personal data exposure', 'Unclear what is safe to paste into prompts'],
        actions: ['Strip identifiers before every prompt', 'Use workplace-approved tools for work tasks'],
      },
      {
        label: 'Workplace AI',
        pct: 64,
        summary: 'Disclose AI use when your employer expects transparency on generated content.',
      },
    ],
    strengths: [
      {
        title: 'You keep perspective on emotional and social AI use',
        detail: 'emotional & social 76%.',
      },
      {
        title: 'You check sources before acting on AI output',
        detail: 'verification 74%.',
      },
    ],
    resources: [
      timelineResource('how-does-a-large-language-model-work', 'How Does a Large Language Model Work?'),
      timelineResource('15-ai-buzzwords-teachers-2026', '15 AI Buzzwords Every Teacher Should Know in 2026'),
      timelineResource('misinformation-detector-teachers', 'How good are you at detecting misinformation?'),
    ],
    peer: {
      comparisonLabel: 'How you compare nationally',
      averageScore: 60,
      topQuartile: 75,
    },
  },
}

const roleIcons: Record<RoleKey, typeof GraduationCap> = {
  teacher: GraduationCap,
  student: BookOpenCheck,
  parent: Home,
  leader: School,
  support: ClipboardList,
  public: Users,
}

const dashboardTabs: Array<{ key: DashboardTabKey; label: string; kicker: string }> = [
  { key: 'intro', label: 'Overview', kicker: 'What the benchmark measures · six audiences' },
  { key: 'questions', label: 'Questions & weights', kicker: 'Every question · option scores · audience weights' },
  { key: 'score', label: 'Readiness score', kicker: 'Mock result · readiness bands · cohort context' },
  { key: 'signals', label: 'Key signals', kicker: 'Strengths · dependency index · oversight ratio' },
  { key: 'domains', label: 'Domain breakdown', kicker: 'Nine DfE-aligned domains · focus areas' },
  { key: 'simulator', label: 'Behaviour simulator', kicker: 'Tune habits · watch the score move · modelled' },
  { key: 'quiz', label: 'Spot the risk', kicker: 'Safe or risky? · real classroom scenarios' },
]

/** Shared red → green spectrum for the header stripe and readiness scale segments. */
const READINESS_SCALE_GRADIENT =
  'linear-gradient(90deg, #dc2626 0%, #f59e0b 25%, #eab308 50%, #22c55e 75%, #16a34a 100%)'

const readinessBands = [
  { slug: 'emerging', label: 'Emerging', min: 0, max: 39, color: '#dc2626', short: 'At risk' },
  { slug: 'developing', label: 'Developing', min: 40, max: 59, color: '#f59e0b', short: 'Concern' },
  { slug: 'established', label: 'Established', min: 60, max: 74, color: '#eab308', short: 'Est.' },
  { slug: 'strong', label: 'Strong', min: 75, max: 89, color: '#22c55e', short: 'Str.' },
  { slug: 'leading', label: 'Leading', min: 90, max: 100, color: '#16a34a', short: 'Lead.' },
] as const

function scoreReadinessBand(score: number) {
  const clamped = Math.max(0, Math.min(100, score))
  return readinessBands.find((band) => clamped >= band.min && clamped <= band.max) ?? readinessBands[0]
}

const DEPENDENCY_SCALE_GRADIENT =
  'linear-gradient(90deg, #16a34a 0%, #eab308 50%, #dc2626 100%)'

function parseMetricPercent(value: string) {
  return Math.max(0, Math.min(100, parseInt(value.replace(/[^\d]/g, ''), 10) || 0))
}

function dependencyIndexColor(pct: number) {
  if (pct >= 60) return '#dc2626'
  if (pct >= 35) return '#d97706'
  return '#16a34a'
}

function isDependencyMetric(label: string) {
  return /dependency/i.test(label)
}

function DependencyScaleBar({ value }: { value: number }) {
  return (
    <div
      className="mt-3"
      role="img"
      aria-label={`${value}% on the scale from non-reliant to over-reliant`}
    >
      <div className="relative h-3 overflow-visible rounded-full" style={{ background: DEPENDENCY_SCALE_GRADIENT }}>
        <span
          className="absolute top-1/2 size-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white bg-slate-900 shadow-md"
          style={{ left: `${value}%` }}
          aria-hidden
        />
      </div>
      <div className="mt-1.5 flex justify-between gap-2 text-[0.65rem] font-semibold leading-tight sm:text-xs">
        <span className="text-emerald-700">Non-reliant</span>
        <span className="text-red-700">Over-reliant</span>
      </div>
    </div>
  )
}

function ReadinessScaleBar({ score }: { score: number }) {
  const active = scoreReadinessBand(score)

  return (
    <div>
      <div className="flex gap-1" aria-hidden>
        {readinessBands.map((band) => (
          <span
            key={band.slug}
            className="h-3 flex-1 rounded-sm transition-opacity"
            style={{
              backgroundColor: band.color,
              opacity: band.slug === active.slug ? 1 : 0.22,
            }}
          />
        ))}
      </div>
      <div className="mt-1.5 flex justify-between gap-1 text-[0.62rem] leading-tight" aria-hidden>
        {readinessBands.map((band) => (
          <span
            key={band.slug}
            className="flex-1 text-center font-semibold"
            style={{ color: band.slug === active.slug ? band.color : '#94a3b8' }}
          >
            {band.label}
          </span>
        ))}
      </div>
    </div>
  )
}

const toneStyle: Record<Tone, { bg: string; text: string; border: string; label: string }> = {
  secure: {
    bg: 'bg-emerald-50',
    text: 'text-emerald-800',
    border: 'border-emerald-300',
    label: 'secure',
  },
  practice: {
    bg: 'bg-amber-50',
    text: 'text-amber-800',
    border: 'border-amber-300',
    label: 'practise',
  },
  attention: {
    bg: 'bg-rose-50',
    text: 'text-rose-800',
    border: 'border-rose-300',
    label: 'focus',
  },
}

function PeerComparisonBar({ model }: { model: RoleModel }) {
  const yourScore = model.score
  const { averageScore, topQuartile, comparisonLabel } = model.peer
  const yourColor = peerYourScoreColor(yourScore, averageScore, topQuartile)
  const gapAverage = peerGapAverageText(yourScore, averageScore)
  const gapTopShort = peerGapTopShort(yourScore, topQuartile)

  return (
    <section
      className="rounded-lg border border-slate-200 bg-slate-50"
      style={{ padding: '0.65rem' }}
      aria-label={comparisonLabel}
    >
      <div className="flex flex-wrap items-center justify-between gap-x-2 gap-y-1">
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Cohort context</p>
        <p className="text-[0.7rem] font-medium leading-tight text-slate-600 sm:text-xs">
          {gapAverage} · {gapTopShort}
        </p>
      </div>
      <div
        className="mt-2 grid"
        style={{ gridTemplateColumns: 'repeat(3, minmax(0, 1fr))', gap: '0.4rem' }}
      >
        {[
          { label: 'You', value: yourScore, color: yourColor ?? model.accent },
          { label: 'Nat. avg', value: averageScore, color: '#64748b' },
          { label: 'Top quartile', value: topQuartile, color: '#16a34a' },
        ].map((item) => (
          <div
            key={item.label}
            className="min-w-0 rounded-md bg-white ring-1 ring-slate-200"
            style={{ padding: '0.42rem 0.5rem' }}
          >
            <p
              className="truncate font-semibold uppercase tracking-wide text-slate-500"
              style={{ fontSize: '0.66rem', lineHeight: 1.15 }}
            >
              {item.label}
            </p>
            <p
              className="font-semibold tabular-nums"
              style={{ color: item.color, fontSize: '1.15rem', lineHeight: 1, marginTop: '0.2rem' }}
            >
              {item.value}%
            </p>
          </div>
        ))}
      </div>
      <div className="relative mt-2 rounded-full bg-slate-200" style={{ height: '0.38rem' }} aria-hidden>
        <span
          className="absolute left-0 top-0 h-full rounded-full"
          style={{ width: `${yourScore}%`, backgroundColor: yourColor ?? model.accent }}
        />
        <span
          className="absolute top-1/2 h-3 w-px -translate-y-1/2 bg-slate-500"
          style={{ left: `${averageScore}%` }}
        />
        <span
          className="absolute top-1/2 h-3 w-px -translate-y-1/2 bg-emerald-600"
          style={{ left: `${topQuartile}%` }}
        />
      </div>
      <div className="mt-1 flex justify-between text-[0.6rem] font-semibold text-slate-500">
        <span>0</span>
        <span>100</span>
      </div>
    </section>
  )
}

function CoreSummary({ model, icon: Icon }: { model: RoleModel; icon: typeof GraduationCap }) {
  const activeBand = scoreReadinessBand(model.score)

  return (
    <section
      className="relative overflow-hidden rounded-lg border border-slate-200 bg-white p-4 pt-5 shadow-sm sm:p-5 sm:pt-6"
      aria-label="Result summary"
    >
      <div
        className="absolute left-0 top-0 h-2 w-full"
        style={{ background: READINESS_SCALE_GRADIENT }}
        aria-hidden
      />

      <div className="mt-1 flex items-start justify-between gap-3">
        <div className="min-w-0">
          <p
            className="text-xs font-semibold uppercase tracking-wide"
            style={{ color: model.accent }}
          >
            {model.label} · {model.audience}
          </p>
          <div className="mt-2 flex flex-wrap items-end gap-x-3 gap-y-1">
            <p
              className="w-full text-sm font-semibold uppercase tracking-wide sm:w-auto"
              style={{ color: model.accent }}
            >
              {model.scoreLabel}
            </p>
            <p
              className="text-5xl font-semibold tabular-nums leading-none"
              style={{ color: activeBand.color }}
            >
              {model.score}
            </p>
            <p className="pb-1 text-sm font-semibold text-slate-500">/100</p>
            <p className="pb-1 text-[1.4625rem] font-semibold leading-tight" style={{ color: activeBand.color }}>
              {activeBand.label}
            </p>
          </div>
          <p className="mt-2 text-sm font-medium" style={{ color: model.ink }}>
            <span className="tabular-nums font-semibold">{model.risk}%</span> behavioural risk
          </p>
        </div>
        <span
          className="grid size-11 shrink-0 place-items-center rounded-lg text-white shadow-sm"
          style={{ backgroundColor: model.accent }}
        >
          <Icon className="size-6" />
        </span>
      </div>

      <div
        className="mt-4 rounded-lg border border-slate-200/80 p-3 sm:p-4"
        style={{ backgroundColor: model.soft }}
      >
        <p className="text-sm font-semibold leading-relaxed" style={{ color: model.ink }}>
          {model.motif}
        </p>
      </div>

      <div className="mt-4">
        <ReadinessScaleBar score={model.score} />
      </div>

      <div className="mt-3">
        <PeerComparisonBar model={model} />
      </div>
    </section>
  )
}

function DomainTiles({ domains }: { domains: Domain[] }) {
  const chartWidth = 900
  const chartHeight = 250
  const plotTop = 24
  const plotBottom = 214
  const plotLeft = 52
  const plotRight = 878
  const target = 70
  const average = Math.round(domains.reduce((total, domain) => total + domain.value, 0) / domains.length)
  const xFor = (index: number) =>
    plotLeft + (index * (plotRight - plotLeft)) / Math.max(1, domains.length - 1)
  const yFor = (value: number) =>
    plotBottom - (value / 100) * (plotBottom - plotTop)
  const points = domains.map((domain, index) => `${xFor(index)},${yFor(domain.value)}`).join(' ')
  const areaPoints = `${plotLeft},${plotBottom} ${points} ${plotRight},${plotBottom}`
  const tickerFor = (label: string) =>
    label
      .split(/\s|&/)
      .filter(Boolean)
      .map((word) => word[0])
      .join('')
      .slice(0, 3)
      .toUpperCase()

  return (
    <section className="benchmark-market" aria-label="Domain readiness index">
      <div className="benchmark-market__header">
        <div>
          <p className="benchmark-market__eyebrow">Readiness index</p>
          <div className="benchmark-market__quote">
            <strong>{average}.00</strong>
            <span className={average >= target ? 'is-up' : 'is-down'}>
              {average >= target ? '+' : ''}{average - target}.00 vs target
            </span>
          </div>
        </div>
        <div className="benchmark-market__legend" aria-label="Chart legend">
          <span><i className="is-secure" /> Secure</span>
          <span><i className="is-practice" /> Building</span>
          <span><i className="is-attention" /> Attention</span>
        </div>
      </div>

      <div className="benchmark-market__chart-wrap">
        <svg
          className="benchmark-market__chart"
          viewBox={`0 0 ${chartWidth} ${chartHeight}`}
          role="img"
          aria-label={`Domain readiness ranges from ${Math.min(...domains.map((domain) => domain.value))}% to ${Math.max(...domains.map((domain) => domain.value))}%, against a 70% target.`}
        >
          <defs>
            <linearGradient id="readiness-market-fill" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stopColor="#2563eb" stopOpacity="0.28" />
              <stop offset="100%" stopColor="#2563eb" stopOpacity="0.02" />
            </linearGradient>
            <filter id="readiness-market-shadow" x="-20%" y="-20%" width="140%" height="140%">
              <feDropShadow dx="0" dy="3" stdDeviation="4" floodColor="#0f172a" floodOpacity="0.16" />
            </filter>
          </defs>

          {[100, 75, 50, 25, 0].map((tick) => (
            <g key={tick}>
              <line x1={plotLeft} x2={plotRight} y1={yFor(tick)} y2={yFor(tick)} className="benchmark-market__grid-line" />
              <text x="10" y={yFor(tick) + 4} className="benchmark-market__axis-label">{tick}</text>
            </g>
          ))}

          <line x1={plotLeft} x2={plotRight} y1={yFor(target)} y2={yFor(target)} className="benchmark-market__target-line" />
          <text x={plotRight - 2} y={yFor(target) - 9} textAnchor="end" className="benchmark-market__target-label">
            TARGET 70
          </text>
          <polygon points={areaPoints} fill="url(#readiness-market-fill)" />
          <polyline points={points} className="benchmark-market__trend-line" filter="url(#readiness-market-shadow)" />

          {domains.map((domain, index) => (
            <g key={domain.label}>
              <circle cx={xFor(index)} cy={yFor(domain.value)} r="8" className={`benchmark-market__point benchmark-market__point--${domain.tone}`} />
              <text x={xFor(index)} y={yFor(domain.value) - 14} textAnchor="middle" className="benchmark-market__point-value">
                {domain.value}
              </text>
              <text x={xFor(index)} y={plotBottom + 25} textAnchor="middle" className="benchmark-market__ticker-label">
                {tickerFor(domain.label)}
              </text>
            </g>
          ))}
        </svg>
      </div>

      <div className="benchmark-market__ticker-grid">
        {domains.map((domain) => {
          const style = toneStyle[domain.tone]
          const gap = domain.value - target
          return (
            <article key={domain.label} className={`benchmark-market__ticker benchmark-market__ticker--${domain.tone}`}>
              <div className="benchmark-market__ticker-topline">
                <span className="benchmark-market__ticker-code">{tickerFor(domain.label)}</span>
                <span className={`benchmark-market__ticker-change ${gap >= 0 ? 'is-up' : 'is-down'}`}>
                  {gap >= 0 ? '+' : ''}{gap}
                </span>
              </div>
              <h3>{domain.label}</h3>
              <div className="benchmark-market__ticker-quote">
                <strong>{domain.value}%</strong>
                <span className={`${style.bg} ${style.text}`}>{style.label}</span>
              </div>
              <p>{domain.prompt}</p>
            </article>
          )
        })}
      </div>
    </section>
  )
}

function focusSeverity(pct: number) {
  if (pct <= 25) return 'critical' as const
  if (pct <= 40) return 'high' as const
  return 'moderate' as const
}

function FocusGuidanceAccordion({
  label,
  children,
  defaultOpen = false,
}: {
  label: string
  children: React.ReactNode
  defaultOpen?: boolean
}) {
  return (
    <details className="airb__results-accordion airb__leader-accordion airb__focus-guidance-accordion" open={defaultOpen}>
      <summary>{label}</summary>
      <div className="airb__focus-guidance-body">{children}</div>
    </details>
  )
}

function matchingDomainTone(area: FocusArea, domains: Domain[]) {
  return domains.find((domain) => domain.label.toLowerCase() === area.label.toLowerCase())?.tone ?? 'attention'
}

function FocusAreaCard({ area, domains }: { area: FocusArea; domains: Domain[] }) {
  const severity = focusSeverity(area.pct)
  const domainTone = matchingDomainTone(area, domains)
  const tone = toneStyle[domainTone]
  const hasGuidance = Boolean(area.likely_impact?.length || area.actions?.length)

  return (
    <div className={`airb__focus-card airb__teacher-focus-card airb__focus-card--${severity}`}>
      <div className="airb__focus-card-header">
        <h4 className="airb__focus-card-title">{area.label}</h4>
      </div>
      <div className="airb__focus-score-row">
        <p className="airb__focus-card-score">{area.pct}%</p>
        <span className={`airb__focus-badge ${tone.bg} ${tone.text}`}>{tone.label}</span>
      </div>
      <p className="airb__focus-card-summary">{area.summary}</p>
      {hasGuidance ? (
        <FocusGuidanceAccordion label="View classroom impact">
          {area.likely_impact?.length ? (
            <div className="airb__focus-practice airb__teacher-focus-practice">
              <div className="airb__focus-practice-title">In practice this means</div>
              {area.likely_impact.map((item) => (
                <div key={item} className="airb__teacher-focus-impact">
                  {item}
                </div>
              ))}
            </div>
          ) : null}
          {area.actions?.map((item, index) => (
            <div key={item} className="airb__teacher-action-row">
              <span className="airb__teacher-action-num">{index + 1}</span>
              <span className="airb__teacher-action-text">{item}</span>
            </div>
          ))}
        </FocusGuidanceAccordion>
      ) : null}
    </div>
  )
}

function StrengthCard({
  strengths,
  heading = "What you're doing well",
}: {
  strengths: Strength[]
  heading?: string
}) {
  if (!strengths.length) return null

  const parseStrengthDetail = (detail?: string) => {
    const match = detail?.match(/^(.*?)\s+(\d+)%\.?$/)
    if (!match) return { label: detail ?? '', value: null }
    return {
      label: match[1].trim(),
      value: Number(match[2]),
    }
  }

  return (
    <div className="demo-airb airb__teacher-strength-card">
      <h3 className="airb__benchmark-card-heading">{heading}</h3>
      <div className="airb__teacher-strength-grid">
        {strengths.map((strength) => {
          const detail = parseStrengthDetail(strength.detail)
          return (
            <section key={strength.title} className="airb__teacher-strength-row">
              <div className="airb__teacher-strength-heading">
                <span className="airb__teacher-strength-tick" aria-hidden="true">
                  ✓
                </span>
                <p className="airb__teacher-strength-title">{strength.title}</p>
              </div>
              {detail.value !== null ? (
                <div className="airb__teacher-strength-score">
                  <p className="airb__teacher-strength-value">{detail.value}%</p>
                  <p className="airb__teacher-strength-detail">{detail.label}</p>
                </div>
              ) : strength.detail ? (
                <p className="airb__teacher-strength-detail">{strength.detail}</p>
              ) : null}
            </section>
          )
        })}
      </div>
    </div>
  )
}

function PriorityFocusStack({
  areas,
  domains,
  heading = 'Lowest domain drivers',
}: {
  areas: FocusArea[]
  domains: Domain[]
  heading?: string
}) {
  return (
    <div className="demo-airb">
      <h3 className="airb__leader-section-label">
        <span className="airb__lbl-long">{heading}</span>
      </h3>
      <p className="airb__focus-stack-intro">
        These cards expand the lowest domain scores above into the likely classroom impact and next action.
      </p>
      <div className="airb__leader-focus-stack">
        {areas.map((area) => (
          <FocusAreaCard key={area.label} area={area} domains={domains} />
        ))}
      </div>
    </div>
  )
}

function SignalsPanel({ model }: { model: RoleModel }) {
  return (
    <div className="grid gap-4">
      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <p className="text-sm font-semibold uppercase tracking-wide" style={{ color: model.accent }}>
          {model.scene}
        </p>
        <h2 className="mt-2 text-xl font-semibold tracking-normal text-slate-950 sm:text-2xl">{model.headline}</h2>

        <div className="mt-4">
          <StrengthCard strengths={model.strengths} />
        </div>
      </section>

      <div className="grid gap-4 md:grid-cols-2">
        {[model.metricA, model.metricB].map((metric) => {
          const metricPct = parseMetricPercent(metric.value)
          const metricColor = isDependencyMetric(metric.label)
            ? dependencyIndexColor(metricPct)
            : model.accent
          return (
            <section key={metric.label} className="rounded-lg border border-slate-200 bg-white p-4">
              <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{metric.label}</p>
              <p
                className="mt-2 text-4xl font-semibold tabular-nums leading-none"
                style={{ color: metricColor }}
              >
                {metric.value}
              </p>
              {isDependencyMetric(metric.label) ? <DependencyScaleBar value={metricPct} /> : null}
              <p className="mt-3 text-sm leading-relaxed text-slate-600">{metric.note}</p>
            </section>
          )
        })}
      </div>
    </div>
  )
}

function DomainsPanel({ model }: { model: RoleModel }) {
  return (
    <div className="grid gap-4">
      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <h3 className="text-sm font-semibold uppercase tracking-wide text-slate-500">
          Domain breakdown & key signals
        </h3>
        <div className="mt-3">
          <DomainTiles domains={model.domains} />
        </div>
      </section>

      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <PriorityFocusStack areas={model.focusAreas} domains={model.domains} />
      </section>
    </div>
  )
}

/* ---------- Questions & weights ---------- */

/** Risk score 0–3 → chip colour (0 = safest, 3 = highest risk). */
function riskChipColor(score: number) {
  if (score <= 0) return { bg: '#ecfdf5', fg: '#047857' }
  if (score === 1) return { bg: '#fefce8', fg: '#a16207' }
  if (score === 2) return { bg: '#fff7ed', fg: '#c2410c' }
  return { bg: '#fef2f2', fg: '#b91c1c' }
}

function QuestionRow({ q }: { q: AuditQuestion }) {
  return (
    <article className="rounded-lg border border-slate-200 bg-white p-3">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <p className="min-w-0 flex-1 text-sm font-semibold leading-snug text-slate-900">{q.text}</p>
        <span className="rounded bg-slate-100 px-1.5 py-0.5 text-[0.6rem] font-bold uppercase tracking-wide text-slate-600">
          {weightScaleLabel(q)}
        </span>
      </div>
      <p className="mt-1 text-[0.65rem] font-medium uppercase tracking-wide text-slate-400">
        {q.domainLabel} · {q.section}
      </p>
      {q.type === 'slider' ? (
        <p className="mt-2 text-xs text-slate-600">
          Continuous 0–100% slider — Human Oversight Ratio™. Bands: 0–10% critical · 11–25% high
          reliance · 26–50% moderate · 51%+ strong oversight.
        </p>
      ) : (
        <div className="mt-2 flex flex-wrap gap-1.5">
          {q.options.map((opt) => {
            const chip = riskChipColor(opt.score)
            return (
              <span
                key={`${q.id}-${opt.value}`}
                className="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium"
                style={{ backgroundColor: chip.bg, color: chip.fg }}
              >
                {opt.label}
                <strong className="tabular-nums">risk {opt.score}</strong>
              </span>
            )
          })}
        </div>
      )}
    </article>
  )
}

function QuestionsStage({
  role,
  onPickAudience,
}: {
  role: RoleKey
  onPickAudience: (key: AuditAudienceKey) => void
}) {
  const auditKey = auditKeyForRole(role)
  const audit = AUDITS[auditKey]
  const totalQ = questionCount(audit)
  const sections = questionsBySection(audit)

  return (
    <div className="grid gap-4">
      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
          Live AIRB model · from the published benchmark
        </p>
        <h2 className="mt-1 text-xl font-semibold tracking-tight text-slate-950 sm:text-2xl">
          Questions and option risk scores
        </h2>
        <p className="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
          Pulled from the live AI Risk &amp; Readiness Benchmark™. Each radio answer carries a{' '}
          <strong className="font-semibold text-slate-800">risk score 0–3</strong>. Domain risk =
          mean of its question scores ÷ 3 × 100. Readiness is the inverse. The teacher oversight
          slider is the Human Oversight Ratio™ — share of AI output modified before use.
        </p>
        <p className="mt-2 text-sm">
          <a
            href={LIVE_BENCHMARK_URL}
            target="_blank"
            rel="noreferrer"
            className="font-semibold text-teal-700 underline underline-offset-2 hover:text-teal-900"
          >
            aiawarenessday.co.uk/timeline/ai-risk-readiness-benchmark
          </a>
        </p>
        <div className="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
          {(Object.keys(AUDITS) as AuditAudienceKey[]).map((key) => (
            <div key={key} className="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
              <p className="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
                {AUDITS[key].label}
              </p>
              <p className="mt-0.5 text-2xl font-semibold tabular-nums text-slate-950">
                {questionCount(AUDITS[key])}
              </p>
              <p className="text-[0.65rem] text-slate-500">
                questions
                {AUDIENCE_WEIGHTS[key] != null ? ` · school blend ×${AUDIENCE_WEIGHTS[key]}` : ''}
              </p>
            </div>
          ))}
        </div>
        <div className="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
          <p className="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
            Nine DfE-aligned domains
          </p>
          <div className="mt-2 flex flex-wrap gap-1.5">
            {Object.values(DOMAIN_LABELS).map((label) => (
              <span
                key={label}
                className="rounded-md bg-white px-2 py-1 text-[0.7rem] font-medium text-slate-700 ring-1 ring-slate-200"
              >
                {label}
              </span>
            ))}
          </div>
        </div>
      </section>

      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <h3 className="text-sm font-semibold text-slate-900">Shared scales (option → risk 0–3)</h3>
        <div className="mt-3 grid gap-3 lg:grid-cols-3">
          {WEIGHT_SCALES.map((scale) => (
            <div key={scale.id} className="rounded-lg border border-slate-200 bg-slate-50 p-3">
              <p className="text-xs font-semibold text-slate-800">{scale.title}</p>
              <p className="mt-0.5 text-[0.7rem] text-slate-500">{scale.note}</p>
              <div className="mt-2 flex flex-wrap gap-1.5">
                {scale.options.map((opt) => {
                  const chip = riskChipColor(opt.score)
                  return (
                    <span
                      key={opt.label}
                      className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[0.7rem] font-medium"
                      style={{ backgroundColor: chip.bg, color: chip.fg }}
                    >
                      {opt.label} <strong className="tabular-nums">{opt.score}</strong>
                    </span>
                  )
                })}
              </div>
            </div>
          ))}
        </div>
      </section>

      <div className="flex flex-wrap gap-1.5">
        {(Object.keys(AUDITS) as AuditAudienceKey[]).map((key) => {
          const active = key === auditKey
          return (
            <button
              key={key}
              type="button"
              onClick={() => onPickAudience(key)}
              className={`rounded-lg border px-3 py-1.5 text-xs font-semibold ${
                active
                  ? 'border-slate-900 bg-slate-900 text-white'
                  : 'border-slate-300 bg-white text-slate-600 hover:border-slate-400'
              }`}
            >
              {AUDITS[key].label}
            </button>
          )
        })}
      </div>

      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <div className="flex flex-wrap items-end justify-between gap-2">
          <div>
            <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: audit.color }}>
              {audit.label} audit
            </p>
            <h3 className="mt-1 text-lg font-semibold text-slate-950">{totalQ} questions</h3>
            <p className="mt-1 max-w-2xl text-sm text-slate-600">{audit.blurb}</p>
          </div>
        </div>

        <div className="mt-4 grid gap-4">
          {sections.map((section) => (
            <div key={section.name}>
              <div className="mb-2 flex items-center gap-2">
                <span
                  className="h-2 w-2 rounded-full"
                  style={{ backgroundColor: audit.color }}
                  aria-hidden
                />
                <h4 className="text-sm font-semibold text-slate-900">{section.name}</h4>
                <span className="text-[0.65rem] font-medium uppercase tracking-wide text-slate-400">
                  {section.questions[0]?.domainLabel}
                </span>
              </div>
              <div className="grid gap-2">
                {section.questions.map((q) => (
                  <QuestionRow key={q.id} q={q} />
                ))}
              </div>
            </div>
          ))}
        </div>
      </section>
    </div>
  )
}

/* ---------- Intro stage ---------- */

const BENCHMARK_FOCUS: Partial<Record<RoleKey, string>> = {
  teacher: 'Reliance, data entry, verification',
  student: 'Critical thinking, prompt literacy',
  parent: 'Safety awareness, home usage',
  leader: 'Compliance, governance, policy',
  support: 'Operational dependency, data protection',
  public: 'Personal AI use, verification habits',
}

const AUDIT_CONTRAST: Array<{ traditional: string; behavioural: string }> = [
  { traditional: 'Do you have an AI policy?', behavioural: 'Do staff actually follow it?' },
  { traditional: 'Do you provide training?', behavioural: 'Has training reduced risky behaviour?' },
  { traditional: 'Do you allow AI tools?', behavioural: 'How dependent are people becoming on them?' },
  { traditional: 'Are safeguards documented?', behavioural: 'Are people actively bypassing safeguards?' },
  { traditional: 'Is governance in place?', behavioural: 'Where is data exposure actually occurring?' },
]

function IntroStage({
  role,
  onPickRole,
}: {
  role: RoleKey
  onPickRole: (key: RoleKey) => void
}) {
  return (
    <div className="grid gap-4">
      <section className="rounded-lg border border-slate-200 bg-white p-5">
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
          AI Risk &amp; Readiness Benchmark™ · free for UK schools
        </p>
        <h2 className="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">
          Do you actually know how AI is changing{' '}
          <span style={{ color: '#dc2626' }}>behaviour</span> in your school community?
        </h2>
        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-slate-600">
          Most AI audits only measure <strong className="font-semibold text-slate-800">adoption</strong> —
          the tech, the policies, the infrastructure. They completely miss{' '}
          <strong className="font-semibold text-slate-800">exposure</strong> — how dependent
          teachers and students are becoming on these tools, and where the actual risks lie. The
          benchmark measures behavioural risk across nine DfE-aligned domains and produces two
          signature data points: the <strong className="font-semibold text-slate-800">AI Dependency Index™</strong>{' '}
          and the <strong className="font-semibold text-slate-800">DfE Alignment Score</strong>.
        </p>
        <div className="mt-4">
          <ReadinessScaleBar score={roles[role].score} />
        </div>
      </section>

      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <h3 className="text-sm font-semibold text-slate-900">
          Traditional AI audits vs the behavioural risk approach
        </h3>
        <div className="mt-3 grid gap-1.5">
          {AUDIT_CONTRAST.map((row) => (
            <div key={row.traditional} className="grid gap-1.5 sm:grid-cols-2">
              <p className="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-500 ring-1 ring-slate-200">
                {row.traditional}
              </p>
              <p className="rounded-md bg-teal-50 px-3 py-2 text-xs font-medium text-teal-900 ring-1 ring-teal-200">
                {row.behavioural}
              </p>
            </div>
          ))}
        </div>
        <p className="mt-3 text-xs leading-relaxed text-slate-500">
          Focusing on behavioural risk shows leaders exactly where confidence outpaces competence —
          and where to target interventions.
        </p>
      </section>

      <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        {(Object.keys(roles) as RoleKey[]).map((key) => {
          const RoleIcon = roleIcons[key]
          const roleModel = roles[key]
          const band = scoreReadinessBand(roleModel.score)
          const active = key === role
          return (
            <button
              key={key}
              type="button"
              onClick={() => onPickRole(key)}
              className={`rounded-lg border bg-white p-4 text-left transition-shadow hover:shadow-md ${
                active ? 'border-slate-900 ring-1 ring-slate-900' : 'border-slate-200'
              }`}
              aria-pressed={active}
            >
              <div className="flex items-center justify-between gap-2">
                <span
                  className="grid size-9 place-items-center rounded-lg text-white"
                  style={{ backgroundColor: roleModel.accent }}
                >
                  <RoleIcon className="size-5" />
                </span>
                <span
                  className="text-2xl font-semibold tabular-nums leading-none"
                  style={{ color: band.color }}
                >
                  {roleModel.score}
                </span>
              </div>
              <p className="mt-3 text-sm font-semibold text-slate-950">
                {roleModel.label} Benchmark
              </p>
              <p className="mt-1 text-xs leading-relaxed text-slate-500">
                {BENCHMARK_FOCUS[key] ?? roleModel.audience}
              </p>
              <p className="mt-2 text-xs font-semibold" style={{ color: band.color }}>
                {band.label} · {roleModel.risk}% behavioural risk
              </p>
            </button>
          )
        })}
      </div>
    </div>
  )
}

/* ---------- Behaviour simulator ---------- */

type SimBehaviourKey = 'verify' | 'challenge' | 'attemptFirst' | 'withoutAi' | 'privacy' | 'literacy'
type SimValues = Record<SimBehaviourKey, number>

const SIM_BEHAVIOURS: Array<{
  key: SimBehaviourKey
  label: string
  hint: string
  domain: string
  group: 'oversight' | 'dependency' | 'privacy' | 'literacy'
}> = [
  {
    key: 'verify',
    label: 'Verify AI outputs against a reliable source',
    hint: '0 = never check · 100 = always check',
    domain: 'Human Oversight',
    group: 'oversight',
  },
  {
    key: 'challenge',
    label: 'Challenge or push back on AI recommendations',
    hint: '0 = accept everything · 100 = interrogate everything',
    domain: 'Human Oversight',
    group: 'oversight',
  },
  {
    key: 'attemptFirst',
    label: 'Attempt tasks yourself before reaching for AI',
    hint: '0 = AI first, always · 100 = own attempt first',
    domain: 'AI Dependency',
    group: 'dependency',
  },
  {
    key: 'withoutAi',
    label: 'Could work effectively for a week with no AI',
    hint: '0 = could not cope · 100 = no problem',
    domain: 'AI Dependency',
    group: 'dependency',
  },
  {
    key: 'privacy',
    label: 'Keep personal / pupil data out of AI tools',
    hint: '0 = paste anything in · 100 = strictly anonymised',
    domain: 'Privacy & Data Protection',
    group: 'privacy',
  },
  {
    key: 'literacy',
    label: 'Understand hallucinations and tool limits',
    hint: '0 = unaware · 100 = confident and current',
    domain: 'AI Literacy',
    group: 'literacy',
  },
]

const SIM_PRESETS: Array<{ id: string; label: string; values: SimValues }> = [
  {
    id: 'hands-off',
    label: 'Hands-off adopter',
    values: { verify: 15, challenge: 10, attemptFirst: 20, withoutAi: 25, privacy: 40, literacy: 30 },
  },
  {
    id: 'busy',
    label: 'Busy pragmatist',
    values: { verify: 45, challenge: 40, attemptFirst: 50, withoutAi: 45, privacy: 60, literacy: 55 },
  },
  {
    id: 'balanced',
    label: 'Balanced practitioner',
    values: { verify: 65, challenge: 60, attemptFirst: 65, withoutAi: 60, privacy: 80, literacy: 70 },
  },
  {
    id: 'cautious',
    label: 'Cautious verifier',
    values: { verify: 90, challenge: 85, attemptFirst: 85, withoutAi: 80, privacy: 95, literacy: 90 },
  },
]

const SIM_DEFAULT: SimValues = SIM_PRESETS[1].values

/** Mirrors the live benchmark scoring: readiness = mean of behaviour values,
 *  dependency index inverts the dependency answers, oversight ratio averages
 *  the oversight answers. */
function computeSimResult(values: SimValues) {
  const all = SIM_BEHAVIOURS.map((b) => values[b.key])
  const readiness = Math.round(all.reduce((a, b) => a + b, 0) / all.length)
  const depIndex = Math.round(100 - (values.attemptFirst + values.withoutAi) / 2)
  const oversight = Math.round((values.verify + values.challenge) / 2)
  return { readiness, risk: 100 - readiness, depIndex, oversight }
}

function oversightBandFor(v: number) {
  if (v <= 10) return { label: 'Critical reliance', color: '#dc2626' }
  if (v <= 25) return { label: 'High reliance', color: '#ea580c' }
  if (v <= 50) return { label: 'Moderate oversight', color: '#d97706' }
  return { label: 'Strong human oversight', color: '#16a34a' }
}

const SIM_GRID_COLS = 8
const SIM_GRID_ROWS = 6
const SIM_GRID_CELLS = SIM_GRID_COLS * SIM_GRID_ROWS

/** Visual metaphor: every cell is an AI output this term. Green = checked by a
 *  human, red = unchecked and habit-forming, grey = unchecked. */
function ClassroomGrid({ oversight, depIndex }: { oversight: number; depIndex: number }) {
  const checkedCells = Math.round((oversight / 100) * SIM_GRID_CELLS)
  const riskyCells = Math.round(((SIM_GRID_CELLS - checkedCells) * depIndex) / 100)

  return (
    <div className="rounded-lg border border-slate-200 bg-white">
      <div className="border-b border-slate-200 bg-slate-50 px-3 py-2">
        <p className="text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">
          A term of AI outputs · each square is one AI-assisted task
        </p>
      </div>
      <div className="flex items-center justify-center px-4 py-4">
        <div
          className="inline-grid grid-cols-8 gap-1"
          role="img"
          aria-label={`${checkedCells} of ${SIM_GRID_CELLS} AI outputs human-checked, ${riskyCells} high-risk`}
        >
          {Array.from({ length: SIM_GRID_CELLS }).map((_, i) => {
            const isChecked = i < checkedCells
            const isRisky = !isChecked && i < checkedCells + riskyCells
            return (
              <span
                key={i}
                className="size-[clamp(1.1rem,2.2vw,1.7rem)] rounded-sm transition-colors duration-200"
                style={{
                  backgroundColor: isChecked ? '#16a34a' : isRisky ? '#dc2626' : '#cbd5e1',
                  opacity: isChecked ? 0.9 : isRisky ? 0.85 : 0.7,
                }}
              />
            )
          })}
        </div>
      </div>
      <div className="flex flex-wrap gap-3 border-t border-slate-200 px-3 py-2 text-[0.65rem] font-medium text-slate-500">
        <span className="flex items-center gap-1.5">
          <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#16a34a]" /> Human-checked
        </span>
        <span className="flex items-center gap-1.5">
          <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#dc2626]" /> Unchecked + dependent
        </span>
        <span className="flex items-center gap-1.5">
          <span className="inline-block h-2.5 w-2.5 rounded-sm bg-[#cbd5e1]" /> Unchecked
        </span>
      </div>
    </div>
  )
}

function SimulatorStage({
  values,
  onChange,
}: {
  values: SimValues
  onChange: (values: SimValues) => void
}) {
  const result = computeSimResult(values)
  const band = scoreReadinessBand(result.readiness)
  const ovBand = oversightBandFor(result.oversight)
  const depColor = dependencyIndexColor(result.depIndex)
  const activePreset = SIM_PRESETS.find((p) =>
    SIM_BEHAVIOURS.every((b) => p.values[b.key] === values[b.key]),
  )

  return (
    <div className="grid gap-4">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <p className="max-w-xl text-sm leading-relaxed text-slate-600">
          Recreates the benchmark's two signature metrics: the AI Dependency Index™ (reliance,
          verification and independent-practice habits) and the Human Oversight Ratio™ (0–10%
          critical · 11–25% high reliance · 26–50% moderate · 51%+ strong oversight). Drag the
          sliders or load a persona and watch the scores move.
        </p>
        <span className="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-[0.65rem] font-bold uppercase tracking-wider text-amber-700">
          Modelled
        </span>
      </div>

      <div className="grid gap-4 lg:grid-cols-[1.05fr_0.95fr]">
        <div className="grid content-start gap-3">
          <div className="flex flex-wrap gap-1.5">
            {SIM_PRESETS.map((preset) => (
              <button
                key={preset.id}
                type="button"
                onClick={() => onChange({ ...preset.values })}
                className={`rounded-lg border px-2.5 py-1.5 text-[0.7rem] font-semibold transition-colors ${
                  activePreset?.id === preset.id
                    ? 'border-slate-900 bg-slate-900 text-white'
                    : 'border-slate-300 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-900'
                }`}
              >
                {preset.label}
              </button>
            ))}
          </div>

          <div className="grid gap-4 rounded-lg border border-slate-200 bg-white p-4">
            {SIM_BEHAVIOURS.map((behaviour) => (
              <label key={behaviour.key} className="block">
                <div className="flex items-baseline justify-between gap-2">
                  <span className="text-xs font-semibold text-slate-800">{behaviour.label}</span>
                  <span className="text-sm font-semibold tabular-nums text-slate-950">
                    {values[behaviour.key]}%
                  </span>
                </div>
                <input
                  type="range"
                  min={0}
                  max={100}
                  step={5}
                  value={values[behaviour.key]}
                  onChange={(e) =>
                    onChange({ ...values, [behaviour.key]: Number(e.target.value) })
                  }
                  className="mt-1.5 w-full accent-slate-900"
                  aria-label={behaviour.label}
                />
                <div className="mt-0.5 flex justify-between text-[0.62rem] text-slate-400">
                  <span>{behaviour.hint}</span>
                  <span className="font-semibold uppercase tracking-wide">{behaviour.domain}</span>
                </div>
              </label>
            ))}
          </div>
        </div>

        <div className="grid content-start gap-3">
          <div className="grid gap-2 sm:grid-cols-3">
            <div className="rounded-lg border border-slate-200 bg-white p-3">
              <p className="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
                Readiness
              </p>
              <p
                className="mt-1 text-4xl font-semibold tabular-nums leading-none"
                style={{ color: band.color }}
              >
                {result.readiness}
              </p>
              <p className="mt-1 text-xs font-semibold" style={{ color: band.color }}>
                {band.label}
              </p>
            </div>
            <div className="rounded-lg border border-slate-200 bg-white p-3">
              <p className="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
                AI Dependency Index™
              </p>
              <p
                className="mt-1 text-4xl font-semibold tabular-nums leading-none"
                style={{ color: depColor }}
              >
                {result.depIndex}
              </p>
              <DependencyScaleBar value={result.depIndex} />
            </div>
            <div className="rounded-lg border border-slate-200 bg-white p-3">
              <p className="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
                Oversight Ratio™
              </p>
              <p
                className="mt-1 text-4xl font-semibold tabular-nums leading-none"
                style={{ color: ovBand.color }}
              >
                {result.oversight}
              </p>
              <p className="mt-1 text-xs font-semibold" style={{ color: ovBand.color }}>
                {ovBand.label}
              </p>
            </div>
          </div>

          <div>
            <ReadinessScaleBar score={result.readiness} />
          </div>

          <ClassroomGrid oversight={result.oversight} depIndex={result.depIndex} />

          <div
            className="rounded-lg border border-l-4 bg-white px-3 py-2.5 text-sm leading-relaxed text-slate-600"
            style={{ borderColor: '#e2e8f0', borderLeftColor: band.color }}
          >
            {result.depIndex >= 60
              ? 'High dependency with weak checking habits — the riskiest profile in the benchmark. Rebuilding the "attempt first" habit moves the score fastest.'
              : result.oversight < 40
                ? 'Outputs are flowing through unchecked. Verification is the single highest-leverage behaviour in the model.'
                : result.readiness >= 75
                  ? 'Strong profile — AI used as a tool with human judgement kept in the loop. This is what the benchmark rewards.'
                  : 'A workable middle ground. Nudging verification and first-attempt habits above 70% lifts this into the Strong band.'}
          </div>

          <details className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500">
            <summary className="cursor-pointer text-[0.7rem] font-semibold uppercase tracking-wider text-slate-700">
              Model transparency
            </summary>
            <div className="mt-2 space-y-1.5 font-mono text-[0.68rem] leading-relaxed">
              <p>readiness = mean(all six behaviours)</p>
              <p>dependency = 100 − mean(attemptFirst, withoutAi)</p>
              <p>oversight = mean(verify, challenge)</p>
              <p className="font-sans">
                Simplified from the live audit (which weights per-question option values per
                audience). Illustrative — six sliders stand in for the full question set.
              </p>
            </div>
          </details>
        </div>
      </div>
    </div>
  )
}

/* ---------- Spot-the-risk quiz ---------- */

type QuizScenario = {
  id: string
  text: string
  risky: boolean
  domain: string
  why: string
}

const QUIZ_SCENARIOS: QuizScenario[] = [
  {
    id: 'send-report',
    text: 'Pasting a pupil\u2019s SEND report into a free chatbot to draft a summary.',
    risky: true,
    domain: 'Privacy & Data Protection',
    why: 'Identifiable and sensitive pupil data entering an external AI tool — the highest-severity behaviour in the benchmark.',
  },
  {
    id: 'starter-check',
    text: 'Using AI to draft a lesson starter, then checking the facts against the textbook.',
    risky: false,
    domain: 'Human Oversight',
    why: 'AI drafts, human verifies against a reliable source. Exactly the oversight habit the benchmark rewards.',
  },
  {
    id: 'feedback-unread',
    text: 'Sending AI-written pupil feedback home without reading it first.',
    risky: true,
    domain: 'Human Oversight',
    why: 'Unreviewed output going straight to families removes the human from the loop entirely.',
  },
  {
    id: 'quiz-edit',
    text: 'Asking AI to suggest quiz questions, then editing them for your class.',
    risky: false,
    domain: 'AI Dependency',
    why: 'The teacher stays the author — AI accelerates, the human adapts and owns the result.',
  },
  {
    id: 'names-marking',
    text: 'Marking with a consumer chatbot, pupil names left in the work.',
    risky: true,
    domain: 'Privacy & Data Protection',
    why: 'Names make it personal data. Anonymise first, or use an approved tool with a data agreement.',
  },
  {
    id: 'trust-lesson',
    text: 'Running a class discussion on when AI answers can\u2019t be trusted.',
    risky: false,
    domain: 'AI Literacy',
    why: 'Actively building pupils\u2019 verification habits — literacy work the benchmark scores highly.',
  },
  {
    id: 'week-unreviewed',
    text: 'Letting AI plan a full week of lessons unreviewed because you\u2019re behind.',
    risky: true,
    domain: 'AI Dependency',
    why: 'Time pressure is how dependency forms: volume plus zero review compounds the risk.',
  },
  {
    id: 'anonymised-errors',
    text: 'Anonymising pupil work before using AI to spot common errors.',
    risky: false,
    domain: 'Privacy & Data Protection',
    why: 'Data minimisation done right — the insight without the personal data.',
  },
]

function QuizStage() {
  const [order] = useState(() =>
    [...QUIZ_SCENARIOS].sort(() => Math.random() - 0.5),
  )
  const [round, setRound] = useState(0)
  const [picked, setPicked] = useState<boolean | null>(null)
  const [score, setScore] = useState(0)
  const [attempts, setAttempts] = useState(0)

  const scenario = order[round % order.length]
  const answered = picked !== null
  const correct = answered && picked === scenario.risky

  const choose = (guessRisky: boolean) => {
    if (answered) return
    setPicked(guessRisky)
    setAttempts((n) => n + 1)
    if (guessRisky === scenario.risky) setScore((n) => n + 1)
  }

  const next = () => {
    setPicked(null)
    setRound((n) => n + 1)
  }

  return (
    <div className="mx-auto grid w-full max-w-2xl gap-4 text-center">
      <div>
        <h2 className="text-2xl font-semibold tracking-tight text-slate-950">Safe or risky?</h2>
        <p className="mt-1 text-sm text-slate-500">
          Score: <span className="font-semibold tabular-nums text-slate-900">{score} / {attempts}</span>
          {' · '}scenarios drawn from the audit questions
        </p>
      </div>

      <div className="rounded-lg border border-slate-200 bg-white p-5">
        <p className="text-[0.65rem] font-semibold uppercase tracking-wider text-slate-400">
          Scenario {attempts + (answered ? 0 : 1)}
        </p>
        <p className="mt-2 text-lg font-medium leading-relaxed text-slate-900">{scenario.text}</p>
      </div>

      <div className="grid gap-2 sm:grid-cols-2">
        {([false, true] as const).map((guessRisky) => {
          const isPick = answered && picked === guessRisky
          const isAnswer = answered && scenario.risky === guessRisky
          return (
            <button
              key={String(guessRisky)}
              type="button"
              disabled={answered}
              onClick={() => choose(guessRisky)}
              className={`rounded-lg border-2 px-4 py-4 text-base font-semibold transition-colors ${
                isAnswer
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-800'
                  : isPick
                    ? 'border-rose-400 bg-rose-50 text-rose-700'
                    : answered
                      ? 'border-slate-200 bg-white text-slate-400'
                      : 'border-slate-300 bg-white text-slate-800 hover:border-slate-500'
              }`}
            >
              {guessRisky ? 'Risky' : 'Safe'}
            </button>
          )
        })}
      </div>

      {answered ? (
        <div
          className={`rounded-lg border p-4 text-left ${
            correct ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50'
          }`}
          role="status"
        >
          <p className={`text-sm font-bold ${correct ? 'text-emerald-800' : 'text-rose-700'}`}>
            {correct ? 'Correct' : 'Not quite'} — this one is {scenario.risky ? 'risky' : 'safe'}.
          </p>
          <p className="mt-1 text-sm leading-relaxed text-slate-700">{scenario.why}</p>
          <p className="mt-2 text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">
            Domain · {scenario.domain}
          </p>
          <button
            type="button"
            onClick={next}
            className="mt-3 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white"
          >
            Next scenario
          </button>
        </div>
      ) : null}
    </div>
  )
}

export function BenchmarkDashboardsDemo() {
  const [role, setRole] = useState<RoleKey>('teacher')
  const [tab, setTab] = useState<DashboardTabKey>('intro')
  const [simValues, setSimValues] = useState<SimValues>({ ...SIM_DEFAULT })
  const model = roles[role]
  const Icon = roleIcons[role]
  const slideIndex = Math.max(0, dashboardTabs.findIndex((item) => item.key === tab))
  const activeSlide = dashboardTabs[slideIndex] ?? dashboardTabs[0]
  const weakestDomain = model.domains.reduce((weakest, domain) =>
    domain.value < weakest.value ? domain : weakest,
  )
  const simResult = useMemo(() => computeSimResult(simValues), [simValues])

  const go = (direction: number) => {
    const next = (slideIndex + direction + dashboardTabs.length) % dashboardTabs.length
    setTab(dashboardTabs[next].key)
  }

  const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
      void document.documentElement.requestFullscreen()
    } else {
      void document.exitFullscreen()
    }
  }

  useEffect(() => {
    document.documentElement.classList.add('airb-deck-mode')
    document.body.classList.add('airb-deck-mode')

    const onKeyDown = (event: KeyboardEvent) => {
      const tag = (event.target as HTMLElement | null)?.tagName
      if (tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA') return

      if (event.key === 'ArrowRight' || event.key === 'PageDown' || event.key === ' ') {
        event.preventDefault()
        go(1)
      } else if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
        event.preventDefault()
        go(-1)
      } else if (event.key >= '1' && event.key <= String(dashboardTabs.length)) {
        setTab(dashboardTabs[Number(event.key) - 1].key)
      } else if (event.key.toLowerCase() === 'f') {
        toggleFullscreen()
      }
    }

    window.addEventListener('keydown', onKeyDown)
    return () => {
      window.removeEventListener('keydown', onKeyDown)
      document.documentElement.classList.remove('airb-deck-mode')
      document.body.classList.remove('airb-deck-mode')
    }
  }, [slideIndex])

  return (
    <main className="airb-deck">
      <div className="airb-deck__atmosphere" aria-hidden="true">
        <i className="airb-deck__orb airb-deck__orb--warm" />
        <i className="airb-deck__orb airb-deck__orb--cool" />
        <i className="airb-deck__grid-texture" />
      </div>

      <header className="airb-deck__header">
        <AnimatePresence mode="wait">
          <motion.div
            key={activeSlide.key}
            className="airb-deck__header-copy"
            initial={{ opacity: 0, y: 6 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -6 }}
            transition={{ duration: 0.2 }}
          >
            <p className="airb-deck__kicker">{activeSlide.kicker}</p>
            <h1 className="airb-deck__title">{activeSlide.label}</h1>
          </motion.div>
        </AnimatePresence>
        <div className="airb-deck__header-actions">
          <span className="airb-deck__count" aria-label={`Slide ${slideIndex + 1} of ${dashboardTabs.length}`}>
            <strong>{String(slideIndex + 1).padStart(2, '0')}</strong>
            <i />
            <span>{String(dashboardTabs.length).padStart(2, '0')}</span>
          </span>
          <span className="airb-deck__status">
            {model.score}/100 · {scoreReadinessBand(model.score).label}
          </span>
          <nav className="airb-deck__dots" aria-label="Dashboard slides">
            {dashboardTabs.map((item, index) => (
              <button
                key={item.key}
                type="button"
                className={`airb-deck__dot${index === slideIndex ? ' is-active' : ''}`}
                onClick={() => setTab(item.key)}
                aria-label={item.label}
                aria-current={index === slideIndex ? 'true' : undefined}
              />
            ))}
          </nav>
        </div>
      </header>

      <div className="airb-deck__progress" aria-hidden="true">
        <i style={{ transform: `scaleX(${(slideIndex + 1) / dashboardTabs.length})` }} />
      </div>

      <div className="airb-deck__stage">
        <aside className="airb-deck__rail airb-deck__rail--left" aria-label="Audience filters">
          <div className="airb-deck__brand">
            <p>Behavioural AI benchmark</p>
            <h2>Teacher dependency simulator</h2>
          </div>
          <div className="airb-deck__rail-block">
            <p className="airb-deck__rail-label">Audience</p>
            <div className="airb-deck__role-list" role="tablist" aria-label="Benchmark role">
              {(Object.keys(roles) as RoleKey[]).map((key) => {
                const RoleIcon = roleIcons[key]
                const roleModel = roles[key]
                const active = key === role
                return (
                  <button
                    key={key}
                    type="button"
                    role="tab"
                    aria-selected={active}
                    className={`airb-deck__role${active ? ' is-active' : ''}`}
                    onClick={() => setRole(key)}
                  >
                    <RoleIcon aria-hidden="true" />
                    <span>{roleModel.label}</span>
                    <strong>{roleModel.score}</strong>
                  </button>
                )
              })}
            </div>
          </div>
          <div className="airb-deck__rail-summary">
            <p className="airb-deck__rail-label">Current profile</p>
            <strong>
              {model.score}
              <small>/100</small>
            </strong>
            <span>{model.risk}% behavioural risk</span>
            <p>{model.motif}</p>
          </div>
          <Link to="/" className="airb-deck__back-link">
            ← All demos
          </Link>
        </aside>

        <section className="airb-deck__canvas" aria-live="polite">
          <div className="airb-deck__canvas-scroll">
            <AnimatePresence mode="wait">
              <motion.div
                key={activeSlide.key}
                className="airb-deck__panel"
                role="tabpanel"
                aria-label={activeSlide.label}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -10 }}
                transition={{ duration: 0.22 }}
              >
                {tab === 'intro' ? (
                  <IntroStage
                    role={role}
                    onPickRole={(key) => {
                      setRole(key)
                      setTab('questions')
                    }}
                  />
                ) : null}
                {tab === 'questions' ? (
                  <QuestionsStage
                    role={role}
                    onPickAudience={(key) =>
                      setRole(key === 'support_staff' ? 'support' : (key as RoleKey))
                    }
                  />
                ) : null}
                {tab === 'score' ? <CoreSummary model={model} icon={Icon} /> : null}
                {tab === 'signals' ? <SignalsPanel model={model} /> : null}
                {tab === 'domains' ? <DomainsPanel model={model} /> : null}
                {tab === 'simulator' ? (
                  <SimulatorStage values={simValues} onChange={setSimValues} />
                ) : null}
                {tab === 'quiz' ? <QuizStage /> : null}
              </motion.div>
            </AnimatePresence>
          </div>
        </section>

        <aside className="airb-deck__rail airb-deck__rail--right" aria-label="Slide context">
          {tab === 'intro' ? (
            <>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">What it measures</p>
                <p className="airb-deck__rail-copy">
                  Behaviour, not tools — oversight, dependency, privacy and literacy across eight
                  DfE-aligned domains.
                </p>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">How to drive</p>
                <p className="airb-deck__rail-copy">
                  Pick an audience card, then ← → through the deck. Next slide shows every question
                  and its weights.
                </p>
              </div>
            </>
          ) : null}

          {tab === 'questions' ? (
            <>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Within an audit</p>
                <p className="airb-deck__rail-copy">
                  readiness = mean(all answer scores). Each option carries a fixed 0–100 weight.
                </p>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">School blend</p>
                <p className="airb-deck__rail-copy">
                  Leader ×1.4 · Teacher ×1.2 · Student ×1.0 · Parent ×0.9
                </p>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Tags</p>
                <p className="airb-deck__rail-copy">
                  Dep questions feed the dependency index. Oversight questions feed the oversight
                  ratio.
                </p>
              </div>
            </>
          ) : null}

          {tab === 'simulator' ? (
            <>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Live result</p>
                <article className="airb-deck__signal">
                  <span>Readiness</span>
                  <strong>{simResult.readiness}/100</strong>
                  <p>{scoreReadinessBand(simResult.readiness).label}</p>
                </article>
                <article className="airb-deck__signal">
                  <span>AI Dependency Index™</span>
                  <strong>{simResult.depIndex}</strong>
                  <p>{oversightBandFor(simResult.oversight).label}</p>
                </article>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Try this</p>
                <p className="airb-deck__rail-copy">
                  Load “Hands-off adopter”, then raise only the verify slider — watch oversight
                  rescue the grid before the score follows.
                </p>
              </div>
            </>
          ) : null}

          {tab === 'quiz' ? (
            <>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Why these scenarios</p>
                <p className="airb-deck__rail-copy">
                  Each one maps to a real audit question. The risky ones are the behaviours that
                  drag the readiness score down hardest.
                </p>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Severity order</p>
                <p className="airb-deck__rail-copy">
                  Pupil data in AI tools ranks above unchecked outputs, which rank above
                  convenience habits.
                </p>
              </div>
            </>
          ) : null}

          {tab === 'score' ? (
            <>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Band</p>
                <p className="airb-deck__rail-copy">
                  {scoreReadinessBand(model.score).label} · {model.risk}% behavioural risk
                </p>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Peer context</p>
                <p className="airb-deck__rail-copy">
                  {peerGapAverageText(model.score, model.peer.averageScore)} vs {model.peer.comparisonLabel}
                </p>
              </div>
            </>
          ) : null}

          {tab === 'signals' ? (
            <>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Priority action</p>
                <p className="airb-deck__rail-copy">{model.priority}</p>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Next step</p>
                <p className="airb-deck__rail-copy">{model.nextAction}</p>
              </div>
            </>
          ) : null}

          {tab === 'domains' ? (
            <>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Weakest domain</p>
                <article className="airb-deck__signal">
                  <span>{weakestDomain.label}</span>
                  <strong>{weakestDomain.value}%</strong>
                  <p>{weakestDomain.prompt}</p>
                </article>
              </div>
              <div className="airb-deck__rail-block">
                <p className="airb-deck__rail-label">Priority action</p>
                <p className="airb-deck__rail-copy">{model.priority}</p>
              </div>
            </>
          ) : null}

          <button type="button" className="airb-deck__fullscreen" onClick={toggleFullscreen}>
            <Maximize2 aria-hidden="true" />
            Fullscreen
          </button>
        </aside>
      </div>

      <footer className="airb-deck__footer">
        <p>
          AIRB · ← → navigate · 1–{dashboardTabs.length} jump · F fullscreen · {model.label.toLowerCase()} ·{' '}
          {activeSlide.label.toLowerCase()}
        </p>
        <div>
          <button type="button" onClick={() => go(-1)}>
            Prev
          </button>
          <button type="button" onClick={() => go(1)}>
            Next
          </button>
        </div>
      </footer>
      <p className="airb-deck__prototype">
        Prototype only · mock result data · WordPress benchmark remains untouched
      </p>
    </main>
  )
}

