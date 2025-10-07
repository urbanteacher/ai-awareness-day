import React from 'react'

interface SplitImageCardProps {
  title: string
  description: string
  tags: string[]
  theme?: 'safe' | 'smart' | 'creative' | 'responsible' | 'future'
  difficulty?: 'beginner' | 'intermediate' | 'advanced'
  imageUrl?: string
  showCornerCut?: boolean
  size?: 'small' | 'medium' | 'large'
  className?: string
}

const themes = {
  safe: {
    gradient: "from-red-500 to-red-600",
    theme: "BE SAFE",
    dotColor: "bg-red-500"
  },
  smart: {
    gradient: "from-blue-500 to-blue-600",
    theme: "BE SMART",
    dotColor: "bg-blue-500"
  },
  creative: {
    gradient: "from-green-500 to-green-600",
    theme: "BE CREATIVE",
    dotColor: "bg-green-500"
  },
  responsible: {
    gradient: "from-purple-500 to-purple-600",
    theme: "BE RESPONSIBLE",
    dotColor: "bg-purple-500"
  },
  future: {
    gradient: "from-orange-500 to-orange-600",
    theme: "BE FUTURE",
    dotColor: "bg-orange-500"
  }
}

const difficulties = {
  beginner: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
  intermediate: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400",
  advanced: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
}

const sizes = {
  small: {
    height: "h-64",
    padding: "p-4",
    titleSize: "text-lg",
    descriptionSize: "text-sm",
    imageHeight: "h-16"
  },
  medium: {
    height: "h-80",
    padding: "p-6",
    titleSize: "text-2xl",
    descriptionSize: "text-base",
    imageHeight: "h-24"
  },
  large: {
    height: "h-96",
    padding: "p-8",
    titleSize: "text-3xl",
    descriptionSize: "text-lg",
    imageHeight: "h-32"
  }
}

export function SplitImageCard({
  title,
  description,
  tags,
  theme = 'smart',
  difficulty = 'beginner',
  imageUrl = 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center',
  showCornerCut = true,
  size = 'medium',
  className = ''
}: SplitImageCardProps) {
  const currentTheme = themes[theme]
  const currentDifficulty = difficulties[difficulty]
  const currentSize = sizes[size]

  return (
    <div 
      className={`bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl ${className}`}
      style={{
        clipPath: showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
      }}
    >
      {/* Left side - Theme section with image */}
      <div 
        className={`flex-1 bg-gradient-to-br ${currentTheme.gradient} p-6 relative overflow-hidden`}
      >
        {/* Background image overlay */}
        <div 
          className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
          style={{
            backgroundImage: `url('${imageUrl}')`
          }}
        />
        
        {/* Dark overlay for text readability */}
        <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-300" />
        
        {/* Decorative Polygon Corner */}
        {showCornerCut && (
          <div 
            className="absolute top-0 right-0 w-8 h-8 bg-white/20"
            style={{
              clipPath: "polygon(100% 0, 0 0, 100% 100%)"
            }}
          />
        )}
        
        {/* Theme header */}
        <div className="relative z-10 flex items-center space-x-2 mb-4">
          <div className={`w-3 h-3 rounded-full ${currentTheme.dotColor}`} />
          <span className="text-sm font-medium text-white">{currentTheme.theme}</span>
        </div>
        
        {/* Image content in theme section */}
        <div className="relative z-10 mb-4">
          <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
            <img 
              src={imageUrl} 
              alt="AI Activity" 
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
          </div>
        </div>
        
        {/* Title in theme section with difficulty badge */}
        <div className="relative z-10 flex items-start justify-between">
          <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
            <span className="line-clamp-2">{title}</span>
          </h4>
          <span className={`px-2 py-1 text-xs font-medium rounded-full ${currentDifficulty} flex-shrink-0`}>
            {difficulty.charAt(0).toUpperCase() + difficulty.slice(1)}
          </span>
        </div>
      </div>
      
      {/* Right side - Content section */}
      <div className="bg-gray-800 p-6 flex-1 flex flex-col">
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
                <span key={index} className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                  {tag}
                </span>
              ))}
            </div>
          </div>
        </div>

        {/* View Details Link */}
        <div className="mt-4 mb-8">
          <span className="text-purple-400 text-base font-medium group-hover:text-purple-300 transition-colors">
            View Details →
          </span>
        </div>
      </div>
    </div>
  )
}
