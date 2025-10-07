"use client"

import { useState } from "react"
import { Navigation } from "@/components/navigation"
import { SectionWrapper, Container } from "@/components/ui"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Palette, Eye, Download } from "lucide-react"
import Link from "next/link"
import DisplayBoardMockup from "@/app/components/sections/DisplayBoardSection/DisplayBoardMockup"

// Display Board Design Variations
const boardVariations = [
  {
    id: "classic",
    name: "Classic Design",
    description: "Traditional school display board with clean layout",
    className: "bg-white border-4 border-gray-800",
    headerStyle: "bg-gradient-to-r from-blue-600 to-blue-500",
    panelStyle: "border-2",
    cornerStyle: "polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))"
  },
  {
    id: "modern",
    name: "Modern Design",
    description: "Sleek contemporary design with rounded corners",
    className: "bg-gray-50 border-4 border-gray-300 rounded-2xl",
    headerStyle: "bg-gradient-to-r from-purple-600 to-pink-500 rounded-t-2xl",
    panelStyle: "border-2 rounded-xl",
    cornerStyle: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
  },
  {
    id: "minimalist",
    name: "Minimalist Design",
    description: "Clean, simple design with subtle colors",
    className: "bg-white border-2 border-gray-200",
    headerStyle: "bg-gradient-to-r from-gray-700 to-gray-600",
    panelStyle: "border border-gray-300",
    cornerStyle: "polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px))"
  },
  {
    id: "colorful",
    name: "Colorful Design",
    description: "Vibrant design with bright colors and bold styling",
    className: "bg-gradient-to-br from-yellow-50 to-orange-50 border-4 border-orange-400",
    headerStyle: "bg-gradient-to-r from-orange-500 to-red-500",
    panelStyle: "border-3 border-orange-400",
    cornerStyle: "polygon(0 0, calc(100% - 25px) 0, 100% 25px, 100% 100%, 25px 100%, 0 calc(100% - 25px))"
  },
  {
    id: "dark",
    name: "Dark Theme",
    description: "Dark mode design with neon accents",
    className: "bg-gray-900 border-4 border-cyan-400",
    headerStyle: "bg-gradient-to-r from-cyan-500 to-blue-500",
    panelStyle: "border-2 border-cyan-400",
    cornerStyle: "polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))"
  },
  {
    id: "academic",
    name: "Academic Style",
    description: "Professional academic design with formal styling",
    className: "bg-white border-4 border-blue-800",
    headerStyle: "bg-gradient-to-r from-blue-800 to-blue-700",
    panelStyle: "border-2 border-blue-600",
    cornerStyle: "polygon(0 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 12px 100%, 0 calc(100% - 12px))"
  }
]

// Layout Variations
const layoutVariations = [
  {
    id: "grid-3x2",
    name: "3x2 Grid",
    description: "Six panels in a 3x2 grid layout",
    className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4"
  },
  {
    id: "grid-2x3",
    name: "2x3 Grid", 
    description: "Six panels in a 2x3 grid layout",
    className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4"
  },
  {
    id: "linear",
    name: "Linear Layout",
    description: "Panels arranged in a single row",
    className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4"
  },
  {
    id: "hexagon",
    name: "Hexagon Layout",
    description: "Panels arranged in a hexagon pattern",
    className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4"
  }
]

// Size Variations
const sizeVariations = [
  {
    id: "small",
    name: "Small Board",
    description: "Compact display for smaller spaces",
    className: "max-w-4xl",
    scale: "scale-75"
  },
  {
    id: "medium",
    name: "Medium Board",
    description: "Standard size for most classrooms",
    className: "max-w-6xl",
    scale: "scale-100"
  },
  {
    id: "large",
    name: "Large Board",
    description: "Large display for hallways and common areas",
    className: "max-w-8xl",
    scale: "scale-125"
  }
]

export default function DisplayBoardDemoPage() {
  const [selectedVariation, setSelectedVariation] = useState("classic")
  const [selectedLayout, setSelectedLayout] = useState("grid-3x2")
  const [selectedSize, setSelectedSize] = useState("medium")

  const currentVariation = boardVariations.find(v => v.id === selectedVariation)
  const currentLayout = layoutVariations.find(l => l.id === selectedLayout)
  const currentSize = sizeVariations.find(s => s.id === selectedSize)

  return (
    <div className="flex min-h-screen flex-col">
      <Navigation />
      
      <main className="flex-1">
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
              <h1 className="text-4xl font-bold text-black dark:text-white">
                Display Board Design Demo
              </h1>
              <p className="text-lg text-black dark:text-white">
                Interactive playground for testing different display board designs and layouts
              </p>
            </div>
          </Container>
        </SectionWrapper>

        {/* Controls Panel */}
        <SectionWrapper className="bg-card">
          <Container>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
              
              {/* Design Variations */}
              <div className="space-y-4">
                <h3 className="text-lg font-semibold">Design Style</h3>
                <div className="grid grid-cols-2 gap-2">
                  {boardVariations.map((variation) => (
                    <Button
                      key={variation.id}
                      variant={selectedVariation === variation.id ? "default" : "outline"}
                      size="sm"
                      onClick={() => setSelectedVariation(variation.id)}
                      className="text-xs"
                    >
                      {variation.name}
                    </Button>
                  ))}
                </div>
                <p className="text-sm text-muted-foreground">
                  {currentVariation?.description}
                </p>
              </div>

              {/* Layout Options */}
              <div className="space-y-4">
                <h3 className="text-lg font-semibold">Layout Style</h3>
                <div className="space-y-2">
                  {layoutVariations.map((layout) => (
                    <Button
                      key={layout.id}
                      variant={selectedLayout === layout.id ? "default" : "outline"}
                      size="sm"
                      onClick={() => setSelectedLayout(layout.id)}
                      className="w-full justify-start"
                    >
                      {layout.name}
                    </Button>
                  ))}
                </div>
                <p className="text-sm text-muted-foreground">
                  {currentLayout?.description}
                </p>
              </div>

              {/* Size Options */}
              <div className="space-y-4">
                <h3 className="text-lg font-semibold">Board Size</h3>
                <div className="space-y-2">
                  {sizeVariations.map((size) => (
                    <Button
                      key={size.id}
                      variant={selectedSize === size.id ? "default" : "outline"}
                      size="sm"
                      onClick={() => setSelectedSize(size.id)}
                      className="w-full justify-start"
                    >
                      {size.name}
                    </Button>
                  ))}
                </div>
                <p className="text-sm text-muted-foreground">
                  {currentSize?.description}
                </p>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Live Preview */}
        <SectionWrapper className="bg-muted/30">
          <Container>
            <div className="text-center mb-8">
              <h2 className="text-2xl font-bold mb-2">🎨 Live Preview</h2>
              <p className="text-muted-foreground">
                {currentVariation?.name} • {currentLayout?.name} • {currentSize?.name}
              </p>
            </div>

            <div className="flex justify-center">
              <div className={`${currentSize?.className} mx-auto`}>
                <div className={`${currentSize?.scale} transform transition-all duration-300`}>
                  <DisplayBoardMockup />
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Design Showcase */}
        <SectionWrapper className="bg-card">
          <Container>
            <div className="text-center mb-8">
              <h2 className="text-2xl font-bold mb-2">Design Variations</h2>
              <p className="text-muted-foreground">
                Explore different design styles and layouts
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {boardVariations.map((variation) => (
                <div key={variation.id} className="space-y-4">
                  <div className={`${variation.className} p-4 rounded-lg shadow-lg`}>
                    <div className={`${variation.headerStyle} text-white p-4 rounded-lg mb-4`}>
                      <h3 className="text-lg font-bold">AI AWARENESS DAY 2026</h3>
                      <p className="text-sm opacity-90">Know it, Question it, Use it Wisely</p>
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                      <div className={`${variation.panelStyle} border-red-500 p-2 rounded`}>
                        <div className="bg-red-500 text-white p-2 rounded text-center">
                          <span className="text-xs font-bold">BE SAFE</span>
                        </div>
                      </div>
                      <div className={`${variation.panelStyle} border-blue-500 p-2 rounded`}>
                        <div className="bg-blue-500 text-white p-2 rounded text-center">
                          <span className="text-xs font-bold">BE SMART</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div className="text-center">
                    <h4 className="font-semibold">{variation.name}</h4>
                    <p className="text-sm text-muted-foreground">{variation.description}</p>
                  </div>
                </div>
              ))}
            </div>
          </Container>
        </SectionWrapper>

        {/* Quick Actions */}
        <SectionWrapper className="bg-muted/30">
          <Container>
            <div className="text-center space-y-6">
              <h2 className="text-2xl font-bold">Ready to Create Your Display Board?</h2>
              <p className="text-muted-foreground max-w-2xl mx-auto">
                Download the design assets and templates to create your own AI Awareness Day display board.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Button size="lg" className="gap-2">
                  <Download className="h-5 w-5" />
                  Download Design Assets
                </Button>
                <Button variant="outline" size="lg" className="gap-2">
                  <Eye className="h-5 w-5" />
                  View Style Guide
                </Button>
              </div>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
