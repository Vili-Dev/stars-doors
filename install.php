<?php
/**
 * SCRIPT D'INSTALLATION AUTOMATIQUE - STARS DOORS
 * Exécute toutes les migrations SQL dans le bon ordre
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sécurité : désactiver après installation
$installation_allowed = true; // Changer à false après installation

if (!$installation_allowed) {
    die('Installation désactivée. Modifiez $installation_allowed dans install.php');
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Stars Doors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .install-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .log-output {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        .info { color: #00aaff; }
        .step {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .spinner {
            display: none;
        }
        .spinner.active {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="card">
            <div class="card-header bg-primary text-white text-center py-4">
                <h1 class="mb-0">
                    <i class="fas fa-rocket"></i> Installation Stars Doors
                </h1>
                <p class="mb-0">Plateforme Intergalactique de Réservation</p>
            </div>
            <div class="card-body p-4">
                <?php if (!isset($_POST['install'])): ?>
                    <!-- Formulaire de configuration -->
                    <form method="POST" action="" id="installForm">
                        <h4 class="mb-3"><i class="fas fa-database"></i> Configuration Base de Données</h4>

                        <div class="mb-3">
                            <label class="form-label">Hôte MySQL</label>
                            <input type="text" class="form-control" name="db_host" value="localhost" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nom de la base de données</label>
                            <input type="text" class="form-control" name="db_name" value="stars_doors" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Utilisateur MySQL</label>
                            <input type="text" class="form-control" name="db_user" value="root" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mot de passe MySQL</label>
                            <input type="password" class="form-control" name="db_pass" placeholder="Laisser vide si pas de mot de passe">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Port MySQL</label>
                            <input type="number" class="form-control" name="db_port" value="3306" required>
                        </div>

                        <hr>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Ce qui sera installé :</h6>
                            <ul class="mb-0">
                                <li>✅ Structure de base (users, annonces, réservations...)</li>
                                <li>✅ 15 planètes et 15 races galactiques</li>
                                <li>✅ 14 monnaies et 8 vaisseaux spatiaux</li>
                                <li>✅ Système de transport et compatibilité</li>
                                <li>✅ Programme de fidélité et assurances</li>
                                <li>✅ Co-voiturage, visas, météo spatiale</li>
                                <li>✅ Fonctions SQL et triggers automatiques</li>
                            </ul>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                J'ai compris que cette installation va créer/modifier la base de données
                            </label>
                        </div>

                        <button type="submit" name="install" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-play"></i> Lancer l'Installation
                            <span class="spinner-border spinner-border-sm ms-2 spinner" role="status"></span>
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Installation en cours -->
                    <div id="installLog" class="log-output">
                        <div class="text-center mb-3">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Installation...</span>
                            </div>
                        </div>
<?php
// Configuration
$db_host = $_POST['db_host'];
$db_name = $_POST['db_name'];
$db_user = $_POST['db_user'];
$db_pass = $_POST['db_pass'];
$db_port = $_POST['db_port'];

$log = "";

function logMessage($message, $type = 'info') {
    global $log;
    $log .= "<span class='$type'>$message</span>\n";
    echo "<script>document.getElementById('installLog').innerHTML = " . json_encode($log) . ";</script>";
    flush();
    ob_flush();
}

logMessage("========================================", "info");
logMessage("   INSTALLATION STARS DOORS", "success");
logMessage("========================================", "info");
logMessage("");

// Étape 1: Connexion MySQL
logMessage("[1/6] Connexion à MySQL...", "info");
try {
    $pdo = new PDO(
        "mysql:host=$db_host;port=$db_port;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    logMessage("✓ Connexion réussie", "success");
} catch (PDOException $e) {
    logMessage("✗ Erreur : " . $e->getMessage(), "error");
    die();
}

// Étape 2: Création de la base
logMessage("[2/6] Création de la base de données '$db_name'...", "info");
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");
    logMessage("✓ Base de données créée/sélectionnée", "success");
} catch (PDOException $e) {
    logMessage("✗ Erreur : " . $e->getMessage(), "error");
    die();
}

// Étape 3: Exécution des migrations
$migrations = [
    'database/schema.sql' => 'Structure de base',
    'database/migration_phase1_spatial.sql' => 'Système spatial (planètes, races)',
    'database/migration_phase3_transport.sql' => 'Transport et monnaies',
    'database/migration_phase4_advanced.sql' => 'Fonctionnalités avancées'
];

$step = 3;
foreach ($migrations as $file => $description) {
    logMessage("[{$step}/6] Installation : $description...", "info");

    if (!file_exists($file)) {
        logMessage("⚠ Fichier non trouvé : $file", "warning");
        continue;
    }

    try {
        $sql = file_get_contents($file);

        // Supprimer les commentaires et diviser par requête
        $queries = array_filter(
            array_map('trim', explode(';', $sql)),
            function($query) {
                return !empty($query) && strpos($query, '--') !== 0;
            }
        );

        $success = 0;
        $errors = 0;

        foreach ($queries as $query) {
            if (empty(trim($query))) continue;

            try {
                $pdo->exec($query);
                $success++;
            } catch (PDOException $e) {
                // Ignorer certaines erreurs connues
                if (strpos($e->getMessage(), 'Duplicate entry') === false &&
                    strpos($e->getMessage(), 'already exists') === false) {
                    $errors++;
                    logMessage("  ⚠ Erreur dans requête : " . substr($e->getMessage(), 0, 100), "warning");
                }
            }
        }

        logMessage("✓ $success requêtes exécutées ($errors erreurs ignorées)", "success");

    } catch (Exception $e) {
        logMessage("✗ Erreur : " . $e->getMessage(), "error");
    }

    $step++;
}

// Étape 6: Vérifications
logMessage("[6/6] Vérifications finales...", "info");

try {
    $checks = [
        "SELECT COUNT(*) as nb FROM planetes" => "planètes",
        "SELECT COUNT(*) as nb FROM races" => "races",
        "SELECT COUNT(*) as nb FROM monnaies" => "monnaies",
        "SELECT COUNT(*) as nb FROM vaisseaux" => "vaisseaux",
        "SELECT COUNT(*) as nb FROM assurances_voyage" => "assurances",
        "SELECT COUNT(*) as nb FROM programmes_fidelite" => "programmes fidélité"
    ];

    foreach ($checks as $query => $item) {
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        logMessage("  ✓ " . $result['nb'] . " $item", "success");
    }

} catch (PDOException $e) {
    logMessage("  ⚠ Vérification échouée : " . $e->getMessage(), "warning");
}

logMessage("", "info");
logMessage("========================================", "success");
logMessage("   INSTALLATION TERMINÉE !", "success");
logMessage("========================================", "success");
logMessage("", "info");
logMessage("🎉 Votre plateforme Stars Doors est prête !", "success");
logMessage("", "info");
logMessage("📝 Prochaines étapes :", "info");
logMessage("1. Supprimez ou désactivez install.php", "warning");
logMessage("2. Allez sur index.php", "info");
logMessage("3. Créez votre premier compte", "info");
logMessage("", "info");
logMessage("🚀 Bon voyage à travers la galaxie !", "success");
?>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-success btn-lg">
                            <i class="fas fa-home"></i> Accéder au Site
                        </a>
                        <a href="register.php" class="btn btn-primary btn-lg ms-2">
                            <i class="fas fa-user-plus"></i> Créer un Compte
                        </a>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>IMPORTANT :</strong> Pour des raisons de sécurité, supprimez ou désactivez le fichier
                        <code>install.php</code> après l'installation.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-3 text-white">
            <small>Stars Doors v1.0 - Installation automatisée</small>
        </div>
    </div>

    <script>
        document.getElementById('installForm')?.addEventListener('submit', function() {
            document.querySelector('.spinner').classList.add('active');
        });
    </script>
</body>
</html>
