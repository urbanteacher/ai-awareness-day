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
  QrCode,
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
        description={
          <>
            Here are some ideas for creating a display board to celebrate AI awareness day
            <br />
            <br />
          </>
        }
        align="center"
      />

      {/* Main Display Board Container - Modern Design */}
      <div 
        className="bg-transparent border-4 border-black dark:border-white p-8 shadow-2xl relative overflow-hidden"
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
          <div 
            className="bg-blue-600 text-white p-8 shadow-xl border-4 border-black dark:border-white relative overflow-hidden"
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

        {/* Six Theme Panels Grid - Modern Card Design */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
          
          {/* BE SAFE Panel - Modern Design */}
          <div className="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-red-400">
            {/* Header */}
            <div className="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 relative">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <Shield className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-xl font-bold">BE SAFE</h3>
                  <p className="text-sm opacity-90">Understanding AI safety</p>
                </div>
              </div>
              {/* Floating elements */}
              <div className="absolute top-2 right-2 w-8 h-8 bg-white/10 rounded-full"></div>
              <div className="absolute bottom-2 left-2 w-4 h-4 bg-white/10 rounded-full"></div>
            </div>
            
            {/* Content */}
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    AI systems can be biased if trained on biased data. Always question the source and verify information before making important decisions!
                  </p>
                </div>
                
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    How can we ensure AI tools we use are fair and unbiased?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE SMART Panel - Modern Design */}
          <div className="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-blue-400">
            {/* Header */}
            <div className="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 relative">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <Brain className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-xl font-bold">BE SMART</h3>
                  <p className="text-sm opacity-90">Critical thinking about AI</p>
                </div>
              </div>
              {/* Floating elements */}
              <div className="absolute top-2 right-2 w-8 h-8 bg-white/10 rounded-full"></div>
              <div className="absolute bottom-2 left-2 w-4 h-4 bg-white/10 rounded-full"></div>
            </div>
            
            {/* Content */}
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    AI can process information 1000x faster than humans, but humans are still better at creative problem-solving and understanding context!
                  </p>
                </div>
                
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    What are the strengths and weaknesses of AI compared to human intelligence?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE CREATIVE Panel - Modern Design */}
          <div className="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-green-400">
            {/* Header */}
            <div className="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 relative">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <Heart className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-xl font-bold">BE CREATIVE</h3>
                  <p className="text-sm opacity-90">Using AI as a creative partner</p>
                </div>
              </div>
              {/* Floating elements */}
              <div className="absolute top-2 right-2 w-8 h-8 bg-white/10 rounded-full"></div>
              <div className="absolute bottom-2 left-2 w-4 h-4 bg-white/10 rounded-full"></div>
            </div>
            
            {/* Content */}
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    AI can generate art, music, and stories, but the most creative works come from human-AI collaboration and human imagination!
                  </p>
                </div>
                
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    How can AI enhance your creative projects without replacing your unique voice?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE RESPONSIBLE Panel - Modern Design */}
          <div className="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-purple-400">
            {/* Header */}
            <div className="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 relative">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <Award className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-xl font-bold">BE RESPONSIBLE</h3>
                  <p className="text-sm opacity-90">Ethical considerations</p>
                </div>
              </div>
              {/* Floating elements */}
              <div className="absolute top-2 right-2 w-8 h-8 bg-white/10 rounded-full"></div>
              <div className="absolute bottom-2 left-2 w-4 h-4 bg-white/10 rounded-full"></div>
            </div>
            
            {/* Content */}
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    Every AI decision affects real people. We must consider the impact of our AI choices and use technology responsibly!
                  </p>
                </div>
                
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    What responsibilities do we have when using AI tools in our daily lives?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE FUTURE Panel - Modern Design */}
          <div className="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-orange-400">
            {/* Header */}
            <div className="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 relative">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <Zap className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-xl font-bold">BE FUTURE</h3>
                  <p className="text-sm opacity-90">Preparing for AI-integrated future</p>
                </div>
              </div>
              {/* Floating elements */}
              <div className="absolute top-2 right-2 w-8 h-8 bg-white/10 rounded-full"></div>
              <div className="absolute bottom-2 left-2 w-4 h-4 bg-white/10 rounded-full"></div>
            </div>
            
            {/* Content */}
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    By 2030, 85% of jobs will require AI skills. Start learning now to be future-ready and competitive in the job market!
                  </p>
                </div>
                
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">
                    What AI skills do you want to develop to prepare for your future career?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* QR Code Challenges Panel - Modern Design */}
          <div className="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-black dark:border-white hover:border-cyan-400">
            {/* Header */}
            <div className="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white p-6 relative">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <QrCode className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-xl font-bold">QR CHALLENGES</h3>
                  <p className="text-sm opacity-90">Scan & Investigate School Policy</p>
                </div>
              </div>
              {/* Floating elements */}
              <div className="absolute top-2 right-2 w-8 h-8 bg-white/10 rounded-full"></div>
              <div className="absolute bottom-2 left-2 w-4 h-4 bg-white/10 rounded-full"></div>
            </div>
            
            {/* Content */}
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-3">
                    <Globe className="w-4 h-4 text-cyan-600" />
                    <h4 className="font-semibold text-sm text-gray-800 dark:text-gray-200">Scan QR Codes Below</h4>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    Use your phone camera to scan these QR codes and discover your school's AI policies and guidelines!
                  </p>
                  <div className="grid grid-cols-2 gap-3">
                    <div className="bg-white dark:bg-gray-700 p-3 rounded-xl border-2 border-dashed border-cyan-300 text-center">
                      <div className="w-16 h-16 mx-auto mb-2 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                        <QrCode className="w-8 h-8 text-cyan-600" />
                      </div>
                      <p className="text-xs text-gray-600 dark:text-gray-300 font-medium">School Policy</p>
                    </div>
                    <div className="bg-white dark:bg-gray-700 p-3 rounded-xl border-2 border-dashed border-cyan-300 text-center">
                      <div className="w-16 h-16 mx-auto mb-2 bg-cyan-100 dark:bg-cyan-900 rounded-lg flex items-center justify-center">
                        <QrCode className="w-8 h-8 text-cyan-600" />
                      </div>
                      <p className="text-xs text-gray-600 dark:text-gray-300 font-medium">AI Guidelines</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Interactive Corner - Modern Design */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
          
          {/* Weekly Questions Box - Modern Design */}
          <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-black dark:border-white hover:border-yellow-400 transition-all duration-300 overflow-hidden">
            <div className="bg-gradient-to-r from-yellow-500 to-orange-500 text-white p-6">
              <div className="flex items-center gap-3 mb-4">
                <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                  <Star className="w-5 h-5" />
                </div>
                <h3 className="text-xl font-bold">This Week's Questions</h3>
              </div>
            </div>
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Shield className="w-4 h-4 text-red-600" />
                    <p className="text-sm font-semibold text-gray-800 dark:text-gray-200">BE SAFE</p>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">How can we ensure AI tools are fair?</p>
                </div>
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Brain className="w-4 h-4 text-blue-600" />
                    <p className="text-sm font-semibold text-gray-800 dark:text-gray-200">BE SMART</p>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">What are AI's strengths vs humans?</p>
                </div>
                <div className="p-4 rounded-xl">
                  <div className="flex items-center gap-2 mb-2">
                    <Heart className="w-4 h-4 text-green-600" />
                    <p className="text-sm font-semibold text-gray-800 dark:text-gray-200">BE CREATIVE</p>
                  </div>
                  <p className="text-sm text-gray-700 dark:text-gray-300">How can AI enhance creativity?</p>
                </div>
              </div>
            </div>
          </div>

          {/* Response Pocket - Modern Design */}
          <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-black dark:border-white hover:border-green-400 transition-all duration-300 overflow-hidden">
            <div className="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-6">
              <div className="flex items-center gap-3 mb-4">
                <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                  <MessageSquare className="w-5 h-5" />
                </div>
                <h3 className="text-xl font-bold">Student Responses</h3>
              </div>
            </div>
            <div className="p-6 bg-white dark:bg-gray-800">
              <div className="space-y-4">
                <div className="p-4 rounded-xl">
                  <p className="text-sm text-gray-700 dark:text-gray-300 italic">"AI should be transparent about how it makes decisions" - Sarah</p>
                </div>
                <div className="p-4 rounded-xl">
                  <p className="text-sm text-gray-700 dark:text-gray-300 italic">"Humans are better at understanding emotions" - Marcus</p>
                </div>
                <div className="p-4 rounded-xl">
                  <p className="text-sm text-gray-700 dark:text-gray-300 italic">"AI can help brainstorm ideas but I add the creativity" - Emma</p>
                </div>
                <div className="bg-gray-100 dark:bg-gray-700 p-3 rounded-xl text-center">
                  <p className="text-xs text-gray-500 dark:text-gray-400 italic">Students write answers on sticky notes and place them here</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* AI Leaders Gallery - Simplified Design */}
        <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-black dark:border-white hover:border-indigo-400 transition-all duration-300 overflow-hidden mb-8">
          <div className="bg-indigo-500 text-white p-6">
            <div className="flex items-center gap-3 mb-4">
              <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <Star className="w-5 h-5" />
              </div>
              <h3 className="text-xl font-bold">AI Leaders & Innovators</h3>
            </div>
          </div>
          <div className="p-6 bg-white dark:bg-gray-800">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
              {/* Empty spaces for teacher to add AI leader photos */}
              <div className="bg-gray-100 dark:bg-gray-700 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 text-center hover:border-indigo-400 transition-colors">
                <div className="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full mx-auto mb-3 flex items-center justify-center">
                  <span className="text-2xl">📸</span>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-300 font-semibold">Add Photo</p>
                <p className="text-xs text-gray-500 dark:text-gray-400">AI Leader</p>
              </div>
              <div className="bg-gray-100 dark:bg-gray-700 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 text-center hover:border-indigo-400 transition-colors">
                <div className="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full mx-auto mb-3 flex items-center justify-center">
                  <span className="text-2xl">📸</span>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-300 font-semibold">Add Photo</p>
                <p className="text-xs text-gray-500 dark:text-gray-400">AI Leader</p>
              </div>
              <div className="bg-gray-100 dark:bg-gray-700 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 text-center hover:border-indigo-400 transition-colors">
                <div className="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full mx-auto mb-3 flex items-center justify-center">
                  <span className="text-2xl">📸</span>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-300 font-semibold">Add Photo</p>
                <p className="text-xs text-gray-500 dark:text-gray-400">AI Leader</p>
              </div>
              <div className="bg-gray-100 dark:bg-gray-700 p-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 text-center hover:border-indigo-400 transition-colors">
                <div className="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full mx-auto mb-3 flex items-center justify-center">
                  <span className="text-2xl">📸</span>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-300 font-semibold">Add Photo</p>
                <p className="text-xs text-gray-500 dark:text-gray-400">AI Leader</p>
              </div>
            </div>
            <div className="bg-gray-100 dark:bg-gray-700 p-4 rounded-xl text-center">
              <p className="text-sm text-gray-600 dark:text-gray-300 italic">
                Teachers: Add photos of famous AI leaders like Andrew Ng, Fei-Fei Li, Yann LeCun, Demis Hassabis, etc.
              </p>
            </div>
          </div>
        </div>

         {/* Student Spotlight - Simplified Design */}
         <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-black dark:border-white hover:border-purple-400 transition-all duration-300 overflow-hidden mb-8">
           <div className="bg-purple-500 text-white p-6">
             <div className="flex items-center gap-3 mb-4">
               <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                 <Users className="w-5 h-5" />
               </div>
               <h3 className="text-xl font-bold">Student Spotlight</h3>
             </div>
           </div>
           <div className="p-6 bg-white dark:bg-gray-800">
             <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div className="p-4 rounded-xl">
                 <div className="flex items-center gap-3">
                   <div className="w-12 h-12 bg-purple-200 dark:bg-purple-800 rounded-full flex items-center justify-center">
                     <Users className="w-6 h-6 text-purple-600 dark:text-purple-300" />
                   </div>
                   <div>
                     <p className="font-semibold text-sm text-gray-800 dark:text-gray-200">Sarah Chen</p>
                     <p className="text-xs text-gray-600 dark:text-gray-400">Riverside High School</p>
                     <p className="text-xs text-gray-700 dark:text-gray-300 mt-1">Created AI ethics presentation shared across 5 schools</p>
                   </div>
                 </div>
               </div>
               <div className="p-4 rounded-xl">
                 <div className="flex items-center gap-3">
                   <div className="w-12 h-12 bg-purple-200 dark:bg-purple-800 rounded-full flex items-center justify-center">
                     <Users className="w-6 h-6 text-purple-600 dark:text-purple-300" />
                   </div>
                   <div>
                     <p className="font-semibold text-sm text-gray-800 dark:text-gray-200">Marcus Johnson</p>
                     <p className="text-xs text-gray-600 dark:text-gray-400">Valley Middle School</p>
                     <p className="text-xs text-gray-700 dark:text-gray-300 mt-1">Developed chatbot to help students with homework</p>
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