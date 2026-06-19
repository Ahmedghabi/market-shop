import type { HTMLAttributes, ReactNode, ButtonHTMLAttributes, InputHTMLAttributes, SelectHTMLAttributes, TextareaHTMLAttributes } from 'react';

type CommonProps = {
  children: ReactNode;
  className?: string;
};

type Variant = 'primary' | 'secondary' | 'ghost';

export function Button({ children, className = '', variant = 'primary', ...props }: CommonProps & ButtonHTMLAttributes<HTMLButtonElement> & { variant?: Variant }) {
  return (
    <button className={`ds-btn ds-btn--${variant} ${className}`.trim()} {...props}>
      {children}
    </button>
  );
}

export function Card({ children, className = '', ...props }: CommonProps & HTMLAttributes<HTMLElement>) {
  return <section className={`ds-card ${className}`.trim()} {...props}>{children}</section>;
}

export function Panel({ children, className = '', ...props }: CommonProps & HTMLAttributes<HTMLElement>) {
  return <section className={`ds-panel ${className}`.trim()} {...props}>{children}</section>;
}

export function Badge({ children, className = '', tone = 'neutral' }: CommonProps & { tone?: 'success' | 'warning' | 'error' | 'neutral' }) {
  return <span className={`ds-badge ds-badge--${tone} ${className}`.trim()}>{children}</span>;
}

export function Input(props: InputHTMLAttributes<HTMLInputElement>) {
  return <input {...props} className={`ds-input ${props.className ?? ''}`.trim()} />;
}

export function Select(props: SelectHTMLAttributes<HTMLSelectElement>) {
  return <select {...props} className={`ds-select ${props.className ?? ''}`.trim()} />;
}

export function Textarea(props: TextareaHTMLAttributes<HTMLTextAreaElement>) {
  return <textarea {...props} className={`ds-textarea ${props.className ?? ''}`.trim()} />;
}

export function PageSection({ children, className = '', ...props }: CommonProps & HTMLAttributes<HTMLElement>) {
  return <section className={`ds-section ${className}`.trim()} {...props}>{children}</section>;
}
