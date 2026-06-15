import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'

import './index.css'
import App from './App.tsx'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <BrowserRouter>
      <div className="theme min-h-svh bg-background font-sans text-foreground antialiased">
        <App />
      </div>
    </BrowserRouter>
  </StrictMode>,
)
