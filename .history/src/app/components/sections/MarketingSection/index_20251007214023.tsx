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
  },
  {
    id: "email-templates",
    title: "Email Templates",
    description: "Professional email templates for parent communications and announcements",
    icon: Mail,
    color: "bg-green-500",
    content: [
      {
        type: "Parent Announcement",
        subject: "AI Awareness Day 2026 - Important Information",
        text: "Dear Parents,\n\nWe're excited to announce our school's participation in AI Awareness Day 2026! This educational initiative will help our students understand and engage with artificial intelligence in a safe, educational environment.\n\nDate: [Insert Date]\nTime: [Insert Time]\nLocation: [Insert Location]\n\nPlease ensure your child brings their permission slip and any required materials.\n\nBest regards,\n[Your Name]"
      },
      {
        type: "Staff Briefing",
        subject: "AI Awareness Day 2026 - Staff Preparation",
        text: "Dear Team,\n\nAI Awareness Day 2026 is approaching! Please review the attached materials and prepare your classrooms for the activities.\n\nKey Points:\n- All activities are age-appropriate\n- Safety guidelines must be followed\n- Student engagement is our priority\n\nQuestions? Contact [Name] at [Email]\n\nThank you for your dedication!"
      }
    ] as ContentItem[]
  },
  {
    id: "press-releases",
    title: "Press Releases",
    description: "Media-ready press releases for local news and community outreach",
    icon: Copy,
    color: "bg-purple-500",
    content: [
      {
        type: "Local Media",
        title: "Local School Participates in National AI Awareness Day 2026",
        text: "FOR IMMEDIATE RELEASE\n\n[School Name] Joins National AI Awareness Day 2026 Initiative\n\n[City, State] - [School Name] is proud to announce its participation in AI Awareness Day 2026, a national educational initiative designed to help students understand and engage with artificial intelligence technology.\n\nThe school will host a series of interactive workshops, hands-on activities, and educational sessions designed to teach students about AI's role in modern society while emphasizing critical thinking and responsible use.\n\n'This initiative aligns perfectly with our commitment to preparing students for the future,' said [Principal Name], Principal of [School Name]. 'AI literacy is becoming as essential as reading and writing.'\n\nActivities will include:\n- Interactive AI demonstrations\n- Student-led projects\n- Parent information sessions\n- Community showcase\n\nFor more information, contact [Contact Name] at [Phone] or [Email]."
      }
    ] as ContentItem[]
  },
  {
    id: "social-graphics",
    title: "Social Graphics",
    description: "Visual content ideas and graphic design suggestions for social media",
    icon: Instagram,
    color: "bg-pink-500",
    content: [
      {
        type: "Instagram Post",
        title: "Visual Content Ideas",
        text: "📱 Instagram Post Ideas:\n\n1. Student showcase carousel\n2. Behind-the-scenes activity photos\n3. Quote graphics with AI facts\n4. Before/after learning moments\n5. Teacher testimonials\n6. Parent engagement posts\n\n🎨 Design Elements:\n- Use school colors\n- Include AI Awareness Day 2026 logo\n- Add relevant hashtags\n- Keep text minimal and impactful\n- Use high-quality images\n\n📐 Recommended Sizes:\n- Square: 1080x1080px\n- Story: 1080x1920px\n- Reel: 1080x1920px"
      },
      {
        type: "Facebook Cover",
        title: "Facebook Cover Design",
        text: "📘 Facebook Cover Ideas:\n\n1. Collage of student activities\n2. AI Awareness Day 2026 banner\n3. School logo with AI elements\n4. Quote overlay on activity photo\n5. Timeline of events\n\n🎨 Design Tips:\n- 1200x630px recommended\n- Keep text readable on mobile\n- Use contrasting colors\n- Include call-to-action\n- Update regularly with new content"
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

        <div className="max-w-6xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
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
                    {/* Header with Dynamic Gradient */}
                    <div className={`bg-gradient-to-r ${section.color.replace('bg-', 'from-')}-600 to-${section.color.replace('bg-', '')}-500 p-6`}>
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
