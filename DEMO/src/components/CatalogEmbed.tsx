type CatalogEmbedProps = {
  title: string
  src: string
}

/** Full-height embedded preview of an external catalog or marketing site. */
export function CatalogEmbed({ title, src }: CatalogEmbedProps) {
  return (
    <div className="flex flex-col gap-3">
      <div className="relative min-h-[min(78vh,720px)] w-full overflow-hidden rounded-xl border border-border bg-muted/40 shadow-sm">
        <iframe
          title={title}
          src={src}
          className="absolute inset-0 h-full min-h-[min(78vh,720px)] w-full border-0 bg-background"
          sandbox="allow-scripts allow-same-origin allow-popups allow-forms allow-downloads"
          referrerPolicy="strict-origin-when-cross-origin"
        />
      </div>
      <p className="text-xs text-muted-foreground">
        Embedded live site. If the area stays blank, the publisher may block iframes — use the
        official site link in the header, or open this page&apos;s origin in a normal tab.
      </p>
    </div>
  )
}
