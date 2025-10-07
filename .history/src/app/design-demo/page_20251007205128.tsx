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
              
            </div>
          </Container>
        </SectionWrapper>

        {/* Live Preview */}
        <SectionWrapper className="bg-card">
          <Container>
            
            <div className="relative p-20 rounded-xl overflow-hidden">
              <div className="relative">
                <div className="grid grid-cols-1 gap-8 items-start">
                  {/* Split + Image Card 1 */}
                  <div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:shadow-2xl hover:shadow-purple-500/20"
                       style={{
                         clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                       }}>
                    
                    {/* Left side - Theme section with image */}
                    <div className="bg-gradient-to-br from-blue-500 to-blue-600 p-6 relative overflow-hidden">
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
                            className="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-90"
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
                    <div className="bg-gray-800 p-6 flex flex-col">
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

                  {/* Split + Image Card 2 */}
                  <div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:shadow-2xl hover:shadow-purple-500/20"
                       style={{
                         clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                       }}>
                    
                    {/* Left side - Theme section with image */}
                    <div className="bg-gradient-to-br from-purple-500 to-purple-600 p-6 relative overflow-hidden">
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
                        <span className="text-sm font-medium text-white">BE CREATIVE</span>
                      </div>
                      
                      {/* Image content in theme section */}
                      <div className="relative z-10 mb-4">
                        <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
                          <img 
                            src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=200&fit=crop&crop=center" 
                            alt="AI Activity" 
                            className="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-90"
                          />
                        </div>
                      </div>
                      
                      {/* Title in theme section with difficulty badge */}
                      <div className="relative z-10 flex items-start justify-between">
                        <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
                          <span className="line-clamp-2">AI Art Generation</span>
                        </h4>
                        <span className="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400 flex-shrink-0">
                          Intermediate
                        </span>
                      </div>
                    </div>
                    
                    {/* Right side - Content section */}
                    <div className="bg-gray-800 p-6 flex flex-col">
                      {/* Description */}
                      <div className="flex-1">
                        <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
                          <span className="line-clamp-3">Explore how AI can create art and creative content</span>
                        </p>
                      </div>

                      {/* Tags */}
                      <div className="mt-4 space-y-2">
                        <div className="flex items-center space-x-2">
                          <span className="text-xs text-gray-400">Tags</span>
                          <div className="flex flex-wrap gap-1">
                            <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                              creativity
                            </span>
                            <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                              art
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

                  {/* Split + Image Card 3 */}
                  <div className="bg-gray-800 p-0 border border-gray-600 hover:border-gray-500 transition-all duration-300 group cursor-pointer relative overflow-hidden flex flex-col hover:shadow-2xl hover:shadow-purple-500/20"
                       style={{
                         clipPath: "polygon(0 0, calc(100% - 50px) 0, 100% 50px, 100% 100%, 50px 100%, 0 calc(100% - 50px))"
                       }}>
                    
                    {/* Left side - Theme section with image */}
                    <div className="bg-gradient-to-br from-green-500 to-green-600 p-6 relative overflow-hidden">
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
                        <span className="text-sm font-medium text-white">BE RESPONSIBLE</span>
                      </div>
                      
                      {/* Image content in theme section */}
                      <div className="relative z-10 mb-4">
                        <div className="w-full h-24 bg-white/20 rounded-lg overflow-hidden backdrop-blur-sm">
                          <img 
                            src="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400&h=200&fit=crop&crop=center" 
                            alt="AI Activity" 
                            className="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-90"
                          />
                        </div>
                      </div>
                      
                      {/* Title in theme section with difficulty badge */}
                      <div className="relative z-10 flex items-start justify-between">
                        <h4 className="text-2xl font-bold text-white leading-tight flex-1 mr-4">
                          <span className="line-clamp-2">AI Ethics & Safety</span>
                        </h4>
                        <span className="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 flex-shrink-0">
                          Advanced
                        </span>
                      </div>
                    </div>
                    
                    {/* Right side - Content section */}
                    <div className="bg-gray-800 p-6 flex flex-col">
                      {/* Description */}
                      <div className="flex-1">
                        <p className="text-white text-base leading-relaxed line-clamp-3 h-16 flex items-start">
                          <span className="line-clamp-3">Learn about responsible AI use and ethical considerations</span>
                        </p>
                      </div>

                      {/* Tags */}
                      <div className="mt-4 space-y-2">
                        <div className="flex items-center space-x-2">
                          <span className="text-xs text-gray-400">Tags</span>
                          <div className="flex flex-wrap gap-1">
                            <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                              ethics
                            </span>
                            <span className="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded truncate max-w-[120px]">
                              safety
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