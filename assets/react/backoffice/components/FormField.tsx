import { cloneElement, isValidElement, useId, type InputHTMLAttributes, type SelectHTMLAttributes, type TextareaHTMLAttributes, type ReactElement, type ReactNode } from 'react';

type FormFieldProps = {
  label: string;
  error?: string;
  hint?: string;
  required?: boolean;
  children: ReactNode;
};

export function FormField({ label, error, hint, required, children }: FormFieldProps) {
  const fieldId = useId();
  const descriptionId = `${fieldId}-description`;
  const describedBy = error || hint ? descriptionId : undefined;
  const field = isValidElement(children)
    ? cloneElement(children as ReactElement<Record<string, unknown>>, {
        id: fieldId,
        'aria-describedby': describedBy,
        'aria-invalid': error ? true : undefined,
      })
    : children;

  return (
    <div className="bo-form-group">
      <label className="bo-form-label" htmlFor={fieldId}>
        {label}
        {required && <span style={{ color: 'var(--bo-error)' }}> *</span>}
      </label>
      {field}
      {(error || hint) && <span id={descriptionId} className={error ? 'bo-input-error' : 'bo-form-hint'}>{error || hint}</span>}
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
