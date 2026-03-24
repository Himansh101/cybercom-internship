const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8080/api';

const clearAuth = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
};

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
    const message = json.error ?? `HTTP ${res.status}`;
    const isAuthError = res.status === 401 || /token has expired|invalid token|missing or malformed authorization/i.test(message);

    if (isAuthError) {
      clearAuth();
      window.location.reload();
    }

    throw new Error(message);
  }
  return json;
};

const get  = (path)         => fetch(`${BASE}${path}`, { headers: headers() }).then(unwrap);
const post = (path, body)   => fetch(`${BASE}${path}`, { method: 'POST',   headers: headers(), body: JSON.stringify(body) }).then(unwrap);
const put  = (path, body)   => fetch(`${BASE}${path}`, { method: 'PUT',    headers: headers(), body: JSON.stringify(body) }).then(unwrap);
const del  = (path)         => fetch(`${BASE}${path}`, { method: 'DELETE', headers: headers() }).then(unwrap);
const postForm = (path, formData) => fetch(`${BASE}${path}`, {
  method: 'POST',
  headers: (() => {
    const t = getToken();
    return t ? { Authorization: `Bearer ${t}` } : {};
  })(),
  body: formData,
}).then(unwrap);

/**
 * Trigger a CSV download from the export endpoint.
 * The payload is sent as a query param because the browser opens the URL directly.
 */
const getBlob = async (path, payload) => {
  const t   = getToken();
  const url = `${BASE}${path}?payload=${encodeURIComponent(JSON.stringify(payload))}`;
  const authHeaders = t ? { Authorization: `Bearer ${t}` } : {};
  const res = await fetch(url, { headers: authHeaders });
  if (!res.ok) {
    let message = 'Export failed';
    try {
      const json = await res.json();
      message = json.error ?? message;
    } catch {
      // ignore non-JSON error bodies
    }

    if (res.status === 401) {
      clearAuth();
      window.location.reload();
    }
    throw new Error(message);
  }

  const blob = await res.blob();
  const objectUrl = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = objectUrl;
  a.download = `report_${Date.now()}.csv`;
  document.body.appendChild(a);
  a.click();
  a.remove();
  setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
};

export const api = {
  login:          (creds)     => post('/auth/login', creds),
  getSchema:      ()          => get('/schema'),
  queryReport:    (payload)   => post('/reports/query', payload),
  queryChart:     (payload)   => post('/reports/chart', payload),
  exportReport:   (payload)   => getBlob('/reports/export', payload),
  uploadCsv:      (file)      => {
    const formData = new FormData();
    formData.append('file', file);
    return postForm('/ingestion/upload', formData);
  },
  getFacets:      (field)     => get(`/facets/${field}`),
  getSavedViews:  ()          => get('/saved-views'),
  saveView:       (data)      => post('/saved-views', data),
  updateView:     (id, data)  => put(`/saved-views/${id}`, data),
  deleteView:     (id)        => del(`/saved-views/${id}`),
  getScheduledReports: ()     => get('/scheduled-reports'),
  saveScheduledReport: (data) => post('/scheduled-reports', data),
  deleteScheduledReport: (id) => del(`/scheduled-reports/${id}`),
};
