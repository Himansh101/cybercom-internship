import { create } from 'zustand';
import { arrayMove } from '@dnd-kit/sortable';

let _nextId = 1;
const uid = () => String(_nextId++);

const syncNextId = (groups = []) => {
  const numericIds = groups.flatMap((group) => [
    Number(group?.id),
    ...(Array.isArray(group?.rules) ? group.rules.map((rule) => Number(rule?.id)) : []),
  ]).filter((value) => Number.isFinite(value));

  _nextId = numericIds.length > 0 ? Math.max(...numericIds) + 1 : 1;
};

const normalizeSavedGroups = (groups = []) => {
  const sortedGroups = [...groups].sort(
    (a, b) => (a?.position ?? Number.MAX_SAFE_INTEGER) - (b?.position ?? Number.MAX_SAFE_INTEGER)
  );

  return sortedGroups.map((group, groupIndex) => ({
    id: String(group?.id ?? uid()),
    operator: group?.operator === 'OR' ? 'OR' : 'AND',
    rules: [...(Array.isArray(group?.rules) ? group.rules : [])]
      .sort((a, b) => (a?.position ?? Number.MAX_SAFE_INTEGER) - (b?.position ?? Number.MAX_SAFE_INTEGER))
      .map((rule) => ({
        id: String(rule?.id ?? uid()),
        field: rule?.field ?? '',
        type: rule?.type ?? 'text',
        value: rule?.value ?? '',
        from: rule?.from ?? '',
        to: rule?.to ?? '',
        position: rule?.position ?? rule?.order ?? 0,
      })),
    position: group?.position ?? groupIndex,
  }));
};

const serializeGroups = (groups = []) => groups.map((group, groupIndex) => ({
  id: group.id,
  type: 'group',
  operator: group.operator,
  position: groupIndex,
  rules: group.rules
    .filter((rule) => rule.field)
    .map((rule, ruleIndex) => ({
      field: rule.field,
      type: rule.type,
      value: rule.value,
      from: rule.from,
      to: rule.to,
      position: ruleIndex,
    })),
}));

const buildPayloadFromConfig = (config = {}) => ({
  q: config.search_query ?? '',
  filters: serializeGroups(config.filterGroups ?? []),
  filter_groups_operator: config.filter_groups_operator ?? 'AND',
  sort: config.sorting ?? { field: 'created_at', direction: 'desc' },
  date_range: config.date_range ?? { start: '', end: '' },
  compare_mode: config.compare_mode ?? null,
});

/**
 * Filter store — manages filter groups, sorting, date range, and compare mode.
 */
export const useFilterStore = create((set, get) => ({
  searchQuery:  '',
  filterGroups: [],          // [{ id, operator:'AND'|'OR', rules:[] }]
  filterGroupsOperator: 'AND',
  sorting:      { field: 'created_at', direction: 'desc' },
  dateRange:    { start: '', end: '' },
  compareMode:  null,        // 'previous_period' | 'same_last_year' | null
  appliedConfig: {
    search_query: '',
    filterGroups: [],
    filter_groups_operator: 'AND',
    sorting: { field: 'created_at', direction: 'desc' },
    date_range: { start: '', end: '' },
    compare_mode: null,
  },

  // ── Search ──────────────────────────────────────────────────────────────────
  setSearchQuery: (q) => set({ searchQuery: q }),

  // ── Groups ──────────────────────────────────────────────────────────────────
  addGroup: () => set((s) => ({
    filterGroups: [...s.filterGroups, { id: uid(), operator: 'AND', rules: [] }],
  })),

  removeGroup: (groupId) => set((s) => ({
    filterGroups: s.filterGroups.filter((g) => g.id !== groupId),
  })),

  reorderGroups: (activeId, overId) => set((s) => {
    const oldIndex = s.filterGroups.findIndex((group) => group.id === activeId);
    const newIndex = s.filterGroups.findIndex((group) => group.id === overId);
    if (oldIndex === -1 || newIndex === -1 || oldIndex === newIndex) {
      return s;
    }

    const filterGroups = arrayMove(s.filterGroups, oldIndex, newIndex).map((group, index) => ({
      ...group,
      position: index,
    }));

    return { filterGroups };
  }),

  setGroupOperator: (groupId, operator) => set((s) => ({
    filterGroups: s.filterGroups.map((g) =>
      g.id === groupId ? { ...g, operator } : g
    ),
  })),

  setFilterGroupsOperator: (operator) => set({
    filterGroupsOperator: operator === 'OR' ? 'OR' : 'AND',
  }),

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

  reorderFilter: (groupId, activeRuleId, overRuleId) => set((s) => ({
    filterGroups: s.filterGroups.map((group) => {
      if (group.id !== groupId) {
        return group;
      }

      const oldIndex = group.rules.findIndex((rule) => rule.id === activeRuleId);
      const newIndex = group.rules.findIndex((rule) => rule.id === overRuleId);
      if (oldIndex === -1 || newIndex === -1 || oldIndex === newIndex) {
        return group;
      }

      return {
        ...group,
        rules: arrayMove(group.rules, oldIndex, newIndex).map((rule, index) => ({
          ...rule,
          position: index,
        })),
      };
    }),
  })),

  // ── Sorting ─────────────────────────────────────────────────────────────────
  setSort: (field, direction) => set((s) => ({
    sorting: { field, direction },
    appliedConfig: {
      ...s.appliedConfig,
      sorting: { field, direction },
    },
  })),

  toggleSort: (field) => set((s) => {
    const nextSorting = {
      field,
      direction: s.sorting.field === field && s.sorting.direction === 'asc' ? 'desc' : 'asc',
    };

    return {
      sorting: nextSorting,
      appliedConfig: {
        ...s.appliedConfig,
        sorting: nextSorting,
      },
    };
  }),

  // ── Date range ──────────────────────────────────────────────────────────────
  setDateRange: (start, end) => set({ dateRange: { start, end } }),

  // ── Compare mode ────────────────────────────────────────────────────────────
  setCompareMode: (mode) => set({ compareMode: mode }),

  applyFilters: () => set((s) => ({
    appliedConfig: {
      search_query: s.searchQuery,
      filterGroups: s.filterGroups,
      filter_groups_operator: s.filterGroupsOperator,
      sorting: s.sorting,
      date_range: s.dateRange,
      compare_mode: s.compareMode,
    },
  })),

  /**
   * Replace filter state from a saved view config.
   */
  applySavedConfig: (config = {}) => set(() => {
    const filterGroups = (() => {
      const groups = normalizeSavedGroups(Array.isArray(config.filters) ? config.filters : []);
      syncNextId(groups);
      return groups;
    })();

    const searchQuery = config.search_query ?? '';
    const filterGroupsOperator = config.filter_groups_operator === 'OR' ? 'OR' : 'AND';
    const sorting = config.sorting ?? { field: 'created_at', direction: 'desc' };
    const dateRange = config.date_range ?? { start: '', end: '' };
    const compareMode = config.compare_mode ?? null;

    return {
      searchQuery,
      filterGroups,
      filterGroupsOperator,
      sorting,
      dateRange,
      compareMode,
      appliedConfig: {
        search_query: searchQuery,
        filterGroups,
        filter_groups_operator: filterGroupsOperator,
        sorting,
        date_range: dateRange,
        compare_mode: compareMode,
      },
    };
  }),

  // ── Reset all filters ────────────────────────────────────────────────────────
  resetAll: () => set({
    searchQuery:  '',
    filterGroups: [],
    filterGroupsOperator: 'AND',
    sorting:      { field: 'created_at', direction: 'desc' },
    dateRange:    { start: '', end: '' },
    compareMode:  null,
  }),

  clearAllFilters: () => set({
    searchQuery: '',
    filterGroups: [],
    filterGroupsOperator: 'AND',
    sorting: { field: 'created_at', direction: 'desc' },
    dateRange: { start: '', end: '' },
    compareMode: null,
    appliedConfig: {
      search_query: '',
      filterGroups: [],
      filter_groups_operator: 'AND',
      sorting: { field: 'created_at', direction: 'desc' },
      date_range: { start: '', end: '' },
      compare_mode: null,
    },
  }),

  /** Serialize current filters into the API payload shape */
  toPayload: () => {
    const { searchQuery, filterGroups, filterGroupsOperator, sorting, dateRange, compareMode } = get();

    const filters = serializeGroups(filterGroups);

    return {
      q: searchQuery,
      filters,
      filter_groups_operator: filterGroupsOperator,
      sort: sorting,
      date_range: dateRange,
      compare_mode: compareMode,
    };
  },

  toAppliedPayload: () => buildPayloadFromConfig(get().appliedConfig),

  serializeForSavedView: () => {
    const { searchQuery, filterGroups, filterGroupsOperator, sorting, dateRange, compareMode } = get();

    return {
      search_query: searchQuery,
      filters: serializeGroups(filterGroups),
      filter_groups_operator: filterGroupsOperator,
      sorting,
      date_range: dateRange,
      compare_mode: compareMode,
    };
  },

  hasUnappliedChanges: () => {
    const state = get();
    const current = JSON.stringify(state.serializeForSavedView());
    const applied = JSON.stringify({
      search_query: state.appliedConfig.search_query,
      filters: serializeGroups(state.appliedConfig.filterGroups ?? []),
      filter_groups_operator: state.appliedConfig.filter_groups_operator ?? 'AND',
      sorting: state.appliedConfig.sorting,
      date_range: state.appliedConfig.date_range,
      compare_mode: state.appliedConfig.compare_mode,
    });

    return current !== applied;
  },
}));
