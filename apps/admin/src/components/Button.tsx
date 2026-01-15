import type { ButtonHTMLAttributes } from 'react'

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  label: string
}

const baseClasses =
  'rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500'

export function Button({ label, className = '', ...props }: ButtonProps) {
  const classes = [baseClasses, className].filter(Boolean).join(' ')

  return (
    <button className={classes} {...props}>
      {label}
    </button>
  )
}
