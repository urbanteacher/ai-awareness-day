import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { ThemeProvider } from "@/components/theme-provider";
import { ThemeScript } from "@/components/theme-script";
import { Toaster } from "sonner";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "Ai Awareness Day - National Campaign",
  description: "Join the national campaign for AI awareness in schools. Comprehensive resources including activities, assemblies, and teaching materials to help students understand AI safely and responsibly across the UK.",
  keywords: ["AI", "Education", "Schools", "Awareness", "Activities", "Assemblies", "Teaching", "Students", "Safety", "National Campaign", "UK"],
  authors: [{ name: "AiAwarenessDay Team" }],
  creator: "AiAwarenessDay",
  publisher: "AiAwarenessDay",
  openGraph: {
    type: "website",
    locale: "en_GB",
    url: "https://aiawarenessday.co.uk",
    title: "Ai Awareness Day - National Campaign",
    description: "Join the national campaign for AI awareness in schools. Comprehensive resources including activities, assemblies, and teaching materials to help students understand AI safely and responsibly across the UK.",
    siteName: "Ai Awareness Day",
  },
  twitter: {
    card: "summary_large_image",
    title: "Ai Awareness Day - National Campaign",
    description: "Join the national campaign for AI awareness in schools. Comprehensive resources including activities, assemblies, and teaching materials to help students understand AI safely and responsibly across the UK.",
    creator: "@aiawarenessday",
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      "max-video-preview": -1,
      "max-image-preview": "large",
      "max-snippet": -1,
    },
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
        <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)" />
        <meta name="theme-color" content="#0a0a0a" media="(prefers-color-scheme: dark)" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
        <ThemeScript />
      </head>
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased font-sans`}
      >
        <ThemeProvider
          defaultTheme="system"
          storageKey="ui-theme"
        >
          {children}
          <Toaster richColors />
        </ThemeProvider>
      </body>
    </html>
  );
}
