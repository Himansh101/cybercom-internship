import { useFilterStore } from '../../store/filterStore.js';
import FilterGroup from './FilterGroup.jsx';

/**
 * FilterBuilder — top-level filter panel.
 * Renders a search bar, filter groups, and add-group button.
 */
export default function FilterBuilder({ schema }) {
  const {
    searchQuery, setSearchQuery,
    filterGroups, addGroup, removeGroup, setGroupOperator,
    addFilter, removeFilter, updateFilter,
    resetAll,
  } = useFilterStore();

  return (
    <div className="card p-3 space-y-3">
      {/* Header */}
      <div className="flex items-center justify-between">
        <h3 className="font-medium text-gray-800">Filters</h3>
        {(filterGroups.length > 0 || searchQuery) && (
          <button className="text-xs text-gray-400 hover:text-red-500" onClick={resetAll}>
            Clear all
          </button>
        )}
      </div>

      {/* Full-text search */}
      <input
        className="input"
        placeholder="Search all fields…"
        value={searchQuery}
        onChange={(e) => setSearchQuery(e.target.value)}
      />

      {/* Filter groups */}
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
          />
        ))}
      </div>

      {/* Add group */}
      <button
        className="text-xs text-blue-600 hover:text-blue-800 font-medium"
        onClick={addGroup}
      >
        + Add filter group
      </button>
    </div>
  );
}
