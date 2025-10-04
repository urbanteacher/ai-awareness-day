"use client"

import { motion } from "framer-motion"
import { Container, SectionWrapper } from "@/components/ui"
import { HeroContent } from "./HeroContent"
import { HeroCTAs } from "./HeroCTAs"

export function HeroSection() {
  return (
        <SectionWrapper className="relative overflow-hidden bg-muted/30 py-0">
          {/* Background gradient */}
          <div className="absolute inset-0 bg-gradient-to-br from-background via-background to-muted/20" />
          
          {/* Decorative elements */}
          <div className="absolute top-0 left-1/4 w-72 h-72 bg-primary/5 rounded-full blur-3xl" />
          <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-accent/5 rounded-full blur-3xl" />
          
          <Container className="relative z-10">
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.8 }}
            >
              <HeroContent />
              <HeroCTAs />
            </motion.div>
          </Container>
        </SectionWrapper>
  )
}

