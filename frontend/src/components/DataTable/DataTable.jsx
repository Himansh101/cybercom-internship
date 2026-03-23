import { useRef } from 'react';
import { useVirtualizer } from '@tanstack/react-virtual';
import {
  DndContext, closestCenter,
  KeyboardSensor, PointerSensor, useSensor, useSensors,
} from '@dnd-kit/core';
import {
  SortableContext, horizontalListSortingStrategy, arrayMove,
} from '@dnd-kit/sortable';
import { useReportStore } from '../../store/reportStore.js';
import ResizableHeader from './ResizableHeader.jsx';

const ROW_HEIGHT = 36;

/**
 * DataTable renders the current report rows with virtualization and sorting.
 */
export default function DataTable({ schema }) {
  const {
    data,
    compareData,
    total,
    page,
    perPage,
    isLoading,
    visibleColumns,
    columnOrder,
    columnWidths,
    setColumnOrder,
    setPage,
  } = useReportStore();

  const parentRef = useRef(null);

  const activeColumns = columnOrder
    .filter((column) => visibleColumns.includes(column))
    .map((name) => schema.find((field) => field.name === name))
    .filter(Boolean);

  const rowVirtualizer = useVirtualizer({
    count: data.length,
    getScrollElement: () => parentRef.current,
    estimateSize: () => ROW_HEIGHT,
    overscan: 10,
  });

  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 5 } }),
    useSensor(KeyboardSensor),
  );

  const handleDragEnd = ({ active, over }) => {
    if (!over || active.id === over.id) {
      return;
    }

    const oldIndex = columnOrder.indexOf(active.id);
    const newIndex = columnOrder.indexOf(over.id);
    if (oldIndex !== -1 && newIndex !== -1) {
      setColumnOrder(arrayMove(columnOrder, oldIndex, newIndex));
    }
  };

  const totalPages = Math.ceil(total / perPage) || 1;

  const formatCell = (value, type, field) => {
    if (value === null || value === undefined || value === '') {
      return <span className="text-gray-300">-</span>;
    }

    if (field.includes('url')) {
      const text = String(value);
      return (
        <a
          href={text}
          target="_blank"
          rel="noreferrer"
          className="text-blue-600 hover:underline"
          title={text}
        >
          {text}
        </a>
      );
    }

    if (type === 'boolean') {
      return value ? 'Yes' : 'No';
    }

    if (type === 'pdate') {
      return new Date(value).toLocaleDateString();
    }

    if (type === 'pfloat') {
      return Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    return String(value);
  };

  const renderDelta = (current, previous) => {
    if (previous === undefined || previous === null) {
      return null;
    }

    const pct = previous !== 0 ? ((current - previous) / Math.abs(previous)) * 100 : 0;
    const color = pct >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50';

    return (
      <span className={`ml-1 rounded px-1 text-xs ${color}`}>
        {pct >= 0 ? '+' : ''}
        {pct.toFixed(1)}%
      </span>
    );
  };

  return (
    <div className="card overflow-hidden">
      <div ref={parentRef} className="overflow-auto" style={{ maxHeight: '60vh' }}>
        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
          <SortableContext items={activeColumns.map((column) => column.name)} strategy={horizontalListSortingStrategy}>
            <table className="w-full border-collapse" style={{ tableLayout: 'fixed' }}>
              <thead>
                <tr>
                  {activeColumns.map((column) => (
                    <ResizableHeader
                      key={column.name}
                      field={column.name}
                      label={column.label}
                      width={columnWidths[column.name] ?? 160}
                    />
                  ))}
                </tr>
              </thead>

              <tbody style={{ height: rowVirtualizer.getTotalSize(), position: 'relative' }}>
                {isLoading ? (
                  Array.from({ length: 8 }).map((_, index) => (
                    <tr key={index} style={{ height: ROW_HEIGHT }}>
                      {activeColumns.map((column) => (
                        <td key={column.name} className="px-3 py-2">
                          <div className="skeleton h-4 w-full" />
                        </td>
                      ))}
                    </tr>
                  ))
                ) : data.length === 0 ? (
                  <tr>
                    <td colSpan={Math.max(activeColumns.length, 1)} className="py-12 text-center text-gray-400">
                      No results found.
                    </td>
                  </tr>
                ) : (
                  rowVirtualizer.getVirtualItems().map((virtualRow) => {
                    const row = data[virtualRow.index];
                    const compareRow = compareData?.[virtualRow.index];

                    return (
                      <tr
                        key={virtualRow.key}
                        data-index={virtualRow.index}
                        ref={rowVirtualizer.measureElement}
                        className="border-b border-gray-100 transition-colors hover:bg-blue-50"
                        style={{
                          position: 'absolute',
                          top: virtualRow.start,
                          left: 0,
                          width: '100%',
                          height: ROW_HEIGHT,
                        }}
                      >
                        {activeColumns.map((column) => (
                          <td
                            key={column.name}
                            className="truncate px-3 py-2 text-gray-700"
                            style={{
                              width: columnWidths[column.name] ?? 160,
                              maxWidth: columnWidths[column.name] ?? 160,
                            }}
                            title={row[column.name] === null || row[column.name] === undefined ? '' : String(row[column.name])}
                          >
                            {formatCell(row[column.name], column.type, column.name)}
                            {compareRow && (column.type === 'pfloat' || column.type === 'pint')
                              ? renderDelta(row[column.name], compareRow[column.name])
                              : null}
                          </td>
                        ))}
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </SortableContext>
        </DndContext>
      </div>

      <div className="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-4 py-2 text-xs text-gray-600">
        <span>
          {total.toLocaleString()} results
          {total > 0 ? ` page ${page} of ${totalPages}` : ''}
        </span>
        <div className="flex items-center gap-1">
          <button className="btn-secondary px-2 py-1" disabled={page <= 1} onClick={() => setPage(page - 1)}>
            Prev
          </button>
          <span className="px-2">
            {page} / {totalPages}
          </span>
          <button className="btn-secondary px-2 py-1" disabled={page >= totalPages} onClick={() => setPage(page + 1)}>
            Next
          </button>
        </div>
      </div>
    </div>
  );
}

