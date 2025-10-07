"use client"

import { useState } from "react"
import { SectionWrapper, Container, SectionHeader, SectionCard } from "@/components/ui"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Palette, Eye, Download, Code, Ruler, Settings, RotateCcw, Copy, Check } from "lucide-react"
import Link from "next/link"

const cardThemes = [
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
  },
  {
    name: "BE RESPONSIBLE",
    theme: "BE RESPONSIBLE",
    gradient: "from-blue-500 to-blue-600", 
    dotColor: "bg-blue-500",
    color: "blue"
  },
  {
    name: "BE SMART",
    theme: "BE SMART",
    gradient: "from-purple-500 to-pink-500",
    dotColor: "bg-purple-500",
    color: "purple"
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

export default function SplitImageDesignPage() {
  const [selectedTheme, setSelectedTheme] = useState(4) // BE SMART
  const [cardSize, setCardSize] = useState(1) // Medium
  const [difficultyLevel, setDifficultyLevel] = useState(0) // Beginner
  const [customTitle, setCustomTitle] = useState("AI in Daily Life")
  const [customDescription, setCustomDescription] = useState("Discuss how AI is already part of students' daily routines")
  const [customTags, setCustomTags] = useState(["discussion", "everyday"])
  const [showCornerCut, setShowCornerCut] = useState(true)

  const generateCode = () => {
    return `// Split + Image Activity Card
<div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex hover:scale-105 hover:shadow-2xl"
     style={{
       clipPath: showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
     }}>
  
  {/* Left side - Theme section with image */}
  <div className="w-1/2 bg-gradient-to-br ${cardThemes[selectedTheme].gradient} p-6 relative overflow-hidden flex flex-col">
    {/* Background image overlay */}
    <div 
      className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
      style={{
        backgroundImage: "url('your-image-url')"
      }}
    />
    
    {/* Dark overlay for text readability */}
    <div className="absolute inset-0 bg-black/30 group-hover:bg-black/20 transition-colors duration-300" />
    
    {/* Theme header */}
    <div className="relative z-10 flex items-center space-x-2 mb-4">
      <div className="w-3 h-3 rounded-full bg-white" />
      <span className="text-sm font-medium text-white">${cardThemes[selectedTheme].theme}</span>
    </div>
    
    {/* Image content in theme section */}
    <div className="relative z-10 mb-4">
      <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
        <img 
          src="your-image-url" 
          alt="AI Activity" 
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
      </div>
    </div>
    
    {/* Title in theme section with difficulty badge */}
    <div className="relative z-10 flex items-start justify-between">
      <h4 className="${cardSizes[cardSize].titleSize} font-bold text-white leading-tight flex-1 mr-4">
        <span className="line-clamp-2">${customTitle}</span>
      </h4>
      <span className="px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[difficultyLevel].className} flex-shrink-0">
        ${difficultyLevels[difficultyLevel].name}
      </span>
    </div>
  </div>
  
  {/* Right side - Content section */}
  <div className="w-1/2 bg-gray-800 p-6 flex flex-col">
    {/* Description */}
    <div className="flex-1">
      <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
        <span className="line-clamp-3">${customDescription}</span>
      </p>
    </div>

    {/* Tags */}
    <div className="mt-4 space-y-2">
      <div className="flex items-center space-x-2">
        <span className="text-xs text-gray-400">Tags</span>
        <div className="flex flex-wrap gap-1">
          ${customTags.map(tag => `<span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">${tag}</span>`).join('\n          ')}
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
                    Back to Demo
                  </Button>
                </Link>
                <Link href="/design-concept">
                  <Button variant="ghost" size="sm" className="gap-2">
                    <Palette className="h-4 w-4" />
                    Design Concept
                  </Button>
                </Link>
              </div>
              
              <SectionHeader
                title="🎨 Split + Image Design"
                description="A modern split-layout card combining visual appeal with functional content"
                titleColor="purple"
                align="center"
              />
              
              <div className="flex flex-wrap justify-center gap-4">
                <Button size="lg" className="gap-2">
                  <Code className="h-5 w-5" />
                  View Code
                </Button>
                <Button variant="outline" size="lg" className="gap-2">
                  <Download className="h-5 w-5" />
                  Download Assets
                </Button>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Controls Panel */}
        <SectionWrapper className="bg-card border-b">
          <Container>
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
              {/* Theme Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <h3 className="font-semibold flex items-center gap-2">
                    <Palette className="h-4 w-4" />
                    Theme
                  </h3>
                  <div className="space-y-2">
                    {cardThemes.map((theme, index) => (
                      <label key={index} className="flex items-center space-x-2 cursor-pointer">
                        <input
                          type="radio"
                          name="theme"
                          checked={selectedTheme === index}
                          onChange={() => setSelectedTheme(index)}
                          className="w-4 h-4"
                        />
                        <span className="text-sm">{theme.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
              </SectionCard>

              {/* Size Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <h3 className="font-semibold flex items-center gap-2">
                    <Ruler className="h-4 w-4" />
                    Size
                  </h3>
                  <div className="space-y-2">
                    {cardSizes.map((size, index) => (
                      <label key={index} className="flex items-center space-x-2 cursor-pointer">
                        <input
                          type="radio"
                          name="size"
                          checked={cardSize === index}
                          onChange={() => setCardSize(index)}
                          className="w-4 h-4"
                        />
                        <span className="text-sm">{size.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
              </SectionCard>

              {/* Difficulty Controls */}
              <SectionCard>
                <div className="space-y-4">
                  <h3 className="font-semibold flex items-center gap-2">
                    <Settings className="h-4 w-4" />
                    Difficulty
                  </h3>
                  <div className="space-y-2">
                    {difficultyLevels.map((level, index) => (
                      <label key={index} className="flex items-center space-x-2 cursor-pointer">
                        <input
                          type="radio"
                          name="difficulty"
                          checked={difficultyLevel === index}
                          onChange={() => setDifficultyLevel(index)}
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
                  <h3 className="font-semibold flex items-center gap-2">
                    <Code className="h-4 w-4" />
                    Content
                  </h3>
                  <div className="space-y-3">
                    <div>
                      <label className="text-sm font-medium">Title</label>
                      <input
                        type="text"
                        value={customTitle}
                        onChange={(e) => setCustomTitle(e.target.value)}
                        className="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                      />
                    </div>
                    <div>
                      <label className="text-sm font-medium">Description</label>
                      <textarea
                        value={customDescription}
                        onChange={(e) => setCustomDescription(e.target.value)}
                        className="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm h-20 resize-none"
                      />
                    </div>
                    <div>
                      <label className="text-sm font-medium">Tags (comma separated)</label>
                      <input
                        type="text"
                        value={customTags.join(", ")}
                        onChange={(e) => setCustomTags(e.target.value.split(", ").filter(tag => tag.trim()))}
                        className="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                      />
                    </div>
                    <div className="flex items-center space-x-2">
                      <input
                        type="checkbox"
                        id="cornerCut"
                        checked={showCornerCut}
                        onChange={(e) => setShowCornerCut(e.target.checked)}
                        className="w-4 h-4"
                      />
                      <label htmlFor="cornerCut" className="text-sm">Show Corner Cut</label>
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
              description="See your split design changes in real-time"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="flex justify-center">
              <div className="w-full max-w-4xl">
                {/* Split + Image Card */}
                <div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex hover:scale-105 hover:shadow-2xl"
                     style={{
                       clipPath: showCornerCut ? "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))" : "none"
                     }}>
                  
                  {/* Left side - Theme section with image */}
                  <div 
                    className={`w-1/2 bg-gradient-to-br ${cardThemes[selectedTheme].gradient} p-6 relative overflow-hidden flex flex-col`}
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
                    {showCornerCut && (
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
                      <span className="text-sm font-medium text-white">{cardThemes[selectedTheme].theme}</span>
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
                      <h4 className={`${cardSizes[cardSize].titleSize} font-bold text-white leading-tight flex-1 mr-4`}>
                        <span className="line-clamp-2">{customTitle}</span>
                      </h4>
                      <span className={`px-2 py-1 text-xs font-medium rounded-full ${difficultyLevels[difficultyLevel].className} flex-shrink-0`}>
                        {difficultyLevels[difficultyLevel].name}
                      </span>
                    </div>
                  </div>
                  
                  {/* Right side - Content section */}
                  <div className="w-1/2 bg-gray-800 p-6 flex flex-col">

                    {/* Description */}
                    <div className="flex-1">
                      <p className={`text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start`}>
                        <span className="line-clamp-3">{customDescription}</span>
                      </p>
                    </div>

                    {/* Tags */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400">Tags</span>
                        <div className="flex flex-wrap gap-1">
                          {customTags.map((tag, index) => (
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
              description="Copy the generated code for your split design"
              titleColor="purple"
              className="mb-8"
            />
            
            <SectionCard>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <h4 className="font-semibold">JSX Code</h4>
                  <Button size="sm" className="gap-2">
                    <Copy className="h-4 w-4" />
                    Copy Code
                  </Button>
                </div>
                <pre className="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm">
                  <code>{generateCode()}</code>
                </pre>
              </div>
            </SectionCard>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
