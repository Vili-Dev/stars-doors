<?php
// Constantes de configuration pour Stars Doors

// Empêcher l'accès direct
if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

// Version de l'application
define('APP_VERSION', '1.0.0');
define('APP_NAME', $_ENV['SITE_NAME'] ?? 'Stars Doors');

// Configuration du site - AUTO-DÉTECTION DU CHEMIN
if (!empty($_ENV['SITE_URL'])) {
    define('SITE_URL', rtrim($_ENV['SITE_URL'], '/'));
} else {
    // Auto-détection du SITE_URL basé sur le chemin actuel
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // Extraire le chemin jusqu'au dossier racine du projet
    $path = str_replace('\\', '/', dirname($script));
    $path = preg_replace('#/config$#', '', $path); // Enlever /config si présent
    $path = preg_replace('#/includes$#', '', $path); // Enlever /includes si présent
    $path = preg_replace('#/admin$#', '', $path); // Enlever /admin si présent
    $path = rtrim($path, '/');
    define('SITE_URL', $protocol . '://' . $host . $path);
}
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@starsdoors.com');

// Environnement
define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'development');
define('DEBUG_MODE', filter_var($_ENV['DEBUG_MODE'] ?? false, FILTER_VALIDATE_BOOLEAN));

// Configuration de sécurité
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'change_this_in_production');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 120)); // minutes
define('CSRF_TOKEN_LIFETIME', 3600); // secondes

// Configuration de la base de données
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'stars_doors');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_PORT', (int)($_ENV['DB_PORT'] ?? 3306));
define('DB_CHARSET', 'utf8mb4');

// Configuration des uploads
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 5242880)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', $_ENV['ALLOWED_IMAGE_TYPES'] ?? 'jpg,jpeg,png,gif,webp'));
define('UPLOAD_LISTINGS_PATH', UPLOAD_PATH . 'listings/');
define('UPLOAD_AVATARS_PATH', UPLOAD_PATH . 'avatars/');

// Configuration email
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'localhost');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@starsdoors.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Stars Doors');
define('MAIL_AUTH_TYPE', $_ENV['MAIL_AUTH_TYPE'] ?? '');
define('MAIL_SMTP_AUTOTLS', filter_var($_ENV['MAIL_SMTP_AUTOTLS'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('MAIL_SMTPAUTH', filter_var($_ENV['MAIL_SMTPAUTH'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('MAIL_FALLBACK_ENABLED', filter_var($_ENV['MAIL_FALLBACK_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN));
// Debug for mail (PHPMailer SMTP transcript in development)
define('MAIL_DEBUG', filter_var($_ENV['MAIL_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
// EmailJS configuration
define('EMAILJS_SERVICE_ID', $_ENV['EMAILJS_SERVICE_ID'] ?? '');
define('EMAILJS_TEMPLATE_ID', $_ENV['EMAILJS_TEMPLATE_ID'] ?? '');
define('EMAILJS_PUBLIC_KEY', $_ENV['EMAILJS_PUBLIC_KEY'] ?? '');
define('EMAILJS_PRIVATE_KEY', $_ENV['EMAILJS_PRIVATE_KEY'] ?? '');
define('EMAILJS_API_URL', $_ENV['EMAILJS_API_URL'] ?? 'https://api.emailjs.com/api/v1.0/email/send');
// cURL/SSL configuration
define('CURL_CAINFO_PATH', $_ENV['CURL_CAINFO_PATH'] ?? '');
define('RELAX_SSL_VERIFY', filter_var($_ENV['RELAX_SSL_VERIFY'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('HTTP_PROXY', $_ENV['HTTP_PROXY'] ?? '');

// Configuration business
define('DEFAULT_COMMISSION_RATE', (float)($_ENV['DEFAULT_COMMISSION_RATE'] ?? 5.0));
define('BOOKING_CONFIRMATION_DELAY', (int)($_ENV['BOOKING_CONFIRMATION_DELAY'] ?? 24)); // heures
define('MIN_BOOKING_DAYS', 1);
define('MAX_BOOKING_DAYS', 365);

// Configuration de pagination
define('LISTINGS_PER_PAGE', 20);
define('REVIEWS_PER_PAGE', 10);
define('MESSAGES_PER_PAGE', 15);

// Configuration cache
define('CACHE_ENABLED', filter_var($_ENV['CACHE_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('CACHE_TTL', (int)($_ENV['CACHE_TTL'] ?? 3600));
define('CACHE_PATH', __DIR__ . '/../cache/');

// Configuration timezone
define('TIMEZONE', 'Europe/Paris');

// Configuration des logs
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'info');
define('LOG_FILE', $_ENV['LOG_FILE'] ?? 'logs/app.log');
define('LOG_PATH', __DIR__ . '/../logs/');

// Mode maintenance
define('MAINTENANCE_MODE', filter_var($_ENV['MAINTENANCE_MODE'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('MAINTENANCE_MESSAGE', $_ENV['MAINTENANCE_MESSAGE'] ?? 'Site en maintenance. Retour bientôt.');

// Configuration des services externes
define('GOOGLE_ANALYTICS_ID', $_ENV['GOOGLE_ANALYTICS_ID'] ?? '');
define('FACEBOOK_PIXEL_ID', $_ENV['FACEBOOK_PIXEL_ID'] ?? '');

// Configuration rate limiting
define('RATE_LIMIT_ENABLED', filter_var($_ENV['RATE_LIMIT_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('RATE_LIMIT_MAX_REQUESTS', (int)($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? 100));
define('RATE_LIMIT_WINDOW', (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 3600)); // secondes

// Constantes des rôles utilisateur
define('ROLE_LOCATAIRE', 'locataire');
define('ROLE_PROPRIETAIRE', 'proprietaire');
define('ROLE_ADMIN', 'admin');

// Constantes des statuts de réservation
define('RESERVATION_EN_ATTENTE', 'en_attente');
define('RESERVATION_CONFIRMEE', 'confirmee');
define('RESERVATION_ANNULEE', 'annulee');
define('RESERVATION_TERMINEE', 'terminee');

// Constantes des types de logement
define('TYPE_APPARTEMENT', 'appartement');
define('TYPE_MAISON', 'maison');
define('TYPE_STUDIO', 'studio');
define('TYPE_VILLA', 'villa');
define('TYPE_CHAMBRE', 'chambre');

// Constantes des équipements
define('EQUIPEMENTS_DISPONIBLES', [
    'wifi' => 'WiFi',
    'parking' => 'Parking',
    'climatisation' => 'Climatisation',
    'lave_linge' => 'Lave-linge',
    'television' => 'Télévision',
    'animaux_acceptes' => 'Animaux acceptés'
]);

// Configuration des mots de passe
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SYMBOLS', false);

// Configuration des images
define('IMAGE_QUALITY', 85); // Qualité JPEG
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_MAX_HEIGHT', 1080);
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 200);

// Configuration des cookies
define('COOKIE_SECURE', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
define('COOKIE_HTTPONLY', true);
define('COOKIE_SAMESITE', 'Strict');

// Messages d'erreur par défaut
define('ERROR_MESSAGES', [
    'db_connection' => 'Erreur de connexion à la base de données',
    'invalid_credentials' => 'Email ou mot de passe incorrect',
    'access_denied' => 'Accès refusé',
    'invalid_token' => 'Token de sécurité invalide',
    'file_upload_error' => 'Erreur lors de l\'upload du fichier',
    'invalid_file_type' => 'Type de fichier non autorisé',
    'file_too_large' => 'Fichier trop volumineux',
    'required_field' => 'Ce champ est requis',
    'invalid_email' => 'Format d\'email invalide',
    'invalid_phone' => 'Format de téléphone invalide',
    'weak_password' => 'Mot de passe trop faible',
    'passwords_mismatch' => 'Les mots de passe ne correspondent pas',
    'email_exists' => 'Cet email est déjà utilisé',
    'user_not_found' => 'Utilisateur non trouvé',
    'account_disabled' => 'Compte désactivé',
    'booking_unavailable' => 'Ces dates ne sont pas disponibles',
    'invalid_dates' => 'Dates invalides',
    'maintenance_mode' => 'Site en maintenance'
]);

// URLs des API externes (pour futures intégrations)
define('GOOGLE_MAPS_API_URL', 'https://maps.googleapis.com/maps/api/');
define('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5/');

// Création des dossiers nécessaires s'ils n'existent pas
$directories = [
    UPLOAD_PATH,
    UPLOAD_LISTINGS_PATH,
    UPLOAD_AVATARS_PATH,
    CACHE_PATH,
    LOG_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configuration du gestionnaire d'erreurs personnalisé en mode développement
if (ENVIRONMENT === 'development') {
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error_info = [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        error_log('PHP Error: ' . json_encode($error_info));
        
        if (DEBUG_MODE) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 5px; border-radius: 4px;'>";
            echo "<strong>PHP Error:</strong> $message in <strong>$file</strong> on line <strong>$line</strong>";
            echo "</div>";
        }
        
        return true;
    });
}

// Vérification de maintenance avant toute autre opération
if (MAINTENANCE_MODE && !defined('MAINTENANCE_BYPASS')) {
    // Autoriser l'accès aux administrateurs
    session_start();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Maintenance - <?php echo APP_NAME; ?></title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
                .maintenance { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #dc3545; margin-bottom: 20px; }
                p { color: #666; line-height: 1.6; }
                .icon { font-size: 3em; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="maintenance">
                <div class="icon">🔧</div>
                <h1>Site en maintenance</h1>
                <p><?php echo htmlspecialchars(MAINTENANCE_MESSAGE); ?></p>
                <p><small>Merci de votre patience.</small></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>