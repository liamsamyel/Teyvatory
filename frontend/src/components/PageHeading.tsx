import type { ReactNode } from 'react'

type PageHeadingProps = {
  eyebrow?: string
  title: string
  description: string
  action?: ReactNode
}

export function PageHeading({ eyebrow = 'BuildLab workspace', title, description, action }: PageHeadingProps) {
  return (
    <div className="mb-8 flex flex-col justify-between gap-5 border-b border-white/[0.08] pb-7 sm:flex-row sm:items-end">
      <div>
        <p className="mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#b99a5b]">{eyebrow}</p>
        <h1 className="text-2xl font-semibold tracking-tight text-[#f1efe9] sm:text-[30px]">{title}</h1>
        <p className="mt-2 max-w-2xl text-sm leading-6 text-[#96938b]">{description}</p>
      </div>
      {action && <div className="shrink-0">{action}</div>}
    </div>
  )
}
