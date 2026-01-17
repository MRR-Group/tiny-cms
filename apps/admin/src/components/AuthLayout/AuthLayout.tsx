import { ReactNode } from 'react';

export function AuthLayout({ children, subtitle }: { children: ReactNode; subtitle?: string }) {
  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans">
      <div className="w-full max-w-md">
        <div className="text-center mb-10">
          <h1 className="text-5xl font-serif font-bold text-primary mb-3">TinyCMS</h1>
          {subtitle && <p className="text-slate-500 font-medium tracking-tight">{subtitle}</p>}
        </div>

        <div className="bg-white border border-slate-200 rounded-3xl p-10 shadow-xl shadow-slate-200/50">
          {children}
        </div>
      </div>
    </div>
  );
}
