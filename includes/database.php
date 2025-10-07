<?php
// Connexion à la base de données MySQL avec PDO

// Empêcher l'accès direct
if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

try {
    // Configuration de la connexion MySQL
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'stars_doors';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    $port = $_ENV['DB_PORT'] ?? 3306;
    
    // DSN pour MySQL
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    // Options PDO pour la sécurité et les performances
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_PERSISTENT => false, // Connexions persistantes désactivées par défaut
    ];
    
    // Création de la connexion PDO
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Vérification de la connexion en mode développement
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        // Test simple de la connexion
        $stmt = $pdo->query('SELECT 1');
        if (!$stmt) {
            throw new PDOException('Impossible de tester la connexion à la base de données');
        }
    }
    
} catch (PDOException $e) {
    // Gestion des erreurs de connexion
    $error_message = 'Erreur de connexion à la base de données';
    
    // En développement, afficher plus de détails
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        $error_message .= ': ' . $e->getMessage();
        error_log("Erreur PDO: " . $e->getMessage());
    } else {
        // En production, logger l'erreur sans l'afficher
        error_log("Erreur de connexion DB: " . $e->getMessage());
    }
    
    // Affichage d'une page d'erreur générique
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Service temporairement indisponible - Stars Doors</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .error-container { max-width: 500px; margin: 0 auto; }
            h1 { color: #e74c3c; }
            p { color: #666; line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Service temporairement indisponible</h1>
            <p>Nous rencontrons actuellement des difficultés techniques. Veuillez réessayer dans quelques instants.</p>
            <p>Si le problème persiste, contactez l'équipe technique.</p>
            <?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px; text-align: left;">
                <strong>Erreur de développement :</strong><br>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Fonction utilitaire pour exécuter des requêtes préparées
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erreur SQL: " . $e->getMessage() . " - SQL: " . $sql);
        throw $e;
    }
}

// Fonction pour commencer une transaction
function beginTransaction($pdo) {
    return $pdo->beginTransaction();
}

// Fonction pour valider une transaction
function commitTransaction($pdo) {
    return $pdo->commit();
}

// Fonction pour annuler une transaction
function rollbackTransaction($pdo) {
    return $pdo->rollBack();
}

// Variable globale pour la connexion (accessible dans toute l'application)
$GLOBALS['pdo'] = $pdo;
?>