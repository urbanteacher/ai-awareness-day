"use client"

import { motion } from "framer-motion"
import { ChevronRight } from "lucide-react"
import { cn } from "@/lib/utils"

interface SplitImageCardProps {
  activity: {
    id: string
    title: string
    description: string
    theme: string
    level: string
    subject: string
    tags: string[]
  }
  onClick?: () => void
  className?: string
}

const themeConfig = {
  safe: {
    gradient: "from-red-500 to-red-600",
    dotColor: "bg-red-500",
    label: "BE SAFE"
  },
  smart: {
    gradient: "from-blue-500 to-blue-600", 
    dotColor: "bg-blue-500",
    label: "BE SMART"
  },
  creative: {
    gradient: "from-green-500 to-green-600",
    dotColor: "bg-green-500", 
    label: "BE CREATIVE"
  },
  responsible: {
    gradient: "from-purple-500 to-purple-600",
    dotColor: "bg-purple-500",
    label: "BE RESPONSIBLE"
  },
  future: {
    gradient: "from-orange-500 to-orange-600",
    dotColor: "bg-orange-500",
    label: "BE FUTURE"
  }
}

const difficultyColors = {
  Beginner: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
  Intermediate: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400", 
  Advanced: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400",
  All: "bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
}

export function SplitImageCard({ activity, onClick, className }: SplitImageCardProps) {
  const theme = themeConfig[activity.theme as keyof typeof themeConfig] || themeConfig.smart
  
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.4 }}
      className={cn(
        "bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl",
        className
      )}
      style={{
        clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
      }}
      onClick={onClick}
    >
      {/* Theme section with image */}
      <div className={cn("bg-gradient-to-br p-6 relative overflow-hidden", theme.gradient)}>
        {/* Background image overlay */}
        <div 
          className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
          style={{
            backgroundImage: "url('https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center')"
          }}
        />
        
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
          <div className={cn("w-3 h-3 rounded-full", theme.dotColor)} />
          <span className="text-sm font-medium text-white">{theme.label}</span>
        </div>
        
        {/* Image content in theme section */}
        <div className="relative z-10 mb-4">
          <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
            <img 
              src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=200&fit=crop&crop=center" 
              alt={activity.title} 
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
          </div>
        </div>
        
        {/* Title in theme section with difficulty badge */}
        <div className="relative z-10 flex items-start justify-between">
          <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
            <span className="line-clamp-2">{activity.title}</span>
          </h4>
          <span className={cn(
            "px-2 py-1 text-xs font-medium rounded-full flex-shrink-0",
            difficultyColors[activity.level as keyof typeof difficultyColors] || difficultyColors.All
          )}>
            {activity.level}
          </span>
        </div>
      </div>
      
      {/* Content section */}
      <div className="bg-gray-800 p-6 flex flex-col">
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
                <span 
                  key={index}
                  className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]"
                  title={tag}
                >
                  {tag.length > 15 ? `${tag.substring(0, 15)}...` : tag}
                </span>
              ))}
            </div>
          </div>
        </div>

        {/* View Details Link */}
        <div className="mt-4 mb-8">
          <span className="text-purple-400 text-base font-medium group-hover:text-purple-300 transition-colors">
            View Details <ChevronRight className="w-4 h-4 inline ml-1" />
          </span>
        </div>
      </div>
    </motion.div>
  )
}
