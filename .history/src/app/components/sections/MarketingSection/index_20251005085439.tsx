"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Copy, Download, Share2, Mail, Twitter, Facebook, Linkedin, Instagram } from "lucide-react"
import { Container, SectionHeader } from "@/components/ui"

type ContentItem = 
  | { platform: string; text: string; hashtags: string }
  | { type: string; text: string }
  | { type: string; subject: string; text: string }
  | { type: string; title: string; text: string }

const marketingContent = [
  {
    id: "social-media-posts",
    title: "Social Media Posts",
    description: "Ready-to-use posts for Twitter, Facebook, LinkedIn, and Instagram",
    icon: Share2,
    color: "bg-blue-500",
    content: [
      {
        platform: "Twitter/X",
        text: "🚀 Excited to announce our school's participation in #AIAwarenessDay2026! We're preparing our students for an AI-integrated future with hands-on activities and real-world applications. #EdTech #FutureReady #AIEducation",
        hashtags: "#AIAwarenessDay2026 #EdTech #FutureReady #AIEducation #DigitalLiteracy"
      },
      {
        platform: "Facebook",
        text: "🌟 Our school is proud to be part of AI Awareness Day 2026! This initiative helps our students understand, question, and use AI wisely. From interactive workshops to real-world applications, we're building the next generation of AI-literate citizens. Join us in preparing students for tomorrow's world!",
        hashtags: "#AIAwarenessDay2026 #Education #Technology #FutureReady #StudentSuccess"
      },
      {
        platform: "LinkedIn",
        text: "🎓 Proud to announce our school's participation in AI Awareness Day 2026. We're equipping our students with essential AI literacy skills through comprehensive educational activities. This initiative represents our commitment to preparing students for an AI-integrated future. #Education #AI #FutureOfWork #StudentDevelopment",
        hashtags: "#AIAwarenessDay2026 #Education #AI #FutureOfWork #StudentDevelopment #EdTech"
      },
      {
        platform: "Instagram",
        text: "✨ AI Awareness Day 2026 is here! Our students are diving deep into the world of artificial intelligence with hands-on activities, creative projects, and real-world applications. Swipe to see their amazing work! #AIAwarenessDay2026 #StudentWork #AIEducation #FutureReady",
        hashtags: "#AIAwarenessDay2026 #StudentWork #AIEducation #FutureReady #CreativeLearning"
      }
    ] as ContentItem[]
  }
]

export default function MarketingSection() {
  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text)
    // You could add a toast notification here
    alert('Content copied to clipboard!')
  }

  const downloadContent = (content: any, filename: string) => {
    const blob = new Blob([JSON.stringify(content, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    a.click()
    URL.revokeObjectURL(url)
  }

  return (
    <Container>
      <div className="space-y-16">
        <SectionHeader
          title="Marketing & Media"
          subtitle="Share & Promote"
          description="Ready-to-use social media content for promoting your AI Awareness Day 2026 activities"
          align="center"
        />

        <div className="max-w-4xl mx-auto">
          <div className="flex justify-center">
            <div className="w-full max-w-2xl">
              {marketingContent.map((section, index) => {
              const IconComponent = section.icon
              return (
                <div
                  key={section.id}
                  className="relative"
                >
                  {/* Polygon Card with Grey Theme */}
                  <div 
                    className="bg-gray-800 dark:bg-gray-800 text-white overflow-hidden shadow-2xl hover:shadow-3xl transition-all duration-300"
                    style={{ 
                      clipPath: 'polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))' 
                    }}
                  >
                    {/* Header with Blue Gradient */}
                    <div className="bg-gradient-to-r from-blue-600 to-blue-500 p-6">
                      <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                          <IconComponent className="w-6 h-6 text-white" />
                        </div>
                        <div>
                          <h3 className="text-2xl font-bold text-white">{section.title}</h3>
                          <p className="text-blue-100">{section.description}</p>
                        </div>
                      </div>
                    </div>

                    {/* Content Area */}
                    <div className="p-6 bg-gray-800">
                      <div className="space-y-6">
                        {section.content.map((item, itemIndex) => (
                          <div 
                            key={itemIndex} 
                            className="bg-gray-700 rounded-lg p-4 border border-gray-600 hover:bg-gray-600 transition-colors duration-200"
                          >
                            <div className="flex items-center justify-between mb-4">
                              <h4 className="font-bold text-white text-lg">
                                {'platform' in item ? item.platform : 'type' in item ? item.type : 'title' in item ? (item as any).title : 'Content'}
                              </h4>
                              <div className="flex gap-2">
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => copyToClipboard(item.text)}
                                  className="bg-gray-600 border-gray-500 text-white hover:bg-gray-500 hover:border-gray-400"
                                >
                                  <Copy className="w-4 h-4 mr-1" />
                                  Copy
                                </Button>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => downloadContent(item, `${section.id}-${itemIndex}.txt`)}
                                  className="bg-gray-600 border-gray-500 text-white hover:bg-gray-500 hover:border-gray-400"
                                >
                                  <Download className="w-4 h-4 mr-1" />
                                  Download
                                </Button>
                              </div>
                            </div>
                            <div className="space-y-3">
                              {'subject' in item && item.subject && (
                                <div>
                                  <p className="text-sm font-medium text-gray-300">Subject:</p>
                                  <p className="text-sm text-gray-200">{item.subject}</p>
                                </div>
                              )}
                              <div>
                                <p className="text-sm font-medium text-gray-300 mb-2">Content:</p>
                                <div className="bg-gray-800 p-4 rounded border border-gray-600 text-sm text-gray-200 whitespace-pre-wrap">
                                  {item.text}
                                </div>
                              </div>
                              {'hashtags' in item && item.hashtags && (
                                <div>
                                  <p className="text-sm font-medium text-gray-300 mb-1">Hashtags:</p>
                                  <p className="text-sm text-blue-400 font-medium">{item.hashtags}</p>
                                </div>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>

                    {/* Decorative corner polygon */}
                    <div className="absolute top-2 right-2 w-6 h-6 bg-blue-400/20 rounded-sm" 
                         style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
                  </div>
                </div>
              )
            })}
            </div>
          </div>

          {/* Quick Actions */}
          <div className="mt-12 text-center">
            <div 
              className="bg-gray-800 p-8 text-white max-w-4xl mx-auto relative overflow-hidden"
              style={{ 
                clipPath: 'polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))' 
              }}
            >
              {/* Background gradient overlay */}
              <div className="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
              
              <div className="relative z-10">
                <h3 className="text-2xl font-bold mb-4 text-white">
                  Need More Content?
                </h3>
                <p className="text-gray-300 mb-6">
                  Download our complete marketing kit with additional templates, graphics, and resources
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Button 
                    size="lg" 
                    className="bg-blue-600 hover:bg-blue-700 text-white font-semibold"
                    onClick={() => downloadContent(marketingContent, 'ai-awareness-day-marketing-kit.json')}
                  >
                    <Download className="w-5 h-5 mr-2" />
                    Download Complete Kit
                  </Button>
                  <Button 
                    size="lg" 
                    variant="outline" 
                    className="border-gray-500 text-white hover:bg-gray-700 hover:border-gray-400 font-semibold"
                    onClick={() => copyToClipboard(JSON.stringify(marketingContent, null, 2))}
                  >
                    <Copy className="w-5 h-5 mr-2" />
                    Copy All Content
                  </Button>
                </div>
              </div>

            {/* Decorative corner polygon */}
            <div className="absolute top-2 right-2 w-6 h-6 bg-blue-400/20 rounded-sm" 
                 style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
          </div>
        </div>
        </div>
      </div>
    </Container>
  )
}
