import { useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api } from '../services/api.js';
import { useReportStore } from '../store/reportStore.js';
import { useFilterStore } from '../store/filterStore.js';
import { useDebounce } from './useDebounce.js';

/**
 * useReport — builds the API payload from both stores, fires the query,
 * and syncs results back into the report store.
 *
 * Returns the raw TanStack Query result for consumers that need it.
 */
export function useReport() {
  const { page, perPage, getActiveColumns, setData, setLoading, setError } = useReportStore();
  const { toPayload } = useFilterStore();

  // Build payload from stores
  const filterPayload  = toPayload();
  const activeColumns  = getActiveColumns();

  const payload = {
    ...filterPayload,
    page,
    per_page: perPage,
    columns:  activeColumns,
  };

  // Debounce the payload so rapid filter changes don't flood the API
  const debouncedPayload = useDebounce(payload, 400);

  const query = useQuery({
    queryKey:  ['report', debouncedPayload],
    queryFn:   () => api.queryReport(debouncedPayload).then((r) => r.data),
    keepPreviousData: true,
    staleTime: 15_000,
  });

  // Sync loading / error / data into the store
  useEffect(() => { setLoading(query.isLoading || query.isFetching); }, [query.isLoading, query.isFetching]);
  useEffect(() => { setError(query.error?.message ?? null); },         [query.error]);
  useEffect(() => {
    if (query.data) {
      setData(query.data.data ?? [], query.data.total ?? 0, query.data.compare_data ?? null);
    }
  }, [query.data]);

  return query;
}
