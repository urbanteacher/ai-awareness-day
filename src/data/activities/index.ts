import { Clock, Users, Presentation, BookOpen } from "lucide-react"

export const activityLibraries = [
  {
    id: "lesson-starters",
    title: "Lesson Starters",
    description: "Quick 5-minute AI discussions to kick off any lesson",
    duration: "5 min",
    color: "from-blue-500 to-blue-600",
    icon: Clock,
    activities: [
      {
        id: "1",
        title: "AI in Daily Life",
        description: "Discuss how AI is already part of students' daily routines",
        theme: "smart",
        level: "Beginner",
        subject: "General",
        tags: ["discussion", "everyday", "awareness"]
      },
      {
        id: "2", 
        title: "What is AI?",
        description: "Simple explanation of artificial intelligence concepts",
        theme: "smart",
        level: "Beginner",
        subject: "Computing",
        tags: ["basics", "definition", "concepts"]
      },
      {
        id: "3",
        title: "AI vs Human Intelligence",
        description: "Compare and contrast AI capabilities with human thinking",
        theme: "smart",
        level: "Intermediate",
        subject: "Philosophy",
        tags: ["comparison", "critical thinking", "analysis"]
      },
      {
        id: "4",
        title: "AI in Healthcare",
        description: "Explore how AI is revolutionizing medical diagnosis and treatment",
        theme: "smart",
        level: "Intermediate",
        subject: "Science",
        tags: ["healthcare", "medicine", "technology"]
      }
    ]
  },
  {
    id: "tutor-time",
    title: "Tutor Time",
    description: "15-20 minute group activities for form time",
    duration: "15-20 min",
    color: "from-green-500 to-green-600",
    icon: Users,
    activities: [
      {
        id: "5",
        title: "AI Ethics Debate",
        description: "Explore the ethical implications of AI technology",
        theme: "responsible",
        level: "Intermediate",
        subject: "PSHE",
        tags: ["ethics", "debate", "critical thinking"]
      },
      {
        id: "6",
        title: "AI and Privacy",
        description: "Discuss how AI affects personal privacy and data protection",
        theme: "safe",
        level: "Intermediate",
        subject: "PSHE",
        tags: ["privacy", "safety", "data protection"]
      },
      {
        id: "7",
        title: "Future Jobs and AI",
        description: "Explore how AI might change the job market and careers",
        theme: "future",
        level: "Advanced",
        subject: "Careers",
        tags: ["careers", "future", "employment"]
      },
      {
        id: "8",
        title: "AI in Art and Creativity",
        description: "Examine how AI is being used in creative fields like art and music",
        theme: "creative",
        level: "Intermediate",
        subject: "Art",
        tags: ["creativity", "art", "music", "innovation"]
      }
    ]
  },
  {
    id: "assemblies",
    title: "Assemblies",
    description: "20-minute whole-school presentations",
    duration: "20 min",
    color: "from-purple-500 to-purple-600",
    icon: Presentation,
    activities: [
      {
        id: "9",
        title: "AI Safety Assembly",
        description: "School-wide presentation on AI safety and responsible use",
        theme: "safe",
        level: "All",
        subject: "Safeguarding",
        tags: ["safety", "assembly", "whole school"]
      },
      {
        id: "10",
        title: "The Future of AI",
        description: "Inspirational assembly about AI's potential to solve global challenges",
        theme: "future",
        level: "All",
        subject: "General",
        tags: ["future", "inspiration", "global challenges"]
      },
      {
        id: "11",
        title: "AI and Climate Change",
        description: "How AI is being used to address environmental issues",
        theme: "responsible",
        level: "All",
        subject: "Geography",
        tags: ["environment", "climate", "sustainability"]
      }
    ]
  },
  {
    id: "after-school-clubs",
    title: "After-School Clubs",
    description: "30-45 minute hands-on projects and activities",
    duration: "30-45 min",
    color: "from-orange-500 to-orange-600",
    icon: BookOpen,
    activities: [
      {
        id: "12",
        title: "Build a Simple Chatbot",
        description: "Hands-on workshop to create a basic conversational AI",
        theme: "creative",
        level: "Advanced",
        subject: "Computing",
        tags: ["programming", "hands-on", "chatbot"]
      },
      {
        id: "13",
        title: "AI Art Workshop",
        description: "Create artwork using AI tools and discuss the creative process",
        theme: "creative",
        level: "Intermediate",
        subject: "Art",
        tags: ["art", "creativity", "AI tools"]
      },
      {
        id: "14",
        title: "AI Research Project",
        description: "Independent research project on a specific AI application",
        theme: "smart",
        level: "Advanced",
        subject: "Research",
        tags: ["research", "independent", "presentation"]
      }
    ]
  },
  {
    id: "cross-curricular",
    title: "Cross-Curricular Integration",
    description: "40-minute subject-specific AI applications",
    duration: "40 min",
    color: "from-indigo-500 to-indigo-600",
    icon: BookOpen,
    activities: [
      {
        id: "15",
        title: "AI in Mathematics",
        description: "Explore how AI uses mathematical concepts and algorithms",
        theme: "smart",
        level: "Intermediate",
        subject: "Mathematics",
        tags: ["mathematics", "algorithms", "problem solving"]
      },
      {
        id: "16",
        title: "AI in English Literature",
        description: "Analyze how AI might interpret and analyze literary texts",
        theme: "creative",
        level: "Intermediate",
        subject: "English",
        tags: ["literature", "analysis", "interpretation"]
      },
      {
        id: "17",
        title: "AI in History",
        description: "Examine how AI is used to analyze historical data and patterns",
        theme: "smart",
        level: "Intermediate",
        subject: "History",
        tags: ["history", "data analysis", "patterns"]
      },
      {
        id: "18",
        title: "AI in Science Experiments",
        description: "Design experiments that use AI to analyze scientific data",
        theme: "smart",
        level: "Advanced",
        subject: "Science",
        tags: ["experiments", "data analysis", "scientific method"]
      }
    ]
  }
]

export const themes = [
  { id: "all", name: "All Themes", color: "bg-gray-600" },
  { id: "safe", name: "BE SAFE", color: "bg-red-500" },
  { id: "smart", name: "BE SMART", color: "bg-blue-500" },
  { id: "creative", name: "BE CREATIVE", color: "bg-green-500" },
  { id: "responsible", name: "BE RESPONSIBLE", color: "bg-purple-500" },
  { id: "future", name: "BE FUTURE", color: "bg-orange-500" }
]
