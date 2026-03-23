import {
  DndContext,
  PointerSensor,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { useSortable, SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import FilterRow from './FilterRow.jsx';

/**
 * FilterGroup — renders one filter group with AND/OR toggle,
 * its list of FilterRows, and an "Add filter" button.
 */
export default function FilterGroup({ group, schema, onAddFilter, onUpdateFilter, onRemoveFilter, onSetOperator, onRemoveGroup, onReorderFilter }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: group.id });
  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 6 } }));

  const handleRuleDragEnd = ({ active, over }) => {
    if (!over || active.id === over.id) {
      return;
    }

    onReorderFilter(group.id, active.id, over.id);
  };

  return (
    <div
      ref={setNodeRef}
      className="border border-gray-200 rounded-lg p-3 bg-gray-50 space-y-2"
      style={{
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.75 : 1,
      }}
    >
      {/* Header row */}
      <div className="flex items-center gap-2">
        <button
          type="button"
          className="cursor-grab text-gray-400 hover:text-gray-600 active:cursor-grabbing"
          aria-label="Drag filter group"
          {...attributes}
          {...listeners}
        >
          ::
        </button>
        <span className="text-xs font-medium text-gray-500 uppercase tracking-wide">Group</span>

        {/* AND / OR toggle */}
        <div className="flex rounded border border-gray-300 overflow-hidden text-xs">
          {['AND', 'OR'].map((op) => (
            <button
              key={op}
              className={`px-2 py-0.5 transition-colors ${
                group.operator === op
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-600 hover:bg-gray-100'
              }`}
              onClick={() => onSetOperator(group.id, op)}
            >
              {op}
            </button>
          ))}
        </div>

        <button
          className="ml-auto text-xs text-gray-400 hover:text-red-500 transition-colors"
          onClick={() => onRemoveGroup(group.id)}
          title="Remove group"
        >
          Remove group ✕
        </button>
      </div>

      {/* Rules */}
      <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleRuleDragEnd}>
        <SortableContext items={group.rules.map((rule) => rule.id)} strategy={verticalListSortingStrategy}>
          <div className="space-y-2 pl-3 border-l-2 border-blue-200">
            {group.rules.map((rule) => (
              <FilterRow
                key={rule.id}
                groupId={group.id}
                rule={rule}
                schema={schema}
                onUpdate={onUpdateFilter}
                onRemove={onRemoveFilter}
              />
            ))}

            {group.rules.length === 0 && (
              <p className="text-xs text-gray-400 italic">No filters yet — add one below.</p>
            )}
          </div>
        </SortableContext>
      </DndContext>

      {/* Add filter */}
      <button
        className="text-xs text-blue-600 hover:text-blue-800 font-medium"
        onClick={() => onAddFilter(group.id)}
      >
        + Add filter
      </button>
    </div>
  );
}
