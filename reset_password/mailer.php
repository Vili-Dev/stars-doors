<?php
require_once __DIR__ . '/../includes/config.php';

/**
 * Helper d’envoi d’e-mails via EmailJS pour la réinitialisation de mot de passe.
 *
 * Ce fichier encapsule l’appel à l’API REST d’EmailJS côté serveur (via cURL).
 * Il prépare la charge utile (payload) selon la configuration et les variables
 * de template, envoie la requête HTTP et expose une interface simple:
 * - setFrom, addAddress, isHTML, send.
 *
 * Points importants:
 * - Configurez EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID et EMAILJS_PRIVATE_KEY
 *   (recommandé côté serveur) ou EMAILJS_PUBLIC_KEY dans `config/.env`.
 * - Dans votre template EmailJS, définissez le champ « To » avec `{{to_email}}`.
 * - N’envoyez pas en même temps `Authorization: Bearer <PRIVATE_KEY>` et `user_id`.
 */

/**
 * Classe EmailJSMailer
 *
 * Gère l’expéditeur, les destinataires, le contenu et les paramètres d’envoi.
 * Construit le payload EmailJS et effectue la requête POST vers `EMAILJS_API_URL`.
 */
class EmailJSMailer {
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $ErrorInfo = '';
    private $fromAddress = '';
    private $fromName = '';
    private $to = [];
    private $isHtml = true;

    /**
     * Définit l’adresse d’expéditeur et le nom d’affichage (optionnel).
     *
     * @param string $address Adresse email de l’expéditeur.
     * @param string $name    Nom d’affichage de l’expéditeur.
     */
    public function setFrom($address, $name = '') {
        $this->fromAddress = $address;
        $this->fromName = $name;
    }

    /**
     * Ajoute un destinataire à la liste d’envoi.
     *
     * Remarque: cet helper envoie au premier destinataire. Pour plusieurs
     * destinataires, envoyer une requête par destinataire.
     *
     * @param string $address Adresse email du destinataire.
     */
    public function addAddress($address) {
            $this->to[] = $address;
    }

    /**
     * Active/désactive le format HTML. Utile pour les templates.
     *
     * @param bool $isHtml True pour HTML, false pour texte brut.
     */
    public function isHTML($isHtml = true) {
        $this->isHtml = (bool)$isHtml;
    }

    /**
     * Envoie l’e-mail via l’API REST EmailJS.
     *
     * Valide la configuration, prépare `template_params`, ajoute les en-têtes
     * requis (`Origin`, `Authorization` le cas échéant) et exécute un POST cURL
     * vers `EMAILJS_API_URL`.
     *
     * @return bool True en cas de succès, false sinon (détails dans $ErrorInfo).
     */
    public function send() {
        if (!EMAILJS_SERVICE_ID || !EMAILJS_TEMPLATE_ID) {
            $this->ErrorInfo = 'EmailJS configuration missing. Please set EMAILJS_SERVICE_ID and EMAILJS_TEMPLATE_ID in .env';
            return false;
        }
        // Exige au moins une clé d’authentification (clé privée recommandée côté serveur)
        if (!EMAILJS_PRIVATE_KEY && !EMAILJS_PUBLIC_KEY) {
            $this->ErrorInfo = 'EmailJS keys missing. Set EMAILJS_PRIVATE_KEY (recommended for server) or EMAILJS_PUBLIC_KEY in .env';
            return false;
        }
        if (empty($this->to)) {
            $this->ErrorInfo = 'No recipient specified';
            return false;
        }

        // Utilise le premier destinataire. Pour du multi-destinataire, envoyer
        // une requête par destinataire.
        $toEmail = $this->to[0];

            // Prépare la charge utile JSON pour EmailJS.
            // Le template doit référencer ces variables (ex.: To: {{to_email}}).
            $payload = [
                'service_id' => EMAILJS_SERVICE_ID,
                'template_id' => EMAILJS_TEMPLATE_ID,
                'template_params' => [
                    'to_email' => $toEmail,
                    'reply_to' => $toEmail,
                    'from_email' => $this->fromAddress ?: MAIL_FROM_ADDRESS,
                    'from_name' => $this->fromName ?: MAIL_FROM_NAME,
                    'subject' => $this->Subject,
                    'message' => $this->Body,
                    'alt_message' => $this->AltBody,
                    'is_html' => $this->isHtml ? 'true' : 'false'
                ]
            ];
        // Inclure 'user_id' uniquement si aucune clé privée n’est utilisée
        // (EmailJS attend soit 'Authorization', soit 'user_id').
        if (EMAILJS_PUBLIC_KEY && !EMAILJS_PRIVATE_KEY) {
            $payload['user_id'] = EMAILJS_PUBLIC_KEY; // identifiant côté client (omettre si la clé privée est utilisée)
        }

        $ch = curl_init(EMAILJS_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        $headers = ['Content-Type: application/json'];
        // Ajoute l’en-tête Origin si disponible (utile pour la validation EmailJS)
        if (defined('SITE_URL') && SITE_URL) {
            $headers[] = 'Origin: ' . SITE_URL;
        }
        // Authentification côté serveur via clé privée (Bearer token)
        if (EMAILJS_PRIVATE_KEY) {
            $headers[] = 'Authorization: Bearer ' . EMAILJS_PRIVATE_KEY;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Paramètres SSL/TLS
        if (CURL_CAINFO_PATH) {
            curl_setopt($ch, CURLOPT_CAINFO, CURL_CAINFO_PATH);
        }
        // En développement, on peut relâcher la vérification TLS si nécessaire
        if (RELAX_SSL_VERIFY && ENVIRONMENT === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        // Utiliser un proxy HTTP si configuré
        if (HTTP_PROXY) {
            curl_setopt($ch, CURLOPT_PROXY, HTTP_PROXY);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            // Erreur de transport au niveau cURL
            $this->ErrorInfo = 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        // Succès EmailJS (codes 2xx)
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        // Erreur EmailJS: retourner le code et la réponse pour diagnostic
        $this->ErrorInfo = 'EmailJS error (HTTP ' . $httpCode . '): ' . $response;
        return false;
    }
}

$mail = new EmailJSMailer();
$mail->isHTML(true);
// Retourne une instance préconfigurée; le code appelant définit Sujet/Corps et destinataire
return $mail;