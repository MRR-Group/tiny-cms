import { ReactNode } from 'react';
import { Logo } from '@/components/Logo/Logo';

export function AuthLayout({ children, subtitle }: { children: ReactNode; subtitle?: string }) {
  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-10">
          <div className="flex justify-center mb-3">
            <Logo variant="branding" subtitle='' />
          </div>
          {subtitle && <p className="text-slate-500 font-medium tracking-tight mt-3">{subtitle}</p>}
        </div>

        <div className="bg-white border border-slate-200 rounded-3xl p-10 shadow-xl shadow-slate-200/50">
          {children}
        </div>
      </div>
    </div>
  );
}
