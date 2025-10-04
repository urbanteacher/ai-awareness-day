"use client"

import { useState } from "react"
import Link from "next/link"
import { Button, ThemeToggle, Container } from "@/components/ui"
import { Menu, X } from "lucide-react"

export function Navigation() {
  const [isOpen, setIsOpen] = useState(false)

  return (
    <nav className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <Container className="flex h-20 items-center justify-between pt-2">
        {/* Logo */}
        <div className="flex items-center flex-shrink-0">
          <Link href="/" className="flex flex-col items-start hover:opacity-80 transition-opacity">
            <span className="text-sm font-bold leading-tight">AI</span>
            <span className="text-sm font-bold leading-tight">Awareness</span>
            <span className="text-sm font-bold leading-tight">Day</span>
            <span className="text-xs font-thin leading-tight self-end">2026</span>
          </Link>
        </div>

            {/* Desktop Navigation */}
            <div className="hidden md:flex items-center space-x-8">
              <Link
                href="/design-concept"
                className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
              >
                Design
              </Link>
              <Link
                href="/contact"
                className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
              >
                Contact
              </Link>
            </div>

        {/* Desktop CTA Buttons */}
        <div className="hidden md:flex items-center space-x-3">
          <ThemeToggle />
        </div>

        {/* Mobile Menu Button */}
        <div className="md:hidden flex items-center space-x-2">
          <ThemeToggle />
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setIsOpen(!isOpen)}
            className="p-2"
            aria-label="Toggle menu"
          >
            {isOpen ? <X className="h-4 w-4" /> : <Menu className="h-4 w-4" />}
          </Button>
        </div>
      </Container>

      {/* Mobile Navigation */}
      {isOpen && (
        <div className="md:hidden border-t bg-background">
          <Container className="py-4">
            <div className="flex flex-col space-y-4">
              <Link 
                href="/design-concept" 
                className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors py-2"
                onClick={() => setIsOpen(false)}
              >
                Design
              </Link>
              <Link 
                href="/contact" 
                className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors py-2"
                onClick={() => setIsOpen(false)}
              >
                Contact
              </Link>
            </div>
          </Container>
        </div>
      )}
    </nav>
  )
}
