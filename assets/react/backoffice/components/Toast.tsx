import { useEffect } from 'react';
import { useNotification } from '../hooks/useNotification';

export function ToastContainer() {
  const { notice, hideNotice } = useNotification();

  useEffect(() => {
    if (notice) {
      const timer = setTimeout(hideNotice, 4000);
      return () => clearTimeout(timer);
    }
  }, [notice, hideNotice]);

  if (!notice) return null;

  return (
    <div className="bo-toast-container">
      <div className={`bo-toast bo-toast-${notice.type}`}>
        <span>{notice.message}</span>
        <button className="bo-toast-close" onClick={hideNotice}>&times;</button>
      </div>
    </div>
  );
}
