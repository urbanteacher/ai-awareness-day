import { FeedMagazine } from '@/components/feed/FeedVariants'
import { FeedSwipeDeck } from '@/components/feed/FeedSwipeDeck'
import type { FeedUpdate } from '@/lib/feed-demo-data'

/**
 * Production pattern: portrait swipe on mobile, hybrid magazine on desktop (md+).
 */
export function CampaignResponsiveFeed({
  items,
  className = '',
  production = false,
}: {
  items: FeedUpdate[]
  className?: string
  production?: boolean
}) {
  return (
    <div
      className={`${production ? 'timeline-feed__content' : ''} ${className}`.trim()}
    >
      <div className="md:hidden">
        <FeedSwipeDeck
          items={items}
          orientation="portrait"
          heightClass="h-[min(65dvh,520px)]"
          className="mt-4"
        />
      </div>
      <div className="mt-4 hidden md:block">
        <FeedMagazine items={items} />
      </div>
    </div>
  )
}
