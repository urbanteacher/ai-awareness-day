import { CatalogEmbed } from '@/components/CatalogEmbed'
import { DemoPage } from '@/components/DemoPage'

export function WatermelonDemo() {
  return (
    <DemoPage title="Watermelon UI" sourceUrl="https://ui.watermelon.sh/" variant="wide">
      <p className="max-w-2xl text-muted-foreground">
        Watermelon is a component and blocks registry. The live marketing and catalog UI is embedded
        below so you can browse without leaving this demo shell.
      </p>
      <CatalogEmbed title="Watermelon UI — live site" src="https://ui.watermelon.sh/" />
    </DemoPage>
  )
}
