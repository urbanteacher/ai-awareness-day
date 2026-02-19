import { Suspense, lazy } from 'react'
import { Navigation } from "@/components/navigation"
import { HeroSection } from "./components/sections/HeroSection"
import { SectionWrapper, Container } from "@/components/ui"

/**
 * Lazy Loading Strategy:
 * - Above the fold: HeroSection loads immediately for fast initial render
 * - Below the fold: All other sections are lazy loaded to improve performance
 * - Each section is wrapped in Suspense with a loading skeleton
 */
const BackerSection = lazy(() => import('./components/sections/BackerSection'))
const ImplementationSection = lazy(() => import('./components/sections/ImplementationSection'))
const CommunicationsSection = lazy(() => import('./components/sections/CommunicationsSection'))
const ActivitiesSection = lazy(() => import('./components/sections/ActivitiesSection'))
const LibrarySection = lazy(() => import('./components/sections/LibrarySection'))
const DisplayBoardMockup = lazy(() => import('./components/sections/DisplayBoardSection/DisplayBoardMockup'))
const MarketingSection = lazy(() => import('./components/sections/MarketingSection'))
const ToolsSection = lazy(() => import('./components/sections/ToolsSection'))

/**
 * Loading Skeleton Component
 * 
 * Displays while lazy-loaded sections are loading to prevent layout shift.
 * Mimics the typical structure of sections with:
 * - Header area (title + description placeholders)
 * - Content grid (6 placeholder cards)
 * - Smooth pulse animation for better UX
 */
function SectionSkeleton() {
  return (
    <div className="w-full py-20 sm:py-24">
      <Container>
        <div className="space-y-8">
          {/* Header skeleton */}
          <div className="text-center space-y-4">
            <div className="h-8 bg-muted rounded-lg w-64 mx-auto animate-pulse" />
            <div className="h-4 bg-muted rounded w-96 mx-auto animate-pulse" />
          </div>
          {/* Content grid skeleton */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="h-64 bg-muted rounded-lg animate-pulse" />
            ))}
          </div>
        </div>
      </Container>
    </div>
  )
}

/**
 * Main Home Page Component
 * 
 * Layout Structure:
 * 1. Navigation - Fixed header with authentication
 * 2. Hero Section - Above the fold, loads immediately
 * 3. Lazy-loaded sections - Below the fold for performance
 * 
 * Color Pattern:
 * - White sections: Backer, Implementation, Activities, Display Board, Tools
 * - Gray sections: Communications, Library, Marketing
 * - Alternating pattern creates visual rhythm
 */
export default function Home() {
  return (
    <div className="min-h-screen bg-background">
      {/* Navigation - Fixed header */}
      <Navigation />
      
      <main>
        {/* HERO SECTION - Above the fold, loads immediately for fast initial render */}
        <HeroSection />
        
        {/* LAZY-LOADED SECTIONS - Below the fold for performance optimization */}
        <Suspense fallback={<SectionSkeleton />}>
          {/* BACKER SECTION - Partner logos and reach statistics */}
          <section className="w-full !pt-12 !pb-4 bg-gray-50 dark:bg-gray-900" style={{ backgroundColor: 'var(--gray-50)' }}>
            <Container>
              <BackerSection />
            </Container>
          </section>
          
          {/* IMPLEMENTATION SECTION - Step-by-step guide */}
          <SectionWrapper id="implementation" className="bg-white dark:bg-gray-800" padding="sm">
            <ImplementationSection />
          </SectionWrapper>
          
          {/* COMMUNICATIONS SECTION - Marketing materials and templates */}
          <SectionWrapper id="communications" className="bg-gray-50 dark:bg-gray-900" padding="sm">
            <CommunicationsSection />
          </SectionWrapper>
          
          {/* ACTIVITIES SECTION - Interactive activities and resources */}
          <SectionWrapper id="activities" className="bg-white dark:bg-gray-800" padding="sm">
            <ActivitiesSection />
          </SectionWrapper>
          
          {/* LIBRARY SECTION - Resource library and downloads */}
          <SectionWrapper id="library" className="bg-gray-50 dark:bg-gray-900" padding="sm">
            <LibrarySection />
          </SectionWrapper>
          
          {/* DISPLAY BOARD SECTION - Visual mockup and examples */}
          <SectionWrapper id="display-board" className="bg-white dark:bg-gray-800" padding="sm">
            <DisplayBoardMockup />
          </SectionWrapper>
          
          {/* MARKETING SECTION - Social media and promotional content */}
          <SectionWrapper id="marketing" className="bg-gray-50 dark:bg-gray-900" padding="sm">
            <MarketingSection />
          </SectionWrapper>
          
          {/* TOOLS SECTION - Additional tools and utilities */}
          <SectionWrapper id="tools" className="bg-white dark:bg-gray-800" padding="sm">
            <ToolsSection />
          </SectionWrapper>
        </Suspense>
      </main>
    </div>
  )
}