import { InputHTMLAttributes, forwardRef } from 'react';

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, helperText, className = '', id, ...props }, ref) => {
    return (
      <div className="space-y-1.5 w-full">
        {label && (
          <label htmlFor={id} className="block text-sm font-semibold text-slate-700 mb-2 ml-1">
            {label}
          </label>
        )}
        <input
          id={id}
          ref={ref}
          className={`
            w-full px-4 py-3 bg-white border border-slate-200 rounded-xl 
            text-slate-900 placeholder-slate-400 text-sm
            focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary 
            transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed
            ${error ? 'border-red-500 focus:ring-red-500/20 focus:border-red-500' : ''}
            ${className}
          `}
          {...props}
        />
        {error ? (
          <p className="text-xs text-red-500 font-medium ml-1 mt-1">{error}</p>
        ) : helperText ? (
          <p className="text-xs text-slate-400 font-medium ml-1 mt-1">{helperText}</p>
        ) : null}
      </div>
    );
  }
);

Input.displayName = 'Input';
