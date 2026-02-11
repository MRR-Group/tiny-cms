import { Outlet, useNavigate, NavLink } from 'react-router-dom';
import HomeIcon from '@/assets/icons/home.svg?react';
import LogoutIcon from '@/assets/icons/logout.svg?react';
import DocumentIcon from '@/assets/icons/document.svg?react';
import UsersIcon from '@/assets/icons/users.svg?react';
import ChevronLeftIcon from '@/assets/icons/chevron-left.svg?react';
import { createAuthService } from '@/domain/auth';
import { useState, useEffect } from 'react';
import { Logo } from '@/components/Logo/Logo';
import { SideMenuItem } from '@/components/SideMenuItem/SideMenuItem';

export function Layout() {
  const navigate = useNavigate();
  const [isCollapsed, setIsCollapsed] = useState(window.innerWidth < 768);

  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth < 768) {
        setIsCollapsed(true);
      }
    };

    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const handleLogout = () => {
    createAuthService().logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen flex bg-slate-50">
      <aside
        className={`bg-white border-r border-slate-200 p-4 flex flex-col shadow-sm transition-all duration-300 ease-in-out relative ${
          isCollapsed ? 'w-20' : 'w-64'
        }`}
      >
        <button
          onClick={() => setIsCollapsed(!isCollapsed)}
          className="absolute -right-3 top-8 bg-white border border-slate-200 rounded-full p-1.5 shadow-sm hover:bg-slate-50 hover:text-primary transition-colors z-10"
          title={isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
        >
          <ChevronLeftIcon
            className={`w-4 h-4 text-slate-500 transition-transform duration-300 ${isCollapsed ? 'rotate-180' : ''}`}
          />
        </button>

        <div
          className={`mb-8 flex items-center min-h-[60px] ${isCollapsed ? 'justify-center' : 'px-2'}`}
        >
          <Logo variant={isCollapsed ? 'sidebar-collapsed' : 'sidebar-expanded'} />
        </div>

        <nav className="space-y-2 flex-1">
          <NavLink
            to={createAuthService().getUserRole() === 'admin' ? '/admin/sites' : '/'}
            className="block"
          >
            {({ isActive }) => (
              <SideMenuItem
                icon={createAuthService().getUserRole() === 'admin' ? DocumentIcon : HomeIcon}
                label={createAuthService().getUserRole() === 'admin' ? 'Sites' : 'Dashboard'}
                isCollapsed={isCollapsed}
                isActive={isActive}
              />
            )}
          </NavLink>

          {createAuthService().getUserRole() === 'admin' && (
            <>
              <NavLink to="/users/create" className="block">
                {({ isActive }) => (
                  <SideMenuItem
                    icon={UsersIcon}
                    label="Add User"
                    isCollapsed={isCollapsed}
                    isActive={isActive}
                  />
                )}
              </NavLink>
            </>
          )}
        </nav>

        <div className="pt-4 border-t border-slate-100 mt-auto">
          <button onClick={handleLogout} className="w-full text-left">
            <SideMenuItem icon={LogoutIcon} label="Logout" isCollapsed={isCollapsed} />
          </button>
        </div>
      </aside>

      <main className="flex-1 p-8 overflow-y-auto transition-all duration-300">
        <div className="max-w-6xl mx-auto">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
