import { useEffect } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { useNotification } from '../hooks/useNotification';

const icons: Record<string, string> = {
  success: '✓',
  error: '!',
  warning: '!',
  info: 'i',
};

export function ToastContainer() {
  const { notice, hideNotice } = useNotification();

  useEffect(() => {
    if (notice) {
      const timer = setTimeout(hideNotice, 4000);
      return () => clearTimeout(timer);
    }
  }, [notice, hideNotice]);

  return (
    <div className="bo-toast-container">
      <AnimatePresence>
        {notice && (
          <motion.div
            key={notice.message}
            className={`bo-toast bo-toast-${notice.type}`}
            initial={{ opacity: 0, x: 60, scale: 0.94 }}
            animate={{ opacity: 1, x: 0, scale: 1 }}
            exit={{ opacity: 0, x: 40, scale: 0.96, transition: { duration: 0.15 } }}
            transition={{ type: 'spring', stiffness: 420, damping: 34 }}
          >
            <span className="bo-toast-icon">{icons[notice.type] ?? 'i'}</span>
            <span>{notice.message}</span>
            <button className="bo-toast-close" onClick={hideNotice} aria-label="Fermer">
              &times;
            </button>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
