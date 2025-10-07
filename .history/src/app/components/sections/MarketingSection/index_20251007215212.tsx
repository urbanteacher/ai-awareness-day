"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Copy, Download, Share2, Mail, Twitter, Facebook, Linkedin, Instagram, ChevronDown, Youtube } from "lucide-react"
import { Container, SectionHeader } from "@/components/ui"
import { useState } from "react"

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
      },
      {
        platform: "TikTok",
        text: "🎬 AI Awareness Day 2026 is trending! Our students are creating amazing content about AI in education. From coding challenges to AI art projects, watch how we're preparing the next generation! #AIAwarenessDay2026 #AIEducation #StudentLife #TechTrends",
        hashtags: "#AIAwarenessDay2026 #AIEducation #StudentLife #TechTrends #Viral"
      },
      {
        platform: "YouTube",
        text: "📺 New Video: AI Awareness Day 2026 - How Our School is Preparing Students for an AI Future. Watch our comprehensive coverage of hands-on AI activities, student interviews, and expert insights. Subscribe for more educational content! #AIAwarenessDay2026 #AIEducation #EdTech",
        hashtags: "#AIAwarenessDay2026 #AIEducation #EdTech #YouTube #EducationalContent"
      }
    ] as ContentItem[]
  }
]

export default function MarketingSection() {
  const [showAll, setShowAll] = useState(false)

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

  const getVisibleContent = (content: ContentItem[]) => {
    return showAll ? content : content.slice(0, 4)
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

        <div className="max-w-7xl mx-auto">
          <div className="flex justify-center">
            <div className="w-full max-w-6xl">
              {marketingContent.map((section, index) => {
              const IconComponent = section.icon
              return (
                <div
                  key={section.id}
                  className="relative"
                >
                  {/* Polygon Card with Grey Theme */}
                  <div 
                    className="bg-gray-800 text-white overflow-hidden shadow-2xl hover:shadow-3xl transition-all duration-300"
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

                    {/* Content Area - 2x2 Grid */}
                    <div className="p-8 bg-gray-800">
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {getVisibleContent(section.content).map((item, itemIndex) => (
                          <div 
                            key={itemIndex} 
                            className="bg-gray-700 rounded-lg p-6 border border-gray-600 hover:bg-gray-600 transition-colors duration-200"
                          >
                            <div className="flex items-center justify-between mb-4">
                              <div className="flex items-center gap-2">
                                {'platform' in item && (
                                  <>
                                    {item.platform === 'Twitter/X' && <Twitter className="w-5 h-5 text-blue-400" />}
                                    {item.platform === 'Facebook' && <Facebook className="w-5 h-5 text-blue-600" />}
                                    {item.platform === 'LinkedIn' && <Linkedin className="w-5 h-5 text-blue-700" />}
                                    {item.platform === 'Instagram' && <Instagram className="w-5 h-5 text-pink-500" />}
                                    {item.platform === 'TikTok' && <div className="w-5 h-5 bg-black rounded text-white flex items-center justify-center text-xs font-bold">TT</div>}
                                    {item.platform === 'YouTube' && <Youtube className="w-5 h-5 text-red-500" />}
                                  </>
                                )}
                                <h4 className="font-bold text-white text-lg">
                                  {'platform' in item ? item.platform : 'type' in item ? item.type : 'title' in item ? (item as any).title : 'Content'}
                                </h4>
                              </div>
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
                                <div className="bg-gray-800 p-4 rounded border border-gray-600 text-sm text-gray-200 whitespace-pre-wrap max-h-32 overflow-y-auto">
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
                      
                      {/* Show More Button */}
                      {section.content.length > 4 && (
                        <div className="mt-6 text-center">
                          <Button
                            onClick={() => setShowAll(!showAll)}
                            variant="outline"
                            className="border-gray-500 text-black hover:bg-gray-700 hover:border-gray-400"
                          >
                            <ChevronDown className={`w-4 h-4 mr-2 transition-transform ${showAll ? 'rotate-180' : ''}`} />
                            {showAll ? 'Show Less' : `Show More (${section.content.length - 4} more)`}
                          </Button>
                        </div>
                      )}
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

        </div>
      </div>
    </Container>
  )
}
