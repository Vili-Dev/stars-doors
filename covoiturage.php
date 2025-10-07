<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/currency.php';

$title = 'Co-voiturage Spatial - Stars Doors';
$current_page = 'covoiturage';

// Filtres
$depart_filter = filter_input(INPUT_GET, 'depart', FILTER_VALIDATE_INT);
$arrivee_filter = filter_input(INPUT_GET, 'arrivee', FILTER_VALIDATE_INT);
$date_filter = $_GET['date'] ?? '';

// Récupération des co-voiturages disponibles
$sql = "SELECT c.*,
        pd.nom as depart_nom,
        pd.galaxie as depart_galaxie,
        pa.nom as arrivee_nom,
        pa.galaxie as arrivee_galaxie,
        ABS(pa.distance_terre_al - pd.distance_terre_al) as distance,
        v.nom as vaisseau_nom,
        v.type as vaisseau_type,
        u.nom as organisateur_nom,
        u.prenom as organisateur_prenom,
        r.nom as race_nom,
        (SELECT COUNT(*) FROM covoiturage_participants cp WHERE cp.id_covoiturage = c.id_covoiturage AND cp.statut = 'accepte') as nb_participants
        FROM covoiturage_spatial c
        INNER JOIN planetes pd ON c.id_planete_depart = pd.id_planete
        INNER JOIN planetes pa ON c.id_planete_arrivee = pa.id_planete
        INNER JOIN vaisseaux v ON c.id_vaisseau = v.id_vaisseau
        INNER JOIN users u ON c.id_organisateur = u.id_user
        LEFT JOIN races r ON u.id_race = r.id_race
        WHERE c.statut IN ('ouvert', 'complet')";

$params = [];

if ($depart_filter) {
    $sql .= " AND c.id_planete_depart = ?";
    $params[] = $depart_filter;
}

if ($arrivee_filter) {
    $sql .= " AND c.id_planete_arrivee = ?";
    $params[] = $arrivee_filter;
}

if ($date_filter) {
    $sql .= " AND DATE(c.date_depart) = ?";
    $params[] = $date_filter;
}

$sql .= " ORDER BY c.date_depart ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $covoiturages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $covoiturages = [];
    error_log("Erreur covoiturage: " . $e->getMessage());
}

// Récupérer les planètes pour les filtres
try {
    $stmt = $pdo->query("SELECT id_planete, nom, galaxie FROM planetes WHERE statut = 'active' ORDER BY nom");
    $planetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $planetes = [];
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-5">
    <!-- En-tête -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold mb-3">
            <i class="fas fa-users-cog text-primary"></i> Co-voiturage Spatial
        </h1>
        <p class="lead text-muted">
            Partagez votre vaisseau, divisez les coûts, voyagez ensemble à travers la galaxie
        </p>
    </div>

    <!-- Avantages -->
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card border-success h-100">
                <div class="card-body text-center">
                    <i class="fas fa-coins fa-3x text-success mb-3"></i>
                    <h5>Économisez</h5>
                    <p class="text-muted mb-0">Jusqu'à 70% d'économies sur vos trajets spatiaux</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-friends fa-3x text-primary mb-3"></i>
                    <h5>Rencontrez</h5>
                    <p class="text-muted mb-0">Voyagez avec d'autres explorateurs galactiques</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning h-100">
                <div class="card-body text-center">
                    <i class="fas fa-leaf fa-3x text-warning mb-3"></i>
                    <h5>Écologique</h5>
                    <p class="text-muted mb-0">Réduisez votre empreinte carbone galactique</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <a href="search.php" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-search"></i> Trouver un co-voiturage
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <?php if (isLoggedIn()): ?>
                <a href="covoiturage_create.php" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-plus-circle"></i> Proposer un trajet
                </a>
            <?php else: ?>
                <a href="login.php?redirect=covoiturage_create.php" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-sign-in-alt"></i> Connexion pour proposer
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-plane-departure"></i> Départ</label>
                        <select name="depart" class="form-select">
                            <option value="">Toutes les planètes</option>
                            <?php foreach ($planetes as $p): ?>
                                <option value="<?php echo $p['id_planete']; ?>" <?php echo $depart_filter == $p['id_planete'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nom']); ?> (<?php echo htmlspecialchars($p['galaxie']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-plane-arrival"></i> Arrivée</label>
                        <select name="arrivee" class="form-select">
                            <option value="">Toutes les planètes</option>
                            <?php foreach ($planetes as $p): ?>
                                <option value="<?php echo $p['id_planete']; ?>" <?php echo $arrivee_filter == $p['id_planete'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nom']); ?> (<?php echo htmlspecialchars($p['galaxie']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des co-voiturages -->
    <h3 class="mb-3"><i class="fas fa-list"></i> Trajets Disponibles</h3>

    <?php if (empty($covoiturages)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> Aucun co-voiturage ne correspond à vos critères.
            <br>
            <a href="covoiturage_create.php" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-plus"></i> Soyez le premier à proposer ce trajet
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($covoiturages as $cov): ?>
                <?php
                $places_restantes = $cov['places_disponibles'] - $cov['nb_participants'];
                $is_complet = $places_restantes <= 0;
                $date_depart = new DateTime($cov['date_depart']);
                $equipements = json_decode($cov['equipements_partages'] ?? '[]', true);
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm <?php echo $is_complet ? 'border-danger' : 'border-success'; ?>">
                        <div class="card-header <?php echo $is_complet ? 'bg-danger' : 'bg-success'; ?> text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-rocket"></i>
                                    <?php echo htmlspecialchars($cov['vaisseau_nom']); ?>
                                </h6>
                                <?php if ($is_complet): ?>
                                    <span class="badge bg-light text-dark">COMPLET</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">
                                        <?php echo $places_restantes; ?> place<?php echo $places_restantes > 1 ? 's' : ''; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Trajet -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <i class="fas fa-circle text-success"></i>
                                        <strong><?php echo htmlspecialchars($cov['depart_nom']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($cov['depart_galaxie']); ?></small>
                                    </div>
                                </div>
                                <div class="text-center my-2">
                                    <i class="fas fa-arrow-down text-primary"></i>
                                    <small class="text-muted d-block"><?php echo number_format($cov['distance'], 2); ?> AL</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <strong><?php echo htmlspecialchars($cov['arrivee_nom']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($cov['arrivee_galaxie']); ?></small>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Date et heure -->
                            <div class="mb-2">
                                <i class="fas fa-calendar-alt text-primary"></i>
                                <strong><?php echo $date_depart->format('d/m/Y à H:i'); ?></strong>
                            </div>

                            <!-- Organisateur -->
                            <div class="mb-2">
                                <i class="fas fa-user text-info"></i>
                                <?php echo htmlspecialchars($cov['organisateur_prenom'] . ' ' . $cov['organisateur_nom']); ?>
                                <small class="text-muted">
                                    (<?php echo htmlspecialchars($cov['race_nom'] ?? 'Inconnu'); ?>)
                                </small>
                            </div>

                            <!-- Prix -->
                            <div class="mb-3">
                                <h4 class="text-success mb-0">
                                    <?php echo formatMontant($cov['prix_par_personne']); ?>
                                    <small class="text-muted">/pers.</small>
                                </h4>
                            </div>

                            <!-- Équipements -->
                            <?php if (!empty($equipements)): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Équipements :</small>
                                    <br>
                                    <?php foreach (array_slice($equipements, 0, 3) as $eq): ?>
                                        <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($eq); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Règles -->
                            <?php if ($cov['regles']): ?>
                                <div class="collapse" id="regles-<?php echo $cov['id_covoiturage']; ?>">
                                    <small class="text-muted">
                                        <strong>Règles :</strong>
                                        <?php echo nl2br(htmlspecialchars($cov['regles'])); ?>
                                    </small>
                                </div>
                                <a class="btn btn-link btn-sm p-0" data-bs-toggle="collapse" href="#regles-<?php echo $cov['id_covoiturage']; ?>">
                                    <small>Voir les règles</small>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <?php if (isLoggedIn()): ?>
                                <?php if (!$is_complet): ?>
                                    <a href="covoiturage_join.php?id=<?php echo $cov['id_covoiturage']; ?>" class="btn btn-success w-100">
                                        <i class="fas fa-check-circle"></i> Rejoindre
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="fas fa-times-circle"></i> Complet
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php?redirect=covoiturage_join.php?id=<?php echo $cov['id_covoiturage']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt"></i> Connexion requise
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Comment ça marche -->
    <div class="card mt-5 shadow-sm bg-light">
        <div class="card-body p-5">
            <h3 class="text-center mb-4"><i class="fas fa-question-circle"></i> Comment ça marche ?</h3>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <h5><i class="fas fa-search text-primary"></i> 1. Trouvez votre trajet</h5>
                    <p class="text-muted">
                        Recherchez un co-voiturage spatial qui correspond à votre itinéraire et vos dates.
                    </p>
                </div>

                <div class="col-md-6 mb-4">
                    <h5><i class="fas fa-handshake text-success"></i> 2. Réservez votre place</h5>
                    <p class="text-muted">
                        Contactez l'organisateur et réservez votre place dans le vaisseau.
                    </p>
                </div>

                <div class="col-md-6 mb-4">
                    <h5><i class="fas fa-rocket text-info"></i> 3. Voyagez ensemble</h5>
                    <p class="text-muted">
                        Embarquez et profitez d'un voyage spatial convivial et économique.
                    </p>
                </div>

                <div class="col-md-6 mb-4">
                    <h5><i class="fas fa-star text-warning"></i> 4. Notez l'expérience</h5>
                    <p class="text-muted">
                        Laissez un avis pour aider la communauté des voyageurs spatiaux.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
