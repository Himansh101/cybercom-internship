import { useState } from 'react';
import { useReportStore } from '../../store/reportStore.js';

/**
 * ColumnSelector — dropdown popover with checkboxes for toggling visible columns.
 */
export default function ColumnSelector({ schema }) {
  const [open, setOpen] = useState(false);
  const { visibleColumns, setVisibleColumns } = useReportStore();

  const toggle = (name) => {
    if (visibleColumns.includes(name)) {
      if (visibleColumns.length === 1) return; // keep at least one
      setVisibleColumns(visibleColumns.filter((c) => c !== name));
    } else {
      setVisibleColumns([...visibleColumns, name]);
    }
  };

  return (
    <div className="relative">
      <button className="btn-secondary text-xs" onClick={() => setOpen((v) => !v)}>
        ⊞ Columns
      </button>

      {open && (
        <>
          {/* Backdrop */}
          <div className="fixed inset-0 z-10" onClick={() => setOpen(false)} />

          {/* Popover */}
          <div className="absolute right-0 top-8 z-20 card p-3 w-48 shadow-lg space-y-1">
            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
              Visible columns
            </p>
            {schema.map((f) => (
              <label key={f.name} className="flex items-center gap-2 cursor-pointer hover:bg-gray-50 px-1 py-0.5 rounded">
                <input
                  type="checkbox"
                  checked={visibleColumns.includes(f.name)}
                  onChange={() => toggle(f.name)}
                  className="rounded border-gray-300 text-blue-600"
                />
                <span className="text-sm text-gray-700">{f.label}</span>
              </label>
            ))}
          </div>
        </>
      )}
    </div>
  );
}
