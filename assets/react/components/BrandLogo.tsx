type BrandLogoProps = {
  compact?: boolean;
  className?: string;
  alt?: string;
};

export function BrandLogo({ compact = false, className = '', alt = 'Hanooti' }: BrandLogoProps) {
  return (
    <img
      src={compact ? '/img/hanooti-mark.svg' : '/img/hanooti-logo.svg'}
      alt={alt}
      className={`hanooti-logo ${className}`.trim()}
    />
  );
}
