import { useState } from 'react'

import { CampaignResponsiveFeed } from '@/components/feed/CampaignResponsiveFeed'
import { FeedFilters } from '@/components/feed/FeedVariants'
import {
  DEMO_UPDATES,
  filterUpdates,
  type FeedUpdate,
  type UpdateType,
} from '@/lib/feed-demo-data'

import '@/styles/timeline-production.css'

function TimelineStatsBar() {
  return (
    <div
      className="timeline-stats-bar"
      role="status"
      aria-label="Campaign stats"
    >
      <span className="timeline-stats-bar__stat timeline-stats-bar__days">
        <span className="timeline-stats-bar__icon" aria-hidden="true">
          ⏱
        </span>
        <span className="timeline-stats-bar__value">18</span>
        <span className="timeline-stats-bar__label--full">days to go</span>
        <span className="timeline-stats-bar__label--short">days</span>
      </span>
      <span className="timeline-stats-bar__sep" aria-hidden="true">
        ·
      </span>
      <span className="timeline-stats-bar__stat timeline-stats-bar__stat--schools">
        <span className="timeline-stats-bar__value">0</span>
        <span className="timeline-stats-bar__label--full">schools registered</span>
        <span className="timeline-stats-bar__label--short">schools</span>
      </span>
      <span className="timeline-stats-bar__sep" aria-hidden="true">
        ·
      </span>
      <span className="timeline-stats-bar__stat">
        <span className="timeline-stats-bar__value">8</span>
        <span className="timeline-stats-bar__label--full">free resources</span>
        <span className="timeline-stats-bar__label--short">resources</span>
      </span>
    </div>
  )
}

export function TimelineProductionShell({
  children,
}: {
  children?: (items: FeedUpdate[]) => React.ReactNode
}) {
  const [filter, setFilter] = useState<UpdateType | 'all'>('all')
  const filtered = filterUpdates(DEMO_UPDATES, filter)

  return (
    <section className="timeline-production section" id="timeline-demo">
      <div className="container">
        <div className="fade-up visible">
          <span className="section-label section-label--live">Live</span>
          <h2 className="section-title">Campaign Updates</h2>
          <TimelineStatsBar />
        </div>

        <FeedFilters active={filter} onChange={setFilter} production />

        <div className="timeline-feed">
          {filtered.length === 0 ? (
            <p className="timeline-feed__empty">No updates match this filter.</p>
          ) : children ? (
            children(filtered)
          ) : (
            <CampaignResponsiveFeed items={filtered} production />
          )}
        </div>
      </div>
    </section>
  )
}
