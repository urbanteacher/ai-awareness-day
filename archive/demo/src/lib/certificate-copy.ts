/** Mirrors inc/certificate-copy.php for offline / React demo. */

export type InvolvedAs =
  | 'teacher'
  | 'school_leader'
  | 'organisation'
  | 'parent'
  | ''

export type CertificateCopy = {
  headlinePrimary: string
  /** Optional sub-headline under the title (hidden when empty). */
  eyebrow?: string
  affiliationPrefix: string
  body: string
}

function isTechOrganisation(orgType: string): boolean {
  return orgType === 'company' || orgType === 'education_provider'
}

export function getCertificateCopy(
  involvedAs: InvolvedAs,
  orgType = '',
): CertificateCopy {
  const defaultBody =
    'has actively contributed to AI Awareness Day 2026 through their engagement in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.'

  const base: CertificateCopy = {
    headlinePrimary: 'AI Awareness Day 2026',
    eyebrow: 'Certificate of participation',
    affiliationPrefix: 'from',
    body: defaultBody,
  }

  switch (involvedAs) {
    case 'teacher':
      return {
        ...base,
        body: 'has actively contributed to AI Awareness Day 2026 through their work with learners and participation in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
      }
    case 'school_leader':
      return {
        ...base,
        body: "has actively contributed to AI Awareness Day 2026 through their leadership and their school's participation in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.",
      }
    case 'organisation':
      if (isTechOrganisation(orgType)) {
        return {
          ...base,
          body: "has actively contributed to AI Awareness Day 2026 through their organisation's engagement in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.",
        }
      }
      return {
        ...base,
        body: "has actively contributed to AI Awareness Day 2026 through their organisation's support of our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.",
      }
    case 'parent':
      return {
        ...base,
        affiliationPrefix: 'in support of',
        body: "has actively contributed to AI Awareness Day 2026 as a parent or carer supporting their child's learning, as part of our nationwide exploration of artificial intelligence in education.",
      }
    default:
      return base
  }
}

export const INVOLVED_AS_OPTIONS: { value: InvolvedAs; label: string }[] = [
  { value: 'teacher', label: 'Teacher' },
  { value: 'school_leader', label: 'School leader' },
  { value: 'organisation', label: 'Organisation' },
  { value: 'parent', label: 'Parent / carer' },
]
