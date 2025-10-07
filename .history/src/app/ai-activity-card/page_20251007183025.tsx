import { SectionWrapper, Container, SectionHeader, SectionCard } from "@/components/ui"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Download, Code, Eye } from "lucide-react"
import Link from "next/link"

export default function AIActivityCardPage() {
  return (
    <div className="min-h-screen bg-background">
      <main>
        {/* Hero Section */}
        <SectionWrapper className="bg-gradient-to-br from-blue-50 to-purple-50 pt-20 pb-16">
          <Container>
            <div className="text-center space-y-6">
              <div className="flex items-center justify-center gap-2 mb-4">
                <Link href="/design-demo">
                  <Button variant="ghost" size="sm" className="gap-2">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Demo
                  </Button>
                </Link>
              </div>
              
              <SectionHeader
                title="🤖 AI Activity Card Design"
                description="Interactive card design for AI awareness activities with digital circuit aesthetics"
                titleColor="purple"
                align="center"
              />
              
              <div className="flex flex-wrap justify-center gap-4">
                <Button size="lg" className="gap-2">
                  <Download className="h-5 w-5" />
                  Download Assets
                </Button>
                <Button variant="outline" size="lg" className="gap-2">
                  <Code className="h-5 w-5" />
                  View Code
                </Button>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Design Showcase */}
        <SectionWrapper className="bg-card">
          <Container>
            <SectionHeader
              title="🎨 Live Design Preview"
              description="Interactive AI activity card with digital circuit aesthetics"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="flex justify-center">
              <div className="w-full max-w-md">
                {/* AI Activity Card */}
                <div className="bg-gradient-to-br from-blue-900 to-purple-900 p-0 border border-blue-700 hover:border-blue-600 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96"
                     style={{
                       clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                     }}>
                  
                  {/* Header Section with Digital Pattern */}
                  <div className="flex-1 bg-gradient-to-br from-blue-800 to-purple-800 p-6 relative overflow-hidden">
                    {/* Digital Circuit Background */}
                    <div className="absolute inset-0 opacity-30">
                      <svg className="w-full h-full" viewBox="0 0 400 200" fill="none">
                        {/* Circuit lines */}
                        <path d="M50 50 L350 50 L350 80 L50 80 Z" stroke="white" strokeWidth="1" fill="none" opacity="0.3"/>
                        <path d="M50 120 L350 120 L350 150 L50 150 Z" stroke="white" strokeWidth="1" fill="none" opacity="0.3"/>
                        <path d="M100 20 L100 180" stroke="white" strokeWidth="1" opacity="0.2"/>
                        <path d="M200 20 L200 180" stroke="white" strokeWidth="1" opacity="0.2"/>
                        <path d="M300 20 L300 180" stroke="white" strokeWidth="1" opacity="0.2"/>
                        
                        {/* Circuit dots */}
                        <circle cx="100" cy="50" r="2" fill="white" opacity="0.6"/>
                        <circle cx="200" cy="50" r="2" fill="white" opacity="0.6"/>
                        <circle cx="300" cy="50" r="2" fill="white" opacity="0.6"/>
                        <circle cx="100" cy="120" r="2" fill="white" opacity="0.6"/>
                        <circle cx="200" cy="120" r="2" fill="white" opacity="0.6"/>
                        <circle cx="300" cy="120" r="2" fill="white" opacity="0.6"/>
                      </svg>
                    </div>
                    
                    {/* AI Banner Overlay */}
                    <div className="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20 flex items-center justify-center">
                      <div className="text-6xl font-bold text-white/40 tracking-widest">AI</div>
                    </div>
                    
                    {/* Theme Header */}
                    <div className="relative z-10 flex items-center space-x-2 mb-4">
                      <div className="w-3 h-3 rounded-full bg-white" />
                      <span className="text-sm font-medium text-white">BE SMART</span>
                    </div>
                    
                    {/* Title with Difficulty Badge */}
                    <div className="relative z-10 flex items-start justify-between">
                      <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
                        <span className="line-clamp-2">AI in Daily Life</span>
                      </h4>
                      <span className="px-3 py-1 text-xs font-medium rounded-full bg-yellow-400 text-black flex-shrink-0">
                        Intermediate
                      </span>
                    </div>
                  </div>
                  
                  {/* Content Section */}
                  <div className="bg-gray-900 p-6 flex-1 flex flex-col">
                    {/* Description */}
                    <div className="flex-1">
                      <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
                        <span className="line-clamp-3">Discuss how AI is already part of students' daily routines</span>
                      </p>
                    </div>

                    {/* Tags */}
                    <div className="mt-4 space-y-2">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs text-gray-400">Tags</span>
                        <div className="flex flex-wrap gap-1">
                          <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                            discussion
                          </span>
                          <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                            everyday
                          </span>
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

        {/* Design Specifications */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-900">
          <Container>
            <SectionHeader
              title="📋 Design Specifications"
              description="Complete technical specifications for the AI activity card design"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              {/* Visual Specs */}
              <SectionCard>
                <h3 className="text-lg font-semibold mb-4">Visual Specifications</h3>
                <div className="space-y-3 text-sm">
                  <div>
                    <span className="font-medium">Shape:</span> Irregular polygon with cut-out corners
                  </div>
                  <div>
                    <span className="font-medium">Dimensions:</span> 400px × 384px (h-96)
                  </div>
                  <div>
                    <span className="font-medium">Background:</span> Deep blue to purple gradient
                  </div>
                  <div>
                    <span className="font-medium">Pattern:</span> Digital circuit board aesthetic
                  </div>
                  <div>
                    <span className="font-medium">Colors:</span> Blue-900, Purple-900, Gray-900
                  </div>
                </div>
              </SectionCard>

              {/* Typography Specs */}
              <SectionCard>
                <h3 className="text-lg font-semibold mb-4">Typography</h3>
                <div className="space-y-3 text-sm">
                  <div>
                    <span className="font-medium">Title:</span> text-2xl, font-bold, white
                  </div>
                  <div>
                    <span className="font-medium">Description:</span> text-base, white, 3-line clamp
                  </div>
                  <div>
                    <span className="font-medium">CTA:</span> text-base, purple-400, font-medium
                  </div>
                  <div>
                    <span className="font-medium">Tags:</span> text-xs, gray-300
                  </div>
                  <div>
                    <span className="font-medium">Badge:</span> text-xs, black on yellow-400
                  </div>
                </div>
              </SectionCard>

              {/* Layout Specs */}
              <SectionCard>
                <h3 className="text-lg font-semibold mb-4">Layout Structure</h3>
                <div className="space-y-3 text-sm">
                  <div>
                    <span className="font-medium">Header:</span> Theme indicator + digital pattern
                  </div>
                  <div>
                    <span className="font-medium">Title Section:</span> Title + difficulty badge
                  </div>
                  <div>
                    <span className="font-medium">Content:</span> Description + tags + CTA
                  </div>
                  <div>
                    <span className="font-medium">Spacing:</span> mt-4, mb-8 for CTA
                  </div>
                  <div>
                    <span className="font-medium">Hover:</span> scale-105, shadow-2xl
                  </div>
                </div>
              </SectionCard>

              {/* Interactive Specs */}
              <SectionCard>
                <h3 className="text-lg font-semibold mb-4">Interactive Elements</h3>
                <div className="space-y-3 text-sm">
                  <div>
                    <span className="font-medium">Hover Effects:</span> Scale, shadow, color transitions
                  </div>
                  <div>
                    <span className="font-medium">Transitions:</span> duration-300 for smooth animations
                  </div>
                  <div>
                    <span className="font-medium">Cursor:</span> pointer for clickable elements
                  </div>
                  <div>
                    <span className="font-medium">Responsive:</span> max-w-md container
                  </div>
                  <div>
                    <span className="font-medium">Accessibility:</span> Proper contrast ratios
                  </div>
                </div>
              </SectionCard>
            </div>
          </Container>
        </SectionWrapper>

        {/* Code Implementation */}
        <SectionWrapper className="bg-card">
          <Container>
            <SectionHeader
              title="💻 Implementation Code"
              description="Ready-to-use code for implementing this design"
              titleColor="purple"
              className="mb-8"
            />
            
            <SectionCard>
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <h4 className="font-semibold">React Component Code</h4>
                  <Button size="sm" className="gap-2">
                    <Code className="h-4 w-4" />
                    Copy Code
                  </Button>
                </div>
                
                <div className="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                  <pre className="text-sm">
{`<div className="bg-gradient-to-br from-blue-900 to-purple-900 p-0 border border-blue-700 hover:border-blue-600 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96"
     style={{
       clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
     }}>
  
  {/* Header Section */}
  <div className="flex-1 bg-gradient-to-br from-blue-800 to-purple-800 p-6 relative overflow-hidden">
    {/* Digital Pattern Background */}
    <div className="absolute inset-0 opacity-30">
      {/* SVG Circuit Pattern */}
    </div>
    
    {/* AI Banner */}
    <div className="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20 flex items-center justify-center">
      <div className="text-6xl font-bold text-white/40 tracking-widest">AI</div>
    </div>
    
    {/* Theme Header */}
    <div className="relative z-10 flex items-center space-x-2 mb-4">
      <div className="w-3 h-3 rounded-full bg-white" />
      <span className="text-sm font-medium text-white">BE SMART</span>
    </div>
    
    {/* Title with Badge */}
    <div className="relative z-10 flex items-start justify-between">
      <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
        AI in Daily Life
      </h4>
      <span className="px-3 py-1 text-xs font-medium rounded-full bg-yellow-400 text-black">
        Intermediate
      </span>
    </div>
  </div>
  
  {/* Content Section */}
  <div className="bg-gray-900 p-6 flex-1 flex flex-col">
    {/* Description */}
    <div className="flex-1">
      <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
        Discuss how AI is already part of students' daily routines
      </p>
    </div>
    
    {/* Tags */}
    <div className="mt-4 space-y-2">
      <div className="flex items-center space-x-2">
        <span className="text-xs text-gray-400">Tags</span>
        <div className="flex flex-wrap gap-1">
          <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">discussion</span>
          <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">everyday</span>
        </div>
      </div>
    </div>
    
    {/* CTA */}
    <div className="mt-4 mb-8">
      <span className="text-purple-400 text-base font-medium group-hover:text-purple-300 transition-colors">
        View Details →
      </span>
    </div>
  </div>
</div>`}
                  </pre>
                </div>
              </div>
            </SectionCard>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
