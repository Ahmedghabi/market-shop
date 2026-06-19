import type { ButtonHTMLAttributes, ReactNode } from 'react';

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger';
type Size = 'sm' | 'md';

export function Button({
  children,
  variant = 'primary',
  size = 'md',
  className = '',
  ...props
}: {
  children: ReactNode;
  variant?: Variant;
  size?: Size;
  className?: string;
} & ButtonHTMLAttributes<HTMLButtonElement>) {
  const cls = `bo-btn bo-btn-${variant} ${size === 'sm' ? 'bo-btn-sm' : ''} ${className}`;
  return (
    <button className={cls} {...props}>
      {children}
    </button>
  );
}
