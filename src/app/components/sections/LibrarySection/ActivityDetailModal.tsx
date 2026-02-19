"use client"

import { motion, AnimatePresence } from "framer-motion"
import { X, Clock, Users, Target, List, Info, Star, Tag, BookOpen, MessageCircle, Lightbulb } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { getActivityModalData } from "@/data/activities/modal-data"

interface ActivityDetailModalProps {
  activity: {
    id: string
    title: string
    theme: string
    level: string
    subject: string
    description: string
    tags: string[]
  } | null
  onClose: () => void
}

const themes = [
  { id: "safe", name: "BE SAFE", color: "bg-red-500" },
  { id: "smart", name: "BE SMART", color: "bg-blue-500" },
  { id: "creative", name: "BE CREATIVE", color: "bg-green-500" },
  { id: "responsible", name: "BE RESPONSIBLE", color: "bg-purple-500" },
  { id: "future", name: "BE FUTURE", color: "bg-orange-500" }
]

// Get activity details from modal data
const getActivityDetails = (activity: any) => {
  const modalData = getActivityModalData(activity.id)
  
  if (modalData) {
    return {
      detailedDescription: modalData.detailedDescription,
      duration: modalData.duration,
      ageRange: modalData.ageRange,
      learningObjectives: modalData.learningObjectives,
      materials: modalData.materials,
      instructions: modalData.instructions,
      discussionQuestions: modalData.discussionQuestions,
      extensionActivities: modalData.extensionActivities,
      additionalInfo: {
        level: modalData.level,
        duration: modalData.duration,
        ageRange: modalData.ageRange
      }
    }
  }
  
  // Fallback for activities without modal data
  return {
    detailedDescription: activity.description,
    duration: "As specified",
    ageRange: "All ages",
    learningObjectives: ["Learning objectives will be met"],
    materials: ["Basic materials as needed"],
    instructions: ["Follow the activity guidelines"],
    discussionQuestions: ["What did you learn from this activity?"],
    extensionActivities: ["Explore related topics"],
    additionalInfo: {
      level: activity.level || "All Levels",
      duration: "As specified",
      ageRange: "All ages"
    }
  }
}

export function ActivityDetailModal({ activity, onClose }: ActivityDetailModalProps) {
  if (!activity) return null

  const details = getActivityDetails(activity)
  const theme = themes.find(t => t.id === activity.theme)

  return (
    <AnimatePresence>
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        className="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
        onClick={onClose}
      >
        <motion.div
          initial={{ opacity: 0, scale: 0.9, y: 20 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          exit={{ opacity: 0, scale: 0.9, y: 20 }}
          className="bg-gray-800 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto"
          onClick={(e) => e.stopPropagation()}
          style={{
            clipPath: 'polygon(0% 0%, 95% 0%, 100% 5%, 100% 100%, 5% 100%, 0% 95%)'
          }}
        >
          {/* Header */}
          <div className="p-6 border-b border-gray-700">
            <div className="flex items-start justify-between">
              <div className="flex items-center space-x-4">
                <div className={`w-3 h-3 ${theme?.color} rounded-full`} />
                <div>
                  <h2 className="text-2xl font-bold text-white">{activity.title}</h2>
                  <p className="text-gray-300">{activity.description}</p>
                </div>
              </div>
              <Button
                onClick={onClose}
                variant="ghost"
                size="sm"
                className="text-gray-400 hover:text-white"
              >
                <X className="w-5 h-5" />
              </Button>
            </div>
            
            <div className="flex flex-wrap gap-2 mt-4">
              <Badge variant="outline" className="bg-gray-700 text-gray-300">
                <Star className="w-3 h-3 mr-1" />
                {activity.level}
              </Badge>
              <Badge variant="outline" className="bg-gray-700 text-gray-300">
                <Tag className="w-3 h-3 mr-1" />
                {activity.subject}
              </Badge>
              {activity.tags.slice(0, 4).map((tag, index) => (
                <Badge key={index} variant="outline" className="bg-gray-700 text-gray-300">
                  {tag.length > 20 ? `${tag.substring(0, 20)}...` : tag}
                </Badge>
              ))}
            </div>
          </div>

          {/* Content */}
          <div className="p-6 space-y-8">
            {/* Detailed Description */}
            <div>
              <h3 className="text-lg font-semibold text-white mb-4 flex items-center">
                <Info className="w-5 h-5 mr-2 text-blue-400" />
                Activity Overview
              </h3>
              <p className="text-gray-300 leading-relaxed">{details.detailedDescription}</p>
            </div>

            {/* Materials & Duration */}
            <div>
              <h3 className="text-lg font-semibold text-white mb-4 flex items-center">
                <Clock className="w-5 h-5 mr-2 text-green-400" />
                Materials & Duration
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="text-sm font-medium text-gray-300 mb-2">Materials Needed</h4>
                  <ul className="space-y-1">
                    {details.materials.map((item: string, index: number) => (
                      <li key={index} className="text-sm text-gray-400 flex items-center">
                        <div className="w-1.5 h-1.5 bg-green-400 rounded-full mr-2" />
                        {item}
                      </li>
                    ))}
                  </ul>
                </div>
                <div>
                  <h4 className="text-sm font-medium text-gray-300 mb-2">Activity Details</h4>
                  <div className="space-y-2">
                    <div className="text-sm text-gray-400">
                      <span className="font-medium">Duration:</span> {details.duration}
                    </div>
                    <div className="text-sm text-gray-400">
                      <span className="font-medium">Age Range:</span> {details.ageRange}
                    </div>
                    <div className="text-sm text-gray-400">
                      <span className="font-medium">Level:</span> {details.additionalInfo.level}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Learning Objectives and Instructions */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
              {/* Learning Objectives */}
              <div>
                <h3 className="text-lg font-semibold text-white mb-4 flex items-center">
                  <Target className="w-5 h-5 mr-2 text-green-400" />
                  Learning Objectives
                </h3>
                <ul className="space-y-2">
                  {details.learningObjectives.map((objective: string, index: number) => (
                    <li key={index} className="text-sm text-gray-300 flex items-start">
                      <div className="w-1.5 h-1.5 bg-green-400 rounded-full mr-2 mt-2 flex-shrink-0" />
                      {objective}
                    </li>
                  ))}
                </ul>
              </div>

              {/* Instructions */}
              <div>
                <h3 className="text-lg font-semibold text-white mb-4 flex items-center">
                  <List className="w-5 h-5 mr-2 text-purple-400" />
                  Instructions
                </h3>
                <ol className="space-y-3">
                  {details.instructions.map((instruction: string, index: number) => (
                    <li key={index} className="text-sm text-gray-300 flex items-start">
                      <span className="bg-purple-600 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                        {index + 1}
                      </span>
                      {instruction}
                    </li>
                  ))}
                </ol>
              </div>
            </div>

            {/* Discussion Questions and Extension Activities */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
              {/* Discussion Questions */}
              <div>
                <h3 className="text-lg font-semibold text-white mb-4 flex items-center">
                  <MessageCircle className="w-5 h-5 mr-2 text-blue-400" />
                  Discussion Questions
                </h3>
                <ul className="space-y-2">
                  {details.discussionQuestions.map((question: string, index: number) => (
                    <li key={index} className="text-sm text-gray-300 flex items-start">
                      <div className="w-1.5 h-1.5 bg-blue-400 rounded-full mr-2 mt-2 flex-shrink-0" />
                      {question}
                    </li>
                  ))}
                </ul>
              </div>

              {/* Extension Activities */}
              <div>
                <h3 className="text-lg font-semibold text-white mb-4 flex items-center">
                  <Lightbulb className="w-5 h-5 mr-2 text-yellow-400" />
                  Extension Activities
                </h3>
                <ul className="space-y-2">
                  {details.extensionActivities.map((activity: string, index: number) => (
                    <li key={index} className="text-sm text-gray-300 flex items-start">
                      <div className="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-2 mt-2 flex-shrink-0" />
                      {activity}
                    </li>
                  ))}
                </ul>
              </div>
            </div>

          </div>

          {/* Footer */}
          <div className="p-6 border-t border-gray-700 flex justify-end space-x-3">
            <Button
              onClick={onClose}
              variant="outline"
              className="bg-gray-700 text-gray-300 border-gray-600 hover:bg-gray-600"
            >
              Close
            </Button>
            <Button className="bg-purple-600 hover:bg-purple-700">
              Download Activity
            </Button>
          </div>
        </motion.div>
      </motion.div>
    </AnimatePresence>
  )
}



