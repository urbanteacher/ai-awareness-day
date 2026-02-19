"use client"

import { Container, SectionHeader } from "@/components/ui"
import { ToolCard } from "./ToolCard"
import { motion } from "framer-motion"
import { useState } from "react"
import { 
  BookOpen, 
  Palette, 
  GraduationCap, 
  Calculator,
  PenTool,
  Play,
  FileText,
  Lightbulb,
  ChevronDown,
  ChevronUp
} from "lucide-react"

const teacherTools = [
  // Content Creation
  {
    title: "Claude.ai",
    description: "Lesson planning, differentiation, feedback",
    category: "Content Creation",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Lesson planning assistance",
      "Differentiation strategies",
      "Student feedback generation",
      "Curriculum alignment",
      "Assessment rubrics"
    ],
    icon: <BookOpen className="h-5 w-5" />,
    demoUrl: "https://claude.ai",
    docsUrl: "https://claude.ai"
  },
  {
    title: "ChatGPT",
    description: "Brainstorming, rubrics, simplifying texts",
    category: "Content Creation",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Brainstorming sessions",
      "Rubric creation",
      "Text simplification",
      "Creative writing prompts",
      "Question generation"
    ],
    icon: <PenTool className="h-5 w-5" />,
    demoUrl: "https://chat.openai.com",
    docsUrl: "https://openai.com/chatgpt"
  },
  {
    title: "Perplexity AI",
    description: "Research with citations, fact-checking",
    category: "Content Creation",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Research with citations",
      "Fact-checking capabilities",
      "Source verification",
      "Academic references",
      "Real-time information"
    ],
    icon: <FileText className="h-5 w-5" />,
    demoUrl: "https://perplexity.ai",
    docsUrl: "https://perplexity.ai"
  },
  // Visual & Creative
  {
    title: "Canva Education",
    description: "AI-powered design for presentations",
    category: "Visual & Creative",
    type: "web" as const,
    status: "available" as const,
    features: [
      "AI-powered design suggestions",
      "Educational templates",
      "Presentation creation",
      "Infographic maker",
      "Collaborative features"
    ],
    icon: <Palette className="h-5 w-5" />,
    demoUrl: "https://canva.com/education",
    docsUrl: "https://canva.com/education"
  },
  {
    title: "Bing Create",
    description: "Generate custom images for lessons",
    category: "Visual & Creative",
    type: "web" as const,
    status: "available" as const,
    features: [
      "AI image generation",
      "Custom lesson visuals",
      "Educational illustrations",
      "Creative prompts",
      "High-quality outputs"
    ],
    icon: <Palette className="h-5 w-5" />,
    demoUrl: "https://bing.com/create",
    docsUrl: "https://bing.com/create"
  },
  {
    title: "Adobe Express",
    description: "Quick graphics and animations",
    category: "Visual & Creative",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Quick graphics creation",
      "Animation tools",
      "Templates library",
      "Easy sharing",
      "Mobile-friendly"
    ],
    icon: <Palette className="h-5 w-5" />,
    demoUrl: "https://express.adobe.com",
    docsUrl: "https://express.adobe.com"
  },
  // Learning Platforms
  {
    title: "Scratch + AI",
    description: "Block-based AI programming",
    category: "Learning Platforms",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Block-based programming",
      "AI integration",
      "Student-friendly interface",
      "Creative projects",
      "No coding experience needed"
    ],
    icon: <GraduationCap className="h-5 w-5" />,
    demoUrl: "https://scratch.mit.edu",
    docsUrl: "https://scratch.mit.edu"
  },
  {
    title: "Teachable Machine",
    description: "Train AI models without coding",
    category: "Learning Platforms",
    type: "web" as const,
    status: "available" as const,
    features: [
      "No coding required",
      "Image classification",
      "Audio recognition",
      "Pose detection",
      "Export to mobile apps"
    ],
    icon: <GraduationCap className="h-5 w-5" />,
    demoUrl: "https://teachablemachine.withgoogle.com",
    docsUrl: "https://teachablemachine.withgoogle.com"
  },
  {
    title: "Machine Learning for Kids",
    description: "Hands-on ML projects",
    category: "Learning Platforms",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Age-appropriate projects",
      "Scratch integration",
      "Hands-on activities",
      "Teacher guides",
      "Student worksheets"
    ],
    icon: <GraduationCap className="h-5 w-5" />,
    demoUrl: "https://machinelearningforkids.co.uk",
    docsUrl: "https://machinelearningforkids.co.uk"
  },
  // Subject-Specific
  {
    title: "Wolfram Alpha",
    description: "Mathematics and science calculations",
    category: "Subject-Specific",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Mathematical calculations",
      "Science problem solving",
      "Step-by-step solutions",
      "Graphing capabilities",
      "Educational examples"
    ],
    icon: <Calculator className="h-5 w-5" />,
    demoUrl: "https://wolframalpha.com",
    docsUrl: "https://wolframalpha.com"
  },
  {
    title: "Grammarly",
    description: "Writing assistance and feedback",
    category: "Subject-Specific",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Writing assistance",
      "Grammar checking",
      "Style suggestions",
      "Plagiarism detection",
      "Educational discounts"
    ],
    icon: <PenTool className="h-5 w-5" />,
    demoUrl: "https://grammarly.com/edu",
    docsUrl: "https://grammarly.com/edu"
  },
  {
    title: "Khan Academy",
    description: "AI-powered personalized learning",
    category: "Subject-Specific",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Personalized learning paths",
      "AI-powered recommendations",
      "Adaptive practice",
      "Progress tracking",
      "Free for educators"
    ],
    icon: <BookOpen className="h-5 w-5" />,
    demoUrl: "https://khanacademy.org",
    docsUrl: "https://khanacademy.org"
  },
  // Interactive Demos
  {
    title: "Quick Draw",
    description: "AI learns to recognize drawings",
    category: "Interactive Demos",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Drawing recognition",
      "Real-time AI learning",
      "Educational games",
      "Data visualization",
      "Machine learning concepts"
    ],
    icon: <Play className="h-5 w-5" />,
    demoUrl: "https://quickdraw.withgoogle.com",
    docsUrl: "https://quickdraw.withgoogle.com"
  },
  {
    title: "Semantris",
    description: "Word association AI game",
    category: "Interactive Demos",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Word association game",
      "AI-powered gameplay",
      "Educational fun",
      "Vocabulary building",
      "Interactive learning"
    ],
    icon: <Play className="h-5 w-5" />,
    demoUrl: "https://research.google.com/semantris",
    docsUrl: "https://research.google.com/semantris"
  },
  {
    title: "AutoDraw",
    description: "AI-assisted drawing tool",
    category: "Interactive Demos",
    type: "web" as const,
    status: "available" as const,
    features: [
      "AI-assisted drawing",
      "Shape recognition",
      "Creative assistance",
      "Easy to use",
      "Educational applications"
    ],
    icon: <Play className="h-5 w-5" />,
    demoUrl: "https://autodraw.com",
    docsUrl: "https://autodraw.com"
  },
  // Professional Development
  {
    title: "Elements of AI",
    description: "Free AI fundamentals course",
    category: "Professional Development",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Free AI fundamentals course",
      "Self-paced learning",
      "Certificate of completion",
      "Practical exercises",
      "No prerequisites"
    ],
    icon: <Lightbulb className="h-5 w-5" />,
    demoUrl: "https://elementsofai.com",
    docsUrl: "https://elementsofai.com"
  },
  {
    title: "AI4K12",
    description: "AI curriculum guidelines",
    category: "Professional Development",
    type: "web" as const,
    status: "available" as const,
    features: [
      "AI curriculum guidelines",
      "Grade-level standards",
      "Learning progressions",
      "Teacher resources",
      "Assessment frameworks"
    ],
    icon: <Lightbulb className="h-5 w-5" />,
    demoUrl: "https://ai4k12.org",
    docsUrl: "https://ai4k12.org"
  },
  {
    title: "Google AI Education",
    description: "Ready-to-use lesson plans",
    category: "Professional Development",
    type: "web" as const,
    status: "available" as const,
    features: [
      "Ready-to-use lesson plans",
      "Teacher training materials",
      "Classroom activities",
      "Assessment tools",
      "Free resources"
    ],
    icon: <Lightbulb className="h-5 w-5" />,
    demoUrl: "https://ai.google/education",
    docsUrl: "https://ai.google/education"
  }
]

export default function ToolsSection() {
  const [expandedCategories, setExpandedCategories] = useState<Set<string>>(new Set())
  
  // Group tools by category
  const toolsByCategory = teacherTools.reduce((acc, tool) => {
    if (!acc[tool.category]) {
      acc[tool.category] = []
    }
    acc[tool.category].push(tool)
    return acc
  }, {} as Record<string, typeof teacherTools>)

  const categories = [
    "Content Creation",
    "Visual & Creative", 
    "Learning Platforms",
    "Subject-Specific",
    "Interactive Demos",
    "Professional Development"
  ]

  const toggleCategory = (category: string) => {
    const newExpanded = new Set(expandedCategories)
    if (newExpanded.has(category)) {
      newExpanded.delete(category)
    } else {
      newExpanded.add(category)
    }
    setExpandedCategories(newExpanded)
  }

  const getVisibleTools = (categoryTools: typeof teacherTools) => {
    const isExpanded = expandedCategories.has(categoryTools[0]?.category || '')
    return isExpanded ? categoryTools : categoryTools.slice(0, 2) // Show only 2 tools initially on mobile
  }

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case "Content Creation":
        return <PenTool className="h-5 w-5" />
      case "Visual & Creative":
        return <Palette className="h-5 w-5" />
      case "Learning Platforms":
        return <GraduationCap className="h-5 w-5" />
      case "Subject-Specific":
        return <Calculator className="h-5 w-5" />
      case "Interactive Demos":
        return <Play className="h-5 w-5" />
      case "Professional Development":
        return <Lightbulb className="h-5 w-5" />
      default:
        return <BookOpen className="h-5 w-5" />
    }
  }

  return (
    <Container>
      <div className="space-y-16">
        <SectionHeader
          title="Free AI Tools for Teachers"
          subtitle="Start using AI in your classroom today - no budget required"
          description="Access our curated collection of free AI tools designed specifically for educators. No budget required - start using AI in your classroom today."
          align="center"
        />
        
        {/* Tools by Category */}
        <div className="space-y-12">
          {categories.map((category) => {
            const categoryTools = toolsByCategory[category] || []
            if (categoryTools.length === 0) return null

            return (
              <motion.div 
                key={category} 
                className="space-y-6"
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, amount: 0.3 }}
                transition={{ duration: 0.6 }}
              >
                {/* Category Header with Polygon - Mobile Friendly */}
                <div 
                  className="bg-gray-800 text-white p-4 sm:p-6 relative overflow-hidden cursor-pointer"
                  style={{ 
                    clipPath: 'polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))' 
                  }}
                  onClick={() => toggleCategory(category)}
                >
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3 sm:gap-4">
                      <div className="w-8 h-8 sm:w-12 sm:h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                        {getCategoryIcon(category)}
                      </div>
                      <div>
                        <h3 className="text-lg sm:text-2xl font-bold text-white">{category}</h3>
                        <p className="text-xs sm:text-base text-gray-300">
                          {categoryTools.length} free tools ready to use
                        </p>
                      </div>
                    </div>
                    
                    {/* Mobile Toggle Button */}
                    <div className="sm:hidden">
                      {expandedCategories.has(category) ? (
                        <ChevronUp className="w-5 h-5 text-gray-300" />
                      ) : (
                        <ChevronDown className="w-5 h-5 text-gray-300" />
                      )}
                    </div>
                  </div>
                  
                  {/* Decorative corner polygon */}
                  <div className="absolute top-2 right-2 w-4 h-4 sm:w-6 sm:h-6 bg-blue-400/20 rounded-sm" 
                       style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
                </div>
                
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {categoryTools.map((tool, index) => (
                    <ToolCard
                      key={tool.title}
                      title={tool.title}
                      description={tool.description}
                      category={tool.category}
                      type={tool.type}
                      status={tool.status}
                      features={tool.features}
                      icon={tool.icon}
                      downloadUrl={(tool as any).downloadUrl}
                      demoUrl={tool.demoUrl}
                      docsUrl={tool.docsUrl}
                      className="animate-in fade-in-0 slide-in-from-bottom-4"
                      style={{ animationDelay: `${index * 100}ms` }}
                    />
                  ))}
                </div>
              </motion.div>
            )
          })}
        </div>

        {/* Call to Action */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, amount: 0.3 }}
          transition={{ duration: 0.6, delay: 0.2 }}
          className="text-center"
        >
          <div 
            className="bg-gray-800 p-8 text-white max-w-4xl mx-auto relative overflow-hidden"
            style={{ 
              clipPath: 'polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))' 
            }}
          >
            {/* Background gradient overlay */}
            <div className="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
            
            <div className="relative z-10 space-y-6">
              <h3 className="text-2xl font-bold text-white">Need Help Getting Started?</h3>
              <p className="text-gray-300 max-w-2xl mx-auto">
                New to AI in education? We provide free training resources and support to help you integrate these tools into your teaching practice.
              </p>
              <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                  Get Training Support
                </button>
                <button className="px-6 py-3 border border-gray-500 text-white hover:bg-gray-700 hover:border-gray-400 font-semibold rounded-lg transition-colors">
                  Join Teacher Community
                </button>
              </div>
            </div>

            {/* Decorative corner polygon */}
            <div className="absolute top-2 right-2 w-6 h-6 bg-blue-400/20 rounded-sm" 
                 style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
          </div>
        </motion.div>
      </div>
    </Container>
  )
}
