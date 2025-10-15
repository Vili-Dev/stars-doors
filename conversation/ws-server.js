// Serveur WebSocket simple pour le chat (développement uniquement)
// Diffuse les messages entre l'expéditeur et le destinataire; identifie les clients via `hello`.
const WebSocket = require('ws'); // Import de la bibliothèque WebSocket côté Node.js

const wss = new WebSocket.Server({ port: 8080 }); // Démarrage du serveur WS sur le port 8080

const clients = new Map(); // Association des connexions (ws) aux identifiants utilisateurs (userId)

wss.on('connection', (ws) => {
  // Nouvelle connexion client
  ws.on('message', (message) => {
    let data;
    // Parsing JSON sécurisé; abandon si payload invalide
    try { data = JSON.parse(message); } catch (e) { return; }
    // Handshake d'identification: mémorise l'userId du client
    if (data.type === 'hello' && data.userId) {
      clients.set(ws, data.userId);
      return;
    }
    // Message de chat: nécessite userId, toId et un contenu texte
    if (data.type === 'chat' && data.userId && data.toId && typeof data.content === 'string') {
      // Diffusion au destinataire ciblé et écho à l'expéditeur
      for (const [client, uid] of clients.entries()) {
        // Envoi uniquement aux clients connectés concernés
        if (client.readyState === WebSocket.OPEN && (uid === data.toId || uid === data.userId)) {
          client.send(JSON.stringify(data));
        }
      }
    }
  });
  // Nettoyage à la déconnexion
  ws.on('close', () => {
    clients.delete(ws);
  });
});

console.log('Serveur WebSocket démarré sur ws://127.0.0.1:8080');

// Notes de sécurité et améliorations possibles:
// - Usage prévu en développement: pas d'authentification, ni TLS, ni vérification d'origine.
// - Ajouter un mécanisme ping/pong pour détecter les connexions mortes.
// - Gérer des « rooms »/canaux pour isoler des conversations.
// - En production: placer derrière un proxy (ex. Nginx), activer TLS et authentifier les clients.