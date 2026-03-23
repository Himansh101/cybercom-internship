import { create } from 'zustand';

let _nextId = 1;
const uid = () => String(_nextId++);

/**
 * Filter store — manages filter groups, sorting, date range, and compare mode.
 */
export const useFilterStore = create((set, get) => ({
  searchQuery:  '',
  filterGroups: [],          // [{ id, operator:'AND'|'OR', rules:[] }]
  sorting:      { field: 'created_at', direction: 'desc' },
  dateRange:    { start: '', end: '' },
  compareMode:  null,        // 'previous_period' | 'same_last_year' | null

  // ── Search ──────────────────────────────────────────────────────────────────
  setSearchQuery: (q) => set({ searchQuery: q }),

  // ── Groups ──────────────────────────────────────────────────────────────────
  addGroup: () => set((s) => ({
    filterGroups: [...s.filterGroups, { id: uid(), operator: 'AND', rules: [] }],
  })),

  removeGroup: (groupId) => set((s) => ({
    filterGroups: s.filterGroups.filter((g) => g.id !== groupId),
  })),

  setGroupOperator: (groupId, operator) => set((s) => ({
    filterGroups: s.filterGroups.map((g) =>
      g.id === groupId ? { ...g, operator } : g
    ),
  })),

  // ── Rules ───────────────────────────────────────────────────────────────────
  addFilter: (groupId, rule = {}) => set((s) => ({
    filterGroups: s.filterGroups.map((g) =>
      g.id === groupId
        ? { ...g, rules: [...g.rules, { id: uid(), field: '', type: 'text', value: '', ...rule }] }
        : g
    ),
  })),

  removeFilter: (groupId, ruleId) => set((s) => ({
    filterGroups: s.filterGroups.map((g) =>
      g.id === groupId
        ? { ...g, rules: g.rules.filter((r) => r.id !== ruleId) }
        : g
    ),
  })),

  updateFilter: (groupId, ruleId, changes) => set((s) => ({
    filterGroups: s.filterGroups.map((g) =>
      g.id === groupId
        ? { ...g, rules: g.rules.map((r) => r.id === ruleId ? { ...r, ...changes } : r) }
        : g
    ),
  })),

  // ── Sorting ─────────────────────────────────────────────────────────────────
  setSort: (field, direction) => set({ sorting: { field, direction } }),

  toggleSort: (field) => set((s) => ({
    sorting: {
      field,
      direction: s.sorting.field === field && s.sorting.direction === 'asc' ? 'desc' : 'asc',
    },
  })),

  // ── Date range ──────────────────────────────────────────────────────────────
  setDateRange: (start, end) => set({ dateRange: { start, end } }),

  // ── Compare mode ────────────────────────────────────────────────────────────
  setCompareMode: (mode) => set({ compareMode: mode }),

  /**
   * Replace filter state from a saved view config.
   */
  applySavedConfig: (config = {}) => set({
    searchQuery: config.search_query ?? '',
    filterGroups: Array.isArray(config.filters) ? config.filters : [],
    sorting: config.sorting ?? { field: 'created_at', direction: 'desc' },
    dateRange: config.date_range ?? { start: '', end: '' },
    compareMode: config.compare_mode ?? null,
  }),

  // ── Reset all filters ────────────────────────────────────────────────────────
  resetAll: () => set({
    searchQuery:  '',
    filterGroups: [],
    sorting:      { field: 'created_at', direction: 'desc' },
    dateRange:    { start: '', end: '' },
    compareMode:  null,
  }),

  /** Serialize current filters into the API payload shape */
  toPayload: () => {
    const { searchQuery, filterGroups, sorting, dateRange, compareMode } = get();

    const filters = filterGroups.map((g) => ({
      type:     'group',
      operator: g.operator,
      rules:    g.rules
        .filter((r) => r.field)
        .map(({ id, ...r }) => r),   // strip internal id
    })).filter((g) => g.rules.length > 0);

    return { q: searchQuery, filters, sort: sorting, date_range: dateRange, compare_mode: compareMode };
  },
}));
