import { Route, Routes } from 'react-router-dom'

import { ActivityCardsDemo } from '@/pages/ActivityCardsDemo'
import { BlogLayoutsDemo } from '@/pages/BlogLayoutsDemo'
import { FeedStylesDemo } from '@/pages/FeedStylesDemo'
import { AliImamDemo } from '@/pages/AliImamDemo'
import { BalloonsDemo } from '@/pages/BalloonsDemo'
import { CertificateDemo } from '@/pages/CertificateDemo'
import { ComponentryDemo } from '@/pages/ComponentryDemo'
import { CultDemo } from '@/pages/CultDemo'
import { DotMatrixDemo } from '@/pages/DotMatrixDemo'
import { Home } from '@/pages/Home'
import { MetalDemo } from '@/pages/MetalDemo'
import { SkiperDemo } from '@/pages/SkiperDemo'
import { StyleUIDemo } from '@/pages/StyleUIDemo'
import { WatermelonDemo } from '@/pages/WatermelonDemo'

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/cards" element={<ActivityCardsDemo />} />
      <Route path="/blog" element={<BlogLayoutsDemo />} />
      <Route path="/feeds" element={<FeedStylesDemo />} />
      <Route path="/metal" element={<MetalDemo />} />
      <Route path="/styleui" element={<StyleUIDemo />} />
      <Route path="/skiper" element={<SkiperDemo />} />
      <Route path="/aliimam" element={<AliImamDemo />} />
      <Route path="/watermelon" element={<WatermelonDemo />} />
      <Route path="/cult" element={<CultDemo />} />
      <Route path="/dotmatrix" element={<DotMatrixDemo />} />
      <Route path="/componentry" element={<ComponentryDemo />} />
      <Route path="/balloons" element={<BalloonsDemo />} />
      <Route path="/certificate" element={<CertificateDemo />} />
    </Routes>
  )
}
