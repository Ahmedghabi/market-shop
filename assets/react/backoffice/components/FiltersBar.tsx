import { Input, Select } from './FormField';

export function FiltersBar({
  search,
  onSearchChange,
  status,
  onStatusChange,
  statusOptions,
  children,
}: {
  search?: string;
  onSearchChange?: (v: string) => void;
  status?: string;
  onStatusChange?: (v: string) => void;
  statusOptions?: { value: string; label: string }[];
  children?: React.ReactNode;
}) {
  return (
    <div className="bo-filters">
      {onSearchChange && (
        <Input
          placeholder="Rechercher..."
          value={search ?? ''}
          onChange={(e) => onSearchChange(e.target.value)}
        />
      )}
      {onStatusChange && statusOptions && (
        <Select value={status ?? ''} onChange={(e) => onStatusChange(e.target.value)}>
          <option value="">Tous les statuts</option>
          {statusOptions.map((opt) => (
            <option key={opt.value} value={opt.value}>{opt.label}</option>
          ))}
        </Select>
      )}
      {children}
    </div>
  );
}
