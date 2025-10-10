<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';

// Vérifier que l'utilisateur est connecté et est propriétaire
requireProprietaire();

$title = 'Modifier une annonce - Stars Doors';
$errors = [];
$success = false;

// Récupérer l'ID de l'annonce
$id_annonce = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_annonce) {
    setFlashMessage('Annonce introuvable.', 'error');
    redirect('dashboard.php');
}

// Vérifier que l'annonce appartient bien à l'utilisateur connecté
try {
    $stmt = $pdo->prepare("SELECT * FROM annonces WHERE id_annonce = ? AND id_user = ?");
    $stmt->execute([$id_annonce, getCurrentUserId()]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$annonce) {
        setFlashMessage('Vous n\'avez pas accès à cette annonce.', 'error');
        redirect('dashboard.php');
    }
} catch (PDOException $e) {
    setFlashMessage('Erreur lors du chargement de l\'annonce.', 'error');
    redirect('dashboard.php');
}

// Récupération des planètes
try {
    $stmt = $pdo->query("SELECT id_planete, nom, galaxie, systeme_solaire, type_atmosphere,
                        gravite, temperature_moyenne, habitable_humains, habitable_aliens
                        FROM planetes
                        WHERE statut = 'active'
                        ORDER BY nom ASC");
    $planetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $planetes = [];
    error_log("Erreur récupération planètes: " . $e->getMessage());
}

// Récupérer les photos existantes
try {
    $stmt = $pdo->prepare("SELECT * FROM photo WHERE id_annonce = ? ORDER BY photo_principale DESC, ordre ASC");
    $stmt->execute([$id_annonce]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $photos = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide.';
    } else {
        // Récupération des données de base
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $id_planete = filter_input(INPUT_POST, 'id_planete', FILTER_VALIDATE_INT);
        $quartier = trim($_POST['quartier'] ?? '');
        $zone = trim($_POST['zone'] ?? '');
        $ville = trim($_POST['quartier'] ?? '');
        $pays = trim($_POST['zone'] ?? 'France');
        $adresse = trim($_POST['adresse'] ?? '');
        $code_postal = trim($_POST['code_postal'] ?? '');
        $vue_spatiale = $_POST['vue_spatiale'] ?? null;

        // Caractéristiques
        $type_logement = $_POST['type_logement'] ?? '';
        $prix_nuit = filter_input(INPUT_POST, 'prix_nuit', FILTER_VALIDATE_FLOAT);
        $nb_chambres = filter_input(INPUT_POST, 'nb_chambres', FILTER_VALIDATE_INT);
        $nb_lits = filter_input(INPUT_POST, 'nb_lits', FILTER_VALIDATE_INT);
        $nb_salles_bain = filter_input(INPUT_POST, 'nb_salles_bain', FILTER_VALIDATE_INT);
        $capacite_max = filter_input(INPUT_POST, 'capacite_max', FILTER_VALIDATE_INT);
        $surface = filter_input(INPUT_POST, 'surface', FILTER_VALIDATE_INT);
        $etage = filter_input(INPUT_POST, 'etage', FILTER_VALIDATE_INT);

        // Équipements classiques
        $wifi = isset($_POST['wifi']) ? 1 : 0;
        $parking = isset($_POST['parking']) ? 1 : 0;
        $climatisation = isset($_POST['climatisation']) ? 1 : 0;
        $lave_linge = isset($_POST['lave_linge']) ? 1 : 0;
        $television = isset($_POST['television']) ? 1 : 0;
        $animaux_acceptes = isset($_POST['animaux_acceptes']) ? 1 : 0;

        // Équipements spatiaux
        $generateur_gravite = isset($_POST['generateur_gravite']) ? 1 : 0;
        $dome_protection = isset($_POST['dome_protection']) ? 1 : 0;
        $systeme_traduction = isset($_POST['systeme_traduction']) ? 1 : 0;
        $capsule_transport = isset($_POST['capsule_transport']) ? 1 : 0;
        $baie_observation_spatiale = isset($_POST['baie_observation_spatiale']) ? 1 : 0;
        $recycleur_air = isset($_POST['recycleur_air']) ? 1 : 0;
        $regulateur_temperature = isset($_POST['regulateur_temperature']) ? 1 : 0;
        $bouclier_radiations = isset($_POST['bouclier_radiations']) ? 1 : 0;
        $communicateur_intergalactique = isset($_POST['communicateur_intergalactique']) ? 1 : 0;

        $type_d_air = $_POST['type_d_air'] ?? 'oxygene';
        $bouteille_air = $_POST['bouteille_air'] ?? 'oxygene';
        $regles_maison = trim($_POST['regles_maison'] ?? '');
        $instructions_checkin = trim($_POST['instructions_checkin'] ?? '');
        $disponible = isset($_POST['disponible']) ? 1 : 0;

        // Validation
        if (strlen($titre) < 10 || strlen($titre) > 100) {
            $errors[] = 'Le titre doit contenir entre 10 et 100 caractères.';
        }

        if (strlen($description) < 50 || strlen($description) > 2000) {
            $errors[] = 'La description doit contenir entre 50 et 2000 caractères.';
        }

        if (!$id_planete) {
            $errors[] = 'Veuillez sélectionner une planète.';
        }

        if (!in_array($type_logement, ['appartement', 'maison', 'studio', 'villa', 'chambre'])) {
            $errors[] = 'Type de logement invalide.';
        }

        if (!$prix_nuit || $prix_nuit <= 0) {
            $errors[] = 'Le prix par nuit doit être supérieur à 0.';
        }

        if ($nb_chambres === false || $nb_chambres < 0) {
            $errors[] = 'Le nombre de chambres est invalide.';
        }

        if (!$capacite_max || $capacite_max < 1) {
            $errors[] = 'La capacité maximale doit être d\'au moins 1 personne.';
        }

        // Mise à jour si pas d'erreurs
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE annonces SET
                    id_planete = ?, titre = ?, description = ?, ville = ?, pays = ?, quartier = ?, zone = ?,
                    adresse = ?, code_postal = ?, prix_nuit = ?, nb_chambres = ?, nb_lits = ?,
                    nb_salles_bain = ?, capacite_max = ?, type_logement = ?, surface = ?, etage = ?,
                    wifi = ?, parking = ?, climatisation = ?, lave_linge = ?, television = ?, animaux_acceptes = ?,
                    vue_spatiale = ?, generateur_gravite = ?, dome_protection = ?, systeme_traduction = ?,
                    capsule_transport = ?, baie_observation_spatiale = ?, recycleur_air = ?,
                    regulateur_temperature = ?, bouclier_radiations = ?, communicateur_intergalactique = ?,
                    type_d_air = ?, bouteille_air = ?, regles_maison = ?, instructions_checkin = ?,
                    disponible = ?, date_modification = NOW()
                    WHERE id_annonce = ? AND id_user = ?");

                $stmt->execute([
                    $id_planete, $titre, $description, $ville, $pays, $quartier, $zone,
                    $adresse, $code_postal, $prix_nuit, $nb_chambres, $nb_lits,
                    $nb_salles_bain, $capacite_max, $type_logement, $surface, $etage,
                    $wifi, $parking, $climatisation, $lave_linge, $television, $animaux_acceptes,
                    $vue_spatiale, $generateur_gravite, $dome_protection, $systeme_traduction,
                    $capsule_transport, $baie_observation_spatiale, $recycleur_air,
                    $regulateur_temperature, $bouclier_radiations, $communicateur_intergalactique,
                    $type_d_air, $bouteille_air, $regles_maison, $instructions_checkin,
                    $disponible, $id_annonce, getCurrentUserId()
                ]);

                // Traiter l'upload de nouvelles images (maximum 6 au total)
                if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                    $upload_dir = 'uploads/annonces/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Compter les images existantes
                    $stmt_count = $pdo->prepare("SELECT COUNT(*) as count FROM photo WHERE id_annonce = ?");
                    $stmt_count->execute([$id_annonce]);
                    $existing_count = $stmt_count->fetch(PDO::FETCH_ASSOC)['count'];

                    $max_images = 6;
                    $files_count = count($_FILES['photos']['name']);
                    
                    // Vérifier la limite totale
                    if ($existing_count + $files_count > $max_images) {
                        $errors[] = "Vous ne pouvez pas avoir plus de $max_images images au total. Vous avez déjà $existing_count images.";
                    } else {
                        // Récupérer l'ordre max actuel
                        $stmt_max = $pdo->prepare("SELECT MAX(ordre) as max_ordre FROM photo WHERE id_annonce = ?");
                        $stmt_max->execute([$id_annonce]);
                        $max_ordre = $stmt_max->fetch(PDO::FETCH_ASSOC)['max_ordre'] ?? 0;

                        $uploaded_count = $max_ordre + 1;
                        foreach ($_FILES['photos']['name'] as $key => $filename) {
                            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                                $tmp_name = $_FILES['photos']['tmp_name'][$key];
                                $file_size = $_FILES['photos']['size'][$key];

                                // Vérifier le type de fichier
                                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mime_type = finfo_file($finfo, $tmp_name);
                                finfo_close($finfo);

                                if (!in_array($mime_type, $allowed_types)) {
                                    continue;
                                }

                                // Vérifier la taille (max 5MB)
                                if ($file_size > 5 * 1024 * 1024) {
                                    continue;
                                }

                                // Redimensionner et optimiser l'image
                                $resize_result = resizeAndOptimizeImage($tmp_name, $filename);
                                if (!$resize_result['success']) {
                                    continue;
                                }

                                // Générer un nom unique (standardisé en JPG)
                                $new_filename = uniqid('img_' . $id_annonce . '_') . '.jpg';
                                $filepath = $upload_dir . $new_filename;

                                if (move_uploaded_file($resize_result['tmp_file'], $filepath)) {
                                    // Insérer dans la base (pas de photo principale si déjà existante)
                                    $stmt_photo = $pdo->prepare("INSERT INTO photo (id_annonce, nom_fichier, chemin, description, ordre, photo_principale) VALUES (?, ?, ?, ?, ?, 0)");
                                    $stmt_photo->execute([$id_annonce, $new_filename, $filepath, '', $uploaded_count]);
                                    $uploaded_count++;
                                }

                                // Nettoyer le fichier temporaire
                                if (file_exists($resize_result['tmp_file'])) {
                                    unlink($resize_result['tmp_file']);
                                }
                            }
                        }
                    }
                }

                $success = true;
                setFlashMessage('Votre annonce a été modifiée avec succès !', 'success');
                redirect("listing.php?id=$id_annonce");

            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de la modification de l\'annonce. ERREUR SQL: ' . $e->getMessage();
                error_log("Erreur modification annonce: " . $e->getMessage());
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
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4">
                <i class="fas fa-edit"></i> Modifier l'annonce
            </h1>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Informations de base -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations de base</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="titre" class="form-label">Titre de l'annonce *</label>
                            <input type="text" class="form-control" id="titre" name="titre"
                                   value="<?php echo htmlspecialchars($annonce['titre']); ?>"
                                   minlength="10" maxlength="100" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="5"
                                      minlength="50" maxlength="2000" required><?php echo htmlspecialchars($annonce['description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Localisation spatiale -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Localisation spatiale</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="id_planete" class="form-label">Planète *</label>
                            <select class="form-select" id="id_planete" name="id_planete" required>
                                <option value="">Choisissez une planète...</option>
                                <?php foreach ($planetes as $planete): ?>
                                <option value="<?php echo $planete['id_planete']; ?>"
                                        <?php echo $annonce['id_planete'] == $planete['id_planete'] ? 'selected' : ''; ?>>
                                    🌍 <?php echo htmlspecialchars($planete['nom']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="zone" class="form-label">Zone / Continent</label>
                                <input type="text" class="form-control" id="zone" name="zone"
                                       value="<?php echo htmlspecialchars($annonce['zone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quartier" class="form-label">Quartier / Ville</label>
                                <input type="text" class="form-control" id="quartier" name="quartier"
                                       value="<?php echo htmlspecialchars($annonce['quartier'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="adresse" name="adresse"
                                       value="<?php echo htmlspecialchars($annonce['adresse']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal"
                                       value="<?php echo htmlspecialchars($annonce['code_postal']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vue_spatiale" class="form-label">Vue depuis le logement</label>
                            <select class="form-select" id="vue_spatiale" name="vue_spatiale">
                                <option value="">Aucune vue particulière</option>
                                <option value="espace_profond" <?php echo $annonce['vue_spatiale'] == 'espace_profond' ? 'selected' : ''; ?>>🌌 Espace profond</option>
                                <option value="planete_voisine" <?php echo $annonce['vue_spatiale'] == 'planete_voisine' ? 'selected' : ''; ?>>🪐 Planète voisine</option>
                                <option value="lunes" <?php echo $annonce['vue_spatiale'] == 'lunes' ? 'selected' : ''; ?>>🌙 Lunes</option>
                                <option value="cratere" <?php echo $annonce['vue_spatiale'] == 'cratere' ? 'selected' : ''; ?>>🏔️ Cratère</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Caractéristiques -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-home"></i> Caractéristiques du logement</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type_logement" class="form-label">Type de logement *</label>
                                <select class="form-select" id="type_logement" name="type_logement" required>
                                    <option value="">Choisir...</option>
                                    <option value="appartement" <?php echo $annonce['type_logement'] == 'appartement' ? 'selected' : ''; ?>>Appartement</option>
                                    <option value="maison" <?php echo $annonce['type_logement'] == 'maison' ? 'selected' : ''; ?>>Maison</option>
                                    <option value="studio" <?php echo $annonce['type_logement'] == 'studio' ? 'selected' : ''; ?>>Studio</option>
                                    <option value="villa" <?php echo $annonce['type_logement'] == 'villa' ? 'selected' : ''; ?>>Villa</option>
                                    <option value="chambre" <?php echo $annonce['type_logement'] == 'chambre' ? 'selected' : ''; ?>>Chambre</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prix_nuit" class="form-label">Prix par nuit (€) *</label>
                                <input type="number" step="0.01" class="form-control" id="prix_nuit" name="prix_nuit"
                                       value="<?php echo $annonce['prix_nuit']; ?>" min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="nb_chambres" class="form-label">Chambres *</label>
                                <input type="number" class="form-control" id="nb_chambres" name="nb_chambres"
                                       value="<?php echo $annonce['nb_chambres']; ?>" min="0" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="nb_lits" class="form-label">Lits *</label>
                                <input type="number" class="form-control" id="nb_lits" name="nb_lits"
                                       value="<?php echo $annonce['nb_lits']; ?>" min="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="nb_salles_bain" class="form-label">Salles de bain *</label>
                                <input type="number" class="form-control" id="nb_salles_bain" name="nb_salles_bain"
                                       value="<?php echo $annonce['nb_salles_bain']; ?>" min="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="capacite_max" class="form-label">Capacité max *</label>
                                <input type="number" class="form-control" id="capacite_max" name="capacite_max"
                                       value="<?php echo $annonce['capacite_max']; ?>" min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="surface" class="form-label">Surface (m²)</label>
                                <input type="number" class="form-control" id="surface" name="surface"
                                       value="<?php echo $annonce['surface']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="etage" class="form-label">Étage</label>
                                <input type="number" class="form-control" id="etage" name="etage"
                                       value="<?php echo $annonce['etage']; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Équipements -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Équipements</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Équipements classiques</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="wifi" name="wifi" value="1" <?php echo $annonce['wifi'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="wifi">WiFi</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="parking" name="parking" value="1" <?php echo $annonce['parking'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="parking">Parking</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="climatisation" name="climatisation" value="1" <?php echo $annonce['climatisation'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="climatisation">Climatisation</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="lave_linge" name="lave_linge" value="1" <?php echo $annonce['lave_linge'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="lave_linge">Lave-linge</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="television" name="television" value="1" <?php echo $annonce['television'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="television">Télévision</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="animaux_acceptes" name="animaux_acceptes" value="1" <?php echo $annonce['animaux_acceptes'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="animaux_acceptes">Animaux acceptés</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3 text-primary">Équipements spatiaux</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="generateur_gravite" name="generateur_gravite" value="1" <?php echo ($annonce['generateur_gravite'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="generateur_gravite">Générateur de gravité</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dome_protection" name="dome_protection" value="1" <?php echo ($annonce['dome_protection'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="dome_protection">Dôme de protection</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="systeme_traduction" name="systeme_traduction" value="1" <?php echo ($annonce['systeme_traduction'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="systeme_traduction">Traducteur universel</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos existantes -->
                <?php if (!empty($photos)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-images"></i> Photos actuelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php foreach ($photos as $photo): ?>
                            <div class="col-md-3">
                                <div class="position-relative">
                                    <img src="<?php echo htmlspecialchars($photo['chemin']); ?>" class="img-fluid rounded" alt="Photo">
                                    <?php if ($photo['photo_principale']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-2">Principale</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Ajouter de nouvelles photos -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-camera"></i> Ajouter de nouvelles photos</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="photos" class="form-label">Télécharger des photos</label>
                            <input type="file" class="form-control" id="photos" name="photos[]"
                                   accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
                            <div class="form-text">Formats acceptés : JPG, PNG, WEBP. Taille max : 5 MB par image.</div>
                        </div>
                        <div id="photo-preview" class="row g-2"></div>
                    </div>
                </div>

                <!-- Disponibilité -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-toggle-on"></i> Disponibilité</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="disponible" name="disponible" value="1" <?php echo $annonce['disponible'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="disponible">
                                Annonce disponible à la réservation
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
                    <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
// Preview des nouvelles images
document.getElementById('photos').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('photo-preview');
    previewContainer.innerHTML = '';

    const files = Array.from(e.target.files);
    files.forEach((file) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-3';
                col.innerHTML = `
                    <div class="position-relative">
                        <img src="${event.target.result}" class="img-fluid rounded" alt="Preview">
                    </div>
                `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
