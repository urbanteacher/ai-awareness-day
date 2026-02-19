"use client"

import { Star, ChevronRight, Tag } from "lucide-react"
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
        "bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80",
        className
      )}
      style={{
        clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
      }}
      onClick={onClick}
    >
      {/* Theme-colored top section - 12% height */}
      <div 
        className={`absolute inset-0 bg-gradient-to-r ${getThemeGradient(activity.theme)} opacity-20 group-hover:opacity-30 transition-opacity duration-300`}
        style={{
          clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
        }}
      />
      
      {/* Decorative Polygon Corner */}
      <div 
        className="absolute top-0 right-0 w-8 h-8 bg-white/10 dark:bg-black/10"
        style={{
          clipPath: "polygon(100% 0, 0 0, 100% 100%)"
        }}
      />
      
      {/* Activity Header */}
      <div className="relative z-10 flex items-start justify-between mb-4">
        <div className="flex items-center space-x-2">
          <div className={`w-3 h-3 rounded-full ${getThemeDotColor(activity.theme)}`} />
          <span className="text-sm font-medium text-white">{getThemeLabel(activity.theme)}</span>
        </div>
        <div className="flex items-center space-x-1 text-yellow-400 mr-4">
          <Star className="w-4 h-4 fill-current" />
          <span className="text-sm font-medium text-white">{activity.level}</span>
        </div>
      </div>

      {/* Activity Content */}
      <div className="relative z-10 flex-1 flex flex-col">
        <div className="space-y-3 flex-1">
          <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
            <span className="line-clamp-2">{activity.title}</span>
          </h4>
          <p className="text-white text-sm leading-relaxed line-clamp-2">
            {activity.description}
          </p>
        </div>

        {/* Activity Meta */}
        <div className="mt-4 space-y-2">
          <div className="flex items-center space-x-2">
            <Tag className="w-4 h-4 text-gray-400" />
            <span className="text-xs text-gray-400">{activity.subject}</span>
          </div>
          
          <div className="flex flex-wrap gap-1">
            {activity.tags.slice(0, 2).map((tag: string, tagIndex: number) => (
              <span 
                key={tagIndex}
                className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]"
                title={tag}
              >
                {tag.length > 15 ? `${tag.substring(0, 15)}...` : tag}
              </span>
            ))}
          </div>
        </div>

        {/* View Details Link */}
        <div className="mt-2 pt-2 border-t border-gray-700">
          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2">
            View Details <ChevronRight className="w-4 h-4 inline ml-1" />
          </span>
        </div>
      </div>
    </div>
  )
}
