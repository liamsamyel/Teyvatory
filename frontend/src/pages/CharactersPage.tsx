import { useQuery } from '@tanstack/react-query'
import { Search, UserRound } from 'lucide-react'
import { useState } from 'react'
import { PageHeading } from '../components/PageHeading'
import { fetchCharacters, type CatalogFilters } from '../features/catalog/catalogApi'

const elementOptions = [
  ['pyro', 'Pyro'], ['hydro', 'Hydro'], ['dendro', 'Dendro'], ['electro', 'Electro'],
  ['anemo', 'Anemo'], ['cryo', 'Cryo'], ['geo', 'Geo'],
]

const weaponOptions = [
  ['sword', 'Sword'], ['claymore', 'Claymore'], ['polearm', 'Polearm'], ['bow', 'Bow'], ['catalyst', 'Catalyst'],
]

const initialFilters: CatalogFilters = { q: '', element: '', weaponType: '', rarity: '' }

function CharacterCard({ character }: { character: Awaited<ReturnType<typeof fetchCharacters>>['data'][number] }) {
  return (
    <article className="overflow-hidden rounded-xl border border-white/[0.09] bg-[#1f1f1d] transition-colors hover:border-white/[0.16]">
      <div className="relative grid h-40 place-items-center bg-[#252522]">
        <div className="absolute inset-x-0 bottom-0 h-14 bg-gradient-to-t from-[#1f1f1d] to-transparent" aria-hidden="true" />
        {character.iconKey ? (
          <img
            src={`https://enka.network/ui/${encodeURIComponent(character.iconKey)}.png`}
            alt={`${character.name} icon`}
            loading="lazy"
            className="relative z-[1] h-36 w-36 object-contain object-bottom"
            onError={(event) => { event.currentTarget.style.visibility = 'hidden' }}
          />
        ) : (
          <UserRound size={42} className="text-[#77756f]" aria-hidden="true" />
        )}
      </div>
      <div className="p-4">
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <h2 className="truncate text-sm font-medium text-[#e5e2da]">{character.name}</h2>
            <p className="mt-1 text-[11px] text-[#85827a]">{character.weaponType?.name ?? 'Weapon type unavailable'}</p>
          </div>
          <span className="shrink-0 text-xs tracking-[0.08em] text-[#c8ad73]" aria-label={character.rarity ? `${character.rarity} star rarity` : 'Rarity unavailable'}>
            {character.rarity ? '★'.repeat(character.rarity) : '—'}
          </span>
        </div>
        <div className="mt-3 flex items-center gap-2 border-t border-white/[0.07] pt-3">
          {character.element?.color && <span className="size-2 rounded-full" style={{ backgroundColor: character.element.color }} aria-hidden="true" />}
          <span className="text-[11px] text-[#96938b]">{character.element?.name ?? 'Element unavailable'}</span>
        </div>
      </div>
    </article>
  )
}

function FilterSelect({ label, value, options, onChange }: {
  label: string
  value: string
  options: string[][]
  onChange: (value: string) => void
}) {
  return (
    <label className="flex min-w-32 flex-col gap-1.5 text-[10px] font-medium uppercase tracking-wider text-[#85827a]">
      {label}
      <select value={value} onChange={(event) => onChange(event.target.value)} className="h-10 rounded-lg border border-white/[0.1] bg-[#20201e] px-3 text-xs normal-case tracking-normal text-[#d7d3ca] outline-none focus:border-[#b99a5b]/70">
        <option value="">All {label.toLowerCase()}</option>
        {options.map(([key, name]) => <option key={key} value={key}>{name}</option>)}
      </select>
    </label>
  )
}

export function CharactersPage() {
  const [filters, setFilters] = useState(initialFilters)
  const query = useQuery({
    queryKey: ['catalog', 'characters', filters],
    queryFn: ({ signal }) => fetchCharacters(filters, signal),
  })

  function updateFilter(key: keyof CatalogFilters, value: string) {
    setFilters((current) => ({ ...current, [key]: value }))
  }

  return (
    <>
      <PageHeading title="My Characters" description="Browse the shared game catalog and, when available, your imported showcase characters." />

      <div className="mb-6 flex flex-col gap-3 rounded-xl border border-white/[0.09] bg-[#1f1f1d] p-4 sm:flex-row sm:items-end">
        <label className="flex min-w-48 flex-1 flex-col gap-1.5 text-[10px] font-medium uppercase tracking-wider text-[#85827a]">
          Search characters
          <span className="relative">
            <Search size={15} className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#77756f]" aria-hidden="true" />
            <input value={filters.q} onChange={(event) => updateFilter('q', event.target.value)} placeholder="Name or game ID" className="h-10 w-full rounded-lg border border-white/[0.1] bg-[#20201e] pl-9 pr-3 text-xs normal-case tracking-normal text-[#dedad1] placeholder:text-[#6f6d67] outline-none focus:border-[#b99a5b]/70" />
          </span>
        </label>
        <FilterSelect label="Element" value={filters.element} options={elementOptions} onChange={(value) => updateFilter('element', value)} />
        <FilterSelect label="Weapon" value={filters.weaponType} options={weaponOptions} onChange={(value) => updateFilter('weaponType', value)} />
        <FilterSelect label="Rarity" value={filters.rarity} options={['5', '4', '3', '2', '1'].map((value) => [value, `${value} star`])} onChange={(value) => updateFilter('rarity', value)} />
      </div>

      {query.isPending && <div className="rounded-xl border border-white/[0.09] bg-[#1f1f1d] p-8 text-sm text-[#96938b]" role="status">Loading character catalog…</div>}
      {query.isError && <div className="rounded-xl border border-red-900/50 bg-red-950/20 p-5 text-sm text-red-200" role="alert">Couldn’t load the catalog. Check that the Laravel API is running, then retry.</div>}
      {query.data?.meta.catalogState === 'not_loaded' && (
        <div className="rounded-xl border border-dashed border-white/[0.13] bg-[#1c1c1a] px-6 py-12 text-center">
          <p className="text-sm font-medium text-[#ddd8cc]">The game catalog hasn’t been loaded yet.</p>
          <p className="mx-auto mt-2 max-w-lg text-xs leading-5 text-[#85827a]">Character records will appear here after a source version is validated and imported. The page won’t show invented sample entries.</p>
        </div>
      )}
      {query.data?.meta.catalogState === 'ready' && query.data.data.length === 0 && (
        <div className="rounded-xl border border-white/[0.09] bg-[#1f1f1d] px-6 py-12 text-center text-sm text-[#96938b]">No characters match those filters.</div>
      )}
      {query.data && query.data.meta.catalogState === 'ready' && query.data.data.length > 0 && (
        <>
          <p className="mb-4 text-xs text-[#85827a]">{query.data.meta.total} catalog characters</p>
          <div className="grid grid-cols-1 gap-3 min-[420px]:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            {query.data.data.map((character) => <CharacterCard key={character.gameId} character={character} />)}
          </div>
          {query.data.meta.release?.attribution && <p className="mt-6 text-[10px] text-[#77756f]">Catalog data: {query.data.meta.release.attribution}</p>}
        </>
      )}
    </>
  )
}
