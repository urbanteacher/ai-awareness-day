/** Mirrors plugins/.../class-airb-certificate-copy.php for the React demo. */

export type BenchmarkCertificateRole =
  | 'teacher'
  | 'student'
  | 'parent'
  | 'leader'
  | 'support'
  | 'public'

export type BenchmarkCertificateCopy = {
  headlinePrimary: string
  body: string
  namePlaceholder: string
  evidenceActionLabel: string
  evidenceChangeLabel: string
  evidenceLinkLabel: string
  evidenceActionPlaceholder: string
  evidenceChangePlaceholder: string
  evidenceLinkPlaceholder: string
}

const HEADLINE = 'Responsible AI Progress Certificate'

const ROLE_COPY: Record<BenchmarkCertificateRole, Omit<BenchmarkCertificateCopy, 'headlinePrimary'>> = {
  teacher: {
    body: 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible classroom AI action linked to AI Awareness Day.',
    namePlaceholder: 'Example: Alex Teacher',
    evidenceActionLabel: 'What did you do with learners?',
    evidenceChangeLabel: 'What changed in your classroom practice, lesson, or checking habit?',
    evidenceLinkLabel: 'Optional evidence link (lesson slide, pupil activity, CPD note, etc.)',
    evidenceActionPlaceholder:
      'Example: I modelled checking an AI summary with my Year 9 class before they used it in a research task.',
    evidenceChangePlaceholder:
      'Example: Pupils now explain the source they would verify before trusting an AI answer.',
    evidenceLinkPlaceholder: 'https://…',
  },
  student: {
    body: 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible learning action linked to AI Awareness Day.',
    namePlaceholder: 'Example: Alex Student',
    evidenceActionLabel: 'What did you do in your learning?',
    evidenceChangeLabel: 'What changed in how you study, verify answers, or use AI for school work?',
    evidenceLinkLabel: 'Optional evidence link (study notes, draft work, reflection, etc.)',
    evidenceActionPlaceholder:
      'Example: I tried the task myself first, then used AI only to check one step and wrote what I changed.',
    evidenceChangePlaceholder: 'Example: I now check AI answers against my textbook before handing work in.',
    evidenceLinkPlaceholder: 'https://…',
  },
  parent: {
    body: "has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible home support action linked to AI Awareness Day.",
    namePlaceholder: 'Example: Alex Parent',
    evidenceActionLabel: "What did you do to support your child's AI use at home?",
    evidenceChangeLabel: 'What changed in your conversations, routines, or understanding at home?',
    evidenceLinkLabel: 'Optional evidence link (conversation guide, homework note, family agreement, etc.)',
    evidenceActionPlaceholder:
      'Example: I asked my child to explain their homework answer in their own words before they used AI again.',
    evidenceChangePlaceholder:
      'Example: We now agree when AI is allowed for homework and when they must try first.',
    evidenceLinkPlaceholder: 'https://…',
  },
  leader: {
    body: 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible AI governance action linked to AI Awareness Day.',
    namePlaceholder: 'Example: Alex Leader',
    evidenceActionLabel: 'What leadership or governance action did you take?',
    evidenceChangeLabel: 'What changed in policy, staff practice, safeguarding, or oversight?',
    evidenceLinkLabel: 'Optional evidence link (policy note, SLT paper, staff briefing, action plan, etc.)',
    evidenceActionPlaceholder: 'Example: I led a staff briefing on verifying AI outputs before use with pupils.',
    evidenceChangePlaceholder:
      'Example: Faculty leads now record one AI oversight check in their meeting notes.',
    evidenceLinkPlaceholder: 'https://…',
  },
  support: {
    body: 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible operational AI action linked to AI Awareness Day.',
    namePlaceholder: 'Example: Alex Support Staff',
    evidenceActionLabel: 'What operational action did you take?',
    evidenceChangeLabel: 'What changed in office workflow, data handling, or staff guidance?',
    evidenceLinkLabel: 'Optional evidence link (checklist, process note, data guidance, CPD record, etc.)',
    evidenceActionPlaceholder:
      'Example: I updated our office AI checklist so pupil data is never pasted into public tools.',
    evidenceChangePlaceholder:
      'Example: Colleagues now ask whether AI use is approved before drafting parent letters.',
    evidenceLinkPlaceholder: 'https://…',
  },
  public: {
    body: 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible personal AI action linked to AI Awareness Day.',
    namePlaceholder: 'Example: Alex Participant',
    evidenceActionLabel: 'What personal AI action did you take?',
    evidenceChangeLabel: 'What changed in how you verify, protect privacy, or use AI safely?',
    evidenceLinkLabel: 'Optional evidence link (notes, screenshot, privacy checklist, etc.)',
    evidenceActionPlaceholder:
      'Example: I removed personal details from a prompt and checked the answer against a trusted source.',
    evidenceChangePlaceholder: 'Example: I now verify AI answers before sharing them with family or colleagues.',
    evidenceLinkPlaceholder: 'https://…',
  },
}

export function getBenchmarkCertificateCopy(role: BenchmarkCertificateRole): BenchmarkCertificateCopy {
  const copy = ROLE_COPY[role]
  return {
    headlinePrimary: HEADLINE,
    ...copy,
  }
}
