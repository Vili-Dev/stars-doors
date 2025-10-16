<?php

session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

// CSRF helper fallback si besoin
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES).'">';
    }
    function verify_csrf(): void {
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Jeton CSRF invalide.');
        }
    }
}

$title = 'Gestion des utilisateurs - Administration';

// --- Params (filtres/tri/pagination) ---
$role   = $_GET['role'] ?? '';
$actif  = $_GET['actif'] ?? '';
$sort   = $_GET['sort'] ?? 'created_at';
$dir    = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = max(5, min(50, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

$allowSort = ['created_at','updated_at','email','nom','prenom','derniere_connexion'];
if (!in_array($sort, $allowSort, true)) $sort = 'created_at';

// --- Build WHERE (sans recherche texte) ---
$where = [];
$params = [];

if (in_array($role, ['locataire','proprietaire','admin'], true)) {
    $where[] = "role = :role";
    $params[':role'] = $role;
}
if ($actif !== '') {
    if ($actif === '1' || $actif === '0') {
        $where[] = "actif = :actif";
        $params[':actif'] = (int)$actif;
    }
}
$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

// --- Count total ---
$total = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSql");
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("users count error: ".$e->getMessage());
}

// --- Fetch page ---
$rows = [];
try {
    $sql = "SELECT id_user, nom, prenom, email, telephone, role, actif, email_verifie,
                   derniere_connexion, created_at, updated_at, langue_preferee, niveau_fidelite, total_points_fidelite
            FROM users
            $whereSql
            ORDER BY $sort $dir
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k=>$v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("users page error: ".$e->getMessage());
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
        if ($_GET['success'] === '1'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Utilisateur enregistré avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ✅ Utilisateur supprimé avec succès !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif;
    endif;
    
    // Messages d'erreur
    if (isset($_GET['error'])):
        if ($_GET['error'] === '1'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ Erreur lors de l'enregistrement.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'delete_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ Erreur lors de la suppression.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'cannot_delete_self'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                ⚠️ Vous ne pouvez pas supprimer votre propre compte !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'user_not_found'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ❌ Utilisateur introuvable.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif;
    endif;
    ?>
    
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Gestion des utilisateurs</h1>
                <a href="user_edit.php" class="btn btn-success">+ Nouvel utilisateur</a>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Utilisateurs</li>
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
                    SUM(CASE WHEN role = 'locataire' THEN 1 ELSE 0 END) as locataires,
                    SUM(CASE WHEN role = 'proprietaire' THEN 1 ELSE 0 END) as proprietaires,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN actif = 1 THEN 1 ELSE 0 END) as actifs,
                    SUM(CASE WHEN email_verifie = 1 THEN 1 ELSE 0 END) as emails_verifies,
                    SUM(CASE WHEN DATE(derniere_connexion) >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) THEN 1 ELSE 0 END) as actifs_7j
                FROM users
            ")->fetch(PDO::FETCH_ASSOC);
        ?>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= $stats['total'] ?></h3>
                        <p class="mb-0">Total utilisateurs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h3><?= $stats['actifs'] ?></h3>
                        <p class="mb-0">✅ Actifs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h3><?= $stats['locataires'] ?></h3>
                        <p class="mb-0">🏠 Locataires</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <h3><?= $stats['actifs_7j'] ?></h3>
                        <p class="mb-0">📊 Actifs (7j)</p>
                    </div>
                </div>
            </div>
        <?php } catch (PDOException $e) {
            error_log("Erreur stats utilisateurs: " . $e->getMessage());
        } ?>
    </div>

    <!-- Filtres -->
    <form class="card mb-3" method="get" action="users.php">
        <div class="card-body row g-2">
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value>Tous les rôles</option>
                    <?php foreach (['locataire', 'proprietaire', 'admin'] as $r): ?>
                        <option value="<?= $r?>" <?= $role===$r ? 'selected' :''?>> <?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="actif" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="1" <?= $actif==='1' ? 'selected' :''?>>Actifs</option>
                    <option value="0" <?= $actif==='0' ? 'selected' :''?>>Inactifs</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="sort" class="form-select">
                    <?php foreach ($allowSort as $s): ?>
                        <option value="<?= $s?>" <?= $sort===$s ? 'selected' :''?>>Trier par <?= str_replace('_', ' ', ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                </select>   
            </div>
            <div class="col-md-3">
                <select name="dir" class="form-select">
                    <option value="desc" <?= $dir==='DESC' ? 'selected' :''?>>Ordre décroissant</option>
                    <option value="asc" <?= $dir==='ASC' ? 'selected' :''?>>Ordre croissant</option>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button class="btn btn-primary">Filtrer</button>
            </div>
        </div>
    </form>

    <!-- Tableau des utilisateurs -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="7" class="text-center">Aucun utilisateur trouvé.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $u): ?>
                        <tr>
                            <td><?= (int)$u['id_user'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($u['email']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($u['role']) ?></div>
                            </td>
                            <td>
                                <?php if ((int)$u["actif"] === 1): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($u['derniere_connexion'])): ?>
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($u['derniere_connexion']))) ?>
                                <?php else: ?>-
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="user_edit.php?id=<?= $u['id_user'] ?>" class="btn btn-sm btn-primary" title="Modifier">
                                    ✏️ Modifier
                                </a>
                                <a href="user_delete.php?id=<?= $u['id_user'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Supprimer"
                                   onclick="return confirm('Voulez-vous vraiment supprimer <?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?> ?')">
                                    🗑️ Supprimer
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
                               href="?page=<?= $i ?>&limit=<?= $limit ?>&sort=<?= $sort ?>&dir=<?= strtolower($dir) ?>&role=<?= $role ?>&actif=<?= $actif ?>">
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