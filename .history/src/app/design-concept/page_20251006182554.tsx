import { Suspense } from 'react'
import { Navigation } from "@/components/navigation"
import { SectionWrapper, Container, SectionHeader, Grid, SectionCard } from "@/components/ui"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Palette, Type, Shapes, Layout, Eye, Download, Code, Ruler } from "lucide-react"
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
  },
  {
    name: "Tagline Gradient",
    hex: "Blue → Purple → Pink",
    usage: "Hero section tagline",
    gradient: "linear-gradient(to right, #2563eb, #7c3aed, #db2777)",
    tw: "bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600"
  },
  {
    name: "Muted Foreground",
    hex: "#6b7280",
    usage: "Text, borders, subtle elements"
  },
  {
    name: "Card Bottom",
    hex: "#1f2937",
    usage: "Polygon card bottom section - consistent in both light and dark modes"
  }
]

const typographyScale = [
  {
    name: "Main Title (Stacked)",
    size: "text-5xl md:text-7xl",
    weight: "font-bold",
    usage: "Hero section main title - stacked vertically",
    example: "AI\nAWARENESS\nDAY"
  },
  {
    name: "Year Text",
    size: "text-2xl md:text-4xl",
    weight: "font-thin",
    usage: "Hero section year display - positioned absolutely",
    example: "2026"
  },
  {
    name: "Tagline",
    size: "text-2xl md:text-3xl",
    weight: "font-semibold",
    usage: "Hero section tagline with gradient",
    example: "Know it, Question it, Use it Wisely"
  },
  {
    name: "Description",
    size: "text-lg md:text-xl",
    weight: "font-normal",
    usage: "Hero section description text",
    example: "A nationwide campaign equipping students with critical AI skills through engaging activities, ethical discussions, and creative challenges."
  },
  {
    name: "Section Headers",
    size: "text-2xl sm:text-3xl lg:text-4xl",
    weight: "font-bold",
    usage: "Page section titles"
  },
  {
    name: "Body Text",
    size: "text-base",
    weight: "font-normal",
    usage: "Standard body text, descriptions"
  }
]

const polygonSpecs = [
  {
    variant: "Small",
    dimensions: "w-32 h-32",
    pixels: "128px × 128px",
    class: "w-32 h-32"
  },
  {
    variant: "Medium (Default)",
    dimensions: "w-40 h-40",
    pixels: "160px × 160px",
    class: "w-40 h-40"
  },
  {
    variant: "Large",
    dimensions: "w-48 h-48",
    pixels: "192px × 192px",
    class: "w-48 h-48"
  }
]

const technicalSpecs = [
  {
    label: "Shape Type",
    value: "6-sided polygon (hexagonal with chamfered corners)"
  },
  {
    label: "Format",
    value: "SVG with linearGradient definitions"
  },
  {
    label: "Corner Style",
    value: "45-degree beveled cuts at all 4 corners"
  },
  {
    label: "Gradient Direction",
    value: "135deg (diagonal top-left to bottom-right)"
  },
  {
    label: "Text Positioning",
    value: "Centered title, right-aligned bottom text"
  },
  {
    label: "Scalability",
    value: "Vector format - infinitely scalable"
  }
]

const clipPathCoords = "polygon(0% 0%, 75% 0%, 100% 25%, 100% 100%, 25% 100%, 0% 75%)"

export default function DesignConceptPage() {
  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
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
              
                  <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-black dark:text-white">
                    AI Awareness Day 2026
                  </h1>
                  <p className="text-lg text-black dark:text-white">Complete Design Specification & Visual Reference</p>
            </div>
          </Container>
        </SectionWrapper>

        {/* Hero Section Demo */}
        <SectionWrapper className="bg-white dark:bg-gray-800">
          <Container>
            <SectionHeader
              title="🎨 Hero Section - Live Preview"
              description="Complete hero section design with exact wording, layout, and animations from the main site"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="relative p-20 bg-muted/30 rounded-xl overflow-hidden">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5" />
              <div className="relative">
                {/* Exact Hero Layout from HeroContent.tsx */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                  {/* Left Section - Text and Buttons */}
                  <div>
                    <div className="relative">
                      <div className="flex flex-col items-start">
                        <span className="text-5xl md:text-7xl font-bold leading-tight">
                          AI
                        </span>
                        <span className="text-5xl md:text-7xl font-bold leading-tight">
                          AWARENESS
                        </span>
                        <span className="text-5xl md:text-7xl font-bold leading-tight">
                          DAY
                        </span>
                      </div>
                      <span className="absolute bottom-3 right-32 text-2xl md:text-4xl font-thin leading-tight text-muted-foreground">
                        2026
                      </span>
                    </div>
              
                    <p className="text-2xl md:text-3xl font-semibold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mt-6">
                      Know it, Question it, Use it Wisely
                    </p>
                
                    <p className="text-lg md:text-xl text-muted-foreground leading-relaxed mt-4">
                      A nationwide campaign equipping students with critical AI skills through engaging activities, 
                      ethical discussions, and creative challenges.
                    </p>
                  </div>

                  {/* Right Section - Polygon Grid */}
                  <div className="hidden lg:flex justify-end">
                    <div className="grid grid-cols-3 gap-4">
                      <img 
                        src="/polygon-shapes/safe-polygon.svg" 
                        alt="Safe AI Polygon" 
                        className="w-40 h-40"
                      />
                      <img 
                        src="/polygon-shapes/smart-polygon.svg" 
                        alt="Smart AI Polygon" 
                        className="w-40 h-40"
                      />
                      <img 
                        src="/polygon-shapes/creative-polygon.svg" 
                        alt="Creative AI Polygon" 
                        className="w-40 h-40"
                      />
                      <img 
                        src="/polygon-shapes/responsible-polygon.svg" 
                        alt="Responsible AI Polygon" 
                        className="w-40 h-40"
                      />
                      <img 
                        src="/polygon-shapes/future-polygon.svg" 
                        alt="Future AI Polygon" 
                        className="w-40 h-40"
                      />
                      <div className="w-40 h-40"></div>
                    </div>
                  </div>
                </div>

                {/* Mobile Polygon Grid */}
                <div className="flex justify-center mt-8 lg:hidden">
                  <div className="grid grid-cols-3 gap-4">
                    <img 
                      src="/polygon-shapes/safe-polygon.svg" 
                      alt="Safe AI Polygon" 
                      className="w-32 h-32"
                    />
                    <img 
                      src="/polygon-shapes/smart-polygon.svg" 
                      alt="Smart AI Polygon" 
                      className="w-32 h-32"
                    />
                    <img 
                      src="/polygon-shapes/creative-polygon.svg" 
                      alt="Creative AI Polygon" 
                      className="w-32 h-32"
                    />
                    <img 
                      src="/polygon-shapes/responsible-polygon.svg" 
                      alt="Responsible AI Polygon" 
                      className="w-32 h-32"
                    />
                    <img 
                      src="/polygon-shapes/future-polygon.svg" 
                      alt="Future AI Polygon" 
                      className="w-32 h-32"
                    />
                    <div className="w-32 h-32"></div>
                  </div>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-8">
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Main Title Layout</h4>
                  <p className="text-sm text-muted-foreground font-mono">text-5xl md:text-7xl | font-bold | stacked vertically</p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Year Positioning</h4>
                  <p className="text-sm text-muted-foreground font-mono">absolute bottom-3 right-32 | text-2xl md:text-4xl | font-thin</p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Tagline</h4>
                  <p className="text-sm text-muted-foreground font-mono">"Know it, Question it, Use it Wisely" | gradient text</p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Description</h4>
                  <p className="text-sm text-muted-foreground font-mono">"A nationwide campaign equipping students..." | text-lg md:text-xl</p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Layout Structure</h4>
                  <p className="text-sm text-muted-foreground font-mono">grid-cols-1 lg:grid-cols-2 | gap-12 | items-start</p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Responsive Behavior</h4>
                  <p className="text-sm text-muted-foreground font-mono">Desktop: side-by-side | Mobile: stacked with centered polygons</p>
                </div>
              </SectionCard>
            </div>
          </Container>
        </SectionWrapper>

        {/* Polygon Cards */}
        <SectionWrapper className="bg-white dark:bg-gray-800">
          <Container>
            <SectionHeader
              title="📐 Polygon Cards - All Variants"
              description="Interactive polygon cards with live measurements and specifications"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="grid grid-cols-2 md:grid-cols-5 gap-8 justify-items-center mb-8">
              <div className="text-center space-y-2">
                <img 
                  src="/polygon-shapes/safe-polygon.svg" 
                  alt="Safe AI Polygon" 
                  className="w-40 h-40 mx-auto"
                />
                <p className="text-sm font-medium">Safe</p>
                <p className="text-xs text-muted-foreground">Red Gradient</p>
              </div>

              <div className="text-center space-y-2">
                <img 
                  src="/polygon-shapes/smart-polygon.svg" 
                  alt="Smart AI Polygon" 
                  className="w-40 h-40 mx-auto"
                />
                <p className="text-sm font-medium">Smart</p>
                <p className="text-xs text-muted-foreground">Blue Gradient</p>
              </div>

              <div className="text-center space-y-2">
                <img 
                  src="/polygon-shapes/creative-polygon.svg" 
                  alt="Creative AI Polygon" 
                  className="w-40 h-40 mx-auto"
                />
                <p className="text-sm font-medium">Creative</p>
                <p className="text-xs text-muted-foreground">Green Gradient</p>
              </div>

              <div className="text-center space-y-2">
                <img 
                  src="/polygon-shapes/responsible-polygon.svg" 
                  alt="Responsible AI Polygon" 
                  className="w-40 h-40 mx-auto"
                />
                <p className="text-sm font-medium">Responsible</p>
                <p className="text-xs text-muted-foreground">Purple Gradient</p>
              </div>

              <div className="text-center space-y-2">
                <img 
                  src="/polygon-shapes/future-polygon.svg" 
                  alt="Future AI Polygon" 
                  className="w-40 h-40 mx-auto"
                />
                <p className="text-sm font-medium">Future</p>
                <p className="text-xs text-muted-foreground">Orange Gradient</p>
              </div>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full border-collapse">
                <thead>
                  <tr className="border-b">
                    <th className="text-left p-3 font-semibold">Size Variant</th>
                    <th className="text-left p-3 font-semibold">Dimensions</th>
                    <th className="text-left p-3 font-semibold">Pixels</th>
                    <th className="text-left p-3 font-semibold">Class</th>
                  </tr>
                </thead>
                <tbody>
                  {polygonSpecs.map((spec, index) => (
                    <tr key={index} className="border-b">
                      <td className="p-3 font-medium">{spec.variant}</td>
                      <td className="p-3 text-muted-foreground">{spec.dimensions}</td>
                      <td className="p-3 text-muted-foreground font-mono">{spec.pixels}</td>
                      <td className="p-3 text-muted-foreground font-mono bg-muted/50 rounded px-2">{spec.class}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </Container>
        </SectionWrapper>

        {/* Annotated Diagram */}
        <SectionWrapper className="bg-white dark:bg-gray-800">
          <Container>
            <SectionHeader
              title="📏 Polygon Shape Specifications"
              description="Detailed technical specifications and measurements for the polygon shape system"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="relative p-16 bg-muted/30 rounded-xl flex justify-center items-center">
              <div className="relative">
                <img 
                  src="/polygon-shapes/smart-polygon.svg" 
                  alt="Smart AI Polygon - Technical Reference" 
                  className="w-60 h-60"
                />
                
                {/* Annotations */}
                <div className="absolute -top-12 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 px-3 py-1 rounded shadow-lg text-sm font-semibold">
                  Width: 160px (Medium)
                </div>
                <div className="absolute -right-16 top-1/2 transform -translate-y-1/2 bg-white dark:bg-gray-800 px-3 py-1 rounded shadow-lg text-sm font-semibold">
                  Height: 160px
                </div>
                <div className="absolute -bottom-12 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 px-3 py-1 rounded shadow-lg text-sm font-semibold">
                  25% Corner Cuts
                </div>
                <div className="absolute -left-16 top-1/2 transform -translate-y-1/2 bg-white dark:bg-gray-800 px-3 py-1 rounded shadow-lg text-sm font-semibold">
                  SVG Polygon
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-8">
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">SVG Path Coordinates</h4>
                  <p className="text-sm text-muted-foreground font-mono">
                    M0,0 L120,0 L160,40 L160,160<br/>
                    L40,160 L0,120 Z
                  </p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Corner Cut Size</h4>
                  <p className="text-sm text-muted-foreground">25% of dimension = 40px (medium)</p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Gradient Section</h4>
                  <p className="text-sm text-muted-foreground">Height: 60% | 96px on medium size</p>
                </div>
              </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Text Section</h4>
                    <p className="text-sm text-muted-foreground">Height: 40% | 64px on medium | bg: #1f2937 (consistent in both modes)</p>
                  </div>
                </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Title Font</h4>
                  <p className="text-sm text-muted-foreground">text-lg: 18px | font-weight: 700</p>
                </div>
              </SectionCard>
              <SectionCard>
                <div className="space-y-2">
                  <h4 className="font-semibold">Subtitle Font</h4>
                  <p className="text-sm text-muted-foreground">text-xs: 12px | padding: 8px</p>
                </div>
              </SectionCard>
            </div>
          </Container>
        </SectionWrapper>

        {/* Color Palette */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-900">
          <Container>
            <SectionHeader
              title="🎨 Color Palette"
              description="Complete color system with gradients, hex codes, and usage guidelines"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="space-y-8">
              <div>
                <h3 className="text-xl font-semibold mb-4">Polygon Card Gradients</h3>
                <Grid cols={2} gap="md">
                  {colorPalette.slice(0, 5).map((color, index) => (
                    <SectionCard key={index} className="overflow-hidden">
                      <div 
                        className="h-24 flex items-center justify-center text-white font-bold text-lg"
                        style={{ background: color.gradient }}
                      >
                        {color.name.split(' ')[0]}
                      </div>
                      <div className="p-4">
                        <h4 className="font-semibold">{color.name}</h4>
                        <p className="text-sm text-muted-foreground font-mono">{color.hex}</p>
                        <p className="text-sm text-muted-foreground mt-1">{color.usage}</p>
                      </div>
                    </SectionCard>
                  ))}
                </Grid>
              </div>

              <div>
                <h3 className="text-xl font-semibold mb-4">Hero Section Colors</h3>
                <Grid cols={2} gap="md">
                  {colorPalette.slice(5).map((color, index) => (
                    <SectionCard key={index} className="overflow-hidden">
                      <div 
                        className="h-24 flex items-center justify-center text-white font-bold text-lg"
                        style={{ background: color.gradient || color.hex }}
                      >
                        {color.name.split(' ')[0]}
                      </div>
                      <div className="p-4">
                        <h4 className="font-semibold">{color.name}</h4>
                        <p className="text-sm text-muted-foreground font-mono">{color.hex}</p>
                        <p className="text-sm text-muted-foreground mt-1">{color.usage}</p>
                      </div>
                    </SectionCard>
                  ))}
                </Grid>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Activity Card Design */}
        <SectionWrapper className="bg-white dark:bg-gray-800">
          <Container>
            <SectionHeader
              title="🎯 Activity Card Design System"
              description="Complete specifications for activity cards used throughout the application"
              titleColor="purple"
              className="mb-8"
            />
            
            {/* Library Section Activity Cards */}
            <div className="mb-12">
              <h3 className="text-2xl font-bold mb-6">Library Section Activity Cards</h3>
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                {/* Sample Activity Card */}
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden h-80 flex flex-col hover:scale-105 hover:shadow-2xl"
                     style={{
                       clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                     }}>
                  {/* Theme-colored top section - 12% height */}
                  <div 
                    className="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-600 opacity-20 group-hover:opacity-30 transition-opacity duration-300"
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
                  
                  {/* Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-blue-500" />
                      <span className="text-sm font-medium text-white">BE SMART</span>
                    </div>
                    <div className="flex items-center space-x-1 text-yellow-400 mr-4">
                      <span className="text-sm font-medium text-white">Beginner</span>
                    </div>
                  </div>

                  {/* Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-3 flex-1">
                      <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                        <span className="line-clamp-2">AI in Daily Life</span>
                      </h4>
                      <p className="text-white text-sm leading-relaxed line-clamp-2">
                        Discuss how AI is already part of students' daily routines
                      </p>
                    </div>

                    {/* Activity Meta */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400">General</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                          discussion
                        </span>
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                          everyday
                        </span>
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

                {/* Sample Activity Card 2 - FUTURE Theme */}
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden h-80 flex flex-col hover:scale-105 hover:shadow-2xl"
                     style={{
                       clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                     }}>
                  {/* Theme-colored top section - FUTURE theme */}
                  <div 
                    className="absolute inset-0 bg-gradient-to-r from-orange-500 to-orange-600 opacity-20 group-hover:opacity-30 transition-opacity duration-300"
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
                  
                  {/* Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-orange-500" />
                      <span className="text-sm font-medium text-white">BE FUTURE</span>
                    </div>
                    <div className="flex items-center space-x-1 text-yellow-400 mr-4">
                      <span className="text-sm font-medium text-white">Advanced</span>
                    </div>
                  </div>

                  {/* Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-3 flex-1">
                      <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                        <span className="line-clamp-2">AI Career Pathways</span>
                      </h4>
                      <p className="text-white text-sm leading-relaxed line-clamp-2">
                        Explore future career opportunities in AI and technology fields
                      </p>
                    </div>

                    {/* Activity Meta */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400">Career Guidance</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                          career
                        </span>
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                          future
                        </span>
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

                {/* Sample Activity Card 3 - SAFE Theme */}
                <div className="bg-gray-800 p-6 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden h-80 flex flex-col hover:scale-105 hover:shadow-2xl"
                     style={{
                       clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                     }}>
                  {/* Theme-colored top section - SAFE theme */}
                  <div 
                    className="absolute inset-0 bg-gradient-to-r from-red-500 to-red-600 opacity-20 group-hover:opacity-30 transition-opacity duration-300"
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
                  
                  {/* Activity Header */}
                  <div className="relative z-10 flex items-start justify-between mb-4">
                    <div className="flex items-center space-x-2">
                      <div className="w-3 h-3 rounded-full bg-red-500" />
                      <span className="text-sm font-medium text-white">BE SAFE</span>
                    </div>
                    <div className="flex items-center space-x-1 text-yellow-400 mr-4">
                      <span className="text-sm font-medium text-white">Intermediate</span>
                    </div>
                  </div>

                  {/* Activity Content */}
                  <div className="relative z-10 flex-1 flex flex-col">
                    <div className="space-y-3 flex-1">
                      <h4 className="text-xl font-bold text-white group-hover:text-purple-300 transition-colors leading-tight h-16 flex items-start mt-4">
                        <span className="line-clamp-2">AI Privacy Workshop</span>
                      </h4>
                      <p className="text-white text-sm leading-relaxed line-clamp-2">
                        Learn about data privacy and safe AI usage practices
                      </p>
                    </div>

                    {/* Activity Meta */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400">Digital Safety</span>
                      </div>
                      
                      <div className="flex flex-wrap gap-1">
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                          privacy
                        </span>
                        <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                          security
                        </span>
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

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Card Dimensions</h4>
                    <p className="text-sm text-muted-foreground font-mono">Height: 320px (h-80) | Padding: 24px (p-6)</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Clip Path</h4>
                    <p className="text-sm text-muted-foreground font-mono">polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Background</h4>
                    <p className="text-sm text-muted-foreground font-mono">bg-gray-800 | border-gray-600 | hover:border-gray-500</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Theme Colors</h4>
                    <p className="text-sm text-muted-foreground font-mono">Safe: red-500/600 | Smart: blue-500/600 | Creative: green-500/600</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Typography</h4>
                    <p className="text-sm text-muted-foreground font-mono">Title: text-xl font-bold | Description: text-sm | Tags: text-xs</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Hover Effects</h4>
                    <p className="text-sm text-muted-foreground font-mono">scale-105 | shadow-2xl | opacity changes</p>
                  </div>
                </SectionCard>
              </div>
            </div>

            {/* Activities Section Cards */}
            <div className="mb-12">
              <h3 className="text-2xl font-bold mb-6">Activities Section Cards</h3>
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                {/* Sample Activity Type Card */}
                <div className="group relative overflow-hidden rounded-lg border bg-card p-6 transition-all duration-300 hover:shadow-lg hover:scale-105">
                  <div className="space-y-4">
                    <div className="flex items-start justify-between">
                      <div className="flex items-center space-x-3">
                        <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                          <div className="w-5 h-5">⏱️</div>
                        </div>
                        <div>
                          <h3 className="font-semibold text-lg">Quick Activities</h3>
                          <p className="text-sm text-muted-foreground">5-15 minutes</p>
                        </div>
                      </div>
                      
                      <span className="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                        Beginner
                      </span>
                    </div>
                    
                    <p className="text-muted-foreground text-sm leading-relaxed">
                      Short, focused activities perfect for quick classroom sessions or warm-ups.
                    </p>
                    
                    <div className="grid grid-cols-2 gap-4 text-sm">
                      <div className="flex items-center space-x-2 text-muted-foreground">
                        <div className="w-4 h-4">⏰</div>
                        <span>5-15 min</span>
                      </div>
                      <div className="flex items-center space-x-2 text-muted-foreground">
                        <div className="w-4 h-4">👥</div>
                        <span>Individual</span>
                      </div>
                    </div>
                    
                    <div className="pt-2">
                      <button className="w-full px-4 py-2 text-sm border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md transition-colors">
                        Start Activity →
                      </button>
                    </div>
                  </div>
                  
                  {/* Hover effect overlay */}
                  <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                </div>

                {/* Sample Activity Type Card 2 - Creative Activities */}
                <div className="group relative overflow-hidden rounded-lg border bg-card p-6 transition-all duration-300 hover:shadow-lg hover:scale-105">
                  <div className="space-y-4">
                    <div className="flex items-start justify-between">
                      <div className="flex items-center space-x-3">
                        <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                          <div className="w-5 h-5">🎨</div>
                        </div>
                        <div>
                          <h3 className="font-semibold text-lg">Creative Activities</h3>
                          <p className="text-sm text-muted-foreground">30-60 minutes</p>
                        </div>
                      </div>
                      
                      <span className="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                        Intermediate
                      </span>
                    </div>
                    
                    <p className="text-muted-foreground text-sm leading-relaxed">
                      Hands-on creative projects that encourage artistic expression and innovation with AI tools.
                    </p>
                    
                    <div className="grid grid-cols-2 gap-4 text-sm">
                      <div className="flex items-center space-x-2 text-muted-foreground">
                        <div className="w-4 h-4">⏰</div>
                        <span>30-60 min</span>
                      </div>
                      <div className="flex items-center space-x-2 text-muted-foreground">
                        <div className="w-4 h-4">👥</div>
                        <span>Small Groups</span>
                      </div>
                    </div>
                    
                    <div className="pt-2">
                      <button className="w-full px-4 py-2 text-sm border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md transition-colors">
                        Start Activity →
                      </button>
                    </div>
                  </div>
                  
                  {/* Hover effect overlay */}
                  <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                </div>

                {/* Sample Activity Type Card 3 - Responsible Activities */}
                <div className="group relative overflow-hidden rounded-lg border bg-card p-6 transition-all duration-300 hover:shadow-lg hover:scale-105">
                  <div className="space-y-4">
                    <div className="flex items-start justify-between">
                      <div className="flex items-center space-x-3">
                        <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                          <div className="w-5 h-5">⚖️</div>
                        </div>
                        <div>
                          <h3 className="font-semibold text-lg">Responsible Activities</h3>
                          <p className="text-sm text-muted-foreground">45-90 minutes</p>
                        </div>
                      </div>
                      
                      <span className="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                        Advanced
                      </span>
                    </div>
                    
                    <p className="text-muted-foreground text-sm leading-relaxed">
                      Deep-dive discussions and case studies exploring AI ethics, bias, and responsible development.
                    </p>
                    
                    <div className="grid grid-cols-2 gap-4 text-sm">
                      <div className="flex items-center space-x-2 text-muted-foreground">
                        <div className="w-4 h-4">⏰</div>
                        <span>45-90 min</span>
                      </div>
                      <div className="flex items-center space-x-2 text-muted-foreground">
                        <div className="w-4 h-4">👥</div>
                        <span>Class Discussion</span>
                      </div>
                    </div>
                    
                    <div className="pt-2">
                      <button className="w-full px-4 py-2 text-sm border border-input bg-background hover:bg-accent hover:text-accent-foreground rounded-md transition-colors">
                        Start Activity →
                      </button>
                    </div>
                  </div>
                  
                  {/* Hover effect overlay */}
                  <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-primary/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Card Structure</h4>
                    <p className="text-sm text-muted-foreground font-mono">rounded-lg border bg-card p-6</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Icon Container</h4>
                    <p className="text-sm text-muted-foreground font-mono">w-10 h-10 rounded-lg bg-primary/10</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Difficulty Badges</h4>
                    <p className="text-sm text-muted-foreground font-mono">Beginner: green | Intermediate: yellow | Advanced: red</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Typography</h4>
                    <p className="text-sm text-muted-foreground font-mono">Title: text-lg font-semibold | Description: text-sm</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Hover Effects</h4>
                    <p className="text-sm text-muted-foreground font-mono">hover:shadow-lg | hover:scale-105</p>
                  </div>
                </SectionCard>
                <SectionCard>
                  <div className="space-y-2">
                    <h4 className="font-semibold">Button Styling</h4>
                    <p className="text-sm text-muted-foreground font-mono">w-full | border | hover:bg-accent</p>
                  </div>
                </SectionCard>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Typography Scale */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-900">
          <Container>
            <SectionHeader
              title="Typography Scale"
              description="Complete typography system with live examples and measurements"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="space-y-8">
              {typographyScale.map((type, index) => (
                <SectionCard key={index}>
                  <div className="space-y-4">
                    <div className="flex items-center justify-between">
                      <h4 className="font-semibold text-lg">{type.name}</h4>
                      <Badge variant="outline">{type.size}</Badge>
                    </div>
                    <div className={`${type.size} ${type.weight} text-foreground`}>
                      {type.name === "Main Title (Stacked)" ? (
                        <div className="flex flex-col items-start">
                          <span>AI</span>
                          <span>AWARENESS</span>
                          <span>DAY</span>
                        </div>
                      ) : type.name === "Year Text" ? (
                        <div className="relative">
                          <span>2026</span>
                          <span className="text-xs text-muted-foreground ml-2">(positioned absolutely)</span>
                        </div>
                      ) : type.name === "Tagline" ? (
                        <span className="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                          {type.example}
                        </span>
                      ) : (
                        type.example
                      )}
                    </div>
                    <p className="text-sm text-muted-foreground">{type.usage}</p>
                  </div>
                </SectionCard>
              ))}
            </div>
          </Container>
        </SectionWrapper>

        {/* Technical Specifications */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-900">
          <Container>
            <SectionHeader
              title="⚙️ Technical Specifications"
              description="Complete technical details for implementation and customization"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {technicalSpecs.map((spec, index) => (
                <SectionCard key={index}>
                  <div className="space-y-2">
                    <h4 className="font-semibold">{spec.label}</h4>
                    <p className="text-sm text-muted-foreground">{spec.value}</p>
                  </div>
                </SectionCard>
              ))}
            </div>
          </Container>
        </SectionWrapper>

        {/* Download Assets */}
        <SectionWrapper className="bg-gradient-to-br from-purple-50 to-blue-50">
          <Container>
            <div className="text-center space-y-6">
              <SectionHeader
                title="Download Design Assets"
                description="Get access to all design elements, icons, and resources for your own AI awareness projects"
                titleColor="purple"
                align="center"
              />
              
              <div className="flex flex-wrap justify-center gap-4">
                <Button size="lg" className="gap-2">
                  <Download className="h-5 w-5" />
                  Download SVG Assets
                </Button>
                <Button variant="outline" size="lg" className="gap-2">
                  <Eye className="h-5 w-5" />
                  View Style Guide
                </Button>
              </div>
              
              <p className="text-sm text-muted-foreground max-w-2xl mx-auto">
                All design assets are available for educational use and can be customized to fit your specific AI awareness initiatives.
              </p>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
