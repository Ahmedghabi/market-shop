import { useEffect, useState, type ImgHTMLAttributes } from 'react';

const DEFAULT_MEDIA_IMAGE = '/img/hanooti-mark.svg';

type ImageWithFallbackProps = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src'> & {
  src?: string | null;
  fallbackSrc?: string;
};

export function ImageWithFallback({ src, fallbackSrc = DEFAULT_MEDIA_IMAGE, alt = '', onError, ...props }: ImageWithFallbackProps) {
  const [currentSrc, setCurrentSrc] = useState(src || fallbackSrc);

  useEffect(() => {
    setCurrentSrc(src || fallbackSrc);
  }, [fallbackSrc, src]);

  return (
    <img
      {...props}
      src={currentSrc}
      alt={alt}
      onError={(event) => {
        onError?.(event);
        if (currentSrc !== fallbackSrc) setCurrentSrc(fallbackSrc);
      }}
    />
  );
}
