export interface LogoProps {
  variant?: 'branding' | 'sidebar-expanded' | 'sidebar-collapsed';
  className?: string;
  subtitle?: string;
}

export function Logo({ variant = 'branding', className = '', subtitle = 'CMS Admin' }: LogoProps) {
  if (variant === 'sidebar-collapsed') {
    return (
      <div className={`text-center ${className}`}>
        <h1 className="font-serif font-bold text-primary text-sm leading-tight transition-all duration-300">
          Tiny
          <br />
          CMS
        </h1>
      </div>
    );
  }

  const titleSizes = {
    branding: 'text-5xl',
    'sidebar-expanded': 'text-2xl',
  };

  const subtitleSizes = {
    branding: 'text-sm',
    'sidebar-expanded': 'text-xs',
  };

  return (
    <div className={`flex flex-col ${className}`}>
      <h1
        className={`font-serif font-bold text-primary ${titleSizes[variant as keyof typeof titleSizes] || 'text-2xl'} transition-all duration-300`}
      >
        TinyCMS
      </h1>
      {subtitle && (
        <p
          className={`text-slate-400 tracking-widest uppercase mt-1 font-sans font-medium ${subtitleSizes[variant as keyof typeof subtitleSizes] || 'text-xs'}`}
        >
          {subtitle}
        </p>
      )}
    </div>
  );
}
