import { createContext, useContext, useState, useCallback, type ReactNode } from 'react';
import type { Notice, NoticeType } from '../types';

type NotificationCtx = {
  notice: Notice | null;
  showNotice: (message: string, type?: NoticeType) => void;
  hideNotice: () => void;
};

export const NotificationCtx = createContext<NotificationCtx>({
  notice: null,
  showNotice: () => {},
  hideNotice: () => {},
});

export function useNotification() {
  return useContext(NotificationCtx);
}

export function NotificationProvider({ children }: { children: ReactNode }) {
  const [notice, setNotice] = useState<Notice | null>(null);

  const showNotice = useCallback((message: string, type: NoticeType = 'info') => {
    setNotice({ message, type });
  }, []);

  const hideNotice = useCallback(() => {
    setNotice(null);
  }, []);

  return (
    <NotificationCtx.Provider value={{ notice, showNotice, hideNotice }}>
      {children}
    </NotificationCtx.Provider>
  );
}
