"use client"

import { Container, SectionHeader } from "@/components/ui"
import { StepCard } from "./StepCard"
import { StepConnector } from "./StepConnector"

const implementationSteps = [
  {
    step: 1,
    title: "Sample Letter for Headteacher",
    description: "Templates for stakeholder buy-in and school administration approval.",
    isCompleted: false
  },
  {
    step: 2,
    title: "Choose SOW to Celebrate Campaign",
    description: "Flexible format selection to match your school's needs and resources.",
    isCompleted: false
  },
  {
    step: 3,
    title: "Design Display Board for School",
    description: "Physical presence in schools with ready-to-print materials and layouts.",
    isCompleted: false
  },
  {
    step: 4,
    title: "Share Campaign with Community",
    description: "Community building component to engage students and share experiences.",
    isCompleted: false
  }
]

export default function ImplementationSection() {
  return (
    <Container>
      <div className="space-y-16">
        <div className="space-y-4 text-center">
          <p className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
            Everything you need to get started
          </p>
            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl text-purple-600 dark:text-purple-400">
              Implementation Guide
            </h2>
          <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
            Complete toolkit with templates, activities, and resources to launch AI Awareness Day in your school.
          </p>
        </div>
        
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {implementationSteps.map((step, index) => (
              <StepCard
                key={step.step}
                step={step.step}
                title={step.title}
                description={step.description}
                isActive={false}
                isCompleted={step.isCompleted}
              />
            ))}
          </div>
        </div>
      </div>
    </Container>
  )
}
