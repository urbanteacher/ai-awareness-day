import { useCallback, useMemo, useRef, useState } from 'react'
import { Download, FileImage, Upload } from 'lucide-react'

import { CertificatePreview } from '@/components/certificate/CertificatePreview'
import { DemoPage } from '@/components/DemoPage'
import { Button } from '@/components/ui/button'
import {
  buildCertificateData,
  exportCertificatePdf,
  fileToDataUrl,
  makeCertificateId,
} from '@/lib/certificate'
import { INVOLVED_AS_OPTIONS, type InvolvedAs } from '@/lib/certificate-copy'

import '@/styles/certificate.css'

const ACCEPT_LOGO = 'image/png,image/jpeg,image/webp,image/svg+xml'
const ACCEPT_EVIDENCE = 'image/*,application/pdf'

export function CertificateDemo() {
  const certRef = useRef<HTMLDivElement>(null)
  const [teacherName, setTeacherName] = useState('')
  const [schoolName, setSchoolName] = useState('')
  const [participationDate, setParticipationDate] = useState(() =>
    new Date().toISOString().slice(0, 10),
  )
  const [schoolLogoUrl, setSchoolLogoUrl] = useState<string | null>(null)
  const [involvedAs, setInvolvedAs] = useState<InvolvedAs>('teacher')
  const [orgType, setOrgType] = useState('company')
  const [evidencePreview, setEvidencePreview] = useState<{
    name: string
    url: string | null
    type: string
  } | null>(null)
  const [certificateId] = useState(makeCertificateId)
  const [generating, setGenerating] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const certificateData = useMemo(
    () =>
      buildCertificateData({
        teacherName,
        schoolName,
        participationDate,
        schoolLogoUrl,
        certificateId,
        involvedAs,
        orgType,
      }),
    [teacherName, schoolName, participationDate, schoolLogoUrl, certificateId, involvedAs, orgType],
  )

  const handleSchoolLogo = useCallback(async (file: File | undefined) => {
    if (!file) {
      setSchoolLogoUrl(null)
      return
    }
    if (!file.type.startsWith('image/')) {
      setError('School logo must be an image (PNG, JPG, WebP, or SVG).')
      return
    }
    setError(null)
    setSchoolLogoUrl(await fileToDataUrl(file))
  }, [])

  const handleEvidence = useCallback(async (file: File | undefined) => {
    if (!file) {
      setEvidencePreview(null)
      return
    }
    setError(null)
    const url = file.type.startsWith('image/') ? await fileToDataUrl(file) : null
    setEvidencePreview({ name: file.name, url, type: file.type })
  }, [])

  const canDownload =
    teacherName.trim().length > 0 &&
    schoolName.trim().length > 0 &&
    participationDate.length > 0

  const downloadPdf = async () => {
    const el = certRef.current
    if (!el || !canDownload) return
    setGenerating(true)
    setError(null)
    try {
      const safeName = teacherName.trim().replace(/\s+/g, '-').slice(0, 40)
      await exportCertificatePdf(el, `ai-awareness-day-certificate-${safeName}.pdf`)
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not generate PDF.')
    } finally {
      setGenerating(false)
    }
  }

  return (
    <DemoPage
      title="Participation certificate"
      sourceUrl="https://ai-awareness-day.org"
      variant="wide"
    >
      <p className="text-sm text-muted-foreground">
        Experiment: teachers submit participation evidence and receive a certificate with the
        AI Awareness Day mark and their school logo. Generation runs entirely in the browser
        (html2canvas + jsPDF). For production, add moderation before issuing.
      </p>

      <div className="grid gap-10 lg:grid-cols-[minmax(0,340px)_1fr] lg:items-start">
        <form
          className="flex flex-col gap-5 rounded-xl border border-border bg-card p-5 shadow-sm"
          onSubmit={(e) => e.preventDefault()}
        >
          <div>
            <label htmlFor="involved-as" className="mb-1.5 block text-sm font-medium">
              Participant type
            </label>
            <select
              id="involved-as"
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
              value={involvedAs}
              onChange={(e) => setInvolvedAs(e.target.value as InvolvedAs)}
            >
              {INVOLVED_AS_OPTIONS.map((opt) => (
                <option key={opt.value || 'default'} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </div>

          {involvedAs === 'organisation' ? (
            <div>
              <label htmlFor="org-type" className="mb-1.5 block text-sm font-medium">
                Organisation type
              </label>
              <select
                id="org-type"
                className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
                value={orgType}
                onChange={(e) => setOrgType(e.target.value)}
              >
                <option value="company">Company / tech</option>
                <option value="education_provider">Education provider</option>
                <option value="charity">Charity</option>
                <option value="public_body">Public body</option>
                <option value="institution">Institution</option>
                <option value="other">Other</option>
              </select>
            </div>
          ) : null}

          <div>
            <label htmlFor="teacher-name" className="mb-1.5 block text-sm font-medium">
              Name on certificate
            </label>
            <input
              id="teacher-name"
              type="text"
              required
              autoComplete="name"
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
              value={teacherName}
              onChange={(e) => setTeacherName(e.target.value)}
              placeholder="Alex Morgan"
            />
          </div>

          <div>
            <label htmlFor="school-name" className="mb-1.5 block text-sm font-medium">
              School or organisation
            </label>
            <input
              id="school-name"
              type="text"
              required
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
              value={schoolName}
              onChange={(e) => setSchoolName(e.target.value)}
              placeholder="Riverside Academy"
            />
          </div>

          <div>
            <label htmlFor="participation-date" className="mb-1.5 block text-sm font-medium">
              Participation date
            </label>
            <input
              id="participation-date"
              type="date"
              required
              className="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm"
              value={participationDate}
              onChange={(e) => setParticipationDate(e.target.value)}
            />
          </div>

          <div>
            <label htmlFor="school-logo" className="mb-1.5 flex items-center gap-2 text-sm font-medium">
              <Upload className="size-4" aria-hidden />
              School logo
            </label>
            <input
              id="school-logo"
              type="file"
              accept={ACCEPT_LOGO}
              className="w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-primary-foreground"
              onChange={(e) => void handleSchoolLogo(e.target.files?.[0])}
            />
            <p className="mt-1 text-xs text-muted-foreground">
              PNG or SVG with transparent background works best.
            </p>
          </div>

          <div>
            <label
              htmlFor="evidence"
              className="mb-1.5 flex items-center gap-2 text-sm font-medium"
            >
              <FileImage className="size-4" aria-hidden />
              Evidence of participation
            </label>
            <input
              id="evidence"
              type="file"
              accept={ACCEPT_EVIDENCE}
              className="w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5"
              onChange={(e) => void handleEvidence(e.target.files?.[0])}
            />
            <p className="mt-1 text-xs text-muted-foreground">
              Photo or PDF for your records — not printed on the certificate in this demo.
            </p>
            {evidencePreview ? (
              <div className="mt-3 rounded-lg border border-border bg-muted/40 p-3">
                <p className="text-xs font-medium text-foreground">{evidencePreview.name}</p>
                {evidencePreview.url ? (
                  <img
                    src={evidencePreview.url}
                    alt=""
                    className="mt-2 max-h-32 w-full rounded object-contain"
                  />
                ) : (
                  <p className="mt-1 text-xs text-muted-foreground">PDF attached</p>
                )}
              </div>
            ) : null}
          </div>

          {error ? (
            <p className="text-sm text-destructive" role="alert">
              {error}
            </p>
          ) : null}

          <Button
            type="button"
            size="lg"
            className="w-full"
            disabled={!canDownload || generating}
            onClick={() => void downloadPdf()}
          >
            <Download className="size-4" aria-hidden />
            {generating ? 'Generating PDF…' : 'Download certificate (PDF)'}
          </Button>
        </form>

        <div className="flex flex-col gap-4">
          <h2 className="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
            Live preview
          </h2>
          <CertificatePreview ref={certRef} data={certificateData} compact />
        </div>
      </div>
    </DemoPage>
  )
}
