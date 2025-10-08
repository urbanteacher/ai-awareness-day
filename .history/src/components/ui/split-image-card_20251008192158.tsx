import React from 'react'
import { cn } from '@/lib/utils'

export interface SplitImageCardProps {
  theme: 'safe' | 'smart' | 'creative' | 'responsible' | 'future'
  title: string
  description: string
  difficulty: 'beginner' | 'intermediate' | 'advanced'
  tags: string[]
  imageUrl: string
  backgroundImageUrl?: string
  className?: string
  onClick?: () => void
}

const themeConfig = {
  safe: {
    gradient: 'from-red-500 to-red-600',
    dotColor: 'bg-red-500',
    label: 'BE SAFE',
    difficultyColor: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  },
  smart: {
    gradient: 'from-blue-500 to-blue-600',
    dotColor: 'bg-blue-500',
    label: 'BE SMART',
    difficultyColor: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
  },
  creative: {
    gradient: 'from-green-500 to-green-600',
    dotColor: 'bg-green-500',
    label: 'BE CREATIVE',
    difficultyColor: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
  },
  responsible: {
    gradient: 'from-purple-500 to-purple-600',
    dotColor: 'bg-purple-500',
    label: 'BE RESPONSIBLE',
    difficultyColor: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
  },
  future: {
    gradient: 'from-orange-500 to-orange-600',
    dotColor: 'bg-orange-500',
    label: 'BE FUTURE',
    difficultyColor: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
  }
}

const difficultyConfig = {
  beginner: 'Beginner',
  intermediate: 'Intermediate',
  advanced: 'Advanced'
}

export function SplitImageCard({
  theme,
  title,
  description,
  difficulty,
  tags,
  imageUrl,
  backgroundImageUrl,
  className,
  onClick
}: SplitImageCardProps) {
  const config = themeConfig[theme]
  const difficultyText = difficultyConfig[difficulty]

  return (
    <div 
      className={cn(
        "bg-gray-800 p-0 border border-gray-600 group cursor-pointer relative overflow-hidden flex flex-col",
        className
      )}
      style={{
        clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
      }}
      onClick={onClick}
    >
      {/* Left side - Theme section with image */}
      <div className={cn("bg-gradient-to-br", config.gradient, "p-4 sm:p-6 relative overflow-hidden")}>
        {/* Background image overlay */}
        {backgroundImageUrl && (
          <div 
            className="absolute inset-0 bg-cover bg-center opacity-20"
            style={{
              backgroundImage: `url('${backgroundImageUrl}')`
            }}
          />
        )}
        
        {/* Dark overlay for text readability */}
        <div className="absolute inset-0 bg-black/30" />
        
        {/* Decorative Polygon Corner */}
        <div 
          className="absolute top-0 right-0 w-8 h-8 bg-white/20"
          style={{
            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
          }}
        />
        
        {/* Theme header */}
        <div className="relative z-10 flex items-center space-x-2 mb-3 sm:mb-4">
          <div className={cn("w-2 h-2 sm:w-3 sm:h-3 rounded-full", config.dotColor)} />
          <span className="text-xs sm:text-sm font-medium text-white">{config.label}</span>
        </div>
        
        {/* Image content in theme section */}
        <div className="relative z-10 mb-4">
          <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
            <img 
              src={imageUrl}
              alt={title}
              className="w-full h-full object-cover"
            />
          </div>
        </div>
        
        {/* Title in theme section with difficulty badge */}
        <div className="relative z-10 flex items-start justify-between">
          <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
            <span className="line-clamp-2">{title}</span>
          </h4>
          <span className={cn("px-2 py-1 text-xs font-medium rounded-full flex-shrink-0", config.difficultyColor)}>
            {difficultyText}
          </span>
        </div>
      </div>
      
      {/* Right side - Content section */}
      <div className="bg-gray-800 p-6 flex flex-col">
        {/* Description */}
        <div className="flex-1">
          <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
            <span className="line-clamp-3">{description}</span>
          </p>
        </div>

        {/* Tags */}
        <div className="mt-4 space-y-2">
          <div className="flex items-center space-x-2">
            <span className="text-xs text-gray-400">Tags</span>
            <div className="flex flex-wrap gap-1">
              {tags.map((tag, index) => (
                <span 
                  key={index}
                  className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]"
                >
                  {tag}
                </span>
              ))}
            </div>
          </div>
        </div>

        {/* View Details Link */}
        <div className="mt-4 mb-8">
          <span className="text-purple-400 text-base font-medium">
            View Details →
          </span>
        </div>
      </div>
    </div>
  )
}

export default SplitImageCard
