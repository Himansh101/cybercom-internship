import { useState } from 'react';
import { createPortal } from 'react-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '../../services/api.js';
import { useFilterStore } from '../../store/filterStore.js';
import { useReportStore } from '../../store/reportStore.js';

/**
 * Admin control for creating and managing scheduled report emails.
 */
export default function ScheduledReports({ defaultEmail = '' }) {
  const qc = useQueryClient();
  const { serializeForSavedView } = useFilterStore();
  const { visibleColumns, columnOrder, columnWidths } = useReportStore();

  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({
    report_name: 'Scheduled Report',
    recipient_email: defaultEmail,
    frequency: 'daily',
    send_time: '09:00',
    day_of_week: '1',
    day_of_month: '1',
    timezone: 'Asia/Calcutta',
  });
  const [error, setError] = useState('');

  const { data: schedulesRes } = useQuery({
    queryKey: ['scheduled-reports'],
    queryFn: () => api.getScheduledReports().then((r) => r.data),
    staleTime: 30_000,
    enabled: open,
  });
  const schedules = schedulesRes ?? [];

  const saveMutation = useMutation({
    mutationFn: (payload) => api.saveScheduledReport(payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['scheduled-reports'] });
      setError('');
      setOpen(false);
    },
    onError: (e) => setError(e.message),
  });

  const deleteMutation = useMutation({
    mutationFn: (id) => api.deleteScheduledReport(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['scheduled-reports'] }),
  });

  const handleCreate = () => {
    const filterConfig = serializeForSavedView();
    const payload = {
      report_name: form.report_name.trim(),
      recipient_email: form.recipient_email.trim(),
      frequency: form.frequency,
      send_time: form.send_time,
      timezone: form.timezone.trim() || 'UTC',
      day_of_week: form.frequency === 'weekly' ? Number(form.day_of_week) : null,
      day_of_month: form.frequency === 'monthly' ? Number(form.day_of_month) : null,
      payload: {
        ...filterConfig,
        columns: visibleColumns,
        column_order: columnOrder,
        column_widths: columnWidths,
      },
      is_active: true,
    };

    saveMutation.mutate(payload);
  };

  const modal = open ? (
    <div className="fixed inset-0 z-[1000] flex items-center justify-center bg-black/30 p-4" onClick={() => setOpen(false)}>
      <div className="card w-full max-w-3xl p-6 shadow-xl" onClick={(e) => e.stopPropagation()}>
        <div className="mb-4 flex items-center justify-between">
          <h3 className="text-lg font-semibold text-gray-800">Scheduled Reports</h3>
          <button type="button" className="btn-secondary text-xs" onClick={() => setOpen(false)}>Close</button>
        </div>

        <div className="grid gap-3 md:grid-cols-2">
          <input
            className="input"
            placeholder="Report name"
            value={form.report_name}
            onChange={(e) => setForm((current) => ({ ...current, report_name: e.target.value }))}
          />
          <input
            className="input"
            type="email"
            placeholder="Recipient email"
            value={form.recipient_email}
            onChange={(e) => setForm((current) => ({ ...current, recipient_email: e.target.value }))}
          />
          <select
            className="input"
            value={form.frequency}
            onChange={(e) => setForm((current) => ({ ...current, frequency: e.target.value }))}
          >
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
          <input
            className="input"
            type="time"
            value={form.send_time}
            onChange={(e) => setForm((current) => ({ ...current, send_time: e.target.value }))}
          />
          {form.frequency === 'weekly' && (
            <select
              className="input"
              value={form.day_of_week}
              onChange={(e) => setForm((current) => ({ ...current, day_of_week: e.target.value }))}
            >
              <option value="0">Sunday</option>
              <option value="1">Monday</option>
              <option value="2">Tuesday</option>
              <option value="3">Wednesday</option>
              <option value="4">Thursday</option>
              <option value="5">Friday</option>
              <option value="6">Saturday</option>
            </select>
          )}
          {form.frequency === 'monthly' && (
            <input
              className="input"
              type="number"
              min="1"
              max="28"
              value={form.day_of_month}
              onChange={(e) => setForm((current) => ({ ...current, day_of_month: e.target.value }))}
            />
          )}
          <input
            className="input"
            placeholder="Timezone"
            value={form.timezone}
            onChange={(e) => setForm((current) => ({ ...current, timezone: e.target.value }))}
          />
        </div>

        {error && <p className="mt-3 text-sm text-red-600">{error}</p>}

        <div className="mt-4 flex justify-end">
          <button type="button" className="btn-primary text-xs" onClick={handleCreate} disabled={saveMutation.isPending}>
            {saveMutation.isPending ? 'Saving...' : 'Create Schedule'}
          </button>
        </div>

        <div className="mt-6 space-y-3">
          <h4 className="text-sm font-semibold text-gray-700">Existing schedules</h4>
          {schedules.length === 0 ? (
            <p className="text-xs text-gray-400">No scheduled reports yet.</p>
          ) : (
            schedules.map((schedule) => (
              <div key={schedule.id} className="rounded border border-gray-200 p-3 text-sm">
                <div className="flex items-start justify-between gap-4">
                  <div>
                    <p className="font-medium text-gray-800">{schedule.report_name}</p>
                    <p className="text-xs text-gray-500">
                      {schedule.frequency} at {schedule.send_time} ({schedule.timezone}) to {schedule.recipient_email}
                    </p>
                    <p className="text-xs text-gray-400">
                      Last run: {schedule.last_run_at ?? 'Never'}
                    </p>
                    {schedule.runs?.[0] && (
                      <p className={`text-xs ${schedule.runs[0].status === 'success' ? 'text-green-600' : 'text-red-600'}`}>
                        Latest run: {schedule.runs[0].status} {schedule.runs[0].message ? `- ${schedule.runs[0].message}` : ''}
                      </p>
                    )}
                  </div>
                  <button
                    type="button"
                    className="btn-danger text-xs"
                    onClick={() => deleteMutation.mutate(schedule.id)}
                    disabled={deleteMutation.isPending}
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  ) : null;

  return (
    <>
      <button type="button" className="btn-secondary text-xs" onClick={() => setOpen(true)}>
        Schedules
      </button>
      {typeof document !== 'undefined' ? createPortal(modal, document.body) : null}
    </>
  );
}
