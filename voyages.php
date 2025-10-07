<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Vérification de connexion
if (!isLoggedIn()) {
    redirect('login.php?redirect=voyages.php');
}

$title = 'Mes Voyages - Stars Doors';
$current_page = 'voyages';
$user_id = $_SESSION['user_id'];

// Récupération de l'historique des voyages (basé sur les réservations confirmées et terminées)
try {
    $sql = "SELECT
            r.id_reservation,
            r.date_debut,
            r.date_fin,
            r.statut,
            r.created_at as date_reservation,
            a.titre as annonce_titre,
            a.type,
            a.id_annonce,
            p.id_planete,
            p.nom as planete_nom,
            p.galaxie,
            p.systeme_solaire,
            p.atmosphere,
            p.gravite,
            p.distance_terre,
            u_prop.prenom as proprio_prenom,
            u_prop.nom as proprio_nom,
            race.nom as proprio_race
            FROM reservations r
            INNER JOIN annonces a ON r.id_annonce = a.id_annonce
            INNER JOIN planetes p ON a.id_planete = p.id_planete
            INNER JOIN users u_prop ON a.id_user = u_prop.id_user
            LEFT JOIN races race ON u_prop.id_race = race.id_race
            WHERE r.id_user = ?
            AND r.statut IN ('confirmee', 'terminee')
            ORDER BY r.date_debut DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $voyages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques utilisateur
    $stmt = $pdo->prepare("SELECT
        COUNT(DISTINCT a.id_planete) as nb_planetes_visitees,
        COUNT(DISTINCT p.galaxie) as nb_galaxies_visitees,
        COUNT(*) as nb_voyages_total,
        SUM(DATEDIFF(r.date_fin, r.date_debut)) as nb_jours_total
        FROM reservations r
        INNER JOIN annonces a ON r.id_annonce = a.id_annonce
        INNER JOIN planetes p ON a.id_planete = p.id_planete
        WHERE r.id_user = ? AND r.statut IN ('confirmee', 'terminee')");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Badge voyageur (basé sur le nombre de planètes visitées)
    $badge = 'novice';
    if ($stats['nb_planetes_visitees'] >= 15) {
        $badge = 'legende_galactique';
    } elseif ($stats['nb_planetes_visitees'] >= 10) {
        $badge = 'aventurier';
    } elseif ($stats['nb_planetes_visitees'] >= 5) {
        $badge = 'explorateur';
    }

    // Mise à jour du badge si nécessaire
    $stmt = $pdo->prepare("UPDATE users SET
        nb_planetes_visitees = ?,
        nb_galaxies_visitees = ?,
        badge_voyageur = ?
        WHERE id_user = ?");
    $stmt->execute([
        $stats['nb_planetes_visitees'],
        $stats['nb_galaxies_visitees'],
        $badge,
        $user_id
    ]);

} catch (PDOException $e) {
    $voyages = [];
    $stats = [
        'nb_planetes_visitees' => 0,
        'nb_galaxies_visitees' => 0,
        'nb_voyages_total' => 0,
        'nb_jours_total' => 0
    ];
    $badge = 'novice';
    error_log("Erreur récupération voyages: " . $e->getMessage());
}

// Planètes uniques visitées
$planetes_visitees = [];
foreach ($voyages as $voyage) {
    $planetes_visitees[$voyage['id_planete']] = [
        'nom' => $voyage['planete_nom'],
        'galaxie' => $voyage['galaxie']
    ];
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-5">
    <!-- En-tête avec badge -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold mb-3">
                <i class="fas fa-rocket text-primary"></i> Mes Voyages Galactiques
            </h1>
            <p class="lead text-muted">
                Votre carnet de bord spatial et historique d'exploration
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="card border-warning shadow">
                <div class="card-body text-center">
                    <h5 class="text-warning mb-2">
                        <i class="fas fa-medal"></i> Badge Voyageur
                    </h5>
                    <h3 class="mb-0">
                        <?php
                        $badge_labels = [
                            'novice' => '🌟 Novice',
                            'explorateur' => '🚀 Explorateur',
                            'aventurier' => '🌌 Aventurier',
                            'legende_galactique' => '👑 Légende Galactique'
                        ];
                        echo $badge_labels[$badge];
                        ?>
                    </h3>
                    <small class="text-muted">
                        <?php
                        if ($badge === 'novice') {
                            echo "Visitez 5 planètes pour devenir Explorateur";
                        } elseif ($badge === 'explorateur') {
                            echo "Visitez 10 planètes pour devenir Aventurier";
                        } elseif ($badge === 'aventurier') {
                            echo "Visitez 15 planètes pour devenir Légende";
                        } else {
                            echo "Vous avez atteint le niveau maximum !";
                        }
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-5">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center border-primary shadow-sm">
                <div class="card-body">
                    <i class="fas fa-globe text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo number_format($stats['nb_planetes_visitees']); ?></h3>
                    <p class="text-muted mb-0">Planètes visitées</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center border-success shadow-sm">
                <div class="card-body">
                    <i class="fas fa-dharmachakra text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo number_format($stats['nb_galaxies_visitees']); ?></h3>
                    <p class="text-muted mb-0">Galaxies explorées</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center border-info shadow-sm">
                <div class="card-body">
                    <i class="fas fa-rocket text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo number_format($stats['nb_voyages_total']); ?></h3>
                    <p class="text-muted mb-0">Voyages effectués</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center border-warning shadow-sm">
                <div class="card-body">
                    <i class="fas fa-calendar-alt text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?php echo number_format($stats['nb_jours_total']); ?></h3>
                    <p class="text-muted mb-0">Jours en voyage</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Planètes visitées -->
    <?php if (!empty($planetes_visitees)): ?>
        <div class="card mb-5 shadow-sm">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="fas fa-map-marked-alt text-primary"></i> Carte Galactique
                </h4>
                <p class="text-muted">Planètes que vous avez explorées :</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($planetes_visitees as $id => $planete): ?>
                        <a href="planet_detail.php?id=<?php echo $id; ?>"
                           class="badge bg-primary text-decoration-none"
                           style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                            <i class="fas fa-globe"></i>
                            <?php echo htmlspecialchars($planete['nom']); ?>
                            <small class="opacity-75">(<?php echo htmlspecialchars($planete['galaxie']); ?>)</small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Historique des voyages -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-history"></i> Historique des Voyages
            </h4>
        </div>
        <div class="card-body">
            <?php if (empty($voyages)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-rocket text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-4 text-muted">Aucun voyage pour le moment</h4>
                    <p class="text-muted">Commencez votre aventure galactique en explorant nos planètes !</p>
                    <a href="planetes.php" class="btn btn-primary mt-3">
                        <i class="fas fa-globe"></i> Explorer les planètes
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Destination</th>
                                <th>Logement</th>
                                <th>Durée</th>
                                <th>Hôte</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($voyages as $voyage): ?>
                                <?php
                                $duree = (strtotime($voyage['date_fin']) - strtotime($voyage['date_debut'])) / 86400;
                                $is_termine = $voyage['statut'] === 'terminee';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo date('d/m/Y', strtotime($voyage['date_debut'])); ?></strong>
                                        <br>
                                        <small class="text-muted">au <?php echo date('d/m/Y', strtotime($voyage['date_fin'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="planet_detail.php?id=<?php echo $voyage['id_planete']; ?>"
                                           class="text-decoration-none fw-bold">
                                            <i class="fas fa-globe text-primary"></i>
                                            <?php echo htmlspecialchars($voyage['planete_nom']); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($voyage['galaxie']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="listing.php?id=<?php echo $voyage['id_annonce']; ?>"
                                           class="text-decoration-none">
                                            <?php echo htmlspecialchars($voyage['annonce_titre']); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted"><?php echo ucfirst($voyage['type']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo number_format($duree); ?></strong> jour<?php echo $duree > 1 ? 's' : ''; ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-route"></i>
                                            <?php echo number_format($voyage['distance_terre']); ?> AL
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($voyage['proprio_prenom'] . ' ' . $voyage['proprio_nom']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-user-astronaut"></i>
                                            <?php echo htmlspecialchars($voyage['proprio_race'] ?? 'Inconnu'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($is_termine): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Terminé
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-plane-departure"></i> En cours
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="listing.php?id=<?php echo $voyage['id_annonce']; ?>"
                                               class="btn btn-outline-primary"
                                               title="Voir l'annonce">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($is_termine): ?>
                                                <a href="reviews.php?reservation=<?php echo $voyage['id_reservation']; ?>"
                                                   class="btn btn-outline-warning"
                                                   title="Laisser un avis">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Suggestions -->
    <?php if (!empty($voyages)): ?>
        <div class="alert alert-info mt-4">
            <h5><i class="fas fa-lightbulb"></i> Prochaine destination ?</h5>
            <p class="mb-2">Basé sur vos voyages précédents, vous pourriez aimer :</p>
            <a href="search.php?galaxie=<?php echo urlencode($voyages[0]['galaxie']); ?>" class="btn btn-sm btn-info">
                <i class="fas fa-search"></i> Plus de logements dans <?php echo htmlspecialchars($voyages[0]['galaxie']); ?>
            </a>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
