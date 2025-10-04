import { cn } from "@/lib/utils"

/**
 * SectionWrapper Component
 * 
 * A reusable wrapper component that provides consistent styling for page sections.
 * Handles background colors, padding, and section IDs for navigation.
 * 
 * Features:
 * - Consistent padding options (xs, sm, md, lg, xl)
 * - Background color variants (default, muted, accent)
 * - Section IDs for navigation anchors
 * - Custom className support for overrides
 */
interface SectionWrapperProps {
  children: React.ReactNode
  className?: string
  background?: "default" | "muted" | "accent"
  padding?: "xs" | "sm" | "md" | "lg" | "xl"
  id?: string
  variant?: "default" | "gray"
}

// Background color variants
const backgroundClasses = {
  default: "bg-background",    // White background
  muted: "bg-muted/30",        // Light gray with opacity
  accent: "bg-accent/10"       // Accent color with low opacity
}

// Additional variant classes (legacy support)
const variantClasses = {
  default: "",
  gray: "bg-muted/30"
}

// Responsive padding options - mobile first approach
const paddingClasses = {
  xs: "py-6 sm:py-8",      // Extra small: 24px mobile, 32px desktop
  sm: "py-8 sm:py-12",     // Small: 32px mobile, 48px desktop (default)
  md: "py-12 sm:py-16",    // Medium: 48px mobile, 64px desktop
  lg: "py-16 sm:py-20",    // Large: 64px mobile, 80px desktop
  xl: "py-20 sm:py-24"     // Extra large: 80px mobile, 96px desktop
}

/**
 * SectionWrapper Implementation
 * 
 * Combines all styling classes using cn() utility for proper class merging.
 * Order of classes matters for CSS specificity:
 * 1. Base width
 * 2. Background color
 * 3. Variant classes
 * 4. Padding
 * 5. Custom className (highest priority)
 */
export function SectionWrapper({ 
  children, 
  className,
  background = "default",
  padding = "sm",
  id,
  variant = "default"
}: SectionWrapperProps) {
  return (
    <section 
      id={id}
      className={cn(
        "w-full",                           // Full width base
        backgroundClasses[background],       // Background color
        variantClasses[variant],            // Additional variants
        paddingClasses[padding],            // Responsive padding
        className                          // Custom overrides (highest priority)
      )}
    >
      {children}
    </section>
  )
}
