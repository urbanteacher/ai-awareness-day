# AI Awareness Day Design System Rules

## Overview
This document defines the design system structure and integration patterns for the AI Awareness Day website, specifically for Figma design integration using Model Context Protocol (MCP).

## 1. Design Token Definitions

### 1.1 Color System
**Location**: `src/app/globals.css` (lines 13-56)

The project uses CSS custom properties with HSL values for consistent theming:

```css
:root {
  --background: 0 0% 100%;
  --foreground: 240 10% 3.9%;
  --primary: 240 5.9% 10%;
  --secondary: 240 4.8% 95.9%;
  --muted: 240 4.8% 95.9%;
  --accent: 240 4.8% 95.9%;
  --destructive: 0 84.2% 60.2%;
  --border: 240 5.9% 90%;
  --input: 240 5.9% 90%;
  --ring: 240 5.9% 10%;
  --radius: 0.5rem;
}
```

**Dark Mode Variants**:
```css
.dark {
  --background: 240 10% 3.9%;
  --foreground: 0 0% 98%;
  --primary: 0 0% 98%;
  /* ... additional dark mode tokens */
}
```

**Theme-Specific Colors**:
**Location**: `src/data/themes.ts`
```typescript
export const themeConfigs = [
  { id: "safe", title: "SAFE", gradient: "linear-gradient(135deg, #ef4444, #dc2626)" },
  { id: "smart", title: "SMART", gradient: "linear-gradient(135deg, #3b82f6, #2563eb)" },
  { id: "creative", title: "CREATIVE", gradient: "linear-gradient(135deg, #10b981, #059669)" },
  { id: "responsible", title: "RESPONSIBLE", gradient: "linear-gradient(135deg, #8b5cf6, #7c3aed)" },
  { id: "future", title: "FUTURE", gradient: "linear-gradient(135deg, #f97316, #ea580c)" }
]
```

### 1.2 Typography
**Font System**: Geist Sans (primary), Geist Mono (code)
**Location**: `src/app/globals.css` (lines 80-81)

```css
--font-sans: var(--font-geist-sans);
--font-mono: var(--font-geist-mono);
```

**Typography Scale**:
**Location**: `src/components/ui/sections/SectionHeader.tsx` (lines 13-18)

```typescript
const titleSizeClasses = {
  sm: "text-2xl font-bold sm:text-3xl",
  md: "text-3xl font-bold sm:text-4xl", 
  lg: "text-3xl font-bold sm:text-4xl lg:text-5xl",
  xl: "text-4xl font-bold sm:text-5xl lg:text-6xl"
}
```

### 1.3 Spacing System
**Location**: `src/lib/constants.ts` (lines 33-39)

```typescript
export const SPACING = {
  xs: "py-6 sm:py-8",    // 24px mobile, 32px desktop
  sm: "py-8 sm:py-12",   // 32px mobile, 48px desktop
  md: "py-12 sm:py-16",  // 48px mobile, 64px desktop
  lg: "py-16 sm:py-20",  // 64px mobile, 80px desktop
  xl: "py-20 sm:py-24"   // 80px mobile, 96px desktop
} as const
```

### 1.4 Border Radius
**Global**: `--radius: 0.5rem` (8px)
**Usage**: Applied consistently across all components

## 2. Component Library

### 2.1 Component Architecture
**Location**: `src/components/ui/`

**Base Pattern**: All components follow this structure:
- TypeScript interfaces for props
- `cn()` utility for class merging
- Forward refs for DOM access
- Variant-based styling using `class-variance-authority`

**Example**: `src/components/ui/button.tsx`
```typescript
const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground shadow hover:bg-primary/90",
        destructive: "bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90",
        outline: "border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground",
        secondary: "bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80",
        ghost: "hover:bg-accent hover:text-accent-foreground",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        default: "h-9 px-4 py-2",
        sm: "h-8 rounded-md px-3 text-xs",
        lg: "h-10 rounded-md px-8",
        icon: "h-9 w-9",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  }
)
```

### 2.2 Section Components
**Location**: `src/components/ui/sections/`

**SectionWrapper**: Main layout component
```typescript
interface SectionWrapperProps {
  children: React.ReactNode
  className?: string
  background?: "default" | "muted" | "accent"
  padding?: "xs" | "sm" | "md" | "lg" | "xl"
  id?: string
  variant?: "default" | "gray"
}
```

**SectionHeader**: Consistent header styling
```typescript
interface SectionHeaderProps {
  title: string
  subtitle?: string
  description?: string
  className?: string
  align?: "left" | "center" | "right"
  titleSize?: "sm" | "md" | "lg" | "xl"
  titleColor?: "default" | "purple" | "blue" | "green" | "orange" | "red"
}
```

### 2.3 Component Exports
**Location**: `src/components/ui/index.ts`

All components are centrally exported for easy importing:
```typescript
export { Button } from './button'
export { Badge } from './badge'
export { Card, CardContent, CardDescription, CardHeader, CardTitle } from './card'
export { SectionWrapper } from './sections/SectionWrapper'
export { Container } from './sections/Container'
export { SectionHeader } from './sections/SectionHeader'
// ... additional exports
```

## 3. Frameworks & Libraries

### 3.1 Core Framework
- **React**: 19.1.0 with TypeScript
- **Next.js**: 15.4.4 (App Router)
- **Build System**: Turbopack (dev), Webpack (production)

### 3.2 Styling Framework
- **Tailwind CSS**: v4 with PostCSS
- **Class Variance Authority**: Component variant management
- **Tailwind Merge**: Class conflict resolution
- **CLSX**: Conditional class names

### 3.3 UI Libraries
- **Radix UI**: Headless component primitives
- **Lucide React**: Icon system
- **Framer Motion**: Animations
- **Next Themes**: Dark mode support

### 3.4 Utility Libraries
- **Sonner**: Toast notifications
- **React Hook Form**: Form management

## 4. Asset Management

### 4.1 Image Assets
**Location**: `public/` directory
- **Logos**: `public/logos/` (PNG, JPG formats)
- **Polygon Shapes**: `public/polygon-shapes/` (SVG format)
- **General Assets**: `public/` root

**Usage Pattern**:
```typescript
// Static imports for critical images
import logo from '/public/logos/logo.png'

// Dynamic imports for non-critical images
<img src={`/polygon-shapes/${shapeName}.svg`} alt="Shape" />
```

### 4.2 Asset Optimization
- **Next.js Image Optimization**: Automatic WebP conversion
- **Content Visibility**: `content-visibility: auto` for performance
- **Lazy Loading**: Implemented for below-the-fold content

## 5. Icon System

### 5.1 Icon Library
**Library**: Lucide React (v0.526.0)
**Location**: Imported as needed in components

**Usage Pattern**:
```typescript
import { Shield, Brain, Heart, Award, Zap } from "lucide-react"

// In component
<Shield className="w-6 h-6" />
```

### 5.2 Icon Styling
- **Consistent Sizing**: `w-4 h-4`, `w-5 h-5`, `w-6 h-6`
- **Color Inheritance**: Icons inherit text color by default
- **Pointer Events**: Disabled for interactive elements

## 6. Styling Approach

### 6.1 CSS Methodology
**Primary**: Tailwind CSS utility classes
**Secondary**: CSS custom properties for theming
**Component Level**: Class Variance Authority for variants

### 6.2 Global Styles
**Location**: `src/app/globals.css`

**Key Features**:
- CSS custom properties for theming
- Dark mode support
- Accessibility enhancements
- Performance optimizations
- Focus management

### 6.3 Responsive Design
**Mobile-First Approach**:
```typescript
// Example from SectionWrapper
const paddingClasses = {
  xs: "py-6 sm:py-8",      // Mobile: 24px, Desktop: 32px
  sm: "py-8 sm:py-12",     // Mobile: 32px, Desktop: 48px
  md: "py-12 sm:py-16",    // Mobile: 48px, Desktop: 64px
  lg: "py-16 sm:py-20",    // Mobile: 64px, Desktop: 80px
  xl: "py-20 sm:py-24"     // Mobile: 80px, Desktop: 96px
}
```

**Breakpoints**:
```typescript
export const BREAKPOINTS = {
  sm: 640,   // Small devices
  md: 768,   // Medium devices  
  lg: 1024,  // Large devices
  xl: 1280,  // Extra large devices
  "2xl": 1536 // 2X large devices
} as const
```

## 7. Project Structure

### 7.1 Directory Organization
```
src/
├── app/                    # Next.js App Router
│   ├── components/sections/ # Page sections
│   ├── contact/            # Contact page
│   ├── design-concept/     # Design concept page
│   ├── design-demo/        # Design demo page
│   ├── display-board-demo/ # Display board demo
│   ├── inclusion-guide/    # Inclusion guide page
│   ├── globals.css         # Global styles
│   ├── layout.tsx          # Root layout
│   └── page.tsx            # Home page
├── components/             # Reusable components
│   ├── ui/                # Base UI components
│   │   ├── sections/      # Section-specific components
│   │   └── index.ts       # Component exports
│   ├── navigation.tsx     # Navigation component
│   └── theme-provider.tsx # Theme management
├── data/                  # Static data
│   ├── activities/        # Activity data
│   └── themes.ts          # Theme configurations
└── lib/                   # Utilities
    ├── constants.ts       # App constants
    └── utils.ts           # Utility functions
```

### 7.2 Feature Organization
**Sections**: Each major feature is a section component
- **Location**: `src/app/components/sections/`
- **Pattern**: `{FeatureName}Section/index.tsx`
- **Sub-components**: Co-located in same directory

**Examples**:
- `HeroSection/` - Landing hero
- `ActivitiesSection/` - Interactive activities
- `LibrarySection/` - Resource library
- `DisplayBoardSection/` - Display board mockup

## 8. Figma Integration Guidelines

### 8.1 Design Token Mapping
When integrating Figma designs:

1. **Colors**: Map to CSS custom properties in `globals.css`
2. **Typography**: Use existing size classes or extend `titleSizeClasses`
3. **Spacing**: Use `SPACING` constants or Tailwind spacing scale
4. **Border Radius**: Use `--radius` (0.5rem) or Tailwind radius classes

### 8.2 Component Creation Pattern
```typescript
// 1. Define interface
interface NewComponentProps {
  variant?: "default" | "secondary"
  size?: "sm" | "md" | "lg"
  className?: string
}

// 2. Define variants using CVA
const componentVariants = cva(
  "base-classes",
  {
    variants: {
      variant: {
        default: "default-styles",
        secondary: "secondary-styles"
      },
      size: {
        sm: "small-styles",
        md: "medium-styles", 
        lg: "large-styles"
      }
    },
    defaultVariants: {
      variant: "default",
      size: "md"
    }
  }
)

// 3. Implement component
export function NewComponent({ variant, size, className, ...props }: NewComponentProps) {
  return (
    <div className={cn(componentVariants({ variant, size }), className)} {...props}>
      {/* Component content */}
    </div>
  )
}
```

### 8.3 Dark Mode Considerations
- Always include `dark:` variants for colors
- Test both light and dark modes
- Use semantic color tokens (`--background`, `--foreground`) when possible
- Follow existing patterns in `SectionHeader.tsx` for color variants

### 8.4 Responsive Design
- Use mobile-first approach
- Follow existing breakpoint patterns
- Test on multiple screen sizes
- Use Tailwind responsive prefixes (`sm:`, `md:`, `lg:`, `xl:`)

## 9. Animation Guidelines

### 9.1 Animation Library
**Framer Motion**: Used for complex animations
**CSS Transitions**: Used for simple hover states

### 9.2 Animation Constants
**Location**: `src/lib/constants.ts`
```typescript
export const ANIMATION_DURATION = {
  fast: 150,    // 150ms
  normal: 300,  // 300ms
  slow: 500     // 500ms
} as const
```

### 9.3 Accessibility
- Respect `prefers-reduced-motion`
- Provide focus indicators
- Ensure animations don't interfere with usability

## 10. Performance Considerations

### 10.1 Lazy Loading
- Sections below the fold are lazy-loaded
- Images use `content-visibility: auto`
- Components use React.lazy() with Suspense

### 10.2 Bundle Optimization
- Tree-shaking enabled
- Dynamic imports for non-critical code
- Image optimization via Next.js

This design system provides a solid foundation for integrating Figma designs while maintaining consistency, accessibility, and performance across the AI Awareness Day website.
