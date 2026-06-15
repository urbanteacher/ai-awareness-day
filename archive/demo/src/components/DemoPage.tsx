import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'

type DemoPageProps = {
  title: string
  sourceUrl: string
  children: ReactNode
  /** Wider layout for embedded full-site previews */
  variant?: 'default' | 'wide'
}

export function DemoPage({ title, sourceUrl, children, variant = 'default' }: DemoPageProps) {
  return (
    <div
      className={
        variant === 'wide'
          ? 'mx-auto flex min-h-svh max-w-6xl flex-col gap-8 px-4 py-10 lg:max-w-[90rem]'
          : 'mx-auto flex min-h-svh max-w-3xl flex-col gap-8 px-4 py-10'
      }
    >
      <header className="flex flex-col gap-3 border-b border-border pb-6">
        <Link
          to="/"
          className="text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
        >
          ← All demos
        </Link>
        <h1 className="font-heading text-3xl font-semibold tracking-tight text-foreground">
          {title}
        </h1>
        <a
          href={sourceUrl}
          target="_blank"
          rel="noreferrer"
          className="w-fit text-sm text-primary underline-offset-4 hover:underline"
        >
          Official site
        </a>
      </header>
      <div className="flex flex-1 flex-col gap-6">{children}</div>
    </div>
  )
}
