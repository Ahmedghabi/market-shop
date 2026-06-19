import type { TableColumn, SortConfig } from '../types';

export function Table<T extends { id: string }>({
  columns,
  data,
  sort,
  onSort,
  onRowClick,
  isLoading,
}: {
  columns: TableColumn<T>[];
  data: T[];
  sort?: SortConfig;
  onSort?: (field: string) => void;
  onRowClick?: (item: T) => void;
  isLoading?: boolean;
}) {
  return (
    <div className="bo-table-wrapper">
      <table className="bo-table">
        <thead>
          <tr>
            {columns.map((col) => (
              <th
                key={col.key}
                className={sort?.field === col.key ? 'sorted' : ''}
                style={col.width ? { width: col.width } : undefined}
                onClick={col.sortable && onSort ? () => onSort(col.key) : undefined}
              >
                {col.label}
                {col.sortable && sort?.field === col.key && (
                  <span>{sort.direction === 'asc' ? ' ↑' : ' ↓'}</span>
                )}
              </th>
            ))}
            <th style={{ width: 80 }}>Actions</th>
          </tr>
        </thead>
        <tbody>
          {data.map((item) => (
            <tr
              key={item.id}
              onClick={onRowClick ? () => onRowClick(item) : undefined}
              style={onRowClick ? { cursor: 'pointer' } : undefined}
            >
              {columns.map((col) => (
                <td key={col.key}>{col.render(item)}</td>
              ))}
              <td>
                <div className="cell-actions">
                  {onRowClick && (
                    <button className="bo-btn bo-btn-ghost bo-btn-sm" onClick={(e) => { e.stopPropagation(); onRowClick(item); }}>
                      Voir
                    </button>
                  )}
                </div>
              </td>
            </tr>
          ))}
          {!isLoading && data.length === 0 && (
            <tr>
              <td colSpan={columns.length + 1} style={{ textAlign: 'center', padding: 40, color: 'var(--bo-text-muted)' }}>
                Aucune donnée
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );
}
