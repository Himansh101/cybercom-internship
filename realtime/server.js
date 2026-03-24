import http from 'node:http';
import { WebSocketServer } from 'ws';

const port = Number(process.env.REALTIME_PORT || 3001);
const clients = new Set();

const broadcast = (payload) => {
  const message = JSON.stringify(payload);
  for (const client of clients) {
    if (client.readyState === 1) {
      client.send(message);
    }
  }
};

const server = http.createServer((req, res) => {
  if (req.method === 'GET' && req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, clients: clients.size }));
    return;
  }

  if (req.method === 'POST' && req.url === '/broadcast') {
    let body = '';

    req.on('data', (chunk) => {
      body += chunk;
    });

    req.on('end', () => {
      try {
        const payload = JSON.parse(body || '{}');
        broadcast(payload);
        res.writeHead(202, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, clients: clients.size }));
      } catch {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: false, error: 'Invalid JSON payload' }));
      }
    });

    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ ok: false, error: 'Not found' }));
});

const wss = new WebSocketServer({ server });

wss.on('connection', (socket) => {
  clients.add(socket);

  socket.send(JSON.stringify({
    type: 'connection_ack',
    timestamp: new Date().toISOString(),
  }));

  socket.on('close', () => {
    clients.delete(socket);
  });
});

server.listen(port, '0.0.0.0', () => {
  console.log(`Realtime websocket server listening on ${port}`);
});
