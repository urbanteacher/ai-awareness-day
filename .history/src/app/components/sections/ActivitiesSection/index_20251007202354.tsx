"use client"

import { Container, SectionWrapper } from "@/components/ui"
import { motion } from "framer-motion"
import { Clock, Users, Presentation, BookOpen } from "lucide-react"
import { ActivityTypeCard } from "./ActivityTypeCard"

const activityTypes = [
  {
    id: "lesson-starters",
    title: "Starters",
    duration: "5 min",
    participants: "Individual",
    description: "Minimal setup, maximum engagement",
    icon: Clock,
    themeColor: "from-blue-500 to-blue-600",
    themeName: "BE SMART",
    difficulty: "Beginner" as const,
    category: "Quick Activities",
    imageUrl: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center",
    features: [
      "Quick AI discussions",
      "Ready-made prompts",
      "No preparation needed",
      "Immediate student engagement"
    ]
  },
  {
    id: "tutor-time",
    title: "Tutor Time",
    duration: "15-20 min",
    participants: "Small Groups",
    description: "Morning registration activities",
    icon: Users,
    themeColor: "from-green-500 to-green-600",
    themeName: "BE CREATIVE",
    difficulty: "Intermediate" as const,
    category: "Group Activities",
    imageUrl: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center",
    features: [
      "Group discussions",
      "Interactive activities",
      "Peer learning",
      "Daily AI awareness"
    ]
  },
  {
    id: "assemblies",
    title: "Assemblies",
    duration: "20 min",
    participants: "Whole School",
    description: "Whole-school presentations",
    icon: Presentation,
    themeColor: "from-purple-500 to-purple-600",
    themeName: "BE RESPONSIBLE",
    difficulty: "Advanced" as const,
    category: "Presentations",
    imageUrl: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center",
    features: [
      "School-wide impact",
      "Keynote presentations",
      "Student showcases",
      "Community building"
    ]
  },
  {
    id: "after-school-clubs",
    title: "After School Subjects",
    duration: "30-45 min",
    participants: "Small Groups",
    description: "Extended learning",
    icon: BookOpen,
    themeColor: "from-orange-500 to-orange-600",
    themeName: "BE FUTURE-READY",
    difficulty: "Advanced" as const,
    category: "Extended Learning",
    imageUrl: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center",
    features: [
      "Deep dive sessions",
      "Hands-on projects",
      "Creative challenges",
      "Advanced exploration"
    ]
  }
]

export default function ActivitiesSection() {
  return (
    <SectionWrapper className="bg-background">
      <Container>
        <div className="space-y-16">
          <div className="space-y-4 text-center">
            <p className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
              Accommodate different scheduling needs
            </p>
            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl text-purple-600 dark:text-purple-400">
              Choose Your Activity Type
            </h2>
            <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
              Flexible format selection to match your school's needs and resources
            </p>
          </div>
          
          <div className="max-w-7xl mx-auto">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {activityTypes.map((activity, index) => (
                <motion.div
                  key={activity.id}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, amount: 0.3 }}
                  transition={{ duration: 0.6, delay: index * 0.1 }}
                  className="relative"
                >
                  <ActivityTypeCard
                    title={activity.title}
                    description={activity.description}
                    duration={activity.duration}
                    participants={activity.participants}
                    difficulty={activity.difficulty}
                    category={activity.category}
                    icon={<activity.icon className="w-6 h-6" />}
                    themeColor={activity.themeColor}
                    themeName={activity.themeName}
                    imageUrl={activity.imageUrl}
                    features={activity.features}
                  />
                </motion.div>
              ))}
            </div>
          </div>
        </div>
      </Container>
    </SectionWrapper>
  )
}