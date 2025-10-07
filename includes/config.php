<?php
// Configuration générale de l'application Stars Doors

// Empêcher l'accès direct
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 70400) {
    die('PHP 7.4 ou supérieur requis');
}

// Démarrage des sessions sécurisées si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    // Configuration sécurisée des sessions
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

// Chargement des constantes
require_once __DIR__ . '/../config/constants.php';

// Chargement des variables d'environnement depuis .env si le fichier existe
$env_file = __DIR__ . '/../config/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignorer les commentaires
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Supprimer les guillemets si présents
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Configuration des erreurs selon l'environnement
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Configuration du fuseau horaire
date_default_timezone_set(TIMEZONE);

// Configuration de l'encodage
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Headers de sécurité
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Fonction d'autoload simple pour les classes personnalisées
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Inclusion de la connexion à la base de données
require_once __DIR__ . '/database.php';
?>