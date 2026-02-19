import { SectionWrapper, Container, SectionHeader } from "@/components/ui"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Eye, Download, Code } from "lucide-react"
import Link from "next/link"

export default function AIDailyLifeCardPage() {
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
                title="🤖 AI Daily Life Card"
                description="Exact recreation of the Split + Image design from the reference image"
                titleColor="purple"
                align="center"
              />
            </div>
          </Container>
        </SectionWrapper>

        {/* Card Design Recreation */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-900 py-16">
          <Container>
            <div className="flex justify-center">
              <div className="w-full max-w-2xl">
                {/* Split + Image Card - Exact Recreation */}
                <div className="bg-gray-800 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96"
                     style={{
                       clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                     }}>
                  
                  {/* Left side - Theme section with image */}
                  <div className="flex-1 bg-gradient-to-br from-blue-500 to-purple-600 p-6 relative overflow-hidden">
                    {/* Background image overlay - AI network pattern */}
                    <div 
                      className="absolute inset-0 bg-cover bg-center opacity-20 group-hover:opacity-30 transition-opacity duration-300"
                      style={{
                        backgroundImage: "url('https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center')"
                      }}
                    />
                    
                    {/* Digital pattern overlay */}
                    <div className="absolute inset-0 bg-gradient-to-r from-blue-600/30 to-purple-600/30 opacity-40" />
                    
                    {/* Dark overlay for text readability */}
                    <div className="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors duration-300" />
                    
                    {/* Decorative Polygon Corner */}
                    <div 
                      className="absolute top-0 right-0 w-8 h-8 bg-white/20"
                      style={{
                        clipPath: "polygon(100% 0, 0 0, 100% 100%)"
                      }}
                    />
                    
                    {/* Theme header */}
                    <div className="relative z-10 flex items-center space-x-2 mb-4">
                      <div className="w-3 h-3 rounded-full bg-white" />
                      <span className="text-sm font-medium text-white">BE SMART</span>
                    </div>
                    
                    {/* AI pattern overlay */}
                    <div className="relative z-10 mb-4">
                      <div className="w-full h-20 bg-white/10 rounded-lg overflow-hidden backdrop-blur-sm border border-white/20">
                        <div className="w-full h-full bg-gradient-to-r from-blue-500/20 to-purple-500/20 flex items-center justify-center">
                          <div className="text-white/60 text-xs font-mono">AI NETWORK</div>
                        </div>
                      </div>
                    </div>
                    
                    {/* Title with difficulty badge */}
                    <div className="relative z-10 flex items-start justify-between">
                      <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
                        <span className="line-clamp-2">AI in Daily Life</span>
                      </h4>
                      <span className="px-3 py-1 text-xs font-medium rounded-full bg-yellow-400 text-black flex-shrink-0">
                        Intermediate
                      </span>
                    </div>
                  </div>
                  
                  {/* Right side - Content section */}
                  <div className="bg-gray-800 p-6 flex-1 flex flex-col">

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

                {/* Design Specifications */}
                <div className="mt-8 p-6 bg-white dark:bg-gray-800 rounded-lg border">
                  <h3 className="text-lg font-semibold mb-4">Design Specifications</h3>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                      <h4 className="font-medium mb-2">Layout</h4>
                      <ul className="space-y-1 text-muted-foreground">
                        <li>• Split layout (50/50)</li>
                        <li>• Left: Theme section with gradient</li>
                        <li>• Right: Content section (dark)</li>
                        <li>• Polygon corner cut (50px)</li>
                      </ul>
                    </div>
                    <div>
                      <h4 className="font-medium mb-2">Typography</h4>
                      <ul className="space-y-1 text-muted-foreground">
                        <li>• Title: 2xl, bold, white</li>
                        <li>• Description: base (16px), white</li>
                        <li>• Tags: xs, gray-400</li>
                        <li>• View Details: base (16px), purple</li>
                      </ul>
                    </div>
                    <div>
                      <h4 className="font-medium mb-2">Colors</h4>
                      <ul className="space-y-1 text-muted-foreground">
                        <li>• Theme: Blue-purple gradient</li>
                        <li>• Content: Dark gray (gray-800)</li>
                        <li>• Text: White, gray-400</li>
                        <li>• Accent: Purple-400</li>
                      </ul>
                    </div>
                    <div>
                      <h4 className="font-medium mb-2">Spacing</h4>
                      <ul className="space-y-1 text-muted-foreground">
                        <li>• Card height: 384px (h-96)</li>
                        <li>• Padding: 24px (p-6)</li>
                        <li>• Margins: 16px (mt-4)</li>
                        <li>• View Details: 32px bottom</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
