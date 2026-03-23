import { useState } from 'react';
import { createPortal } from 'react-dom';
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

  const { activeViewId, loadView, resetToBaseView, visibleColumns, columnOrder, columnWidths } = useReportStore();
  const { clearAllFilters, serializeForSavedView } = useFilterStore();

  const { data: viewsRes } = useQuery({
    queryKey: ['saved-views'],
    queryFn:  () => api.getSavedViews().then((r) => r.data),
    staleTime: 30_000,
  });
  const views = viewsRes ?? [];

  const saveMutation = useMutation({
    mutationFn: (data) => api.saveView(data),
    onSuccess:  () => {
      qc.invalidateQueries({ queryKey: ['saved-views'] });
      setShowModal(false);
      setViewName('');
      setSaveError('');
    },
    onError: (e) => setSaveError(e.message),
  });

  const deleteMutation = useMutation({
    mutationFn: (id) => api.deleteView(id),
    onSuccess:  () => qc.invalidateQueries({ queryKey: ['saved-views'] }),
  });

  const defaultMutation = useMutation({
    mutationFn: (view) => api.updateView(view.id, { ...view.config, name: view.name, is_default: true }),
    onSuccess:  () => qc.invalidateQueries({ queryKey: ['saved-views'] }),
  });

  const handleSave = () => {
    if (!viewName.trim()) {
      setSaveError('Please enter a name');
      return;
    }

    const filterConfig = serializeForSavedView();
    saveMutation.mutate({
      name:          viewName.trim(),
      columns:       visibleColumns,
      column_order:  columnOrder,
      column_widths: columnWidths,
      ...filterConfig,
    });
  };

  const handleLoad = (view) => {
    loadView(view);
    useFilterStore.getState().applySavedConfig(view.config ?? {});
  };

  const handleBackToDefault = () => {
    resetToBaseView();
    clearAllFilters();
  };

  const modal = showModal ? (
    <div
      className="fixed inset-0 z-[1000] flex items-center justify-center bg-black/30 p-4"
      onClick={() => setShowModal(false)}
    >
      <div
        className="card relative w-full max-w-md p-6 shadow-xl"
        onClick={(e) => e.stopPropagation()}
      >
        <h4 className="mb-4 font-semibold text-gray-800">Save current view</h4>
        {saveError && (
          <p className="mb-2 text-xs text-red-600">{saveError}</p>
        )}
        <input
          className="input mb-4"
          placeholder="View name..."
          value={viewName}
          onChange={(e) => setViewName(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && handleSave()}
          autoFocus
        />
        <div className="flex flex-wrap justify-end gap-2">
          <button className="btn-secondary shrink-0" onClick={() => setShowModal(false)}>Cancel</button>
          <button
            className="btn-primary shrink-0"
            onClick={handleSave}
            disabled={saveMutation.isPending}
          >
            {saveMutation.isPending ? 'Saving...' : 'Save'}
          </button>
        </div>
      </div>
    </div>
  ) : null;

  return (
    <>
      <div className="flex h-full flex-col">
        <div className="flex items-center justify-between border-b border-gray-100 px-3 py-2">
          <h3 className="text-sm font-semibold text-gray-700">Saved Views</h3>
          <div className="flex items-center gap-2">
            <button
              className="btn-secondary py-1 text-xs"
              onClick={handleBackToDefault}
            >
              Original
            </button>
            <button
              className="btn-primary py-1 text-xs"
              onClick={() => {
                setShowModal(true);
                setSaveError('');
              }}
            >
              + Save
            </button>
          </div>
        </div>

        <div className="flex-1 overflow-y-auto p-2 space-y-1">
          {views.length === 0 ? (
            <p className="mt-6 text-center text-xs italic text-gray-400">No saved views yet.</p>
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
      </div>
      {typeof document !== 'undefined' ? createPortal(modal, document.body) : null}
    </>
  );
}
