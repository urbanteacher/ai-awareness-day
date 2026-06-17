/** Client-side evidence scoring — mirrors production airb-certificate-evidence.js */

export type EvidenceTheme = 'smart' | 'creative' | 'responsible' | 'future' | 'safe'

export type EvidencePathways = {
  self_declared: boolean
  structured_reflection: boolean
  evidence_link: boolean
  quality_validated: boolean
}

export type EvidenceAssessment = {
  quality_score: number
  quality_tier: 'needs_more_detail' | 'likely_valid' | 'strong_evidence' | 'needs_manual_review'
  tier_label: string
  can_unlock: boolean
  score_eligible: boolean
  evidence_satisfied: boolean
  pathways: EvidencePathways
  messages: string[]
  reflection_chars: number
}

export const EVIDENCE_THEMES: { slug: EvidenceTheme; label: string }[] = [
  { slug: 'smart', label: 'Smart' },
  { slug: 'creative', label: 'Creative' },
  { slug: 'responsible', label: 'Responsible' },
  { slug: 'future', label: 'Future' },
  { slug: 'safe', label: 'Safe' },
]

export const SCORE_THRESHOLD = 70
export const MIN_REFLECTION_CHARS = 120
export const MIN_SELF_DECLARED_CHARS = 40
export const QUALITY_THRESHOLD = 70

export const EVIDENCE_PATHWAYS = [
  {
    key: 'self_declared' as const,
    label: 'Self-declared action',
    hint: 'Choose a theme and describe what you did in at least 40 characters.',
  },
  {
    key: 'structured_reflection' as const,
    label: 'Structured reflection',
    hint: 'Concrete action verb plus at least 120 characters across your answers.',
  },
  {
    key: 'evidence_link' as const,
    label: 'Evidence link',
    hint: 'Add a link to a slide, activity, policy note, or CPD artefact.',
  },
  {
    key: 'quality_validated' as const,
    label: 'Quality-checked evidence',
    hint: 'Reach an evidence quality score of at least 70.',
  },
]

const VERBS = [
  'taught', 'discussed', 'planned', 'modelled', 'reviewed', 'shared', 'introduced', 'led',
  'created', 'explored', 'helped', 'changed', 'verified', 'practised', 'applied', 'talked',
]

function hasConcreteAction(text: string): boolean {
  const lower = text.toLowerCase()
  if (lower.length < 12) return false
  return VERBS.some((verb) => new RegExp(`\\b${verb}\\b`, 'i').test(lower))
}

function isGeneric(text: string): boolean {
  const lower = text.toLowerCase().replace(/\s+/g, ' ').trim()
  return lower.length < 40 || lower === 'i used ai' || lower === 'used chatgpt'
}

function isValidEvidenceLink(link: string): boolean {
  if (!link.trim()) return false
  try {
    const url = new URL(link.trim())
    return url.protocol === 'http:' || url.protocol === 'https:'
  } catch {
    return false
  }
}

export function assessCertificateEvidence(
  role: string,
  theme: string,
  action: string,
  change: string,
  link = '',
  benchmarkScore = 0,
): EvidenceAssessment {
  const combined = `${action} ${change}`.trim()
  const messages: string[] = []
  let score = 0

  const themeOk = EVIDENCE_THEMES.some((item) => item.slug === theme)
  if (themeOk) score += 20
  else messages.push('Choose one AI Awareness Day theme.')

  const verbOk = hasConcreteAction(action)
  if (verbOk) score += 25

  const reflectionLen = combined.length
  const reflectionOk = reflectionLen >= MIN_REFLECTION_CHARS
  if (reflectionOk) score += 20

  if (!isGeneric(combined)) score += 10
  else score = Math.max(0, score - 15)

  if (link.trim()) score = Math.min(100, score + 5)

  score = Math.max(0, Math.min(100, score))

  const pathways: EvidencePathways = {
    self_declared: themeOk && action.trim().length >= MIN_SELF_DECLARED_CHARS && !isGeneric(action),
    structured_reflection: themeOk && verbOk && reflectionOk,
    evidence_link: themeOk && isValidEvidenceLink(link),
    quality_validated: themeOk && score >= QUALITY_THRESHOLD,
  }

  const evidenceSatisfied =
    pathways.self_declared ||
    pathways.structured_reflection ||
    pathways.evidence_link ||
    pathways.quality_validated
  const scoreEligible = benchmarkScore >= SCORE_THRESHOLD

  if (!scoreEligible) {
    messages.unshift(`Benchmark score must be at least ${SCORE_THRESHOLD}% (currently ${benchmarkScore}%).`)
  } else if (!evidenceSatisfied) {
    messages.push(
      'Complete one evidence option: self-declared action, structured reflection, evidence link, or quality score of at least 70.',
    )
  }

  let quality_tier: EvidenceAssessment['quality_tier'] = 'needs_more_detail'
  let tier_label = 'Needs more detail'
  if (link.trim() && score >= QUALITY_THRESHOLD) {
    quality_tier = 'needs_manual_review'
    tier_label = 'Needs manual review'
  } else if (score >= 85) {
    quality_tier = 'strong_evidence'
    tier_label = 'Strong evidence'
  } else if (score >= QUALITY_THRESHOLD) {
    quality_tier = 'likely_valid'
    tier_label = 'Likely valid'
  }

  return {
    quality_score: score,
    quality_tier,
    tier_label,
    can_unlock: scoreEligible && themeOk && evidenceSatisfied,
    score_eligible: scoreEligible,
    evidence_satisfied: evidenceSatisfied,
    pathways,
    messages,
    reflection_chars: reflectionLen,
  }
}
