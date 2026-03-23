import { useState } from 'react';

/**
 * SavedViewItem renders a single saved view row.
 */
export default function SavedViewItem({ view, isActive, canManage, onLoad, onDelete, onSetDefault }) {
  const [confirming, setConfirming] = useState(false);

  return (
    <div
      className={`flex items-center gap-2 rounded-lg px-3 py-2 cursor-pointer transition-colors ${
        isActive ? 'border border-blue-200 bg-blue-50' : 'border border-transparent hover:bg-gray-50'
      }`}
      onClick={() => onLoad(view)}
    >
      <button
        type="button"
        className={`text-sm transition-colors ${view.is_default ? 'text-yellow-400' : 'text-gray-300'} ${
          canManage && !view.is_default ? 'hover:text-yellow-400' : ''
        }`}
        title={view.is_default ? 'Default view' : (canManage ? 'Set as default' : 'Saved view')}
        onClick={(e) => {
          if (!canManage) {
            return;
          }
          e.stopPropagation();
          onSetDefault(view);
        }}
      >
        *
      </button>

      <div className="min-w-0 flex-1">
        <p className="truncate text-sm font-medium text-gray-800">{view.name}</p>
        <p className="text-xs text-gray-400">
          {new Date(view.created_at).toLocaleDateString()}
          {view.owner_name ? ` | ${view.owner_name}` : ''}
        </p>
      </div>

      {!canManage ? null : confirming ? (
        <div className="flex gap-1" onClick={(e) => e.stopPropagation()}>
          <button type="button" className="text-xs font-medium text-red-600" onClick={() => onDelete(view.id)}>
            Yes
          </button>
          <button type="button" className="text-xs text-gray-400" onClick={() => setConfirming(false)}>
            No
          </button>
        </div>
      ) : (
        <button
          type="button"
          className="text-xs text-gray-300 transition-colors hover:text-red-500"
          title="Delete view"
          onClick={(e) => {
            e.stopPropagation();
            setConfirming(true);
          }}
        >
          x
        </button>
      )}
    </div>
  );
}
