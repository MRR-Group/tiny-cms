import { ButtonHTMLAttributes, ReactNode } from 'react';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  children: ReactNode;
}

const variantClasses = {
  primary: 'bg-primary hover:bg-primary/90 text-white shadow-md shadow-primary/20',
  secondary: 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm',
  danger: 'bg-red-500 hover:bg-red-600 text-white shadow-md shadow-red-200',
  ghost: 'bg-transparent hover:bg-slate-50 text-slate-600',
};

const sizeClasses = {
  sm: 'px-3 py-1.5 text-xs font-medium uppercase tracking-wider',
  md: 'px-6 py-2.5 text-sm font-medium',
  lg: 'px-8 py-3.5 text-base font-medium',
};

export function Button({
  variant = 'primary',
  size = 'md',
  children,
  className = '',
  disabled,
  ...props
}: ButtonProps) {
  return (
    <button
      className={`
        inline-flex items-center justify-center gap-2
        rounded-xl
        transition-all duration-300
        active:scale-95
        focus:outline-none focus:ring-2 focus:ring-primary/20
        disabled:opacity-50 disabled:cursor-not-allowed
        ${variantClasses[variant]}
        ${sizeClasses[size]}
        ${className}
      `}
      disabled={disabled}
      {...props}
    >
      {children}
    </button>
  );
}
