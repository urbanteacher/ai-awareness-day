"use client"

import { Navigation } from "@/components/navigation"
import { SectionWrapper, Container, SectionHeader } from "@/components/ui"
import { SplitImageCard } from "@/components/ui/split-image-card"
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
                  {/* Split + Image Card */}
                  <SplitImageCard
                    title="AI in Daily Life"
                    description="Discuss how AI is already part of students' daily routines"
                    tags={['discussion', 'everyday']}
                    theme="smart"
                    difficulty="beginner"
                    imageUrl="https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&h=600&fit=crop&crop=center"
                    showCornerCut={true}
                    className="h-96"
                  />
                </div>
              </div>
            </div>
          </Container>
        </SectionWrapper>
      </main>
    </div>
  )
}