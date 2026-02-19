"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { 
  Calendar, 
  Users, 
  MapPin, 
  Clock, 
  ExternalLink,
  Eye,
  Share2,
  Heart
} from "lucide-react"
import { Button } from "@/components/ui/button"

interface BoardPreviewProps {
  title: string
  description: string
  location: string
  date: string
  time: string
  attendees: number
  maxAttendees: number
  category: string
  isLive?: boolean
  isUpcoming?: boolean
  className?: string
}

export function BoardPreview({ 
  title, 
  description, 
  location, 
  date, 
  time, 
  attendees, 
  maxAttendees,
  category,
  isLive = false,
  isUpcoming = false,
  className 
}: BoardPreviewProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.5 }}
      className={cn(
        "group relative overflow-hidden rounded-lg border bg-card transition-all duration-300 hover:shadow-lg hover:scale-105",
        className
      )}
    >
      {/* Status Badge */}
      <div className="absolute top-4 right-4 z-10">
        {isLive && (
          <span className="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 rounded-full flex items-center space-x-1">
            <div className="w-2 h-2 bg-red-500 rounded-full animate-pulse" />
            <span>Live</span>
          </span>
        )}
        {isUpcoming && (
          <span className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded-full">
            Upcoming
          </span>
        )}
      </div>

      <div className="p-6 space-y-4">
        {/* Header */}
        <div className="space-y-2">
          <div className="flex items-start justify-between">
            <h3 className="font-semibold text-lg leading-tight group-hover:text-primary transition-colors">
              {title}
            </h3>
          </div>
          <p className="text-sm text-muted-foreground line-clamp-2">
            {description}
          </p>
          <span className="inline-block px-2 py-1 text-xs font-medium bg-muted rounded-full">
            {category}
          </span>
        </div>

        {/* Event Details */}
        <div className="space-y-3">
          <div className="flex items-center space-x-2 text-sm text-muted-foreground">
            <Calendar className="h-4 w-4" />
            <span>{date}</span>
          </div>
          <div className="flex items-center space-x-2 text-sm text-muted-foreground">
            <Clock className="h-4 w-4" />
            <span>{time}</span>
          </div>
          <div className="flex items-center space-x-2 text-sm text-muted-foreground">
            <MapPin className="h-4 w-4" />
            <span>{location}</span>
          </div>
          <div className="flex items-center space-x-2 text-sm text-muted-foreground">
            <Users className="h-4 w-4" />
            <span>{attendees}/{maxAttendees} attendees</span>
          </div>
        </div>

        {/* Progress Bar */}
        <div className="space-y-2">
          <div className="flex justify-between text-xs text-muted-foreground">
            <span>Attendance</span>
            <span>{Math.round((attendees / maxAttendees) * 100)}%</span>
          </div>
          <div className="w-full bg-muted rounded-full h-2">
            <div 
              className="bg-primary h-2 rounded-full transition-all duration-300"
              style={{ width: `${(attendees / maxAttendees) * 100}%` }}
            />
          </div>
        </div>

        {/* Actions */}
        <div className="flex space-x-2 pt-2">
          <Button variant="outline" size="sm" className="flex-1 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
            <Eye className="mr-2 h-4 w-4" />
            View Details
          </Button>
          <Button size="sm" className="flex-1">
            <ExternalLink className="mr-2 h-4 w-4" />
            Join Event
          </Button>
        </div>

        {/* Social Actions */}
        <div className="flex items-center justify-between pt-2 border-t">
          <div className="flex items-center space-x-4">
            <button className="flex items-center space-x-1 text-sm text-muted-foreground hover:text-primary transition-colors">
              <Heart className="h-4 w-4" />
              <span>12</span>
            </button>
            <button className="flex items-center space-x-1 text-sm text-muted-foreground hover:text-primary transition-colors">
              <Share2 className="h-4 w-4" />
              <span>Share</span>
            </button>
          </div>
          <div className="text-xs text-muted-foreground">
            {isLive ? "Live Now" : isUpcoming ? "Starting Soon" : "Past Event"}
          </div>
        </div>
      </div>

      {/* Hover effect overlay */}
      <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
    </motion.div>
  )
}

