<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$title = 'Explorer les Planètes - Stars Doors';
$current_page = 'planetes';

// Filtres
$galaxie_filter = $_GET['galaxie'] ?? '';
$atmosphere_filter = $_GET['atmosphere'] ?? '';
$habitable_filter = $_GET['habitable'] ?? '';

// Récupération des galaxies disponibles
try {
    $stmt = $pdo->query("SELECT DISTINCT galaxie FROM planetes WHERE statut = 'active' ORDER BY galaxie");
    $galaxies = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $galaxies = [];
}

// Construction de la requête avec filtres
$sql = "SELECT p.*,
        COUNT(DISTINCT a.id_annonce) as nb_annonces,
        COUNT(DISTINCT r.id_reservation) as nb_reservations,
        AVG(a.note_moyenne) as note_moyenne_planete
        FROM planetes p
        LEFT JOIN annonces a ON p.id_planete = a.id_planete AND a.disponible = 1
        LEFT JOIN reservations r ON a.id_annonce = r.id_annonce AND r.statut = 'confirmee'
        WHERE p.statut = 'active'";

$params = [];

if ($galaxie_filter) {
    $sql .= " AND p.galaxie = ?";
    $params[] = $galaxie_filter;
}

if ($atmosphere_filter) {
    $sql .= " AND p.type_atmosphere = ?";
    $params[] = $atmosphere_filter;
}

if ($habitable_filter === 'humains') {
    $sql .= " AND p.habitable_humains = 1";
} elseif ($habitable_filter === 'aliens') {
    $sql .= " AND p.habitable_aliens = 1";
}

$sql .= " GROUP BY p.id_planete ORDER BY nb_annonces DESC, p.nom ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $planetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $planetes = [];
    error_log("Erreur récupération planètes: " . $e->getMessage());
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main>
    <section class="planetes-hero">
        <div class="container">
            <div class="row justify-content-center text-center">
                <h1>Explorez la Galaxie</h1>
                <p class="lead">Découvrez des milliers de planètes habitables</p>
            </div>
        </div>
    </section>

    <!-- Filtres -->
    <section class="filters-section bg-light py-4">
        <div class="container">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="galaxie" class="form-label">
                        <i class="fas fa-star"></i> Galaxie
                    </label>
                    <select class="form-select" id="galaxie" name="galaxie">
                        <option value="">Toutes les galaxies</option>
                        <?php foreach ($galaxies as $galaxie): ?>
                        <option value="<?php echo htmlspecialchars($galaxie); ?>"
                                <?php echo $galaxie_filter === $galaxie ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($galaxie); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="atmosphere" class="form-label">
                        <i class="fas fa-wind"></i> Atmosphère
                    </label>
                    <select class="form-select" id="atmosphere" name="atmosphere">
                        <option value="">Tous types</option>
                        <option value="oxygene" <?php echo $atmosphere_filter === 'oxygene' ? 'selected' : ''; ?>>Oxygène</option>
                        <option value="azote" <?php echo $atmosphere_filter === 'azote' ? 'selected' : ''; ?>>Azote</option>
                        <option value="helium" <?php echo $atmosphere_filter === 'helium' ? 'selected' : ''; ?>>Hélium</option>
                        <option value="methane" <?php echo $atmosphere_filter === 'methane' ? 'selected' : ''; ?>>Méthane</option>
                        <option value="co2" <?php echo $atmosphere_filter === 'co2' ? 'selected' : ''; ?>>CO2</option>
                        <option value="mixte" <?php echo $atmosphere_filter === 'mixte' ? 'selected' : ''; ?>>Mixte</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="habitable" class="form-label">
                        <i class="fas fa-users"></i> Habitable pour
                    </label>
                    <select class="form-select" id="habitable" name="habitable">
                        <option value="">Tous</option>
                        <option value="humains" <?php echo $habitable_filter === 'humains' ? 'selected' : ''; ?>>Humains</option>
                        <option value="aliens" <?php echo $habitable_filter === 'aliens' ? 'selected' : ''; ?>>Aliens</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <?php if ($galaxie_filter || $atmosphere_filter || $habitable_filter): ?>
                    <a href="planetes.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <!-- Section Planètes Disponibles -->
    <section class="planetes-grid py-5">
        <div class="container">
            <h2 class="text-center mb-4">Nos Planètes Disponibles</h2>
            <div class="row">
                <?php if (!empty($planetes)): ?>
                <?php foreach ($planetes as $planete): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="position-relative">
                            <?php if ($planete['image_planete']): ?>
                                <img src="uploads/planetes/<?php echo htmlspecialchars($planete['image_planete']); ?>"
                                     class="card-img-top" alt="<?php echo htmlspecialchars($planete['nom']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center"
                                     style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <h1 class="text-white display-1">🪐</h1>
                                </div>
                            <?php endif; ?>

                            <!-- Badges -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <?php if ($planete['habitable_humains']): ?>
                                <span class="badge bg-success">👤 Humains</span>
                                <?php endif; ?>
                                <?php if ($planete['habitable_aliens']): ?>
                                <span class="badge bg-info">👽 Aliens</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($planete['nb_annonces'] > 0): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary">
                                    <?php echo $planete['nb_annonces']; ?> logements
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-globe text-primary"></i>
                                <?php echo htmlspecialchars($planete['nom']); ?>
                            </h5>

                            <p class="text-muted small mb-2">
                                <i class="fas fa-star"></i>
                                <?php echo htmlspecialchars($planete['galaxie']); ?> -
                                <?php echo htmlspecialchars($planete['systeme_solaire']); ?>
                            </p>

                            <p class="card-text small">
                                <?php echo htmlspecialchars(substr($planete['description'], 0, 100)) . '...'; ?>
                            </p>

                            <!-- Caractéristiques clés -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-wind"></i>
                                        <?php echo ucfirst($planete['type_atmosphere']); ?>
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-weight"></i>
                                        <?php echo $planete['gravite']; ?>G
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-thermometer-half"></i>
                                        <?php echo $planete['temperature_moyenne']; ?>°C
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-moon"></i>
                                        <?php echo $planete['nombre_lunes']; ?> lune(s)
                                    </small>
                                </div>
                            </div>

                            <!-- Statistiques -->
                            <?php if ($planete['nb_annonces'] > 0): ?>
                            <div class="border-top pt-3 mb-3">
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>
                                        <i class="fas fa-home"></i>
                                        <?php echo $planete['nb_annonces']; ?> logements
                                    </span>
                                    <?php if ($planete['nb_reservations'] > 0): ?>
                                    <span>
                                        <i class="fas fa-calendar-check"></i>
                                        <?php echo $planete['nb_reservations']; ?> réservations
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($planete['note_moyenne_planete'] > 0): ?>
                                <div class="mt-2">
                                    <span class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= round($planete['note_moyenne_planete']) ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                    <small class="text-muted ms-1">
                                        <?php echo number_format($planete['note_moyenne_planete'], 1); ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="d-grid gap-2">
                                <?php if ($planete['nb_annonces'] > 0): ?>
                                <a href="search.php?planete=<?php echo $planete['id_planete']; ?>"
                                   class="btn btn-primary">
                                    <i class="fas fa-search"></i> Voir les logements
                                </a>
                                <?php else: ?>
                                <button class="btn btn-outline-secondary" disabled>
                                    Aucun logement disponible
                                </button>
                                <?php endif; ?>
                                <a href="planet_detail.php?id=<?php echo $planete['id_planete']; ?>"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-info-circle"></i> En savoir plus
                                </a>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <small class="text-muted">
                                <i class="fas fa-ruler-horizontal"></i>
                                Distance: <?php echo number_format($planete['distance_terre_al'], 2); ?> années-lumière
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h3>Aucune planète trouvée</h3>
                    <p class="text-muted">Essayez de modifier vos filtres de recherche</p>
                    <a href="planetes.php" class="btn btn-primary mt-3">
                        <i class="fas fa-redo"></i> Réinitialiser les filtres
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section info -->
    <section class="info-section bg-primary text-white py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="h1 mb-3">🌍</div>
                    <h4><?php echo count($planetes); ?></h4>
                    <p class="mb-0">Planètes explorées</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="h1 mb-3">🏠</div>
                    <h4>
                        <?php
                        $total_annonces = array_sum(array_column($planetes, 'nb_annonces'));
                        echo $total_annonces;
                        ?>
                    </h4>
                    <p class="mb-0">Logements disponibles</p>
                </div>
                <div class="col-md-4">
                    <div class="h1 mb-3">🌌</div>
                    <h4><?php echo count($galaxies); ?></h4>
                    <p class="mb-0">Galaxies accessibles</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Informations complémentaires avec onglets -->
    <section class="additional-info py-5">
        <div class="container">
            <h2 class="text-center mb-5">Informations Complémentaires</h2>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-justified mb-4" id="infoTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="races-tab" data-bs-toggle="tab" data-bs-target="#races"
                            type="button" role="tab" aria-controls="races" aria-selected="true">
                        <i class="fas fa-users"></i> Races Galactiques
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="covoiturage-tab" data-bs-toggle="tab" data-bs-target="#covoiturage"
                            type="button" role="tab" aria-controls="covoiturage" aria-selected="false">
                        <i class="fas fa-rocket"></i> Co-voiturage Spatial
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fidelite-tab" data-bs-toggle="tab" data-bs-target="#fidelite"
                            type="button" role="tab" aria-controls="fidelite" aria-selected="false">
                        <i class="fas fa-gem"></i> Programme Fidélité
                    </button>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content" id="infoTabsContent">
                <!-- Onglet Races -->
                <div class="tab-pane fade show active" id="races" role="tabpanel" aria-labelledby="races-tab">
                    <div class="text-center mb-4">
                        <h3><i class="fas fa-users text-primary"></i> Races Galactiques</h3>
                        <p class="text-muted">Découvrez les différentes espèces intelligentes qui peuplent la galaxie</p>
                    </div>

                    <div class="text-center">
                        <a href="races.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-eye"></i> Voir toutes les races
                        </a>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4 text-center mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="h1 mb-3">👤</div>
                                    <h5>Humains</h5>
                                    <p class="small text-muted">Espèce originaire de la Terre, adaptable et exploratrice</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="h1 mb-3">👽</div>
                                    <h5>Aliens</h5>
                                    <p class="small text-muted">Diverses espèces extra-terrestres avec des caractéristiques uniques</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="h1 mb-3">🤖</div>
                                    <h5>Cyborgs</h5>
                                    <p class="small text-muted">Êtres hybrides combinant biologie et technologie</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Onglet Co-voiturage -->
                <div class="tab-pane fade" id="covoiturage" role="tabpanel" aria-labelledby="covoiturage-tab">
                    <div class="text-center mb-4">
                        <h3><i class="fas fa-rocket text-primary"></i> Co-voiturage Spatial</h3>
                        <p class="text-muted">Partagez vos voyages interplanétaires et économisez sur vos déplacements</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5><i class="fas fa-user-plus text-success"></i> Proposer un trajet</h5>
                                    <p class="text-muted">Vous avez de la place dans votre vaisseau ? Proposez un co-voiturage et partagez les frais de voyage.</p>
                                    <ul class="small">
                                        <li>Divisez les coûts de carburant</li>
                                        <li>Rencontrez d'autres voyageurs</li>
                                        <li>Voyagez en toute sécurité</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5><i class="fas fa-search text-info"></i> Rejoindre un trajet</h5>
                                    <p class="text-muted">Trouvez un co-voiturage vers votre destination et économisez jusqu'à 70% sur vos frais de transport.</p>
                                    <ul class="small">
                                        <li>Prix réduits</li>
                                        <li>Départs fréquents</li>
                                        <li>Système de notation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="covoiturage.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket"></i> Voir les trajets disponibles
                        </a>
                    </div>
                </div>

                <!-- Onglet Fidélité -->
                <div class="tab-pane fade" id="fidelite" role="tabpanel" aria-labelledby="fidelite-tab">
                    <div class="text-center mb-4">
                        <h3><i class="fas fa-gem text-primary"></i> Programme Fidélité</h3>
                        <p class="text-muted">Gagnez des points à chaque réservation et profitez d'avantages exclusifs</p>
                    </div>

                    <div class="row">
                        <div class="col-md-15 mb-3">
                            <div class="card text-center h-100" style="background: linear-gradient(135deg, #cd7f32 0%, #e6a85c 100%);">
                                <div class="card-body text-white">
                                    <i class="fas fa-medal fa-2x mb-2"></i>
                                    <h5>Bronze</h5>
                                    <p class="small mb-0">Niveau de départ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-15 mb-3">
                            <div class="card text-center h-100" style="background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);">
                                <div class="card-body">
                                    <i class="fas fa-medal fa-2x mb-2 text-secondary"></i>
                                    <h5>Silver</h5>
                                    <p class="small mb-0">5% de réduction</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-15 mb-3">
                            <div class="card text-center h-100" style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);">
                                <div class="card-body">
                                    <i class="fas fa-medal fa-2x mb-2 text-warning"></i>
                                    <h5>Gold</h5>
                                    <p class="small mb-0">10% de réduction</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-15 mb-3">
                            <div class="card text-center h-100" style="background: linear-gradient(135deg, #4a4a4a 0%, #8c8c8c 100%);">
                                <div class="card-body text-white">
                                    <i class="fas fa-crown fa-2x mb-2"></i>
                                    <h5>Platinum</h5>
                                    <p class="small mb-0">15% de réduction</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-15 mb-3">
                            <div class="card text-center h-100" style="background: linear-gradient(135deg, #b9f2ff 0%, #6dd5ed 100%);">
                                <div class="card-body">
                                    <i class="fas fa-gem fa-2x mb-2 text-info"></i>
                                    <h5>Diamond</h5>
                                    <p class="small mb-0">25% de réduction</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="fidelite.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-gift"></i> Découvrir le programme
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="planetes-grid">
        <div class="container">
            <div class="row">
                <?php foreach ($planetes as $planete): ?>
                    <div class="col-md-4 mb-4">
                        <div class="planet-card">
                            <!-- Carousel Bootstrap pour les images -->
                            <div id="carousel-<?php echo $planete['id_planete']; ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php
                                    // Récupérer les images de la planète
                                    $stmt = $pdo->prepare("SELECT * FROM planetes_images WHERE id_planete = ?");
                                    $stmt->execute([$planete['id_planete']]);
                                    $images = $stmt->fetchAll();
                                    
                                    foreach ($images as $index => $image): ?>
                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                            <img src="assets/images/planetes/<?php echo $image['image_url']; ?>" 
                                                 class="d-block w-100" 
                                                 alt="<?php echo $planete['nom']; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($images) > 1): ?>
                                    <button class="carousel-control-prev" type="button" 
                                            data-bs-target="#carousel-<?php echo $planete['id_planete']; ?>" 
                                            data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Précédent</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" 
                                            data-bs-target="#carousel-<?php echo $planete['id_planete']; ?>" 
                                            data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Suivant</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <!-- Reste des informations de la planète -->
                            <div class="planet-content">
                                <h3><?php echo $planete['nom']; ?></h3>
                                <p><?php echo $planete['description']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
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
