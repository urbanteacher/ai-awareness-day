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
    cardStyle: 'default'
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
      cardStyle: 'default'
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

  const currentTypography = typographyVariants[designState.typographyVariant]
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
                Design Demo & Testing
              </h1>
              <p className="text-lg text-black dark:text-white">Interactive design playground for AI Awareness Day 2026</p>
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
                      <li>• {typographyVariants[designState.typographyVariant].name}</li>
                      <li>• {layoutVariants[designState.layoutVariant].name}</li>
                      <li>• {backgroundVariants[designState.backgroundVariant].name}</li>
                      <li>• Polygons: {designState.showPolygons ? 'On' : 'Off'}</li>
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
              title="🎨 Live Preview"
              description="See your design changes in real-time"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className={`relative p-20 rounded-xl overflow-hidden ${currentBackground.className}`}>
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5" />
              <div className="relative">
                <div className={`grid ${currentLayout.layout} ${currentLayout.gap} ${currentLayout.alignment}`}>
                  {/* Text Content */}
                  <div>
                    <div className="relative">
                      <div className="flex flex-col items-start">
                        <span className={`${currentTypography.titleSize} ${currentTypography.titleWeight} leading-tight`}>
                          {designState.customTitle.split(' ')[0]}
                        </span>
                        <span className={`${currentTypography.titleSize} ${currentTypography.titleWeight} leading-tight`}>
                          {designState.customTitle.split(' ')[1]}
                        </span>
                        <span className={`${currentTypography.titleSize} ${currentTypography.titleWeight} leading-tight`}>
                          {designState.customTitle.split(' ')[2]}
                        </span>
                      </div>
                      <span className="absolute bottom-3 right-32 text-2xl md:text-4xl font-thin leading-tight text-muted-foreground">
                        2026
                      </span>
                    </div>
              
                    <p className={`${currentTypography.taglineSize} font-semibold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mt-6`}>
                      {designState.customTagline}
                    </p>
                
                    <p className={`${currentTypography.descriptionSize} text-muted-foreground leading-relaxed mt-4`}>
                      {designState.customDescription}
                    </p>
                  </div>

                  {/* Polygon Grid */}
                  {designState.showPolygons && (
                    <div className="hidden lg:flex justify-end">
                      <div className="grid grid-cols-3 gap-4">
                        <img 
                          src="/polygon-shapes/safe-polygon.svg" 
                          alt="Safe AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-32 h-32' : designState.polygonSize === 'large' ? 'w-48 h-48' : 'w-40 h-40'}`}
                        />
                        <img 
                          src="/polygon-shapes/smart-polygon.svg" 
                          alt="Smart AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-32 h-32' : designState.polygonSize === 'large' ? 'w-48 h-48' : 'w-40 h-40'}`}
                        />
                        <img 
                          src="/polygon-shapes/creative-polygon.svg" 
                          alt="Creative AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-32 h-32' : designState.polygonSize === 'large' ? 'w-48 h-48' : 'w-40 h-40'}`}
                        />
                        <img 
                          src="/polygon-shapes/responsible-polygon.svg" 
                          alt="Responsible AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-32 h-32' : designState.polygonSize === 'large' ? 'w-48 h-48' : 'w-40 h-40'}`}
                        />
                        <img 
                          src="/polygon-shapes/future-polygon.svg" 
                          alt="Future AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-32 h-32' : designState.polygonSize === 'large' ? 'w-48 h-48' : 'w-40 h-40'}`}
                        />
                        <div className={`${designState.polygonSize === 'small' ? 'w-32 h-32' : designState.polygonSize === 'large' ? 'w-48 h-48' : 'w-40 h-40'}`}></div>
                      </div>
                    </div>
                  )}

                  {/* Mobile Polygon Grid */}
                  {designState.showPolygons && (
                    <div className="flex justify-center mt-8 lg:hidden">
                      <div className="grid grid-cols-3 gap-4">
                        <img 
                          src="/polygon-shapes/safe-polygon.svg" 
                          alt="Safe AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-24 h-24' : designState.polygonSize === 'large' ? 'w-32 h-32' : 'w-28 h-28'}`}
                        />
                        <img 
                          src="/polygon-shapes/smart-polygon.svg" 
                          alt="Smart AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-24 h-24' : designState.polygonSize === 'large' ? 'w-32 h-32' : 'w-28 h-28'}`}
                        />
                        <img 
                          src="/polygon-shapes/creative-polygon.svg" 
                          alt="Creative AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-24 h-24' : designState.polygonSize === 'large' ? 'w-32 h-32' : 'w-28 h-28'}`}
                        />
                        <img 
                          src="/polygon-shapes/responsible-polygon.svg" 
                          alt="Responsible AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-24 h-24' : designState.polygonSize === 'large' ? 'w-32 h-32' : 'w-28 h-28'}`}
                        />
                        <img 
                          src="/polygon-shapes/future-polygon.svg" 
                          alt="Future AI Polygon" 
                          className={`${designState.polygonSize === 'small' ? 'w-24 h-24' : designState.polygonSize === 'large' ? 'w-32 h-32' : 'w-28 h-28'}`}
                        />
                        <div className={`${designState.polygonSize === 'small' ? 'w-24 h-24' : designState.polygonSize === 'large' ? 'w-32 h-32' : 'w-28 h-28'}`}></div>
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
              title="🎨 Design Variations"
              description="Quick preset designs to inspire your creativity"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {/* Variation 1: Bold & Dramatic */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gradient-to-br from-gray-900 to-black text-white p-8 rounded-lg">
                  <h3 className="text-4xl font-black mb-4">AI AWARENESS DAY</h3>
                  <p className="text-xl font-semibold bg-gradient-to-r from-red-500 to-orange-500 bg-clip-text text-transparent mb-4">
                    Know it, Question it, Use it Wisely
                  </p>
                  <p className="text-gray-300">
                    A nationwide campaign equipping students with critical AI skills.
                  </p>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Bold & Dramatic</h4>
                  <p className="text-sm text-muted-foreground">Dark theme with high contrast and bold typography</p>
                </div>
              </SectionCard>

              {/* Variation 2: Clean & Minimal */}
              <SectionCard className="overflow-hidden">
                <div className="bg-white border-2 border-gray-200 p-8 rounded-lg">
                  <h3 className="text-3xl font-light text-gray-900 mb-4">AI AWARENESS DAY</h3>
                  <p className="text-lg font-medium text-gray-600 mb-4">
                    Know it, Question it, Use it Wisely
                  </p>
                  <p className="text-gray-500">
                    A nationwide campaign equipping students with critical AI skills.
                  </p>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Clean & Minimal</h4>
                  <p className="text-sm text-muted-foreground">Light theme with clean lines and minimal styling</p>
                </div>
              </SectionCard>

              {/* Variation 3: Colorful & Vibrant */}
              <SectionCard className="overflow-hidden">
                <div className="bg-gradient-to-br from-purple-500 via-pink-500 to-red-500 text-white p-8 rounded-lg">
                  <h3 className="text-4xl font-bold mb-4">AI AWARENESS DAY</h3>
                  <p className="text-xl font-semibold text-yellow-300 mb-4">
                    Know it, Question it, Use it Wisely
                  </p>
                  <p className="text-purple-100">
                    A nationwide campaign equipping students with critical AI skills.
                  </p>
                </div>
                <div className="p-4">
                  <h4 className="font-semibold mb-2">Colorful & Vibrant</h4>
                  <p className="text-sm text-muted-foreground">Bright gradients with energetic color combinations</p>
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
