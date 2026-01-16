import { Outlet } from 'react-router-dom';
import HomeIcon from '@/assets/icons/home.svg?react';

export function Layout() {
  return (
    <div className="min-h-screen flex">
      <aside className="w-64 bg-slate-900 border-r border-slate-700 p-6">
        <div className="mb-8">
          <h1 className="text-xl font-bold bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">
            Tiny CMS
          </h1>
          <p className="text-slate-500 text-sm mt-1">Admin Panel</p>
        </div>

        <nav className="space-y-2">
          <a
            href="/"
            className="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-primary-500/10 text-primary-400 font-medium transition-colors"
          >
            <HomeIcon className="w-5 h-5" />
            Dashboard
          </a>
        </nav>
      </aside>

      <main className="flex-1 p-8">
        <Outlet />
      </main>
    </div>
  );
}
