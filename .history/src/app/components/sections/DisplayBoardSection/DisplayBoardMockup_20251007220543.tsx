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
        title="AI Awareness Day 2026 Display Board"
        subtitle="Interactive Learning Hub"
        description="A comprehensive visual learning experience showcasing AI themes, student engagement, and educational resources"
        align="center"
      />

      {/* Main Display Board Container - Modern Design */}
      <div className="bg-gradient-to-br from-slate-50 to-blue-50 border-4 border-slate-300 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        {/* Background Pattern */}
        <div className="absolute inset-0 opacity-5">
          <div className="absolute top-10 left-10 w-32 h-32 bg-blue-500 rounded-full blur-3xl"></div>
          <div className="absolute bottom-10 right-10 w-40 h-40 bg-purple-500 rounded-full blur-3xl"></div>
          <div className="absolute top-1/2 left-1/4 w-24 h-24 bg-green-500 rounded-full blur-2xl"></div>
        </div>
        
        {/* Central Header Section - Modern Design */}
        <div className="relative mb-12">
          <div className="bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 text-white rounded-2xl p-8 shadow-xl border-4 border-white/20 relative overflow-hidden">
            {/* Animated Background Elements */}
            <div className="absolute top-0 left-0 w-full h-full opacity-10">
              <div className="absolute top-4 left-4 w-8 h-8 bg-white rounded-full animate-pulse"></div>
              <div className="absolute top-8 right-8 w-6 h-6 bg-white rounded-full animate-pulse delay-1000"></div>
              <div className="absolute bottom-4 left-1/4 w-4 h-4 bg-white rounded-full animate-pulse delay-500"></div>
              <div className="absolute bottom-8 right-1/4 w-5 h-5 bg-white rounded-full animate-pulse delay-1500"></div>
            </div>
            
            <div className="relative z-10 text-center">
              <div className="flex items-center justify-center gap-6 mb-6">
                {/* School Logo Placeholder - Enhanced */}
                <div className="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border-2 border-white/30 shadow-lg">
                  <Sparkles className="w-12 h-12 text-white" />
                </div>
                <div className="text-left">
                  <h1 className="text-4xl font-bold mb-2 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                    AI AWARENESS DAY 2026
                  </h1>
                  <p className="text-xl opacity-90 font-medium">Know it, Question it, Use it Wisely</p>
                  <div className="flex items-center justify-center gap-2 mt-2">
                    <Badge variant="secondary" className="bg-white/20 text-white border-white/30">
                      Interactive Learning
                    </Badge>
                    <Badge variant="secondary" className="bg-white/20 text-white border-white/30">
                      Student Engagement
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Six Theme Panels Grid - Modern Card Design */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
          
          {/* BE SAFE Panel - Modern Design */}
          <div className="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-red-200 hover:border-red-400">
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
            <div className="p-6 bg-gradient-to-br from-gray-50 to-white">
              <div className="space-y-4">
                <div className="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-xl border-l-4 border-yellow-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-yellow-800">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    AI systems can be biased if trained on biased data. Always question the source and verify information before making important decisions!
                  </p>
                </div>
                
                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border-l-4 border-blue-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-blue-800">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    How can we ensure AI tools we use are fair and unbiased?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE SMART Panel - Modern Design */}
          <div className="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-blue-200 hover:border-blue-400">
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
            <div className="p-6 bg-gradient-to-br from-gray-50 to-white">
              <div className="space-y-4">
                <div className="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-xl border-l-4 border-yellow-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-yellow-800">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    AI can process information 1000x faster than humans, but humans are still better at creative problem-solving and understanding context!
                  </p>
                </div>
                
                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border-l-4 border-blue-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-blue-800">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    What are the strengths and weaknesses of AI compared to human intelligence?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE CREATIVE Panel - Modern Design */}
          <div className="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-green-200 hover:border-green-400">
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
            <div className="p-6 bg-gradient-to-br from-gray-50 to-white">
              <div className="space-y-4">
                <div className="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-xl border-l-4 border-yellow-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-yellow-800">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    AI can generate art, music, and stories, but the most creative works come from human-AI collaboration and human imagination!
                  </p>
                </div>
                
                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border-l-4 border-blue-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-blue-800">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    How can AI enhance your creative projects without replacing your unique voice?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE RESPONSIBLE Panel - Modern Design */}
          <div className="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-purple-200 hover:border-purple-400">
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
            <div className="p-6 bg-gradient-to-br from-gray-50 to-white">
              <div className="space-y-4">
                <div className="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-xl border-l-4 border-yellow-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-yellow-800">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    Every AI decision affects real people. We must consider the impact of our AI choices and use technology responsibly!
                  </p>
                </div>
                
                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border-l-4 border-blue-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-blue-800">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    What responsibilities do we have when using AI tools in our daily lives?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* BE READY FOR THE FUTURE Panel - Modern Design */}
          <div className="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-orange-200 hover:border-orange-400">
            {/* Header */}
            <div className="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 relative">
              <div className="flex items-center gap-3 mb-3">
                <div className="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <Zap className="w-6 h-6" />
                </div>
                <div>
                  <h3 className="text-xl font-bold">BE READY FOR THE FUTURE</h3>
                  <p className="text-sm opacity-90">Preparing for AI-integrated future</p>
                </div>
              </div>
              {/* Floating elements */}
              <div className="absolute top-2 right-2 w-8 h-8 bg-white/10 rounded-full"></div>
              <div className="absolute bottom-2 left-2 w-4 h-4 bg-white/10 rounded-full"></div>
            </div>
            
            {/* Content */}
            <div className="p-6 bg-gradient-to-br from-gray-50 to-white">
              <div className="space-y-4">
                <div className="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-xl border-l-4 border-yellow-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Lightbulb className="w-4 h-4 text-yellow-600" />
                    <h4 className="font-semibold text-sm text-yellow-800">Did You Know?</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    By 2030, 85% of jobs will require AI skills. Start learning now to be future-ready and competitive in the job market!
                  </p>
                </div>
                
                <div className="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border-l-4 border-blue-400">
                  <div className="flex items-center gap-2 mb-2">
                    <Target className="w-4 h-4 text-blue-600" />
                    <h4 className="font-semibold text-sm text-blue-800">Weekly Question</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    What AI skills do you want to develop to prepare for your future career?
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* QR Code Challenges Panel - Modern Design */}
          <div className="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-2 border-cyan-200 hover:border-cyan-400">
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
            <div className="p-6 bg-gradient-to-br from-gray-50 to-white">
              <div className="space-y-4">
                <div className="bg-gradient-to-r from-cyan-50 to-blue-50 p-4 rounded-xl border-l-4 border-cyan-400">
                  <div className="flex items-center gap-2 mb-3">
                    <Globe className="w-4 h-4 text-cyan-600" />
                    <h4 className="font-semibold text-sm text-cyan-800">Scan QR Codes Below</h4>
                  </div>
                  <p className="text-sm text-gray-700 mb-4">
                    Use your phone camera to scan these QR codes and discover your school's AI policies and guidelines!
                  </p>
                  <div className="grid grid-cols-2 gap-3">
                    <div className="bg-white p-3 rounded-xl border-2 border-dashed border-cyan-300 text-center">
                      <div className="w-16 h-16 mx-auto mb-2 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <QrCode className="w-8 h-8 text-cyan-600" />
                      </div>
                      <p className="text-xs text-gray-600 font-medium">School Policy</p>
                    </div>
                    <div className="bg-white p-3 rounded-xl border-2 border-dashed border-cyan-300 text-center">
                      <div className="w-16 h-16 mx-auto mb-2 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <QrCode className="w-8 h-8 text-cyan-600" />
                      </div>
                      <p className="text-xs text-gray-600 font-medium">AI Guidelines</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Interactive Corner */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          
          {/* Weekly Questions Box */}
          <div className="border-2 border-gray-400 overflow-hidden p-4 bg-muted text-foreground relative" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="flex items-center gap-2 mb-3">
              <Star className="w-5 h-5 text-yellow-400" />
              <h3 className="font-bold text-gray-900 dark:text-white">This Week's Questions</h3>
            </div>
            <div className="space-y-2">
              <div className="bg-red-900 p-2 rounded border-l-4 border-red-500">
                <p className="text-xs font-semibold text-red-400">BE SAFE</p>
                <p className="text-xs text-gray-300">How can we ensure AI tools are fair?</p>
              </div>
              <div className="bg-blue-900 p-2 rounded border-l-4 border-blue-500">
                <p className="text-xs font-semibold text-blue-400">BE SMART</p>
                <p className="text-xs text-gray-300">What are AI's strengths vs humans?</p>
              </div>
              <div className="bg-green-900 p-2 rounded border-l-4 border-green-500">
                <p className="text-xs font-semibold text-green-400">BE CREATIVE</p>
                <p className="text-xs text-gray-300">How can AI enhance creativity?</p>
              </div>
            </div>
            {/* Decorative corner polygon */}
            <div className="absolute top-2 right-2 w-6 h-6 bg-white/10 rounded-sm" 
                 style={{ clipPath: 'polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%)' }}></div>
          </div>

          {/* Response Pocket */}
          <div className="border-2 border-dashed border-gray-400 overflow-hidden p-4 bg-muted text-foreground relative" 
               style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
            <div className="flex items-center gap-2 mb-3">
              <MessageSquare className="w-5 h-5 text-green-400" />
              <h3 className="font-bold text-gray-900 dark:text-white">Student Responses</h3>
            </div>
            <div className="bg-gray-700 p-4 rounded border-2 border-dashed border-green-400 text-center">
              <div className="space-y-2">
                <div className="bg-yellow-900 p-2 rounded text-xs text-gray-300 border border-yellow-700/50">
                  "AI should be transparent about how it makes decisions" - Sarah
                </div>
                <div className="bg-blue-900 p-2 rounded text-xs text-gray-300 border border-blue-700/50">
                  "Humans are better at understanding emotions" - Marcus
                </div>
                <div className="bg-green-900 p-2 rounded text-xs text-gray-300 border border-green-700/50">
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
        <div className="border-2 border-gray-400 overflow-hidden p-4 bg-gray-800 text-white mb-6 relative" 
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
         <div className="border-2 border-gray-400 overflow-hidden p-4 bg-muted text-foreground mb-6 relative" 
              style={{ clipPath: 'polygon(0 0, calc(100% - 15px) 0, 100% 15px, 100% 100%, 15px 100%, 0 calc(100% - 15px))' }}>
           <div className="flex items-center gap-2 mb-3">
             <Users className="w-5 h-5 text-purple-400" />
             <h3 className="font-bold text-gray-900 dark:text-white">Student Spotlight</h3>
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
