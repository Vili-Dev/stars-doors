// Added: WebSocket-powered chat client
// - Connects to WS server, sends/receives `chat` events
// - Persists messages via REST API `conversation/api/save_message.php`
// - Updates DOM with live messages and status badge
(() => {
  const cfg = window.CHAT_CONFIG || {};
  const statusEl = document.getElementById('ws-status');
  const messagesEl = document.getElementById('chat-messages');
  const formEl = document.getElementById('chat-form');
  const inputEl = document.getElementById('chat-input');

  // Allow sending even without recipient (self as default in API)
  if (!cfg.wsUrl || !cfg.userId) return;

  const ws = new WebSocket(cfg.wsUrl);

  const setStatus = (text, cls) => {
    if (!statusEl) return;
    statusEl.textContent = text;
    statusEl.className = 'badge ' + (cls || 'bg-secondary');
  };

  ws.addEventListener('open', () => {
    setStatus('Connecté', 'bg-success');
    // Identify this client
    ws.send(JSON.stringify({ type: 'hello', userId: cfg.userId }));
  });

  ws.addEventListener('close', () => setStatus('Hors ligne', 'bg-secondary'));
  ws.addEventListener('error', () => setStatus('Erreur', 'bg-danger'));

  const appendMessage = (msg, isMe) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex mb-3 ' + (isMe ? 'justify-content-end' : 'justify-content-start');
    const bubble = document.createElement('div');
    bubble.className = (isMe ? 'bg-primary text-white' : 'bg-light') + ' rounded p-2';
    bubble.style.maxWidth = '70%';
    bubble.innerHTML = '<div>' + escapeHtml(msg.content) + '</div>' +
                       '<small class="' + (isMe ? 'text-white-50' : 'text-muted') + '">' + new Date().toLocaleString() + '</small>';
    wrapper.appendChild(bubble);
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  };

  const escapeHtml = (str) => str.replace(/[&<>"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]));

  ws.addEventListener('message', (ev) => {
    try {
      const data = JSON.parse(ev.data);
      const effectiveToId = cfg.toId || cfg.userId;
      if (data.type === 'chat' && ((data.toId === cfg.userId && data.userId === effectiveToId) || (data.userId === cfg.userId && data.toId === effectiveToId))) {
        appendMessage({ content: data.content }, data.userId === cfg.userId);
      }
    } catch (e) {}
  });

  formEl?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = inputEl.value.trim();
    if (!text) return;

    const effectiveToId = cfg.toId || cfg.userId; // fallback to self
    const payload = { type: 'chat', userId: cfg.userId, toId: effectiveToId, content: text, listingId: cfg.listingId };
    if (ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify(payload));
    }

    // Persist to DB
    try {
      await fetch(cfg.saveUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ toId: effectiveToId, content: text, listingId: cfg.listingId })
      });
    } catch (e) {}

    appendMessage({ content: text }, true);
    inputEl.value = '';
  });
})();