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

// Tailwind class mappings for themed surfaces used across the app
export const themeTw = {
  safe: {
    headerBg: "bg-red-500",
    border: "border-red-500",
  },
  smart: {
    headerBg: "bg-blue-500",
    border: "border-blue-500",
  },
  creative: {
    headerBg: "bg-green-500",
    border: "border-green-500",
  },
  responsible: {
    headerBg: "bg-purple-500",
    border: "border-purple-500",
  },
  future: {
    headerBg: "bg-orange-500",
    border: "border-orange-500",
  },
} as const
