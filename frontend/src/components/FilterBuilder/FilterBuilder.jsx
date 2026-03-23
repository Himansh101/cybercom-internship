import { useFilterStore } from '../../store/filterStore.js';
import {
  DndContext,
  PointerSensor,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import {
  SortableContext,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import FilterGroup from './FilterGroup.jsx';

/**
 * FilterBuilder — top-level filter panel.
 * Renders a search bar, filter groups, and add-group button.
 */
export default function FilterBuilder({ schema }) {
  const {
    searchQuery, setSearchQuery,
    filterGroups, filterGroupsOperator, addGroup, removeGroup, setGroupOperator, setFilterGroupsOperator,
    addFilter, removeFilter, updateFilter, reorderGroups, reorderFilter,
    resetAll, clearAllFilters, applyFilters, hasUnappliedChanges,
  } = useFilterStore();

  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 6 } }));

  const handleGroupDragEnd = ({ active, over }) => {
    if (!over || active.id === over.id) {
      return;
    }

    reorderGroups(active.id, over.id);
  };

  const hasPendingChanges = hasUnappliedChanges();

  return (
    <div className="card p-3 space-y-3">
      {/* Header */}
      <div className="flex items-center justify-between">
        <h3 className="font-medium text-gray-800">Filters</h3>
        {(filterGroups.length > 0 || searchQuery) && (
          <div className="flex items-center gap-3">
            <button className="text-xs text-gray-400 hover:text-gray-600" onClick={resetAll}>
              Reset draft
            </button>
            <button className="text-xs text-red-500 hover:text-red-600" onClick={clearAllFilters}>
              Clear filters
            </button>
          </div>
        )}
      </div>

      {/* Full-text search */}
      <input
        className="input"
        placeholder="Search all fields…"
        value={searchQuery}
        onChange={(e) => setSearchQuery(e.target.value)}
      />

      {filterGroups.length > 1 && (
        <div className="flex items-center gap-2 text-xs">
          <span className="text-gray-500">Between groups</span>
          <div className="flex rounded border border-gray-300 overflow-hidden text-xs">
            {['AND', 'OR'].map((op) => (
              <button
                key={op}
                className={`px-2.5 py-1 transition-colors ${
                  filterGroupsOperator === op
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-600 hover:bg-gray-100'
                }`}
                onClick={() => setFilterGroupsOperator(op)}
              >
                {op}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Filter groups */}
      <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleGroupDragEnd}>
        <SortableContext items={filterGroups.map((group) => group.id)} strategy={verticalListSortingStrategy}>
          <div className="space-y-2">
            {filterGroups.map((group) => (
              <FilterGroup
                key={group.id}
                group={group}
                schema={schema}
                onAddFilter={addFilter}
                onUpdateFilter={updateFilter}
                onRemoveFilter={removeFilter}
                onSetOperator={setGroupOperator}
                onRemoveGroup={removeGroup}
                onReorderFilter={reorderFilter}
              />
            ))}
          </div>
        </SortableContext>
      </DndContext>

      <div className="flex items-center justify-between pt-1">
        <button
          className="text-xs text-blue-600 hover:text-blue-800 font-medium"
          onClick={addGroup}
        >
          + Add filter group
        </button>

        <button
          className={`btn-primary text-xs py-1 ${hasPendingChanges ? '' : 'opacity-60'}`}
          onClick={applyFilters}
          disabled={!hasPendingChanges}
        >
          Apply filters
        </button>
      </div>
    </div>
  );
}
