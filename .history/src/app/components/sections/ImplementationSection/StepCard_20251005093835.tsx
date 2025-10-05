"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { CheckCircle, Circle } from "lucide-react"

interface StepCardProps {
  step: number
  title: string
  description: string
  icon?: string
  isActive?: boolean
  isCompleted?: boolean
  className?: string
}

export function StepCard({ 
  step, 
  title, 
  description, 
  icon,
  isActive = false, 
  isCompleted = false,
  className 
}: StepCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: step * 0.1 }}
      className={cn(
        "relative bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white transition-all duration-300 hover:scale-105 hover:shadow-xl h-full group",
        isActive && "ring-2 ring-blue-400/50",
        isCompleted && "bg-green-100 dark:bg-green-900",
        className
      )}
      style={{
        clipPath: 'polygon(0% 0%, 85% 0%, 100% 15%, 100% 100%, 15% 100%, 0% 85%)'
      }}
    >
      <div className="space-y-0">
        {/* Colored Header with Title */}
        <div 
          className="relative h-24 overflow-hidden"
          style={{
            clipPath: 'polygon(0% 0%, 85% 0%, 100% 15%, 100% 100%, 15% 100%, 0% 85%)'
          }}
        >
          <div className={cn(
            "absolute inset-0",
            step === 1 && "bg-gradient-to-r from-purple-500 to-purple-600",
            step === 2 && "bg-gradient-to-r from-blue-500 to-blue-600", 
            step === 3 && "bg-gradient-to-r from-teal-500 to-teal-600",
            step === 4 && "bg-gradient-to-r from-green-500 to-green-600"
          )}>
            <div className="absolute inset-0 bg-gray-800/20"></div>
            <div className="absolute top-3 left-4">
              <div className="px-3 py-1 rounded-full bg-gray-800 text-white text-xs font-bold">
                Step {step}
              </div>
            </div>
            
            {/* Title in colored area */}
            <div className="absolute bottom-3 left-4 right-4">
              <h3 className="text-lg font-semibold text-white leading-tight">{title}</h3>
            </div>
            
            {/* Geometric Pattern */}
            <div className="absolute bottom-0 right-0 w-16 h-16 opacity-20">
              <div className="w-full h-full bg-gray-800/30 rounded-full"></div>
            </div>
          </div>
        </div>
        
        {/* Content */}
        <div className="p-6 bg-gray-800">
          <p className="text-white text-base leading-relaxed">{description}</p>
        </div>
      </div>
    </motion.div>
  )
}

