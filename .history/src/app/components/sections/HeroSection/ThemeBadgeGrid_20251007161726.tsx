"use client"

import { motion, type Variants } from "framer-motion"
import { cn } from "@/lib/utils"
import { themeConfigs } from "@/data/themes"

const themes = themeConfigs.map((cfg) => ({
  name: cfg.title,
  initial: cfg.title.charAt(0),
  solidHex: cfg.solidHex,
  description: {
    SAFE: "Ethical AI practices",
    SMART: "Intelligent solutions",
    CREATIVE: "Innovative approaches",
    RESPONSIBLE: "Accountable development",
    FUTURE: "Forward-thinking vision",
  }[cfg.title as "SAFE" | "SMART" | "CREATIVE" | "RESPONSIBLE" | "FUTURE"],
}))

export function ThemeBadgeGrid() {
  const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.15,
      },
    },
  }

  const itemVariants: Variants = {
    hidden: { opacity: 0, y: 30, scale: 0.8 },
    visible: { 
      opacity: 1, 
      y: 0, 
      scale: 1,
      transition: { 
        duration: 0.6,
        type: "spring",
        stiffness: 100
      } 
    },
  }

  return (
    <motion.div
      className="flex flex-wrap justify-center gap-8 max-w-5xl mx-auto"
      variants={containerVariants}
      initial="hidden"
      whileInView="visible"
      viewport={{ once: true, amount: 0.3 }}
    >
      {themes.map((theme, index) => (
        <motion.div
          key={theme.name}
          className="group relative"
          variants={itemVariants}
          whileHover={{ scale: 1.05 }}
          whileTap={{ scale: 0.95 }}
        >
          {/* Hexagonal Badge */}
          <div className="relative w-24 h-24 flex items-center justify-center">
            {/* Hexagon Shape */}
            <div 
              className={cn(
                "w-24 h-24 flex items-center justify-center transition-all duration-300 group-hover:shadow-xl"
              )}
              style={{
                clipPath: 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)',
                backgroundColor: theme.solidHex,
              }}
            >
              <span className="text-white font-black text-2xl">
                {theme.initial}
              </span>
            </div>
            
            {/* Hover Effect Overlay */}
            <div 
              className="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
              style={{
                clipPath: 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)'
              }}
            />
          </div>
          
          {/* Theme Name */}
          <div className="text-center mt-3">
            <h3 className="font-bold text-sm text-foreground group-hover:text-primary transition-colors">
              {theme.name}
            </h3>
            <p className="text-xs text-muted-foreground mt-1 leading-tight max-w-20">
              {theme.description}
            </p>
          </div>
        </motion.div>
      ))}
    </motion.div>
  )
}
