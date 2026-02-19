"use client"

import { motion } from "framer-motion"

export function HeroCTAs() {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.8, delay: 0.6 }}
      className="space-y-8"
    >
      {/* Empty - buttons and stats removed */}
    </motion.div>
  )
}
