import { ReactNode } from 'react'
import { cn } from '@/lib/utils'

interface GridProps {
  children: ReactNode
  className?: string
  cols?: 1 | 2 | 3 | 4 | 5 | 6
  responsive?: {
    sm?: 1 | 2 | 3 | 4 | 5 | 6
    md?: 1 | 2 | 3 | 4 | 5 | 6
    lg?: 1 | 2 | 3 | 4 | 5 | 6
    xl?: 1 | 2 | 3 | 4 | 5 | 6
  }
  gap?: 'sm' | 'md' | 'lg' | 'xl'
}

const gapClasses = {
  sm: 'gap-3',
  md: 'gap-6',
  lg: 'gap-8',
  xl: 'gap-12'
}

const colClasses = {
  1: 'grid-cols-1',
  2: 'grid-cols-2',
  3: 'grid-cols-3',
  4: 'grid-cols-4',
  5: 'grid-cols-5',
  6: 'grid-cols-6'
}

/**
 * Grid Component
 * 
 * A responsive grid layout component with consistent spacing and breakpoints.
 * 
 * Features:
 * - Responsive column layouts
 * - Consistent gap spacing
 * - Flexible breakpoint configuration
 * - Clean, reusable pattern for content grids
 */
export function Grid({
  children,
  className,
  cols = 3,
  responsive = {
    sm: 1,
    md: 2,
    lg: 3
  },
  gap = 'md'
}: GridProps) {
  const responsiveClasses = [
    colClasses[cols],
    responsive.sm && `sm:${colClasses[responsive.sm]}`,
    responsive.md && `md:${colClasses[responsive.md]}`,
    responsive.lg && `lg:${colClasses[responsive.lg]}`,
    responsive.xl && `xl:${colClasses[responsive.xl]}`
  ].filter(Boolean).join(' ')

  return (
    <div className={cn(
      'grid',
      responsiveClasses,
      gapClasses[gap],
      className
    )}>
      {children}
    </div>
  )
}
