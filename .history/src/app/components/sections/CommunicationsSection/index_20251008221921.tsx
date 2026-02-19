"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { motion } from "framer-motion"
import { Download, FileText, Users, Mail, Newspaper, MessageSquare, ChevronDown, ChevronUp } from "lucide-react"
import { useState } from "react"
import { Container, SectionWrapper } from "@/components/ui"

const sampleLetters = [
  {
    id: "slt-shorter-email",
    title: "SLT Shorter Version",
    icon: MessageSquare,
    description: "Concise request focusing on key points",
    content: `Subject: Quick Request - AI Awareness Day

Dear [Name],

Our students are using AI daily without understanding the risks. There's a free national program called AI Awareness Day with ready-made resources that addresses our current concerns about AI homework and student safety.

It's a one-day program of activities with zero cost and no preparation needed as all materials are provided. This would help us address safeguarding concerns and respond to parent questions about AI, while meeting Ofsted's digital citizenship expectations.

Can I have 5 minutes to discuss this? I'll coordinate everything if approved.

Thanks,
[Name]`
  },
  {
    id: "slt-main-email",
    title: "SLT Main Email Template",
    icon: MessageSquare,
    description: "Comprehensive proposal for senior leadership approval",
    content: `Subject: AI Awareness Day 2026 - Request for Approval

Dear [Headteacher's Name],

I'd like to propose our participation in AI Awareness Day 2026, a national initiative helping students understand AI safely and responsibly.

Our students are already using ChatGPT and other AI tools daily, and we're starting to see homework issues related to this. Parents are increasingly asking what we're doing about AI, and Ofsted expects us to deliver digital citizenship education that addresses current technologies.

The program provides ready-made resources for one day or week of activities including 5-minute lesson starters, 15-minute tutor time discussions, and an assembly with full script provided. Everything follows the theme "Know it, Question it, Use it Wisely" and covers essential areas: staying safe with AI, spotting fakes and misinformation, using AI creatively but appropriately, understanding bias, and developing future-ready skills.

The best part is this requires no budget as all resources are free, no staff preparation time as everything is ready-made, and no AI expertise from teachers. I'm happy to coordinate everything including briefing staff, distributing resources, and communicating with parents.

Could we have 5 minutes at the next SLT meeting to discuss this? I can also send you sample materials to review if that would be helpful.

Best regards,
[Your Name]
[Position]`
  },
  {
    id: "parent-letter",
    title: "Letter to Parents/Guardians",
    icon: Users,
    description: "Inform parents about AI Awareness Day and encourage family involvement",
    content: `Dear Parents and Guardians,

We are excited to announce our participation in AI Awareness Day 2026, taking place on [DATE]. This innovative educational initiative will help our students develop critical skills for navigating an AI-influenced world safely and responsibly.

Why This Matters
Your children interact with AI daily—through social media, gaming, and learning apps. This day will help them understand these technologies, recognise both opportunities and risks, and develop essential critical thinking skills.

What Will Happen
Through age-appropriate activities, assemblies, and discussions, students will explore five key themes: staying safe online, thinking critically about AI content, using AI creatively, acting responsibly with technology, and preparing for future careers.

How You Can Help
• Discuss AI experiences with your child
• Explore the provided family resources
• Attend our parent information evening on [DATE]
• Share your own AI questions and concerns
• Celebrate your child's learning

We look forward to your support in making this a meaningful learning experience for all our students.

Best regards,
[School Leadership Team]`
  },
  {
    id: "staff-briefing",
    title: "Staff Briefing Document",
    icon: FileText,
    description: "Comprehensive guide for teachers and support staff",
    content: `AI AWARENESS DAY 2026 - STAFF BRIEFING

Overview
AI Awareness Day 2026 is a nationwide initiative designed to equip students with critical AI literacy skills. Our school's participation will help students understand, question, and use AI responsibly.

Key Themes
1. BE SAFE - Understanding AI limitations and risks
2. BE SMART - Developing critical evaluation skills
3. BE CREATIVE - Using AI as a collaborative tool
4. BE RESPONSIBLE - Acting ethically with technology
5. BE READY FOR THE FUTURE - Preparing for AI-influenced careers

Your Role
• Facilitate age-appropriate discussions
• Guide students through activities
• Model responsible AI use
• Support student learning and questions
• Document student engagement

Resources Provided
• Activity guides for each theme
• Discussion prompts and questions
• Assessment rubrics
• Parent communication templates
• Technical support contacts

Timeline
• Week 1: Preparation and resource distribution
• Week 2: Staff training and setup
• Week 3: AI Awareness Day implementation
• Week 4: Reflection and feedback collection

Questions or concerns? Contact [AI Coordinator Name] at [email/phone].`
  },
  {
    id: "community-announcement",
    title: "Community Announcement",
    icon: Mail,
    description: "Public announcement for local community and media",
    content: `FOR IMMEDIATE RELEASE

[School Name] Participates in AI Awareness Day 2026
Students Learn Critical AI Skills for the Digital Age

[City, State] - [School Name] is proud to announce our participation in AI Awareness Day 2026, a nationwide educational initiative designed to equip students with essential AI literacy skills.

About AI Awareness Day 2026
This innovative program helps students understand artificial intelligence, recognize both opportunities and risks, and develop critical thinking skills for navigating an AI-influenced world safely and responsibly.

What Students Will Learn
• How to identify AI-generated content
• Critical evaluation of AI information
• Creative collaboration with AI tools
• Ethical considerations in AI use
• Future career preparation

Community Impact
"Our students are growing up in a world where AI is increasingly present," said [Principal Name]. "This program ensures they have the knowledge and skills to use these technologies wisely and safely."

The program includes:
• Interactive classroom activities
• School-wide assemblies
• Family engagement resources
• Teacher professional development
• Community showcase events

Media Contact
[Contact Name]
[Title]
[Phone]
[Email]

About [School Name]
[Brief school description and mission statement]`
  },
  {
    id: "press-release",
    title: "Press Release Template",
    icon: Newspaper,
    description: "Professional press release for local media and school communications",
    content: `FOR IMMEDIATE RELEASE

[School Name] Participates in AI Awareness Day 2026 to Prepare Students for AI-Integrated Future

[City, State] - [Date] - [School Name] is proud to announce its participation in AI Awareness Day 2026, a nationwide initiative designed to equip students with essential artificial intelligence literacy skills.

The school will implement a comprehensive program featuring interactive workshops, hands-on activities, and real-world applications of AI technology. Students will explore five core themes: Safe AI use, Smart AI understanding, Creative AI applications, Responsible AI practices, and Future AI possibilities.

"Preparing our students for an AI-integrated future is not just important—it's essential," said [Principal/Head Teacher Name]. "AI Awareness Day 2026 provides us with the perfect opportunity to engage our students in meaningful discussions about artificial intelligence and its impact on their future careers and daily lives."

The program includes:
• Interactive workshops on AI fundamentals
• Creative projects showcasing AI applications
• Discussions on AI ethics and responsible use
• Real-world case studies and examples
• Student presentations and demonstrations

Parents and community members are invited to attend the AI Awareness Day showcase on [Date] at [Time] in [Location], where students will present their projects and demonstrate their learning.

For more information about AI Awareness Day 2026, visit [Website] or contact [Contact Information].

About [School Name]:
[School Name] is committed to providing students with a comprehensive education that prepares them for success in an increasingly digital world. Our participation in AI Awareness Day 2026 reflects our dedication to innovative teaching methods and future-ready education.

###
Contact: [Contact Name]
Phone: [Phone Number]
Email: [Email Address]`
  },
  {
    id: "slt-brief-email",
    title: "SLT Very Brief Version",
    icon: MessageSquare,
    description: "Ultra-short template for busy leadership",
    content: `Subject: AI Education Proposal

Hi [Name],

Given the increasing AI-related homework issues we're seeing, I'd like to implement AI Awareness Day, a free program with ready-made resources that teaches students to use AI safely and responsibly.

No cost, no prep time needed. I'll handle all coordination. Could we discuss briefly this week?

Best regards,
[Name]`
  },
  {
    id: "slt-followup-email",
    title: "SLT Follow-up Email",
    icon: MessageSquare,
    description: "For when there's no initial response",
    content: `Subject: Re: AI Awareness Day 2026

Hi [Name],

Just following up on my email about AI Awareness Day. With students increasingly using ChatGPT for homework, this free program would really help us get ahead of potential issues.

May I pop in for 2 minutes to show you the resources? Or shall I just go ahead and prepare a proposal for the next SLT meeting?

Thanks,
[Name]`
  },
  {
    id: "slt-discussion-email",
    title: "SLT After Discussion Email",
    icon: MessageSquare,
    description: "Following up on informal conversations",
    content: `Subject: AI Awareness Day - As Discussed

Dear [Name],

Following our conversation about students using AI for homework, here's the AI Awareness Day program I mentioned.

As discussed, it's completely free with ready-made resources covering AI safety, identifying fakes, responsible use, and future skills. The materials include assembly scripts, lesson starters, and tutor time activities that require no technical knowledge from staff.

I'm happy to lead this initiative and would just need your approval to proceed with a launch date of [proposed date]. Shall I brief department heads at the next meeting?

Best regards,
[Name]`
  },
  {
    id: "slt-incident-email",
    title: "SLT After Incident Email",
    icon: MessageSquare,
    description: "Crisis response template for AI-related incidents",
    content: `Subject: AI Education Response

Dear [Name],

Following the recent ChatGPT incident in Year 10, I've found a program that provides exactly the framework we need. AI Awareness Day is a free initiative with ready-made resources that teaches students how to use AI responsibly.

The program addresses the issues we've been discussing: academic integrity, AI safety, and helping students understand appropriate use. All materials are provided and I can implement this immediately with your approval.

Could we discuss this as a proactive response to prevent future incidents?

Best regards,
[Name]`
  },
  {
    id: "slt-innovation-email",
    title: "SLT Innovation Email",
    icon: MessageSquare,
    description: "Leadership opportunity angle for forward-thinking schools",
    content: `Subject: AI Education Leadership Opportunity

Dear [Name],

I've identified an opportunity for us to lead regionally in AI education. AI Awareness Day 2026 is a national initiative we can implement immediately at zero cost.

While other schools are still discussing AI challenges, we could be the first in our area to deliver comprehensive AI literacy education. The program comes with complete resources and would position us as forward-thinking educators addressing parent concerns proactively.

This aligns perfectly with our innovation goals and I can coordinate everything. May I have your approval to proceed?

Best regards,
[Name]`
  },
  {
    id: "slt-department-email",
    title: "SLT Department Head Email",
    icon: MessageSquare,
    description: "Department-level proposal template",
    content: `Subject: Department AI Education Proposal

Dear [Name],

The [Department Name] team would like to participate in AI Awareness Day 2026. We're seeing increasing AI use in student work and need to address this properly.

This free program provides ready-made resources for teaching responsible AI use without requiring any budget or additional planning time. We'd like to pilot this in our department first, then potentially expand school-wide if successful.

May we have your approval to proceed?

Best regards,
[Name]`
  }
]

export default function CommunicationsSection() {
  const [showAll, setShowAll] = useState(false)
  const displayedLetters = showAll ? sampleLetters : sampleLetters.slice(0, 4)

  return (
    <SectionWrapper className="bg-background">
      <Container>
        <div className="space-y-16">
          <div className="space-y-4 text-center">
            <p className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
              Reduce administrative burden
            </p>
            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl text-purple-600 dark:text-purple-400">
              Sample Letters & Communications
            </h2>
            <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
              All templates customizable and download-ready
            </p>
          </div>
          
          <div className="max-w-7xl mx-auto">
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {displayedLetters.map((letter, index) => (
              <motion.div
                key={letter.id}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true, amount: 0.3 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                className="relative"
              >
                {/* Polygon Card */}
                <div 
                  className="bg-gray-800 p-6 hover:shadow-lg transition-all duration-300 relative overflow-hidden border-2 border-gray-600"
                  style={{
                    clipPath: 'polygon(0% 0%, 90% 0%, 100% 10%, 100% 100%, 10% 100%, 0% 90%)'
                  }}
                >
                  {/* Icon polygon */}
                  <div 
                    className="w-12 h-12 bg-purple-600 dark:bg-purple-500 flex items-center justify-center mb-4"
                    style={{
                      clipPath: 'polygon(0% 0%, 75% 0%, 100% 25%, 100% 100%, 25% 100%, 0% 75%)'
                    }}
                  >
                    <letter.icon className="w-6 h-6 text-white" />
                  </div>
                  
                  <h3 className="text-lg font-semibold text-white mb-2 line-clamp-2 h-12 flex items-start">
                    <span className="line-clamp-2">{letter.title}</span>
                  </h3>
                  <p className="text-gray-300 text-sm mb-4 line-clamp-2 h-10 flex items-start">
                    <span className="line-clamp-2">{letter.description}</span>
                  </p>
                  
                  <div className="bg-gray-700 p-4 rounded-lg mb-4 max-h-48 overflow-y-auto">
                    <pre className="text-xs text-gray-300 whitespace-pre-wrap font-mono leading-relaxed">
                      {letter.content}
                    </pre>
                  </div>
                  
                  <div className="flex gap-2">
                    <Button 
                      size="sm" 
                      className="flex-1 bg-purple-600 hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600"
                      onClick={() => {
                        const blob = new Blob([letter.content], { type: 'text/plain' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `${letter.id}.txt`;
                        a.click();
                        URL.revokeObjectURL(url);
                      }}
                    >
                      <Download className="w-4 h-4 mr-2" />
                      Download
                    </Button>
                    <Button 
                      size="sm" 
                      variant="outline" 
                      className="flex-1"
                      onClick={() => {
                        navigator.clipboard.writeText(letter.content);
                        alert('Template copied to clipboard!');
                      }}
                    >
                      Copy Text
                    </Button>
                  </div>
                  
                  {/* Decorative corner polygon */}
                  <div 
                    className="absolute top-0 right-0 w-8 h-8 bg-gray-600 opacity-50"
                    style={{
                      clipPath: 'polygon(0% 0%, 100% 0%, 100% 100%, 0% 75%)'
                    }}
                  ></div>
                </div>
              </motion.div>
            ))}
          </div>

            {/* Show More/Show Less Button */}
            {sampleLetters.length > 4 && (
              <div className="text-center mt-8">
                <Button
                  onClick={() => setShowAll(!showAll)}
                  variant="outline"
                  className="bg-card hover:bg-muted border-2 border-purple-200 hover:border-purple-300 dark:border-purple-700 dark:hover:border-purple-600 text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 px-8 py-3 rounded-lg font-medium transition-all duration-300"
                >
                  {showAll ? (
                    <>
                      <ChevronUp className="w-5 h-5 mr-2" />
                      Show Less
                    </>
                  ) : (
                    <>
                      <ChevronDown className="w-5 h-5 mr-2" />
                      Show More Templates ({sampleLetters.length - 4} more)
                    </>
                  )}
                </Button>
              </div>
            )}
          </div>
        </div>
      </Container>
    </SectionWrapper>
  )
}
