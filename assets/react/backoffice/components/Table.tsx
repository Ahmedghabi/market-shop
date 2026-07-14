import { motion } from 'framer-motion';
import type { ReactNode } from 'react';
import type { TableColumn, SortConfig } from '../types';
import { EmptyState } from './States';

export function Table<T extends { id: string }>({
  columns,
  data,
  sort,
  onSort,
  onRowClick,
  renderActions,
  isLoading,
}: {
  columns: TableColumn<T>[];
  data: T[];
  sort?: SortConfig;
  onSort?: (field: string) => void;
  onRowClick?: (item: T) => void;
  renderActions?: (item: T) => ReactNode;
  isLoading?: boolean;
}) {
  if (!isLoading && data.length === 0) {
    return <EmptyState />;
  }

  const hasActions = Boolean(onRowClick || renderActions);

  return (
    <div className="bo-table-wrapper">
      <table className="bo-table">
        <thead>
          <tr>
            {columns.map((col) => (
              <th
                key={col.key}
                aria-sort={sort?.field === col.key ? (sort.direction === 'asc' ? 'ascending' : 'descending') : undefined}
                className={sort?.field === col.key ? 'sorted' : ''}
                style={col.width ? { width: col.width } : undefined}
              >
                {col.sortable && onSort ? (
                  <button type="button" className="bo-table-sort" onClick={() => onSort(col.key)}>
                    {col.label}
                    {sort?.field === col.key && <span aria-hidden="true">{sort.direction === 'asc' ? ' ↑' : ' ↓'}</span>}
                  </button>
                ) : col.label}
              </th>
            ))}
            {hasActions && <th style={{ width: 80 }}>Actions</th>}
          </tr>
        </thead>
        <tbody>
          {data.map((item, index) => (
            <motion.tr
              key={item.id}
              onClick={onRowClick ? () => onRowClick(item) : undefined}
              onKeyDown={onRowClick ? (event) => {
                if ('Enter' === event.key || ' ' === event.key) {
                  event.preventDefault();
                  onRowClick(item);
                }
              } : undefined}
              tabIndex={onRowClick ? 0 : undefined}
              style={onRowClick ? { cursor: 'pointer' } : undefined}
              initial={{ opacity: 0, y: 6 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.18, delay: Math.min(index, 12) * 0.02 }}
            >
              {columns.map((col) => (
                <td key={col.key}>{col.render(item)}</td>
              ))}
              {hasActions && <td>
                <div className="cell-actions">
                  {renderActions?.(item)}
                </div>
              </td>}
            </motion.tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
