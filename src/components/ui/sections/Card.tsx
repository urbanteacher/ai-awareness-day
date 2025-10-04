import { ReactNode } from 'react'
import { Card as UICard, CardContent, CardDescription, CardHeader, CardTitle } from '../card'
import { cn } from '@/lib/utils'

interface SectionCardProps {
  children: ReactNode
  title?: string
  description?: string
  className?: string
  headerClassName?: string
  contentClassName?: string
  variant?: 'default' | 'outline' | 'elevated' | 'flat'
  hover?: boolean
}

const variantClasses = {
  default: 'bg-card text-card-foreground',
  outline: 'border border-border bg-card text-card-foreground',
  elevated: 'bg-card text-card-foreground shadow-lg',
  flat: 'bg-muted/50 text-foreground'
}

/**
 * SectionCard Component
 * 
 * A standardized card component for use in sections with consistent styling.
 * 
 * Features:
 * - Consistent card styling across sections
 * - Optional title and description
 * - Hover effects
 * - Multiple variants
 * - Flexible content areas
 */
export function SectionCard({
  children,
  title,
  description,
  className,
  headerClassName,
  contentClassName,
  variant = 'default',
  hover = true
}: SectionCardProps) {
  return (
    <UICard className={cn(
      variantClasses[variant],
      hover && 'hover:shadow-md transition-shadow duration-200',
      className
    )}>
      {(title || description) && (
        <CardHeader className={headerClassName}>
          {title && <CardTitle>{title}</CardTitle>}
          {description && <CardDescription>{description}</CardDescription>}
        </CardHeader>
      )}
      <CardContent className={contentClassName}>
        {children}
      </CardContent>
    </UICard>
  )
}
