import {
  ArrowRight,
  BookOpenCheck,
  ClipboardList,
  Download,
  FileText,
  GraduationCap,
  Home,
  LockKeyhole,
  MessageCircleQuestion,
  School,
  Users,
} from 'lucide-react'
import { useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'

import { CertificatePreview } from '@/components/certificate/CertificatePreview'
import {
  buildCertificateData,
  exportCertificatePdf,
  makeCertificateId,
} from '@/lib/certificate'
import { getBenchmarkCertificateCopy, type BenchmarkCertificateRole } from '@/lib/benchmark-certificate-copy'
import {
  assessCertificateEvidence,
  EVIDENCE_PATHWAYS,
  EVIDENCE_THEMES,
  SCORE_THRESHOLD,
  type EvidenceTheme,
} from '@/lib/certificate-evidence'

import '@/styles/certificate.css'
import '@/styles/focus-areas.css'

type RoleKey = 'teacher' | 'student' | 'parent' | 'leader' | 'support' | 'public'
type DashboardTabKey = 'overview' | 'progress' | 'resources'
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
  journey: string[]
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

const breakingNowResource: ResourceLink = {
  ...timelineResource('how-does-a-large-language-model-work', 'How Does a Large Language Model Work?'),
  kicker: 'Breaking now',
  description: 'The top timeline explainer to help staff and students understand what sits behind AI answers.',
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
    journey: ['Audit complete', 'Verification ready', 'Responsible practitioner', 'AI champion'],
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
    journey: ['Aware learner', 'Independent attempt', 'Verification habit', 'AI study mentor'],
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
    journey: ['Aware at home', 'Homework routine', 'Safety conversations', 'School partnership'],
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
    journey: ['Emerging', 'Developing', 'Established', 'Leading'],
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
    journey: ['AI aware', 'Approved tools', 'Data confident', 'Safe workflow'],
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
    journey: ['Aware user', 'Privacy reset', 'Verification habit', 'Confident practice'],
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

const dashboardTabs: Array<{ key: DashboardTabKey; label: string }> = [
  { key: 'overview', label: 'Overview' },
  { key: 'progress', label: 'Progress & certificate' },
  { key: 'resources', label: 'Resources' },
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

const supportOptions = [
  {
    slug: 'whole_school_cpd',
    label: 'I want CPD',
    description: 'Training matched to the risk areas in this audit.',
  },
  {
    slug: 'teacher_activity_day',
    label: 'I want classroom resources',
    description: 'Lesson activities, prompts and verification routines.',
  },
  {
    slug: 'whole_school_benchmark',
    label: 'I want my school to run the benchmark',
    description: 'Build a picture across staff, students, parents and leaders.',
  },
]

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

function RoleChips({
  role,
  onRole,
}: {
  role: RoleKey
  onRole: (role: RoleKey) => void
}) {
  return (
    <div
      className="flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      role="tablist"
      aria-label="Benchmark role"
    >
      {(Object.keys(roles) as RoleKey[]).map((key) => {
        const Icon = roleIcons[key]
        const active = role === key
        const model = roles[key]
        return (
          <button
            key={key}
            type="button"
            role="tab"
            aria-selected={active}
            aria-label={`Show ${model.label} dashboard`}
            onClick={() => onRole(key)}
            className={`inline-flex min-h-12 shrink-0 items-center gap-2 rounded-full border px-3 py-2 text-sm font-semibold transition ${
              active ? 'bg-white shadow-sm' : 'bg-white/60 hover:bg-white'
            }`}
            style={{
              borderColor: active ? model.accent : '#d8ddd8',
              boxShadow: active ? `0 0 0 2px ${model.soft}` : undefined,
            }}
          >
            <span
              className="grid size-7 place-items-center rounded-full text-white"
              style={{ backgroundColor: model.accent }}
            >
              <Icon className="size-4" />
            </span>
            <span className="text-slate-950">{model.label}</span>
            <span className="tabular-nums text-slate-500">{model.score}</span>
          </button>
        )
      })}
    </div>
  )
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
      className="sticky top-0 z-20 relative overflow-hidden rounded-lg border border-slate-200 bg-white p-4 pt-5 shadow-sm sm:p-5 sm:pt-6"
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

function DashboardSubTabs({
  tab,
  onTab,
}: {
  tab: DashboardTabKey
  onTab: (tab: DashboardTabKey) => void
}) {
  return (
    <div
      className="flex gap-1 overflow-x-auto border-b border-slate-200 pb-px [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
      role="tablist"
      aria-label="Result sections"
    >
      {dashboardTabs.map((item) => {
        const active = tab === item.key
        return (
          <button
            key={item.key}
            type="button"
            role="tab"
            aria-selected={active}
            onClick={() => onTab(item.key)}
            className={`min-h-12 shrink-0 snap-start border-b-2 px-4 py-2.5 text-sm font-semibold transition ${
              active
                ? 'border-slate-950 text-slate-950'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            }`}
          >
            {item.label}
          </button>
        )
      })}
    </div>
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

function GuidanceCtaCard({ model }: { model: RoleModel }) {
  const scrollToFollowUp = () => {
    document.getElementById('benchmark-follow-up')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  return (
    <article className="demo-airb airb__leader-cta-card">
      <h4 className="airb__leader-cta-title">Need more guidance</h4>
      <p className="airb__leader-cta-body">{model.priority}</p>
      <button type="button" className="airb__btn airb__btn--premium airb__leader-cta-btn" onClick={scrollToFollowUp}>
        Request support
      </button>
    </article>
  )
}

function OverviewPanel({ model }: { model: RoleModel }) {
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

        <div className="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{model.metricA.label}</p>
          {(() => {
            const metricPct = parseMetricPercent(model.metricA.value)
            const metricColor = isDependencyMetric(model.metricA.label)
              ? dependencyIndexColor(metricPct)
              : model.accent
            return (
              <>
                <p
                  className="mt-1 text-3xl font-semibold tabular-nums leading-none sm:text-4xl"
                  style={{ color: metricColor }}
                >
                  {model.metricA.value}
                </p>
                {isDependencyMetric(model.metricA.label) ? (
                  <DependencyScaleBar value={metricPct} />
                ) : null}
              </>
            )
          })()}
          <p className="mt-2 text-sm leading-relaxed text-slate-600">{model.metricA.note}</p>
        </div>

        <div className="mt-4">
          <h3 className="text-sm font-semibold uppercase tracking-wide text-slate-500">Domain breakdown & key signals</h3>
          <div className="mt-3">
            <DomainTiles domains={model.domains} />
          </div>
        </div>

        <div className="mt-4">
          <PriorityFocusStack areas={model.focusAreas} domains={model.domains} />
        </div>
      </section>

      <GuidanceCtaCard model={model} />
    </div>
  )
}

function ProgressPanel({ model, roleKey }: { model: RoleModel; roleKey: RoleKey }) {
  const currentIndex = Math.min(1, model.journey.length - 1)
  const retakeTarget = Math.min(model.score + 10, 92)
  const weakestDomain = model.domains.reduce((weakest, domain) =>
    domain.value < weakest.value ? domain : weakest,
  )

  const progressSteps = [
    {
      title: model.journey[0] ?? 'Audit complete',
      body: 'First stamp earned for finishing the audit.',
    },
    {
      title: model.journey[1] ?? 'Practice challenge',
      body: `Improve ${weakestDomain.label.toLowerCase()} with a one-week habit.`,
    },
    {
      title: model.journey[2] ?? 'Retake evidence',
      body: `Return and reach ${retakeTarget}% to show improvement.`,
    },
    {
      title: model.journey[3] ?? 'Certificate unlock',
      body: 'Generate a shareable certificate once progress is evidenced.',
    },
  ].map((step, index) => {
    const status = index <= currentIndex ? 'unlocked' : index === currentIndex + 1 ? 'active' : 'locked'
    return { ...step, status }
  })

  return (
    <div className="grid gap-4">
      <section className="rounded-lg border border-slate-200 bg-white p-4">
        <div className="flex flex-wrap items-end justify-between gap-3">
          <div>
            <p className="text-sm font-semibold uppercase tracking-wide" style={{ color: model.accent }}>
              Progress passport
            </p>
            <h3 className="mt-1 text-lg font-semibold tracking-normal text-slate-950">From audit to evidence</h3>
          </div>
          <p className="rounded-md bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
            {currentIndex + 1} of {progressSteps.length} stamped
          </p>
        </div>

        <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
          <div
            className="h-full rounded-full"
            style={{
              width: `${((currentIndex + 1) / progressSteps.length) * 100}%`,
              backgroundColor: model.accent,
            }}
          />
        </div>

        <div className="benchmark-passport-grid mt-3">
          {progressSteps.map((step, index) => {
            const unlocked = step.status === 'unlocked'
            const active = step.status === 'active'
            return (
              <section
                key={step.title}
                className="rounded-lg border border-slate-200 p-3"
                style={{ backgroundColor: active ? model.soft : unlocked ? '#f0fdf4' : '#f8fafc' }}
              >
                <div className="flex items-center gap-2">
                  <span
                    className="grid size-7 shrink-0 place-items-center rounded-full text-xs font-semibold"
                    style={{
                      backgroundColor: unlocked ? model.accent : '#fff',
                      boxShadow: `inset 0 0 0 1px ${active ? model.accent : '#cbd5e1'}`,
                      color: unlocked ? '#fff' : active ? model.accent : '#64748b',
                    }}
                  >
                    {unlocked ? '✓' : index + 1}
                  </span>
                  <p className="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-500">
                    {unlocked ? 'Stamped' : active ? 'Next' : 'Locked'}
                  </p>
                </div>
                <h4 className="mt-2 text-sm font-semibold leading-tight text-slate-950">{step.title}</h4>
                <p className="mt-1 text-xs leading-relaxed text-slate-600">{step.body}</p>
              </section>
            )
          })}
        </div>
      </section>

      <CertificatePanel model={model} roleKey={roleKey} />
    </div>
  )
}

function ResourcesPanel({ model }: { model: RoleModel }) {
  if (!model.resources.length) {
    return <p className="text-sm leading-relaxed text-slate-600">No further reading links for this role in the demo yet.</p>
  }

  return (
    <div className="demo-airb airb__resources-panel">
      <section className="airb__leader-help-support airb__benchmark-help-support">
        <div className="airb__resources-header">
          <p className="airb__leader-section-label">CPD for teachers and students</p>
          <p className="airb__resources-intro">
            Suggested next steps after the audit, kept separate from the follow-up request form below.
          </p>
        </div>
        <ul className="airb__results-resource-links airb__leader-resource-links airb__results-resource-links--cards">
          {model.resources.map((link) => (
            <li key={link.url} className="airb__results-resource-item">
              <ResourceCard link={link} />
            </li>
          ))}
        </ul>
      </section>

      <section className="airb__breaking-resource" aria-labelledby="airb-breaking-now-title">
        <p className="airb__leader-section-label" id="airb-breaking-now-title">
          Breaking now
        </p>
        <ResourceCard link={breakingNowResource} featured />
      </section>
    </div>
  )
}

function ResourceCard({ link, featured = false }: { link: ResourceLink; featured?: boolean }) {
  return (
    <a
      className={`airb__results-resource-card${featured ? ' airb__results-resource-card--featured' : ''}`}
      href={link.url}
      {...(link.external
        ? { target: '_blank', rel: 'noopener noreferrer' }
        : {})}
    >
      {link.image ? (
        <span className="airb__resource-link-media">
          <img className="airb__resource-link-thumb" src={link.image} alt="" loading="lazy" decoding="async" />
        </span>
      ) : (
        <span className="airb__resource-link-media airb__resource-link-media--icon" aria-hidden="true" />
      )}
      <span className="airb__resource-link-body">
        {link.kicker ? <span className="airb__resource-link-kicker">{link.kicker}</span> : null}
        <span className="airb__resource-link-label">{link.label}</span>
        {link.description ? <span className="airb__resource-link-description">{link.description}</span> : null}
      </span>
      <ArrowRight className="airb__resource-link-arrow" aria-hidden="true" />
    </a>
  )
}

function CertificatePanel({ model, roleKey }: { model: RoleModel; roleKey: RoleKey }) {
  const certRef = useRef<HTMLDivElement>(null)
  const [certificateId] = useState(makeCertificateId)
  const [generatingCertificate, setGeneratingCertificate] = useState(false)
  const [certificateError, setCertificateError] = useState<string | null>(null)
  const [theme, setTheme] = useState<EvidenceTheme | ''>('')
  const [action, setAction] = useState('')
  const [change, setChange] = useState('')
  const [link, setLink] = useState('')
  const [unlocked, setUnlocked] = useState(false)
  const roleCopy = getBenchmarkCertificateCopy(roleKey as BenchmarkCertificateRole)
  const scoreEligible = model.score >= SCORE_THRESHOLD
  const assessment = useMemo(
    () => assessCertificateEvidence(roleKey, theme, action, change, link, model.score),
    [action, change, link, model.score, roleKey, theme],
  )
  const canUnlock = scoreEligible && assessment.can_unlock && !unlocked

  const certificateData = useMemo(() => {
    const demoName =
      roleKey === 'teacher'
        ? 'Demo Teacher'
        : roleKey === 'student'
          ? 'Demo Student'
          : roleKey === 'parent'
            ? 'Demo Parent'
            : roleKey === 'leader'
              ? 'Demo Leader'
              : roleKey === 'support'
                ? 'Demo Support Staff'
                : 'Demo Participant'

    const data = buildCertificateData({
      teacherName: demoName,
      schoolName: '',
      participationDate: new Date().toISOString().slice(0, 10),
      schoolLogoUrl: null,
      certificateId,
      involvedAs: roleKey === 'teacher' ? 'teacher' : '',
    })

    return {
      ...data,
      copy: {
        headlinePrimary: roleCopy.headlinePrimary,
        affiliationPrefix: 'from',
        body: roleCopy.body,
      },
    }
  }, [certificateId, roleCopy.body, roleCopy.headlinePrimary, roleKey])

  const downloadCertificate = async () => {
    const el = certRef.current
    if (!el) return
    setGeneratingCertificate(true)
    setCertificateError(null)
    try {
      const safeRole = model.label.toLowerCase().replace(/[^a-z0-9]+/g, '-')
      await exportCertificatePdf(el, `ai-readiness-${safeRole}-progress-certificate.pdf`)
    } catch (e) {
      setCertificateError(e instanceof Error ? e.message : 'Could not generate certificate PDF.')
    } finally {
      setGeneratingCertificate(false)
    }
  }

  const qualityClass =
    assessment.quality_tier === 'strong_evidence'
      ? 'is-strong'
      : assessment.quality_tier === 'likely_valid'
        ? 'is-valid'
        : assessment.quality_tier === 'needs_manual_review'
          ? 'is-review'
          : 'is-weak'

  return (
    <section className="rounded-lg border border-slate-200 bg-white p-4">
      <div className="benchmark-certificate-layout">
        <div className="grid content-start gap-3">
          <div className="rounded-lg border border-slate-200 bg-slate-50 p-2.5">
            <div className="flex items-start gap-3">
              <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-white ring-1 ring-slate-200">
                <FileText className="size-5" style={{ color: model.accent }} />
              </span>
              <div className="min-w-0">
                <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: model.accent }}>
                  Certificate
                </p>
                <h3 className="mt-1 text-lg font-semibold leading-tight text-slate-950">
                  {roleCopy.headlinePrimary}
                </h3>
                <p className="mt-1 text-xs leading-relaxed text-slate-600">
                  {unlocked
                    ? 'Certificate unlocked in this demo session.'
                    : 'Submit one verified action linked to an AI Awareness Day theme.'}
                </p>
              </div>
            </div>
          </div>

          <div className="benchmark-certificate-stats">
            <div className="rounded-lg border border-slate-200 bg-white p-2.5">
              <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Current</p>
              <p className="mt-1 text-xl font-semibold tabular-nums leading-none text-slate-950">{model.score}%</p>
            </div>
            <div className="rounded-lg border border-slate-200 bg-white p-2.5">
              <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Need</p>
              <p className="mt-1 text-xl font-semibold tabular-nums leading-none text-slate-950">{SCORE_THRESHOLD}%</p>
            </div>
            <div className="rounded-lg border border-slate-200 bg-white p-2.5">
              <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Gap</p>
              <p className="mt-1 text-xl font-semibold tabular-nums leading-none text-slate-950">
                {scoreEligible ? 'Met' : `+${SCORE_THRESHOLD - model.score}`}
              </p>
            </div>
          </div>

          <p
            className={`benchmark-certificate-gate text-xs leading-relaxed ${scoreEligible ? 'is-open' : 'is-blocked'}`}
          >
            {scoreEligible
              ? 'Reach the benchmark score threshold and complete one of the evidence options below.'
              : `Reach at least ${SCORE_THRESHOLD}% on the benchmark before unlocking.`}
          </p>

          <fieldset className="benchmark-certificate-themes border-0 p-0" disabled={!scoreEligible || unlocked}>
            <legend className="text-xs font-semibold text-slate-700">Choose one theme</legend>
            <div className="benchmark-certificate-theme-grid mt-2">
              {EVIDENCE_THEMES.map((item) => (
                <label key={item.slug} className="benchmark-certificate-theme-option">
                  <input
                    type="radio"
                    name={`demo-cert-theme-${roleKey}`}
                    value={item.slug}
                    checked={theme === item.slug}
                    onChange={() => setTheme(item.slug)}
                  />
                  <span>{item.label}</span>
                </label>
              ))}
            </div>
          </fieldset>

          <label className="benchmark-certificate-reflection block text-xs font-semibold text-slate-700">
            {roleCopy.evidenceActionLabel}
            <textarea
              className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-normal text-slate-800"
              rows={3}
              value={action}
              disabled={!scoreEligible || unlocked}
              onChange={(event) => setAction(event.target.value)}
              placeholder={roleCopy.evidenceActionPlaceholder}
            />
          </label>

          <label className="benchmark-certificate-reflection block text-xs font-semibold text-slate-700">
            {roleCopy.evidenceChangeLabel}
            <textarea
              className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-normal text-slate-800"
              rows={3}
              value={change}
              disabled={!scoreEligible || unlocked}
              onChange={(event) => setChange(event.target.value)}
              placeholder={roleCopy.evidenceChangePlaceholder}
            />
          </label>

          <label className="benchmark-certificate-reflection block text-xs font-semibold text-slate-700">
            {roleCopy.evidenceLinkLabel}
            <input
              className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-normal text-slate-800"
              type="url"
              value={link}
              disabled={!scoreEligible || unlocked}
              onChange={(event) => setLink(event.target.value)}
              placeholder={roleCopy.evidenceLinkPlaceholder}
            />
          </label>

          <div className={`benchmark-certificate-quality ${qualityClass}`}>
            <div className="benchmark-certificate-quality__head">
              <span className="benchmark-certificate-quality__label">Evidence quality</span>
              <strong className="benchmark-certificate-quality__score">{assessment.quality_score}/100</strong>
              <span className="benchmark-certificate-quality__tier">{assessment.tier_label}</span>
            </div>
            <ul className="benchmark-certificate-pathways">
              {EVIDENCE_PATHWAYS.map((pathway) => {
                const met = assessment.pathways[pathway.key]
                return (
                  <li
                    key={pathway.key}
                    className={`benchmark-certificate-pathways__item${met ? ' is-met' : ''}`}
                  >
                    <span className="benchmark-certificate-pathways__status" aria-hidden="true">
                      {met ? '✓' : '○'}
                    </span>
                    <span>
                      <strong>{pathway.label}</strong>
                      <br />
                      <span className="benchmark-certificate-pathways__hint">{pathway.hint}</span>
                    </span>
                  </li>
                )
              })}
            </ul>
            {assessment.messages.length ? (
              <ul className="benchmark-certificate-quality__messages">
                {assessment.messages.map((message) => (
                  <li key={message}>{message}</li>
                ))}
              </ul>
            ) : null}
          </div>

          {certificateError ? (
            <p className="text-sm font-semibold text-red-600" role="alert">
              {certificateError}
            </p>
          ) : null}

          <div className="grid gap-2">
            <button
              type="button"
              className="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60"
              style={{ backgroundColor: model.accent }}
              disabled={!canUnlock}
              onClick={() => setUnlocked(true)}
            >
              {unlocked ? 'Certificate unlocked' : 'Unlock certificate'}
              <LockKeyhole className="size-4" />
            </button>
            <button
              type="button"
              className="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
              disabled={!unlocked || generatingCertificate}
              onClick={() => void downloadCertificate()}
            >
              {generatingCertificate ? 'Generating PDF...' : 'Download / print certificate'}
              <Download className="size-4" />
            </button>
          </div>

          <p className="text-xs leading-relaxed text-slate-500">
            Evidence is checked before unlock. This recognises progress — not certification as an expert user.
          </p>
        </div>

        <div className="rounded-lg border border-slate-200 bg-slate-50 p-2">
          <CertificatePreview ref={certRef} data={certificateData} compact />
        </div>
      </div>
    </section>
  )
}

function FollowUpForm({ model }: { model: RoleModel }) {
  const [supportInterests, setSupportInterests] = useState<string[]>(['whole_school_cpd'])
  const [supportSubmitted, setSupportSubmitted] = useState(false)
  const weakestDomain = model.domains.reduce((weakest, domain) =>
    domain.value < weakest.value ? domain : weakest,
  )

  const toggleSupportInterest = (slug: string) => {
    setSupportSubmitted(false)
    setSupportInterests((current) =>
      current.includes(slug)
        ? current.filter((item) => item !== slug)
        : [...current, slug],
    )
  }

  return (
    <section
      id="benchmark-follow-up"
      className="rounded-lg border border-slate-200 bg-white p-4 sm:p-5"
      aria-labelledby="benchmark-follow-up-heading"
    >
      <div className="flex items-start gap-3">
        <MessageCircleQuestion className="mt-1 size-5 shrink-0" style={{ color: model.accent }} />
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide" style={{ color: model.accent }}>
            Optional next step
          </p>
          <h2 id="benchmark-follow-up-heading" className="mt-1 text-lg font-semibold leading-tight text-slate-950">
            Want help with your next step?
          </h2>
          <p className="mt-2 text-sm leading-relaxed text-slate-600">
            You have seen your results — tell us if you would like CPD, classroom resources, or a
            whole-school rollout.
          </p>
        </div>
      </div>

      <div className="mt-4 grid gap-2">
        {supportOptions.map((option) => {
          const checked = supportInterests.includes(option.slug)
          return (
            <label
              key={option.slug}
              className={`flex min-h-12 cursor-pointer gap-3 rounded-lg border p-3 text-sm transition ${
                checked ? 'bg-blue-50' : 'bg-slate-50'
              }`}
              style={{ borderColor: checked ? model.accent : '#e2e8f0' }}
            >
              <input
                type="checkbox"
                className="mt-1 size-4 shrink-0"
                checked={checked}
                onChange={() => toggleSupportInterest(option.slug)}
              />
              <span>
                <span className="block font-semibold text-slate-950">{option.label}</span>
                <span className="mt-0.5 block leading-relaxed text-slate-600">{option.description}</span>
              </span>
            </label>
          )
        })}
      </div>

      <fieldset className="mt-4">
        <legend className="text-sm font-semibold text-slate-700">Which best describes you?</legend>
        <div className="mt-2 grid gap-2 sm:grid-cols-2">
          {['Teacher', 'Middle leader', 'Senior leader', 'Governor'].map((roleLabel) => (
            <label
              key={roleLabel}
              className="flex min-h-12 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700"
            >
              <input type="radio" name="teacher-support-role" defaultChecked={roleLabel === 'Teacher'} />
              {roleLabel}
            </label>
          ))}
        </div>
      </fieldset>

      <div className="mt-4 grid gap-3 sm:grid-cols-2">
        <label className="block text-sm font-semibold text-slate-700">
          Your name
          <input
            className="mt-1 min-h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-normal text-slate-700 outline-none focus:border-blue-500 focus:bg-white"
            type="text"
            autoComplete="name"
            placeholder="Alex Morgan"
          />
        </label>
        <label className="block text-sm font-semibold text-slate-700">
          Email address *
          <input
            className="mt-1 min-h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-normal text-slate-700 outline-none focus:border-blue-500 focus:bg-white"
            type="email"
            required
            autoComplete="email"
            placeholder="alex@school.org"
          />
        </label>
      </div>

      <label className="mt-4 block text-sm font-semibold text-slate-700">
        School / trust name
        <input
          className="mt-1 min-h-12 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-normal text-slate-700 outline-none focus:border-blue-500 focus:bg-white"
          type="text"
          autoComplete="organization"
          placeholder="Riverside Academy"
        />
      </label>

      <label className="mt-4 block text-sm font-semibold text-slate-700">
        Anything else we should know?
        <textarea
          className="mt-1 min-h-20 w-full resize-none rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-normal text-slate-700 outline-none focus:border-blue-500 focus:bg-white"
          placeholder={`Example: We need help with ${weakestDomain.label.toLowerCase()} across Year 8 lessons.`}
        />
      </label>

      {supportSubmitted ? (
        <p className="mt-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">
          Demo request captured. In production this would submit through the existing interest form.
        </p>
      ) : null}

      <button
        type="button"
        className="mt-4 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold"
        style={{ borderColor: model.accent, color: model.accent }}
        onClick={() => setSupportSubmitted(true)}
      >
        Request follow-up
        <ArrowRight className="size-4" />
      </button>
    </section>
  )
}

export function BenchmarkDashboardsDemo() {
  const [role, setRole] = useState<RoleKey>('teacher')
  const [tab, setTab] = useState<DashboardTabKey>('overview')
  const model = roles[role]
  const Icon = roleIcons[role]

  return (
    <main className="min-h-svh bg-[#f5f1e8] text-slate-900">
      <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:gap-5 sm:py-5 lg:px-6">
        <header className="rounded-lg border border-slate-200 bg-white p-4">
          <Link
            to="/"
            className="text-sm font-medium text-slate-500 underline-offset-4 hover:text-slate-950 hover:underline"
          >
            ← All demos
          </Link>
          <h1 className="mt-3 text-2xl font-semibold tracking-normal text-slate-950 sm:text-3xl">
            Benchmark results dashboard
          </h1>
          <p className="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600 sm:text-base">
            Education-focused layout: core score stays visible, detail lives in clear sections, and
            follow-up support comes after the results.
          </p>
        </header>

        <RoleChips role={role} onRole={setRole} />
        <CoreSummary model={model} icon={Icon} />

        <section className="rounded-lg border border-slate-200 bg-white">
          <div className="px-4 pt-3 sm:px-5">
            <DashboardSubTabs tab={tab} onTab={setTab} />
          </div>
          <div className="p-4 sm:p-5" role="tabpanel">
            {tab === 'overview' && <OverviewPanel model={model} />}
            {tab === 'progress' && <ProgressPanel model={model} roleKey={role} />}
            {tab === 'resources' && <ResourcesPanel model={model} />}
          </div>
        </section>

        <FollowUpForm model={model} />

        <footer className="pb-4 text-sm text-slate-500">
          Prototype only. Uses mock result data and does not touch the WordPress benchmark plugin.
        </footer>
      </div>
    </main>
  )
}
