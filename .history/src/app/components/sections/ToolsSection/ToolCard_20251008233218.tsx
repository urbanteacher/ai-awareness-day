"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { 
  ExternalLink, 
  Download, 
  Play, 
  Code,
  Settings,
  BarChart3,
  Shield,
  Zap
} from "lucide-react"
import { Button } from "@/components/ui/button"

interface ToolCardProps {
  title: string
  description: string
  category: string
  type: "web" | "desktop" | "api" | "cli"
  status: "available" | "beta" | "coming-soon"
  features: string[]
  icon: React.ReactNode
  downloadUrl?: string
  demoUrl?: string
  docsUrl?: string
  className?: string
  style?: React.CSSProperties
}

const typeIcons = {
  web: <ExternalLink className="h-4 w-4" />,
  desktop: <Download className="h-4 w-4" />,
  api: <Code className="h-4 w-4" />,
  cli: <Settings className="h-4 w-4" />
}

const statusColors = {
  available: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400",
  beta: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400",
  "coming-soon": "bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
}

const categoryIcons = {
  "AI Ethics": <Shield className="h-5 w-5" />,
  "Analytics": <BarChart3 className="h-5 w-5" />,
  "Development": <Code className="h-5 w-5" />,
  "Automation": <Zap className="h-5 w-5" />
}

export function ToolCard({ 
  title, 
  description, 
  category, 
  type,
  status,
  features,
  icon,
  downloadUrl,
  demoUrl,
  docsUrl,
  className,
  style
}: ToolCardProps) {
  const isDisabled = status === "coming-soon"

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.5 }}
      className={cn(
        "group relative overflow-hidden transition-all duration-300 hover:scale-105",
        isDisabled && "opacity-60 cursor-not-allowed",
        className
      )}
      style={style}
    >
      {/* Polygon Card with Grey Theme */}
      <div 
        className="bg-gray-800 text-white overflow-hidden shadow-2xl hover:shadow-3xl transition-all duration-300"
        style={{ 
          clipPath: 'polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))' 
        }}
      >
        {/* Header with Blue Gradient */}
        <div className="bg-gradient-to-r from-blue-600 to-blue-500 p-4">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
              {categoryIcons[category as keyof typeof categoryIcons] || icon}
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="font-bold text-lg text-white leading-tight">
                {title}
              </h3>
              <p className="text-blue-100 text-sm">{category}</p>
            </div>
            {/* Status Badge */}
            <div className="flex-shrink-0">
              <span className={cn(
                "px-2 py-1 text-xs font-medium rounded-full",
                status === "available" ? "bg-green-500/20 text-green-300" : 
                status === "beta" ? "bg-yellow-500/20 text-yellow-300" : 
                "bg-gray-500/20 text-gray-300"
              )}>
                {status === "coming-soon" ? "Coming Soon" : status === "beta" ? "Beta" : "Available"}
              </span>
            </div>
          </div>
        </div>

        {/* Content Area */}
        <div className="p-6 bg-gray-800 space-y-4">
          {/* Description */}
          <p className="text-gray-200 text-sm leading-relaxed">
            {description}
          </p>

          {/* Features */}
          <div className="space-y-2">
            <h4 className="text-sm font-medium text-gray-300">Key Features:</h4>
            <ul className="space-y-1">
              {/* Show 2 features on mobile, 3 on desktop */}
              {features.slice(0, 2).map((feature, index) => (
                <li key={index} className="text-xs text-gray-300 flex items-center space-x-2">
                  <div className="w-1 h-1 bg-blue-400 rounded-full flex-shrink-0" />
                  <span>{feature}</span>
                </li>
              ))}
              {/* Show 3rd feature only on desktop */}
              <li className="hidden sm:block text-xs text-gray-300 flex items-center space-x-2">
                <div className="w-1 h-1 bg-blue-400 rounded-full flex-shrink-0" />
                <span>{features[2]}</span>
              </li>
              {features.length > 3 && (
                <li className="text-xs text-gray-400">
                  +{features.length - 3} more features
                </li>
              )}
            </ul>
          </div>

          {/* Type Indicator */}
          <div className="flex items-center space-x-2 text-sm text-gray-400">
            {typeIcons[type]}
            <span className="capitalize">{type} tool</span>
          </div>

          {/* Actions */}
          <div className="flex space-x-2 pt-2">
            {status === "available" && (
              <>
                {downloadUrl && (
                  <Button 
                    variant="outline" 
                    size="sm" 
                    className="flex-1 bg-gray-700 border-gray-600 text-white hover:bg-gray-600 hover:border-gray-500"
                    asChild
                  >
                    <a href={downloadUrl} target="_blank" rel="noopener noreferrer">
                      <Download className="mr-2 h-4 w-4" />
                      Download
                    </a>
                  </Button>
                )}
                {demoUrl && (
                  <Button 
                    size="sm" 
                    className="flex-1 bg-blue-600 hover:bg-blue-700 text-white"
                    asChild
                  >
                    <a href={demoUrl} target="_blank" rel="noopener noreferrer">
                      <ExternalLink className="mr-2 h-4 w-4" />
                      Visit Website
                    </a>
                  </Button>
                )}
              </>
            )}
            
            {status === "beta" && (
              <Button 
                variant="outline" 
                size="sm" 
                className="w-full bg-gray-700 border-gray-600 text-white hover:bg-gray-600 hover:border-gray-500"
                asChild
              >
                <a href={demoUrl || "#"} target="_blank" rel="noopener noreferrer">
                  <Play className="mr-2 h-4 w-4" />
                  Try Beta
                </a>
              </Button>
            )}
            
            {status === "coming-soon" && (
              <Button 
                variant="outline" 
                size="sm" 
                className="w-full bg-gray-700 border-gray-600 text-gray-400"
                disabled
              >
                Coming Soon
              </Button>
            )}
          </div>

        </div>

        {/* Decorative corner polygon */}
        <div className="absolute top-2 right-2 w-6 h-6 bg-blue-400/20 rounded-sm" 
             style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
      </div>
    </motion.div>
  )
}

