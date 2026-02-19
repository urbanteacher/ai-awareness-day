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
      className={`grid grid-cols-3 gap-3 md:gap-6 justify-items-center ${className}`}
    >
      {themeConfigs.map((theme, index) => (
        <motion.div
          key={theme.id}
          initial={{ opacity: 0, scale: 0.8 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ 
            duration: 0.4, 
            delay: 0.1 + (index * 0.1),
            type: "spring",
            stiffness: 200
          }}
          whileHover={{ 
            scale: 1.05,
            transition: { duration: 0.2 }
          }}
          className="flex items-center justify-center transition-all duration-300"
        >
          <PolygonCard
            title={theme.title}
            gradient={theme.gradient}
            size="small"
          />
        </motion.div>
      ))}

      {/* Empty space for 6th position in 3x2 grid */}
      <div className="w-20 h-20 sm:w-24 sm:h-24"></div>
    </motion.div>
  )
}
