import {
  BarChart3, Boxes, ChevronDown, CircleUserRound, Database, Gauge,
  LayoutDashboard, Settings2, ShieldCheck, Swords, UsersRound,
} from 'lucide-react'
import type { LucideIcon } from 'lucide-react'
import { NavLink, Outlet } from 'react-router-dom'

type NavigationItem = { to: string; label: string; icon: LucideIcon; end?: boolean }

const mainNavigation: NavigationItem[] = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard, end: true },
  { to: '/characters', label: 'My Characters', icon: UsersRound },
  { to: '/builds', label: 'Builds', icon: Boxes },
  { to: '/compare', label: 'Compare Builds', icon: BarChart3 },
  { to: '/optimizer', label: 'Optimizer', icon: Gauge },
  { to: '/teams', label: 'Teams', icon: UsersRound },
]

const accountNavigation: NavigationItem[] = [
  { to: '/import', label: 'Import Profile', icon: Database },
  { to: '/settings', label: 'Settings', icon: Settings2 },
]

function NavigationLink({ to, label, icon: Icon, end = false }: NavigationItem) {
  return (
    <NavLink
      to={to}
      end={end}
      className={({ isActive }) => `group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors ${isActive ? 'bg-[#b99a5b]/12 text-[#e4cc91]' : 'text-[#aaa79f] hover:bg-white/[0.045] hover:text-[#f1efe9]'}`}
    >
      {({ isActive }) => (
        <>
          <Icon size={17} strokeWidth={1.8} className={isActive ? 'text-[#c8ad73]' : 'text-[#77756f] group-hover:text-[#b6b1a7]'} aria-hidden="true" />
          <span className="flex-1">{label}</span>
          {isActive && <span className="size-1.5 rounded-full bg-[#c8ad73]" aria-hidden="true" />}
        </>
      )}
    </NavLink>
  )
}

export function AppShell() {
  return (
    <div className="min-h-screen bg-[#171716] text-[#f1efe9]">
      <aside className="fixed inset-y-0 left-0 z-20 hidden w-64 flex-col border-r border-white/[0.08] bg-[#1b1b19] md:flex">
        <div className="flex h-[72px] items-center gap-3 border-b border-white/[0.08] px-5">
          <div className="grid size-9 place-items-center rounded-lg bg-[#b99a5b] text-[#171716]"><Swords size={19} strokeWidth={2.2} aria-hidden="true" /></div>
          <div>
            <p className="text-[15px] font-semibold tracking-wide">BuildLab</p>
            <p className="text-[10px] uppercase tracking-[0.15em] text-[#77756f]">Character workspace</p>
          </div>
        </div>

        <nav aria-label="Main navigation" className="flex-1 overflow-y-auto px-3 py-5">
          <p className="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.17em] text-[#6f6d67]">Workspace</p>
          <div className="space-y-1">{mainNavigation.map((item) => <NavigationLink key={item.to} {...item} />)}</div>
          <p className="px-3 pb-2 pt-7 text-[10px] font-semibold uppercase tracking-[0.17em] text-[#6f6d67]">Manage</p>
          <div className="space-y-1">{accountNavigation.map((item) => <NavigationLink key={item.to} {...item} />)}</div>
          <div className="mt-8 rounded-lg border border-white/[0.08] bg-white/[0.025] p-3.5">
            <div className="mb-2 flex items-center gap-2 text-xs font-medium text-[#ddd8cc]"><ShieldCheck size={15} className="text-[#c8ad73]" aria-hidden="true" />Public data only</div>
            <p className="text-[11px] leading-5 text-[#85827a]">BuildLab never asks for your HoYoverse password or private account access.</p>
          </div>
        </nav>

        <div className="border-t border-white/[0.08] p-3">
          <div className="flex items-center gap-3 rounded-lg px-3 py-2.5">
            <div className="grid size-8 place-items-center rounded-full border border-white/10 bg-[#242421] text-[#aaa79f]"><CircleUserRound size={17} aria-hidden="true" /></div>
            <div className="min-w-0 flex-1"><p className="text-xs font-medium text-[#e0ddd6]">Local workspace</p><p className="text-[10px] text-[#77756f]">Account features coming later</p></div>
            <ChevronDown size={14} className="text-[#77756f]" aria-hidden="true" />
          </div>
        </div>
      </aside>

      <div className="md:pl-64">
        <header className="sticky top-0 z-10 border-b border-white/[0.08] bg-[#171716]/95 px-4 py-3 backdrop-blur md:hidden">
          <div className="mb-3 flex items-center gap-2.5">
            <div className="grid size-8 place-items-center rounded-lg bg-[#b99a5b] text-[#171716]"><Swords size={17} aria-hidden="true" /></div>
            <span className="text-sm font-semibold tracking-wide">BuildLab</span>
            <span className="ml-auto text-[10px] text-[#85827a]">Genshin Impact</span>
          </div>
          <nav aria-label="Mobile navigation" className="flex gap-1 overflow-x-auto pb-1">
            {[...mainNavigation, ...accountNavigation].map((item) => (
              <NavLink key={item.to} to={item.to} end={item.end} className={({ isActive }) => `shrink-0 rounded-md px-3 py-2 text-xs ${isActive ? 'bg-[#b99a5b]/12 text-[#e4cc91]' : 'text-[#aaa79f]'}`}>
                {item.label}
              </NavLink>
            ))}
          </nav>
        </header>

        <main className="min-h-screen px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
          <div className="mx-auto w-full max-w-7xl"><Outlet /></div>
        </main>
      </div>
    </div>
  )
}
