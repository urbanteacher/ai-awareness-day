"use client"

import { Container, SectionWrapper, SplitImageCard } from "@/components/ui"
import { motion } from "framer-motion"
import { Clock, Users, Presentation, BookOpen } from "lucide-react"

const activityTypes = [
  {
    id: "lesson-starters",
    title: "Starters",
    description: "Minimal setup, maximum engagement with quick AI discussions and ready-made prompts",
    tags: ["Quick AI discussions", "Ready-made prompts", "No preparation needed", "Immediate engagement"],
    theme: "smart" as const,
    difficulty: "beginner" as const,
    duration: "5 min",
    icon: Clock,
    color: "from-blue-500 to-blue-600",
    imageUrl: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "tutor-time",
    title: "Tutor Time",
    description: "Morning registration activities with group discussions and interactive learning",
    tags: ["Group discussions", "Interactive activities", "Peer learning", "Daily AI awareness"],
    theme: "creative" as const,
    difficulty: "intermediate" as const,
    duration: "15-20 min",
    icon: Users,
    color: "from-green-500 to-green-600",
    imageUrl: "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "assemblies",
    title: "Assemblies",
    description: "Whole-school presentations with keynote talks and student showcases",
    tags: ["School-wide impact", "Keynote presentations", "Student showcases", "Community building"],
    theme: "future" as const,
    difficulty: "intermediate" as const,
    duration: "20 min",
    icon: Presentation,
    color: "from-purple-500 to-purple-600",
    imageUrl: "https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "after-school-clubs",
    title: "After School Subjects",
    description: "Extended learning with deep dive sessions and hands-on creative projects",
    tags: ["Deep dive sessions", "Hands-on projects", "Creative challenges", "Advanced exploration"],
    theme: "responsible" as const,
    difficulty: "advanced" as const,
    duration: "30-45 min",
    icon: BookOpen,
    color: "from-orange-500 to-orange-600",
    imageUrl: "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&h=600&fit=crop&crop=center"
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
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
              {activityTypes.map((activity, index) => (
                <motion.div
                  key={activity.id}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true, amount: 0.3 }}
                  transition={{ duration: 0.6, delay: index * 0.1 }}
                  className="relative"
                >
                  <SplitImageCard
                    title={activity.title}
                    description={activity.description}
                    tags={activity.tags}
                    theme={activity.theme}
                    difficulty={activity.difficulty}
                    imageUrl={activity.imageUrl}
                    showCornerCut={true}
                    className="h-80"
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