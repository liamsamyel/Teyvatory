import { z } from 'zod'

const characterSchema = z.object({
  gameId: z.string(),
  name: z.string(),
  rarity: z.number().nullable(),
  iconKey: z.string().nullable(),
  element: z.object({ key: z.string(), name: z.string(), color: z.string().nullable() }).nullable(),
  weaponType: z.object({ key: z.string(), name: z.string() }).nullable(),
})

const catalogResponseSchema = z.object({
  data: z.array(characterSchema),
  meta: z.object({
    catalogState: z.enum(['not_loaded', 'ready']),
    release: z.object({
      source: z.string(),
      sourceVersion: z.string(),
      gameVersion: z.string().nullable(),
      importedAt: z.string().nullable(),
      attribution: z.string().nullable(),
    }).nullable(),
    currentPage: z.number(),
    lastPage: z.number(),
    perPage: z.number(),
    total: z.number(),
  }),
})

export type CatalogCharacter = z.infer<typeof characterSchema>
export type CatalogResponse = z.infer<typeof catalogResponseSchema>

export type CatalogFilters = {
  q: string
  element: string
  weaponType: string
  rarity: string
}

export async function fetchCharacters(filters: CatalogFilters, signal?: AbortSignal): Promise<CatalogResponse> {
  const params = new URLSearchParams()
  if (filters.q.trim()) params.set('q', filters.q.trim())
  if (filters.element) params.set('element', filters.element)
  if (filters.weaponType) params.set('weapon_type', filters.weaponType)
  if (filters.rarity) params.set('rarity', filters.rarity)

  const query = params.size ? `?${params.toString()}` : ''
  const response = await fetch(`/api/v1/catalog/characters${query}`, {
    headers: { Accept: 'application/json' },
    signal,
  })

  if (!response.ok) {
    throw new Error(`Character catalog request failed (${response.status})`)
  }

  const payload: unknown = await response.json()
  return catalogResponseSchema.parse(payload)
}
