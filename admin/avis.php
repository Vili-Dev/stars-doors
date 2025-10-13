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

$title = 'Modération des avis - Administration';

// Paramètres de filtrage
$statut = $_GET['statut'] ?? '';
$sort = $_GET['sort'] ?? 'date_avis';
$dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(5, min(50, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$allowSort = ['date_avis', 'note'];
if (!in_array($sort, $allowSort, true)) $sort = 'date_avis';

// Construction WHERE
$where = [];
$params = [];

if (in_array($statut, ['en_attente', 'approuve', 'rejete'], true)) {
    $where[] = "av.statut = :statut";
    $params[':statut'] = $statut;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Compter le total
$total = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM avis av $whereSql");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Erreur count avis: " . $e->getMessage());
}

// Récupérer les avis
$rows = [];
try {
    $sql = "SELECT av.id_avis, av.commentaire, av.note, av.statut, av.date_avis,
                   u.prenom, u.nom, u.email,
                   an.titre as annonce_titre, an.id_annonce
            FROM avis av
            LEFT JOIN users u ON av.id_user = u.id_user
            LEFT JOIN annonces an ON av.id_annonce = an.id_annonce
            $whereSql
            ORDER BY av.$sort $dir
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
    error_log("Erreur fetch avis: " . $e->getMessage());
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
                ✅ Avis approuvé avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'rejected'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                ⚠️ Avis rejeté avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Avis supprimé avec succès !
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
                ❌ Avis introuvable.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif;
    endif;
    ?>
    
    <div class="row">
        <div class="col-12 mb-4">
            <h1>Modération des avis</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Avis</li>
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
                    SUM(CASE WHEN statut = 'rejete' THEN 1 ELSE 0 END) as rejete,
                    ROUND(AVG(note), 1) as note_moyenne
                FROM avis
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
                        <p class="mb-0">✅ Approuvés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h3>⭐ <?= $stats['note_moyenne'] ?>/5</h3>
                        <p class="mb-0">Note moyenne</p>
                    </div>
                </div>
            </div>
        <?php } catch (PDOException $e) {
            error_log("Erreur stats: " . $e->getMessage());
        } ?>
    </div>

    <!-- Filtres -->
    <form class="card mb-3" method="get" action="avis.php">
        <div class="card-body row g-2">
            <div class="col-md-4">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
                    <option value="approuve" <?= $statut === 'approuve' ? 'selected' : '' ?>>✅ Approuvés</option>
                    <option value="rejete" <?= $statut === 'rejete' ? 'selected' : '' ?>>❌ Rejetés</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <?php foreach ($allowSort as $s): ?>
                        <option value="<?= $s ?>" <?= $sort === $s ? 'selected' : '' ?>>
                            Trier par <?= $s === 'date_avis' ? 'Date' : 'Note' ?>
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
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filtrer</button>
            </div>
        </div>
    </form>

    <!-- Tableau des avis -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Annonce</th>
                        <th>Auteur</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucun avis trouvé.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($rows as $avis): ?>
                        <tr>
                            <td><?= (int)$avis['id_avis'] ?></td>
                            <td>
                                <small>
                                    <a href="annonce_view.php?id=<?= $avis['id_annonce'] ?>" target="_blank">
                                        <?= htmlspecialchars(substr($avis['annonce_titre'], 0, 30)) ?>...
                                    </a>
                                </small>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($avis['prenom'] . ' ' . $avis['nom']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    ⭐ <?= number_format($avis['note'], 1) ?>/5
                                </span>
                            </td>
                            <td>
                                <small><?= htmlspecialchars(substr($avis['commentaire'], 0, 50)) ?>...</small>
                            </td>
                            <td>
                                <?php
                                $badge = [
                                    'en_attente' => 'warning',
                                    'approuve' => 'success',
                                    'rejete' => 'danger'
                                ];
                                $label = [
                                    'en_attente' => '⏳ Attente',
                                    'approuve' => '✅ Approuvé',
                                    'rejete' => '❌ Rejeté'
                                ];
                                ?>
                                <span class="badge bg-<?= $badge[$avis['statut']] ?? 'secondary' ?>">
                                    <?= $label[$avis['statut']] ?? $avis['statut'] ?>
                                </span>
                            </td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($avis['date_avis'])) ?></small>
                            </td>
                            <td>
                                <a href="avis_view.php?id=<?= $avis['id_avis'] ?>" 
                                   class="btn btn-sm btn-info" title="Voir détails">
                                    👁️
                                </a>
                                
                                <?php if ($avis['statut'] === 'en_attente'): ?>
                                    <a href="avis_approve.php?id=<?= $avis['id_avis'] ?>" 
                                       class="btn btn-sm btn-success" title="Approuver"
                                       onclick="return confirm('Approuver cet avis ?')">
                                        ✓
                                    </a>
                                    <a href="avis_reject.php?id=<?= $avis['id_avis'] ?>" 
                                       class="btn btn-sm btn-warning" title="Rejeter"
                                       onclick="return confirm('Rejeter cet avis ?')">
                                        ✗
                                    </a>
                                <?php endif; ?>
                                
                                <a href="avis_delete.php?id=<?= $avis['id_avis'] ?>" 
                                   class="btn btn-sm btn-danger" title="Supprimer"
                                   onclick="return confirm('Supprimer définitivement cet avis ?')">
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