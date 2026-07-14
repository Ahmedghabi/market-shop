import type { HTMLAttributes, ReactNode, ButtonHTMLAttributes, InputHTMLAttributes, SelectHTMLAttributes, TextareaHTMLAttributes } from 'react';
import { motion } from 'framer-motion';

type CommonProps = {
  children: ReactNode;
  className?: string;
};

type Variant = 'primary' | 'secondary' | 'ghost';

type MotionSafe<T> = Omit<T, 'onDrag' | 'onDragStart' | 'onDragEnd' | 'onAnimationStart' | 'onAnimationEnd' | 'onAnimationIteration'>;
type NativeButtonProps = MotionSafe<ButtonHTMLAttributes<HTMLButtonElement>>;

export function Button({ children, className = '', variant = 'primary', disabled, ...props }: CommonProps & NativeButtonProps & { variant?: Variant }) {
  return (
    <motion.button
      className={`ds-btn ds-btn--${variant} ${className}`.trim()}
      disabled={disabled}
      whileHover={disabled ? undefined : { y: -1 }}
      whileTap={disabled ? undefined : { scale: 0.97 }}
      transition={{ type: 'spring', stiffness: 500, damping: 30 }}
      {...props}
    >
      {children}
    </motion.button>
  );
}

export function Card({ children, className = '', ...props }: CommonProps & MotionSafe<HTMLAttributes<HTMLElement>>) {
  return (
    <motion.section
      className={`ds-card ${className}`.trim()}
      initial={{ opacity: 0, y: 14 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
      {...props}
    >
      {children}
    </motion.section>
  );
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
