import React from 'react'
import { SplitImageCard } from './split-image-card'

// Example usage of SplitImageCard component
export const SplitImageCardExamples: React.FC = () => {
  return (
    <div className="space-y-8 p-8">
      <h2 className="text-2xl font-bold mb-6">SplitImageCard Usage Examples</h2>
      
      {/* Basic Usage */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold">Basic Usage</h3>
        <SplitImageCard />
      </div>

      {/* Different Themes */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold">Different Themes</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <SplitImageCard
            title="Safe AI Practices"
            description="Learn about responsible AI usage and safety measures"
            tags={['safety', 'ethics']}
            theme="safe"
            difficulty="beginner"
            imageUrl="https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=400&h=200&fit=crop"
            className="h-80"
          />
          <SplitImageCard
            title="Creative AI Tools"
            description="Explore AI-powered creative tools and applications"
            tags={['creativity', 'tools']}
            theme="creative"
            difficulty="intermediate"
            imageUrl="https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=400&h=200&fit=crop"
            className="h-80"
          />
          <SplitImageCard
            title="Future of AI"
            description="Discover emerging AI technologies and future possibilities"
            tags={['future', 'innovation']}
            theme="future"
            difficulty="advanced"
            imageUrl="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=200&fit=crop"
            className="h-80"
          />
        </div>
      </div>

      {/* Without Corner Cut */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold">Without Corner Cut</h3>
        <SplitImageCard
          title="Responsible AI Development"
          description="Understanding ethical considerations in AI development"
          tags={['ethics', 'development']}
          theme="responsible"
          difficulty="intermediate"
          showCornerCut={false}
          className="h-80"
        />
      </div>

      {/* With Click Handler */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold">With Click Handler</h3>
        <SplitImageCard
          title="Interactive AI Learning"
          description="Click to explore interactive AI learning modules"
          tags={['interactive', 'learning']}
          theme="smart"
          difficulty="beginner"
          onClick={() => alert('Card clicked!')}
          className="h-80"
        />
      </div>

      {/* Custom Styling */}
      <div className="space-y-4">
        <h3 className="text-lg font-semibold">Custom Styling</h3>
        <SplitImageCard
          title="Custom Styled Card"
          description="This card has custom styling applied"
          tags={['custom', 'styling']}
          theme="purple"
          difficulty="intermediate"
          className="h-80 border-2 border-purple-500 hover:border-purple-300"
        />
      </div>
    </div>
  )
}

export default SplitImageCardExamples
