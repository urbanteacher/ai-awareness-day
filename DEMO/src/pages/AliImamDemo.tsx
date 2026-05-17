import { CatalogEmbed } from '@/components/CatalogEmbed'
import { DemoPage } from '@/components/DemoPage'
import { DotPattern } from '@/components/ui/dot-pattern'

export function AliImamDemo() {
  return (
    <DemoPage title="Ali Imam components" sourceUrl="https://aliimam.in/docs/components" variant="wide">
      <section className="space-y-3">
        <h2 className="text-lg font-medium text-foreground">In-app: Dot pattern</h2>
        <p className="max-w-2xl text-sm text-muted-foreground">
          Pulled from the Ali Imam shadcn registry:{' '}
          <code className="rounded bg-muted px-1.5 py-0.5">npx shadcn add @aliimam/dot-pattern</code>
        </p>
        <div className="relative h-56 w-full max-w-xl overflow-hidden rounded-xl border border-border bg-zinc-900 text-zinc-400">
          <DotPattern width={14} height={14} cx={1} cy={1} dotSize={0.9} className="text-zinc-500" />
          <div className="relative z-10 flex h-full items-center justify-center p-6 text-center text-sm text-zinc-200">
            Foreground content sits above the pattern — typical hero / card usage.
          </div>
        </div>
      </section>

      <section className="space-y-3">
        <h2 className="text-lg font-medium text-foreground">Live docs (embedded)</h2>
        <CatalogEmbed title="Ali Imam — components documentation" src="https://aliimam.in/docs/components" />
      </section>
    </DemoPage>
  )
}
