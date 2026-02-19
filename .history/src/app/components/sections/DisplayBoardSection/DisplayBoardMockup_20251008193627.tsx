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
  Sparkles,
  Lightbulb,
  Target,
  Globe
} from "lucide-react";

export default function DisplayBoardMockup() {
  return (
    <div className="w-full max-w-7xl mx-auto">
      <SectionHeader
        title="School Display Board"
        subtitle="AI Awareness Day 2026"
        description="Here are some ideas for creating a display board to celebrate AI awareness day"
        align="center"
      />

      {/* Main Display Board Container - Modern Design */}
      <div className="bg-transparent border-4 border-black dark:border-white p-8 shadow-2xl relative overflow-hidden">
        <div 
          className="w-full h-full"
          style={{
            clipPath: "polygon(0 0, calc(100% - 40px) 0, 100% 40px, 100% 100%, 40px 100%, 0 calc(100% - 40px))"
          }}
        >
        {/* Background Pattern */}
        <div className="absolute inset-0 opacity-5">
          <div className="absolute top-10 left-10 w-32 h-32 bg-blue-500 rounded-full blur-3xl"></div>
          <div className="absolute bottom-10 right-10 w-40 h-40 bg-purple-500 rounded-full blur-3xl"></div>
          <div className="absolute top-1/2 left-1/4 w-24 h-24 bg-green-500 rounded-full blur-2xl"></div>
        </div>
        
        {/* Central Header Section - Simplified Design */}
        <div className="relative mb-12">
          <div className="bg-blue-600 text-white p-8 shadow-xl border-4 border-black dark:border-white relative overflow-hidden">
            <div 
              className="w-full h-full"
              style={{
                clipPath: "polygon(0 0, calc(100% - 30px) 0, 100% 30px, 100% 100%, 30px 100%, 0 calc(100% - 30px))"
              }}
            >
            <div className="text-center">
              <div className="flex items-center justify-center gap-6 mb-6">
                {/* School Logo Placeholder - Circle */}
                <div className="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center border-2 border-white/30 shadow-lg">
                  <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                    <span className="text-blue-600 font-bold text-lg">LOGO</span>
                  </div>
                </div>
                <div className="text-left">
                  <h1 className="text-4xl font-bold mb-2 text-white">
                    AI AWARENESS DAY 2026
                  </h1>
                  <p className="text-xl opacity-90 font-medium">Know it, Question it, Use it Wisely</p>
                </div>
              </div>
            </div>
            </div>
          </div>
        </div>

        {/* Four Core Theme Panels Grid - Condensed Design */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-8">
          
          {/* BE SAFE Panel - Modern Design */}
          <div 
            className="group relative bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-red-400"
            style={{
              clipPath: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
            }}
          >
            {/* Header */}
            <div className="bg-gradient-to-br from-red-500 to-red-600 text-white p-4 relative">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <Shield className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-lg font-bold">BE SAFE</h3>
                </div>
              </div>
            </div>
            
            {/* Content */}
            <div className="p-4 bg-white dark:bg-gray-800">
              <p className="text-sm text-gray-700 dark:text-gray-300 mb-3">
                AI systems can be biased if trained on biased data. Always question the source and verify information!
              </p>
              <div className="flex items-center gap-2">
                <Target className="w-3 h-3 text-blue-600" />
                <p className="text-xs text-gray-600 dark:text-gray-400">How can we ensure AI tools are fair?</p>
              </div>
            </div>
          </div>

          {/* BE SMART Panel - Modern Design */}
          <div 
            className="group relative bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-blue-400"
            style={{
              clipPath: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
            }}
          >
            {/* Header */}
            <div className="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 relative">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <Brain className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-lg font-bold">BE SMART</h3>
                </div>
              </div>
            </div>
            
            {/* Content */}
            <div className="p-4 bg-white dark:bg-gray-800">
              <p className="text-sm text-gray-700 dark:text-gray-300 mb-3">
                AI processes info 1000x faster than humans, but humans are better at creative problem-solving!
              </p>
              <div className="flex items-center gap-2">
                <Target className="w-3 h-3 text-blue-600" />
                <p className="text-xs text-gray-600 dark:text-gray-400">What are AI's strengths vs humans?</p>
              </div>
            </div>
          </div>

          {/* BE CREATIVE Panel - Modern Design */}
          <div 
            className="group relative bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-green-400"
            style={{
              clipPath: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
            }}
          >
            {/* Header */}
            <div className="bg-gradient-to-br from-green-500 to-green-600 text-white p-4 relative">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <Heart className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-lg font-bold">BE CREATIVE</h3>
                </div>
              </div>
            </div>
            
            {/* Content */}
            <div className="p-4 bg-white dark:bg-gray-800">
              <p className="text-sm text-gray-700 dark:text-gray-300 mb-3">
                AI can generate art and music, but the most creative works come from human-AI collaboration!
              </p>
              <div className="flex items-center gap-2">
                <Target className="w-3 h-3 text-blue-600" />
                <p className="text-xs text-gray-600 dark:text-gray-400">How can AI enhance creativity?</p>
              </div>
            </div>
          </div>

          {/* BE RESPONSIBLE Panel - Modern Design */}
          <div 
            className="group relative bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-purple-400"
            style={{
              clipPath: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
            }}
          >
            {/* Header */}
            <div className="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 relative">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <Award className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-lg font-bold">BE RESPONSIBLE</h3>
                </div>
              </div>
            </div>
            
            {/* Content */}
            <div className="p-4 bg-white dark:bg-gray-800">
              <p className="text-sm text-gray-700 dark:text-gray-300 mb-3">
                Every AI decision affects real people. We must consider the impact and use technology responsibly!
              </p>
              <div className="flex items-center gap-2">
                <Target className="w-3 h-3 text-blue-600" />
                <p className="text-xs text-gray-600 dark:text-gray-400">What responsibilities do we have with AI?</p>
              </div>
            </div>
          </div>

          {/* BE FUTURE Panel - Modern Design */}
          <div 
            className="group relative bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-orange-400"
            style={{
              clipPath: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
            }}
          >
            {/* Header */}
            <div className="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-4 relative">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <Zap className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-lg font-bold">BE FUTURE</h3>
                </div>
              </div>
            </div>
            
            {/* Content */}
            <div className="p-4 bg-white dark:bg-gray-800">
              <p className="text-sm text-gray-700 dark:text-gray-300 mb-3">
                By 2030, 85% of jobs will require AI skills. Start learning now to be future-ready!
              </p>
              <div className="flex items-center gap-2">
                <Target className="w-3 h-3 text-blue-600" />
                <p className="text-xs text-gray-600 dark:text-gray-400">What AI skills do you want to develop?</p>
              </div>
            </div>
          </div>

        </div>

        {/* Interactive Corner - Condensed Design */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-8">
          
          {/* Weekly Questions Box - Modern Design */}
          <div 
            className="bg-white dark:bg-gray-800 shadow-lg border-2 border-black dark:border-white hover:border-yellow-400 transition-all duration-300 overflow-hidden"
            style={{
              clipPath: "polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))"
            }}
          >
            <div className="bg-gradient-to-r from-yellow-500 to-orange-500 text-white p-4">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <Star className="w-4 h-4" />
                </div>
                <h3 className="text-lg font-bold">This Week's Questions</h3>
              </div>
            </div>
            <div className="p-4 bg-white dark:bg-gray-800">
              <div className="space-y-3">
                <div className="flex items-center gap-2">
                  <Shield className="w-3 h-3 text-red-600" />
                  <p className="text-xs text-gray-700 dark:text-gray-300">How can we ensure AI tools are fair?</p>
                </div>
                <div className="flex items-center gap-2">
                  <Brain className="w-3 h-3 text-blue-600" />
                  <p className="text-xs text-gray-700 dark:text-gray-300">What are AI's strengths vs humans?</p>
                </div>
                <div className="flex items-center gap-2">
                  <Heart className="w-3 h-3 text-green-600" />
                  <p className="text-xs text-gray-700 dark:text-gray-300">How can AI enhance creativity?</p>
                </div>
              </div>
            </div>
          </div>

          {/* Response Pocket - Modern Design */}
          <div 
            className="bg-white dark:bg-gray-800 shadow-lg border-2 border-black dark:border-white hover:border-green-400 transition-all duration-300 overflow-hidden"
            style={{
              clipPath: "polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))"
            }}
          >
            <div className="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-4">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <MessageSquare className="w-4 h-4" />
                </div>
                <h3 className="text-lg font-bold">Student Responses</h3>
              </div>
            </div>
            <div className="p-4 bg-white dark:bg-gray-800">
              <div className="space-y-2">
                <p className="text-xs text-gray-700 dark:text-gray-300 italic">"AI should be transparent" - Sarah</p>
                <p className="text-xs text-gray-700 dark:text-gray-300 italic">"Humans understand emotions better" - Marcus</p>
                <p className="text-xs text-gray-700 dark:text-gray-300 italic">"AI helps brainstorm, I add creativity" - Emma</p>
                <div className="bg-gray-100 dark:bg-gray-700 p-2 rounded-lg text-center mt-3">
                  <p className="text-xs text-gray-500 dark:text-gray-400 italic">Students write answers on sticky notes here</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* AI Leaders Gallery - Condensed Design */}
        <div 
          className="bg-white dark:bg-gray-800 shadow-lg border-2 border-black dark:border-white overflow-hidden mb-6"
          style={{
            clipPath: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
          }}
        >
          <div className="bg-indigo-500 text-white p-4">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <Star className="w-4 h-4" />
              </div>
              <h3 className="text-lg font-bold">AI Leaders & Innovators</h3>
            </div>
          </div>
          <div className="p-4 bg-white dark:bg-gray-800">
            <div className="grid grid-cols-2 gap-3 mb-3">
              <div className="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-center">
                <div className="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full mx-auto mb-2 flex items-center justify-center">
                  <span className="text-lg">📸</span>
                </div>
                <p className="text-xs text-gray-600 dark:text-gray-300 font-semibold">Add Photo</p>
              </div>
              <div className="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-center">
                <div className="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full mx-auto mb-2 flex items-center justify-center">
                  <span className="text-lg">📸</span>
                </div>
                <p className="text-xs text-gray-600 dark:text-gray-300 font-semibold">Add Photo</p>
              </div>
            </div>
            <div className="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg text-center">
              <p className="text-xs text-gray-600 dark:text-gray-300 italic">
                Add photos of AI leaders like Andrew Ng, Fei-Fei Li, Yann LeCun, etc.
              </p>
            </div>
          </div>
        </div>

         {/* Student Spotlight - Condensed Design */}
         <div 
           className="bg-white dark:bg-gray-800 shadow-lg border-2 border-black dark:border-white overflow-hidden mb-6"
           style={{
             clipPath: "polygon(0 0, calc(100% - 20px) 0, 100% 20px, 100% 100%, 20px 100%, 0 calc(100% - 20px))"
           }}
         >
           <div className="bg-purple-500 text-white p-4">
             <div className="flex items-center gap-2">
               <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                 <Users className="w-4 h-4" />
               </div>
               <h3 className="text-lg font-bold">Student Spotlight</h3>
             </div>
           </div>
           <div className="p-4 bg-white dark:bg-gray-800">
             <div className="space-y-3">
               <div className="flex items-center gap-2">
                 <div className="w-8 h-8 bg-purple-200 dark:bg-purple-800 rounded-full flex items-center justify-center">
                   <Users className="w-4 h-4 text-purple-600 dark:text-purple-300" />
                 </div>
                 <div>
                   <p className="font-semibold text-xs text-gray-800 dark:text-gray-200">Sarah Chen</p>
                   <p className="text-xs text-gray-600 dark:text-gray-400">Created AI ethics presentation</p>
                 </div>
               </div>
               <div className="flex items-center gap-2">
                 <div className="w-8 h-8 bg-purple-200 dark:bg-purple-800 rounded-full flex items-center justify-center">
                   <Users className="w-4 h-4 text-purple-600 dark:text-purple-300" />
                 </div>
                 <div>
                   <p className="font-semibold text-xs text-gray-800 dark:text-gray-200">Marcus Johnson</p>
                   <p className="text-xs text-gray-600 dark:text-gray-400">Developed homework chatbot</p>
                 </div>
               </div>
             </div>
           </div>
         </div>

        </div>
      </div>
    </div>
  );
}