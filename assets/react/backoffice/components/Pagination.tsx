export function Pagination({
  page,
  totalPages,
  onPageChange,
}: {
  page: number;
  totalPages: number;
  onPageChange: (page: number) => void;
}) {
  if (totalPages <= 1) return null;

  const pages: number[] = [];
  const start = Math.max(1, page - 2);
  const end = Math.min(totalPages, page + 2);

  for (let i = start; i <= end; i++) pages.push(i);

  return (
    <div className="bo-pagination">
      <button className="bo-page-btn" disabled={page <= 1} onClick={() => onPageChange(page - 1)}>‹</button>
      {start > 1 && (
        <>
          <button className="bo-page-btn" onClick={() => onPageChange(1)}>1</button>
          {start > 2 && <span style={{ padding: '0 4px', color: 'var(--bo-text-muted)' }}>…</span>}
        </>
      )}
      {pages.map((p) => (
        <button key={p} className={`bo-page-btn ${p === page ? 'active' : ''}`} onClick={() => onPageChange(p)}>
          {p}
        </button>
      ))}
      {end < totalPages && (
        <>
          {end < totalPages - 1 && <span style={{ padding: '0 4px', color: 'var(--bo-text-muted)' }}>…</span>}
          <button className="bo-page-btn" onClick={() => onPageChange(totalPages)}>{totalPages}</button>
        </>
      )}
      <button className="bo-page-btn" disabled={page >= totalPages} onClick={() => onPageChange(page + 1)}>›</button>
    </div>
  );
}
