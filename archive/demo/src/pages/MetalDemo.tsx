import { MetalFx } from 'metal-fx'

import { DemoPage } from '@/components/DemoPage'
import { Button } from '@/components/ui/button'

export function MetalDemo() {
  return (
    <DemoPage title="Metal FX" sourceUrl="https://metal.jakubantalik.com/">
      <p className="text-sm text-muted-foreground">
        Install: <code className="rounded bg-muted px-1.5 py-0.5">npm install metal-fx</code>
      </p>
      <div className="flex flex-wrap items-center gap-6 rounded-lg border border-border bg-muted/30 p-8">
        <MetalFx preset="chromatic" strength={0.85}>
          <Button size="lg" className="rounded-full px-8">
            Chromatic metal
          </Button>
        </MetalFx>
        <MetalFx preset="gold" variant="circle" strength={0.9}>
          <Button size="icon" variant="secondary" className="size-12 rounded-full" aria-label="Gold circle">
            ✦
          </Button>
        </MetalFx>
      </div>
    </DemoPage>
  )
}
