"use client"

import { motion } from "framer-motion"
import { PolygonGrid } from "./PolygonGrid"
import Link from "next/link"

export function HeroContent() {
  return (
    <>
      <style jsx>{`
        @keyframes gradient-shift {
          0%, 100% {
            background-position: 0% 50%;
          }
          50% {
            background-position: 100% 50%;
          }
        }
      `}</style>
      
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
        {/* Left Section - Text and Buttons */}
        <motion.div
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.6 }}
        >
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.1 }}
                className="relative"
              >
                <div className="flex flex-col items-start">
                  <motion.span 
                    className="text-5xl md:text-7xl font-bold leading-tight"
                    whileHover={{ scale: 1.05, rotate: 1 }}
                    transition={{ type: "spring", stiffness: 300, damping: 10 }}
                  >
                    AI
                  </motion.span>
                  <motion.span 
                    className="text-5xl md:text-7xl font-bold leading-tight"
                    whileHover={{ scale: 1.05, rotate: -1 }}
                    transition={{ type: "spring", stiffness: 300, damping: 10 }}
                  >
                    AWARENESS
                  </motion.span>
                  <motion.span 
                    className="text-5xl md:text-7xl font-bold leading-tight"
                    whileHover={{ scale: 1.05, rotate: 1 }}
                    transition={{ type: "spring", stiffness: 300, damping: 10 }}
                  >
                    DAY
                  </motion.span>
                </div>
                <motion.span 
                  className="absolute bottom-3 right-32 text-2xl md:text-4xl font-thin leading-tight text-muted-foreground"
                  whileHover={{ scale: 1.1, y: -5 }}
                  transition={{ type: "spring", stiffness: 400, damping: 10 }}
                >
                  2026
                </motion.span>
              </motion.div>
        
              <motion.p
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.2 }}
                className="text-2xl md:text-3xl font-semibold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent"
                style={{ fontFamily: 'Inter, sans-serif' }}
              >
                Know it, Question it, Use it Wisely
              </motion.p>
          
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.3 }}
            className="text-lg md:text-xl text-muted-foreground leading-relaxed"
            style={{ fontFamily: 'Inter, sans-serif' }}
          >
            A nationwide campaign equipping students with critical AI skills through engaging activities, 
            ethical discussions, and creative challenges.
          </motion.p>

        </motion.div>

        {/* Right Section - Polygon Grid - Hidden on mobile */}
        <div className="hidden lg:flex justify-end">
          <PolygonGrid />
        </div>
      </div>

      {/* Mobile Polygon Grid - Show at bottom on mobile */}
      <div className="flex justify-center mt-8 lg:hidden">
        <PolygonGrid />
      </div>
    </>
  )
}
