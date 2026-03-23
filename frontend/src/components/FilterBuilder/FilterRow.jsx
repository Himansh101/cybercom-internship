import { useQuery } from '@tanstack/react-query';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { api } from '../../services/api.js';

const FACETED_FIELDS = ['category', 'sub_category', 'region', 'source_file_s'];

/**
 * FilterRow — renders a single filter rule with field selector,
 * type-appropriate value input, and a remove button.
 */
export default function FilterRow({ groupId, rule, schema, onUpdate, onRemove }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: rule.id });
  const fieldDef = schema.find((f) => f.name === rule.field);
  const isFacetedField = (fieldName) => {
    const schemaField = schema.find((s) => s.name === fieldName);
    return FACETED_FIELDS.includes(fieldName) || Boolean(schemaField?.faceted);
  };

  // Fetch facet values for dropdown fields
  const { data: facetRes } = useQuery({
    queryKey:  ['facets', rule.field],
    queryFn:   () => api.getFacets(rule.field).then((r) => r.data),
    enabled:   !!rule.field && isFacetedField(rule.field),
    staleTime: 60_000,
  });
  const facetOptions = facetRes ?? [];

  /** Auto-derive filter type from Solr field type */
  const inferType = (fieldName) => {
    const f = schema.find((s) => s.name === fieldName);
    if (!f) return 'text';
    if (isFacetedField(fieldName)) return 'dropdown';
    if (f.type === 'pfloat' || f.type === 'pint') return 'range';
    if (f.type === 'pdate')    return 'date';
    if (f.type === 'boolean')  return 'boolean';
    return 'text';
  };

  const handleFieldChange = (field) => {
    onUpdate(groupId, rule.id, { field, type: inferType(field), value: '', from: '', to: '' });
  };

  return (
    <div
      ref={setNodeRef}
      className="flex items-center gap-2 flex-wrap rounded-md"
      style={{
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.7 : 1,
      }}
    >
      <button
        type="button"
        className="cursor-grab text-gray-400 hover:text-gray-600 active:cursor-grabbing"
        aria-label="Drag filter"
        {...attributes}
        {...listeners}
      >
        ::
      </button>
      {/* Field selector */}
      <select
        className="input w-36"
        value={rule.field}
        onChange={(e) => handleFieldChange(e.target.value)}
      >
        <option value="">— field —</option>
        {schema.map((f) => (
          <option key={f.name} value={f.name}>{f.label}</option>
        ))}
      </select>

      {/* Value inputs by type */}
      {rule.type === 'text' && (
        <input
          className="input w-40"
          placeholder="contains…"
          value={rule.value ?? ''}
          onChange={(e) => onUpdate(groupId, rule.id, { value: e.target.value })}
        />
      )}

      {rule.type === 'dropdown' && (
        <div className="relative w-48">
          <select
            className="input w-full"
            multiple
            size={Math.min(facetOptions.length, 5) || 3}
            value={Array.isArray(rule.value) ? rule.value : []}
            onChange={(e) => {
              const vals = Array.from(e.target.selectedOptions).map((o) => o.value);
              onUpdate(groupId, rule.id, { value: vals });
            }}
          >
            {facetOptions.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.value} ({opt.count})
              </option>
            ))}
          </select>
          <p className="text-xs text-gray-400 mt-0.5">Ctrl/Cmd + click to multi-select</p>
        </div>
      )}

      {rule.type === 'range' && (
        <>
          <input
            className="input w-24"
            type="number"
            placeholder="from"
            value={rule.from ?? ''}
            onChange={(e) => onUpdate(groupId, rule.id, { from: e.target.value })}
          />
          <span className="text-gray-400">—</span>
          <input
            className="input w-24"
            type="number"
            placeholder="to"
            value={rule.to ?? ''}
            onChange={(e) => onUpdate(groupId, rule.id, { to: e.target.value })}
          />
        </>
      )}

      {rule.type === 'date' && (
        <>
          <input
            className="input w-36"
            type="date"
            value={rule.from ?? ''}
            onChange={(e) => onUpdate(groupId, rule.id, { from: e.target.value })}
          />
          <span className="text-gray-400">—</span>
          <input
            className="input w-36"
            type="date"
            value={rule.to ?? ''}
            onChange={(e) => onUpdate(groupId, rule.id, { to: e.target.value })}
          />
        </>
      )}

      {rule.type === 'boolean' && (
        <select
          className="input w-28"
          value={String(rule.value ?? '')}
          onChange={(e) => onUpdate(groupId, rule.id, { value: e.target.value === 'true' })}
        >
          <option value="">— any —</option>
          <option value="true">Yes</option>
          <option value="false">No</option>
        </select>
      )}

      {/* Remove button */}
      <button
        className="text-gray-400 hover:text-red-500 transition-colors ml-auto"
        onClick={() => onRemove(groupId, rule.id)}
        title="Remove filter"
      >
        ✕
      </button>
    </div>
  );
}
