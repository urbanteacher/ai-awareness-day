import { ReactNode } from 'react'
import { Container } from './Container'
import { SectionHeader } from './SectionHeader'
import { cn } from '@/lib/utils'

interface SectionProps {
  children: ReactNode
  title?: string
  subtitle?: string
  description?: string
  className?: string
  containerClassName?: string
  headerClassName?: string
  align?: "left" | "center" | "right"
  titleSize?: "sm" | "md" | "lg" | "xl"
  titleColor?: "default" | "purple" | "blue" | "green" | "orange" | "red"
  showHeader?: boolean
}

/**
 * Section Component
 * 
 * A comprehensive section wrapper that combines Container and SectionHeader
 * for consistent section layouts across the application.
 * 
 * Features:
 * - Optional header with title, subtitle, and description
 * - Consistent container styling
 * - Flexible alignment and styling options
 * - Clean, reusable pattern for all sections
 */
export function Section({
  children,
  title,
  subtitle,
  description,
  className,
  containerClassName,
  headerClassName,
  align = "center",
  titleSize = "lg",
  titleColor = "purple",
  showHeader = true
}: SectionProps) {
  return (
    <div className={cn("w-full", className)}>
      <Container className={containerClassName}>
        {showHeader && (title || subtitle || description) && (
          <SectionHeader
            title={title || ""}
            subtitle={subtitle}
            description={description}
            className={headerClassName}
            align={align}
            titleSize={titleSize}
            titleColor={titleColor}
          />
        )}
        {children}
      </Container>
    </div>
  )
}
