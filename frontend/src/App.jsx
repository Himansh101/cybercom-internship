import { useState, useEffect } from 'react';
import ReportPage from './pages/ReportPage.jsx';

/**
 * App root — handles login gate.
 * If no JWT is in localStorage, show the login form.
 */
export default function App() {
  const [token, setToken] = useState(() => localStorage.getItem('token'));
  const [email, setEmail]       = useState('admin@demo.com');
  const [password, setPassword] = useState('password');
  const [error, setError]       = useState('');
  const [loading, setLoading]   = useState(false);

  const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8080/api';

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res  = await fetch(`${BASE}/auth/login`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ email, password }),
      });
      const json = await res.json();
      if (!json.success) throw new Error(json.error ?? 'Login failed');
      localStorage.setItem('token', json.data.token);
      localStorage.setItem('user',  JSON.stringify(json.data.user));
      setToken(json.data.token);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setToken(null);
  };

  if (!token) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="card w-full max-w-sm p-8">
          <h1 className="text-xl font-semibold mb-1 text-center">Reporting System</h1>
          <p className="text-gray-500 text-xs text-center mb-6">Sign in to continue</p>

          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-xs">
              {error}
            </div>
          )}

          <form onSubmit={handleLogin} className="space-y-4">
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Email</label>
              <input
                type="email"
                className="input"
                value={email}
                onChange={e => setEmail(e.target.value)}
                required
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Password</label>
              <input
                type="password"
                className="input"
                value={password}
                onChange={e => setPassword(e.target.value)}
                required
              />
            </div>
            <button type="submit" className="btn-primary w-full justify-center" disabled={loading}>
              {loading ? 'Signing in…' : 'Sign in'}
            </button>
          </form>

          <p className="text-xs text-gray-400 text-center mt-4">
            Demo: admin@demo.com / password
          </p>
        </div>
      </div>
    );
  }

  return <ReportPage onLogout={handleLogout} />;
}
