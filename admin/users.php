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
    <div class="row">
        <div class="col-12 mb-4">
            <h1>Gestion des utilisateurs</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Utilisateurs</li>
                </ol>
            </nav>
        </div>
    </div>
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
    <div class="card">
        <div class="table-reponsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
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
                                <div class="tw-semibold"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></div>
                            </td>
                            <td>
                                <div class="tw-semibold"><?= htmlspecialchars($u['email']) ?></div>
                            </td>
                            <td>
                                <div class="tw-semibold"><?= htmlspecialchars($u['role']) ?></div>
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
                        </tr>  
                    <?php endforeach; ?> 
                </tbody>
            </table>   
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>