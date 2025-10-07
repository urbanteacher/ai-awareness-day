"use client"

import { Container, SectionWrapper, SectionHeader, SplitImageActivityCard } from "@/components/ui"
import { Button } from "@/components/ui/button"
import { ArrowLeft } from "lucide-react"
import Link from "next/link"

// Sample activities data with image URLs
const sampleActivities = [
  {
    id: "ai-daily-life",
    title: "AI in Daily Life",
    description: "Discuss how AI is already part of students' daily routines and explore the impact on their personal lives and decision-making processes.",
    theme: "smart",
    level: "Intermediate",
    subject: "General",
    tags: ["discussion", "everyday", "technology"],
    imageUrl: "https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "ai-art-creation",
    title: "AI Art Generation",
    description: "Create digital art using AI tools and explore the creative possibilities while understanding the ethical implications of AI-generated content.",
    theme: "creative",
    level: "Beginner",
    subject: "Art",
    tags: ["art", "creative", "tools"],
    imageUrl: "https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "ai-privacy-workshop",
    title: "AI Privacy Workshop",
    description: "Learn about data privacy and safe AI usage practices, including understanding how personal data is collected and used by AI systems.",
    theme: "safe",
    level: "Advanced",
    subject: "Technology",
    tags: ["privacy", "security", "ethics"],
    imageUrl: "https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "ai-future-careers",
    title: "Future AI Careers",
    description: "Explore potential career paths in AI and technology, understanding the skills needed for future job markets and AI-related professions.",
    theme: "future",
    level: "Intermediate",
    subject: "Career",
    tags: ["careers", "future", "skills"],
    imageUrl: "https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "ai-ethics-debate",
    title: "AI Ethics Debate",
    description: "Engage in structured debates about AI ethics, bias, and responsibility, developing critical thinking skills around AI implementation.",
    theme: "responsible",
    level: "Advanced",
    subject: "Ethics",
    tags: ["ethics", "debate", "critical-thinking"],
    imageUrl: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop&crop=center"
  },
  {
    id: "ai-coding-basics",
    title: "AI Coding Basics",
    description: "Introduction to programming concepts for AI, including basic machine learning algorithms and hands-on coding exercises.",
    theme: "smart",
    level: "Beginner",
    subject: "Programming",
    tags: ["coding", "programming", "algorithms"],
    imageUrl: "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=600&fit=crop&crop=center"
  }
]

export default function ActivitiesSplitDemoPage() {
  const handleActivityClick = (activity: any) => {
    console.log("Activity clicked:", activity.title)
    // You can implement modal opening or navigation here
  }

  return (
    <div className="min-h-screen bg-background">
      <main>
        {/* Header */}
        <SectionWrapper className="bg-gradient-to-br from-purple-50 to-blue-50 pt-20 pb-16">
          <Container>
            <div className="text-center space-y-6">
              <div className="flex items-center justify-center gap-2 mb-4">
                <Link href="/">
                  <Button variant="ghost" size="sm" className="gap-2">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Home
                  </Button>
                </Link>
              </div>
              
              <SectionHeader
                title="🎨 Split + Image Activity Cards"
                description="New card design integrated into the Activities section"
                titleColor="purple"
                align="center"
              />
              
              <p className="text-muted-foreground max-w-2xl mx-auto">
                Experience the new Split + Image card design with real activity data. 
                These cards combine visual appeal with clear information hierarchy.
              </p>
            </div>
          </Container>
        </SectionWrapper>

        {/* Activities Grid */}
        <SectionWrapper className="bg-background">
          <Container>
            <div className="space-y-8">
              <SectionHeader
                title="Activity Library"
                description="Activities using the new Split + Image card design"
                titleColor="purple"
                className="mb-8"
              />
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {sampleActivities.map((activity, index) => (
                  <SplitImageActivityCard
                    key={activity.id}
                    activity={activity}
                    onClick={() => handleActivityClick(activity)}
                    className="h-96"
                  />
                ))}
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Integration Guide */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-900">
          <Container>
            <div className="max-w-4xl mx-auto">
              <SectionHeader
                title="💻 Integration Guide"
                description="How to use the Split + Image card in your Activities section"
                titleColor="purple"
                align="center"
                className="mb-8"
              />
              
              <div className="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <h3 className="text-lg font-semibold mb-4">Implementation Steps:</h3>
                <ol className="space-y-3 text-muted-foreground">
                  <li className="flex items-start space-x-3">
                    <span className="flex-shrink-0 w-6 h-6 bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center text-sm font-medium">1</span>
                    <span>Import the <code className="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">SplitImageActivityCard</code> component from <code className="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">@/components/ui</code></span>
                  </li>
                  <li className="flex items-start space-x-3">
                    <span className="flex-shrink-0 w-6 h-6 bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center text-sm font-medium">2</span>
                    <span>Ensure your activity data includes an <code className="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">imageUrl</code> property for the background image</span>
                  </li>
                  <li className="flex items-start space-x-3">
                    <span className="flex-shrink-0 w-6 h-6 bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center text-sm font-medium">3</span>
                    <span>Replace existing activity cards with <code className="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">SplitImageActivityCard</code> in your grid</span>
                  </li>
                  <li className="flex items-start space-x-3">
                    <span className="flex-shrink-0 w-6 h-6 bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center text-sm font-medium">4</span>
                    <span>Customize the <code className="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">onClick</code> handler for your specific use case</span>
                  </li>
                </ol>
                
                <div className="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                  <h4 className="font-medium mb-2">Example Usage:</h4>
                  <pre className="text-sm text-muted-foreground overflow-x-auto">
{`<SplitImageActivityCard
  activity={activity}
  onClick={() => handleActivityClick(activity)}
  className="h-96"
/>`}
                  </pre>
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
