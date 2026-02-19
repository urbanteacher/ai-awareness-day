"use client"

import { Star, ChevronRight } from "lucide-react"
import { cn } from "@/lib/utils"

interface SplitImageActivityCardProps {
  activity: {
    id: string
    title: string
    description: string
    theme: string
    level: string
    subject: string
    tags: string[]
    imageUrl?: string
  }
  onClick?: () => void
  className?: string
}

export function SplitImageActivityCard({ activity, onClick, className }: SplitImageActivityCardProps) {
  const getThemeGradient = (theme: string) => {
    switch (theme.toLowerCase()) {
      case 'safe':
        return 'from-red-500 to-red-600'
      case 'smart':
        return 'from-blue-500 to-blue-600'
      case 'creative':
        return 'from-purple-500 to-pink-500'
      case 'responsible':
        return 'from-purple-500 to-purple-600'
      case 'future':
        return 'from-orange-500 to-orange-600'
      default:
        return 'from-gray-500 to-gray-600'
    }
  }

  const getThemeDotColor = (theme: string) => {
    switch (theme.toLowerCase()) {
      case 'safe':
        return 'bg-red-500'
      case 'smart':
        return 'bg-blue-500'
      case 'creative':
        return 'bg-white'
      case 'responsible':
        return 'bg-purple-500'
      case 'future':
        return 'bg-orange-500'
      default:
        return 'bg-gray-500'
    }
  }

  const getThemeLabel = (theme: string) => {
    switch (theme.toLowerCase()) {
      case 'safe':
        return 'BE SAFE'
      case 'smart':
        return 'BE SMART'
      case 'creative':
        return 'BE CREATIVE'
      case 'responsible':
        return 'BE RESPONSIBLE'
      case 'future':
        return 'BE FUTURE'
      default:
        return theme.toUpperCase()
    }
  }

  const getDifficultyColor = (level: string) => {
    switch (level.toLowerCase()) {
      case 'beginner':
        return 'bg-green-100 text-green-800'
      case 'intermediate':
        return 'bg-yellow-100 text-yellow-800'
      case 'advanced':
        return 'bg-red-100 text-red-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  return (
    <div
      className={cn(
        "bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96",
        className
      )}
      style={{
        clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
      }}
      onClick={onClick}
    >
      {/* Left side - Theme section with image */}
      <div 
        className={`flex-1 bg-gradient-to-br ${getThemeGradient(activity.theme)} p-6 relative overflow-hidden`}
      >
        {/* Background image overlay */}
        {activity.imageUrl && (
          <div 
            className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
            style={{
              backgroundImage: `url('${activity.imageUrl}')`
            }}
          />
        )}
        
        {/* Dark overlay for text readability */}
        <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-300" />
        
        {/* Decorative Polygon Corner */}
        <div 
          className="absolute top-0 right-0 w-8 h-8 bg-white/20"
          style={{
            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
          }}
        />
        
        {/* Theme header */}
        <div className="relative z-10 flex items-center space-x-2 mb-4">
          <div className={`w-3 h-3 rounded-full ${getThemeDotColor(activity.theme)}`} />
          <span className="text-sm font-medium text-white">{getThemeLabel(activity.theme)}</span>
        </div>
        
        {/* Image content in theme section */}
        {activity.imageUrl && (
          <div className="relative z-10 mb-4">
            <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
              <img 
                src={activity.imageUrl} 
                alt={activity.title} 
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
          </div>
        )}
        
        {/* Title in theme section with difficulty badge */}
        <div className="relative z-10 flex items-start justify-between">
          <h4 className="text-xl font-bold text-white leading-tight flex-1 mr-4">
            <span className="line-clamp-2">{activity.title}</span>
          </h4>
          <span className={`px-2 py-1 text-xs font-medium rounded-full ${getDifficultyColor(activity.level)} flex-shrink-0`}>
            {activity.level}
          </span>
        </div>
      </div>
      
      {/* Right side - Content section */}
      <div className="bg-gray-800 p-6 flex-1 flex flex-col">
        {/* Description */}
        <div className="flex-1">
          <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
            <span className="line-clamp-3">{activity.description}</span>
          </p>
        </div>

        {/* Tags */}
        <div className="mt-4 space-y-2">
          <div className="flex items-center space-x-2">
            <span className="text-xs text-gray-400">Tags</span>
            <div className="flex flex-wrap gap-1">
              {activity.tags.slice(0, 2).map((tag, index) => (
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
