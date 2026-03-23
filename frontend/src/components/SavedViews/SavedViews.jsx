import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../../services/api.js';
import { useReportStore } from '../../store/reportStore.js';
import { useFilterStore } from '../../store/filterStore.js';
import SavedViewItem from './SavedViewItem.jsx';

/**
 * SavedViews — sidebar panel for managing and loading saved report views.
 */
export default function SavedViews() {
  const [showModal, setShowModal] = useState(false);
  const [viewName, setViewName]   = useState('');
  const [saveError, setSaveError] = useState('');

  const qc = useQueryClient();

  const { activeViewId, loadView, visibleColumns, columnWidths } = useReportStore();
  const { searchQuery, filterGroups, sorting, dateRange, compareMode } = useFilterStore();

  // Fetch saved views
  const { data: viewsRes } = useQuery({
    queryKey: ['saved-views'],
    queryFn:  () => api.getSavedViews().then((r) => r.data),
    staleTime: 30_000,
  });
  const views = viewsRes ?? [];

  // Save mutation
  const saveMutation = useMutation({
    mutationFn: (data) => api.saveView(data),
    onSuccess:  () => {
      qc.invalidateQueries({ queryKey: ['saved-views'] });
      setShowModal(false);
      setViewName('');
    },
    onError: (e) => setSaveError(e.message),
  });

  // Delete mutation
  const deleteMutation = useMutation({
    mutationFn: (id) => api.deleteView(id),
    onSuccess:  () => qc.invalidateQueries({ queryKey: ['saved-views'] }),
  });

  // Set default mutation
  const defaultMutation = useMutation({
    mutationFn: (view) => api.updateView(view.id, { ...view.config, name: view.name, is_default: true }),
    onSuccess:  () => qc.invalidateQueries({ queryKey: ['saved-views'] }),
  });

  const handleSave = () => {
    if (!viewName.trim()) { setSaveError('Please enter a name'); return; }
    saveMutation.mutate({
      name:          viewName.trim(),
      columns:       visibleColumns,
      column_widths: columnWidths,
      search_query:  searchQuery,
      filters:       filterGroups,
      sorting,
      date_range:    dateRange,
      compare_mode:  compareMode,
    });
  };

  const handleLoad = (view) => {
    loadView(view);
    useFilterStore.getState().applySavedConfig(view.config ?? {});
  };

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <div className="flex items-center justify-between px-3 py-2 border-b border-gray-100">
        <h3 className="text-sm font-semibold text-gray-700">Saved Views</h3>
        <button className="btn-primary text-xs py-1" onClick={() => { setShowModal(true); setSaveError(''); }}>
          + Save
        </button>
      </div>

      {/* List */}
      <div className="flex-1 overflow-y-auto p-2 space-y-1">
        {views.length === 0 ? (
          <p className="text-xs text-gray-400 italic text-center mt-6">No saved views yet.</p>
        ) : (
          views.map((view) => (
            <SavedViewItem
              key={view.id}
              view={view}
              isActive={view.id === activeViewId}
              onLoad={handleLoad}
              onDelete={(id) => deleteMutation.mutate(id)}
              onSetDefault={(v) => defaultMutation.mutate(v)}
            />
          ))
        )}
      </div>

      {/* Save modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/30">
          <div className="card p-6 w-80 shadow-xl">
            <h4 className="font-semibold text-gray-800 mb-4">Save current view</h4>
            {saveError && (
              <p className="text-xs text-red-600 mb-2">{saveError}</p>
            )}
            <input
              className="input mb-4"
              placeholder="View name…"
              value={viewName}
              onChange={(e) => setViewName(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && handleSave()}
              autoFocus
            />
            <div className="flex gap-2 justify-end">
              <button className="btn-secondary" onClick={() => setShowModal(false)}>Cancel</button>
              <button
                className="btn-primary"
                onClick={handleSave}
                disabled={saveMutation.isPending}
              >
                {saveMutation.isPending ? 'Saving…' : 'Save'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
