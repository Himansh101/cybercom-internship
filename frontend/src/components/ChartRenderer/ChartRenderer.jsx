import { useEffect, useRef, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  BarChart, Bar, LineChart, Line, PieChart, Pie, Cell,
  XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
} from 'recharts';
import html2canvas from 'html2canvas';
import { useReportStore } from '../../store/reportStore.js';
import { useFilterStore } from '../../store/filterStore.js';
import { api } from '../../services/api.js';
import ChartControls from './ChartControls.jsx';

const COLORS = ['#2563eb', '#16a34a', '#dc2626', '#d97706', '#7c3aed', '#db2777', '#0891b2', '#65a30d'];
const CHART_HEIGHT = 360;
const AXIS_HEIGHT = 108;

function formatAxisLabel(value, maxWords = 3) {
  const text = typeof value === 'string' ? value.trim() : String(value ?? '');
  if (!text) {
    return '';
  }

  const words = text.split(/\s+/);
  const label = words.slice(0, maxWords).join(' ');
  return words.length > maxWords ? `${label}...` : label;
}

function wrapAxisLabel(value, maxLineLength = 14, maxLines = 4) {
  const text = formatAxisLabel(value, 4);
  if (!text) {
    return [''];
  }

  const words = text.split(/\s+/);
  const lines = [];
  let currentLine = '';

  for (const word of words) {
    const candidate = currentLine ? `${currentLine} ${word}` : word;

    if (candidate.length <= maxLineLength) {
      currentLine = candidate;
      continue;
    }

    if (currentLine) {
      lines.push(currentLine);
      currentLine = word;
    } else {
      lines.push(word);
      currentLine = '';
    }

    if (lines.length === maxLines - 1) {
      break;
    }
  }

  if (currentLine && lines.length < maxLines) {
    lines.push(currentLine);
  }

  if (lines.length === maxLines && words.join(' ') !== lines.join(' ')) {
    lines[maxLines - 1] = `${lines[maxLines - 1].replace(/\.\.\.$/, '')}...`;
  }

  return lines;
}

function CustomXAxisTick({ x, y, payload }) {
  const lines = wrapAxisLabel(payload?.value);

  return (
    <g transform={`translate(${x},${y})`} pointerEvents="none">
      <text x={0} y={18} textAnchor="middle" fill="#666" fontSize={11}>
        {lines.map((line, index) => (
          <tspan key={`${line}-${index}`} x={0} dy={index === 0 ? 0 : 14}>
            {line}
          </tspan>
        ))}
      </text>
    </g>
  );
}

function normalizeCategoryValue(value) {
  return String(value).replace(/\s+/g, ' ').trim();
}

/**
 * ChartRenderer renders simple aggregates over the currently loaded rows.
 */
export default function ChartRenderer({ schema }) {
  const { total } = useReportStore();
  const { addGroup, addFilter, filterGroups, toPayload } = useFilterStore();
  const chartRef = useRef(null);

  const xCandidates = schema.filter((field) => (
    (field.type === 'string' || field.name.endsWith('_s')) &&
    !field.name.includes('url') &&
    !field.name.includes('sku') &&
    !field.name.includes('image') &&
    !field.name.includes('id')
  ));

  const yCandidates = schema.filter((field) => (
    ['pfloat', 'pint'].includes(field.type) ||
    field.name.endsWith('_f') ||
    field.name.endsWith('_i')
  ));

  const preferredX = ['type_s', 'brand_name_s', 'source_file_s', 'stock_s'];
  const preferredY = ['price_f', 'map_price_f', 'store_price_f', 'quantity_i'];

  const [chartType, setChartType] = useState('bar');
  const [xKey, setXKey] = useState(
    preferredX.find((name) => xCandidates.some((field) => field.name === name)) ?? xCandidates[0]?.name ?? 'source_file_s'
  );
  const [yKey, setYKey] = useState(
    preferredY.find((name) => yCandidates.some((field) => field.name === name)) ?? yCandidates[0]?.name ?? 'price_f'
  );
  const [collapsed, setCollapsed] = useState(false);

  useEffect(() => {
    const nextX = preferredX.find((name) => xCandidates.some((field) => field.name === name)) ?? xCandidates[0]?.name ?? '';
    const nextY = preferredY.find((name) => yCandidates.some((field) => field.name === name)) ?? yCandidates[0]?.name ?? '';

    if (!xCandidates.some((field) => field.name === xKey) || xKey === 'id') {
      setXKey(nextX);
    }
    if (!yCandidates.some((field) => field.name === yKey) || yKey === '') {
      setYKey(nextY);
    }
  }, [schema, xKey, yKey]);

  const chartPayload = {
    ...toPayload(),
    x_field: xKey,
    y_field: yKey,
    limit: 15,
  };

  const { data: chartResponse } = useQuery({
    queryKey: ['chart', chartPayload],
    queryFn: () => api.queryChart(chartPayload).then((response) => response.data),
    enabled: Boolean(xKey && yKey),
    staleTime: 15_000,
  });

  const chartData = (chartResponse?.data ?? []).map((item) => ({
    name: normalizeCategoryValue(item.name),
    value: Number(item.value ?? 0),
  }));

  const chartMinWidth = Math.max(chartData.length * 110, 900);

  const handleClick = (payload) => {
    const value = payload?.activePayload?.[0]?.payload?.name ?? payload?.payload?.name;
    if (!value || !xKey) {
      return;
    }

    let groupId = filterGroups[0]?.id;
    if (!groupId) {
      addGroup();
      groupId = useFilterStore.getState().filterGroups[0]?.id;
    }

    if (groupId) {
      addFilter(groupId, { field: xKey, type: 'dropdown', value: [value] });
    }
  };

  const exportPng = async () => {
    if (!chartRef.current) {
      return;
    }
    const canvas = await html2canvas(chartRef.current, { backgroundColor: '#ffffff' });
    const link = document.createElement('a');
    link.download = `chart_${Date.now()}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
  };

  if (total === 0 || !xKey || !yKey) {
    return null;
  }

  return (
    <div className="card p-3 space-y-3">
      <div className="flex items-center justify-between">
        <h3 className="font-medium text-gray-800">Chart</h3>
        <div className="flex items-center gap-2">
          <button className="btn-secondary text-xs py-1" onClick={exportPng}>PNG</button>
          <button className="text-xs text-gray-400 hover:text-gray-600" onClick={() => setCollapsed((value) => !value)}>
            {collapsed ? 'Show' : 'Hide'}
          </button>
        </div>
      </div>

      {!collapsed ? (
        <>
          <ChartControls
            schema={schema}
            chartType={chartType}
            xKey={xKey}
            yKey={yKey}
            onChartType={setChartType}
            onXKey={setXKey}
            onYKey={setYKey}
          />

          <div className="overflow-x-auto">
            <div ref={chartRef} style={{ minWidth: chartMinWidth, height: CHART_HEIGHT }}>
              <ResponsiveContainer width="100%" height="100%">
                {chartType === 'bar' ? (
                  <BarChart data={chartData} margin={{ top: 8, right: 20, bottom: 0, left: 10 }} barCategoryGap={28} barSize={38}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                    <XAxis
                      dataKey="name"
                      interval={0}
                      height={AXIS_HEIGHT}
                      axisLine={false}
                      tickLine={false}
                      tickMargin={10}
                      tick={<CustomXAxisTick />}
                      allowDuplicatedCategory={false}
                    />
                    <YAxis tick={{ fontSize: 11 }} width={40} />
                    <Tooltip
                      allowEscapeViewBox={{ x: true, y: true }}
                      cursor={{ fill: 'rgba(37, 99, 235, 0.08)' }}
                      wrapperStyle={{ zIndex: 30, pointerEvents: 'none' }}
                      contentStyle={{ borderRadius: 8, borderColor: '#d1d5db' }}
                      labelFormatter={(label) => `Product: ${label}`}
                      formatter={(value) => [value, 'Value']}
                    />
                    <Bar dataKey="value" maxBarSize={38} fill="#2563eb" radius={[3, 3, 0, 0]} cursor="pointer" onClick={handleClick} />
                  </BarChart>
                ) : chartType === 'line' ? (
                  <LineChart data={chartData} margin={{ top: 8, right: 20, bottom: 0, left: 10 }}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
                    <XAxis
                      dataKey="name"
                      interval={0}
                      height={AXIS_HEIGHT}
                      axisLine={false}
                      tickLine={false}
                      tickMargin={10}
                      tick={<CustomXAxisTick />}
                      allowDuplicatedCategory={false}
                    />
                    <YAxis tick={{ fontSize: 11 }} width={40} />
                    <Tooltip
                      allowEscapeViewBox={{ x: true, y: true }}
                      wrapperStyle={{ zIndex: 30, pointerEvents: 'none' }}
                      contentStyle={{ borderRadius: 8, borderColor: '#d1d5db' }}
                      labelFormatter={(label) => `Product: ${label}`}
                      formatter={(value) => [value, 'Value']}
                    />
                    <Line type="monotone" dataKey="value" stroke="#2563eb" dot={{ r: 3 }} activeDot={{ r: 5 }} />
                  </LineChart>
                ) : (
                  <PieChart>
                    <Pie
                      data={chartData}
                      dataKey="value"
                      nameKey="name"
                      cx="50%"
                      cy="50%"
                      outerRadius={100}
                      labelLine={false}
                      onClick={handleClick}
                    >
                      {chartData.map((_, index) => (
                        <Cell key={index} fill={COLORS[index % COLORS.length]} />
                      ))}
                    </Pie>
                    <Tooltip
                      allowEscapeViewBox={{ x: true, y: true }}
                      wrapperStyle={{ zIndex: 30, pointerEvents: 'none' }}
                      contentStyle={{ borderRadius: 8, borderColor: '#d1d5db' }}
                      labelFormatter={(label) => `Product: ${label}`}
                      formatter={(value) => [value, 'Value']}
                    />
                    <Legend />
                  </PieChart>
                )}
              </ResponsiveContainer>
            </div>
          </div>

          <p className="text-center text-xs text-gray-400">
            Click a bar or segment to add a drill-down filter
          </p>
        </>
      ) : null}
    </div>
  );
}
