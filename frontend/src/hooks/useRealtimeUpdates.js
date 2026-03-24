import { useEffect, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';

const WS_URL = import.meta.env.VITE_WS_URL ?? 'ws://localhost:3001';

/**
 * Subscribe to realtime report update events over WebSockets.
 */
export function useRealtimeUpdates() {
  const queryClient = useQueryClient();
  const [status, setStatus] = useState('connecting');
  const [lastEvent, setLastEvent] = useState(null);

  useEffect(() => {
    let socket;
    let reconnectTimer;
    let cancelled = false;

    const connect = () => {
      if (cancelled) {
        return;
      }

      setStatus('connecting');
      socket = new WebSocket(WS_URL);

      socket.addEventListener('open', () => {
        setStatus('connected');
      });

      socket.addEventListener('message', (event) => {
        try {
          const payload = JSON.parse(event.data);

          if (payload.type === 'report_data_updated') {
            setLastEvent(payload);
            queryClient.invalidateQueries({ queryKey: ['schema'] });
            queryClient.invalidateQueries({ queryKey: ['report'] });
            queryClient.invalidateQueries({ queryKey: ['chart'] });
            queryClient.invalidateQueries({ queryKey: ['facets'] });
          }
        } catch {
          // Ignore malformed websocket events.
        }
      });

      socket.addEventListener('close', () => {
        if (cancelled) {
          return;
        }

        setStatus('disconnected');
        reconnectTimer = window.setTimeout(connect, 3000);
      });

      socket.addEventListener('error', () => {
        setStatus('disconnected');
      });
    };

    connect();

    return () => {
      cancelled = true;
      if (reconnectTimer) {
        window.clearTimeout(reconnectTimer);
      }
      if (socket && socket.readyState === WebSocket.OPEN) {
        socket.close();
      }
    };
  }, [queryClient]);

  return { status, lastEvent };
}
