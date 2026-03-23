/**
 * ChartControls exposes only useful fields for charting.
 */
export default function ChartControls({ schema, chartType, xKey, yKey, onChartType, onXKey, onYKey }) {
  const numericFields = schema.filter((field) => (
    ['pfloat', 'pint'].includes(field.type) ||
    field.name.endsWith('_f') ||
    field.name.endsWith('_i')
  ));
  const categoryFields = schema.filter((field) => (
    (field.type === 'string' || field.name.endsWith('_s')) &&
    !field.name.includes('url') &&
    !field.name.includes('sku') &&
    !field.name.includes('image') &&
    !field.name.includes('id')
  ));

  return (
    <div className="flex flex-wrap items-center gap-3">
      <div className="flex items-center gap-1.5">
        <label className="text-xs font-medium text-gray-500">Type</label>
        <div className="flex overflow-hidden rounded border border-gray-300 text-xs">
          {['Bar', 'Line', 'Pie'].map((type) => (
            <button
              key={type}
              className={`px-2.5 py-1 transition-colors ${
                chartType === type.toLowerCase()
                  ? 'bg-blue-600 text-white'
                  : 'bg-white text-gray-600 hover:bg-gray-100'
              }`}
              onClick={() => onChartType(type.toLowerCase())}
            >
              {type}
            </button>
          ))}
        </div>
      </div>

      <div className="flex items-center gap-1.5">
        <label className="text-xs font-medium text-gray-500">X</label>
        <select className="input py-1 text-xs w-40" value={xKey} onChange={(event) => onXKey(event.target.value)}>
          {categoryFields.map((field) => (
            <option key={field.name} value={field.name}>{field.label}</option>
          ))}
        </select>
      </div>

      {chartType !== 'pie' ? (
        <div className="flex items-center gap-1.5">
          <label className="text-xs font-medium text-gray-500">Y</label>
          <select className="input py-1 text-xs w-40" value={yKey} onChange={(event) => onYKey(event.target.value)}>
            {numericFields.map((field) => (
              <option key={field.name} value={field.name}>{field.label}</option>
            ))}
          </select>
        </div>
      ) : null}
    </div>
  );
}
