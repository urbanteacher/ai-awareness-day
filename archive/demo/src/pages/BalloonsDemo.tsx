import { balloons, textBalloons } from 'balloons-js'

import { DemoPage } from '@/components/DemoPage'
import { Button } from '@/components/ui/button'

export function BalloonsDemo() {
  return (
    <DemoPage title="balloons-js" sourceUrl="https://arturbien.github.io/balloons-js/">
      <p className="text-sm text-muted-foreground">
        Install: <code className="rounded bg-muted px-1.5 py-0.5">npm install balloons-js</code>
      </p>
      <div className="flex flex-wrap gap-3">
        <Button
          type="button"
          onClick={() => {
            void balloons()
          }}
        >
          Release balloons
        </Button>
        <Button
          type="button"
          variant="secondary"
          onClick={() => {
            textBalloons([{ text: '🎉✨🎈', fontSize: 96, color: '#7c3aed' }])
          }}
        >
          Text balloons
        </Button>
      </div>
    </DemoPage>
  )
}
