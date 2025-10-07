<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$listing_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$listing_id) {
    redirect('search.php');
}

$title = 'Détails du logement - Stars Doors';

// Debug upload
$upload_debug = $_SESSION['upload_debug'] ?? null;
unset($_SESSION['upload_debug']);

// Récupération du logement
try {
    $stmt = $pdo->prepare("SELECT a.*, u.prenom, u.nom, u.email, u.telephone,
                          pl.nom as planete_nom, pl.galaxie, pl.systeme_solaire,
                          pl.type_atmosphere, pl.gravite, pl.temperature_moyenne,
                          pl.couleur_ciel, pl.nombre_lunes, pl.duree_jour_heures,
                          r.nom as race_nom
                          FROM annonces a
                          LEFT JOIN users u ON a.id_user = u.id_user
                          LEFT JOIN planetes pl ON a.id_planete = pl.id_planete
                          LEFT JOIN races r ON u.id_race = r.id_race
                          WHERE a.id_annonce = ? AND a.disponible = 1");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$listing) {
        redirect('search.php');
    }
    
    // Récupération des photos
    $stmt = $pdo->prepare("SELECT * FROM photo WHERE id_annonce = ? ORDER BY photo_principale DESC, ordre ASC");
    $stmt->execute([$listing_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des avis
    $stmt = $pdo->prepare("SELECT av.*, u.prenom, u.nom, r.date_debut, r.date_fin
                          FROM avis av
                          LEFT JOIN users u ON av.id_user = u.id_user
                          LEFT JOIN reservations r ON av.id_reservation = r.id_reservation
                          WHERE av.id_annonce = ? AND av.visible = 1
                          ORDER BY av.date_avis DESC
                          LIMIT 10");
    $stmt->execute([$listing_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcul de la note moyenne
    $stmt = $pdo->prepare("SELECT AVG(note) as moyenne, COUNT(*) as total FROM avis WHERE id_annonce = ? AND visible = 1");
    $stmt->execute([$listing_id]);
    $rating_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $average_rating = $rating_data['moyenne'] ? round($rating_data['moyenne'], 1) : 0;
    $total_reviews = $rating_data['total'];
    
} catch (PDOException $e) {
    error_log("Erreur récupération logement: " . $e->getMessage());
    redirect('search.php');
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-4">
    <?php if ($upload_debug): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <h5>Debug Upload:</h5>
        <ul class="mb-0">
            <?php foreach ($upload_debug as $debug): ?>
            <li><?php echo htmlspecialchars($debug); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Images -->
        <div class="col-12 mb-4">
            <?php if (!empty($photos)): ?>
            <div id="listingCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($photos as $index => $photo): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($photo['chemin']); ?>"
                             class="d-block w-100 listing-main-image" 
                             alt="<?php echo htmlspecialchars($photo['description'] ?: $listing['titre']); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($photos) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#listingCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#listingCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <img src="assets/images/no-image.jpg" class="img-fluid listing-main-image" alt="Pas d'image">
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="mb-4">
                <h1><?php echo htmlspecialchars($listing['titre']); ?></h1>

                <!-- Localisation spatiale -->
                <div class="mb-3">
                    <p class="lead mb-2">
                        <i class="fas fa-globe text-primary"></i>
                        <strong><?php echo htmlspecialchars($listing['planete_nom']); ?></strong>
                        <span class="text-muted ms-2">
                            <i class="fas fa-star-of-life"></i> <?php echo htmlspecialchars($listing['galaxie']); ?>
                        </span>
                    </p>
                    <?php if ($listing['quartier'] || $listing['zone']): ?>
                    <p class="text-muted mb-1">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars(($listing['zone'] ? $listing['zone'] . ' - ' : '') . $listing['quartier']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($listing['adresse']): ?>
                    <p class="text-muted small">
                        📍 <?php echo htmlspecialchars($listing['adresse']); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Ratings et vues -->
                <div class="d-flex align-items-center flex-wrap mb-3">
                    <div class="rating me-4">
                        <?php if ($total_reviews > 0): ?>
                            <span class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $average_rating ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="ms-1"><?php echo $average_rating; ?> (<?php echo $total_reviews; ?> avis)</span>
                        <?php else: ?>
                            <span class="text-muted">Aucun avis</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($listing['vue_spatiale']): ?>
                    <span class="badge bg-info me-2">
                        <i class="fas fa-eye"></i> <?php echo ucfirst(str_replace('_', ' ', $listing['vue_spatiale'])); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                </div>
            </div>

            <!-- Informations planétaires -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-satellite"></i> Informations planétaires</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">🌍 <strong>Système:</strong> <?php echo htmlspecialchars($listing['systeme_solaire']); ?></li>
                                <li class="mb-2">💨 <strong>Atmosphère:</strong> <?php echo ucfirst($listing['type_atmosphere']); ?></li>
                                <li class="mb-2">⚖️ <strong>Gravité:</strong> <?php echo $listing['gravite']; ?>G</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">🌡️ <strong>Température moyenne:</strong> <?php echo $listing['temperature_moyenne']; ?>°C</li>
                                <li class="mb-2">🌈 <strong>Couleur du ciel:</strong> <?php echo htmlspecialchars($listing['couleur_ciel']); ?></li>
                                <li class="mb-2">🌙 <strong>Lunes:</strong> <?php echo $listing['nombre_lunes']; ?></li>
                                <li class="mb-2">⏰ <strong>Durée du jour:</strong> <?php echo $listing['duree_jour_heures']; ?>h</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Caractéristiques du logement -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-home"></i> Caractéristiques du logement</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-bed"></i> <?php echo $listing['nb_chambres']; ?> chambre(s)</li>
                                <li><i class="fas fa-bath"></i> <?php echo $listing['nb_salles_bain']; ?> salle(s) de bain</li>
                                <li><i class="fas fa-users"></i> Jusqu'à <?php echo $listing['capacite_max']; ?> personnes</li>
                                <li><i class="fas fa-home"></i> <?php echo ucfirst($listing['type_logement']); ?></li>
                                <?php if ($listing['surface']): ?>
                                <li><i class="fas fa-ruler-combined"></i> <?php echo $listing['surface']; ?> m²</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <?php if ($listing['wifi']): ?>
                                <li><i class="fas fa-wifi text-success"></i> WiFi intergalactique</li>
                                <?php endif; ?>
                                <?php if ($listing['parking']): ?>
                                <li><i class="fas fa-car text-success"></i> Parking vaisseau</li>
                                <?php endif; ?>
                                <?php if ($listing['climatisation']): ?>
                                <li><i class="fas fa-snowflake text-success"></i> Climatisation</li>
                                <?php endif; ?>
                                <?php if ($listing['lave_linge']): ?>
                                <li><i class="fas fa-tshirt text-success"></i> Lave-linge</li>
                                <?php endif; ?>
                                <?php if ($listing['television']): ?>
                                <li><i class="fas fa-tv text-success"></i> HoloVision</li>
                                <?php endif; ?>
                                <?php if ($listing['animaux_acceptes']): ?>
                                <li><i class="fas fa-paw text-success"></i> Créatures acceptées</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Équipements spatiaux -->
            <?php
            $has_spatial_equipment = $listing['generateur_gravite'] || $listing['dome_protection'] ||
                                     $listing['capsule_transport'] || $listing['baie_observation_spatiale'] ||
                                     $listing['bouclier_radiations'] || $listing['communicateur_intergalactique'];
            ?>
            <?php if ($has_spatial_equipment): ?>
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-rocket"></i> Équipements spatiaux</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <?php if ($listing['generateur_gravite']): ?>
                                <li class="mb-2">🌍 Générateur de gravité artificielle</li>
                                <?php endif; ?>
                                <?php if ($listing['dome_protection']): ?>
                                <li class="mb-2">🛡️ Dôme de protection</li>
                                <?php endif; ?>
                                <?php if ($listing['systeme_traduction']): ?>
                                <li class="mb-2">🗣️ Traducteur universel</li>
                                <?php endif; ?>
                                <?php if ($listing['capsule_transport']): ?>
                                <li class="mb-2">🚀 Navette personnelle incluse</li>
                                <?php endif; ?>
                                <?php if ($listing['baie_observation_spatiale']): ?>
                                <li class="mb-2">🔭 Baie d'observation spatiale</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <?php if ($listing['recycleur_air']): ?>
                                <li class="mb-2">💨 Recycleur d'air</li>
                                <?php endif; ?>
                                <?php if ($listing['regulateur_temperature']): ?>
                                <li class="mb-2">🌡️ Régulateur de température</li>
                                <?php endif; ?>
                                <?php if ($listing['bouclier_radiations']): ?>
                                <li class="mb-2">☢️ Bouclier anti-radiations</li>
                                <?php endif; ?>
                                <?php if ($listing['communicateur_intergalactique']): ?>
                                <li class="mb-2">📡 Communicateur intergalactique</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Atmosphère -->
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-wind"></i> Atmosphère et respiration</h5>
                </div>
                <div class="card-body">
                    <p><strong>Type d'air ambiant:</strong> <?php echo ucfirst($listing['type_d_air']); ?></p>
                    <p><strong>Bouteilles d'air fournies:</strong> <?php echo ucfirst($listing['bouteille_air']); ?></p>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Des équipements de respiration adaptés sont disponibles pour toutes les races
                    </div>
                </div>
            </div>

            <!-- Avis -->
            <?php if (!empty($reviews)): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Avis des voyageurs</h5>
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong><?php echo htmlspecialchars($review['prenom'] . ' ' . $review['nom']); ?></strong>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['note'] ? ' text-warning' : '-o text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($review['date_avis'])); ?></small>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['commentaire'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar réservation -->
        <div class="col-lg-4">
            <div class="card sticky-top">
                <div class="card-body">
                    <div class="price-display mb-3">
                        <h3 class="text-primary"><?php echo number_format($listing['prix_nuit'], 2); ?>€ <small class="text-muted">/ nuit</small></h3>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="booking.php?id=<?php echo $listing['id_annonce']; ?>" class="btn btn-primary w-100 mb-3">
                        Réserver maintenant
                    </a>
                    <?php else: ?>
                    <a href="login.php?redirect=<?php echo urlencode('booking.php?id=' . $listing['id_annonce']); ?>" 
                       class="btn btn-primary w-100 mb-3">
                        Se connecter pour réserver
                    </a>
                    <?php endif; ?>
                    
                    <!-- Contact propriétaire -->
                    <div class="border-top pt-3">
                        <h6>Propriétaire</h6>
                        <p class="mb-1">
                            <strong><?php echo htmlspecialchars($listing['prenom'] . ' ' . $listing['nom']); ?></strong>
                        </p>
                        <?php if ($listing['race_nom']): ?>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-user-astronaut"></i> <?php echo htmlspecialchars($listing['race_nom']); ?>
                        </p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="messages.php?to=<?php echo $listing['id_user']; ?>&listing=<?php echo $listing['id_annonce']; ?>"
                           class="btn btn-outline-primary w-100">
                            <i class="fas fa-envelope"></i> Contacter
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>