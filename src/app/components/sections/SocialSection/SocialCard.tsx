"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { 
  MessageCircle, 
  Heart, 
  Share2, 
  MoreHorizontal,
  User,
  Clock,
  ExternalLink
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"

interface SocialCardProps {
  id: string
  author: {
    name: string
    avatar?: string
    role: string
  }
  content: string
  timestamp: string
  likes: number
  comments: number
  shares: number
  isLiked?: boolean
  category: string
  imageUrl?: string
  className?: string
  style?: React.CSSProperties
}

export function SocialCard({ 
  id,
  author,
  content,
  timestamp,
  likes,
  comments,
  shares,
  isLiked = false,
  category,
  imageUrl,
  className,
  style
}: SocialCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ duration: 0.5 }}
      className={cn(
        "group relative overflow-hidden rounded-lg border bg-card transition-all duration-300 hover:shadow-lg",
        className
      )}
      style={style}
    >
      <div className="p-6 space-y-4">
        {/* Header */}
        <div className="flex items-start justify-between">
          <div className="flex items-center space-x-3">
            <Avatar className="h-10 w-10">
              <AvatarImage src={author.avatar} alt={author.name} />
              <AvatarFallback>
                {author.name.split(' ').map(n => n[0]).join('')}
              </AvatarFallback>
            </Avatar>
            <div className="space-y-1">
              <div className="flex items-center space-x-2">
                <h4 className="font-semibold text-sm">{author.name}</h4>
                <span className="px-2 py-0.5 text-xs font-medium bg-muted rounded-full">
                  {category}
                </span>
              </div>
              <p className="text-xs text-muted-foreground">{author.role}</p>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            <span className="text-xs text-muted-foreground flex items-center space-x-1">
              <Clock className="h-3 w-3" />
              <span>{timestamp}</span>
            </span>
            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
              <MoreHorizontal className="h-4 w-4" />
            </Button>
          </div>
        </div>

        {/* Content */}
        <div className="space-y-3">
          <p className="text-sm leading-relaxed">{content}</p>
          
          {imageUrl && (
            <div className="relative overflow-hidden rounded-lg">
              <img 
                src={imageUrl} 
                alt="Post content" 
                className="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
          )}
        </div>

        {/* Actions */}
        <div className="flex items-center justify-between pt-3 border-t">
          <div className="flex items-center space-x-6">
            <button className={cn(
              "flex items-center space-x-2 text-sm transition-colors",
              isLiked 
                ? "text-red-500 hover:text-red-600" 
                : "text-muted-foreground hover:text-red-500"
            )}>
              <Heart className={cn("h-4 w-4", isLiked && "fill-current")} />
              <span>{likes}</span>
            </button>
            
            <button className="flex items-center space-x-2 text-sm text-muted-foreground hover:text-primary transition-colors">
              <MessageCircle className="h-4 w-4" />
              <span>{comments}</span>
            </button>
            
            <button className="flex items-center space-x-2 text-sm text-muted-foreground hover:text-primary transition-colors">
              <Share2 className="h-4 w-4" />
              <span>{shares}</span>
            </button>
          </div>
          
          <Button variant="ghost" size="sm" className="text-muted-foreground hover:text-primary">
            <ExternalLink className="h-4 w-4" />
          </Button>
        </div>
      </div>

      {/* Hover effect overlay */}
      <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
    </motion.div>
  )
}

