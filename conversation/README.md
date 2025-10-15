# Module Conversation

Ce dossier implémente la messagerie de Stars Doors avec deux approches complémentaires:
- Une page PHP classique (`messages.php`) basée sur rafraîchissement périodique et formulaire.
- Une page chat en temps réel (`chat.php`) basée sur WebSocket avec persistance via une API JSON.

## Aperçu rapide
- `messages.php`: liste les conversations, affiche l’historique, envoie des messages et marque comme lus.
- `chat.php`: vue modulable du chat (header/messages/form), client WebSocket (`assets/js/chat.js`) et injection de `CHAT_CONFIG`.
- `api/save_message.php`: endpoint JSON pour enregistrer les messages côté base.
- `lib/messages_service.php`: helpers pour charger interlocuteur, historiser, marquer lu, envoyer.
- `partials/`: fragments d’interface utilisés par `chat.php`.
- `ws-server.js`: serveur WebSocket simple pour le développement (diffusion entre expéditeur et destinataire).

## Fichiers et responsabilités

- `messages.php`
  - Sécurité: appelle `requireLogin()`.
  - Formulaire POST: `action=send`, champs `destinataire_id`, `sujet` (optionnel), `annonce_id` (optionnel), `contenu`.
  - Insertion DB: `INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, sujet, contenu, lu, date_envoi, date_lecture)`.
  - Conversations: requête corrélée pour dernier message, date, non lus, métadonnées (avatar, race, planète, distance).
  - Messages: récupération ordonnée (`ORDER BY m.date_envoi ASC`) et marquage comme lus.
  - UX: auto-scroll et rechargement toutes les 30s quand une conversation est ouverte.

- `chat.php`
  - Sécurité: `requireLogin()`.
  - Charge l’interlocuteur et l’historique via `lib/messages_service.php`.
  - Marque les messages entrants comme lus.
  - Injecte la configuration client:
    ```js
    window.CHAT_CONFIG = {
      wsUrl: 'ws://127.0.0.1:8080/',
      userId: <ID connecté>,
      toId: <ID interlocuteur>,
      listingId: <ID annonce ou 0>,
      saveUrl: 'api/save_message.php'
    }
    ```
  - Utilise les partials: `partials/chat_header.php`, `partials/chat_messages.php`, `partials/chat_form.php`.

- `api/save_message.php`
  - Authentification requise (`requireLogin`).
  - Attend JSON: `{ toId: number, content: string, listingId?: number, subject?: string }`.
  - Valide `content` (requis) et tolère interlocuteur manquant → par défaut soi-même.
  - Insère en base avec `lu = 0`, `date_envoi = NOW()`, `date_lecture = NULL` (avec `sujet` par défaut à `Chat` si absent), renvoie `{ ok: true }` ou erreur JSON.

- `lib/messages_service.php`
  - `getInterlocutor($pdo, $userId)`: infos utilisateur + race/planète/distance.
  - `getConversationMessages($pdo, $meId, $otherId, $limit)`: historique trié; fallback self-chat si `otherId` manquant.
  - `markAsRead($pdo, $meId, $otherId)`: met `lu = 1` et `date_lecture = NOW()` pour les messages reçus non lus.
  - `sendMessage($pdo, $fromId, $toId, $content, $listingId = null, $subject = 'Chat')`: envoie et persiste un message.

- `partials/`
  - `chat_header.php`: avatar, nom, informations, badge d’état WebSocket.
  - `chat_messages.php`: timeline des messages avec alignement selon auteur.
  - `chat_form.php`: formulaire minimal pour le client WS et fallback POST sans JS (`toId`, `listingId`, `subject`, `content`).

- `ws-server.js`
  - Démarre un serveur WebSocket sur `ws://127.0.0.1:8080`.
  - Identifie les clients via message `{ type: 'hello', userId }`.
  - Diffuse les messages `{ type: 'chat', userId, toId, content }` à l’expéditeur et au destinataire.
  - Usage: développement uniquement (pas sécurisé, pas de multi-room avancé).

- `assets/js/chat.js` (côté client)
  - Se connecte au serveur WS et envoie/écoute des événements `chat`.
  - Persiste chaque message via `fetch(CHAT_CONFIG.saveUrl)` (transmet `listingId`; `subject` par défaut côté serveur si absent).
  - Met à jour le DOM (append des bulles, statut Connecté/Hors ligne).
  - Tolère l’absence d’interlocuteur: fallback self-chat pour l’API.

## Modèle de données (table `messages`)
- `id_message` (int, PK, auto): identifiant du message.
- `id_expediteur` (int): ID de l’expéditeur.
- `id_destinataire` (int): ID du destinataire.
- `id_annonce` (int, nullable): ID d’annonce liée (optionnel).
- `sujet` (varchar): sujet du message (par défaut `Chat`).
- `contenu` (text): contenu du message.
- `lu` (bool/int): 0/1 pour lu/non lu (côté destinataire).
- `date_envoi` (datetime): horodatage d’envoi.
- `date_lecture` (datetime, nullable): horodatage quand le message est marqué lu.

## Sécurité & bonnes pratiques
- Auth obligatoire sur toutes les pages/API du module (`requireLogin`).
- Validation serveur: `filter_input`, `trim`, gestion erreurs PDO + logs.
- Affichage: `htmlspecialchars` pour éviter XSS.
- CSRF: envisager un token sur le POST de `messages.php`.
- WebSocket: serveur dev non sécurisé, à ne pas exposer en production tel quel.

## Installation & démarrage (mode chat)
1. Prérequis: Node.js installé.
2. Démarrer le WebSocket:
   ```bash
   cd conversation
   node ws-server.js
   ```
3. Ouvrir le chat en navigateur: `/conversation/chat.php?to=<ID_UTILISATEUR>`.
4. Le client utilise `ws://127.0.0.1:8080` et persiste via `api/save_message.php` (chemin relatif recommandé).

## Utilisation (mode messages)
- Ouvrir `/conversation/messages.php`.
- Sélectionner une conversation dans la liste de gauche.
- Envoyer un message via le formulaire (sujet optionnel, contenu; `annonce_id` optionnel).
- La page se recharge automatiquement toutes les 30 secondes.

## Configuration
- URL WS: modifier `wsUrl` dans `chat.php` (injection `CHAT_CONFIG`) ou adapter `ws-server.js` (port/hôte).
- API: `saveUrl` par défaut `api/save_message.php` (modifiable dans `CHAT_CONFIG`).
- Limite d’historique: paramètre `limit` de `getConversationMessages`.

## Dépannage
- Badge « Hors ligne »: vérifier que `ws-server.js` tourne et que le port 8080 n’est pas bloqué.
- Messages non persistés: contrôler les erreurs serveur dans `api/save_message.php` et la connexion base.
- Duplicata d’affichage: côté chat, les messages sont affichés à la fois via WS et en append local.
- Interlocuteur introuvable: le chat bascule en self-chat (messages enregistrés pour soi-même).

## Notes
- Le payload `listingId` est stocké dans `id_annonce` côté base.
- Le champ `subject` est optionnel; par défaut `Chat` côté serveur.
- Chemins relatifs (`saveUrl: 'api/save_message.php'`) recommandés pour éviter des 404 en déploiement sous sous-dossier.
- Le module peut coexister: `messages.php` (HTTP) pour simplicité, `chat.php` (WS) pour temps réel.