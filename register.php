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

$title = 'Inscription - Stars Doors';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide.';
    } else {
        // Récupération et nettoyage des données
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $telephone = trim($_POST['telephone'] ?? '');
        $date_naissance = $_POST['date_naissance'] ?? '';
        $id_race = filter_input(INPUT_POST, 'id_race', FILTER_VALIDATE_INT);
        $id_planete_residence = filter_input(INPUT_POST, 'id_planete_residence', FILTER_VALIDATE_INT);
        $role = $_POST['role'] ?? 'locataire';

        // Validation
        if (empty($nom)) {
            $errors[] = 'Le nom est requis.';
        } elseif (strlen($nom) < 2 || strlen($nom) > 80) {
            $errors[] = 'Le nom doit contenir entre 2 et 80 caractères.';
        }

        if (empty($prenom)) {
            $errors[] = 'Le prénom est requis.';
        } elseif (strlen($prenom) < 2 || strlen($prenom) > 80) {
            $errors[] = 'Le prénom doit contenir entre 2 et 80 caractères.';
        }

        if (empty($email)) {
            $errors[] = 'L\'email est requis.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Format d\'email invalide.';
        }

        if (empty($password)) {
            $errors[] = 'Le mot de passe est requis.';
        } elseif (!validatePassword($password)) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères avec au moins une majuscule, une minuscule et un chiffre.';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (empty($telephone)) {
            $errors[] = 'Le téléphone est requis.';
        } elseif (!validatePhone($telephone)) {
            $errors[] = 'Format de téléphone invalide.';
        }

        if (empty($date_naissance)) {
            $errors[] = 'La date de naissance est requise.';
        }

        if (!$id_race) {
            $errors[] = 'La race est requise.';
        } else {
            // Vérifier que la race existe
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM races WHERE id_race = ?");
                $stmt->execute([$id_race]);
                if ($stmt->fetchColumn() == 0) {
                    $errors[] = 'Race invalide.';
                }
            } catch (PDOException $e) {
                error_log("Erreur vérification race: " . $e->getMessage());
            }
        }

        if ($id_planete_residence) {
            // Vérifier que la planète existe
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM planetes WHERE id_planete = ?");
                $stmt->execute([$id_planete_residence]);
                if ($stmt->fetchColumn() == 0) {
                    $errors[] = 'Planète de résidence invalide.';
                }
            } catch (PDOException $e) {
                error_log("Erreur vérification planète: " . $e->getMessage());
            }
        }

        if (!in_array($role, ['locataire', 'proprietaire'])) {
            $errors[] = 'Rôle invalide.';
        }

        // Vérification de l'unicité de l'email
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $errors[] = 'Cet email est déjà utilisé.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de la vérification de l\'email.';
                error_log("Erreur vérification email: " . $e->getMessage());
            }
        }

        // Insertion en base
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, telephone, date_de_naissance, id_race, planete_residence, role)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email, $hashed_password, $telephone, $date_naissance, $id_race, $id_planete_residence, $role]);
                
                setFlashMessage('Inscription réussie ! Vous pouvez maintenant vous connecter.', 'success');
                redirect('login.php');
            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de l\'inscription. Veuillez réessayer.';
                // DEBUG: Afficher l'erreur complète en mode développement
                if (ENVIRONMENT === 'development') {
                    $errors[] = 'ERREUR SQL: ' . $e->getMessage();
                    $errors[] = 'CODE: ' . $e->getCode();
                }
                error_log("Erreur inscription: " . $e->getMessage());
            }
        }
    }
}

// Génération du token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Récupération des races pour le formulaire
try {
    $stmt = $pdo->query("SELECT id_race, nom, description, image_race FROM races ORDER BY nom ASC");
    $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $races = [];
    error_log("Erreur récupération races: " . $e->getMessage());
}

// Récupération des planètes pour le formulaire
try {
    $stmt = $pdo->query("SELECT id_planete, nom, galaxie, type_atmosphere, habitable_humains, habitable_aliens, image_planete
                        FROM planetes
                        WHERE statut = 'active'
                        ORDER BY nom ASC");
    $planetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $planetes = [];
    error_log("Erreur récupération planètes: " . $e->getMessage());
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="card-title text-center mb-4">Inscription</h2>
                    
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Min. 8 caractères avec majuscule, minuscule et chiffre</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_naissance" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control" id="date_naissance" name="date_naissance" 
                                       value="<?php echo htmlspecialchars($_POST['date_naissance'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="id_race" class="form-label">
                                <i class="fas fa-user-astronaut"></i> Race / Espèce
                            </label>
                            <select class="form-select" id="id_race" name="id_race" required>
                                <option value="">Choisissez votre race...</option>
                                <?php foreach ($races as $race): ?>
                                <option value="<?php echo $race['id_race']; ?>"
                                        <?php echo ($_POST['id_race'] ?? '') == $race['id_race'] ? 'selected' : ''; ?>
                                        data-description="<?php echo htmlspecialchars($race['description'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($race['nom']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text" id="race-description"></div>
                        </div>

                        <div class="mb-3">
                            <label for="id_planete_residence" class="form-label">
                                <i class="fas fa-globe"></i> Planète de résidence (optionnel)
                            </label>
                            <select class="form-select" id="id_planete_residence" name="id_planete_residence">
                                <option value="">Aucune pour l'instant</option>
                                <?php foreach ($planetes as $planete): ?>
                                <option value="<?php echo $planete['id_planete']; ?>"
                                        <?php echo ($_POST['id_planete_residence'] ?? '') == $planete['id_planete'] ? 'selected' : ''; ?>
                                        data-galaxie="<?php echo htmlspecialchars($planete['galaxie']); ?>"
                                        data-atmosphere="<?php echo htmlspecialchars($planete['type_atmosphere']); ?>">
                                    <?php echo htmlspecialchars($planete['nom']); ?>
                                    (<?php echo htmlspecialchars($planete['galaxie']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Où résidez-vous actuellement dans la galaxie ?</div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">
                                <i class="fas fa-briefcase"></i> Type de compte
                            </label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="locataire" <?php echo ($_POST['role'] ?? 'locataire') === 'locataire' ? 'selected' : ''; ?>>
                                    🧳 Locataire - Je cherche un logement
                                </option>
                                <option value="proprietaire" <?php echo ($_POST['role'] ?? '') === 'proprietaire' ? 'selected' : ''; ?>>
                                    🏠 Propriétaire - Je loue mes logements
                                </option>
                            </select>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Déjà un compte ? 
                            <a href="login.php">Se connecter</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Affichage dynamique de la description de la race
document.getElementById('id_race').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const description = selectedOption.getAttribute('data-description');
    const descDiv = document.getElementById('race-description');

    if (description) {
        descDiv.innerHTML = '<i class="fas fa-info-circle"></i> ' + description;
        descDiv.style.display = 'block';
    } else {
        descDiv.style.display = 'none';
    }
});

// Info planète au survol
document.getElementById('id_planete_residence').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const galaxie = selectedOption.getAttribute('data-galaxie');
        const atmosphere = selectedOption.getAttribute('data-atmosphere');
        console.log('Planète sélectionnée:', {galaxie, atmosphere});
    }
});
</script>

<?php include 'includes/footer.php'; ?>