import { SelectHTMLAttributes, forwardRef } from 'react';

export interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string;
  options: { value: string; label: string }[];
}

export const Select = forwardRef<HTMLSelectElement, SelectProps>(
  ({ label, error, options, className = '', id, ...props }, ref) => {
    return (
      <div className="space-y-1.5 w-full">
        {label && (
          <label
            htmlFor={id}
            className="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest ml-1"
          >
            {label}
          </label>
        )}
        <div className="relative">
          <select
            id={id}
            ref={ref}
            className={`
              w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl 
              text-slate-900 text-sm appearance-none
              focus:outline-none focus:ring-2 focus:ring-primary/10 focus:border-primary/20 focus:bg-white 
              transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed
              ${error ? 'border-red-200 focus:ring-red-500/10 focus:border-red-500/20' : ''}
              ${className}
            `}
            {...props}
          >
            {options.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
          <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="12"
              height="12"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="m6 9 6 6 6-6" />
            </svg>
          </div>
        </div>
        {error && <p className="text-[10px] text-red-500 font-medium ml-1">{error}</p>}
      </div>
    );
  }
);

Select.displayName = 'Select';
