import { DemoPage } from '@/components/DemoPage'
import { TextureButton } from '@/components/ui/texture-button'

export function CultDemo() {
  return (
    <DemoPage title="Cult UI" sourceUrl="https://www.cult-ui.com/docs">
      <p className="text-sm text-muted-foreground">
        Example install:{' '}
        <code className="rounded bg-muted px-1.5 py-0.5">
          npx shadcn add @cult-ui/texture-button
        </code>
      </p>
      <div className="flex flex-wrap gap-4 rounded-lg border border-border bg-muted/30 p-8">
        <div className="w-44">
          <TextureButton variant="primary">Primary texture</TextureButton>
        </div>
        <div className="w-44">
          <TextureButton variant="accent">Accent texture</TextureButton>
        </div>
        <div className="w-44">
          <TextureButton variant="minimal">Minimal</TextureButton>
        </div>
      </div>
    </DemoPage>
  )
}
