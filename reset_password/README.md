# Réinitialisation du mot de passe

Ce dossier contient l’implémentation complète de la réinitialisation de mot de passe. Ce guide explique le fonctionnement, la configuration requise et les étapes de test et de dépannage.

## Aperçu du flux
- L’utilisateur demande un lien de réinitialisation via `forgot_password.php`.
- Le serveur génère un jeton, en stocke le hash et une date d’expiration en base dans `send_password_reset.php`, puis envoie un e‑mail avec un lien contenant le jeton.
- L’utilisateur ouvre le lien, le jeton est validé par `reset_password.php` et le formulaire de nouveau mot de passe s’affiche.
- Le nouveau mot de passe est validé, haché et enregistré par `process_reset_password.php`. Le jeton est ensuite invalidé et l’utilisateur est redirigé vers `login.php`.

## Pré‑requis
- Base de données `users` avec colonnes (noms tels qu’utilisés dans le projet):
  - `id_user` (PK), `email`
  - `mot_de_passe` (mot de passe haché)
  - `reset_token_hash` (hash du jeton)
  - `reset_token_expires_at` (expiration du jeton, type `DATETIME` ou `TIMESTAMP`)
- Fichier d’environnement `config/.env` configuré, notamment:
  - `SITE_URL=http://localhost/projets/stars-doors` (chemin correct sous Wamp)
  - Paramètres e‑mail via EmailJS (voir section Configuration des e‑mails)
- Fichiers communs inclus: `includes/config.php`, `includes/database.php`, `includes/functions.php` (flash messages et redirection)

## Détails par fichier
- `forgot_password.php`
  - Affiche un formulaire demandant l’email et envoie en `POST` vers `send_password_reset.php`.

- `send_password_reset.php`
  - Génère un jeton aléatoire: `bin2hex(random_bytes(16))`.
  - Calcule son hash: `hash('sha256', $token)` et le stocke dans `users.reset_token_hash`.
  - Définit l’expiration en base côté MySQL: `DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 MINUTE)` dans `users.reset_token_expires_at`.
  - Construit le lien: `rtrim(SITE_URL, '/') . "/reset_password/reset_password.php?token=" . urlencode($token)`.
  - Envoie un e‑mail via `reset_password/mailer.php`.

- `reset_password.php`
  - Reçoit le `token` via `GET`.
  - Vérifie en SQL le hash et la non‑expiration: `SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > UTC_TIMESTAMP()`.
  - Si valide, affiche le formulaire de nouveau mot de passe et les flash messages (via `displayFlashMessages()`).

- `process_reset_password.php`
  - Valide le `token` (même condition SQL que ci‑dessus), puis les champs `password` et `confirm_password`.
  - Applique les règles de longueur via `PASSWORD_MIN_LENGTH` (défini dans `config/constants.php`).
  - En cas d’erreur, pose un flash message et redirige vers `reset_password.php?token=<token>`.
  - Si OK, hache le mot de passe (`password_hash`) et met à jour `users.mot_de_passe`, puis nettoie `reset_token_hash` et `reset_token_expires_at`.
  - Pose un flash message de succès et redirige vers `../login.php`.

- `mailer.php`
  - Envoi via EmailJS côté serveur (cURL). Utilise `EMAILJS_SERVICE_ID`, `EMAILJS_TEMPLATE_ID` et une clé (`EMAILJS_PRIVATE_KEY` recommandée côté serveur ou `EMAILJS_PUBLIC_KEY`).
  - Ajoute `Origin: SITE_URL` dans les en‑têtes si défini.

## Configuration des e‑mails (EmailJS)
Définir dans `config/.env`:
- `EMAILJS_API_URL=https://api.emailjs.com/api/v1.0/email/send` (par défaut)
- `EMAILJS_SERVICE_ID=<votre_service_id>`
- `EMAILJS_TEMPLATE_ID=<votre_template_id>`
- `EMAILJS_PRIVATE_KEY=<votre_clef_privee>` (recommandé côté serveur) ou `EMAILJS_PUBLIC_KEY=<votre_user_id>`
- Émetteur:
  - `MAIL_FROM_ADDRESS=noreply@starsdoors.com`
  - `MAIL_FROM_NAME=Stars Doors`

Options utiles:
- `RELAX_SSL_VERIFY=true` en développement si certificats non valides.
- `CURL_CAINFO_PATH` pour certifcat CA personnalisé.
- `HTTP_PROXY` si un proxy HTTP est requis.

## Sécurité
- Le jeton n’est jamais stocké en clair en base; seul le hash SHA‑256 (`reset_token_hash`) est enregistré.
- Le jeton est valable 30 minutes (modifiable). La comparaison d’expiration s’effectue côté SQL avec `UTC_TIMESTAMP()` pour éviter les problèmes de fuseau horaire.
- Le jeton est invalidé après usage (champs `reset_token_hash` et `reset_token_expires_at` remis à `NULL`).
- Le jeton dans l’URL est encodé (`urlencode`) pour éviter les erreurs liées aux caractères spéciaux.

## Tests (pas à pas)
1. Ouvrir `login.php` et cliquer sur « Réinitialiser » (lien vers `reset_password/forgot_password.php`).
2. Renseigner un email existant et soumettre.
3. Vérifier que l’email reçu contient une URL du type: `http://localhost/projets/stars-doors/reset_password/reset_password.php?token=...`.
4. Cliquer le lien. La page ne doit pas afficher « Token invalide ou expiré. » dans les 30 minutes.
5. Saisir et confirmer un nouveau mot de passe conforme à `PASSWORD_MIN_LENGTH`.
6. Soumettre: un message de succès s’affiche puis redirection vers `login.php`.
7. Tenter une nouvelle ouverture du lien: il doit être invalide (jeton nettoyé).

## Dépannage
- 404 « not found » sur le lien de l’email:
  - Vérifier `SITE_URL` dans `config/.env`. Sous Wamp, l’exemple correct est `http://localhost/projets/stars-doors`.
  - Le lien doit contenir `/reset_password/reset_password.php?token=...`.

- « Token expiré »:
  - Les scripts utilisent `UTC_TIMESTAMP()` côté base; assurez‑vous que `users.reset_token_expires_at` soit de type `DATETIME`/`TIMESTAMP` et stocke une valeur UTC.
  - Régénérez un nouveau jeton en relançant la procédure si le délai de 30 minutes est dépassé.

- « Call to undefined function setFlashMessage() »:
  - Vérifier l’inclusion de `includes/functions.php` dans les fichiers concernés (`reset_password/reset_password.php` et `reset_password/process_reset_password.php`).

## Personnalisation
- Modifier la durée d’expiration:
  - Dans `send_password_reset.php`, ajuster `INTERVAL 30 MINUTE`.
- Modifier la redirection après succès:
  - Dans `process_reset_password.php`, remplacer `redirect('../login.php')` par votre cible.
- Personnaliser le contenu de l’e‑mail:
  - Adapter `Subject` et `Body` dans `send_password_reset.php` et le template EmailJS.

## Structure et dépendances
- Pages: `forgot_password.php`, `reset_password.php`
- Traitements: `send_password_reset.php`, `process_reset_password.php`
- Envoi d’e‑mail: `mailer.php`
- Configs: `includes/config.php`, `includes/database.php`, `config/constants.php`, `config/.env`
- Utilitaires: `includes/functions.php` (flash/redirect)