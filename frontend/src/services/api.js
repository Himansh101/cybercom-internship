const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8080/api';

/** Retrieve the JWT from localStorage. */
const getToken = () => localStorage.getItem('token');

/** Build default headers, attaching Authorization if a token exists. */
const headers = (extra = {}) => {
  const h = { 'Content-Type': 'application/json', ...extra };
  const t = getToken();
  if (t) h['Authorization'] = `Bearer ${t}`;
  return h;
};

/**
 * Unwrap a fetch response.
 * Throws if success === false or HTTP status >= 400.
 */
const unwrap = async (res) => {
  const json = await res.json();
  if (!res.ok || !json.success) {
    throw new Error(json.error ?? `HTTP ${res.status}`);
  }
  return json;
};

const get  = (path)         => fetch(`${BASE}${path}`, { headers: headers() }).then(unwrap);
const post = (path, body)   => fetch(`${BASE}${path}`, { method: 'POST',   headers: headers(), body: JSON.stringify(body) }).then(unwrap);
const put  = (path, body)   => fetch(`${BASE}${path}`, { method: 'PUT',    headers: headers(), body: JSON.stringify(body) }).then(unwrap);
const del  = (path)         => fetch(`${BASE}${path}`, { method: 'DELETE', headers: headers() }).then(unwrap);

/**
 * Trigger a CSV download from the export endpoint.
 * The payload is sent as a query param because the browser opens the URL directly.
 */
const getBlob = async (path, payload) => {
  const t   = getToken();
  const url = `${BASE}${path}?payload=${encodeURIComponent(JSON.stringify(payload))}`;
  const res = await fetch(url, { headers: { Authorization: `Bearer ${t}` } });
  if (!res.ok) throw new Error('Export failed');
  const blob = await res.blob();
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = `report_${Date.now()}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
};

export const api = {
  login:          (creds)     => post('/auth/login', creds),
  getSchema:      ()          => get('/schema'),
  queryReport:    (payload)   => post('/reports/query', payload),
  queryChart:     (payload)   => post('/reports/chart', payload),
  exportReport:   (payload)   => getBlob('/reports/export', payload),
  getFacets:      (field)     => get(`/facets/${field}`),
  getSavedViews:  ()          => get('/saved-views'),
  saveView:       (data)      => post('/saved-views', data),
  updateView:     (id, data)  => put(`/saved-views/${id}`, data),
  deleteView:     (id)        => del(`/saved-views/${id}`),
};
