import { motion } from 'framer-motion';
import type { TableColumn, SortConfig } from '../types';
import { EmptyState } from './States';

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
  if (!isLoading && data.length === 0) {
    return <EmptyState />;
  }

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
          {data.map((item, index) => (
            <motion.tr
              key={item.id}
              onClick={onRowClick ? () => onRowClick(item) : undefined}
              style={onRowClick ? { cursor: 'pointer' } : undefined}
              initial={{ opacity: 0, y: 6 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.18, delay: Math.min(index, 12) * 0.02 }}
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
            </motion.tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
