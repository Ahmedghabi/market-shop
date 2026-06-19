import { useEffect, type ReactNode } from 'react';

export function Modal({
  isOpen,
  onClose,
  title,
  children,
  footer,
  width,
}: {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: ReactNode;
  footer?: ReactNode;
  width?: string;
}) {
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    }
    return () => { document.body.style.overflow = ''; };
  }, [isOpen]);

  if (!isOpen) return null;

  return (
    <div className="bo-overlay" onClick={onClose}>
      <div className="bo-modal" style={width ? { maxWidth: width } : undefined} onClick={(e) => e.stopPropagation()}>
        {title && (
          <div className="bo-modal-header">
            <h3>{title}</h3>
            <button className="bo-modal-close" onClick={onClose}>&times;</button>
          </div>
        )}
        <div className="bo-modal-body">{children}</div>
        {footer && <div className="bo-modal-footer">{footer}</div>}
      </div>
    </div>
  );
}
