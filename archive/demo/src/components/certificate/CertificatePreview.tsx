import { forwardRef } from 'react'

import type { CertificateData } from '@/lib/certificate'
import { formatCertificateDate } from '@/lib/certificate'

type CertificatePreviewProps = {
  data: CertificateData
  /** Smaller scale for inline preview */
  compact?: boolean
}

export const CertificatePreview = forwardRef<HTMLDivElement, CertificatePreviewProps>(
  function CertificatePreview({ data, compact }, ref) {
    const dateLabel = formatCertificateDate(data.participationDate)
    const displayName = data.teacherName.trim() || 'Participant name'
    const displaySchool = data.schoolName.trim() || 'School or organisation'

    return (
      <div
        ref={ref}
        className={
          compact
            ? 'certificate-preview certificate-preview--compact mx-auto w-full max-w-2xl'
            : 'certificate-preview mx-auto w-full max-w-4xl'
        }
        style={{ aspectRatio: '297 / 210' }}
      >
        <div className="certificate-preview__frame">
          <div className="certificate-preview__logos">
            <div className="certificate-preview__logo-slot">
              <span className="certificate-preview__logo-placeholder">AIAD logo</span>
            </div>
            <div className="certificate-preview__logo-divider" aria-hidden="true" />
            <div className="certificate-preview__logo-slot certificate-preview__logo-slot--school">
              {data.schoolLogoUrl ? (
                <img
                  src={data.schoolLogoUrl}
                  alt=""
                  className="certificate-preview__school-logo"
                />
              ) : (
                <span className="certificate-preview__logo-placeholder">School logo</span>
              )}
            </div>
          </div>

          <div className="certificate-preview__content">
            <h1 className="certificate-preview__headline">
              <span className="certificate-preview__headline-primary">{data.copy.headlinePrimary}</span>
            </h1>
            {data.copy.eyebrow ? (
              <p className="certificate-preview__type">{data.copy.eyebrow}</p>
            ) : null}

          <p className="certificate-preview__lead">This certifies that</p>
          <p className="certificate-preview__name">{displayName}</p>
          {data.schoolName.trim() ? (
            <p className="certificate-preview__school">
              {data.copy.affiliationPrefix}{' '}
              <strong>{displaySchool}</strong>
            </p>
          ) : null}
          <p className="certificate-preview__body">{data.copy.body}</p>

          {dateLabel ? (
            <p className="certificate-preview__date">Awarded: {dateLabel}</p>
          ) : null}

            <footer className="certificate-preview__footer">
              <span className="certificate-preview__id">Certificate ID: {data.certificateId}</span>
              <span className="certificate-preview__issuer">Issued by: AI Awareness Day</span>
              <span className="certificate-preview__verify">ai-awareness-day.org</span>
            </footer>
          </div>
        </div>
      </div>
    )
  },
)
