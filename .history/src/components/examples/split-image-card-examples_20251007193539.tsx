import React from 'react'
import { SplitImageCard } from '@/components/ui'

// Example usage of SplitImageCard component
export function SplitImageCardExamples() {
  return (
    <div className="space-y-8 p-8">
      <h2 className="text-3xl font-bold text-center mb-8">Split Image Card Examples</h2>
      
      {/* Example 1: Default Smart Theme */}
      <div>
        <h3 className="text-xl font-semibold mb-4">Default Smart Theme</h3>
        <SplitImageCard
          title="AI in Daily Life"
          description="Discuss how AI is already part of students' daily routines"
          tags={['discussion', 'everyday']}
        />
      </div>

      {/* Example 2: Safe Theme */}
      <div>
        <h3 className="text-xl font-semibold mb-4">Safe Theme</h3>
        <SplitImageCard
          title="AI Privacy Workshop"
          description="Learn about data privacy and safe AI usage practices"
          tags={['privacy', 'security', 'workshop']}
          theme="safe"
          difficulty="advanced"
        />
      </div>

      {/* Example 3: Creative Theme */}
      <div>
        <h3 className="text-xl font-semibold mb-4">Creative Theme</h3>
        <SplitImageCard
          title="AI Art Generation"
          description="Create digital art using AI tools and explore creative possibilities"
          tags={['art', 'creative', 'generation']}
          theme="creative"
          difficulty="intermediate"
        />
      </div>

      {/* Example 4: Future Theme */}
      <div>
        <h3 className="text-xl font-semibold mb-4">Future Theme</h3>
        <SplitImageCard
          title="Future of AI in Education"
          description="Explore how AI will transform learning and teaching methods"
          tags={['future', 'education', 'transformation']}
          theme="future"
          difficulty="beginner"
        />
      </div>

      {/* Example 5: Without Corner Cut */}
      <div>
        <h3 className="text-xl font-semibold mb-4">Without Corner Cut</h3>
        <SplitImageCard
          title="Responsible AI Development"
          description="Understand ethical considerations in AI development and deployment"
          tags={['ethics', 'responsibility', 'development']}
          theme="responsible"
          difficulty="intermediate"
          showCornerCut={false}
        />
      </div>

      {/* Example 6: Custom Image */}
      <div>
        <h3 className="text-xl font-semibold mb-4">Custom Image</h3>
        <SplitImageCard
          title="Machine Learning Basics"
          description="Introduction to machine learning concepts and applications"
          tags={['machine learning', 'basics', 'introduction']}
          theme="smart"
          difficulty="beginner"
          imageUrl="https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=800&h=600&fit=crop&crop=center"
        />
      </div>
    </div>
  )
}

// Usage in a grid layout
export function SplitImageCardGrid() {
  const activities = [
    {
      title: "AI in Daily Life",
      description: "Discuss how AI is already part of students' daily routines",
      tags: ['discussion', 'everyday'],
      theme: 'smart' as const,
      difficulty: 'beginner' as const
    },
    {
      title: "AI Privacy Workshop",
      description: "Learn about data privacy and safe AI usage practices",
      tags: ['privacy', 'security'],
      theme: 'safe' as const,
      difficulty: 'advanced' as const
    },
    {
      title: "AI Art Generation",
      description: "Create digital art using AI tools and explore creative possibilities",
      tags: ['art', 'creative'],
      theme: 'creative' as const,
      difficulty: 'intermediate' as const
    },
    {
      title: "Future of AI in Education",
      description: "Explore how AI will transform learning and teaching methods",
      tags: ['future', 'education'],
      theme: 'future' as const,
      difficulty: 'beginner' as const
    }
  ]

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-8">
      {activities.map((activity, index) => (
        <SplitImageCard
          key={index}
          title={activity.title}
          description={activity.description}
          tags={activity.tags}
          theme={activity.theme}
          difficulty={activity.difficulty}
        />
      ))}
    </div>
  )
}
