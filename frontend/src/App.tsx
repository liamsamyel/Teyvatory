import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { AppShell } from './layout/AppShell'
import { CharactersPage } from './pages/CharactersPage'
import { DashboardPage } from './pages/DashboardPage'
import { WorkspacePage } from './pages/WorkspacePage'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { refetchOnWindowFocus: false, retry: 1, staleTime: 30_000 },
  },
})

const sections = [
  { path: 'builds', title: 'Builds', description: 'Create and organize independent builds for each character.' },
  { path: 'compare', title: 'Build Comparison', description: 'Review stat and equipment trade-offs between saved builds.' },
  { path: 'optimizer', title: 'Artifact Optimizer', description: 'Explore combinations from artifacts you provide to the workspace.' },
  { path: 'teams', title: 'Team Builder', description: 'Arrange characters into saved teams and assign roles.' },
  { path: 'import', title: 'Import Profile', description: 'Preview public showcase data before adding an import snapshot.' },
  { path: 'settings', title: 'Settings', description: 'Manage preferences and connected game UID references.' },
]

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          <Route element={<AppShell />}>
            <Route index element={<DashboardPage />} />
            <Route path="characters" element={<CharactersPage />} />
            {sections.map((section) => (
              <Route key={section.path} path={section.path} element={<WorkspacePage title={section.title} description={section.description} />} />
            ))}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </QueryClientProvider>
  )
}

export default App
