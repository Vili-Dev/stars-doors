<?php
// Fonctions d'authentification et autorisation

// Empêcher l'accès direct
if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['user_email']) && 
           isset($_SESSION['user_role']);
}

/**
 * Vérifie si l'utilisateur est administrateur
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Vérifie si l'utilisateur est propriétaire
 * @return bool
 */
function isProprietaire() {
    return isLoggedIn() && 
           ($_SESSION['user_role'] === 'proprietaire' || $_SESSION['user_role'] === 'admin');
}

/**
 * Vérifie si l'utilisateur est locataire
 * @return bool
 */
function isLocataire() {
    return isLoggedIn() && $_SESSION['user_role'] === 'locataire';
}

/**
 * Obtient l'ID de l'utilisateur connecté
 * @return int|null
 */
function getCurrentUserId() {
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Obtient le rôle de l'utilisateur connecté
 * @return string|null
 */
function getCurrentUserRole() {
    return isLoggedIn() ? $_SESSION['user_role'] : null;
}

/**
 * Obtient les informations de l'utilisateur connecté
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'name' => $_SESSION['user_name'] ?? ''
    ];
}

/**
 * Exige que l'utilisateur soit connecté, sinon redirection vers login
 * @param string $redirect_after_login URL de redirection après connexion
 */
function requireLogin($redirect_after_login = null) {
    if (!isLoggedIn()) {
        $current_url = $redirect_after_login ?: $_SERVER['REQUEST_URI'];
        $login_url = 'login.php';
        
        // Ajuster le chemin si on est dans un sous-dossier
        if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
            $login_url = '../login.php';
        }
        
        header('Location: ' . $login_url . '?redirect=' . urlencode($current_url));
        exit;
    }
}

/**
 * Exige que l'utilisateur soit administrateur
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        http_response_code(403);
        setFlashMessage('Accès refusé. Privilèges administrateur requis.', 'danger');
        redirect('dashboard.php');
    }
}

/**
 * Exige que l'utilisateur soit propriétaire ou admin
 */
function requireProprietaire() {
    requireLogin();
    
    if (!isProprietaire()) {
        http_response_code(403);
        setFlashMessage('Accès refusé. Vous devez être propriétaire.', 'danger');
        redirect('dashboard.php');
    }
}

/**
 * Vérifie si l'utilisateur peut accéder à une ressource
 * @param int $resource_owner_id ID du propriétaire de la ressource
 * @return bool
 */
function canAccess($resource_owner_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admin peut tout voir
    if (isAdmin()) {
        return true;
    }
    
    // L'utilisateur peut voir ses propres ressources
    return getCurrentUserId() === (int)$resource_owner_id;
}

/**
 * Vérifie si l'utilisateur peut modifier une ressource
 * @param int $resource_owner_id ID du propriétaire de la ressource
 * @return bool
 */
function canEdit($resource_owner_id) {
    return canAccess($resource_owner_id);
}

/**
 * Vérifie si l'utilisateur peut supprimer une ressource
 * @param int $resource_owner_id ID du propriétaire de la ressource
 * @return bool
 */
function canDelete($resource_owner_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admin peut tout supprimer
    if (isAdmin()) {
        return true;
    }
    
    // L'utilisateur peut supprimer ses propres ressources
    return getCurrentUserId() === (int)$resource_owner_id;
}

/**
 * Vérifie la force d'un mot de passe
 * @param string $password
 * @return bool
 */
function isStrongPassword($password) {
    // Au moins 8 caractères, une majuscule, une minuscule, un chiffre
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

/**
 * Génère un token CSRF
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Régénère l'ID de session pour la sécurité
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Déconnecte l'utilisateur
 */
function logout() {
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
}

/**
 * Vérifie si l'utilisateur est toujours actif en base
 * @return bool
 */
function checkUserStatus() {
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT actif FROM users WHERE id_user = ?");
        $stmt->execute([getCurrentUserId()]);
        $user = $stmt->fetch();
        
        return $user && $user['actif'] == 1;
    } catch (PDOException $e) {
        error_log("Erreur vérification statut utilisateur: " . $e->getMessage());
        return false;
    }
}

/**
 * Middleware de vérification automatique du statut utilisateur
 */
function checkAuthMiddleware() {
    if (isLoggedIn() && !checkUserStatus()) {
        logout();
        setFlashMessage('Votre compte a été désactivé.', 'warning');
        redirect('login.php');
    }
}

// Exécution automatique du middleware sur chaque requête
if (isLoggedIn()) {
    checkAuthMiddleware();
}
?>