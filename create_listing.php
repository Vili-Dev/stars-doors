<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';

// Vérifier que l'utilisateur est connecté et est propriétaire
requireProprietaire();

$title = 'Créer une annonce - Stars Doors';
$errors = [];
$success = false;

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
        $ville = trim($_POST['quartier'] ?? ''); // Pour compatibilité
        $pays = trim($_POST['zone'] ?? 'France'); // Pour compatibilité
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

        if (!$nb_chambres || $nb_chambres < 0) {
            $errors[] = 'Le nombre de chambres est invalide.';
        }

        if (!$capacite_max || $capacite_max < 1) {
            $errors[] = 'La capacité maximale doit être d\'au moins 1 personne.';
        }

        // Insertion si pas d'erreurs
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO annonces (
                    id_user, id_planete, titre, description, ville, pays, quartier, zone, adresse, code_postal,
                    prix_nuit, nb_chambres, nb_lits, nb_salles_bain, capacite_max,
                    type_logement, surface, etage,
                    wifi, parking, climatisation, lave_linge, television, animaux_acceptes,
                    vue_spatiale,
                    generateur_gravite, dome_protection, systeme_traduction, capsule_transport,
                    baie_observation_spatiale, recycleur_air, regulateur_temperature,
                    bouclier_radiations, communicateur_intergalactique,
                    type_d_air, bouteille_air,
                    regles_maison, instructions_checkin, disponible
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

                $stmt->execute([
                    getCurrentUserId(), $id_planete, $titre, $description, $ville, $pays, $quartier, $zone,
                    $adresse, $code_postal, $prix_nuit, $nb_chambres, $nb_lits,
                    $nb_salles_bain, $capacite_max, $type_logement, $surface, $etage,
                    $wifi, $parking, $climatisation, $lave_linge, $television, $animaux_acceptes,
                    $vue_spatiale,
                    $generateur_gravite, $dome_protection, $systeme_traduction, $capsule_transport,
                    $baie_observation_spatiale, $recycleur_air, $regulateur_temperature,
                    $bouclier_radiations, $communicateur_intergalactique,
                    $type_d_air, $bouteille_air,
                    $regles_maison, $instructions_checkin
                ]);

                $annonce_id = $pdo->lastInsertId();

                // Traiter l'upload des images
                $upload_errors = [];
                if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
                    $upload_dir = 'uploads/annonces/';
                    if (!is_dir($upload_dir)) {
                        if (!mkdir($upload_dir, 0777, true)) {
                            $upload_errors[] = "Impossible de créer le dossier: " . $upload_dir;
                        }
                    }

                    $uploaded_count = 0;
                    foreach ($_FILES['photos']['name'] as $key => $filename) {
                        $upload_errors[] = "Fichier: $filename, Erreur: " . $_FILES['photos']['error'][$key];

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

                            // Vérifier la taille (max 2MB)
                            if ($file_size > 2 * 1024 * 1024) {
                                error_log("Fichier trop gros: $filename - " . $file_size . " bytes");
                                continue;
                            }

                            // Générer un nom unique
                            $extension = pathinfo($filename, PATHINFO_EXTENSION);
                            $new_filename = uniqid('img_' . $annonce_id . '_') . '.' . $extension;
                            $filepath = $upload_dir . $new_filename;

                            if (move_uploaded_file($tmp_name, $filepath)) {
                                // Insérer dans la base
                                $photo_principale = ($uploaded_count === 0) ? 1 : 0;
                                $stmt_photo = $pdo->prepare("INSERT INTO photo (id_annonce, nom_fichier, chemin, description, ordre, photo_principale) VALUES (?, ?, ?, ?, ?, ?)");
                                $stmt_photo->execute([$annonce_id, $new_filename, $filepath, '', $uploaded_count, $photo_principale]);
                                $uploaded_count++;
                                $upload_errors[] = "SUCCESS: Image uploadée $filepath";
                            } else {
                                $upload_errors[] = "ERREUR: Échec de move_uploaded_file de $tmp_name vers $filepath";
                            }
                        } else {
                            $upload_errors[] = "SKIP: Type mime invalide ou fichier trop gros pour $filename";
                        }
                    }
                } else {
                    $upload_errors[] = "Aucune photo uploadée ou tableau vide";
                }

                // Afficher les erreurs d'upload en debug
                if (!empty($upload_errors)) {
                    error_log("DEBUG UPLOAD: " . implode(" | ", $upload_errors));
                    $_SESSION['upload_debug'] = $upload_errors;
                }

                $success = true;
                setFlashMessage('Votre annonce a été créée avec succès !', 'success');
                // Rediriger vers la page de l'annonce ou le dashboard
                redirect("listing.php?id=$annonce_id");

            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de la création de l\'annonce. ERREUR SQL: ' . $e->getMessage() . ' CODE: ' . $e->getCode();
                error_log("Erreur création annonce: " . $e->getMessage());
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
                <i class="fas fa-rocket"></i> Créer une nouvelle annonce spatiale
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
                                   placeholder="Ex: Appartement luxueux avec vue sur les anneaux de Saturne"
                                   value="<?php echo htmlspecialchars($_POST['titre'] ?? ''); ?>"
                                   minlength="10" maxlength="100" required>
                            <div class="form-text">Entre 10 et 100 caractères</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="5"
                                      placeholder="Décrivez votre logement en détail..."
                                      minlength="50" maxlength="2000" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="form-text">Entre 50 et 2000 caractères</div>
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
                                        <?php echo ($_POST['id_planete'] ?? '') == $planete['id_planete'] ? 'selected' : ''; ?>
                                        data-atmosphere="<?php echo htmlspecialchars($planete['type_atmosphere']); ?>"
                                        data-gravite="<?php echo $planete['gravite']; ?>">
                                    🌍 <?php echo htmlspecialchars($planete['nom']); ?>
                                    - <?php echo htmlspecialchars($planete['galaxie']); ?>
                                    (<?php echo htmlspecialchars($planete['type_atmosphere']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="zone" class="form-label">Zone / Continent</label>
                                <input type="text" class="form-control" id="zone" name="zone"
                                       placeholder="Ex: Hémisphère Nord, Cratère Central"
                                       value="<?php echo htmlspecialchars($_POST['zone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quartier" class="form-label">Quartier / Ville</label>
                                <input type="text" class="form-control" id="quartier" name="quartier"
                                       placeholder="Ex: District Nova, Colonie Alpha"
                                       value="<?php echo htmlspecialchars($_POST['quartier'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="adresse" name="adresse"
                                       placeholder="Ex: 42 Avenue des Étoiles, Dôme B-7"
                                       value="<?php echo htmlspecialchars($_POST['adresse'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="code_postal" name="code_postal"
                                       placeholder="Ex: GX-2154"
                                       value="<?php echo htmlspecialchars($_POST['code_postal'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vue_spatiale" class="form-label">Vue depuis le logement</label>
                            <select class="form-select" id="vue_spatiale" name="vue_spatiale">
                                <option value="">Aucune vue particulière</option>
                                <option value="espace_profond">🌌 Espace profond</option>
                                <option value="planete_voisine">🪐 Planète voisine</option>
                                <option value="lunes">🌙 Lunes</option>
                                <option value="cratere">🏔️ Cratère</option>
                                <option value="ocean_alien">🌊 Océan alien</option>
                                <option value="foret_bioluminescente">🌲 Forêt bioluminescente</option>
                                <option value="ville_futuriste">🏙️ Ville futuriste</option>
                                <option value="desert_cristallin">🏜️ Désert cristallin</option>
                                <option value="aurores">✨ Aurores</option>
                                <option value="nebuleuse">☁️ Nébuleuse</option>
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
                                    <option value="appartement">Appartement</option>
                                    <option value="maison">Maison</option>
                                    <option value="studio">Studio</option>
                                    <option value="villa">Villa</option>
                                    <option value="chambre">Chambre</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prix_nuit" class="form-label">Prix par nuit (€) *</label>
                                <input type="number" step="0.01" class="form-control" id="prix_nuit" name="prix_nuit"
                                       value="<?php echo htmlspecialchars($_POST['prix_nuit'] ?? ''); ?>"
                                       min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="nb_chambres" class="form-label">Chambres *</label>
                                <input type="number" class="form-control" id="nb_chambres" name="nb_chambres"
                                       value="<?php echo htmlspecialchars($_POST['nb_chambres'] ?? '1'); ?>"
                                       min="0" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="nb_lits" class="form-label">Lits *</label>
                                <input type="number" class="form-control" id="nb_lits" name="nb_lits"
                                       value="<?php echo htmlspecialchars($_POST['nb_lits'] ?? '1'); ?>"
                                       min="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="nb_salles_bain" class="form-label">Salles de bain *</label>
                                <input type="number" class="form-control" id="nb_salles_bain" name="nb_salles_bain"
                                       value="<?php echo htmlspecialchars($_POST['nb_salles_bain'] ?? '1'); ?>"
                                       min="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="capacite_max" class="form-label">Capacité max *</label>
                                <input type="number" class="form-control" id="capacite_max" name="capacite_max"
                                       value="<?php echo htmlspecialchars($_POST['capacite_max'] ?? '2'); ?>"
                                       min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="surface" class="form-label">Surface (m²)</label>
                                <input type="number" class="form-control" id="surface" name="surface"
                                       value="<?php echo htmlspecialchars($_POST['surface'] ?? ''); ?>" min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="etage" class="form-label">Étage</label>
                                <input type="number" class="form-control" id="etage" name="etage"
                                       value="<?php echo htmlspecialchars($_POST['etage'] ?? ''); ?>">
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
                                    <input class="form-check-input" type="checkbox" id="wifi" name="wifi" value="1">
                                    <label class="form-check-label" for="wifi">
                                        <i class="fas fa-wifi"></i> WiFi intergalactique
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="parking" name="parking" value="1">
                                    <label class="form-check-label" for="parking">
                                        <i class="fas fa-car"></i> Parking vaisseau
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="climatisation" name="climatisation" value="1">
                                    <label class="form-check-label" for="climatisation">
                                        <i class="fas fa-snowflake"></i> Climatisation
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="lave_linge" name="lave_linge" value="1">
                                    <label class="form-check-label" for="lave_linge">
                                        <i class="fas fa-tshirt"></i> Lave-linge
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="television" name="television" value="1">
                                    <label class="form-check-label" for="television">
                                        <i class="fas fa-tv"></i> HoloVision
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="animaux_acceptes" name="animaux_acceptes" value="1">
                                    <label class="form-check-label" for="animaux_acceptes">
                                        <i class="fas fa-paw"></i> Animaux/Créatures acceptés
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3 text-primary"><i class="fas fa-satellite"></i> Équipements spatiaux</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="generateur_gravite" name="generateur_gravite" value="1">
                                    <label class="form-check-label" for="generateur_gravite">
                                        🌍 Générateur de gravité artificielle
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dome_protection" name="dome_protection" value="1">
                                    <label class="form-check-label" for="dome_protection">
                                        🛡️ Dôme de protection
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="systeme_traduction" name="systeme_traduction" value="1" checked>
                                    <label class="form-check-label" for="systeme_traduction">
                                        🗣️ Traducteur universel
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="capsule_transport" name="capsule_transport" value="1">
                                    <label class="form-check-label" for="capsule_transport">
                                        🚀 Navette personnelle
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="baie_observation_spatiale" name="baie_observation_spatiale" value="1">
                                    <label class="form-check-label" for="baie_observation_spatiale">
                                        🔭 Baie d'observation spatiale
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="recycleur_air" name="recycleur_air" value="1" checked>
                                    <label class="form-check-label" for="recycleur_air">
                                        💨 Recycleur d'air
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="regulateur_temperature" name="regulateur_temperature" value="1" checked>
                                    <label class="form-check-label" for="regulateur_temperature">
                                        🌡️ Régulateur de température
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="bouclier_radiations" name="bouclier_radiations" value="1">
                                    <label class="form-check-label" for="bouclier_radiations">
                                        ☢️ Bouclier anti-radiations
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="communicateur_intergalactique" name="communicateur_intergalactique" value="1">
                                    <label class="form-check-label" for="communicateur_intergalactique">
                                        📡 Communicateur intergalactique
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Atmosphère et air -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-wind"></i> Atmosphère et respiration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type_d_air" class="form-label">Type d'air ambiant</label>
                                <select class="form-select" id="type_d_air" name="type_d_air">
                                    <option value="oxygene">Oxygène (humains, la plupart des espèces)</option>
                                    <option value="azote">Azote</option>
                                    <option value="helium">Hélium</option>
                                    <option value="methane">Méthane</option>
                                    <option value="mixte">Atmosphère mixte</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bouteille_air" class="form-label">Bouteilles d'air fournies</label>
                                <select class="form-select" id="bouteille_air" name="bouteille_air">
                                    <option value="oxygene">Oxygène</option>
                                    <option value="azote">Azote</option>
                                    <option value="helium">Hélium</option>
                                </select>
                                <div class="form-text">Pour les visiteurs nécessitant une autre atmosphère</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-camera"></i> Photos du logement</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="photos" class="form-label">Télécharger des photos *</label>
                            <input type="file" class="form-control" id="photos" name="photos[]"
                                   accept="image/jpeg,image/jpg,image/png,image/webp" multiple required>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i>
                                Formats acceptés : JPG, PNG, WEBP. Taille max : 2 MB par image.
                                La première image sera la photo principale.
                            </div>
                        </div>
                        <div id="photo-preview" class="row g-2"></div>
                    </div>
                </div>

                <!-- Règles et instructions -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list-check"></i> Règles et instructions</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="regles_maison" class="form-label">Règles de la maison</label>
                            <textarea class="form-control" id="regles_maison" name="regles_maison" rows="3"
                                      placeholder="Ex: Pas de fête, respecter les horaires de sommeil du quartier, neutralisation gravitationnelle interdite après 22h..."><?php echo htmlspecialchars($_POST['regles_maison'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="instructions_checkin" class="form-label">Instructions d'arrivée</label>
                            <textarea class="form-control" id="instructions_checkin" name="instructions_checkin" rows="3"
                                      placeholder="Ex: Coordonnées spatiales, code d'accès au sas, protocole de décontamination..."><?php echo htmlspecialchars($_POST['instructions_checkin'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Boutons de soumission -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
                    <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-check"></i> Créer l'annonce
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
// Auto-remplissage atmosphère selon la planète
document.getElementById('id_planete').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const atmosphere = selectedOption.getAttribute('data-atmosphere');

    if (atmosphere) {
        document.getElementById('type_d_air').value = atmosphere;
        document.getElementById('bouteille_air').value = atmosphere;
    }
});

// Preview des images
document.getElementById('photos').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('photo-preview');
    previewContainer.innerHTML = '';

    const files = Array.from(e.target.files);
    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-3';
                col.innerHTML = `
                    <div class="position-relative">
                        <img src="${event.target.result}" class="img-fluid rounded" alt="Preview">
                        ${index === 0 ? '<span class="badge bg-primary position-absolute top-0 start-0 m-2">Photo principale</span>' : ''}
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
