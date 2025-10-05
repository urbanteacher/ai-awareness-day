import { motion } from "framer-motion"
import { PolygonCard } from "@/components/ui/polygon-card"
import { themeConfigs } from "@/data/themes"

interface PolygonGridProps {
  className?: string
}

export function PolygonGrid({ className = "" }: PolygonGridProps) {
  return (
    <motion.div
      initial={{ opacity: 0, x: 20 }}
      animate={{ opacity: 1, x: 0 }}
      transition={{ duration: 0.6, delay: 0.2 }}
      className={`grid grid-cols-3 gap-4 ${className}`}
    >
      {themeConfigs.map((theme, index) => (
        <PolygonCard
          key={theme.id}
          title={theme.title}
          gradient={theme.gradient}
        />
      ))}

      {/* Empty space for 6th position */}
      <div className="w-40 h-40"></div>
    </motion.div>
  )
}
