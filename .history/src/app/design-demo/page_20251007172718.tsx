"use client"

import { useState } from 'react'
import { Suspense } from 'react'
import { Navigation } from "@/components/navigation"
import { SectionWrapper, Container, SectionHeader, Grid, SectionCard } from "@/components/ui"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Palette, Type, Shapes, Layout, Eye, Download, Code, Ruler, Settings, RotateCcw, Copy, Check } from "lucide-react"
import Link from "next/link"

// Design System Data
const colorPalette = [
  {
    name: "Red Gradient (Safe)",
    hex: "#ef4444 → #dc2626",
    usage: "Safety theme, warnings, alerts",
    gradient: "linear-gradient(135deg, #ef4444, #dc2626)",
    tw: "bg-gradient-to-br from-red-500 to-red-600"
  },
  {
    name: "Blue Gradient (Smart)",
    hex: "#3b82f6 → #2563eb",
    usage: "Intelligence theme, technology",
    gradient: "linear-gradient(135deg, #3b82f6, #2563eb)",
    tw: "bg-gradient-to-br from-blue-500 to-blue-600"
  },
  {
    name: "Green Gradient (Creative)",
    hex: "#10b981 → #059669",
    usage: "Innovation theme, growth, creativity",
    gradient: "linear-gradient(135deg, #10b981, #059669)",
    tw: "bg-gradient-to-br from-green-500 to-green-600"
  },
  {
    name: "Purple Gradient (Responsible)",
    hex: "#8b5cf6 → #7c3aed",
    usage: "Ethics theme, responsibility",
    gradient: "linear-gradient(135deg, #8b5cf6, #7c3aed)",
    tw: "bg-gradient-to-br from-purple-500 to-purple-600"
  },
  {
    name: "Orange Gradient (Future)",
    hex: "#f97316 → #ea580c",
    usage: "Future theme, progress, energy",
    gradient: "linear-gradient(135deg, #f97316, #ea580c)",
    tw: "bg-gradient-to-br from-orange-500 to-orange-600"
  }
]

const cardThemes = [
  {
    name: "Safe (Red)",
    color: "red",
    gradient: "from-red-500 to-red-600",
    theme: "BE SAFE",
    dotColor: "bg-red-500"
  },
  {
    name: "Smart (Blue)",
    color: "blue", 
    gradient: "from-blue-500 to-blue-600",
    theme: "BE SMART",
    dotColor: "bg-blue-500"
  },
  {
    name: "Creative (Green)",
    color: "green",
    gradient: "from-green-500 to-green-600", 
    theme: "BE CREATIVE",
    dotColor: "bg-green-500"
  },
  {
    name: "Responsible (Purple)",
    color: "purple",
    gradient: "from-purple-500 to-purple-600",
    theme: "BE RESPONSIBLE", 
    dotColor: "bg-purple-500"
  },
  {
    name: "Future (Orange)",
    color: "orange",
    gradient: "from-orange-500 to-orange-600",
    theme: "BE FUTURE",
    dotColor: "bg-orange-500"
  }
]

const cardSizes = [
  {
    name: "Small",
    className: "h-64",
    padding: "p-4",
    titleSize: "text-lg",
    descriptionSize: "text-xs"
  },
  {
    name: "Medium (Default)",
    className: "h-80", 
    padding: "p-6",
    titleSize: "text-xl",
    descriptionSize: "text-sm"
  },
  {
    name: "Large",
    className: "h-96",
    padding: "p-8", 
    titleSize: "text-2xl",
    descriptionSize: "text-base"
  }
]

const cardVariations = [
  {
    name: "Default",
    description: "Standard polygon card with theme gradient"
  },
  {
    name: "Background Image",
    description: "Card with background image overlay"
  },
  {
    name: "Image Inside",
    description: "Card with image content inside"
  },
  {
    name: "Sleek Design",
    description: "Minimalist design with sleek dot and title"
  },
  {
    name: "Glass Morphism",
    description: "Frosted glass effect with blur and transparency"
  },
  {
    name: "Neon Glow",
    description: "Vibrant neon borders and glowing effects"
  },
  {
    name: "Gradient Overlay",
    description: "Full gradient background with overlay text"
  },
  {
    name: "Floating Elements",
    description: "Floating icons and animated elements"
  },
  {
    name: "Split Design",
    description: "Split layout with contrasting sections"
  },
  {
    name: "3D Card",
    description: "Three-dimensional depth and shadow effects"
  }
]

const difficultyLevels = [
  {
    name: "Beginner",
    color: "green",
    className: "bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400"
  },
  {
    name: "Intermediate", 
    color: "yellow",
    className: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400"
  },
  {
    name: "Advanced",
    color: "red", 
    className: "bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
  }
]

const layoutVariants = [
  {
    name: "Side by Side",
    layout: "grid-cols-1 lg:grid-cols-2",
    gap: "gap-12",
    alignment: "items-start"
  },
  {
    name: "Centered Stack",
    layout: "grid-cols-1",
    gap: "gap-8",
    alignment: "items-center text-center"
  },
  {
    name: "Wide Layout",
    layout: "grid-cols-1 lg:grid-cols-3",
    gap: "gap-16",
    alignment: "items-center"
  },
  {
    name: "Compact Grid",
    layout: "grid-cols-1 md:grid-cols-2",
    gap: "gap-6",
    alignment: "items-start"
  }
]

const backgroundVariants = [
  {
    name: "Gradient Background",
    className: "bg-gradient-to-br from-purple-50 to-blue-50",
    description: "Soft gradient background"
  },
  {
    name: "Solid Background",
    className: "bg-background",
    description: "Clean solid background"
  },
  {
    name: "Muted Background",
    className: "bg-muted/30",
    description: "Subtle muted background"
  },
  {
    name: "Dark Background",
    className: "bg-gray-900 text-white",
    description: "Dark theme background"
  }
]

interface DesignState {
  selectedTheme: number
  cardSize: number
  difficultyLevel: number
  layoutVariant: number
  backgroundVariant: number
  showPolygons: boolean
  polygonSize: 'small' | 'medium' | 'large'
  customTitle: string
  customDescription: string
  customTags: string[]
  showCornerCut: boolean
  cardStyle: 'default' | 'minimal' | 'bold'
  cardVariation: number
}

export default function DesignDemoPage() {
  const [designState, setDesignState] = useState<DesignState>({
    selectedTheme: 1, // Smart (Blue)
    cardSize: 1, // Medium
    difficultyLevel: 0, // Beginner
    layoutVariant: 0,
    backgroundVariant: 0,
    showPolygons: true,
    polygonSize: 'medium',
    customTitle: 'AI in Daily Life',
    customDescription: 'Discuss how AI is already part of students\' daily routines',
    customTags: ['discussion', 'everyday'],
    showCornerCut: true,
    cardStyle: 'default',
    cardVariation: 0 // Default
  })

  const [copiedCode, setCopiedCode] = useState<string | null>(null)

  const updateDesignState = (updates: Partial<DesignState>) => {
    setDesignState(prev => ({ ...prev, ...updates }))
  }

  const resetToDefault = () => {
    setDesignState({
      selectedTheme: 1, // Smart (Blue)
      cardSize: 1, // Medium
      difficultyLevel: 0, // Beginner
      layoutVariant: 0,
      backgroundVariant: 0,
      showPolygons: true,
      polygonSize: 'medium',
      customTitle: 'AI in Daily Life',
      customDescription: 'Discuss how AI is already part of students\' daily routines',
      customTags: ['discussion', 'everyday'],
      showCornerCut: true,
      cardStyle: 'default',
      cardVariation: 0 // Default
    })
  }

  const generateCode = () => {
    const currentTheme = cardThemes[designState.selectedTheme]
    const currentSize = cardSizes[designState.cardSize]
    const currentDifficulty = difficultyLevels[designState.difficultyLevel]
    const currentLayout = layoutVariants[designState.layoutVariant]
    const currentBackground = backgroundVariants[designState.backgroundVariant]
    
    return `// Generated Activity Card Code
<div className="${currentBackground.className}">
  <div className="grid ${currentLayout.layout} ${currentLayout.gap} ${currentLayout.alignment}">
    <div className="bg-gray-800 ${currentSize.className} p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
         style={{
           clipPath: "${designState.showCornerCut ? 'polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))' : 'none'}"
         }}>
      
      {/* Theme-colored top section */}
      <div 
        className="absolute inset-0 bg-gradient-to-r ${currentTheme.gradient} opacity-20 group-hover:opacity-30 transition-opacity duration-300"
        style={{
          clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
        }}
      />
      
      {/* Decorative Polygon Corner */}
      ${designState.showCornerCut ? `
      <div 
        className="absolute top-0 right-0 w-8 h-8 bg-white/10 dark:bg-black/10"
        style={{
          clipPath: "polygon(100% 0, 0 0, 100% 100%)"
        }}
      />
      ` : ''}
      
      {/* Activity Header */}
      <div className="relative z-10 flex items-start justify-between mb-4">
        <div className="flex items-center space-x-2">
          <div className="w-3 h-3 rounded-full ${currentTheme.dotColor}" />
          <span className="text-sm font-medium text-white">${currentTheme.theme}</span>
        </div>
        <span className="px-2 py-1 text-xs font-medium rounded-full ${currentDifficulty.className}">
          ${currentDifficulty.name}
        </span>
      </div>

      {/* Activity Content */}
      <div className="relative z-10 flex-1 flex flex-col">
        <div className="space-y-3 flex-1">
          <h4 className="${currentSize.titleSize} font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
            <span className="line-clamp-2">${designState.customTitle}</span>
          </h4>
          <p className="text-white ${currentSize.descriptionSize} leading-relaxed line-clamp-2">
            ${designState.customDescription}
          </p>
        </div>

        {/* Activity Meta */}
        <div className="mt-4 space-y-2">
          <div className="flex items-center space-x-2">
            <span className="text-xs text-gray-400">General</span>
          </div>
          
          <div className="flex flex-wrap gap-1">
            ${designState.customTags.map(tag => `
            <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
              ${tag}
            </span>
            `).join('')}
          </div>
        </div>

        {/* View Details Link */}
        <div className="mt-2 pt-2 border-t border-gray-700">
          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2">
            View Details →
          </span>
        </div>
      </div>
    </div>
  </div>
</div>`
  }

  const copyCode = async () => {
    const code = generateCode()
    try {
      await navigator.clipboard.writeText(code)
      setCopiedCode(code)
      setTimeout(() => setCopiedCode(null), 2000)
    } catch (err) {
      console.error('Failed to copy code:', err)
    }
  }

  const currentLayout = layoutVariants[designState.layoutVariant]
  const currentBackground = backgroundVariants[designState.backgroundVariant]

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
                    Back to Design Spec
                  </Button>
                </Link>
              </div>
              
              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-black dark:text-white">
                Activity Card Design Demo
              </h1>
              <p className="text-lg text-black dark:text-white">Interactive playground for testing polygon-shaped activity card designs</p>
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
                  <div className="flex items-center gap-2">
                    <Palette className="h-5 w-5" />
                    <h3 className="font-semibold">Card Theme</h3>
                  </div>
                  
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Theme Color</label>
                    <select 
                      value={designState.selectedTheme}
                      onChange={(e) => updateDesignState({ selectedTheme: parseInt(e.target.value) })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      {cardThemes.map((theme, index) => (
                        <option key={index} value={index}>{theme.name}</option>
                      ))}
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Card Size</label>
                    <select 
                      value={designState.cardSize}
                      onChange={(e) => updateDesignState({ cardSize: parseInt(e.target.value) })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      {cardSizes.map((size, index) => (
                        <option key={index} value={index}>{size.name}</option>
                      ))}
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Difficulty Level</label>
                    <select 
                      value={designState.difficultyLevel}
                      onChange={(e) => updateDesignState({ difficultyLevel: parseInt(e.target.value) })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      {difficultyLevels.map((level, index) => (
                        <option key={index} value={index}>{level.name}</option>
                      ))}
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="flex items-center gap-2">
                      <input
                        type="checkbox"
                        checked={designState.showCornerCut}
                        onChange={(e) => updateDesignState({ showCornerCut: e.target.checked })}
                        className="rounded"
                      />
                      <span className="text-sm font-medium">Corner Cut</span>
                    </label>
                  </div>
                </div>
              </SectionCard>

              {/* Content Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <div className="flex items-center gap-2">
                    <Type className="h-5 w-5" />
                    <h3 className="font-semibold">Content</h3>
                  </div>
                  
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Activity Title</label>
                    <input
                      type="text"
                      value={designState.customTitle}
                      onChange={(e) => updateDesignState({ customTitle: e.target.value })}
                      className="w-full p-2 border rounded-md text-sm"
                      placeholder="Enter activity title"
                    />
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Description</label>
                    <textarea
                      value={designState.customDescription}
                      onChange={(e) => updateDesignState({ customDescription: e.target.value })}
                      className="w-full p-2 border rounded-md text-sm h-20 resize-none"
                      placeholder="Enter activity description"
                    />
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Tags (comma separated)</label>
                    <input
                      type="text"
                      value={designState.customTags.join(', ')}
                      onChange={(e) => updateDesignState({ customTags: e.target.value.split(',').map(tag => tag.trim()).filter(tag => tag) })}
                      className="w-full p-2 border rounded-md text-sm"
                      placeholder="discussion, everyday, beginner"
                    />
                  </div>
                </div>
              </SectionCard>

              {/* Layout Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <div className="flex items-center gap-2">
                    <Layout className="h-5 w-5" />
                    <h3 className="font-semibold">Layout</h3>
                  </div>
                  
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Grid Layout</label>
                    <select 
                      value={designState.layoutVariant}
                      onChange={(e) => updateDesignState({ layoutVariant: parseInt(e.target.value) })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      {layoutVariants.map((variant, index) => (
                        <option key={index} value={index}>{variant.name}</option>
                      ))}
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Background</label>
                    <select 
                      value={designState.backgroundVariant}
                      onChange={(e) => updateDesignState({ backgroundVariant: parseInt(e.target.value) })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      {backgroundVariants.map((variant, index) => (
                        <option key={index} value={index}>{variant.name}</option>
                      ))}
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Card Style</label>
                    <select 
                      value={designState.cardStyle}
                      onChange={(e) => updateDesignState({ cardStyle: e.target.value as 'default' | 'minimal' | 'bold' })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      <option value="default">Default</option>
                      <option value="minimal">Minimal</option>
                      <option value="bold">Bold</option>
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Card Variation</label>
                    <select 
                      value={designState.cardVariation}
                      onChange={(e) => updateDesignState({ cardVariation: parseInt(e.target.value) })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      {cardVariations.map((variation, index) => (
                        <option key={index} value={index}>{variation.name}</option>
                      ))}
                    </select>
                  </div>
                </div>
              </SectionCard>

              {/* Actions */}
              <SectionCard>
                <div className="space-y-4">
                  <div className="flex items-center gap-2">
                    <Settings className="h-5 w-5" />
                    <h3 className="font-semibold">Actions</h3>
                  </div>
                  
                  <Button 
                    onClick={resetToDefault}
                    variant="outline" 
                    className="w-full gap-2"
                  >
                    <RotateCcw className="h-4 w-4" />
                    Reset to Default
                  </Button>

                  <Button 
                    onClick={copyCode}
                    variant="outline" 
                    className="w-full gap-2"
                  >
                    {copiedCode ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                    {copiedCode ? 'Copied!' : 'Copy Code'}
                  </Button>

                  <div className="text-xs text-muted-foreground">
                    <p>Current Settings:</p>
                    <ul className="mt-1 space-y-1">
                      <li>• {cardThemes[designState.selectedTheme].name}</li>
                      <li>• {cardSizes[designState.cardSize].name}</li>
                      <li>• {difficultyLevels[designState.difficultyLevel].name}</li>
                      <li>• {layoutVariants[designState.layoutVariant].name}</li>
                    </ul>
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
              title="🎨 Live Preview - Activity Card"
              description="See your activity card design changes in real-time"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className={`relative p-20 rounded-xl overflow-hidden ${currentBackground.className}`}>
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5" />
              <div className="relative">
                <div className={`grid ${currentLayout.layout} ${currentLayout.gap} ${currentLayout.alignment}`}>
                  {/* Activity Card Preview - Different Variations */}
                  {designState.cardVariation === 0 && (
                    // Default Card
                    <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Theme-colored top section */}
                      <div 
                        className={`absolute inset-0 bg-gradient-to-r ${cardThemes[designState.selectedTheme].gradient} opacity-20 group-hover:opacity-30 transition-opacity duration-300`}
                        style={{
                          clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                        }}
                      />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-white/10 dark:bg-black/10"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className={`w-3 h-3 rounded-full ${cardThemes[designState.selectedTheme].dotColor}`} />
                          <span className="text-sm font-medium text-white">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className}`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-400">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* View Details Link */}
                        <div className="mt-2 pt-2 border-t border-gray-700">
                          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 1 && (
                    // Background Image Card
                    <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Background Image */}
                      <div 
                        className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
                        style={{
                          backgroundImage: "url('https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center')"
                        }}
                      />
                      
                      {/* Dark overlay */}
                      <div className="absolute inset-0 bg-black/40 group-hover:bg-black/30 transition-colors duration-300" />
                      
                      {/* Theme-colored top section */}
                      <div 
                        className={`absolute inset-0 bg-gradient-to-r ${cardThemes[designState.selectedTheme].gradient} opacity-30 group-hover:opacity-40 transition-opacity duration-300`}
                        style={{
                          clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                        }}
                      />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-white/20 dark:bg-black/20"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className={`w-3 h-3 rounded-full ${cardThemes[designState.selectedTheme].dotColor}`} />
                          <span className="text-sm font-medium text-white">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className}`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-300">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-700/80 text-gray-200 px-2 py-1 rounded truncate max-w-[120px]">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* View Details Link */}
                        <div className="mt-2 pt-2 border-t border-gray-600">
                          <span className="text-purple-300 text-sm font-medium group-hover:text-purple-200 transition-colors ml-2">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 2 && (
                    // Image Inside Card
                    <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Theme-colored top section */}
                      <div 
                        className={`absolute inset-0 bg-gradient-to-r ${cardThemes[designState.selectedTheme].gradient} opacity-20 group-hover:opacity-30 transition-opacity duration-300`}
                        style={{
                          clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                        }}
                      />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-white/10 dark:bg-black/10"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className={`w-3 h-3 rounded-full ${cardThemes[designState.selectedTheme].dotColor}`} />
                          <span className="text-sm font-medium text-white">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className}`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Image Content */}
                      <div className="relative z-10 mb-4">
                        <div className="w-full h-32 bg-gray-700 rounded-lg overflow-hidden">
                          <img 
                            src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=200&fit=crop&crop=center" 
                            alt="AI Activity" 
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                          />
                        </div>
                      </div>

                      {/* Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-purple-300 transition-colors leading-tight`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-400">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* View Details Link */}
                        <div className="mt-2 pt-2 border-t border-gray-700">
                          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 3 && (
                    // Sleek Design Card
                    <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Minimal theme accent */}
                      <div 
                        className={`absolute top-0 left-0 right-0 h-1 bg-gradient-to-r ${cardThemes[designState.selectedTheme].gradient}`}
                      />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-white/5 dark:bg-black/5"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Sleek Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-6">
                        <div className="flex items-center space-x-3">
                          <div className={`w-2 h-2 rounded-full ${cardThemes[designState.selectedTheme].dotColor} shadow-lg`} />
                          <span className="text-xs font-light text-gray-300 tracking-wider uppercase">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-3 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className} border`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Sleek Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-4 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-light text-white group-hover:text-gray-100 transition-colors leading-tight tracking-wide`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-gray-300 ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2 font-light`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Sleek Activity Meta */}
                        <div className="mt-6 space-y-3">
                          <div className="flex items-center space-x-2">
                            <div className="w-1 h-1 bg-gray-500 rounded-full" />
                            <span className="text-xs text-gray-500 font-light">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-2">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-700/50 text-gray-300 px-3 py-1 rounded-full truncate max-w-[120px] border border-gray-600">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* Sleek View Details Link */}
                        <div className="mt-4 pt-3 border-t border-gray-700/50">
                          <span className="text-gray-400 text-sm font-light group-hover:text-white transition-colors ml-2 flex items-center gap-2">
                            View Details
                            <svg className="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                            </svg>
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 4 && (
                    // Glass Morphism Card
                    <div className="bg-white/10 backdrop-blur-md p-6 border border-white/20 hover:border-white/30 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Glass overlay */}
                      <div className="absolute inset-0 bg-white/5 backdrop-blur-sm" />
                      
                      {/* Theme accent with glass effect */}
                      <div 
                        className={`absolute top-0 left-0 right-0 h-2 bg-gradient-to-r ${cardThemes[designState.selectedTheme].gradient} opacity-60`}
                      />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-white/20 backdrop-blur-sm"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Glass Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className={`w-3 h-3 rounded-full ${cardThemes[designState.selectedTheme].dotColor} shadow-lg`} />
                          <span className="text-sm font-medium text-white/90">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className} backdrop-blur-sm`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Glass Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-white/90 transition-colors leading-tight h-16 flex items-start mt-4`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-white/80 ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Glass Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-white/60">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-white/20 text-white/80 px-2 py-1 rounded backdrop-blur-sm truncate max-w-[120px]">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* Glass View Details Link */}
                        <div className="mt-2 pt-2 border-t border-white/20">
                          <span className="text-white/70 text-sm font-medium group-hover:text-white transition-colors ml-2">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 5 && (
                    // Neon Glow Card
                    <div className="bg-gray-900 p-6 border-2 border-transparent hover:border-purple-500/50 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/25"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Neon gradient background */}
                      <div 
                        className={`absolute inset-0 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} opacity-10 group-hover:opacity-20 transition-opacity duration-300`}
                      />
                      
                      {/* Neon border effect */}
                      <div className={`absolute inset-0 bg-gradient-to-r ${cardThemes[designState.selectedTheme].gradient} opacity-0 group-hover:opacity-30 transition-opacity duration-300 rounded-lg blur-sm`} />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className={`absolute top-0 right-0 w-8 h-8 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} opacity-20 group-hover:opacity-40 transition-opacity duration-300`}
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Neon Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className={`w-3 h-3 rounded-full ${cardThemes[designState.selectedTheme].dotColor} shadow-lg shadow-${cardThemes[designState.selectedTheme].color}-500/50`} />
                          <span className="text-sm font-medium text-white group-hover:text-purple-300 transition-colors">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className} group-hover:shadow-lg group-hover:shadow-${difficultyLevels[designState.difficultyLevel].color}-500/50`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Neon Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-gray-300 group-hover:text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2 transition-colors`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Neon Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-400 group-hover:text-purple-400 transition-colors">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-800 text-gray-300 px-2 py-1 rounded truncate max-w-[120px] group-hover:bg-purple-900/30 group-hover:text-purple-300 transition-colors">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* Neon View Details Link */}
                        <div className="mt-2 pt-2 border-t border-gray-700 group-hover:border-purple-500/30 transition-colors">
                          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2 group-hover:drop-shadow-lg group-hover:drop-shadow-purple-500/50">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 6 && (
                    // Gradient Overlay Card
                    <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Full gradient background */}
                      <div 
                        className={`absolute inset-0 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} opacity-30 group-hover:opacity-40 transition-opacity duration-300`}
                      />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-white/20 dark:bg-black/20"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Gradient Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className="w-3 h-3 rounded-full bg-white/80" />
                          <span className="text-sm font-medium text-white">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className="px-2 py-1 text-xs font-medium rounded-full bg-white/20 text-white backdrop-blur-sm">
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Gradient Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-white/90 transition-colors leading-tight h-16 flex items-start mt-4`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-white/90 ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Gradient Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-white/70">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-white/20 text-white px-2 py-1 rounded backdrop-blur-sm truncate max-w-[120px]">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* Gradient View Details Link */}
                        <div className="mt-2 pt-2 border-t border-white/30">
                          <span className="text-white/80 text-sm font-medium group-hover:text-white transition-colors ml-2">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 7 && (
                    // Floating Elements Card
                    <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Floating background elements */}
                      <div className="absolute inset-0 overflow-hidden">
                        <div className={`absolute -top-4 -right-4 w-20 h-20 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} rounded-full opacity-10 group-hover:opacity-20 transition-all duration-500 group-hover:scale-110`} />
                        <div className={`absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} rounded-full opacity-10 group-hover:opacity-20 transition-all duration-700 group-hover:scale-110`} />
                        <div className={`absolute top-1/2 right-8 w-8 h-8 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} rounded-full opacity-5 group-hover:opacity-15 transition-all duration-1000 group-hover:translate-y-2`} />
                      </div>
                      
                      {/* Theme accent */}
                      <div 
                        className={`absolute inset-0 bg-gradient-to-r ${cardThemes[designState.selectedTheme].gradient} opacity-15 group-hover:opacity-25 transition-opacity duration-300`}
                        style={{
                          clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                        }}
                      />
                      
                      {/* Decorative Polygon Corner */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-white/10 dark:bg-black/10"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* Floating Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className={`w-3 h-3 rounded-full ${cardThemes[designState.selectedTheme].dotColor} group-hover:animate-pulse`} />
                          <span className="text-sm font-medium text-white group-hover:text-purple-300 transition-colors">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className} group-hover:animate-bounce`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* Floating Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Floating Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-400">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px] group-hover:bg-purple-700/50 group-hover:text-purple-200 transition-colors">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* Floating View Details Link */}
                        <div className="mt-2 pt-2 border-t border-gray-700">
                          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2 group-hover:translate-x-1 inline-block">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 8 && (
                    // Split Design Card
                    <div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* Left side - Theme section */}
                      <div 
                        className={`flex-1 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} p-6 relative`}
                      >
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
                        <div className="flex items-center space-x-2 mb-4">
                          <div className="w-3 h-3 rounded-full bg-white" />
                          <span className="text-sm font-medium text-white">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        
                        {/* Title in theme section */}
                        <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white leading-tight`}>
                          <span className="line-clamp-2">{designState.customTitle}</span>
                        </h4>
                      </div>
                      
                      {/* Right side - Content section */}
                      <div className="bg-gray-800 p-6 flex-1 flex flex-col">
                        {/* Difficulty badge */}
                        <div className="flex justify-end mb-4">
                          <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className}`}>
                            {difficultyLevels[designState.difficultyLevel].name}
                          </span>
                        </div>

                        {/* Description */}
                        <div className="flex-1">
                          <p className={`text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-3`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* Tags */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-400">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* View Details Link */}
                        <div className="mt-4 pt-2 border-t border-gray-700">
                          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}

                  {designState.cardVariation === 9 && (
                    // 3D Card
                    <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl transform hover:-translate-y-2"
                         style={{
                           clipPath: designState.showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                         }}>
                      
                      {/* 3D depth layers */}
                      <div className="absolute inset-0 bg-gradient-to-br from-gray-700 to-gray-900 transform translate-z-0" />
                      <div className={`absolute inset-0 bg-gradient-to-br ${cardThemes[designState.selectedTheme].gradient} opacity-20 group-hover:opacity-30 transition-opacity duration-300 transform translate-z-1`} />
                      
                      {/* 3D shadow effects */}
                      <div className="absolute inset-0 shadow-inner shadow-black/20" />
                      <div className="absolute -inset-1 bg-gradient-to-r from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                      
                      {/* Decorative Polygon Corner with 3D effect */}
                      {designState.showCornerCut && (
                        <div 
                          className="absolute top-0 right-0 w-8 h-8 bg-gradient-to-br from-white/20 to-transparent shadow-lg"
                          style={{
                            clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                          }}
                        />
                      )}
                      
                      {/* 3D Activity Header */}
                      <div className="relative z-10 flex items-start justify-between mb-4">
                        <div className="flex items-center space-x-2">
                          <div className={`w-3 h-3 rounded-full ${cardThemes[designState.selectedTheme].dotColor} shadow-lg shadow-${cardThemes[designState.selectedTheme].color}-500/50`} />
                          <span className="text-sm font-medium text-white group-hover:text-purple-300 transition-colors drop-shadow-sm">{cardThemes[designState.selectedTheme].theme}</span>
                        </div>
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[designState.difficultyLevel].className} shadow-lg`}>
                          {difficultyLevels[designState.difficultyLevel].name}
                        </span>
                      </div>

                      {/* 3D Activity Content */}
                      <div className="relative z-10 flex-1 flex flex-col">
                        <div className="space-y-3 flex-1">
                          <h4 className={`${cardSizes[designState.cardSize].titleSize} font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4 drop-shadow-sm`}>
                            <span className="line-clamp-2">{designState.customTitle}</span>
                          </h4>
                          <p className={`text-white ${cardSizes[designState.cardSize].descriptionSize} leading-relaxed line-clamp-2 drop-shadow-sm`}>
                            {designState.customDescription}
                          </p>
                        </div>

                        {/* 3D Activity Meta */}
                        <div className="mt-4 space-y-2">
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-400 drop-shadow-sm">General</span>
                          </div>
                          
                          <div className="flex flex-wrap gap-1">
                            {designState.customTags.map((tag, index) => (
                              <span key={index} className="text-xs bg-gray-700/80 text-gray-300 px-2 py-1 rounded truncate max-w-[120px] shadow-sm group-hover:bg-purple-700/50 group-hover:text-purple-200 transition-colors">
                                {tag}
                              </span>
                            ))}
                          </div>
                        </div>

                        {/* 3D View Details Link */}
                        <div className="mt-2 pt-2 border-t border-gray-700">
                          <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2 drop-shadow-sm">
                            View Details →
                          </span>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Design Variations Showcase */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-900">
          <Container>
            <SectionHeader
              title="🎨 Activity Card Variations"
              description="Different card styles and themes to inspire your designs"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
              {/* Safe Theme Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  <div 
                    className="absolute inset-0 bg-gradient-to-r from-red-500 to-red-600 opacity-20 group-hover:opacity-30 transition-opacity duration-300"
                    style={{
                      clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                    }}
                  />
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-red-500" />
                      <span className="text-sm font-medium text-white">BE SAFE</span>
                    </div>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                      Advanced
                    </span>
                  </div>
                  <div className="relative z-10 flex-1 flex flex-col">
                    <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                      <span className="line-clamp-2">AI Privacy Workshop</span>
                    </h4>
                    <p className="text-white text-sm leading-relaxed line-clamp-2">
                      Learn about data privacy and safe AI usage practices
                    </p>
                    <div className="mt-4 space-y-2">
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">privacy</span>
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">security</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Safe Theme (Red)</h4>
                  <p className="text-sm text-muted-foreground">Safety-focused with red gradient and advanced difficulty</p>
                </div>
              </SectionCard>

              {/* Creative Theme Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  <div 
                    className="absolute inset-0 bg-gradient-to-r from-green-500 to-green-600 opacity-20 group-hover:opacity-30 transition-opacity duration-300"
                    style={{
                      clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                    }}
                  />
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-green-500" />
                      <span className="text-sm font-medium text-white">BE CREATIVE</span>
                    </div>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                      Intermediate
                    </span>
                  </div>
                  <div className="relative z-10 flex-1 flex flex-col">
                    <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                      <span className="line-clamp-2">AI Art Generation</span>
                    </h4>
                    <p className="text-white text-sm leading-relaxed line-clamp-2">
                      Create digital art using AI tools and explore creative possibilities
                    </p>
                    <div className="mt-4 space-y-2">
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">art</span>
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">creative</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Creative Theme (Green)</h4>
                  <p className="text-sm text-muted-foreground">Innovation-focused with green gradient and intermediate level</p>
                </div>
              </SectionCard>

              {/* Future Theme Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  <div 
                    className="absolute inset-0 bg-gradient-to-r from-orange-500 to-orange-600 opacity-20 group-hover:opacity-30 transition-opacity duration-300"
                    style={{
                      clipPath: 'polygon(0% 0%, calc(100% - 12px) 0%, 100% 12px, 100% 18%, 0% 18%)'
                    }}
                  />
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-orange-500" />
                      <span className="text-sm font-medium text-white">BE FUTURE</span>
                    </div>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                      Beginner
                    </span>
                  </div>
                  <div className="relative z-10 flex-1 flex flex-col">
                    <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                      <span className="line-clamp-2">AI Career Pathways</span>
                    </h4>
                    <p className="text-white text-sm leading-relaxed line-clamp-2">
                      Explore future career opportunities in AI and technology fields
                    </p>
                    <div className="mt-4 space-y-2">
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">career</span>
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">future</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Future Theme (Orange)</h4>
                  <p className="text-sm text-muted-foreground">Progress-focused with orange gradient and beginner level</p>
                </div>
              </SectionCard>

              {/* Sleek Design Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  
                  {/* Minimal theme accent */}
                  <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-blue-600" />
                  
                  {/* Decorative Polygon Corner */}
                  <div 
                    className="absolute top-0 right-0 w-8 h-8 bg-white/5 dark:bg-black/5"
                    style={{
                      clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                    }}
                  />
                  
                  {/* Sleek Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-6">
                    <div className="flex items-center space-x-3">
                      <div className="w-2 h-2 rounded-full bg-blue-500 shadow-lg" />
                      <span className="text-xs font-light text-gray-300 tracking-wider uppercase">BE SMART</span>
                    </div>
                    <span className="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 border">
                      Beginner
                    </span>
                  </div>

                  {/* Sleek Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-4 flex-1">
                      <h4 className="text-xl font-light text-white group-hover:text-gray-100 transition-colors leading-tight tracking-wide">
                        <span className="line-clamp-2">AI Ethics Discussion</span>
                      </h4>
                      <p className="text-gray-300 text-sm leading-relaxed line-clamp-2 font-light">
                        Explore ethical considerations in AI development and usage
                      </p>
                    </div>

                    {/* Sleek Activity Meta */}
                    <div className="mt-6 space-y-3">
                      <div className="flex items-center space-x-2">
                        <div className="w-1 h-1 bg-gray-500 rounded-full" />
                        <span className="text-xs text-gray-500 font-light">Ethics</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-2">
                        <span className="text-xs bg-gray-700/50 text-gray-300 px-3 py-1 rounded-full truncate max-w-[120px] border border-gray-600">
                          ethics
                        </span>
                        <span className="text-xs bg-gray-700/50 text-gray-300 px-3 py-1 rounded-full truncate max-w-[120px] border border-gray-600">
                          discussion
                        </span>
                      </div>
                    </div>

                    {/* Sleek View Details Link */}
                    <div className="mt-4 pt-3 border-t border-gray-700/50">
                      <span className="text-gray-400 text-sm font-light group-hover:text-white transition-colors ml-2 flex items-center gap-2">
                        View Details
                        <svg className="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                        </svg>
                      </span>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Sleek Design (Blue)</h4>
                  <p className="text-sm text-muted-foreground">Minimalist design with sleek dot and typography</p>
                </div>
              </SectionCard>

              {/* Glass Morphism Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-white/10 backdrop-blur-md p-6 border border-white/20 hover:border-white/30 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  
                  {/* Glass overlay */}
                  <div className="absolute inset-0 bg-white/5 backdrop-blur-sm" />
                  
                  {/* Theme accent with glass effect */}
                  <div className="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-purple-500 to-purple-600 opacity-60" />
                  
                  {/* Decorative Polygon Corner */}
                  <div 
                    className="absolute top-0 right-0 w-8 h-8 bg-white/20 backdrop-blur-sm"
                    style={{
                      clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                    }}
                  />
                  
                  {/* Glass Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-purple-500 shadow-lg" />
                      <span className="text-sm font-medium text-white/90">BE RESPONSIBLE</span>
                    </div>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 backdrop-blur-sm">
                      Advanced
                    </span>
                  </div>

                  {/* Glass Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-3 flex-1">
                      <h4 className="text-xl font-bold text-white group-hover:text-white/90 transition-colors leading-tight h-16 flex items-start mt-4">
                        <span className="line-clamp-2">AI Bias Detection</span>
                      </h4>
                      <p className="text-white/80 text-sm leading-relaxed line-clamp-2">
                        Learn to identify and address bias in AI systems
                      </p>
                    </div>

                    {/* Glass Activity Meta */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-white/60">Ethics</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-white/20 text-white/80 px-2 py-1 rounded backdrop-blur-sm truncate max-w-[120px]">
                          bias
                        </span>
                        <span className="text-xs bg-white/20 text-white/80 px-2 py-1 rounded backdrop-blur-sm truncate max-w-[120px]">
                          fairness
                        </span>
                      </div>
                    </div>

                    {/* Glass View Details Link */}
                    <div className="mt-2 pt-2 border-t border-white/20">
                      <span className="text-white/70 text-sm font-medium group-hover:text-white transition-colors ml-2">
                        View Details →
                      </span>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Glass Morphism (Purple)</h4>
                  <p className="text-sm text-muted-foreground">Frosted glass effect with blur and transparency</p>
                </div>
              </SectionCard>

              {/* Neon Glow Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gray-900 p-6 border-2 border-transparent hover:border-purple-500/50 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/25 h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  
                  {/* Neon gradient background */}
                  <div className="absolute inset-0 bg-gradient-to-br from-pink-500 to-purple-600 opacity-10 group-hover:opacity-20 transition-opacity duration-300" />
                  
                  {/* Neon border effect */}
                  <div className="absolute inset-0 bg-gradient-to-r from-pink-500 to-purple-600 opacity-0 group-hover:opacity-30 transition-opacity duration-300 rounded-lg blur-sm" />
                  
                  {/* Decorative Polygon Corner */}
                  <div 
                    className="absolute top-0 right-0 w-8 h-8 bg-gradient-to-br from-pink-500 to-purple-600 opacity-20 group-hover:opacity-40 transition-opacity duration-300"
                    style={{
                      clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                    }}
                  />
                  
                  {/* Neon Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-pink-500 shadow-lg shadow-pink-500/50" />
                      <span className="text-sm font-medium text-white group-hover:text-purple-300 transition-colors">BE CREATIVE</span>
                    </div>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 group-hover:shadow-lg group-hover:shadow-yellow-500/50">
                      Intermediate
                    </span>
                  </div>

                  {/* Neon Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-3 flex-1">
                      <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                        <span className="line-clamp-2">AI Music Generation</span>
                      </h4>
                      <p className="text-gray-300 group-hover:text-white text-sm leading-relaxed line-clamp-2 transition-colors">
                        Create music using AI tools and explore creative possibilities
                      </p>
                    </div>

                    {/* Neon Activity Meta */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400 group-hover:text-purple-400 transition-colors">Creative</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-800 text-gray-300 px-2 py-1 rounded truncate max-w-[120px] group-hover:bg-purple-900/30 group-hover:text-purple-300 transition-colors">
                          music
                        </span>
                        <span className="text-xs bg-gray-800 text-gray-300 px-2 py-1 rounded truncate max-w-[120px] group-hover:bg-purple-900/30 group-hover:text-purple-300 transition-colors">
                          creative
                        </span>
                      </div>
                    </div>

                    {/* Neon View Details Link */}
                    <div className="mt-2 pt-2 border-t border-gray-700 group-hover:border-purple-500/30 transition-colors">
                      <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2 group-hover:drop-shadow-lg group-hover:drop-shadow-purple-500/50">
                        View Details →
                      </span>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Neon Glow (Pink)</h4>
                  <p className="text-sm text-muted-foreground">Vibrant neon borders and glowing effects</p>
                </div>
              </SectionCard>

              {/* Gradient Overlay Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  
                  {/* Full gradient background */}
                  <div className="absolute inset-0 bg-gradient-to-br from-orange-500 to-red-600 opacity-30 group-hover:opacity-40 transition-opacity duration-300" />
                  
                  {/* Decorative Polygon Corner */}
                  <div 
                    className="absolute top-0 right-0 w-8 h-8 bg-white/20 dark:bg-black/20"
                    style={{
                      clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                    }}
                  />
                  
                  {/* Gradient Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-white/80" />
                      <span className="text-sm font-medium text-white">BE FUTURE</span>
                    </div>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-white/20 text-white backdrop-blur-sm">
                      Beginner
                    </span>
                  </div>

                  {/* Gradient Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-3 flex-1">
                      <h4 className="text-xl font-bold text-white group-hover:text-white/90 transition-colors leading-tight h-16 flex items-start mt-4">
                        <span className="line-clamp-2">AI Career Exploration</span>
                      </h4>
                      <p className="text-white/90 text-sm leading-relaxed line-clamp-2">
                        Discover career paths in AI and emerging technologies
                      </p>
                    </div>

                    {/* Gradient Activity Meta */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-white/70">Career</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-white/20 text-white px-2 py-1 rounded backdrop-blur-sm truncate max-w-[120px]">
                          career
                        </span>
                        <span className="text-xs bg-white/20 text-white px-2 py-1 rounded backdrop-blur-sm truncate max-w-[120px]">
                          future
                        </span>
                      </div>
                    </div>

                    {/* Gradient View Details Link */}
                    <div className="mt-2 pt-2 border-t border-white/30">
                      <span className="text-white/80 text-sm font-medium group-hover:text-white transition-colors ml-2">
                        View Details →
                      </span>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Gradient Overlay (Orange)</h4>
                  <p className="text-sm text-muted-foreground">Full gradient background with overlay text</p>
                </div>
              </SectionCard>

              {/* Floating Elements Card */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-80"
                         style={{
                           clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                         }}>
                  
                  {/* Floating background elements */}
                  <div className="absolute inset-0 overflow-hidden">
                    <div className="absolute -top-4 -right-4 w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-full opacity-10 group-hover:opacity-20 transition-all duration-500 group-hover:scale-110" />
                    <div className="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full opacity-10 group-hover:opacity-20 transition-all duration-700 group-hover:scale-110" />
                    <div className="absolute top-1/2 right-8 w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-full opacity-5 group-hover:opacity-15 transition-all duration-1000 group-hover:translate-y-2" />
                  </div>
                  
                  {/* Theme accent */}
                  <div className="absolute inset-0 bg-gradient-to-r from-green-500 to-green-600 opacity-15 group-hover:opacity-25 transition-opacity duration-300"
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
                  
                  {/* Floating Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-green-500 group-hover:animate-pulse" />
                      <span className="text-sm font-medium text-white group-hover:text-purple-300 transition-colors">BE CREATIVE</span>
                    </div>
                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 group-hover:animate-bounce">
                      Intermediate
                    </span>
                  </div>

                  {/* Floating Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-3 flex-1">
                      <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                        <span className="line-clamp-2">AI Art Workshop</span>
                      </h4>
                      <p className="text-white text-sm leading-relaxed line-clamp-2">
                        Create digital art using AI tools and explore creative possibilities
                      </p>
                    </div>

                    {/* Floating Activity Meta */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400">Creative</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px] group-hover:bg-purple-700/50 group-hover:text-purple-200 transition-colors">
                          art
                        </span>
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px] group-hover:bg-purple-700/50 group-hover:text-purple-200 transition-colors">
                          creative
                        </span>
                      </div>
                    </div>

                    {/* Floating View Details Link */}
                    <div className="mt-2 pt-2 border-t border-gray-700">
                      <span className="text-purple-400 text-sm font-medium group-hover:text-purple-300 transition-colors ml-2 group-hover:translate-x-1 inline-block">
                        View Details →
                      </span>
                    </div>
                  </div>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Floating Elements (Green)</h4>
                  <p className="text-sm text-muted-foreground">Floating icons and animated elements</p>
                </div>
              </SectionCard>
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
            
            <div className="bg-gray-900 text-gray-100 p-6 rounded-lg overflow-x-auto">
              <pre className="text-sm">
                <code>{generateCode()}</code>
              </pre>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
