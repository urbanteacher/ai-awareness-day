"use client"

import { Navigation } from "@/components/navigation"
import { SectionWrapper, Container, SectionHeader } from "@/components/ui"
import { Button } from "@/components/ui/button"
import { ArrowLeft } from "lucide-react"
import Link from "next/link"

export default function DesignDemoPage() {
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
                    Back to Design Concept
                  </Button>
                </Link>
              </div>
              
              <h1 className="text-4xl md:text-6xl font-bold text-black dark:text-white">
                Activity Card Design Demo
              </h1>
              <p className="text-lg text-black dark:text-white">Interactive playground for testing polygon-shaped activity card designs</p>
            </div>
          </Container>
        </SectionWrapper>

        {/* Live Preview */}
        <SectionWrapper className="bg-card">
          <Container>
            <SectionHeader
              title="🎨 Live Preview - Split + Image Card"
              description="Interactive polygon-shaped activity card with split design and image content"
              titleColor="purple"
              className="mb-8"
            />
            
            <div className="relative p-20 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900">
              <div className="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5" />
              <div className="relative">
                <div className="grid grid-cols-1 gap-8 items-start">
                  {/* Split + Image Card */}
                  <div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:scale-105 hover:shadow-2xl"
                       style={{
                         clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                       }}>
                    
                    {/* Left side - Theme section with image */}
                    <div className="flex-1 bg-gradient-to-br from-blue-500 to-blue-600 p-6 relative overflow-hidden">
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
                        <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
                          <span className="line-clamp-2">AI in Daily Life</span>
                        </h4>
                        <span className="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 flex-shrink-0">
                          Beginner
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
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}