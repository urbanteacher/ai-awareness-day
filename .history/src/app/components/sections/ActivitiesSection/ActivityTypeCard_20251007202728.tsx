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
  className 
}: ActivityTypeCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.5 }}
      className={cn(
        "group relative overflow-hidden rounded-lg border bg-card p-6 transition-all duration-300 hover:shadow-lg hover:scale-105",
        className
      )}
    >
      <div className="space-y-4">
        <div className="flex items-start justify-between">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
              {icon}
            </div>
            <div>
              <h3 className="font-semibold text-lg">{title}</h3>
              <p className="text-sm text-muted-foreground">{category}</p>
            </div>
          </div>
          
          <span className={cn(
            "px-2 py-1 text-xs font-medium rounded-full",
            difficultyColors[difficulty]
          )}>
            {difficulty}
          </span>
        </div>
        
        <p className="text-muted-foreground text-sm leading-relaxed">
          {description}
        </p>
        
        <div className="grid grid-cols-2 gap-4 text-sm">
          <div className="flex items-center space-x-2 text-muted-foreground">
            <Clock className="h-4 w-4" />
            <span>{duration}</span>
          </div>
          <div className="flex items-center space-x-2 text-muted-foreground">
            <Users className="h-4 w-4" />
            <span>{participants}</span>
          </div>
        </div>
        
        <div className="pt-2">
          <Button variant="outline" size="sm" className="w-full group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
            <Target className="mr-2 h-4 w-4" />
            Start Activity
            <ArrowRight className="ml-2 h-4 w-4" />
          </Button>
        </div>
      </div>
      
      {/* Hover effect overlay */}
      <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
    </motion.div>
  )
}

