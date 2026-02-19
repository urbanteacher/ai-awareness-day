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
      className={`grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 justify-items-center ${className}`}
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

      {/* Empty space for 6th position */}
      <div className="w-24 h-24 md:w-32 md:h-32"></div>
    </motion.div>
  )
}
