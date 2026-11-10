import { Construction, Database } from 'lucide-react'
import { PageHeading } from '../components/PageHeading'

type WorkspacePageProps = { title: string; description: string }

export function WorkspacePage({ title, description }: WorkspacePageProps) {
  const isCharacters = title === 'My Characters'

  return (
    <>
      <PageHeading title={title} description={description} />
      <section className="grid min-h-72 place-items-center rounded-xl border border-dashed border-white/[0.13] bg-[#1c1c1a] px-6 py-12 text-center">
        <div className="max-w-md">
          <div className="mx-auto grid size-11 place-items-center rounded-xl border border-[#b99a5b]/20 bg-[#b99a5b]/[0.08] text-[#c8ad73]">{isCharacters ? <Database size={19} aria-hidden="true" /> : <Construction size={19} aria-hidden="true" />}</div>
          <h2 className="mt-4 text-base font-medium text-[#e1ded6]">{isCharacters ? 'Character catalog is being connected' : 'This workspace is planned'}</h2>
          <p className="mt-2 text-sm leading-6 text-[#85827a]">{isCharacters ? 'Character browsing will appear when the versioned game catalog is loaded. No sample character entries are being shown as real data.' : 'This section will be wired to real saved data in its implementation phase. There are no demo records here.'}</p>
        </div>
      </section>
    </>
  )
}
