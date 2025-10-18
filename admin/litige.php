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

$title = 'Modération des litiges - Administration';

// Paramètres de filtrage
$statut = $_GET['statut'] ?? '';
$sort = $_GET['sort'] ?? 'date_creation';
$dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(5, min(50, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$allowSort = ['date_creation', 'statut', 'sujet'];
if (!in_array($sort, $allowSort, true)) $sort = 'date_creation';

// Construction WHERE
$where = [];
$params = [];

if (in_array($statut, ['en_attente', 'en_cours', 'resolu', 'rejete'], true)) {
    $where[] = "l.statut = :statut";
    $params[':statut'] = $statut;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Compter le total
$total = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM litiges l $whereSql");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Erreur count litige: " . $e->getMessage());
}

// Récupérer les litiges
$rows = [];
try {
    $sql = "SELECT l.id_litige, l.id_annonce, l.id_reservation, l.id_user, l.statut, l.date_creation, l.sujet, l.description,
    u.prenom, u.nom, u.email,
    a.titre AS annonce_titre,
    r.id_reservation , r.date_debut, r.date_fin
            FROM litiges l
            LEFT JOIN users u ON l.id_user = u.id_user
            LEFT JOIN annonces a ON l.id_annonce = a.id_annonce
            LEFT JOIN reservations r ON l.id_reservation = r.id_reservation
            $whereSql
            ORDER BY l.$sort $dir
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
    error_log("Erreur fetch litige: " . $e->getMessage());
    echo '<pre style="color:red;">Erreur SQL : ' . htmlspecialchars($e->getMessage()) . '</pre>';
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
    <div class="row">
        <div class="col-12 mb-4">
            <h1>Gestion des litiges</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Litiges</li>
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
                    SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
                    SUM(CASE WHEN statut = 'resolu' THEN 1 ELSE 0 END) as resolu,
                    SUM(CASE WHEN statut = 'rejete' THEN 1 ELSE 0 END) as rejete
                FROM litiges
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
                        <p class="mb-0"><i class="bi bi-hourglass-split"></i> En attente</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h3><?= $stats['en_cours'] ?></h3>
                        <p class="mb-0"><i class="bi bi-arrow-repeat"></i> En cours</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h3><?= $stats['resolu'] ?></h3>
                        <p class="mb-0"><i class="bi bi-check2"></i> Résolu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center bg-danger text-white">
                    <div class="card-body">
                        <h3><?= $stats['rejete'] ?></h3>
                        <p class="mb-0"><i class="bi bi-x-circle-fill"></i> Rejeté</p>
                    </div>
                </div>
            </div>
        <?php } catch (PDOException $e) {
            error_log("Erreur stats: " . $e->getMessage());
        } ?>
    </div>

    <!-- Filtres -->
    <form class="card mb-3" method="get" action="litige.php">
        <div class="card-body row g-2">
            <div class="col-md-4">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
                    <option value="en_cours" <?= $statut === 'en_cours' ? 'selected' : '' ?>>🔄 En cours</option>
                    <option value="resolu" <?= $statut === 'resolu' ? 'selected' : '' ?>>✅ Résolu</option>
                    <option value="rejete" <?= $statut === 'rejete' ? 'selected' : '' ?>>❌ Rejeté</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <?php foreach ($allowSort as $s): ?>
                        <option value="<?= $s ?>" <?= $sort === $s ? 'selected' : '' ?>>
                            Trier par <?= str_replace('_',' ',ucfirst($s))?>
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

    <!-- Tableau des litiges -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Annonce</th>
                        <th>Réservation</th>
                        <th>Utilisateur</th>
                        <th>Sujet</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucun litige trouvé.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($rows as $litige): ?>
                        <tr>
                            <td><?= (int)$litige['id_litige'] ?></td>
                            <td><?= htmlspecialchars(substr($litige['annonce_titre'], 0, 30)) ?>...</td>
                            <td><?= htmlspecialchars($litige['id_reservation']) ?></td>
                            <td><?= htmlspecialchars($litige['prenom'].' '.$litige['nom']) ?></td>
                            <td><?= htmlspecialchars($litige['sujet']) ?></td>
                            <td>
                                <?php
                                $badge = [
                                    'en_attente' => 'warning',
                                    'en_cours' => 'info',
                                    'resolu' => 'success',
                                    'rejete' => 'danger'
                                ];
                                $label = [
                                    'en_attente' => '<i class="bi bi-hourglass-split"></i> En attente',
                                    'en_cours' => '<i class="bi bi-arrow-repeat"></i> En cours',
                                    'resolu' => '<i class="bi bi-check-circle"></i> Résolu',
                                    'rejete' => '<i class="bi bi-x-circle-fill"></i> Rejeté'
                                ];
                                ?>
                                <span class="badge bg-<?= $badge[$litige['statut']] ?? 'secondary' ?>">
                                    <?= $label[$litige['statut']] ?? $litige['statut'] ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($litige['date_creation'])) ?></td>
                            <td>
                                <a href="litige_detail.php?id=<?= $litige['id_litige'] ?>" 
                                   class="btn btn-sm btn-info d-inline-flex align-items-center" 
                                   title="Voir détails"> <i class="bi bi-eye me-1"></i>
                                     Voir
                                </a>
                                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link"
                               href="?page=<?= $i ?>&limit=<?= $limit ?>&sort=<?= $sort ?>&dir=<?= strtolower($dir) ?>&statut=<?= $statut ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>