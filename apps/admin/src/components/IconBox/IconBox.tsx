import type { ReactNode } from 'react';

export interface IconBoxProps {
  children: ReactNode;
  isActive?: boolean;
  className?: string;
}

export function IconBox({ children, isActive, className = '' }: IconBoxProps) {
  return (
    <div
      className={`
        w-10 h-10 flex items-center justify-center flex-shrink-0 
        transition-all duration-200 rounded-lg 
        ${isActive ? 'bg-primary/10 text-primary shadow-sm' : 'text-slate-500 group-hover:bg-white group-hover:text-primary'}
        ${className}
      `}
    >
      {children}
    </div>
  );
}
