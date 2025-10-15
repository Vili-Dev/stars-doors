<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$title = 'Accueil - Stars Doors';
$current_page = 'home';

// Récupération des annonces en vedette avec toutes leurs photos
try {
    $stmt = $pdo->prepare("SELECT a.*, u.prenom, u.nom, u.avatar, u.race,
                          pl.nom as planete_nom, pl.galaxie, pl.image_planete
                          FROM annonces a
                          LEFT JOIN users u ON a.id_user = u.id_user
                          LEFT JOIN planetes pl ON a.id_planete = pl.id_planete
                          WHERE a.disponible = 1
                          ORDER BY a.date_creation DESC
                          LIMIT 6");
    $stmt->execute();
    $featured_listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer toutes les photos pour chaque annonce
    foreach ($featured_listings as &$listing) {
        $stmt_photos = $pdo->prepare("SELECT chemin, ordre FROM photo WHERE id_annonce = ? ORDER BY photo_principale DESC, ordre ASC");
        $stmt_photos->execute([$listing['id_annonce']]);
        $listing['photos'] = $stmt_photos->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($listing);
} catch (PDOException $e) {
    $featured_listings = [];
    error_log("Erreur lors de la récupération des annonces: " . $e->getMessage());
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-rocket"></i> Bienvenue chez Stars Doors
                    </h1>
                    
                    <p class="lead mb-4">🌌 Explorez la galaxie et trouvez votre logement parfait sur des milliers de planètes</p>

                    <!-- Barre de recherche -->
                    <form action="search.php" method="GET" class="row g-3 justify-content-center">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="planete" placeholder="🪐 Quelle planète voulez-vous visiter ?">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="date_debut" placeholder="Arrivée" required>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="date_fin" placeholder="Départ" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-light w-100">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Annonces en vedette -->
    <section class="featured-listings py-5">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="fas fa-star"></i> Logements intergalactiques en vedette
            </h2>

            <?php if (!empty($featured_listings)): ?>
            <div class="row">
                <?php foreach ($featured_listings as $listing): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card listing-card h-100 shadow-sm">
                        <!-- Carousel pour les photos -->
                        <div id="carousel-<?php echo $listing['id_annonce']; ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php if (!empty($listing['photos'])): ?>
                                    <?php foreach ($listing['photos'] as $index => $photo): ?>
                                        <?php if (file_exists($photo['chemin'])): ?>
                                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                                <img src="<?php echo htmlspecialchars($photo['chemin']); ?>"
                                                     class="d-block w-100" alt="<?php echo htmlspecialchars($listing['titre']); ?>"
                                                     style="height: 200px; object-fit: cover;">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="carousel-item active">
                                        <img src="assets/images/no-image.jpg" class="d-block w-100" alt="Pas d'image"
                                             style="height: 200px; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Contrôles du carousel (seulement si plusieurs photos) -->
                            <?php if (!empty($listing['photos']) && count($listing['photos']) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $listing['id_annonce']; ?>" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Précédent</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $listing['id_annonce']; ?>" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Suivant</span>
                                </button>

                                <!-- Indicateurs (points) -->
                                <div class="carousel-indicators">
                                    <?php foreach ($listing['photos'] as $index => $photo): ?>
                                        <button type="button" data-bs-target="#carousel-<?php echo $listing['id_annonce']; ?>"
                                                data-bs-slide-to="<?php echo $index; ?>"
                                                <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?>
                                                aria-label="Slide <?php echo $index + 1; ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Badge vue spatiale -->
                            <?php if ($listing['vue_spatiale']): ?>
                            <span class="badge bg-info position-absolute top-0 end-0 m-2" style="z-index: 10;">
                                <?php echo ucfirst(str_replace('_', ' ', $listing['vue_spatiale'])); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <?php echo generateAvatarHtml($listing['avatar'] ?? '', $listing['race'] ?? '', 35, 'me-2'); ?>
                                <div>
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($listing['titre']); ?></h5>
                                    <small class="text-muted">
                                        Par <?php echo htmlspecialchars($listing['prenom'] . ' ' . $listing['nom']); ?>
                                        <?php if (!empty($listing['race'])): ?>
                                            <span class="badge bg-info ms-1"><?php echo htmlspecialchars($listing['race']); ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            
                            <p class="card-text text-muted mb-1">
                                <i class="fas fa-globe text-primary"></i>
                                <strong><?php echo htmlspecialchars($listing['planete_nom']); ?></strong>
                            </p>
                            <p class="card-text text-muted small mb-2">
                                <i class="fas fa-star"></i>
                                <?php echo htmlspecialchars($listing['galaxie']); ?>
                            </p>
                            <p class="card-text small"><?php echo htmlspecialchars(substr($listing['description'], 0, 80)) . '...'; ?></p>

                            <div class="d-flex align-items-center mb-2 text-muted small">
                                <span class="me-3"><i class="fas fa-bed"></i> <?php echo $listing['nb_chambres']; ?></span>
                                <span class="me-3"><i class="fas fa-users"></i> <?php echo $listing['capacite_max']; ?></span>
                                <?php if ($listing['generateur_gravite']): ?>
                                <span class="me-2">🌍</span>
                                <?php endif; ?>
                                <?php if ($listing['baie_observation_spatiale']): ?>
                                <span class="me-2">🔭</span>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="price fw-bold text-primary"><?php echo number_format($listing['prix_nuit'], 2); ?>€/nuit</span>
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
            <div class="text-center">
                <p class="text-muted">Aucune annonce disponible pour le moment. Soyez le premier à partager votre logement spatial !</p>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'proprietaire'): ?>
                <a href="create_listing.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer une annonce
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="search.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-rocket"></i> Explorer toutes les planètes
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>