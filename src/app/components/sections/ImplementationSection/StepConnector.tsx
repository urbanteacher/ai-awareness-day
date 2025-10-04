"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"

interface StepConnectorProps {
  isCompleted?: boolean
  className?: string
}

export function StepConnector({ isCompleted = false, className }: StepConnectorProps) {
  return (
    <div className={cn("flex items-center justify-center py-4", className)}>
      <motion.div
        initial={{ scaleY: 0 }}
        animate={{ scaleY: 1 }}
        transition={{ duration: 0.5, delay: 0.2 }}
        className={cn(
          "w-0.5 h-12 transition-colors duration-300",
          isCompleted ? "bg-green-500" : "bg-muted"
        )}
      />
    </div>
  )
}

