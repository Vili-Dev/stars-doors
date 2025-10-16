<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$planet_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$planet_id) {
    redirect('planetes.php');
}

// Récupération des détails de la planète
try {
    $stmt = $pdo->prepare("SELECT p.*,
                          COUNT(DISTINCT a.id_annonce) as nb_annonces,
                          COUNT(DISTINCT r.id_reservation) as nb_reservations,
                          AVG(a.note_moyenne) as note_moyenne_planete,
                          AVG(a.prix_nuit) as prix_moyen_nuit
                          FROM planetes p
                          LEFT JOIN annonces a ON p.id_planete = a.id_planete AND a.disponible = 1
                          LEFT JOIN reservations r ON a.id_annonce = r.id_annonce AND r.statut = 'confirmee'
                          WHERE p.id_planete = ?
                          GROUP BY p.id_planete");
    $stmt->execute([$planet_id]);
    $planete = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$planete) {
        redirect('planetes.php');
    }
} catch (PDOException $e) {
    error_log("Erreur récupération planète: " . $e->getMessage());
    redirect('planetes.php');
}

// Récupération des races originaires de cette planète
try {
    $stmt = $pdo->prepare("SELECT * FROM races WHERE id_planete_origine = ?");
    $stmt->execute([$planet_id]);
    $races_natives = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $races_natives = [];
}

// Récupération de quelques annonces sur cette planète
try {
    $stmt = $pdo->prepare("SELECT a.*, p.chemin as photo_chemin
                          FROM annonces a
                          LEFT JOIN photo p ON a.id_annonce = p.id_annonce AND p.photo_principale = 1
                          WHERE a.id_planete = ? AND a.disponible = 1
                          ORDER BY a.note_moyenne DESC, a.date_creation DESC
                          LIMIT 6");
    $stmt->execute([$planet_id]);
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $annonces = [];
}

$title = htmlspecialchars($planete['nom']) . ' - Stars Doors';
include 'includes/header.php';
include 'includes/nav.php';
?>

<main>
    <!-- Hero avec image de la planète -->
    <section class="planet-hero position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 400px;">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-8 text-white">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white">Accueil</a></li>
                            <li class="breadcrumb-item"><a href="planetes.php" class="text-white">Planètes</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">
                                <?php echo htmlspecialchars($planete['nom']); ?>
                            </li>
                        </ol>
                    </nav>

                    <h1 class="display-3 mb-3">
                        <i class="fas fa-globe"></i>
                        <?php echo htmlspecialchars($planete['nom']); ?>
                    </h1>

                    <p class="lead mb-4">
                        <i class="fas fa-star"></i>
                        <?php echo htmlspecialchars($planete['galaxie']); ?> -
                        <?php echo htmlspecialchars($planete['systeme_solaire']); ?>
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php if ($planete['habitable_humains']): ?>
                        <span class="badge bg-success fs-6">👤 Habitable pour humains</span>
                        <?php endif; ?>
                        <?php if ($planete['habitable_aliens']): ?>
                        <span class="badge bg-info fs-6">👽 Habitable pour aliens</span>
                        <?php endif; ?>
                        <span class="badge bg-light text-dark fs-6">
                            <?php echo ucfirst($planete['niveau_technologie']); ?>
                        </span>
                    </div>

                    <?php if ($planete['nb_annonces'] > 0): ?>
                    <a href="search.php?planete=<?php echo $planete['id_planete']; ?>" class="btn btn-light btn-lg">
                        <i class="fas fa-search"></i>
                        Voir les <?php echo $planete['nb_annonces']; ?> logements
                    </a>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4 text-center">
                    <div class="planet-icon" style="font-size: 10rem; animation: float 3s ease-in-out infinite;">
                        🪐
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">
                <!-- Description -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-3">
                            <i class="fas fa-info-circle text-primary"></i> À propos de <?php echo htmlspecialchars($planete['nom']); ?>
                        </h3>
                        <p class="lead"><?php echo nl2br(htmlspecialchars($planete['description'])); ?></p>
                    </div>
                </div>

                <!-- Caractéristiques détaillées -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-bar"></i> Caractéristiques planétaires
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">💨</div>
                                    <div>
                                        <h6 class="mb-0">Atmosphère</h6>
                                        <p class="text-muted mb-0"><?php echo ucfirst($planete['type_atmosphere']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">⚖️</div>
                                    <div>
                                        <h6 class="mb-0">Gravité</h6>
                                        <p class="text-muted mb-0"><?php echo $planete['gravite']; ?>G
                                            <?php if ($planete['gravite'] < 0.8): ?>
                                            (Faible)
                                            <?php elseif ($planete['gravite'] <= 1.2): ?>
                                            (Normale)
                                            <?php else: ?>
                                            (Forte)
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">🌡️</div>
                                    <div>
                                        <h6 class="mb-0">Température moyenne</h6>
                                        <p class="text-muted mb-0"><?php echo $planete['temperature_moyenne']; ?>°C</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">🌈</div>
                                    <div>
                                        <h6 class="mb-0">Couleur du ciel</h6>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($planete['couleur_ciel']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">🌙</div>
                                    <div>
                                        <h6 class="mb-0">Lunes</h6>
                                        <p class="text-muted mb-0"><?php echo $planete['nombre_lunes']; ?> satellite(s) naturel(s)</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">⏰</div>
                                    <div>
                                        <h6 class="mb-0">Durée du jour</h6>
                                        <p class="text-muted mb-0"><?php echo $planete['duree_jour_heures']; ?> heures</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">👥</div>
                                    <div>
                                        <h6 class="mb-0">Population</h6>
                                        <p class="text-muted mb-0"><?php echo number_format($planete['population']); ?> habitants</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">💰</div>
                                    <div>
                                        <h6 class="mb-0">Monnaie locale</h6>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($planete['monnaie_locale']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">📏</div>
                                    <div>
                                        <h6 class="mb-0">Distance depuis la Terre</h6>
                                        <p class="text-muted mb-0">
                                            <?php echo number_format($planete['distance_terre_al'], 2); ?> années-lumière
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3" style="font-size: 2rem;">🚀</div>
                                    <div>
                                        <h6 class="mb-0">Niveau technologique</h6>
                                        <p class="text-muted mb-0"><?php echo ucfirst($planete['niveau_technologie']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Races natives -->
                <?php if (!empty($races_natives)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-astronaut"></i> Races originaires
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($races_natives as $race): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <div class="me-3" style="font-size: 2.5rem;">👽</div>
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($race['nom']); ?></h5>
                                        <p class="text-muted small mb-0">
                                            <?php echo htmlspecialchars(substr($race['description'], 0, 150)); ?>...
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Annonces disponibles -->
                <?php if (!empty($annonces)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-home"></i> Logements disponibles sur <?php echo htmlspecialchars($planete['nom']); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($annonces as $annonce): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <?php if (!empty($annonce['photo_chemin']) && file_exists($annonce['photo_chemin'])): ?>
                                    <img src="<?php echo htmlspecialchars($annonce['photo_chemin']); ?>"
                                         class="card-img-top" alt="<?php echo htmlspecialchars($annonce['titre']); ?>"
                                         style="height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($annonce['titre']); ?></h6>
                                        <p class="card-text small text-muted">
                                            <?php echo htmlspecialchars(substr($annonce['description'], 0, 80)); ?>...
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-primary">
                                                <?php echo number_format($annonce['prix_nuit'], 2); ?>€/nuit
                                            </span>
                                            <a href="listing.php?id=<?php echo $annonce['id_annonce']; ?>"
                                               class="btn btn-sm btn-outline-primary">Voir</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-center mt-3">
                            <a href="search.php?planete=<?php echo $planete['id_planete']; ?>"
                               class="btn btn-primary">
                                Voir tous les logements
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Statistiques -->
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i> Statistiques
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Logements disponibles</span>
                                <strong><?php echo $planete['nb_annonces']; ?></strong>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: 100%;"></div>
                            </div>
                        </div>

                        <?php if ($planete['nb_reservations'] > 0): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Réservations</span>
                                <strong><?php echo $planete['nb_reservations']; ?></strong>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: 80%;"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($planete['note_moyenne_planete'] > 0): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Note moyenne</span>
                                <strong><?php echo number_format($planete['note_moyenne_planete'], 1); ?>/5</strong>
                            </div>
                            <div class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= round($planete['note_moyenne_planete']) ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($planete['prix_moyen_nuit'] > 0): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Prix moyen par nuit</span>
                                <strong><?php echo number_format($planete['prix_moyen_nuit'], 2); ?>€</strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="border-top pt-3 mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i>
                                Découverte: <?php
                                $date = !empty($planete['date_decouverte']) ? strtotime($planete['date_decouverte']) : strtotime('1970-01-01');
                                $formatted_date = date('d/m/Y', $date);
                                echo $formatted_date;
                                ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Infos pratiques -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Infos pratiques
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Équipement de respiration fourni
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success"></i>
                                Traducteur universel disponible
                            </li>
                            <?php if ($planete['gravite'] != 1.0): ?>
                            <li class="mb-2">
                                <i class="fas fa-info-circle text-info"></i>
                                Adaptation à la gravité nécessaire
                            </li>
                            <?php endif; ?>
                            <?php if ($planete['temperature_moyenne'] < -20 || $planete['temperature_moyenne'] > 40): ?>
                            <li class="mb-2">
                                <i class="fas fa-info-circle text-info"></i>
                                Vêtements adaptés recommandés
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="planet-images">
    <div id="planetCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM planetes_images WHERE id_planete = ? ORDER BY ordre ASC");
            $stmt->execute([$planet['id_planete']]);
            $images = $stmt->fetchAll();
            
            if (empty($images)) {
                // Image par défaut si aucune image n'est trouvée
                echo '<div class="carousel-item active">
                        <img src="assets/images/default-planet.jpg" class="d-block w-100" alt="'.$planet['nom'].'">
                    </div>';
            } else {
                foreach ($images as $index => $image) {
                    echo '<div class="carousel-item '.($index === 0 ? 'active' : '').'">
                            <img src="assets/images/planetes/'.$image['image_url'].'" class="d-block w-100" alt="'.$planet['nom'].'">
                          </div>';
                }
            }
            ?>
        </div>
        <?php if (count($images) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#planetCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Précédent</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#planetCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Suivant</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<style>
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}
</style>

<?php include 'includes/footer.php'; ?>
