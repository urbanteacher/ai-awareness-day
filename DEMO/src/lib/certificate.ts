import html2canvas from 'html2canvas'
import { jsPDF } from 'jspdf'

import type { CertificateCopy, InvolvedAs } from '@/lib/certificate-copy'
import { getCertificateCopy } from '@/lib/certificate-copy'

export type CertificateData = {
  teacherName: string
  schoolName: string
  participationDate: string
  schoolLogoUrl: string | null
  certificateId: string
  involvedAs: InvolvedAs
  orgType: string
  copy: CertificateCopy
}

export function buildCertificateData(fields: {
  teacherName: string
  schoolName: string
  participationDate: string
  schoolLogoUrl: string | null
  certificateId: string
  involvedAs?: InvolvedAs
  orgType?: string
}): CertificateData {
  const involvedAs = fields.involvedAs ?? 'teacher'
  const orgType = fields.orgType ?? ''
  return {
    ...fields,
    involvedAs,
    orgType,
    copy: getCertificateCopy(involvedAs, orgType),
  }
}

export function makeCertificateId(): string {
  return `AIAD-${Date.now().toString(36).toUpperCase()}`
}

export function fileToDataUrl(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result as string)
    reader.onerror = () => reject(reader.error)
    reader.readAsDataURL(file)
  })
}

export function formatCertificateDate(isoDate: string): string {
  if (!isoDate) return ''
  const d = new Date(isoDate + 'T12:00:00')
  if (Number.isNaN(d.getTime())) return isoDate
  return d.toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

export async function exportCertificatePdf(
  element: HTMLElement,
  filename: string,
): Promise<void> {
  const canvas = await html2canvas(element, {
    scale: 2,
    useCORS: true,
    allowTaint: true,
    backgroundColor: '#ffffff',
    logging: false,
  })

  const imgData = canvas.toDataURL('image/png', 1)
  const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })
  const w = pdf.internal.pageSize.getWidth()
  const h = pdf.internal.pageSize.getHeight()
  pdf.addImage(imgData, 'PNG', 0, 0, w, h)
  pdf.save(filename)
}
