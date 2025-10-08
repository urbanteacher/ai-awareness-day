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
  const [selectedDifficulty, setSelectedDifficulty] = useState("all")
  const [selectedActivity, setSelectedActivity] = useState(null)
  const [expandedLibraries, setExpandedLibraries] = useState<Set<string>>(new Set())
  const [showCardDetails, setShowCardDetails] = useState(false)
  
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
      activities: library.activities.filter(activity => 
        (selectedTheme === "all" || activity.theme === selectedTheme) &&
        (selectedDifficulty === "all" || activity.level === selectedDifficulty)
      )
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
    return isExpanded ? library.activities : library.activities.slice(0, 4) // Show 4 activities initially (extra row for mobile)
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
          <div className="flex flex-wrap sm:flex-nowrap overflow-x-auto sm:overflow-x-visible justify-center gap-2 sm:gap-3 mb-6 px-4 sm:px-0 pb-2 scrollbar-hide">
            {themes.map((theme) => (
              <Button
                key={theme.id}
                variant={selectedTheme === theme.id ? "default" : "outline"}
                onClick={() => setSelectedTheme(theme.id)}
                size="sm"
                className={`text-xs sm:text-sm px-2 sm:px-3 py-1.5 sm:py-2 whitespace-nowrap flex-shrink-0 min-w-fit ${
                  selectedTheme === theme.id
                    ? "bg-purple-600 hover:bg-white hover:text-purple-600 text-white"
                    : "border-gray-300 text-foreground hover:bg-gray-50 hover:text-foreground dark:border-gray-600 dark:hover:bg-gray-800 dark:hover:text-foreground"
                }`}
              >
                <div className={`w-2 h-2 sm:w-3 sm:h-3 rounded-full ${theme.color} mr-1 sm:mr-2`} />
                <span className="text-xs sm:text-sm">{theme.name}</span>
              </Button>
            ))}
          </div>

          {/* Difficulty Filter */}
          <div className="flex flex-wrap sm:flex-nowrap overflow-x-auto sm:overflow-x-visible justify-center gap-2 mb-6 px-4 sm:px-0 pb-2 scrollbar-hide">
            {["all", "Beginner", "Intermediate", "Advanced"].map((difficulty) => (
              <Button
                key={difficulty}
                variant={selectedDifficulty === difficulty ? "default" : "outline"}
                onClick={() => setSelectedDifficulty(difficulty)}
                size="sm"
                className={`text-xs sm:text-sm px-2 sm:px-3 py-1.5 sm:py-2 whitespace-nowrap flex-shrink-0 min-w-fit ${
                  selectedDifficulty === difficulty
                    ? "bg-blue-600 hover:bg-white hover:text-blue-600 text-white"
                    : "border-gray-300 text-foreground hover:bg-gray-50 hover:text-foreground dark:border-gray-600 dark:hover:bg-gray-800 dark:hover:text-foreground"
                }`}
              >
                {difficulty === "all" ? "All" : difficulty === "Beginner" ? "Beginner" : difficulty === "Intermediate" ? "Inter." : "Advanced"}
              </Button>
            ))}
          </div>

          {/* Card Details Toggle - Hidden on mobile */}
          <div className="hidden sm:flex justify-center mb-6 px-4 sm:px-0">
            <Button
              variant="outline"
              onClick={() => setShowCardDetails(!showCardDetails)}
              size="sm"
              className="text-xs sm:text-sm px-3 sm:px-4 py-1.5 sm:py-2 border-2 border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-600 dark:hover:bg-gray-800 dark:hover:text-white"
            >
              {showCardDetails ? "Show Less Details" : "Show Full Details"}
            </Button>
          </div>

          {/* Activity Libraries */}
          <div className="space-y-12">
            {filteredLibraries.map((library) => {
              const visibleActivities = getVisibleActivities(library)
              const isExpanded = expandedLibraries.has(library.id)
              const hasMoreActivities = library.activities.length > 4

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
                  <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 sm:mb-8 space-y-3 sm:space-y-0">
                    <div className="flex items-center space-x-3 sm:space-x-4">
                      <div
                        className={`w-8 h-8 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br ${library.color} items-center justify-center flex`}
                      >
                        <library.icon className="w-4 h-4 sm:w-6 sm:h-6 text-white" />
                      </div>
                      <div className="flex-1 min-w-0">
                        {/* Mobile: Title and duration on same line */}
                        <div className="flex items-center space-x-2 sm:block">
                          <h3 className="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-bold text-foreground leading-tight">
                            <span className="line-clamp-1 sm:line-clamp-2">{library.title}</span>
                          </h3>
                          <Badge className="text-xs px-2 py-1 text-white border-transparent hover:opacity-90 bg-purple-600 dark:bg-purple-500 sm:hidden">
                            {library.duration}
                          </Badge>
                        </div>
                        <p className="text-sm sm:text-base text-muted-foreground line-clamp-2 sm:line-clamp-none">{library.description}</p>
                      </div>
                    </div>
                    <div className="hidden sm:flex justify-end">
                      <Badge className="text-xs sm:text-sm lg:text-base px-2 sm:px-3 lg:px-4 py-1 sm:py-1.5 lg:py-2 text-white border-transparent hover:opacity-90 bg-purple-600 dark:bg-purple-500">
                        {library.duration}
                      </Badge>
                    </div>
                  </div>

                  {/* Activities Grid */}
                  {visibleActivities.length > 0 ? (
                    <>
                      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        {visibleActivities.map((activity: any, activityIndex: number) => (
                        <motion.div
                          key={activity.id}
                          initial={{ opacity: 0, y: 20 }}
                          whileInView={{ opacity: 1, y: 0 }}
                          viewport={{ once: true, amount: 0.3 }}
                          transition={{ duration: 0.4, delay: activityIndex * 0.1 }}
                          className="bg-gray-800 p-0 border border-gray-600 group cursor-pointer relative overflow-hidden flex flex-col"
                          style={{
                            clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                          }}
                          onClick={() => setSelectedActivity(activity)}
                        >
                          {/* Left side - Theme section with image */}
                          <div className={`bg-gradient-to-br ${
                            activity.theme === 'safe' ? 'from-red-500 to-red-600' :
                            activity.theme === 'smart' ? 'from-blue-500 to-blue-600' :
                            activity.theme === 'creative' ? 'from-green-500 to-green-600' :
                            activity.theme === 'responsible' ? 'from-purple-500 to-purple-600' :
                            activity.theme === 'future' ? 'from-orange-500 to-orange-600' : 'from-gray-500 to-gray-600'
                          } p-4 sm:p-6 relative overflow-hidden`}>
                            {/* Background image overlay */}
                            <div 
                              className="absolute inset-0 bg-cover bg-center opacity-20"
                              style={{
                                backgroundImage: `url('https://picsum.photos/800/600?random=${activity.id}')`
                              }}
                            />
                            
                            {/* Dark overlay for text readability */}
                            <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-300" />
                            
                            {/* Decorative Polygon Corner */}
                            <div 
                              className="absolute top-0 right-0 w-8 h-8 bg-white/20"
                              style={{
                                clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                              }}
                            />
                            
                            {/* Theme header */}
                            <div className="relative z-10 flex items-center space-x-2 mb-4">
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
                            
                            {/* Image content in theme section - Hidden on mobile */}
                            <div className="hidden sm:block relative z-10 mb-4">
                              <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
                                <img 
                                  src={`https://picsum.photos/400/200?random=${activity.id + 1000}`} 
                                  alt="AI Activity" 
                                  className="w-full h-full object-cover"
                                />
                              </div>
                            </div>
                            
                            {/* Title in theme section with difficulty */}
                            <div className="relative z-10 flex items-start justify-between">
                              <h4 className="text-base sm:text-lg font-bold text-white leading-tight flex-1 mr-2 sm:mr-4">
                                <span className="line-clamp-1 sm:line-clamp-2">{activity.title}</span>
                              </h4>
                              <div className="flex items-center space-x-1 text-yellow-400 flex-shrink-0">
                                <Star className="w-3 h-3 sm:w-4 sm:h-4 fill-current" />
                                <span className="text-xs sm:text-sm font-medium text-white">{activity.level}</span>
                              </div>
                            </div>
                          </div>
                          
                          {/* Right side - Content section */}
                          <div className="bg-gray-800 p-4 sm:p-6 flex flex-col">
                            {/* Description */}
                            <div className="flex-1">
                              <p className="text-white text-sm sm:text-base leading-relaxed line-clamp-2 sm:line-clamp-3 h-12 sm:h-16 flex items-start">
                                <span className="line-clamp-2 sm:line-clamp-3">{activity.description}</span>
                              </p>
                            </div>

                            {/* Extra spacing for mobile to make cards longer */}
                            <div className="h-8 sm:hidden"></div>

                            {/* Conditional Details Section */}
                            {showCardDetails && (
                              <>
                                {/* Tags */}
                                <div className="mt-3 sm:mt-4 space-y-2">
                                  <div className="flex items-center space-x-2">
                                    <span className="text-xs text-gray-400">Tags</span>
                                    <div className="flex flex-wrap gap-1">
                                      {activity.tags.slice(0, 2).map((tag: string, tagIndex: number) => (
                                        <span 
                                          key={tagIndex}
                                          className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[100px] sm:max-w-[120px]"
                                          title={tag}
                                        >
                                          {tag.length > 12 ? `${tag.substring(0, 12)}...` : tag}
                                        </span>
                                      ))}
                                    </div>
                                  </div>
                                </div>

                                {/* View Details Link */}
                                <div className="mt-3 sm:mt-4 mb-6 sm:mb-8">
                                  <span className="text-purple-400 text-sm sm:text-base font-medium">
                                    View Details →
                                  </span>
                                </div>
                              </>
                            )}
                          </div>
                        </motion.div>
                        ))}
                      </div>

                      {/* Show More/Less Button */}
                      {hasMoreActivities && (
                        <div className="flex justify-center mt-6 sm:mt-8">
                          <Button
                            variant="outline"
                            onClick={() => toggleLibraryExpansion(library.id)}
                            size="sm"
                            className="text-xs sm:text-sm px-3 sm:px-4 py-1.5 sm:py-2 border-2 border-purple-200 hover:border-purple-300 text-purple-600 hover:text-purple-700 dark:border-purple-700 dark:hover:border-purple-600 dark:text-purple-400 dark:hover:text-purple-300"
                          >
                            <span className="hidden sm:inline">
                              {isExpanded ? "Show Less" : `Show More (${library.activities.length - 4} more)`}
                            </span>
                            <span className="sm:hidden">
                              {isExpanded ? "Less" : `More (${library.activities.length - 4})`}
                            </span>
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
          <div className="text-center mt-8 sm:mt-12 mb-6 sm:mb-8">
            <div className="bg-gradient-to-r from-purple-50 to-blue-50 p-4 sm:p-6 lg:p-8 rounded-2xl border-2 border-purple-200 shadow-lg">
              <h3 className="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-3 sm:mb-4">
                🎯 Essential Teacher Resource
              </h3>
              <p className="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6 max-w-2xl mx-auto">
                Ensure every student can participate meaningfully in AI education with our comprehensive inclusion strategies and universal design principles.
              </p>
              <Button
                asChild
                size="sm"
                className="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 sm:px-8 lg:px-12 py-2 sm:py-3 lg:py-4 rounded-full text-sm sm:text-base lg:text-lg font-semibold shadow-lg"
              >
                <a href="/inclusion-guide" className="flex items-center space-x-2 sm:space-x-3">
                  <Heart className="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" />
                  <span className="hidden sm:inline">Inclusion Guide for Teachers</span>
                  <span className="sm:hidden">Inclusion Guide</span>
                </a>
              </Button>
            </div>
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
