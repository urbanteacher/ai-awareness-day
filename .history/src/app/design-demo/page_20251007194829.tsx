"use client"

import { useState } from 'react'
import { Navigation } from "@/components/navigation"
import { SectionWrapper, Container, SectionHeader, SplitImageCard } from "@/components/ui"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Copy, Check } from "lucide-react"
import Link from "next/link"

// Component usage example

export default function DesignDemoPage() {
  const [copiedCode, setCopiedCode] = useState<string | null>(null)

  const copyCode = async () => {
    const code = generateCode()
    try {
      await navigator.clipboard.writeText(code)
      setCopiedCode('Code copied!')
      setTimeout(() => setCopiedCode(null), 2000)
    } catch (err) {
      console.error('Failed to copy code:', err)
    }
  }

  const generateCode = () => {
    return `// Import the SplitImageCard component
import { SplitImageCard } from '@/components/ui'

// Basic usage
<SplitImageCard
  title="AI in Daily Life"
  description="Discuss how AI is already part of students' daily routines"
  tags={['discussion', 'everyday']}
  theme="smart"
  difficulty="beginner"
  size="medium"
  showCornerCut={true}
/>

// With custom image
<SplitImageCard
  title="Machine Learning Basics"
  description="Introduction to machine learning concepts and applications"
  tags={['machine learning', 'basics']}
  theme="smart"
  difficulty="beginner"
  imageUrl="https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=800&h=600&fit=crop&crop=center"
/>

// Different themes
<SplitImageCard
  title="AI Privacy Workshop"
  description="Learn about data privacy and safe AI usage practices"
  tags={['privacy', 'security']}
  theme="safe"
  difficulty="advanced"
/>

<SplitImageCard
  title="AI Art Generation"
  description="Create digital art using AI tools and explore creative possibilities"
  tags={['art', 'creative']}
  theme="creative"
  difficulty="intermediate"
/>

// Available props:
// - title: string (required)
// - description: string (required)
// - tags: string[] (required)
// - theme: 'safe' | 'smart' | 'creative' | 'responsible' | 'future' (optional, default: 'smart')
// - difficulty: 'beginner' | 'intermediate' | 'advanced' (optional, default: 'beginner')
// - imageUrl: string (optional, default: AI-themed image)
// - showCornerCut: boolean (optional, default: true)
// - size: 'small' | 'medium' | 'large' (optional, default: 'medium')
// - className: string (optional, for additional styling)`
  }

  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
      <main>
        {/* Header */}
        <SectionWrapper className="bg-gradient-to-br from-purple-50 to-blue-50 pt-20 pb-16">
          <Container>
            <div className="text-center space-y-6">
              <div className="flex items-center justify-center gap-2 mb-4">
                <Link href="/design-concept">
                  <Button variant="ghost" size="sm" className="gap-2">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Design Concept
                  </Button>
                </Link>
              </div>
              
              <h1 className="text-4xl md:text-6xl font-bold text-black dark:text-white">
                Activity Card Design Demo
              </h1>
              <p className="text-lg text-black dark:text-white">Interactive playground for testing polygon-shaped activity card designs</p>
            </div>
          </Container>
        </SectionWrapper>

        {/* Live Preview */}
        <SectionWrapper className="bg-card">
          <Container>
            <SectionHeader
              title="🎨 Live Preview - Split + Image Card"
              description="Interactive polygon-shaped activity card with split design and image content"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="relative p-20 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5" />
              <div className="relative">
                <div className="grid grid-cols-1 gap-8 items-start">
                  {/* Split + Image Card */}
                  <SplitImageCard
                    title="AI in Daily Life"
                    description="Discuss how AI is already part of students' daily routines"
                    tags={['discussion', 'everyday']}
                    theme="smart"
                    difficulty="beginner"
                    size="medium"
                    showCornerCut={true}
                  />
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Code Output */}
        <SectionWrapper className="bg-card">
          <Container>
            <SectionHeader
              title="💻 Generated Code"
              description="Copy the generated code for your Split + Image card design"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="space-y-4">
              <div className="flex justify-end">
                <Button 
                  onClick={copyCode}
                  variant="outline" 
                  className="gap-2"
                >
                  {copiedCode ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                  {copiedCode || 'Copy Code'}
                </Button>
              </div>
              
              <div className="bg-gray-900 rounded-lg p-6 overflow-x-auto">
                <pre className="text-green-400 text-sm font-mono">
                  <code>{generateCode()}</code>
                </pre>
              </div>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}