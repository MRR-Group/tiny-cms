import type { ButtonHTMLAttributes } from 'react';

const Button = ({ className = '', ...props }: ButtonHTMLAttributes<HTMLButtonElement>) => {
  return (
    <button
      className={`rounded bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 ${className}`}
      type="button"
      {...props}
    />
  );
};

export default Button;
