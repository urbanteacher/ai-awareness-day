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
    const currentTypography = typographyVariants[designState.typographyVariant]
    const currentLayout = layoutVariants[designState.layoutVariant]
    const currentBackground = backgroundVariants[designState.backgroundVariant]
    
    return `// Generated Design Code
<div className="${currentBackground.className}">
  <div className="grid ${currentLayout.layout} ${currentLayout.gap} ${currentLayout.alignment}">
    <div>
      <h1 className="${currentTypography.titleSize} ${currentTypography.titleWeight}">
        ${designState.customTitle}
      </h1>
      <p className="${currentTypography.taglineSize} font-semibold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
        ${designState.customTagline}
      </p>
      <p className="${currentTypography.descriptionSize} text-muted-foreground">
        ${designState.customDescription}
      </p>
    </div>
    ${designState.showPolygons ? `
    <div className="grid grid-cols-3 gap-4">
      <img src="/polygon-shapes/safe-polygon.svg" className="w-${designState.polygonSize === 'small' ? '32' : designState.polygonSize === 'large' ? '48' : '40'} h-${designState.polygonSize === 'small' ? '32' : designState.polygonSize === 'large' ? '48' : '40'}" />
      <img src="/polygon-shapes/smart-polygon.svg" className="w-${designState.polygonSize === 'small' ? '32' : designState.polygonSize === 'large' ? '48' : '40'} h-${designState.polygonSize === 'small' ? '32' : designState.polygonSize === 'large' ? '48' : '40'}" />
      <img src="/polygon-shapes/creative-polygon.svg" className="w-${designState.polygonSize === 'small' ? '32' : designState.polygonSize === 'large' ? '48' : '40'} h-${designState.polygonSize === 'small' ? '32' : designState.polygonSize === 'large' ? '48' : '40'}" />
    </div>
    ` : ''}
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
              {/* Typography Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <div className="flex items-center gap-2">
                    <Type className="h-5 w-5" />
                    <h3 className="font-semibold">Typography</h3>
                  </div>
                  
                  <div className="space-y-2">
                    <label className="text-sm font-medium">Style Variant</label>
                    <select 
                      value={designState.typographyVariant}
                      onChange={(e) => updateDesignState({ typographyVariant: parseInt(e.target.value) })}
                      className="w-full p-2 border rounded-md text-sm"
                    >
                      {typographyVariants.map((variant, index) => (
                        <option key={index} value={index}>{variant.name}</option>
                      ))}
                    </select>
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Custom Title</label>
                    <input
                      type="text"
                      value={designState.customTitle}
                      onChange={(e) => updateDesignState({ customTitle: e.target.value })}
                      className="w-full p-2 border rounded-md text-sm"
                    />
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Custom Tagline</label>
                    <input
                      type="text"
                      value={designState.customTagline}
                      onChange={(e) => updateDesignState({ customTagline: e.target.value })}
                      className="w-full p-2 border rounded-md text-sm"
                    />
                  </div>

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Custom Description</label>
                    <textarea
                      value={designState.customDescription}
                      onChange={(e) => updateDesignState({ customDescription: e.target.value })}
                      className="w-full p-2 border rounded-md text-sm h-20 resize-none"
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
                    <label className="text-sm font-medium">Layout Variant</label>
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
                </div>
              </SectionCard>

              {/* Visual Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <div className="flex items-center gap-2">
                    <Shapes className="h-5 w-5" />
                    <h3 className="font-semibold">Visual Elements</h3>
                  </div>
                  
                  <div className="space-y-2">
                    <label className="flex items-center gap-2">
                      <input
                        type="checkbox"
                        checked={designState.showPolygons}
                        onChange={(e) => updateDesignState({ showPolygons: e.target.checked })}
                        className="rounded"
                      />
                      <span className="text-sm font-medium">Show Polygons</span>
                    </label>
                  </div>

                  {designState.showPolygons && (
                    <div className="space-y-2">
                      <label className="text-sm font-medium">Polygon Size</label>
                      <select 
                        value={designState.polygonSize}
                        onChange={(e) => updateDesignState({ polygonSize: e.target.value as 'small' | 'medium' | 'large' })}
                        className="w-full p-2 border rounded-md text-sm"
                      >
                        <option value="small">Small (128px)</option>
                        <option value="medium">Medium (160px)</option>
                        <option value="large">Large (192px)</option>
                      </select>
                    </div>
                  )}

                  <div className="space-y-2">
                    <label className="text-sm font-medium">Color Themes</label>
                    <div className="grid grid-cols-2 gap-2">
                      {colorPalette.map((color, index) => (
                        <label key={index} className="flex items-center gap-2 text-xs">
                          <input
                            type="checkbox"
                            checked={designState.selectedColors.includes(color.name.toLowerCase().split(' ')[0])}
                            onChange={(e) => {
                              const colorName = color.name.toLowerCase().split(' ')[0]
                              const newColors = e.target.checked 
                                ? [...designState.selectedColors, colorName]
                                : designState.selectedColors.filter(c => c !== colorName)
                              updateDesignState({ selectedColors: newColors })
                            }}
                            className="rounded"
                          />
                          <span>{color.name.split(' ')[0]}</span>
                        </label>
                      ))}
                    </div>
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
