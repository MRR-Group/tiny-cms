import { Link, Outlet } from 'react-router-dom'

export function BaseLayout() {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-4xl items-center justify-between p-4">
          <Link to="/" className="text-lg font-semibold">
            Tiny CMS Admin
          </Link>
          <nav className="flex gap-4 text-sm text-slate-600">
            <span className="opacity-75">Dashboard</span>
            <span className="opacity-75">Content</span>
          </nav>
        </div>
      </header>
      <main className="mx-auto max-w-4xl p-4">
        <Outlet />
      </main>
    </div>
  )
}
