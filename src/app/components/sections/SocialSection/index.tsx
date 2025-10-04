"use client"

import { Container, SectionHeader } from "@/components/ui"
import { SocialCard } from "./SocialCard"
import { Button } from "@/components/ui/button"
import { MessageSquare, Users, TrendingUp } from "lucide-react"

const socialPosts = [
  {
    id: "1",
    author: {
      name: "Dr. Sarah Chen",
      avatar: "/avatars/sarah.jpg",
      role: "AI Ethics Researcher"
    },
    content: "Just finished an amazing workshop on AI bias detection. The community engagement was incredible! We discussed real-world applications and challenges. Looking forward to the next session.",
    timestamp: "2 hours ago",
    likes: 24,
    comments: 8,
    shares: 3,
    isLiked: true,
    category: "Workshop",
    imageUrl: "/images/workshop-1.jpg"
  },
  {
    id: "2",
    author: {
      name: "AI Community Team",
      avatar: "/avatars/community.jpg",
      role: "Community Manager"
    },
    content: "🚀 Exciting news! Our AI Awareness Day 2026 platform has reached 1,000+ active users. Thank you to everyone who's been part of this journey. Let's continue building a responsible AI future together!",
    timestamp: "5 hours ago",
    likes: 67,
    comments: 15,
    shares: 12,
    isLiked: false,
    category: "Announcement"
  },
  {
    id: "3",
    author: {
      name: "Marcus Johnson",
      avatar: "/avatars/marcus.jpg",
      role: "Tech Lead"
    },
    content: "Sharing some insights from our recent AI safety assessment. The key takeaway: transparency and accountability are crucial in AI development. What are your thoughts on implementing AI governance frameworks?",
    timestamp: "1 day ago",
    likes: 42,
    comments: 23,
    shares: 7,
    isLiked: true,
    category: "Discussion"
  },
  {
    id: "4",
    author: {
      name: "Elena Rodriguez",
      avatar: "/avatars/elena.jpg",
      role: "UX Designer"
    },
    content: "Designing AI interfaces that prioritize user understanding and control. Here's a quick sketch of our new AI explanation dashboard concept. Feedback welcome!",
    timestamp: "2 days ago",
    likes: 31,
    comments: 11,
    shares: 5,
    isLiked: false,
    category: "Design",
    imageUrl: "/images/dashboard-sketch.jpg"
  },
  {
    id: "5",
    author: {
      name: "Prof. David Kim",
      avatar: "/avatars/david.jpg",
      role: "Computer Science Professor"
    },
    content: "Teaching AI ethics to my students this semester has been eye-opening. The next generation of developers is thinking critically about AI's impact on society. Proud to be part of this educational journey.",
    timestamp: "3 days ago",
    likes: 89,
    comments: 34,
    shares: 18,
    isLiked: true,
    category: "Education"
  },
  {
    id: "6",
    author: {
      name: "Lisa Wang",
      avatar: "/avatars/lisa.jpg",
      role: "Product Manager"
    },
    content: "Just launched our new AI transparency features! Users can now see how AI decisions are made and provide feedback. This is a small step toward more responsible AI, but every step counts.",
    timestamp: "4 days ago",
    likes: 56,
    comments: 19,
    shares: 9,
    isLiked: false,
    category: "Product Update"
  }
]

export default function SocialSection() {
  return (
    <Container>
      <div className="space-y-16">
        <SectionHeader
          title="Community Feed"
          subtitle="Connect & Share"
          description="Join the conversation and stay connected with the AI awareness community through our social platform."
          align="center"
        />
        
        {/* Community Stats */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="text-center space-y-2 p-6 rounded-lg border bg-card">
            <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mx-auto">
              <Users className="h-6 w-6 text-primary" />
            </div>
            <h3 className="text-2xl font-bold">1,247</h3>
            <p className="text-sm text-muted-foreground">Active Members</p>
          </div>
          
          <div className="text-center space-y-2 p-6 rounded-lg border bg-card">
            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto">
              <MessageSquare className="h-6 w-6 text-green-600" />
            </div>
            <h3 className="text-2xl font-bold">3,456</h3>
            <p className="text-sm text-muted-foreground">Discussions</p>
          </div>
          
          <div className="text-center space-y-2 p-6 rounded-lg border bg-card">
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto">
              <TrendingUp className="h-6 w-6 text-blue-600" />
            </div>
            <h3 className="text-2xl font-bold">89%</h3>
            <p className="text-sm text-muted-foreground">Engagement Rate</p>
          </div>
        </div>

        {/* Social Feed */}
        <div className="space-y-8">
          <div className="flex items-center justify-between">
            <h3 className="text-2xl font-bold">Recent Posts</h3>
            <Button variant="outline" size="sm">
              <MessageSquare className="mr-2 h-4 w-4" />
              Start Discussion
            </Button>
          </div>
          
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {socialPosts.map((post, index) => (
              <SocialCard
                key={post.id}
                id={post.id}
                author={post.author}
                content={post.content}
                timestamp={post.timestamp}
                likes={post.likes}
                comments={post.comments}
                shares={post.shares}
                isLiked={post.isLiked}
                category={post.category}
                imageUrl={post.imageUrl}
                className="animate-in fade-in-0 slide-in-from-bottom-4"
                style={{ animationDelay: `${index * 100}ms` }}
              />
            ))}
          </div>
        </div>

        {/* Call to Action */}
        <div className="text-center space-y-4">
          <h3 className="text-xl font-semibold">Join the Conversation</h3>
          <p className="text-muted-foreground max-w-2xl mx-auto">
            Share your AI experiences, ask questions, and connect with like-minded individuals working toward responsible AI development.
          </p>
          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Button size="lg">
              <MessageSquare className="mr-2 h-4 w-4" />
              Join Discussion
            </Button>
            <Button variant="outline" size="lg">
              <Users className="mr-2 h-4 w-4" />
              View All Members
            </Button>
          </div>
        </div>
      </div>
    </Container>
  )
}
