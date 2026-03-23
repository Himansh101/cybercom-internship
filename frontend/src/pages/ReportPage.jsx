import { useEffect, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api } from '../services/api.js';
import { useReportStore } from '../store/reportStore.js';
import { useFilterStore } from '../store/filterStore.js';
import { useReport } from '../hooks/useReport.js';
import FilterBuilder from '../components/FilterBuilder/FilterBuilder.jsx';
import DataTable from '../components/DataTable/DataTable.jsx';
import ColumnSelector from '../components/DataTable/ColumnSelector.jsx';
import ChartRenderer from '../components/ChartRenderer/ChartRenderer.jsx';
import SavedViews from '../components/SavedViews/SavedViews.jsx';
import AdminUpload from '../components/AdminUpload/AdminUpload.jsx';

/**
 * ReportPage — main orchestration page.
 * Loads schema, wires all components together, handles date compare toolbar.
 */
export default function ReportPage({ onLogout }) {
  const [sidebarOpen, setSidebarOpen] = useState(() => (
    typeof window === 'undefined' ? true : window.innerWidth >= 768
  ));
  const [filterOpen,  setFilterOpen]  = useState(false);

  const { setSchema, schema, total, isLoading } = useReportStore();
  const {
    dateRange,
    setDateRange,
    compareMode,
    setCompareMode,
    applyFilters,
    hasUnappliedChanges,
  } = useFilterStore();

  // Load schema on mount
  const { data: schemaRes } = useQuery({
    queryKey: ['schema'],
    queryFn:  () => api.getSchema().then((r) => r.data),
    staleTime: Infinity,
  });

  useEffect(() => {
    if (schemaRes) setSchema(schemaRes);
  }, [schemaRes]);

  // Activate the report query
  useReport();

  const user = (() => { try { return JSON.parse(localStorage.getItem('user') ?? '{}'); } catch { return {}; } })();
  const isAdmin = user.role === 'admin';

  const handleExport = () => {
    const { toAppliedPayload } = useFilterStore.getState();
    const { getActiveColumns } = useReportStore.getState();
    api.exportReport({ ...toAppliedPayload(), columns: getActiveColumns() });
  };

  const hasPendingFilterChanges = hasUnappliedChanges();

  if (schema.length === 0) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center space-y-2">
          <div className="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto" />
          <p className="text-gray-500 text-sm">Loading schema…</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-100 flex flex-col">
      {/* ── Top bar ───────────────────────────────────────────────────────── */}
      <header className="bg-white border-b border-gray-200 px-4 py-2 flex flex-wrap items-center gap-3 shadow-sm">
        <button
          className="text-gray-500 hover:text-gray-700"
          onClick={() => setSidebarOpen((v) => !v)}
          title="Toggle saved views"
        >
          ☰
        </button>
        <h1 className="font-semibold text-gray-800 text-base">Dynamic Reports</h1>

        <div className="hidden md:block flex-1" />

        {/* Date range */}
        <div className="flex flex-wrap items-center gap-2 text-xs w-full md:w-auto">
          <span className="text-gray-500">From</span>
          <input
            type="date"
            className="input py-1 w-32"
            value={dateRange.start}
            onChange={(e) => setDateRange(e.target.value, dateRange.end)}
          />
          <span className="text-gray-500">To</span>
          <input
            type="date"
            className="input py-1 w-32"
            value={dateRange.end}
            onChange={(e) => setDateRange(dateRange.start, e.target.value)}
          />
        </div>

        {/* Compare mode */}
        <div className="flex rounded border border-gray-300 overflow-hidden text-xs w-full md:w-auto">
          {[
            { label: 'No compare', value: null },
            { label: 'Prev period', value: 'previous_period' },
            { label: 'Last year', value: 'same_last_year' },
          ].map((opt) => (
            <button
              key={String(opt.value)}
              className={`px-2.5 py-1 transition-colors ${
                compareMode === opt.value
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-600 hover:bg-gray-100'
              }`}
              onClick={() => setCompareMode(opt.value)}
            >
              {opt.label}
            </button>
          ))}
        </div>

        <div className="w-full md:w-auto">
          <ColumnSelector schema={schema} />
        </div>

        <button className="btn-secondary text-xs" onClick={() => setFilterOpen((v) => !v)}>
          ⚙ Filters {filterOpen ? '▲' : '▼'}
        </button>

        <button
          className={`btn-primary text-xs ${hasPendingFilterChanges ? '' : 'opacity-60'}`}
          onClick={applyFilters}
          disabled={!hasPendingFilterChanges}
        >
          Apply
        </button>

        <button className="btn-secondary text-xs" onClick={handleExport}>
          ⬇ CSV
        </button>

        {isAdmin && <AdminUpload />}

        {/* User / logout */}
        <div className="flex items-center gap-2 text-xs text-gray-500 ml-auto">
          <span>{user.name ?? 'User'}</span>
          <button className="btn-secondary py-1 px-2" onClick={onLogout}>Logout</button>
        </div>
      </header>

      {/* ── Body ─────────────────────────────────────────────────────────── */}
      <div className="relative flex flex-1 overflow-hidden">
        {sidebarOpen && (
          <button
            className="fixed inset-0 z-20 bg-black/20 md:hidden"
            aria-label="Close saved views"
            onClick={() => setSidebarOpen(false)}
          />
        )}
        {/* Sidebar — saved views */}
        {sidebarOpen && (
          <aside className="fixed inset-y-0 left-0 top-[57px] z-30 w-72 max-w-[85vw] bg-white border-r border-gray-200 flex flex-col overflow-hidden shadow-lg md:static md:top-auto md:z-0 md:w-56 md:max-w-none md:shadow-none">
            <SavedViews />
          </aside>
        )}

        {/* Main content */}
        <main className="min-w-0 flex-1 overflow-auto p-4 space-y-4">
          {/* Loading indicator */}
          {isLoading && (
            <div className="flex items-center gap-2 text-xs text-blue-600">
              <div className="w-3 h-3 border border-blue-600 border-t-transparent rounded-full animate-spin" />
              Loading…
            </div>
          )}

          {/* Filters panel (collapsible) */}
          {filterOpen && <FilterBuilder schema={schema} />}

          {/* Chart */}
          <ChartRenderer schema={schema} />

          {/* Data table */}
          <DataTable schema={schema} />
        </main>
      </div>
    </div>
  );
}
