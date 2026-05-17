import { DemoPage } from '@/components/DemoPage'
import { Skiper40 } from '@/components/ui/skiper-ui/skiper40'

export function SkiperDemo() {
  return (
    <DemoPage title="Skiper UI" sourceUrl="https://skiper-ui.com/components">
      <p className="text-sm text-muted-foreground">
        Install (example):{' '}
        <code className="rounded bg-muted px-1.5 py-0.5">npx shadcn add @skiper-ui/skiper40</code>
      </p>
      <div className="min-h-[420px] overflow-hidden rounded-xl border border-border bg-background">
        <Skiper40 />
      </div>
    </DemoPage>
  )
}
