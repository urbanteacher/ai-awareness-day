"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { FileText, Users, Calendar, Download } from "lucide-react"
import { Button } from "@/components/ui/button"

interface TemplateCardProps {
  title: string
  description: string
  category: string
  icon?: string
  isCustomizable?: boolean
  className?: string
}

export function TemplateCard({ 
  title, 
  description, 
  category, 
  icon,
  isCustomizable = false,
  className 
}: TemplateCardProps) {
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
      <div className="space-y-4 text-center">
        <div className="flex flex-col items-center space-y-3">
          {icon && (
            <div className="text-4xl">{icon}</div>
          )}
          <h3 className="font-semibold text-lg leading-tight">{title}</h3>
          <p className="text-sm text-muted-foreground">{description}</p>
        </div>
        
        <div className="space-y-2">
          <span className="px-3 py-1 text-xs font-medium bg-primary/10 text-primary rounded-full">
            {category}
          </span>
          {isCustomizable && (
            <div className="text-xs text-muted-foreground">
              ✓ Customizable
            </div>
          )}
        </div>
        
        <div className="pt-2">
          <Button variant="outline" size="sm" className="w-full group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
            <Download className="mr-2 h-4 w-4" />
            Download Template
          </Button>
        </div>
      </div>
      
      {/* Hover effect overlay */}
      <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
    </motion.div>
  )
}

