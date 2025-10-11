<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

// CSRF helper
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

$title = 'Modération des annonces - Administration';

// Paramètres de filtrage
$statut = $_GET['statut'] ?? '';
$type = $_GET['type'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(5, min(50, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$allowSort = ['created_at', 'updated_at', 'titre', 'prix_nuit', 'ville'];
if (!in_array($sort, $allowSort, true)) $sort = 'created_at';

// Construction WHERE
$where = [];
$params = [];

if (in_array($statut, ['en_attente', 'approuve', 'rejete'], true)) {
    $where[] = "a.statut = :statut";
    $params[':statut'] = $statut;
}

if (!empty($type)) {
    $where[] = "a.type_logement = :type";
    $params[':type'] = $type;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Compter le total
$total = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM annonces a $whereSql");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Erreur count annonces: " . $e->getMessage());
}

// Récupérer les annonces
$rows = [];
try {
    $sql = "SELECT a.id_annonce, a.titre, a.type_logement, a.prix_nuit, a.ville, 
                   a.statut, a.created_at, a.updated_at,
                   u.prenom, u.nom, u.email
            FROM annonces a
            LEFT JOIN users u ON a.id_user = u.id_user
            $whereSql
            ORDER BY a.$sort $dir
            LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur fetch annonces: " . $e->getMessage());
}

$totalPages = max(1, (int)ceil($total / $limit));

include '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Stars Doors</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Admin</a>
            <a class="nav-link" href="../dashboard.php">Retour au site</a>
            <a class="nav-link" href="../logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    
    <?php
    // Messages de succès
    if (isset($_GET['success'])):
        if ($_GET['success'] === 'approved'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Annonce approuvée avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'rejected'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                ⚠️ Annonce rejetée avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Annonce supprimée avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif;
    endif;
    
    // Messages d'erreur
    if (isset($_GET['error'])):
        if ($_GET['error'] === 'action_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ Erreur lors de l'action.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'not_found'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ Annonce introuvable.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif;
    endif;
    ?>
    
    <div class="row">
        <div class="col-12 mb-4">
            <h1>Modération des annonces</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Annonces</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <?php
        try {
            $stats = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                    SUM(CASE WHEN statut = 'approuve' THEN 1 ELSE 0 END) as approuve,
                    SUM(CASE WHEN statut = 'rejete' THEN 1 ELSE 0 END) as rejete
                FROM annonces
            ")->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= $stats['total'] ?></h3>
                        <p class="mb-0">Total</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <h3><?= $stats['en_attente'] ?></h3>
                        <p class="mb-0">⏳ En attente</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h3><?= $stats['approuve'] ?></h3>
                        <p class="mb-0">✅ Approuvées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <h3><?= $stats['rejete'] ?></h3>
                        <p class="mb-0">❌ Rejetées</p>
                    </div>
                </div>
            </div>
        <?php } catch (PDOException $e) {
            error_log("Erreur stats: " . $e->getMessage());
        } ?>
    </div>

    <!-- Filtres -->
    <form class="card mb-3" method="get" action="annonces.php">
        <div class="card-body row g-2">
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
                    <option value="approuve" <?= $statut === 'approuve' ? 'selected' : '' ?>>✅ Approuvées</option>
                    <option value="rejete" <?= $statut === 'rejete' ? 'selected' : '' ?>>❌ Rejetées</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <?php foreach ($allowSort as $s): ?>
                        <option value="<?= $s ?>" <?= $sort === $s ? 'selected' : '' ?>>
                            Trier par <?= ucfirst(str_replace('_', ' ', $s)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="dir" class="form-select">
                    <option value="desc" <?= $dir === 'DESC' ? 'selected' : '' ?>>Décroissant</option>
                    <option value="asc" <?= $dir === 'ASC' ? 'selected' : '' ?>>Croissant</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-primary w-100">Filtrer</button>
            </div>
        </div>
    </form>

    <!-- Tableau des annonces -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Prix/nuit</th>
                        <th>Ville</th>
                        <th>Propriétaire</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="9" class="text-center">Aucune annonce trouvée.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($rows as $annonce): ?>
                        <tr>
                            <td><?= (int)$annonce['id_annonce'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($annonce['titre']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($annonce['type_logement'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td><?= number_format($annonce['prix_nuit'], 0, ',', ' ') ?> €</td>
                            <td><?= htmlspecialchars($annonce['ville']) ?></td>
                            <td>
                                <small><?= htmlspecialchars($annonce['prenom'] . ' ' . $annonce['nom']) ?></small>
                            </td>
                            <td>
                                <?php
                                $badge = [
                                    'en_attente' => 'warning',
                                    'approuve' => 'success',
                                    'rejete' => 'danger'
                                ];
                                $label = [
                                    'en_attente' => '⏳ En attente',
                                    'approuve' => '✅ Approuvée',
                                    'rejete' => '❌ Rejetée'
                                ];
                                ?>
                                <span class="badge bg-<?= $badge[$annonce['statut']] ?? 'secondary' ?>">
                                    <?= $label[$annonce['statut']] ?? $annonce['statut'] ?>
                                </span>
                            </td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($annonce['created_at'])) ?></small>
                            </td>
                            <td>
                                <a href="annonce_view.php?id=<?= $annonce['id_annonce'] ?>" 
                                   class="btn btn-sm btn-info" title="Voir détails">
                                    👁️
                                </a>
                                
                                <?php if ($annonce['statut'] === 'en_attente'): ?>
                                    <a href="annonce_approve.php?id=<?= $annonce['id_annonce'] ?>" 
                                       class="btn btn-sm btn-success" title="Approuver"
                                       onclick="return confirm('Approuver cette annonce ?')">
                                        ✓
                                    </a>
                                    <a href="annonce_reject.php?id=<?= $annonce['id_annonce'] ?>" 
                                       class="btn btn-sm btn-warning" title="Rejeter"
                                       onclick="return confirm('Rejeter cette annonce ?')">
                                        ✗
                                    </a>
                                <?php endif; ?>
                                
                                <a href="annonce_delete.php?id=<?= $annonce['id_annonce'] ?>" 
                                   class="btn btn-sm btn-danger" title="Supprimer"
                                   onclick="return confirm('Supprimer définitivement cette annonce ?')">
                                    🗑️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>