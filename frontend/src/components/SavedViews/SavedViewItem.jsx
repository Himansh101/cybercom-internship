import { useState } from 'react';

/**
 * SavedViewItem — a single row in the saved views panel.
 * Shows name, default star, load button, and delete.
 */
export default function SavedViewItem({ view, isActive, onLoad, onDelete, onSetDefault }) {
  const [confirming, setConfirming] = useState(false);

  return (
    <div
      className={`flex items-center gap-2 px-3 py-2 rounded-lg cursor-pointer transition-colors
        ${isActive ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50 border border-transparent'}`}
      onClick={() => onLoad(view)}
    >
      {/* Default star */}
      <button
        className={`text-sm transition-colors ${view.is_default ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-400'}`}
        title={view.is_default ? 'Default view' : 'Set as default'}
        onClick={(e) => { e.stopPropagation(); onSetDefault(view); }}
      >
        ★
      </button>

      {/* Name */}
      <div className="flex-1 min-w-0">
        <p className="text-sm font-medium text-gray-800 truncate">{view.name}</p>
        <p className="text-xs text-gray-400">
          {new Date(view.created_at).toLocaleDateString()}
        </p>
      </div>

      {/* Delete */}
      {confirming ? (
        <div className="flex gap-1" onClick={(e) => e.stopPropagation()}>
          <button className="text-xs text-red-600 font-medium" onClick={() => onDelete(view.id)}>
            Yes
          </button>
          <button className="text-xs text-gray-400" onClick={() => setConfirming(false)}>
            No
          </button>
        </div>
      ) : (
        <button
          className="text-gray-300 hover:text-red-500 transition-colors text-xs"
          title="Delete view"
          onClick={(e) => { e.stopPropagation(); setConfirming(true); }}
        >
          ✕
        </button>
      )}
    </div>
  );
}
