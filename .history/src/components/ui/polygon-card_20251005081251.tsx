import { motion } from "framer-motion"
import { ReactNode } from "react"

interface PolygonCardProps {
  title: string
  gradient: string
  children?: ReactNode
  className?: string
  size?: 'small' | 'medium' | 'large'
  animated?: boolean
}

const sizeClasses = {
  small: 'w-20 h-20 sm:w-24 sm:h-24',
  medium: 'w-32 h-32 sm:w-40 sm:h-40',
  large: 'w-40 h-40 sm:w-48 sm:h-48'
}

export function PolygonCard({
  title,
  gradient,
  children,
  className = '',
  size = 'medium',
  animated = true
}: PolygonCardProps) {
  const CardComponent = animated ? motion.div : 'div'

  return (
    <CardComponent
      {...(animated && {
        initial: { opacity: 0, scale: 0.8 },
        animate: { opacity: 1, scale: 1 },
        transition: { duration: 0.6 }
      })}
      className={`relative ${sizeClasses[size]} bg-gray-800 ${className}`}
      style={{
        clipPath: 'polygon(0% 0%, 75% 0%, 100% 25%, 100% 100%, 25% 100%, 0% 75%)'
      }}
    >
      {/* Header with gradient */}
      <div
        className="absolute inset-0"
        style={{
          background: gradient,
          clipPath: 'polygon(0% 0%, 100% 0%, 100% 60%, 0% 60%)'
        }}
      />

      {/* Title section */}
      <div className="absolute top-0 left-0 w-full h-3/5 flex items-center justify-center">
        <span className="text-white font-bold text-sm sm:text-lg">{title}</span>
      </div>

      {/* Bottom section */}
      <div className="absolute bottom-0 right-0 w-full h-2/5 bg-gray-800 flex flex-col justify-end items-end p-2">
        {children || (
          <>
            <span className="text-white text-xs leading-none">AI</span>
            <span className="text-white text-xs leading-none">AWARENESS</span>
            <span className="text-white text-xs leading-none">2026</span>
          </>
        )}
      </div>
    </CardComponent>
  )
}
