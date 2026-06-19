import type { InputHTMLAttributes, SelectHTMLAttributes, TextareaHTMLAttributes, ReactNode } from 'react';

type FormFieldProps = {
  label: string;
  error?: string;
  hint?: string;
  required?: boolean;
  children: ReactNode;
};

export function FormField({ label, error, hint, required, children }: FormFieldProps) {
  return (
    <div className="bo-form-group">
      <label className="bo-form-label">
        {label}
        {required && <span style={{ color: 'var(--bo-error)' }}> *</span>}
      </label>
      {children}
      {error && <span className="bo-input-error">{error}</span>}
      {hint && !error && <span className="bo-form-hint">{hint}</span>}
    </div>
  );
}

export function Input(props: InputHTMLAttributes<HTMLInputElement>) {
  return <input className={`bo-input ${props.className ?? ''}`} {...props} />;
}

export function Select(props: SelectHTMLAttributes<HTMLSelectElement>) {
  return <select className={`bo-select ${props.className ?? ''}`} {...props} />;
}

export function Textarea(props: TextareaHTMLAttributes<HTMLTextAreaElement>) {
  return <textarea className={`bo-textarea ${props.className ?? ''}`} {...props} />;
}
