// Application Constants
// Centralized constants for consistent values across the application

export const APP_CONFIG = {
  name: "AI Awareness Day",
  description: "Join the national campaign for AI awareness in schools",
  url: "https://aiawarenessday.co.uk",
  email: "info@aiawarenessday.co.uk"
} as const

export const THEME_COLORS = {
  safe: "red",
  smart: "blue", 
  creative: "green",
  responsible: "purple",
  future: "orange"
} as const

export const BREAKPOINTS = {
  sm: 640,
  md: 768,
  lg: 1024,
  xl: 1280,
  "2xl": 1536
} as const

export const ANIMATION_DURATION = {
  fast: 150,
  normal: 300,
  slow: 500
} as const

export const SPACING = {
  xs: "py-6 sm:py-8",
  sm: "py-8 sm:py-12", 
  md: "py-12 sm:py-16",
  lg: "py-16 sm:py-20",
  xl: "py-20 sm:py-24"
} as const
