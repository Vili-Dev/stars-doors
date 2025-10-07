<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/validation.php';
require_once 'includes/functions.php';

requireLogin();

$title = 'Mon profil - Stars Doors';
$errors = [];
$success = false;

// Récupération des données utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        redirect('logout.php');
    }
} catch (PDOException $e) {
    error_log("Erreur récupération profil: " . $e->getMessage());
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            // Mise à jour des informations personnelles
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $date_naissance = $_POST['date_naissance'] ?? '';
            $race = trim($_POST['race'] ?? '');

            // Validation
            if (empty($nom) || strlen($nom) < 2 || strlen($nom) > 80) {
                $errors[] = 'Le nom doit contenir entre 2 et 80 caractères.';
            }
            if (empty($prenom) || strlen($prenom) < 2 || strlen($prenom) > 80) {
                $errors[] = 'Le prénom doit contenir entre 2 et 80 caractères.';
            }
            if (empty($telephone) || !validatePhone($telephone)) {
                $errors[] = 'Format de téléphone invalide.';
            }
            if (empty($date_naissance)) {
                $errors[] = 'La date de naissance est requise.';
            }
            if (empty($race)) {
                $errors[] = 'La race est requise.';
            }

            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET nom = ?, prenom = ?, telephone = ?, date_de_naissance = ?, race = ? 
                                          WHERE id_user = ?");
                    $stmt->execute([$nom, $prenom, $telephone, $date_naissance, $race, $_SESSION['user_id']]);
                    
                    // Mise à jour de la session
                    $_SESSION['user_name'] = $prenom . ' ' . $nom;
                    
                    $success = true;
                    setFlashMessage('Profil mis à jour avec succès !', 'success');
                    
                    // Actualisation des données
                    $user['nom'] = $nom;
                    $user['prenom'] = $prenom;
                    $user['telephone'] = $telephone;
                    $user['date_de_naissance'] = $date_naissance;
                    $user['race'] = $race;
                    
                } catch (PDOException $e) {
                    $errors[] = 'Erreur lors de la mise à jour. Veuillez réessayer.';
                    error_log("Erreur mise à jour profil: " . $e->getMessage());
                }
            }
        } elseif ($action === 'change_password') {
            // Changement de mot de passe
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($current_password)) {
                $errors[] = 'Le mot de passe actuel est requis.';
            } elseif (!password_verify($current_password, $user['mot_de_passe'])) {
                $errors[] = 'Le mot de passe actuel est incorrect.';
            }

            if (empty($new_password)) {
                $errors[] = 'Le nouveau mot de passe est requis.';
            } elseif (!validatePassword($new_password)) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères avec au moins une majuscule, une minuscule et un chiffre.';
            }

            if ($new_password !== $confirm_password) {
                $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id_user = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    
                    $success = true;
                    setFlashMessage('Mot de passe modifié avec succès !', 'success');
                } catch (PDOException $e) {
                    $errors[] = 'Erreur lors du changement de mot de passe.';
                    error_log("Erreur changement mot de passe: " . $e->getMessage());
                }
            }
        }
    }
}

// Génération token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1>Mon profil</h1>
            
            <?php displayFlashMessages(); ?>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Informations personnelles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <div class="form-text">L'email ne peut pas être modifié.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       value="<?php echo htmlspecialchars($user['telephone']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control" id="date_naissance" name="date_naissance" 
                                       value="<?php echo htmlspecialchars($user['date_de_naissance']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="race" class="form-label">Race</label>
                                <input type="text" class="form-control" id="race" name="race" 
                                       value="<?php echo htmlspecialchars($user['race']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <input type="text" class="form-control" id="role" 
                                       value="<?php echo ucfirst($user['role']); ?>" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                Membre depuis le <?php echo date('d/m/Y', strtotime($user['date_inscription'])); ?>
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </form>
                </div>
            </div>

            <!-- Changement de mot de passe -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Changer le mot de passe</h5>
                </div>
                <div class="card-body">
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Min. 8 caractères avec majuscule, minuscule et chiffre</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">Changer le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>