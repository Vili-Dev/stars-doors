<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/validation.php';
require_once 'includes/functions.php';

// Redirection si déjà connecté
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$title = 'Connexion - Stars Doors';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide.';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($email)) {
            $errors[] = 'L\'email est requis.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Format d\'email invalide.';
        }

        if (empty($password)) {
            $errors[] = 'Le mot de passe est requis.';
        }

        // Tentative de connexion
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT id_user, email, mot_de_passe, role, actif, prenom, nom 
                                      FROM users 
                                      WHERE email = ? AND actif = 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['mot_de_passe'])) {
                    // Connexion réussie

                    // Mise à jour de la dernière connexion
    try {
        $updateStmt = $pdo->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id_user = :id");
        $updateStmt->execute([':id' => $user['id_user']]);
    } catch (PDOException $e) {
        error_log("Erreur mise à jour derniere_connexion: " . $e->getMessage());
    }
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                    
                    // Régénération de l'ID de session
                    session_regenerate_id(true);
                    
                    setFlashMessage('Connexion réussie !', 'success');
                    
                    // Redirection selon le rôle
                    $redirect = $_GET['redirect'] ?? 'dashboard.php';
                    if ($user['role'] === 'admin' && !isset($_GET['redirect'])) {
                        $redirect = 'admin/index.php';
                    }
                    redirect($redirect);
                } else {
                    $errors[] = 'Email ou mot de passe incorrect.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de la connexion. Veuillez réessayer.';
                error_log("Erreur de connexion: " . $e->getMessage());
            }
        }
    }
}

// Génération du token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">Connexion</h2>
                    
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

                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Pas encore de compte ? 
                            <a href="register.php">Créer un compte</a>
                        </p>
                        <p class="mb-0">Mot de passe oublié? 
                            <a href="reset_password/forgot_password.php">Réinitialiser</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
