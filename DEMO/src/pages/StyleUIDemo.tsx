import { CatalogEmbed } from '@/components/CatalogEmbed'
import { DemoPage } from '@/components/DemoPage'

export function StyleUIDemo() {
  return (
    <DemoPage title="StyleUI" sourceUrl="https://styleui.dev/" variant="wide">
      <p className="max-w-2xl text-muted-foreground">
        Handmade marketing and product templates (full sites, not an npm widget pack). Below is
        the live StyleUI homepage embedded so you can scroll templates in place.
      </p>
      <CatalogEmbed title="StyleUI — live site" src="https://www.styleui.dev/" />
    </DemoPage>
  )
}
