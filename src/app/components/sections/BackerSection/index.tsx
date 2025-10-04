"use client"

import { motion } from "framer-motion"
import { Container, SectionWrapper } from "@/components/ui"
import { BackerLogos } from "./BackerLogos"

/**
 * BackerSection Component
 * 
 * Displays partner organization logos and reach statistics.
 * This section is separate from the HeroSection to reduce visual clutter.
 * 
 * Structure:
 * - Container: Provides consistent horizontal spacing
 * - BackerLogos: Renders the logo grid and statistics
 * 
 * Note: SectionWrapper is applied at the page level for consistent styling
 * and to avoid double padding issues.
 */
export default function BackerSection() {
  return (
    <Container>
      <BackerLogos />
    </Container>
  )
}
