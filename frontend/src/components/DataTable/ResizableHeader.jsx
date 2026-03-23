import { useRef } from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { useReportStore } from '../../store/reportStore.js';
import { useFilterStore } from '../../store/filterStore.js';

/**
 * ResizableHeader — a single <th> with:
 *   • click to sort
 *   • right-edge drag handle to resize
 */
export default function ResizableHeader({ field, label, width }) {
  const setColumnWidth = useReportStore((s) => s.setColumnWidth);
  const { sorting, toggleSort } = useFilterStore();
  const startX  = useRef(null);
  const startW  = useRef(null);
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: field });

  const isActive = sorting.field === field;

  const onMouseDown = (e) => {
    e.preventDefault();
    e.stopPropagation();
    startX.current = e.clientX;
    startW.current = width ?? 120;

    const onMove = (ev) => {
      const delta = ev.clientX - startX.current;
      setColumnWidth(field, startW.current + delta);
    };
    const onUp = () => {
      window.removeEventListener('mousemove', onMove);
      window.removeEventListener('mouseup',  onUp);
    };
    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup',   onUp);
  };

  return (
    <th
      ref={setNodeRef}
      className="relative select-none px-3 py-2 text-left text-xs font-semibold
                 text-gray-600 uppercase tracking-wide border-b border-gray-200
                 bg-gray-50 cursor-pointer hover:bg-gray-100 whitespace-nowrap"
      style={{
        width: width ?? 120,
        minWidth: 60,
        transform: CSS.Transform.toString(transform),
        transition,
        zIndex: isDragging ? 10 : 'auto',
        opacity: isDragging ? 0.9 : 1,
      }}
      onClick={() => toggleSort(field)}
    >
      <span className="flex items-center gap-2">
        <button
          type="button"
          className="cursor-grab text-gray-400 hover:text-gray-600 active:cursor-grabbing"
          onClick={(e) => e.stopPropagation()}
          aria-label={`Drag column ${label}`}
          {...attributes}
          {...listeners}
        >
          ::
        </button>
        {label}
        {isActive && (
          <span className="text-blue-500">
            {sorting.direction === 'asc' ? '↑' : '↓'}
          </span>
        )}
      </span>

      {/* Resize handle */}
      <div
        className="resize-handle"
        onMouseDown={onMouseDown}
        onClick={(e) => e.stopPropagation()}
      />
    </th>
  );
}
