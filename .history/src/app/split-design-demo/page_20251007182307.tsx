"use client"

import { useState } from "react"
import { SectionWrapper, Container, SectionHeader, Grid, SectionCard } from "@/components/ui"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Palette, Copy, Check } from "lucide-react"
import Link from "next/link"

const cardThemes = [
  {
    name: "BE SMART",
    theme: "BE SMART",
    gradient: "from-blue-500 to-blue-600",
    dotColor: "bg-blue-500",
    color: "blue"
  },
  {
    name: "BE SAFE",
    theme: "BE SAFE", 
    gradient: "from-red-500 to-red-600",
    dotColor: "bg-red-500",
    color: "red"
  },
  {
    name: "BE CREATIVE",
    theme: "BE CREATIVE",
    gradient: "from-green-500 to-green-600", 
    dotColor: "bg-green-500",
    color: "green"
  },
  {
    name: "BE FUTURE",
    theme: "BE FUTURE",
    gradient: "from-orange-500 to-orange-600",
    dotColor: "bg-orange-500", 
    color: "orange"
  }
]

const cardSizes = [
  {
    name: "Small",
    titleSize: "text-lg",
    descriptionSize: "text-sm"
  },
  {
    name: "Medium", 
    titleSize: "text-xl",
    descriptionSize: "text-base"
  },
  {
    name: "Large",
    titleSize: "text-2xl", 
    descriptionSize: "text-lg"
  }
]

const difficultyLevels = [
  {
    name: "Beginner",
    className: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400"
  },
  {
    name: "Intermediate", 
    className: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400"
  },
  {
    name: "Advanced",
    className: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
  }
]

interface DesignState {
  selectedTheme: number
  cardSize: number
  difficultyLevel: number
  customTitle: string
  customDescription: string
  customTags: string[]
  showCornerCut: boolean
}

export default function SplitDesignDemoPage() {
  const [designState, setDesignState] = useState<DesignState>({
    selectedTheme: 0,
    cardSize: 1,
    difficultyLevel: 1,
    customTitle: "AI in Daily Life",
    customDescription: "Discuss how AI is already part of students' daily routines",
    customTags: ["discussion", "everyday"],
    showCornerCut: true
  })

  const [copied, setCopied] = useState(false)

  const generateCode = () => {
    const currentTheme = cardThemes[designState.selectedTheme]
    const currentSize = cardSizes[designState.cardSize]
    const currentDifficulty = difficultyLevels[designState.difficultyLevel]

    return `// Split + Image Activity Card
<div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96"
     style={{
       clipPath: ${designState.showCornerCut ? '"polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"' : '"none"'}
     }}>
  
  {/* Left side - Theme section with image */}
  <div className="flex-1 bg-gradient-to-br ${currentTheme.gradient} p-6 relative overflow-hidden">
    {/* Background image overlay */}
    <div 
      className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
      style={{
        backgroundImage: "url('https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center')"
      }}
    />
    
    {/* Dark overlay for text readability */}
    <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-300" />
    
    {/* Decorative Polygon Corner */}
    ${designState.showCornerCut ? `{/* Decorative Polygon Corner */}
    <div 
      className="absolute top-0 right-0 w-8 h-8 bg-white/20"
      style={{
        clipPath: "polygon(100% 0, 0 0, 100% 100%)"
      }}
    />` : ''}
    
    {/* Theme header */}
    <div className="relative z-10 flex items-center space-x-2 mb-4">
      <div className="w-3 h-3 rounded-full bg-white" />
      <span className="text-sm font-medium text-white">${currentTheme.theme}</span>
    </div>
    
    {/* Image content in theme section */}
    <div className="relative z-10 mb-4">
      <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
        <img 
          src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=200&fit=crop&crop=center" 
          alt="AI Activity" 
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
      </div>
    </div>
    
    {/* Title in theme section */}
    <div className="relative z-10">
      <h4 className="${currentSize.titleSize} font-bold text-white leading-tight">
        <span className="line-clamp-2">${designState.customTitle}</span>
      </h4>
    </div>
  </div>
  
  {/* Right side - Content section */}
  <div className="bg-gray-800 p-6 flex-1 flex flex-col">
    {/* Difficulty badge */}
    <div className="flex justify-end mb-4">
      <span className="px-2 py-1 text-xs font-medium rounded-full ${currentDifficulty.className}">
        ${currentDifficulty.name}
      </span>
    </div>

    {/* Description */}
    <div className="flex-1">
      <p className="text-white ${currentSize.descriptionSize} leading-relaxed line-clamp-3 h-16 flex items-start">
        <span className="line-clamp-3">${designState.customDescription}</span>
      </p>
    </div>

    {/* Tags */}
    <div className="mt-4 space-y-2">
      <div className="flex items-center space-x-2">
        <span className="text-xs text-gray-400">Tags</span>
        <div className="flex flex-wrap gap-1">
          ${designState.customTags.map(tag => 
            `<span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
              ${tag}
            </span>`
          ).join('\n          ')}
        </div>
      </div>
    </div>

    {/* View Details Link */}
    <div className="mt-4 mb-8">
      <span className="text-purple-400 text-base font-medium group-hover:text-purple-300 transition-colors">
        View Details →
      </span>
    </div>
  </div>
</div>`
  }

  const copyToClipboard = async () => {
    try {
      await navigator.clipboard.writeText(generateCode())
      setCopied(true)
      setTimeout(() => setCopied(false), 2000)
    } catch (err) {
      console.error('Failed to copy: ', err)
    }
  }

  return (
    <div className="min-h-screen bg-background">
      <main>
        {/* Header */}
        <SectionWrapper className="bg-gradient-to-br from-purple-50 to-blue-50 pt-20 pb-16">
          <Container>
            <div className="text-center space-y-6">
              <div className="flex items-center justify-center gap-2 mb-4">
                <Link href="/design-demo">
                  <Button variant="ghost" size="sm" className="gap-2">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Design Demo
                  </Button>
                </Link>
              </div>
              
              <SectionHeader
                title="🎨 Split + Image Card Design"
                description="Interactive demo for the Split + Image activity card design system"
                titleColor="purple"
                className="mb-8"
              />
            </div>
          </Container>
        </SectionWrapper>

        {/* Controls Panel */}
        <SectionWrapper className="bg-card border-b">
          <Container>
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
              {/* Card Theme Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <h3 className="font-semibold text-lg">Theme</h3>
                  <div className="space-y-2">
                    {cardThemes.map((theme, index) => (
                      <label key={index} className="flex items-center space-x-2 cursor-pointer">
                        <input
                          type="radio"
                          name="theme"
                          checked={designState.selectedTheme === index}
                          onChange={() => setDesignState(prev => ({ ...prev, selectedTheme: index }))}
                          className="w-4 h-4"
                        />
                        <div className={`w-3 h-3 rounded-full ${theme.dotColor}`} />
                        <span className="text-sm">{theme.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
              </SectionCard>

              {/* Card Size Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <h3 className="font-semibold text-lg">Size</h3>
                  <div className="space-y-2">
                    {cardSizes.map((size, index) => (
                      <label key={index} className="flex items-center space-x-2 cursor-pointer">
                        <input
                          type="radio"
                          name="size"
                          checked={designState.cardSize === index}
                          onChange={() => setDesignState(prev => ({ ...prev, cardSize: index }))}
                          className="w-4 h-4"
                        />
                        <span className="text-sm">{size.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
              </SectionCard>

              {/* Difficulty Level Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <h3 className="font-semibold text-lg">Difficulty</h3>
                  <div className="space-y-2">
                    {difficultyLevels.map((level, index) => (
                      <label key={index} className="flex items-center space-x-2 cursor-pointer">
                        <input
                          type="radio"
                          name="difficulty"
                          checked={designState.difficultyLevel === index}
                          onChange={() => setDesignState(prev => ({ ...prev, difficultyLevel: index }))}
                          className="w-4 h-4"
                        />
                        <span className="text-sm">{level.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
              </SectionCard>

              {/* Content Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <h3 className="font-semibold text-lg">Content</h3>
                  <div className="space-y-3">
                    <div>
                      <label className="block text-sm font-medium mb-1">Title</label>
                      <input
                        type="text"
                        value={designState.customTitle}
                        onChange={(e) => setDesignState(prev => ({ ...prev, customTitle: e.target.value }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        placeholder="Enter title..."
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Description</label>
                      <textarea
                        value={designState.customDescription}
                        onChange={(e) => setDesignState(prev => ({ ...prev, customDescription: e.target.value }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm h-20 resize-none"
                        placeholder="Enter description..."
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Tags (comma-separated)</label>
                      <input
                        type="text"
                        value={designState.customTags.join(', ')}
                        onChange={(e) => setDesignState(prev => ({ 
                          ...prev, 
                          customTags: e.target.value.split(',').map(tag => tag.trim()).filter(tag => tag)
                        }))}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        placeholder="discussion, everyday, ai"
                      />
                    </div>
                    <div className="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        id="cornerCut"
                        checked={designState.showCornerCut}
                        onChange={(e) => setDesignState(prev => ({ ...prev, showCornerCut: e.target.checked }))}
                        className="w-4 h-4"
                      />
                      <label htmlFor="cornerCut" className="text-sm cursor-pointer">
                        Show corner cut
                      </label>
                    </div>
                  </div>
                </div>
              </SectionCard>
            </div>
          </Container>
        </SectionWrapper>

        {/* Live Preview */}
        <SectionWrapper className="bg-card">
          <Container>
            <SectionHeader
              title="🎨 Live Preview - Split + Image Card"
              description="See your card design changes in real-time"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="flex justify-center">
              <div className="w-full max-w-2xl">
                {/* Split + Image Card */}
                <div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96"
                     style={{
                       clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                     }}>
                  
                  {/* Left side - Theme section with image */}
                  <div 
                    className={`flex-1 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} p-6 relative overflow-hidden`}
                  >
                    {/* Background image overlay */}
                    <div 
                      className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
                      style={{
                        backgroundImage: "url('https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center')"
                      }}
                    />
                    
                    {/* Dark overlay for text readability */}
                    <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-300" />
                    
                    {/* Decorative Polygon Corner */}
                    {designState.showCornerCut && (
                      <div 
                        className="absolute top-0 right-0 w-8 h-8 bg-white/20"
                        style={{
                          clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                        }}
                      />
                    )}
                    
                    {/* Theme header */}
                    <div className="relative z-10 flex items-center space-x-2 mb-4">
                      <div className="w-3 h-3 rounded-full bg-white" />
                      <span className="text-sm font-medium text-white">{cardThemes[designState.selectedTheme].theme}</span>
                    </div>
                    
                    {/* Image content in theme section */}
                    <div className="relative z-10 mb-4">
                      <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
                        <img 
                          src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=200&fit=crop&crop=center" 
                          alt="AI Activity" 
                          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                      </div>
                    </div>
                    
                    {/* Title in theme section with difficulty badge */}
                    <div className="relative z-10 flex items-start justify-between">
                      <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white leading-tight flex-1 mr-4`}>
                        <span className="line-clamp-2">{designState.customTitle}</span>
                      </h4>
                      <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className} flex-shrink-0`}>
                        {difficultyLevels[designState.difficultyLevel].name}
                      </span>
                    </div>
                  </div>
                  
                  {/* Right side - Content section */}
                  <div className="bg-gray-800 p-6 flex-1 flex flex-col">

                    {/* Description */}
                    <div className="flex-1">
                      <p className={`text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-3 h-16 flex items-start`}>
                        <span className="line-clamp-3">{designState.customDescription}</span>
                      </p>
                    </div>

                    {/* Tags */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400">Tags</span>
                        <div className="flex flex-wrap gap-1">
                          {designState.customTags.map((tag, index) => (
                            <span key={index} className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                              {tag}
                            </span>
                          ))}
                        </div>
                      </div>
                    </div>

                    {/* View Details Link */}
                    <div className="mt-4 mb-8">
                      <span className="text-purple-400 text-base font-medium group-hover:text-purple-300 transition-colors">
                        View Details →
                      </span>
                    </div>
                  </div>
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
              description="Copy the generated code for your current design"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="space-y-4">
              <div className="flex justify-end">
                <Button onClick={copyToClipboard} className="gap-2">
                  {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                  {copied ? "Copied!" : "Copy Code"}
                </Button>
              </div>
              
              <div className="bg-gray-900 rounded-lg p-6 overflow-x-auto">
                <pre className="text-sm text-gray-300 whitespace-pre-wrap">
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
