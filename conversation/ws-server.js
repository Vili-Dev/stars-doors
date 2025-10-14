// Added: Simple WebSocket server for chat (development only)
// Broadcasts messages between sender and recipient; identifies clients on `hello`.
const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 8080 });

const clients = new Map(); // ws -> userId

wss.on('connection', (ws) => {
  ws.on('message', (message) => {
    let data;
    try { data = JSON.parse(message); } catch (e) { return; }
    if (data.type === 'hello' && data.userId) {
      clients.set(ws, data.userId);
      return;
    }
    if (data.type === 'chat' && data.userId && data.toId && typeof data.content === 'string') {
      // Broadcast to the intended recipient and sender (echo)
      for (const [client, uid] of clients.entries()) {
        if (client.readyState === WebSocket.OPEN && (uid === data.toId || uid === data.userId)) {
          client.send(JSON.stringify(data));
        }
      }
    }
  });
  ws.on('close', () => {
    clients.delete(ws);
  });
});

console.log('WebSocket server running on ws://127.0.0.1:8080');