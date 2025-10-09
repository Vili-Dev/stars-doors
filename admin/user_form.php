<?php

if(!isset($_SESSION)) {
    session_start();
}
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

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

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$user = null;

if($id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = :id");
    $stmt->execute([':id' => $id]);  
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$user){
        http_response_code(404);
        exit('Utilisateur non trouvé.');
    }
}

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
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0"><?= $id ? 'Modifier un utilisateur' : 'Créer un utilisateur' ?></h1>
    </div>

    <form class="card" method="post" action="user_save.php">
        <div class="card-body row g-3">
            <?= csrf_field() ?>
            <?php if($id): ?>   
                <input type="hidden" name="id_user" value="<?= (int)$id ?>"><?php endif; ?>   

            <div class="col-md-6">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                 <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date de naissance</label>
                <input type="date" name="date_de_naissance" class="form-control" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($user['date_de_naissance'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <?php foreach(['locataire','proprietaire','admin'] as $role): ?>
                        <option value="<?= $role ?>" <?=(($user['role'] ?? '')=== $role)?'selected':''?>><?= ucfirst($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Mot de passe <?= $id ? '(laisser vide pour conserver le mot de passe actuel)' : '' ?></label>
                <input type="password" name="mot_de_passe" class="form-control"  <?= $id ? '' :'required' ?>>
            </div>
            <div class="col-md-6 form-check ms-2">
                <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1" <?= (isset($user['actif']) ? ((int)$user['actif']===1):true) ? 'checked' : ''?>>
                <label class="form-check-label" for="actif">
                    Actif
                </label>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                 <!-- <a href="users.php" class="btn btn-secondary">Retour</a> -->
                <button class="btn btn-primary" type="submit">Enregistrer</button>
            </div>
        </form>
</main>

<?php include '../includes/footer.php'; ?>