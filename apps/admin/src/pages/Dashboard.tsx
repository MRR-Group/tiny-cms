import { Button } from '@/components/Button/Button';
import DocumentIcon from '@/assets/icons/document.svg?react';
import UsersIcon from '@/assets/icons/users.svg?react';
import ImageIcon from '@/assets/icons/image.svg?react';
import { useNavigate } from 'react-router-dom';

export function Dashboard() {
  const navigate = useNavigate();

  return (
    <div className="animate-in fade-in slide-in-from-bottom-4 duration-700">
      <div className="mb-10">
        <h1 className="text-4xl font-serif font-semibold text-slate-900 mb-2">Dashboard</h1>
        <p className="text-slate-500 font-sans text-sm tracking-tight opacity-80">
          System overview and statistics.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div className="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300">
          <div className="flex items-center gap-5">
            <div className="w-14 h-14 bg-primary/5 rounded-xl flex items-center justify-center">
              <DocumentIcon className="w-7 h-7 text-primary/70" />
            </div>
            <div>
              <p className="text-slate-400 text-[10px] font-sans uppercase tracking-[0.2em] font-medium mb-0.5">
                Total Pages
              </p>
              <p className="text-2xl font-sans font-light text-slate-800 tracking-tighter">0</p>
            </div>
          </div>
        </div>

        <div className="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300">
          <div className="flex items-center gap-5">
            <div className="w-14 h-14 bg-emerald-50 rounded-xl flex items-center justify-center">
              <UsersIcon className="w-7 h-7 text-emerald-500/70" />
            </div>
            <div>
              <p className="text-slate-400 text-[10px] font-sans uppercase tracking-[0.2em] font-medium mb-0.5">
                Active Users
              </p>
              <p className="text-2xl font-sans font-light text-slate-800 tracking-tighter">0</p>
            </div>
          </div>
        </div>

        <div className="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow duration-300">
          <div className="flex items-center gap-5">
            <div className="w-14 h-14 bg-amber-50 rounded-xl flex items-center justify-center">
              <ImageIcon className="w-7 h-7 text-amber-500/70" />
            </div>
            <div>
              <p className="text-slate-400 text-[10px] font-sans uppercase tracking-[0.2em] font-medium mb-0.5">
                Media Assets
              </p>
              <p className="text-2xl font-sans font-light text-slate-800 tracking-tighter">0</p>
            </div>
          </div>
        </div>
      </div>

      <div className="flex gap-4">
        <Button
          variant="primary"
          size="lg"
          className="rounded-full px-8 shadow-lg shadow-primary/20 hover:scale-105 transition-transform"
        >
          Create Page
        </Button>
        <Button
          variant="secondary"
          size="lg"
          className="rounded-full px-8 border-slate-200 bg-white text-slate-900 hover:bg-slate-50 transition-transform hover:scale-105 shadow-sm"
          onClick={() => navigate('/users/create')}
        >
          Create User
        </Button>
      </div>
    </div>
  );
}
