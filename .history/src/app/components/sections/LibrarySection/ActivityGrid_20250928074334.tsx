"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { 
  FileText, 
  Download, 
  Eye, 
  Calendar, 
  User, 
  Star,
  Clock,
  Users
} from "lucide-react"
import { Button } from "@/components/ui/button"

interface ActivityGridProps {
  activities: Array<{
    id: string
    title: string
    description: string
    category: string
    difficulty: string
    duration: string
    participants: string
    author: string
    publishedAt: string
    downloads: number
    rating: number
    isNew?: boolean
    isFeatured?: boolean
  }>
  className?: string
}

export function ActivityGrid({ activities, className }: ActivityGridProps) {
  const getDifficultyColor = (difficulty: string) => {
    if (!difficulty) return "bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
    
    switch (difficulty.toLowerCase()) {
      case "beginner":
        return "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400"
      case "intermediate":
        return "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400"
      case "advanced":
        return "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
      default:
        return "bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
    }
  }

  const getCategoryIcon = (category: string) => {
    if (!category) return <FileText className="h-4 w-4" />
    
    switch (category.toLowerCase()) {
      case "guides":
        return <FileText className="h-4 w-4" />
      case "templates":
        return <FileText className="h-4 w-4" />
      case "tools":
        return <FileText className="h-4 w-4" />
      case "research":
        return <FileText className="h-4 w-4" />
      case "case-studies":
        return <FileText className="h-4 w-4" />
      default:
        return <FileText className="h-4 w-4" />
    }
  }

  return (
    <div className={cn("grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6", className)}>
      {activities.map((activity, index) => (
        <motion.div
          key={activity.id}
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.5, delay: index * 0.1 }}
          className="group relative overflow-hidden rounded-lg border bg-card transition-all duration-300 hover:shadow-lg hover:scale-105"
        >
          {/* Badges */}
          <div className="absolute top-4 right-4 z-10 flex space-x-2">
            {activity.isNew && (
              <span className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full">
                New
              </span>
            )}
            {activity.isFeatured && (
              <span className="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 rounded-full">
                Featured
              </span>
            )}
          </div>

          <div className="p-6 space-y-4">
            {/* Header */}
            <div className="space-y-2">
              <div className="flex items-start space-x-3">
                <div className="flex-shrink-0">
                  <div className="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                    {getCategoryIcon(activity.category)}
                  </div>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="font-semibold text-lg leading-tight group-hover:text-primary transition-colors">
                    {activity.title}
                  </h3>
                  <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
                    {activity.description}
                  </p>
                </div>
              </div>
            </div>

            {/* Meta Information */}
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className={cn(
                  "px-2 py-1 text-xs font-medium rounded-full",
                  getDifficultyColor(activity.difficulty)
                )}>
                  {activity.difficulty}
                </span>
                <div className="flex items-center space-x-1 text-sm text-muted-foreground">
                  <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" />
                  <span>{activity.rating}</span>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3 text-sm text-muted-foreground">
                <div className="flex items-center space-x-2">
                  <Clock className="h-4 w-4" />
                  <span>{activity.duration}</span>
                </div>
                <div className="flex items-center space-x-2">
                  <Users className="h-4 w-4" />
                  <span>{activity.participants}</span>
                </div>
              </div>

              <div className="flex items-center justify-between text-sm text-muted-foreground">
                <div className="flex items-center space-x-2">
                  <User className="h-4 w-4" />
                  <span>{activity.author}</span>
                </div>
                <div className="flex items-center space-x-2">
                  <Download className="h-4 w-4" />
                  <span>{activity.downloads}</span>
                </div>
              </div>
            </div>

            {/* Actions */}
            <div className="flex space-x-2 pt-2">
              <Button variant="outline" size="sm" className="flex-1 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <Eye className="mr-2 h-4 w-4" />
                Preview
              </Button>
              <Button size="sm" className="flex-1">
                <Download className="mr-2 h-4 w-4" />
                Download
              </Button>
            </div>
          </div>

          {/* Hover effect overlay */}
          <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
        </motion.div>
      ))}
    </div>
  )
}
