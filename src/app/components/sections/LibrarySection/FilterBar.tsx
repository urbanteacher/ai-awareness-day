"use client"

import { useState } from "react"
import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { Search, Filter, X } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"

interface FilterBarProps {
  searchQuery: string
  onSearchChange: (query: string) => void
  selectedCategory: string
  onCategoryChange: (category: string) => void
  selectedDifficulty: string
  onDifficultyChange: (difficulty: string) => void
  className?: string
}

const categories = [
  { id: "all", label: "All Resources" },
  { id: "guides", label: "Guides" },
  { id: "templates", label: "Templates" },
  { id: "tools", label: "Tools" },
  { id: "research", label: "Research" },
  { id: "case-studies", label: "Case Studies" }
]

const difficulties = [
  { id: "all", label: "All Levels" },
  { id: "beginner", label: "Beginner" },
  { id: "intermediate", label: "Intermediate" },
  { id: "advanced", label: "Advanced" }
]

export function FilterBar({
  searchQuery,
  onSearchChange,
  selectedCategory,
  onCategoryChange,
  selectedDifficulty,
  onDifficultyChange,
  className
}: FilterBarProps) {
  const [showFilters, setShowFilters] = useState(false)

  const clearFilters = () => {
    onSearchChange("")
    onCategoryChange("all")
    onDifficultyChange("all")
  }

  const hasActiveFilters = searchQuery || selectedCategory !== "all" || selectedDifficulty !== "all"

  return (
    <div className={cn("space-y-4", className)}>
      {/* Search Bar */}
      <div className="relative">
        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          placeholder="Search resources..."
          value={searchQuery}
          onChange={(e) => onSearchChange(e.target.value)}
          className="pl-10 pr-4"
        />
        {searchQuery && (
          <Button
            variant="ghost"
            size="sm"
            onClick={() => onSearchChange("")}
            className="absolute right-2 top-1/2 transform -translate-y-1/2 h-6 w-6 p-0"
          >
            <X className="h-3 w-3" />
          </Button>
        )}
      </div>

      {/* Filter Toggle */}
      <div className="flex items-center justify-between">
        <Button
          variant="outline"
          onClick={() => setShowFilters(!showFilters)}
          className="flex items-center space-x-2"
        >
          <Filter className="h-4 w-4" />
          <span>Filters</span>
          {hasActiveFilters && (
            <span className="ml-1 px-1.5 py-0.5 text-xs bg-primary text-primary-foreground rounded-full">
              {[searchQuery, selectedCategory !== "all", selectedDifficulty !== "all"].filter(Boolean).length}
            </span>
          )}
        </Button>

        {hasActiveFilters && (
          <Button variant="ghost" size="sm" onClick={clearFilters}>
            Clear all
          </Button>
        )}
      </div>

      {/* Filter Options */}
      <motion.div
        initial={false}
        animate={{ height: showFilters ? "auto" : 0 }}
        transition={{ duration: 0.2 }}
        className="overflow-hidden"
      >
        <div className="space-y-4 pt-4 border-t">
          {/* Category Filters */}
          <div>
            <h4 className="text-sm font-medium mb-2">Category</h4>
            <div className="flex flex-wrap gap-2">
              {categories.map((category) => (
                <Button
                  key={category.id}
                  variant={selectedCategory === category.id ? "default" : "outline"}
                  size="sm"
                  onClick={() => onCategoryChange(category.id)}
                  className="text-xs"
                >
                  {category.label}
                </Button>
              ))}
            </div>
          </div>

          {/* Difficulty Filters */}
          <div>
            <h4 className="text-sm font-medium mb-2">Difficulty</h4>
            <div className="flex flex-wrap gap-2">
              {difficulties.map((difficulty) => (
                <Button
                  key={difficulty.id}
                  variant={selectedDifficulty === difficulty.id ? "default" : "outline"}
                  size="sm"
                  onClick={() => onDifficultyChange(difficulty.id)}
                  className="text-xs"
                >
                  {difficulty.label}
                </Button>
              ))}
            </div>
          </div>
        </div>
      </motion.div>
    </div>
  )
}

