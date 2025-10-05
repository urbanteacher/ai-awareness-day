"use client";

import React from "react";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { SectionHeader } from "@/components/ui";
import { 
  Shield, 
  Brain, 
  Heart, 
  Award, 
  Zap,
  BookOpen,
  MessageSquare,
  Star,
  Users,
  QrCode
} from "lucide-react";

export default function DisplayBoardMockup() {
  return (
    <div className="w-full max-w-6xl mx-auto">
      <SectionHeader
        title="Display Board Ideas"
        subtitle="Visual & Interactive"
        description="Here are a range of different ideas and activities you can include in your AI Awareness Day display board 2026"
        align="center"
      />

      {/* Main Display Board Container */}
      <div className="bg-white dark:bg-black border-4 border-gray-800 dark:border-gray-200 rounded-lg p-6 shadow-2xl">
        
        {/* Central Header Section */}
        <div className="text-center mb-8 p-6 bg-blue-600 text-white rounded-lg border-2 border-blue-800">
          <div className="flex items-center justify-center gap-4 mb-4">
            {/* School Logo Placeholder */}
            <div className="w-20 h-20 bg-white dark:bg-gray-800 rounded-lg flex items-center justify-center border-2 border-blue-300 dark:border-blue-600">
              <span className="text-xs font-bold text-blue-600 text-center">Your School<br/>Logo</span>
            </div>
            <div>
              <h1 className="text-3xl font-bold">AI AWARENESS DAY 2026</h1>
              <p className="text-xl opacity-90">Know it, Question it, Use it Wisely</p>
            </div>
          </div>
        </div>

        {/* Six Theme Panels Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 mb-8">
          
          {/* BE SAFE Panel */}
          <div className="border-2 border-red-500 overflow-hidden" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="bg-red-500 text-white p-4 text-center">
              <div className="flex items-center justify-center gap-2 mb-2">
                <Shield className="w-6 h-6" />
                <h3 className="text-lg font-bold">BE SAFE</h3>
              </div>
              <p className="text-sm opacity-90">Understanding AI safety</p>
            </div>
            <div className="p-4 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white">
            <div className="mb-3">
              <h4 className="font-semibold text-sm text-yellow-400 mb-1">💡 Did You Know?</h4>
              <p className="text-xs text-gray-300 bg-yellow-900/30 p-2 rounded border border-yellow-700/50">
                AI systems can be biased if trained on biased data. Always question the source and verify information before making important decisions!
              </p>
            </div>
              <div className="mb-3">
                <h4 className="font-semibold text-sm text-blue-400 mb-1">❓ Weekly Question</h4>
                <p className="text-xs text-gray-300 bg-blue-900/30 p-2 rounded border border-blue-700/50">
                  How can we ensure AI tools we use are fair and unbiased?
                </p>
              </div>
              {/* Decorative corner polygon */}
              <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                   style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
            </div>
          </div>

          {/* BE SMART Panel */}
          <div className="border-2 border-blue-500 overflow-hidden" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="bg-blue-500 text-white p-4 text-center">
              <div className="flex items-center justify-center gap-2 mb-2">
                <Brain className="w-6 h-6" />
                <h3 className="text-lg font-bold">BE SMART</h3>
              </div>
              <p className="text-sm opacity-90">Critical thinking about AI</p>
            </div>
            <div className="p-4 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white relative">
            <div className="mb-3">
              <h4 className="font-semibold text-sm text-yellow-400 mb-1">💡 Did You Know?</h4>
              <p className="text-xs text-gray-300 bg-yellow-900/30 p-2 rounded border border-yellow-700/50">
                AI can process information 1000x faster than humans, but humans are still better at creative problem-solving and understanding context!
              </p>
            </div>
              <div className="mb-3">
                <h4 className="font-semibold text-sm text-blue-400 mb-1">❓ Weekly Question</h4>
                <p className="text-xs text-gray-300 bg-blue-900/30 p-2 rounded border border-blue-700/50">
                  What are the strengths and weaknesses of AI compared to human intelligence?
                </p>
              </div>
              {/* Decorative corner polygon */}
              <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                   style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
            </div>
          </div>

          {/* BE CREATIVE Panel */}
          <div className="border-2 border-green-500 overflow-hidden" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="bg-green-500 text-white p-4 text-center">
              <div className="flex items-center justify-center gap-2 mb-2">
                <Heart className="w-6 h-6" />
                <h3 className="text-lg font-bold">BE CREATIVE</h3>
              </div>
              <p className="text-sm opacity-90">Using AI as a creative partner</p>
            </div>
            <div className="p-4 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white relative">
            <div className="mb-3">
              <h4 className="font-semibold text-sm text-yellow-400 mb-1">💡 Did You Know?</h4>
              <p className="text-xs text-gray-300 bg-yellow-900/30 p-2 rounded border border-yellow-700/50">
                AI can generate art, music, and stories, but the most creative works come from human-AI collaboration and human imagination!
              </p>
            </div>
              <div className="mb-3">
                <h4 className="font-semibold text-sm text-blue-400 mb-1">❓ Weekly Question</h4>
                <p className="text-xs text-gray-300 bg-blue-900/30 p-2 rounded border border-blue-700/50">
                  How can AI enhance your creative projects without replacing your unique voice?
                </p>
              </div>
              {/* Decorative corner polygon */}
              <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                   style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
            </div>
          </div>

          {/* BE RESPONSIBLE Panel */}
          <div className="border-2 border-purple-500 overflow-hidden" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="bg-purple-500 text-white p-4 text-center">
              <div className="flex items-center justify-center gap-2 mb-2">
                <Award className="w-6 h-6" />
                <h3 className="text-lg font-bold">BE RESPONSIBLE</h3>
              </div>
              <p className="text-sm opacity-90">Ethical considerations</p>
            </div>
            <div className="p-4 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white relative">
            <div className="mb-3">
              <h4 className="font-semibold text-sm text-yellow-400 mb-1">💡 Did You Know?</h4>
              <p className="text-xs text-gray-300 bg-yellow-900/30 p-2 rounded border border-yellow-700/50">
                Every AI decision affects real people. We must consider the impact of our AI choices and use technology responsibly!
              </p>
            </div>
              <div className="mb-3">
                <h4 className="font-semibold text-sm text-blue-400 mb-1">❓ Weekly Question</h4>
                <p className="text-xs text-gray-300 bg-blue-900/30 p-2 rounded border border-blue-700/50">
                  What responsibilities do we have when using AI tools in our daily lives?
                </p>
              </div>
              {/* Decorative corner polygon */}
              <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                   style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
            </div>
          </div>

          {/* BE READY FOR THE FUTURE Panel */}
          <div className="border-2 border-orange-500 overflow-hidden" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="bg-orange-500 text-white p-4 text-center">
              <div className="flex items-center justify-center gap-2 mb-2">
                <Zap className="w-6 h-6" />
                <h3 className="text-lg font-bold">BE READY FOR THE FUTURE</h3>
              </div>
              <p className="text-sm opacity-90">Preparing for AI-integrated future</p>
            </div>
            <div className="p-4 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white relative">
            <div className="mb-3">
              <h4 className="font-semibold text-sm text-yellow-400 mb-1">💡 Did You Know?</h4>
              <p className="text-xs text-gray-300 bg-yellow-900/30 p-2 rounded border border-yellow-700/50">
                By 2030, 85% of jobs will require AI skills. Start learning now to be future-ready and competitive in the job market!
              </p>
            </div>
              <div className="mb-3">
                <h4 className="font-semibold text-sm text-blue-400 mb-1">❓ Weekly Question</h4>
                <p className="text-xs text-gray-300 bg-blue-900/30 p-2 rounded border border-blue-700/50">
                  What AI skills do you want to develop to prepare for your future career?
                </p>
              </div>
              {/* Decorative corner polygon */}
              <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                   style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
            </div>
          </div>

          {/* QR Code Challenges Panel */}
          <div className="border-2 border-cyan-500 overflow-hidden rounded-lg shadow-lg" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))' }}>
            <div className="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white p-4 text-center">
              <div className="flex items-center justify-center gap-2 mb-2">
                <div className="w-6 h-6 bg-white/20 rounded flex items-center justify-center">
                  <span className="text-xs font-bold">QR</span>
                </div>
                <h3 className="text-lg font-bold">QR CHALLENGES</h3>
              </div>
              <p className="text-sm opacity-90">Scan & Investigate School Policy</p>
            </div>
            <div className="p-4 bg-gradient-to-br from-gray-800 to-gray-900 text-white relative">
              <div className="mb-4">
                <h4 className="font-semibold text-sm text-cyan-400 mb-2 flex items-center gap-1">
                  📱 Scan QR Codes Below
                </h4>
                <p className="text-xs text-gray-300 mb-3 text-center">
                  Use your phone camera to scan these QR codes and discover your school's AI policies and guidelines!
                </p>
                <div className="grid grid-cols-2 gap-2 mb-3">
                  <div className="bg-white dark:bg-gray-800 p-2 rounded border-2 border-dashed border-cyan-400 dark:border-cyan-600 text-center">
                    <div className="w-16 h-16 mx-auto mb-1 bg-white dark:bg-gray-700 p-1 rounded flex items-center justify-center">
                      <QrCode className="w-12 h-12 text-gray-800" />
                    </div>
                    <p className="text-xs text-gray-600 dark:text-gray-200">School Policy</p>
                  </div>
                  <div className="bg-white dark:bg-gray-800 p-2 rounded border-2 border-dashed border-cyan-400 dark:border-cyan-600 text-center">
                    <div className="w-16 h-16 mx-auto mb-1 bg-white dark:bg-gray-700 p-1 rounded flex items-center justify-center">
                      <QrCode className="w-12 h-12 text-gray-800" />
                    </div>
                    <p className="text-xs text-gray-600 dark:text-gray-200">AI Guidelines</p>
                  </div>
                </div>
              </div>
              {/* Decorative corner polygon */}
              <div className="absolute top-2 right-2 w-6 h-6 bg-cyan-400/20 rounded-sm" 
                   style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
            </div>
          </div>
        </div>

        {/* Interactive Corner */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          
          {/* Weekly Questions Box */}
          <div className="border-2 border-gray-400 overflow-hidden p-4 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white relative" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="flex items-center gap-2 mb-3">
              <Star className="w-5 h-5 text-yellow-400" />
              <h3 className="font-bold text-white">This Week's Questions</h3>
            </div>
            <div className="space-y-2">
              <div className="bg-red-900/30 p-2 rounded border-l-4 border-red-500">
                <p className="text-xs font-semibold text-red-400">BE SAFE</p>
                <p className="text-xs text-gray-300">How can we ensure AI tools are fair?</p>
              </div>
              <div className="bg-blue-900/30 p-2 rounded border-l-4 border-blue-500">
                <p className="text-xs font-semibold text-blue-400">BE SMART</p>
                <p className="text-xs text-gray-300">What are AI's strengths vs humans?</p>
              </div>
              <div className="bg-green-900/30 p-2 rounded border-l-4 border-green-500">
                <p className="text-xs font-semibold text-green-400">BE CREATIVE</p>
                <p className="text-xs text-gray-300">How can AI enhance creativity?</p>
              </div>
            </div>
            {/* Decorative corner polygon */}
            <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                 style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
          </div>

          {/* Response Pocket */}
          <div className="border-2 border-dashed border-gray-400 overflow-hidden p-4 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white relative" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="flex items-center gap-2 mb-3">
              <MessageSquare className="w-5 h-5 text-green-400" />
              <h3 className="font-bold text-white">Student Responses</h3>
            </div>
            <div className="bg-gray-700 p-4 rounded border-2 border-dashed border-green-400 text-center">
              <div className="space-y-2">
                <div className="bg-yellow-900/30 p-2 rounded text-xs border border-yellow-700/50">
                  "AI should be transparent about how it makes decisions" - Sarah
                </div>
                <div className="bg-blue-900/30 p-2 rounded text-xs border border-blue-700/50">
                  "Humans are better at understanding emotions" - Marcus
                </div>
                <div className="bg-green-900/30 p-2 rounded text-xs border border-green-700/50">
                  "AI can help brainstorm ideas but I add the creativity" - Emma
                </div>
                <div className="text-xs text-gray-400 italic">
                  Students write answers on sticky notes and place them here
                </div>
              </div>
            </div>
            {/* Decorative corner polygon */}
            <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                 style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
          </div>
        </div>

        {/* AI Leaders Gallery */}
        <div className="border-2 border-gray-400 overflow-hidden p-4 bg-gray-800 dark:bg-gray-800 text-white mb-6 relative" 
             style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
          <div className="flex items-center gap-2 mb-3">
            <Star className="w-5 h-5 text-indigo-400" />
            <h3 className="font-bold text-white">AI Leaders & Innovators</h3>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {/* Empty spaces for teacher to add AI leader photos */}
            <div className="bg-gray-700 p-3 rounded border-2 border-dashed border-gray-500 text-center">
              <div className="w-16 h-16 bg-gray-600 rounded-full mx-auto mb-2 flex items-center justify-center">
                <span className="text-2xl">📸</span>
              </div>
              <p className="text-xs text-gray-300 font-semibold">Add Photo</p>
              <p className="text-xs text-gray-400">AI Leader</p>
            </div>
            <div className="bg-gray-700 p-3 rounded border-2 border-dashed border-gray-500 text-center">
              <div className="w-16 h-16 bg-gray-600 rounded-full mx-auto mb-2 flex items-center justify-center">
                <span className="text-2xl">📸</span>
              </div>
              <p className="text-xs text-gray-300 font-semibold">Add Photo</p>
              <p className="text-xs text-gray-400">AI Leader</p>
            </div>
            <div className="bg-gray-700 p-3 rounded border-2 border-dashed border-gray-500 text-center">
              <div className="w-16 h-16 bg-gray-600 rounded-full mx-auto mb-2 flex items-center justify-center">
                <span className="text-2xl">📸</span>
              </div>
              <p className="text-xs text-gray-300 font-semibold">Add Photo</p>
              <p className="text-xs text-gray-400">AI Leader</p>
            </div>
            <div className="bg-gray-700 p-3 rounded border-2 border-dashed border-gray-500 text-center">
              <div className="w-16 h-16 bg-gray-600 rounded-full mx-auto mb-2 flex items-center justify-center">
                <span className="text-2xl">📸</span>
              </div>
              <p className="text-xs text-gray-300 font-semibold">Add Photo</p>
              <p className="text-xs text-gray-400">AI Leader</p>
            </div>
          </div>
          <div className="mt-3 text-center">
            <p className="text-xs text-gray-400 italic">
              Teachers: Add photos of famous AI leaders like Andrew Ng, Fei-Fei Li, Yann LeCun, Demis Hassabis, etc.
            </p>
          </div>
          {/* Decorative corner polygon */}
          <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
               style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
        </div>

         {/* Student Spotlight */}
         <div className="border-2 border-gray-400 overflow-hidden p-4 bg-gray-800 dark:bg-gray-800 text-white mb-6 relative" 
              style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
           <div className="flex items-center gap-2 mb-3">
             <Users className="w-5 h-5 text-purple-400" />
             <h3 className="font-bold text-white">Student Spotlight</h3>
           </div>
           <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
             <div className="bg-gray-700 p-3 rounded border border-gray-600">
               <div className="flex items-center gap-3">
                 <div className="w-10 h-10 bg-purple-900/30 rounded-full flex items-center justify-center border border-purple-700/50">
                   <Users className="w-5 h-5 text-purple-400" />
                 </div>
                 <div>
                   <p className="font-semibold text-sm text-white">Sarah Chen</p>
                   <p className="text-xs text-gray-300">Riverside High School</p>
                   <p className="text-xs text-gray-300">Created AI ethics presentation shared across 5 schools</p>
                 </div>
               </div>
             </div>
             <div className="bg-gray-700 p-3 rounded border border-gray-600">
               <div className="flex items-center gap-3">
                 <div className="w-10 h-10 bg-purple-900/30 rounded-full flex items-center justify-center border border-purple-700/50">
                   <Users className="w-5 h-5 text-purple-400" />
                 </div>
                 <div>
                   <p className="font-semibold text-sm text-white">Marcus Johnson</p>
                   <p className="text-xs text-gray-300">Valley Middle School</p>
                   <p className="text-xs text-gray-300">Developed chatbot to help students with homework</p>
                 </div>
               </div>
             </div>
           </div>
           {/* Decorative corner polygon */}
           <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
         </div>

      </div>
    </div>
  );
}
