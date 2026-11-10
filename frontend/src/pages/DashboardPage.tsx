import { ArrowRight, Boxes, Database, UsersRound } from 'lucide-react'
import { Link } from 'react-router-dom'
import { PageHeading } from '../components/PageHeading'

const summary = [
  { label: 'Characters', icon: UsersRound, value: 'Catalog pending', detail: 'No character records loaded' },
  { label: 'Saved builds', icon: Boxes, value: 'Not available yet', detail: 'Build management is next' },
  { label: 'Profile sync', icon: Database, value: 'Not connected', detail: 'Import preview is planned for Phase 3' },
]

export function DashboardPage() {
  return (
    <>
      <PageHeading eyebrow="Your build workspace" title="Dashboard" description="A clear view of your showcase imports and saved build ideas. The catalog and account workflows are being connected in phases." />
      <section aria-label="Workspace status" className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        {summary.map(({ label, icon: Icon, value, detail }) => (
          <article key={label} className="rounded-xl border border-white/[0.09] bg-[#1f1f1d] p-5">
            <div className="mb-7 flex items-center justify-between"><p className="text-xs font-medium text-[#a8a59d]">{label}</p><Icon size={17} className="text-[#b99a5b]" aria-hidden="true" /></div>
            <p className="text-lg font-medium text-[#e7e4dc]">{value}</p>
            <p className="mt-1 text-xs text-[#77756f]">{detail}</p>
          </article>
        ))}
      </section>

      <section className="mt-7 grid gap-4 lg:grid-cols-[1.5fr_1fr]">
        <article className="rounded-xl border border-white/[0.09] bg-[#1f1f1d] p-6 sm:p-8">
          <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#b99a5b]">Getting started</p>
          <h2 className="mt-3 text-xl font-semibold tracking-tight">BuildLab is ready for your catalog.</h2>
          <p className="mt-3 max-w-xl text-sm leading-6 text-[#96938b]">The app shell is in place. Next we’re connecting versioned game data so character cards and build editors can show real catalog records instead of demo content.</p>
          <Link to="/characters" className="mt-6 inline-flex items-center gap-2 text-sm font-medium text-[#d1b878] hover:text-[#ead297]">Open My Characters <ArrowRight size={15} aria-hidden="true" /></Link>
        </article>
        <article className="rounded-xl border border-white/[0.09] bg-[#1f1f1d] p-6">
          <div className="flex items-center gap-2 text-sm font-medium text-[#ddd8cc]"><Database size={16} className="text-[#b99a5b]" aria-hidden="true" />Data boundaries</div>
          <ul className="mt-4 space-y-3 text-xs leading-5 text-[#96938b]">
            <li className="border-l border-[#b99a5b]/50 pl-3">Imported showcase data will remain distinct from your saved builds.</li>
            <li className="border-l border-white/10 pl-3">Temporary optimizer inputs won’t become inventory records.</li>
            <li className="border-l border-white/10 pl-3">Nothing here is placeholder player or character data.</li>
          </ul>
        </article>
      </section>
    </>
  )
}
