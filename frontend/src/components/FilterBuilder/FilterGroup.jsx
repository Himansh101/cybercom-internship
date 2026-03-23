import FilterRow from './FilterRow.jsx';

/**
 * FilterGroup — renders one filter group with AND/OR toggle,
 * its list of FilterRows, and an "Add filter" button.
 */
export default function FilterGroup({ group, schema, onAddFilter, onUpdateFilter, onRemoveFilter, onSetOperator, onRemoveGroup }) {
  return (
    <div className="border border-gray-200 rounded-lg p-3 bg-gray-50 space-y-2">
      {/* Header row */}
      <div className="flex items-center gap-2">
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
