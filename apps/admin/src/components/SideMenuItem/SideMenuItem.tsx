import React, { HTMLAttributes } from 'react';
import { IconBox } from '@/components/IconBox/IconBox';

export interface SideMenuItemProps extends HTMLAttributes<HTMLDivElement> {
    icon: React.FunctionComponent<React.SVGProps<SVGSVGElement>>;
    label: string;
    isActive?: boolean;
    isCollapsed?: boolean;
}

export function SideMenuItem({
    icon: Icon,
    label,
    isActive,
    isCollapsed,
    className = '',
    ...props
}: SideMenuItemProps) {
    return (
        <div
            className={`
        group flex items-center px-2 py-1.5 rounded-lg transition-all duration-200 cursor-pointer w-full
        ${isCollapsed ? 'justify-center' : ''}
        ${isActive ? 'bg-primary/5' : 'hover:bg-slate-50'}
        ${className}
      `}
            title={isCollapsed ? label : ''}
            {...props}
        >
            <IconBox isActive={isActive}>
                <Icon className="w-5 h-5" />
            </IconBox>
            <span
                className={`
          font-medium whitespace-nowrap overflow-hidden transition-all duration-300 
          ${isCollapsed ? 'w-0 opacity-0 ml-0' : 'w-auto opacity-100 ml-3'}
          ${isActive ? 'text-primary' : 'text-slate-600 group-hover:text-primary'}
        `}
            >
                {label}
            </span>
        </div>
    );
}
