export const themeConfigs = [
  {
    id: "safe",
    title: "SAFE",
    gradient: "linear-gradient(135deg, #ef4444, #dc2626)",
  },
  {
    id: "smart", 
    title: "SMART",
    gradient: "linear-gradient(135deg, #3b82f6, #2563eb)",
  },
  {
    id: "creative",
    title: "CREATIVE", 
    gradient: "linear-gradient(135deg, #10b981, #059669)",
  },
  {
    id: "responsible",
    title: "RESPONSIBLE",
    gradient: "linear-gradient(135deg, #8b5cf6, #7c3aed)",
  },
  {
    id: "future",
    title: "FUTURE",
    gradient: "linear-gradient(135deg, #f97316, #ea580c)",
  },
]

// Centralized Tailwind color classes for theme usage
export const themeTw: Record<string, { headerBg: string; border: string; dot: string; gradientFrom: string; gradientTo: string }> = {
  safe: {
    headerBg: "bg-red-500",
    border: "border-red-500",
    dot: "bg-red-500",
    gradientFrom: "from-red-500",
    gradientTo: "to-red-600",
  },
  smart: {
    headerBg: "bg-blue-500",
    border: "border-blue-500",
    dot: "bg-blue-500",
    gradientFrom: "from-blue-500",
    gradientTo: "to-blue-600",
  },
  creative: {
    headerBg: "bg-green-500",
    border: "border-green-500",
    dot: "bg-green-500",
    gradientFrom: "from-green-500",
    gradientTo: "to-green-600",
  },
  responsible: {
    headerBg: "bg-purple-500",
    border: "border-purple-500",
    dot: "bg-purple-500",
    gradientFrom: "from-purple-500",
    gradientTo: "to-purple-600",
  },
  future: {
    headerBg: "bg-orange-500",
    border: "border-orange-500",
    dot: "bg-orange-500",
    gradientFrom: "from-orange-500",
    gradientTo: "to-orange-600",
  },
}
