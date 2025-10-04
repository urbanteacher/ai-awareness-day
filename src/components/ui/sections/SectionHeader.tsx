import { cn } from "@/lib/utils"

interface SectionHeaderProps {
  title: string
  subtitle?: string
  description?: string
  className?: string
  align?: "left" | "center" | "right"
  titleSize?: "sm" | "md" | "lg" | "xl"
  titleColor?: "default" | "purple" | "blue" | "green" | "orange" | "red"
}

const titleSizeClasses = {
  sm: "text-2xl font-bold sm:text-3xl",
  md: "text-3xl font-bold sm:text-4xl",
  lg: "text-3xl font-bold sm:text-4xl lg:text-5xl",
  xl: "text-4xl font-bold sm:text-5xl lg:text-6xl"
}

const titleColorClasses = {
  default: "text-foreground",
  purple: "text-purple-600 dark:text-purple-400",
  blue: "text-blue-600 dark:text-blue-400",
  green: "text-green-600 dark:text-green-400",
  orange: "text-orange-600 dark:text-orange-400",
  red: "text-red-600 dark:text-red-400"
}

export function SectionHeader({ 
  title,
  subtitle,
  description,
  className,
  align = "center",
  titleSize = "lg",
  titleColor = "purple"
}: SectionHeaderProps) {
  const alignClasses = {
    left: "text-left",
    center: "text-center",
    right: "text-right"
  }

  return (
    <div className={cn(
      "space-y-4",
      alignClasses[align],
      className
    )}>
      {subtitle && (
        <p className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
          {subtitle}
        </p>
      )}
      <h2 className={cn(
        "font-bold tracking-tight",
        titleSizeClasses[titleSize],
        titleColorClasses[titleColor]
      )}>
        {title}
      </h2>
      {description && (
        <p className={cn(
          "text-lg text-muted-foreground",
          align === "center" ? "max-w-3xl mx-auto" : ""
        )}>
          {description}
        </p>
      )}
    </div>
  )
}

