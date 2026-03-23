import { create } from 'zustand';

/**
 * Report store — manages schema, visible columns, column widths/order,
 * paginated data, and saved views.
 */
export const useReportStore = create((set, get) => ({
  schema:         [],      // full schema from /api/schema
  visibleColumns: [],      // subset of schema field names shown in table
  columnOrder:    [],      // ordered list of field names (after drag-reorder)
  columnWidths:   {},      // { fieldName: widthPx }
  data:           [],      // current page docs
  compareData:    null,    // comparison period docs (or null)
  total:          0,
  page:           1,
  perPage:        50,
  isLoading:      false,
  error:          null,
  savedViews:     [],
  activeViewId:   null,

  // ── Schema ───────────────────────────────────────────────────────────────────
  setSchema: (schema) => {
    const defaults = schema.map((f) => f.name);
    set({ schema, visibleColumns: defaults, columnOrder: defaults });
  },

  // ── Columns ──────────────────────────────────────────────────────────────────
  setVisibleColumns: (cols) => set({ visibleColumns: cols }),

  setColumnOrder: (order) => set({ columnOrder: order }),

  setColumnWidth: (field, width) =>
    set((s) => ({ columnWidths: { ...s.columnWidths, [field]: Math.max(60, width) } })),

  // ── Data ─────────────────────────────────────────────────────────────────────
  setData: (data, total, compareData = null) => set({ data, total, compareData }),

  setPage: (page) => set({ page }),

  setPerPage: (perPage) => set({ perPage, page: 1 }),

  setLoading: (isLoading) => set({ isLoading }),

  setError: (error) => set({ error }),

  // ── Saved views ──────────────────────────────────────────────────────────────
  setSavedViews: (savedViews) => set({ savedViews }),

  /**
   * Load a saved view — restores columns, widths, and marks the active view.
   *
   * @param {Object} view  Saved view object from the API.
   */
  loadView: (view) => {
    const config = view.config ?? {};
    set({
      activeViewId:   view.id,
      visibleColumns: config.columns     ?? get().visibleColumns,
      columnOrder:    config.columns     ?? get().columnOrder,
      columnWidths:   config.column_widths ?? {},
      page:           1,
    });
  },

  /** Build the columns portion of the API payload */
  getActiveColumns: () => {
    const { columnOrder, visibleColumns } = get();
    return columnOrder.filter((c) => visibleColumns.includes(c));
  },
}));
