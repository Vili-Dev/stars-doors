<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$title = 'Recherche Intergalactique - Stars Doors';
$current_page = 'search';

// Paramètres de recherche
$planete_search = $_GET['planete'] ?? '';
$planete_id = filter_input(INPUT_GET, 'planete', FILTER_VALIDATE_INT);
$galaxie = $_GET['galaxie'] ?? '';
$atmosphere = $_GET['atmosphere'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$type_logement = $_GET['type_logement'] ?? '';
$prix_max = filter_input(INPUT_GET, 'prix_max', FILTER_VALIDATE_FLOAT);
$capacite = filter_input(INPUT_GET, 'capacite', FILTER_VALIDATE_INT);
$gravite_min = filter_input(INPUT_GET, 'gravite_min', FILTER_VALIDATE_FLOAT);
$gravite_max = filter_input(INPUT_GET, 'gravite_max', FILTER_VALIDATE_FLOAT);

// Équipements spatiaux
$generateur_gravite = isset($_GET['generateur_gravite']) ? 1 : 0;
$dome_protection = isset($_GET['dome_protection']) ? 1 : 0;
$baie_observation = isset($_GET['baie_observation']) ? 1 : 0;
$capsule_transport = isset($_GET['capsule_transport']) ? 1 : 0;

// Récupération des planètes pour le formulaire
try {
    $stmt = $pdo->query("SELECT id_planete, nom, galaxie FROM planetes WHERE statut = 'active' ORDER BY nom");
    $planetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $planetes = [];
}

// Récupération des galaxies
try {
    $stmt = $pdo->query("SELECT DISTINCT galaxie FROM planetes WHERE statut = 'active' ORDER BY galaxie");
    $galaxies = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $galaxies = [];
}

// Construction de la requête
$where_conditions = ['a.disponible = 1'];
$params = [];

if ($planete_id) {
    $where_conditions[] = 'a.id_planete = ?';
    $params[] = $planete_id;
} elseif (!empty($planete_search)) {
    $where_conditions[] = 'pl.nom LIKE ?';
    $params[] = '%' . $planete_search . '%';
}

if (!empty($galaxie)) {
    $where_conditions[] = 'pl.galaxie = ?';
    $params[] = $galaxie;
}

if (!empty($atmosphere)) {
    $where_conditions[] = 'pl.type_atmosphere = ?';
    $params[] = $atmosphere;
}

if (!empty($type_logement)) {
    $where_conditions[] = 'a.type_logement = ?';
    $params[] = $type_logement;
}

if ($prix_max) {
    $where_conditions[] = 'a.prix_nuit <= ?';
    $params[] = $prix_max;
}

if ($capacite) {
    $where_conditions[] = 'a.capacite_max >= ?';
    $params[] = $capacite;
}

if ($gravite_min) {
    $where_conditions[] = 'pl.gravite >= ?';
    $params[] = $gravite_min;
}

if ($gravite_max) {
    $where_conditions[] = 'pl.gravite <= ?';
    $params[] = $gravite_max;
}

if ($generateur_gravite) {
    $where_conditions[] = 'a.generateur_gravite = 1';
}

if ($dome_protection) {
    $where_conditions[] = 'a.dome_protection = 1';
}

if ($baie_observation) {
    $where_conditions[] = 'a.baie_observation_spatiale = 1';
}

if ($capsule_transport) {
    $where_conditions[] = 'a.capsule_transport = 1';
}

$where_clause = implode(' AND ', $where_conditions);

try {
    $sql = "SELECT a.*, u.prenom, u.nom,
            pl.nom as planete_nom, pl.galaxie, pl.type_atmosphere, pl.gravite,
            p.chemin as photo_chemin
            FROM annonces a
            LEFT JOIN users u ON a.id_user = u.id_user
            LEFT JOIN planetes pl ON a.id_planete = pl.id_planete
            LEFT JOIN photo p ON a.id_annonce = p.id_annonce AND p.photo_principale = 1
            WHERE $where_clause
            ORDER BY a.note_moyenne DESC, a.date_creation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $listings = [];
    error_log("Erreur recherche: " . $e->getMessage());
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main>
    <!-- Remplacer hero-section par search-hero -->
    <section class="search-hero bg-dark text-white py-4">
        <div class="container">
            <h1>Recherche Intergalactique</h1>
            <p class="lead">Trouvez votre logement parfait parmi les étoiles</p>
        </div>
    </section>

    <!-- Ajouter la section filtres -->
    <div class="search-filters container my-4">
        <form action="" method="GET">
            <div class="row g-3">
                <!-- Localisation -->
                <div class="col-12">
                    <h6 class="text-primary">
                        <i class="fas fa-map-marker-alt"></i> Localisation spatiale
                    </h6>
                </div>
                <div class="col-md-4">
                    <label for="planete" class="form-label">Planète</label>
                    <select class="form-select" id="planete" name="planete">
                        <option value="">Toutes les planètes</option>
                        <?php foreach ($planetes as $p): ?>
                        <option value="<?php echo $p['id_planete']; ?>"
                                <?php echo $planete_id == $p['id_planete'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nom']); ?>
                            (<?php echo htmlspecialchars($p['galaxie']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="galaxie" class="form-label">Galaxie</label>
                    <select class="form-select" id="galaxie" name="galaxie">
                        <option value="">Toutes les galaxies</option>
                        <?php foreach ($galaxies as $g): ?>
                        <option value="<?php echo htmlspecialchars($g); ?>"
                                <?php echo $galaxie === $g ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="atmosphere" class="form-label">
                        <i class="fas fa-wind"></i> Atmosphère
                    </label>
                    <select class="form-select" id="atmosphere" name="atmosphere">
                        <option value="">Tous types</option>
                        <option value="oxygene" <?php echo $atmosphere === 'oxygene' ? 'selected' : ''; ?>>Oxygène</option>
                        <option value="azote" <?php echo $atmosphere === 'azote' ? 'selected' : ''; ?>>Azote</option>
                        <option value="helium" <?php echo $atmosphere === 'helium' ? 'selected' : ''; ?>>Hélium</option>
                        <option value="methane" <?php echo $atmosphere === 'methane' ? 'selected' : ''; ?>>Méthane</option>
                        <option value="co2" <?php echo $atmosphere === 'co2' ? 'selected' : ''; ?>>CO2</option>
                        <option value="mixte" <?php echo $atmosphere === 'mixte' ? 'selected' : ''; ?>>Mixte</option>
                    </select>
                </div>
            </div>

            <hr>

            <!-- Dates et logement -->
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <h6 class="text-primary">
                        <i class="fas fa-calendar"></i> Dates et logement
                    </h6>
                </div>
                <div class="col-md-3">
                    <label for="date_debut" class="form-label">Arrivée</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut"
                           value="<?php echo htmlspecialchars($date_debut); ?>">
                </div>
                <div class="col-md-3">
                    <label for="date_fin" class="form-label">Départ</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin"
                           value="<?php echo htmlspecialchars($date_fin); ?>">
                </div>
                <div class="col-md-3">
                    <label for="type_logement" class="form-label">Type</label>
                    <select class="form-select" id="type_logement" name="type_logement">
                        <option value="">Tous types</option>
                        <option value="appartement" <?php echo $type_logement === 'appartement' ? 'selected' : ''; ?>>Appartement</option>
                        <option value="maison" <?php echo $type_logement === 'maison' ? 'selected' : ''; ?>>Maison</option>
                        <option value="studio" <?php echo $type_logement === 'studio' ? 'selected' : ''; ?>>Studio</option>
                        <option value="villa" <?php echo $type_logement === 'villa' ? 'selected' : ''; ?>>Villa</option>
                        <option value="chambre" <?php echo $type_logement === 'chambre' ? 'selected' : ''; ?>>Chambre</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="capacite" class="form-label">
                        <i class="fas fa-users"></i> Voyageurs
                    </label>
                    <input type="number" class="form-control" id="capacite" name="capacite"
                           value="<?php echo htmlspecialchars($capacite); ?>" min="1" placeholder="Nombre">
                </div>
            </div>

            <hr>

            <!-- Conditions planétaires -->
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <h6 class="text-primary">
                        <i class="fas fa-globe"></i> Conditions planétaires
                    </h6>
                </div>
                <div class="col-md-4">
                    <label for="gravite_min" class="form-label">
                        Gravité minimale (G)
                    </label>
                    <input type="number" step="0.1" class="form-control" id="gravite_min" name="gravite_min"
                           value="<?php echo htmlspecialchars($gravite_min); ?>" placeholder="Ex: 0.8">
                </div>
                <div class="col-md-4">
                    <label for="gravite_max" class="form-label">
                        Gravité maximale (G)
                    </label>
                    <input type="number" step="0.1" class="form-control" id="gravite_max" name="gravite_max"
                           value="<?php echo htmlspecialchars($gravite_max); ?>" placeholder="Ex: 1.2">
                </div>
                <div class="col-md-4">
                    <label for="prix_max" class="form-label">
                        <i class="fas fa-euro-sign"></i> Prix max/nuit
                    </label>
                    <input type="number" step="0.01" class="form-control" id="prix_max" name="prix_max"
                           value="<?php echo htmlspecialchars($prix_max); ?>" placeholder="€">
                </div>
            </div>

            <hr>

            <!-- Équipements spatiaux -->
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <h6 class="text-primary">
                        <i class="fas fa-satellite"></i> Équipements spatiaux
                    </h6>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="generateur_gravite" name="generateur_gravite"
                               <?php echo $generateur_gravite ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="generateur_gravite">
                            🌍 Générateur de gravité
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="dome_protection" name="dome_protection"
                               <?php echo $dome_protection ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="dome_protection">
                            🛡️ Dôme de protection
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="baie_observation" name="baie_observation"
                               <?php echo $baie_observation ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="baie_observation">
                            🔭 Baie d'observation
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="capsule_transport" name="capsule_transport"
                               <?php echo $capsule_transport ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="capsule_transport">
                            🚀 Navette incluse
                        </label>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <a href="search.php" class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="fas fa-redo"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Ajouter la classe search-results -->
    <section class="search-results">
        <div class="container">
            <div class="results-grid">
                <!-- Résultats -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>
                        <i class="fas fa-list"></i>
                        <?php echo count($listings); ?> logement(s) trouvé(s)
                    </h3>
                    <div>
                        <a href="planetes.php" class="btn btn-outline-primary">
                            <i class="fas fa-globe"></i> Explorer par planète
                        </a>
                    </div>
                </div>

                <?php if (!empty($listings)): ?>
                <div class="row">
                    <?php foreach ($listings as $listing): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm hover-shadow">
                            <div class="position-relative">
                                <?php if (!empty($listing['photo_chemin']) && file_exists($listing['photo_chemin'])): ?>
                                    <img src="<?php echo htmlspecialchars($listing['photo_chemin']); ?>"
                                         class="card-img-top" alt="<?php echo htmlspecialchars($listing['titre']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="assets/images/no-image.jpg" class="card-img-top" alt="Pas d'image"
                                         style="height: 200px; object-fit: cover;">
                                <?php endif; ?>

                                <?php if ($listing['vue_spatiale']): ?>
                                <span class="badge bg-info position-absolute top-0 end-0 m-2">
                                    <?php echo ucfirst(str_replace('_', ' ', $listing['vue_spatiale'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($listing['titre']); ?></h5>

                                <p class="text-muted mb-1">
                                    <i class="fas fa-globe text-primary"></i>
                                    <strong><?php echo htmlspecialchars($listing['planete_nom']); ?></strong>
                                </p>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-star"></i>
                                    <?php echo htmlspecialchars($listing['galaxie']); ?>
                                </p>

                                <p class="card-text small">
                                    <?php echo htmlspecialchars(substr($listing['description'], 0, 80)) . '...'; ?>
                                </p>

                                <!-- Caractéristiques -->
                                <div class="d-flex align-items-center mb-2 text-muted small">
                                    <span class="me-3"><i class="fas fa-bed"></i> <?php echo $listing['nb_chambres']; ?></span>
                                    <span class="me-3"><i class="fas fa-users"></i> <?php echo $listing['capacite_max']; ?></span>
                                    <span class="me-3">⚖️ <?php echo $listing['gravite']; ?>G</span>
                                </div>

                                <!-- Badges équipements spatiaux -->
                                <div class="mb-2">
                                    <?php if ($listing['generateur_gravite']): ?>
                                    <span class="badge bg-secondary me-1" title="Générateur de gravité">🌍</span>
                                    <?php endif; ?>
                                    <?php if ($listing['dome_protection']): ?>
                                    <span class="badge bg-secondary me-1" title="Dôme de protection">🛡️</span>
                                    <?php endif; ?>
                                    <?php if ($listing['baie_observation_spatiale']): ?>
                                    <span class="badge bg-secondary me-1" title="Baie d'observation">🔭</span>
                                    <?php endif; ?>
                                    <?php if ($listing['capsule_transport']): ?>
                                    <span class="badge bg-secondary me-1" title="Navette incluse">🚀</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="price fw-bold text-primary">
                                        <?php echo number_format($listing['prix_nuit'], 2); ?>€/nuit
                                    </span>
                                    <a href="listing.php?id=<?php echo $listing['id_annonce']; ?>"
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>Aucun logement trouvé</h4>
                    <p class="text-muted">Essayez de modifier vos critères de recherche ou explorez nos planètes.</p>
                    <a href="planetes.php" class="btn btn-primary mt-3">
                        <i class="fas fa-globe"></i> Explorer les planètes
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
}
</style>

<?php include 'includes/footer.php'; ?>
