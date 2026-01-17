import { Outlet, useNavigate, Link, useLocation } from 'react-router-dom';
import HomeIcon from '@/assets/icons/home.svg?react';
import LogoutIcon from '@/assets/icons/logout.svg?react';
import { authService } from '@/domain/auth';

export function Layout() {
  const navigate = useNavigate();
  const location = useLocation();

  const handleLogout = () => {
    authService.logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen flex bg-slate-50 font-sans">
      <aside className="w-64 bg-white border-r border-slate-200 p-6 flex flex-col shadow-sm">
        <div className="mb-8 p-2">
          <h1 className="text-2xl font-serif font-bold text-primary">TinyCMS</h1>
          <p className="text-slate-400 text-xs tracking-widest uppercase mt-1 font-sans">
            CMS Admin
          </p>
        </div>

        <nav className="space-y-1.5 flex-1">
          <Link
            to="/"
            className={`flex items-center gap-3 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 ${
              location.pathname === '/'
                ? 'bg-primary/10 text-primary shadow-sm'
                : 'text-slate-600 hover:bg-slate-50 hover:text-primary'
            }`}
          >
            <HomeIcon className="w-5 h-5" />
            Dashboard
          </Link>
        </nav>

        <div className="pt-6 border-t border-slate-100">
          <button
            onClick={handleLogout}
            className="flex w-full items-center gap-3 px-4 py-2.5 rounded-lg text-slate-500 font-medium transition-all duration-200 hover:bg-red-50 hover:text-red-600"
          >
            <LogoutIcon className="w-5 h-5" />
            Logout
          </button>
        </div>
      </aside>

      <main className="flex-1 p-8 overflow-y-auto">
        <div className="max-w-6xl mx-auto">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
