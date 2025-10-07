"use client"

import { Container, SectionWrapper } from "@/components/ui"
import { motion } from "framer-motion"
import { useState } from "react"
import { Clock, Users, Presentation, BookOpen, Filter, Star, Tag, ChevronRight, Heart } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { ActivityDetailModal } from "./ActivityDetailModal"
import { activityLibraries, themes } from "@/data/activities"
import { getActivityModalData } from "@/data/activities/modal-data"

// Icon mapping for libraries - preserving exact styling
const iconMap = {
  "lesson-starters": Clock,
  "tutor-time": Users,
  "assemblies": Presentation,
  "cross-curricular": BookOpen,
  "after-school-clubs": BookOpen
}

export default function LibrarySection() {
  const [selectedTheme, setSelectedTheme] = useState("all")
  const [selectedActivity, setSelectedActivity] = useState(null)
  const [expandedLibraries, setExpandedLibraries] = useState<Set<string>>(new Set())
  
  // Using static data from separate file
  const loading = false
  const error = null

  // Define the order for libraries based on duration (shortest to longest)
  const libraryOrder = [
    "lesson-starters",    // 5 min - 🥇 Shortest
    "tutor-time",        // 15-20 min - 🥈 
    "assemblies",        // 20 min - 🥉
    "after-school-clubs", // 30-45 min - 🏅
    "cross-curricular"   // 40 min - 🏆 Longest
  ]

  const filteredLibraries = activityLibraries
    .map(library => ({
      ...library,
      activities: selectedTheme === "all" 
        ? library.activities 
        : library.activities.filter(activity => activity.theme === selectedTheme)
    }))
    .sort((a, b) => {
      const aIndex = libraryOrder.indexOf(a.id)
      const bIndex = libraryOrder.indexOf(b.id)
      return aIndex - bIndex
    })

  const toggleLibraryExpansion = (libraryId: string) => {
    const newExpanded = new Set(expandedLibraries)
    if (newExpanded.has(libraryId)) {
      newExpanded.delete(libraryId)
    } else {
      newExpanded.add(libraryId)
    }
    setExpandedLibraries(newExpanded)
  }

  const getVisibleActivities = (library: any) => {
    const isExpanded = expandedLibraries.has(library.id)
    return isExpanded ? library.activities : library.activities.slice(0, 6) // Show 6 activities initially
  }

  // Show loading state while preserving exact styling
  if (loading) {
    return (
      <SectionWrapper className="bg-background">
        <Container>
          <div className="space-y-16">
            <div className="space-y-4 text-center">
              <p className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
                Ready-to-use content organized by format
              </p>
              <h2 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl text-foreground">
                Activity Libraries
              </h2>
              <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
                Comprehensive collection of activities across all formats and themes
              </p>
            </div>
            <div className="flex justify-center">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
            </div>
          </div>
        </Container>
      </SectionWrapper>
    )
  }

  // Show error state while preserving exact styling
  if (error) {
    return (
      <SectionWrapper className="bg-background">
        <Container>
          <div className="space-y-16">
            <div className="space-y-4 text-center">
              <p className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
                Ready-to-use content organized by format
              </p>
              <h2 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl text-foreground">
                Activity Libraries
              </h2>
              <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
                Comprehensive collection of activities across all formats and themes
              </p>
            </div>
            <div className="text-center text-red-500">
              <p>Error loading activities: {error}</p>
            </div>
          </div>
        </Container>
      </SectionWrapper>
    )
  }

  return (
    <SectionWrapper className="bg-background">
      <Container>
        <div className="space-y-16">
          <div className="space-y-4 text-center">
            <p className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
              Ready-to-use content organized by format
            </p>
            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl text-purple-600 dark:text-purple-400">
              Activity Libraries
            </h2>
            <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
              Comprehensive collection of activities across all formats and themes
            </p>
          </div>

          {/* Theme Filter */}
          <div className="flex flex-wrap justify-center gap-3">
            {themes.map((theme) => (
              <Button
                key={theme.id}
                variant={selectedTheme === theme.id ? "default" : "outline"}
                onClick={() => setSelectedTheme(theme.id)}
                className={`${
                  selectedTheme === theme.id
                    ? "bg-purple-600 hover:bg-white hover:text-purple-600 text-white"
                    : "border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-600 dark:hover:bg-gray-800 dark:hover:text-white"
                }`}
              >
                <div className={`w-3 h-3 rounded-full ${theme.color} mr-2`} />
                {theme.name}
              </Button>
            ))}
          </div>

          {/* Activity Libraries */}
          <div className="space-y-12">
            {filteredLibraries.map((library) => {
              const visibleActivities = getVisibleActivities(library)
              const isExpanded = expandedLibraries.has(library.id)
              const hasMoreActivities = library.activities.length > 6

              return (
                <motion.div
                  key={library.id}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, amount: 0.3 }}
                  transition={{ duration: 0.6 }}
                  className="relative"
                >
                  
                  {/* Library Header */}
                  <div className="flex items-center justify-between mb-8">
                    <div className="flex items-center space-x-4">
                      <div
                        className={`w-12 h-12 rounded-xl bg-gradient-to-br ${library.color} flex items-center justify-center`}
                      >
                        <library.icon className="w-6 h-6 text-white" />
                      </div>
                      <div>
                        <h3 className="text-4xl font-bold text-foreground leading-tight h-16 flex items-center">
                          <span className="line-clamp-2">{library.title}</span>
                        </h3>
                        <p className="text-muted-foreground">{library.description}</p>
                      </div>
                    </div>
                    <div className="text-right">
                      <Badge className="text-lg px-4 py-2 text-white border-transparent hover:opacity-90 bg-purple-600 dark:bg-purple-500">
                        {library.duration}
                      </Badge>
                    </div>
                  </div>

                  {/* Activities Grid */}
                  {visibleActivities.length > 0 ? (
                    <>
                      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {visibleActivities.map((activity: any, activityIndex: number) => (
                        <motion.div
                          key={activity.id}
                          initial={{ opacity: 0, y: 20 }}
                          whileInView={{ opacity: 1, y: 0 }}
                          viewport={{ once: true, amount: 0.3 }}
                          transition={{ duration: 0.4, delay: activityIndex * 0.1 }}
                          className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden h-80 flex flex-col hover:scale-105 hover:shadow-2xl"
                          style={{
                            clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                          }}
                          onClick={() => setSelectedActivity(activity)}
                        >
                          {/* Theme-colored top section - 12% height */}
                          <div 
                            className={`absolute inset-0 bg-gradient-to-r ${
                              activity.theme === 'safe' ? 'from-red-500 to-red-600' :
                              activity.theme === 'smart' ? 'from-blue-500 to-blue-600' :
                              activity.theme === 'creative' ? 'from-green-500 to-green-600' :
                              activity.theme === 'responsible' ? 'from-purple-500 to-purple-600' :
                              activity.theme === 'future' ? 'from-orange-500 to-orange-600' : 'from-gray-500 to-gray-600'
                            } opacity-20 group-hover:opacity-30 transition-opacity duration-300`}
                            style={{
                              clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                            }}
                          />
                          
                          {/* Decorative Polygon Corner */}
                          <div 
                            className="absolute top-0 right-0 w-8 h-8 bg-white/10 dark:bg-black/10"
                            style={{
                              clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                            }}
                          />
                          
                          {/* Activity Header */}
                          <div className="relative z-10 flex items-start justify-between mb-4">
                            <div className="flex items-center space-x-2">
                              <div className={`w-3 h-3 rounded-full ${
                                activity.theme === 'safe' ? 'bg-red-500' :
                                activity.theme === 'smart' ? 'bg-blue-500' :
                                activity.theme === 'creative' ? 'bg-green-500' :
                                activity.theme === 'responsible' ? 'bg-purple-500' :
                                activity.theme === 'future' ? 'bg-orange-500' : 'bg-gray-500'
                              }`} />
                              <span className="text-sm font-medium text-white">
                                {activity.theme === 'safe' ? 'BE SAFE' :
                                 activity.theme === 'smart' ? 'BE SMART' :
                                 activity.theme === 'creative' ? 'BE CREATIVE' :
                                 activity.theme === 'responsible' ? 'BE RESPONSIBLE' :
                                 activity.theme === 'future' ? 'BE FUTURE' : activity.theme?.toUpperCase()}
                              </span>
                            </div>
                            <div className="flex items-center space-x-1 text-yellow-400 mr-4">
                              <Star className="w-4 h-4 fill-current" />
                              <span className="text-sm font-medium text-white">{activity.level}</span>
                            </div>
                          </div>

                          {/* Activity Content */}
                          <div className="relative z-10 flex-1 flex flex-col">
                            <div className="space-y-3 flex-1">
                              <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                                <span className="line-clamp-2">{activity.title}</span>
                              </h4>
                              <p className="text-white text-sm leading-relaxed line-clamp-2">
                                {activity.description}
                              </p>
                            </div>

                            {/* Activity Meta */}
                            <div className="mt-4 space-y-2">
                              <div className="flex items-center space-x-2">
                                <Tag className="w-4 h-4 text-gray-400" />
                                <span className="text-xs text-gray-400">{activity.subject}</span>
                              </div>
                              
                              <div className="flex flex-wrap gap-1">
                                {activity.tags.slice(0, 2).map((tag: string, tagIndex: number) => (
                                  <span 
                                    key={tagIndex}
                                    className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]"
                                    title={tag}
                                  >
                                    {tag.length > 15 ? `${tag.substring(0, 15)}...` : tag}
                                  </span>
                                ))}
                              </div>
                            </div>

                            {/* View Details Link */}
                            <div className="mt-2 pt-2 border-t border-gray-700">
                              <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2">
                                View Details <ChevronRight className="w-4 h-4 inline ml-1" />
                              </span>
                            </div>
                          </div>
                        </motion.div>
                        ))}
                      </div>

                      {/* Show More/Less Button */}
                      {hasMoreActivities && (
                        <div className="flex justify-center mt-8">
                          <Button
                            variant="outline"
                            onClick={() => toggleLibraryExpansion(library.id)}
                            className="border-2 border-purple-200 hover:border-purple-300 text-purple-600 hover:text-purple-700 dark:border-purple-700 dark:hover:border-purple-600 dark:text-purple-400 dark:hover:text-purple-300"
                          >
                            {isExpanded ? "Show Less" : `Show More (${library.activities.length - 6} more)`}
                          </Button>
                        </div>
                      )}
                    </>
                  ) : (
                    <div className="text-center py-12">
                      <p className="text-muted-foreground">No activities found for this theme.</p>
                    </div>
                  )}
                </motion.div>
              )
            })}
          </div>

          {/* Inclusion Guide Button */}
          <div className="text-center">
            <Button
              asChild
              className="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-8 py-3 rounded-full"
            >
              <a href="/inclusion-guide" className="flex items-center space-x-2">
                <Heart className="w-5 h-5" />
                <span>Inclusion Guide for Teachers</span>
              </a>
            </Button>
          </div>
        </div>

        {/* Activity Detail Modal */}
        <ActivityDetailModal 
          activity={selectedActivity} 
          onClose={() => setSelectedActivity(null)} 
        />
      </Container>
    </SectionWrapper>
  )
}
