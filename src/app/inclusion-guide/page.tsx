"use client"

import { motion } from "framer-motion"
import { ArrowLeft, Users, BookOpen, Lightbulb, Heart, Globe, Shield, Target, Zap, Award } from "lucide-react"
import Link from "next/link"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"

const inclusionStrategies = [
  {
    id: "advanced-learners",
    title: "Advanced Learners",
    icon: Award,
    color: "from-purple-500 to-purple-600",
    description: "Challenge and extend high-achieving students",
    strategies: [
      "Independent AI research projects with mentor guidance",
      "Mentoring younger students in AI concepts",
      "Creating educational resources for peers",
      "Participating in external AI competitions",
      "Developing AI solutions for real school challenges"
    ]
  },
  {
    id: "struggling-learners",
    title: "Struggling Learners",
    icon: Heart,
    color: "from-red-500 to-red-600",
    description: "Support students who need additional help",
    strategies: [
      "Visual and hands-on demonstrations of AI concepts",
      "Simplified explanations using everyday examples",
      "Peer support partnerships for mutual learning",
      "Connect AI learning to personal interests",
      "Celebrate small victories and incremental progress"
    ]
  },
  {
    id: "eal-students",
    title: "EAL Students",
    icon: Globe,
    color: "from-blue-500 to-blue-600",
    description: "Support English as an Additional Language learners",
    strategies: [
      "Provide multilingual resources where possible",
      "Use visual learning materials and diagrams",
      "Explore cultural perspectives on AI technology",
      "Use AI translation tools as learning aids",
      "Share international examples and case studies"
    ]
  },
  {
    id: "visual-learners",
    title: "Visual Learners",
    icon: BookOpen,
    color: "from-green-500 to-green-600",
    description: "Support students who learn best through visual means",
    strategies: [
      "Use diagrams, infographics, and flowcharts",
      "Create visual demonstrations of AI processes",
      "Provide video explanations and tutorials",
      "Use mind maps to organize AI concepts",
      "Incorporate visual storytelling techniques"
    ]
  },
  {
    id: "kinesthetic-learners",
    title: "Kinesthetic Learners",
    icon: Zap,
    color: "from-orange-500 to-orange-600",
    description: "Support students who learn through movement and touch",
    strategies: [
      "Provide hands-on AI project activities",
      "Use role-play to demonstrate AI concepts",
      "Create physical models of AI systems",
      "Organize interactive workshops and labs",
      "Use movement-based learning games"
    ]
  },
  {
    id: "auditory-learners",
    title: "Auditory Learners",
    icon: Users,
    color: "from-cyan-500 to-cyan-600",
    description: "Support students who learn best through listening",
    strategies: [
      "Facilitate group discussions about AI topics",
      "Use podcasts and audio explanations",
      "Encourage verbal presentations and debates",
      "Provide audio recordings of key concepts",
      "Use music and sound to explain AI principles"
    ]
  },
  {
    id: "reading-writing-learners",
    title: "Reading/Writing Learners",
    icon: BookOpen,
    color: "from-indigo-500 to-indigo-600",
    description: "Support students who prefer text-based learning",
    strategies: [
      "Provide comprehensive written materials",
      "Create note-taking guides and templates",
      "Encourage written reflections and journals",
      "Use text-based case studies and examples",
      "Provide written step-by-step instructions"
    ]
  },
  {
    id: "sen-students",
    title: "SEN Students",
    icon: Shield,
    color: "from-pink-500 to-pink-600",
    description: "Support students with special educational needs",
    strategies: [
      "Adapt materials to individual learning needs",
      "Provide clear, simple instructions",
      "Offer additional support and scaffolding",
      "Use flexible assessment methods",
      "Create safe, supportive learning environments"
    ]
  },
  {
    id: "anxious-students",
    title: "Anxious Students",
    icon: Heart,
    color: "from-teal-500 to-teal-600",
    description: "Support students who may feel anxious about AI",
    strategies: [
      "Set clear expectations and boundaries",
      "Provide gradual exposure to AI concepts",
      "Use positive reinforcement and encouragement",
      "Create safe spaces for questions and concerns",
      "Focus on AI as a helpful tool, not a replacement"
    ]
  },
  {
    id: "disengaged-students",
    title: "Disengaged Students",
    icon: Target,
    color: "from-yellow-500 to-yellow-600",
    description: "Re-engage students who may lack interest",
    strategies: [
      "Connect AI to personal interests and hobbies",
      "Use relevant, current examples and case studies",
      "Provide choice in activity selection",
      "Show real-world applications and career paths",
      "Use gamification and interactive elements"
    ]
  }
]

const universalDesignPrinciples = [
  {
    title: "Multiple Means of Representation",
    description: "Present information in various formats (visual, auditory, text)",
    examples: ["Diagrams + explanations", "Videos + transcripts", "Interactive + static content"]
  },
  {
    title: "Multiple Means of Engagement",
    description: "Provide different ways to motivate and engage learners",
    examples: ["Choice in activities", "Relevant examples", "Collaborative options"]
  },
  {
    title: "Multiple Means of Expression",
    description: "Allow students to demonstrate learning in different ways",
    examples: ["Written reports", "Oral presentations", "Visual projects", "Digital creations"]
  }
]

export default function InclusionGuidePage() {
  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <div className="bg-muted/30 py-12">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="text-center"
          >
            <div className="flex items-center justify-center mb-6">
              <Link href="/">
                <Button variant="ghost" size="sm" className="mr-4">
                  <ArrowLeft className="w-4 h-4 mr-2" />
                  Back to Home
                </Button>
              </Link>
            </div>
            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl mb-4">
              Inclusion Guide for Teachers
            </h1>
            <p className="text-xl text-muted-foreground max-w-3xl mx-auto">
              Practical strategies to ensure AI education is accessible and engaging for all learners
            </p>
          </motion.div>
        </div>
      </div>

      {/* Universal Design Principles */}
      <div className="py-16">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="text-center mb-12"
          >
            <h2 className="text-3xl font-bold tracking-tight mb-4">Universal Design Principles</h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Apply these principles to make AI education accessible to all students
            </p>
          </motion.div>

          <div className="grid md:grid-cols-3 gap-6 mb-16">
            {universalDesignPrinciples.map((principle, index) => (
              <motion.div
                key={principle.title}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.3 + index * 0.1 }}
              >
                <Card className="h-full">
                  <CardHeader>
                    <CardTitle className="text-xl">{principle.title}</CardTitle>
                    <CardDescription className="text-base">
                      {principle.description}
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ul className="space-y-2">
                      {principle.examples.map((example, i) => (
                        <li key={i} className="flex items-center text-sm text-muted-foreground">
                          <div className="w-2 h-2 bg-primary rounded-full mr-3 flex-shrink-0" />
                          {example}
                        </li>
                      ))}
                    </ul>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* Inclusion Strategies */}
      <div className="py-16 bg-muted/30">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="text-center mb-12"
          >
            <h2 className="text-3xl font-bold tracking-tight mb-4">Inclusion Strategies</h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Specific approaches for different learner needs and preferences
            </p>
          </motion.div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {inclusionStrategies.map((strategy, index) => (
              <motion.div
                key={strategy.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.1 + index * 0.05 }}
              >
                <Card className="h-full hover:shadow-lg transition-shadow">
                  <CardHeader>
                    <div className="flex items-center mb-3">
                      <div className={`p-3 rounded-lg bg-gradient-to-r ${strategy.color} mr-4`}>
                        <strategy.icon className="w-6 h-6 text-white" />
                      </div>
                      <div>
                        <CardTitle className="text-xl">{strategy.title}</CardTitle>
                        <CardDescription className="text-sm">
                          {strategy.description}
                        </CardDescription>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <ul className="space-y-2">
                      {strategy.strategies.map((item, i) => (
                        <li key={i} className="flex items-start text-sm">
                          <div className="w-2 h-2 bg-gradient-to-r from-primary to-primary/60 rounded-full mr-3 mt-2 flex-shrink-0" />
                          <span className="text-muted-foreground">{item}</span>
                        </li>
                      ))}
                    </ul>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* Quick Tips Section */}
      <div className="py-16">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="text-center mb-12"
          >
            <h2 className="text-3xl font-bold tracking-tight mb-4">Quick Implementation Tips</h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Simple steps to get started with inclusive AI education
            </p>
          </motion.div>

          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            {[
              {
                title: "Start Small",
                description: "Begin with one inclusive strategy and gradually add more",
                icon: Target
              },
              {
                title: "Know Your Students",
                description: "Understand individual learning needs and preferences",
                icon: Users
              },
              {
                title: "Be Flexible",
                description: "Adapt activities based on student responses and engagement",
                icon: Lightbulb
              },
              {
                title: "Celebrate Success",
                description: "Acknowledge progress and achievements for all learners",
                icon: Award
              }
            ].map((tip, index) => (
              <motion.div
                key={tip.title}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.2 + index * 0.1 }}
              >
                <Card className="text-center h-full">
                  <CardHeader>
                    <div className="mx-auto p-3 rounded-full bg-primary/10 w-fit mb-4">
                      <tip.icon className="w-6 h-6 text-primary" />
                    </div>
                    <CardTitle className="text-lg">{tip.title}</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <CardDescription className="text-sm">
                      {tip.description}
                    </CardDescription>
                  </CardContent>
                </Card>
              </motion.div>
            ))}
          </div>
        </div>
      </div>

      {/* Call to Action */}
      <div className="py-16 bg-muted/30">
        <div className="container mx-auto px-4 text-center">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
          >
            <h2 className="text-3xl font-bold tracking-tight mb-4">
              Ready to Make AI Education Inclusive?
            </h2>
            <p className="text-lg text-muted-foreground mb-8 max-w-2xl mx-auto">
              Use these strategies to ensure every student can participate meaningfully in AI Awareness Day activities.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link href="/">
                <Button size="lg" className="w-full sm:w-auto">
                  Explore Activities
                </Button>
              </Link>
              <Link href="/contact">
                <Button variant="outline" size="lg" className="w-full sm:w-auto">
                  Get Support
                </Button>
              </Link>
            </div>
          </motion.div>
        </div>
      </div>
    </div>
  )
}

