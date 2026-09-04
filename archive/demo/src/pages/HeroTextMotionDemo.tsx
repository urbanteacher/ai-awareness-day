import { motion, useReducedMotion } from 'framer-motion'
import { Link } from 'react-router-dom'

const spring = { type: 'spring' as const, stiffness: 120, damping: 18 }

export function HeroTextMotionDemo() {
  const reduce = useReducedMotion()

  const container = {
    hidden: {},
    show: {
      transition: {
        staggerChildren: reduce ? 0 : 0.18,
        delayChildren: reduce ? 0 : 0.08,
      },
    },
  }

  const line = {
    hidden: reduce ? { opacity: 1, y: 0 } : { opacity: 0, y: 28 },
    show: {
      opacity: 1,
      y: 0,
      transition: reduce ? { duration: 0 } : spring,
    },
  }

  const yearChars = '2027'.split('')

  return (
    <div className="min-h-svh bg-white text-neutral-900">
      <div className="mx-auto max-w-3xl px-4 py-8">
        <Link
          to="/"
          className="text-sm text-neutral-500 underline-offset-4 hover:text-neutral-900 hover:underline"
        >
          ← All demos
        </Link>
      </div>

      <motion.section
        className="flex min-h-[70vh] flex-col items-center justify-center px-6 text-center"
        variants={container}
        initial="hidden"
        animate="show"
        aria-label="AI Awareness Day 2027 hero"
      >
        <motion.p
          variants={line}
          className="font-heading text-[clamp(2.16rem,9.6vw,4.8rem)] font-semibold leading-[1.05] tracking-tight"
        >
          AI Awareness Day
        </motion.p>

        <motion.p
          variants={line}
          className="mt-2 font-heading text-[clamp(2rem,4vw,3rem)] font-semibold text-neutral-500"
          aria-label="2027"
        >
          {yearChars.map((char, i) => (
            <motion.span
              key={`${char}-${i}`}
              className="inline-block"
              initial={reduce ? false : { opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={
                reduce
                  ? { duration: 0 }
                  : { ...spring, delay: 0.35 + i * 0.06 }
              }
            >
              {char}
            </motion.span>
          ))}
        </motion.p>

        <motion.p
          variants={line}
          className="mt-6 max-w-xl text-[0.85rem] font-semibold uppercase tracking-[0.16em] text-neutral-400"
        >
          Know it, Question it, Use it Wisely
        </motion.p>

        <motion.p
          variants={line}
          className="mt-4 max-w-lg text-base leading-relaxed text-neutral-500"
        >
          A nationwide day for schools, students, and parents to explore AI
          together.
        </motion.p>
      </motion.section>
    </div>
  )
}
