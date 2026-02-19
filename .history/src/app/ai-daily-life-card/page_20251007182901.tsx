"use client"

import { SectionWrapper, Container, SectionHeader } from "@/components/ui"
import { Button } from "@/components/ui/button"
import { ArrowLeft, Download, Code, Copy, Check } from "lucide-react"
import Link from "next/link"
import { useState } from "react"

export default function AIDailyLifeCardPage() {
  const [copied, setCopied] = useState(false)

  const copyCode = () => {
    const code = `<!-- AI Daily Life Activity Card -->
<div className="bg-gradient-to-br from-blue-900 to-purple-900 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96"
     style={{
       clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
     }}>
  
  {/* Header Section with Digital Pattern */}
  <div className="flex-1 bg-gradient-to-br from-blue-800 to-blue-900 p-6 relative overflow-hidden">
    {/* Digital Pattern Background */}
    <div className="absolute inset-0 opacity-30">
      <div className="absolute inset-0 bg-gradient-to-r from-blue-400/20 to-purple-400/20" />
      <div className="absolute top-4 left-4 w-32 h-8 bg-blue-300/10 rounded-sm" />
      <div className="absolute top-8 right-8 w-16 h-4 bg-blue-300/10 rounded-sm" />
      <div className="absolute bottom-4 left-8 w-24 h-6 bg-purple-300/10 rounded-sm" />
      <div className="absolute bottom-8 right-4 w-20 h-4 bg-blue-300/10 rounded-sm" />
    </div>
    
    {/* Circuit Board Pattern Overlay */}
    <div className="absolute inset-0 bg-gradient-to-r from-transparent via-blue-300/10 to-transparent opacity-40" 
         style={{
           backgroundImage: "radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.3) 1px, transparent 1px), radial-gradient(circle at 80% 20%, rgba(147, 51, 234, 0.3) 1px, transparent 1px), radial-gradient(circle at 40% 80%, rgba(59, 130, 246, 0.3) 1px, transparent 1px)",
           backgroundSize: "20px 20px, 30px 30px, 25px 25px"
         }} />
    
    {/* AI Pattern Banner */}
    <div className="absolute top-0 left-0 right-0 h-12 bg-gradient-to-r from-blue-600/40 to-purple-600/40 flex items-center justify-center">
      <div className="text-2xl font-bold text-white/60 tracking-widest">AI</div>
      <div className="absolute inset-0 flex items-center justify-center">
        <div className="w-full h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />
      </div>
    </div>
    
    {/* Theme Header */}
    <div className="relative z-10 flex items-center space-x-2 mb-4 mt-16">
      <div className="w-3 h-3 rounded-full bg-white" />
      <span className="text-sm font-medium text-white">BE SMART</span>
    </div>
    
    {/* Title with Difficulty Badge */}
    <div className="relative z-10 flex items-start justify-between">
      <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
        <span className="line-clamp-2">AI in Daily Life</span>
      </h4>
      <span className="px-3 py-1 text-xs font-medium rounded-full bg-yellow-400 text-yellow-900 flex-shrink-0">
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
</div>`

    navigator.clipboard.writeText(code)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
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
              </div>
              
              <SectionHeader
                title="🤖 AI Daily Life Activity Card"
                description="Interactive design showcase of the AI Daily Life activity card with digital patterns and modern layout"
                titleColor="purple"
                align="center"
              />
            </div>
          </Container>
        </SectionWrapper>

        {/* Card Showcase */}
        <SectionWrapper className="bg-card">
          <Container>
            <div className="max-w-4xl mx-auto">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                {/* Live Card Preview */}
                <div className="space-y-6">
                  <div>
                    <h3 className="text-xl font-semibold mb-4">Live Preview</h3>
                    <div className="bg-gray-100 dark:bg-gray-800 p-8 rounded-lg">
                      <div className="bg-gradient-to-br from-blue-900 to-purple-900 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl h-96"
                           style={{
                             clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                           }}>
                        
                        {/* Header Section with Digital Pattern */}
                        <div className="flex-1 bg-gradient-to-br from-blue-800 to-blue-900 p-6 relative overflow-hidden">
                          {/* Digital Pattern Background */}
                          <div className="absolute inset-0 opacity-30">
                            <div className="absolute inset-0 bg-gradient-to-r from-blue-400/20 to-purple-400/20" />
                            <div className="absolute top-4 left-4 w-32 h-8 bg-blue-300/10 rounded-sm" />
                            <div className="absolute top-8 right-8 w-16 h-4 bg-blue-300/10 rounded-sm" />
                            <div className="absolute bottom-4 left-8 w-24 h-6 bg-purple-300/10 rounded-sm" />
                            <div className="absolute bottom-8 right-4 w-20 h-4 bg-blue-300/10 rounded-sm" />
                          </div>
                          
                          {/* Circuit Board Pattern Overlay */}
                          <div className="absolute inset-0 bg-gradient-to-r from-transparent via-blue-300/10 to-transparent opacity-40" 
                               style={{
                                 backgroundImage: "radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.3) 1px, transparent 1px), radial-gradient(circle at 80% 20%, rgba(147, 51, 234, 0.3) 1px, transparent 1px), radial-gradient(circle at 40% 80%, rgba(59, 130, 246, 0.3) 1px, transparent 1px)",
                                 backgroundSize: "20px 20px, 30px 30px, 25px 25px"
                               }} />
                          
                          {/* AI Pattern Banner */}
                          <div className="absolute top-0 left-0 right-0 h-12 bg-gradient-to-r from-blue-600/40 to-purple-600/40 flex items-center justify-center">
                            <div className="text-2xl font-bold text-white/60 tracking-widest">AI</div>
                            <div className="absolute inset-0 flex items-center justify-center">
                              <div className="w-full h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />
                            </div>
                          </div>
                          
                          {/* Theme Header */}
                          <div className="relative z-10 flex items-center space-x-2 mb-4 mt-16">
                            <div className="w-3 h-3 rounded-full bg-white" />
                            <span className="text-sm font-medium text-white">BE SMART</span>
                          </div>
                          
                          {/* Title with Difficulty Badge */}
                          <div className="relative z-10 flex items-start justify-between">
                            <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
                              <span className="line-clamp-2">AI in Daily Life</span>
                            </h4>
                            <span className="px-3 py-1 text-xs font-medium rounded-full bg-yellow-400 text-yellow-900 flex-shrink-0">
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
                </div>

                {/* Design Specifications */}
                <div className="space-y-6">
                  <div>
                    <h3 className="text-xl font-semibold mb-4">Design Specifications</h3>
                    <div className="space-y-4">
                      <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border">
                        <h4 className="font-semibold text-sm text-gray-600 dark:text-gray-400 mb-2">CARD STRUCTURE</h4>
                        <ul className="text-sm space-y-1">
                          <li>• Irregular polygon with cut-out corners</li>
                          <li>• Split layout: Header + Content sections</li>
                          <li>• Hover effects: scale + shadow</li>
                          <li>• Fixed height: 384px (h-96)</li>
                        </ul>
                      </div>

                      <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border">
                        <h4 className="font-semibold text-sm text-gray-600 dark:text-gray-400 mb-2">HEADER SECTION</h4>
                        <ul className="text-sm space-y-1">
                          <li>• Background: Blue gradient (800-900)</li>
                          <li>• Digital pattern overlay with dots</li>
                          <li>• AI banner with circuit board pattern</li>
                          <li>• Theme indicator: White dot + "BE SMART"</li>
                        </ul>
                      </div>

                      <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border">
                        <h4 className="font-semibold text-sm text-gray-600 dark:text-gray-400 mb-2">TITLE SECTION</h4>
                        <ul className="text-sm space-y-1">
                          <li>• Title: "AI in Daily Life" (text-2xl, bold)</li>
                          <li>• Difficulty badge: Yellow with black text</li>
                          <li>• Layout: Title left, badge right</li>
                        </ul>
                      </div>

                      <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border">
                        <h4 className="font-semibold text-sm text-gray-600 dark:text-gray-400 mb-2">CONTENT SECTION</h4>
                        <ul className="text-sm space-y-1">
                          <li>• Background: Dark gray (gray-900)</li>
                          <li>• Description: 3-line fixed height</li>
                          <li>• Tags: Inline with label</li>
                          <li>• CTA: Purple "View Details →"</li>
                        </ul>
                      </div>

                      <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border">
                        <h4 className="font-semibold text-sm text-gray-600 dark:text-gray-400 mb-2">COLORS & TYPOGRAPHY</h4>
                        <ul className="text-sm space-y-1">
                          <li>• Primary: Blue/Purple gradients</li>
                          <li>• Text: White (headers), Gray (metadata)</li>
                          <li>• Accent: Yellow (badge), Purple (CTA)</li>
                          <li>• Font sizes: 2xl (title), base (content), xs (tags)</li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  {/* Code Actions */}
                  <div className="space-y-4">
                    <h3 className="text-xl font-semibold">Code & Assets</h3>
                    <div className="flex flex-wrap gap-3">
                      <Button onClick={copyCode} className="gap-2">
                        {copied ? <Check className="h-4 w-4" /> : <Copy className="h-4 w-4" />}
                        {copied ? "Copied!" : "Copy Code"}
                      </Button>
                      <Button variant="outline" className="gap-2">
                        <Download className="h-4 w-4" />
                        Download Assets
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>

        {/* Interactive Features */}
        <SectionWrapper className="bg-gray-50 dark:bg-gray-800">
          <Container>
            <div className="max-w-4xl mx-auto">
              <SectionHeader
                title="🎨 Interactive Features"
                description="Explore the design elements and interactions"
                titleColor="blue"
                align="center"
                className="mb-8"
              />
              
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="bg-white dark:bg-gray-900 p-6 rounded-lg border">
                  <h4 className="font-semibold mb-2">Hover Effects</h4>
                  <p className="text-sm text-gray-600 dark:text-gray-400">
                    Scale transformation and enhanced shadow on hover for better interactivity
                  </p>
                </div>
                
                <div className="bg-white dark:bg-gray-900 p-6 rounded-lg border">
                  <h4 className="font-semibold mb-2">Digital Patterns</h4>
                  <p className="text-sm text-gray-600 dark:text-gray-400">
                    CSS-generated circuit board patterns and digital overlays for tech aesthetic
                  </p>
                </div>
                
                <div className="bg-white dark:bg-gray-900 p-6 rounded-lg border">
                  <h4 className="font-semibold mb-2">Responsive Design</h4>
                  <p className="text-sm text-gray-600 dark:text-gray-400">
                    Adapts to different screen sizes while maintaining visual hierarchy
                  </p>
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}
