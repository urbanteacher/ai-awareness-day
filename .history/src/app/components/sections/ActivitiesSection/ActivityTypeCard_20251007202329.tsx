"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { Clock, Users, Target, ArrowRight } from "lucide-react"
import { Button } from "@/components/ui/button"

interface ActivityTypeCardProps {
  title: string
  description: string
  duration: string
  participants: string
  difficulty: "Beginner" | "Intermediate" | "Advanced"
  category: string
  icon: React.ReactNode
  themeColor: string
  themeName: string
  imageUrl?: string
  features: string[]
  className?: string
}

const difficultyColors = {
  Beginner: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
  Intermediate: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400",
  Advanced: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
}

export function ActivityTypeCard({ 
  title, 
  description, 
  duration, 
  participants, 
  difficulty,
  category,
  icon,
  themeColor,
  themeName,
  imageUrl = "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center",
  features,
  className 
}: ActivityTypeCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.5 }}
      className={cn(
        "group relative overflow-hidden border border-gray-600 hover:border-gray-500 transition-all duration-300 cursor-pointer flex flex-col hover:scale-105 hover:shadow-2xl",
        className
      )}
      style={{
        clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
      }}
    >
      {/* Theme section with gradient and image */}
      <div className={`bg-gradient-to-br ${themeColor} p-6 relative overflow-hidden`}>
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
        <div 
          className="absolute top-0 right-0 w-8 h-8 bg-white/20"
          style={{
            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
          }}
        />
        
        {/* Theme header */}
        <div className="relative z-10 flex items-center space-x-2 mb-4">
          <div className="w-3 h-3 rounded-full bg-white" />
          <span className="text-sm font-medium text-white">{themeName}</span>
        </div>
        
        {/* Image content in theme section */}
        <div className="relative z-10 mb-4">
          <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
            <img 
              src={imageUrl} 
              alt={title} 
              className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
          </div>
        </div>
        
        {/* Title in theme section with difficulty badge */}
        <div className="relative z-10 flex items-start justify-between">
          <h3 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
            <span className="line-clamp-2">{title}</span>
          </h3>
          <span className={cn(
            "px-2 py-1 text-xs font-medium rounded-full flex-shrink-0",
            difficultyColors[difficulty]
          )}>
            {difficulty}
          </span>
        </div>
      </div>
      
      {/* Content section */}
      <div className="bg-gray-800 p-6 flex flex-col">
        {/* Description */}
        <div className="flex-1">
          <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
            <span className="line-clamp-3">{description}</span>
          </p>
        </div>

        {/* Features as Tags */}
        <div className="mt-4 space-y-2">
          <div className="flex items-center space-x-2">
            <span className="text-xs text-gray-400">Features</span>
            <div className="flex flex-wrap gap-1">
              {features.slice(0, 2).map((feature, index) => (
                <span key={index} className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                  {feature}
                </span>
              ))}
            </div>
          </div>
        </div>

        {/* Duration and Participants */}
        <div className="mt-4 space-y-2">
          <div className="flex items-center space-x-2 text-gray-300">
            <Clock className="h-4 w-4" />
            <span className="text-sm">{duration}</span>
          </div>
          <div className="flex items-center space-x-2 text-gray-300">
            <Users className="h-4 w-4" />
            <span className="text-sm">{participants}</span>
          </div>
        </div>

        {/* View Details Link */}
        <div className="mt-4 mb-8">
          <span className="text-purple-400 text-base font-medium group-hover:text-purple-300 transition-colors">
            View Details →
          </span>
        </div>
      </div>
    </motion.div>
  )
}

