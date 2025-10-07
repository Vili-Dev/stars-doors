<?php
session_start();
require_once 'includes/functions.php';

// Destruction complète de la session
if (session_status() === PHP_SESSION_ACTIVE) {
    // Suppression de toutes les variables de session
    $_SESSION = [];
    
    // Suppression du cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruction de la session
    session_destroy();
}

// Message de confirmation et redirection
session_start();
setFlashMessage('Vous avez été déconnecté avec succès.', 'success');
redirect('index.php');
?>