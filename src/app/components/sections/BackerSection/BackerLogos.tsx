"use client"

import { motion } from "framer-motion"
import Image from "next/image"

/**
 * BackerLogos Component
 * 
 * Displays partner organization logos in a responsive grid layout.
 * Features:
 * - 3 logos per row on desktop, responsive on mobile
 * - Hover animations and loading states
 * - Fallback text display if images fail to load
 * - Reach statistics displayed below logos
 * 
 * Layout:
 * - Grid: 3 columns on desktop, responsive on smaller screens
 * - Animation: Staggered entrance with spring physics
 * - Hover: Scale effect on individual logos
 */

// Partner organization data with image specifications
const backerLogos = [
  {
    name: "Apps for Good",
    src: "/logos/appsforgood.png",
    alt: "Apps for Good logo",
    width: 120,
    height: 60
  },
  {
    name: "Computing at School",
    src: "/logos/cas.png",
    alt: "Computing at School logo", 
    width: 100,
    height: 60
  },
  {
    name: "E-ACT",
    src: "/logos/e-act.jpg",
    alt: "E-ACT logo",
    width: 120,
    height: 50
  },
  {
    name: "UK Business Tech",
    src: "/logos/ukbt.jpg",
    alt: "UK Business Tech logo",
    width: 110,
    height: 50
  },
  {
    name: "Unthinkable",
    src: "/logos/unthinkable.webp",
    alt: "Unthinkable logo",
    width: 100,
    height: 40
  },
  {
    name: "Tech London Advocates",
    src: "/logos/tla-1.jpg",
    alt: "Tech London Advocates logo",
    width: 120,
    height: 50
  }
]

export function BackerLogos() {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.6 }}
      className="text-center"
    >
      {/* Section header */}
      <p className="text-center text-xl md:text-2xl text-muted-foreground mb-6">
        Supported by leading education and technology organizations
      </p>
      
      {/* Logo grid - 3 columns on desktop, responsive on mobile */}
      <div className="grid grid-cols-3 gap-8 justify-items-center opacity-60 hover:opacity-80 transition-opacity duration-300">
        {backerLogos.map((logo, index) => (
          <motion.div
            key={logo.name}
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ 
              duration: 0.4, 
              delay: 0.1 + (index * 0.1),  // Staggered animation delay
              type: "spring",
              stiffness: 200
            }}
            whileHover={{ 
              scale: 1.05,
              transition: { duration: 0.2 }
            }}
            className="flex items-center justify-center transition-all duration-300"
          >
            {/* Logo image with fallback handling */}
            {logo.src ? (
              <Image
                src={logo.src}
                alt={logo.alt}
                width={logo.width}
                height={logo.height}
                className="object-contain transition-all duration-300"
                onError={(e) => {
                  // Fallback to text if image fails to load
                  const target = e.target as HTMLImageElement;
                  target.style.display = 'none';
                  const parent = target.parentElement;
                  if (parent) {
                    parent.innerHTML = `<span class="text-xs font-medium text-muted-foreground text-center">${logo.name}</span>`;
                  }
                }}
              />
            ) : (
              // Placeholder for missing logos
              <div className="flex flex-col items-center justify-center text-center">
                <div className="w-8 h-8 mb-2 rounded bg-muted/50 flex items-center justify-center">
                  <svg 
                    className="w-4 h-4 text-muted-foreground" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path 
                      strokeLinecap="round" 
                      strokeLinejoin="round" 
                      strokeWidth={2} 
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" 
                    />
                  </svg>
                </div>
                <span className="text-xs font-medium text-muted-foreground leading-tight">
                  {logo.name}
                </span>
              </div>
            )}
          </motion.div>
        ))}
      </div>
      
      {/* Reach statistics - displayed below the logo grid */}
      <motion.p
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6, delay: 0.8 }}
        className="text-center text-xl md:text-2xl text-muted-foreground mt-8"
      >
        <span className="font-bold text-4xl md:text-3xl text-foreground">590,000+ reach</span> across confirmed interested partners
      </motion.p>
    </motion.div>
  )
}

