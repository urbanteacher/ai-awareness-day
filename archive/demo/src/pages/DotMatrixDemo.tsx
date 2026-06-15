import { DemoPage } from '@/components/DemoPage'
import { DotmSquare3 } from '@/components/ui/dotm-square-3'

export function DotMatrixDemo() {
  return (
    <DemoPage title="Dot Matrix" sourceUrl="https://dotmatrix.zzzzshawn.cloud/">
      <p className="text-sm text-muted-foreground">
        Install:{' '}
        <code className="rounded bg-muted px-1.5 py-0.5">
          npx shadcn add @dotmatrix/dotm-square-3
        </code>
      </p>
      <div className="flex items-center justify-center rounded-xl border border-border bg-zinc-950 p-16">
        <DotmSquare3 className="text-emerald-400" hoverAnimated />
      </div>
    </DemoPage>
  )
}
