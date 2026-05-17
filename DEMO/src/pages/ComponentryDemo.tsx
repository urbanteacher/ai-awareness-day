import { DemoPage } from '@/components/DemoPage'
import { HyperText } from '@/components/ui/hyper-text'

export function ComponentryDemo() {
  return (
    <DemoPage title="Componentry" sourceUrl="https://www.componentry.fun/docs">
      <p className="text-sm text-muted-foreground">
        Install:{' '}
        <code className="rounded bg-muted px-1.5 py-0.5">
          npx shadcn add @componentry/hyper-text
        </code>
      </p>
      <div className="rounded-xl border border-border bg-muted/20 p-10">
        <HyperText
          text="Hover to scramble — Componentry hyper text"
          className="text-2xl font-medium text-foreground"
          animateOnLoad
        />
      </div>
    </DemoPage>
  )
}
