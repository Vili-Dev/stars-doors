<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/transport.php';
require_once 'includes/currency.php';

$title = 'Transport Spatial - Stars Doors';
$current_page = 'transport';

// Paramètres du voyage
$id_annonce = filter_input(INPUT_GET, 'annonce', FILTER_VALIDATE_INT);
$id_planete_depart = filter_input(INPUT_GET, 'depart', FILTER_VALIDATE_INT) ?? (isset($_SESSION['user_id']) ? getUserPlaneteResidence($_SESSION['user_id']) : 1);

if (!$id_annonce) {
    redirect('index.php');
}

// Récupérer l'annonce
try {
    $stmt = $pdo->prepare("
        SELECT a.*, p.id_planete, p.nom as planete_nom, p.distance_terre, p.galaxie, p.atmosphere
        FROM annonces a
        INNER JOIN planetes p ON a.id_planete = p.id_planete
        WHERE a.id_annonce = ? AND a.disponible = 1
    ");
    $stmt->execute([$id_annonce]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$annonce) {
        redirect('index.php');
    }
} catch (PDOException $e) {
    error_log("Erreur récupération annonce: " . $e->getMessage());
    redirect('index.php');
}

$id_planete_arrivee = $annonce['id_planete'];

// Récupérer la race de l'utilisateur
$id_race = 1; // Humain par défaut
$user_id = null;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id_race, planete_residence FROM users WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $id_race = $user['id_race'];
        if ($user['planete_residence']) {
            $id_planete_depart = $user['planete_residence'];
        }
    }
}

// Récupérer les vaisseaux disponibles
$vaisseaux = getVaisseaux();

// Durée estimée du séjour (pour calcul adaptation)
$duree_sejour = filter_input(INPUT_GET, 'duree', FILTER_VALIDATE_INT) ?? 7;

// Calculs pour chaque vaisseau
$voyages_details = [];
foreach ($vaisseaux as $v) {
    $details = calculerVoyage($id_planete_depart, $id_planete_arrivee, $v['id_vaisseau'], $id_race, $duree_sejour);
    if ($details) {
        $voyages_details[$v['id_vaisseau']] = $details;
    }
}

// Vérifier compatibilité
$compatibilite = verifierCompatibilite($id_race, $id_planete_arrivee);

// Fonctions helper
function getUserPlaneteResidence($user_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT planete_residence FROM users WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['planete_residence'] ?? 1;
    } catch (PDOException $e) {
        return 1;
    }
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-5">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold mb-3">
                <i class="fas fa-rocket text-primary"></i> Planifiez votre Voyage Spatial
            </h1>
            <p class="lead text-muted">
                Sélectionnez votre vaisseau pour voyager vers
                <strong><?php echo htmlspecialchars($annonce['planete_nom']); ?></strong>
            </p>
        </div>
        <div class="col-lg-4">
            <?php if (isLoggedIn()): ?>
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Votre solde</h6>
                        <h3 class="text-primary mb-0">
                            <?php echo formatMontant(getSolde($user_id)); ?>
                        </h3>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alerte compatibilité -->
    <?php if ($compatibilite): ?>
        <?php if ($compatibilite['niveau_compatibilite'] === 'mortel' || $compatibilite['niveau_compatibilite'] === 'hostile'): ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Avertissement de Sécurité</h5>
                <p class="mb-2">
                    <strong>Niveau de compatibilité :</strong>
                    <?php echo afficherBadgeCompatibilite($compatibilite['niveau_compatibilite']); ?>
                </p>
                <p class="mb-2"><strong>Risques :</strong> <?php echo htmlspecialchars($compatibilite['risques']); ?></p>
                <p class="mb-0"><strong>Recommandations :</strong> <?php echo htmlspecialchars($compatibilite['recommandations']); ?></p>
            </div>
        <?php elseif ($compatibilite['niveau_compatibilite'] === 'adaptable'): ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-info-circle"></i> Équipement Requis</h5>
                <p class="mb-2">
                    Votre race nécessite des équipements d'adaptation pour séjourner sur cette planète.
                </p>
                <p class="mb-0">
                    <strong>Coût d'adaptation :</strong> <?php echo formatMontant($compatibilite['cout_adaptation_journalier']); ?> / jour
                    (Total: <?php echo formatMontant($compatibilite['cout_adaptation_journalier'] * $duree_sejour); ?>)
                </p>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Votre race est <?php echo afficherBadgeCompatibilite($compatibilite['niveau_compatibilite']); ?> avec cette planète.
                Aucun équipement spécial requis.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-plane-departure"></i> Planète de départ</label>
                    <select class="form-select" id="planete_depart" disabled>
                        <option>Chargement...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Durée du séjour</label>
                    <select class="form-select" id="duree_sejour">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $duree_sejour == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?> jour<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-filter"></i> Classe de voyage</label>
                    <select class="form-select" id="filtre_classe">
                        <option value="">Toutes les classes</option>
                        <option value="economique">Économique</option>
                        <option value="business">Business</option>
                        <option value="premiere_classe">Première Classe</option>
                        <option value="luxe">Luxe</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des vaisseaux -->
    <h3 class="mb-3"><i class="fas fa-space-shuttle"></i> Vaisseaux Disponibles</h3>

    <div id="liste-vaisseaux">
        <?php if (empty($voyages_details)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucun vaisseau disponible pour ce trajet.
            </div>
        <?php else: ?>
            <?php foreach ($vaisseaux as $v): ?>
                <?php
                $details = $voyages_details[$v['id_vaisseau']] ?? null;
                if (!$details) continue;

                $equipements = json_decode($v['equipements'] ?? '[]', true);
                $classe_badge = [
                    'economique' => 'secondary',
                    'business' => 'primary',
                    'premiere_classe' => 'warning',
                    'cargo' => 'dark',
                    'luxe' => 'danger'
                ];
                ?>

                <div class="card mb-4 shadow-sm vaisseau-card" data-classe="<?php echo $v['type']; ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Info vaisseau -->
                            <div class="col-lg-4">
                                <h4 class="mb-2"><?php echo htmlspecialchars($v['nom']); ?></h4>
                                <span class="badge bg-<?php echo $classe_badge[$v['type']] ?? 'secondary'; ?> mb-2">
                                    <?php echo ucfirst(str_replace('_', ' ', $v['type'])); ?>
                                </span>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($v['constructeur']); ?>
                                </p>
                                <div class="mb-2">
                                    <small class="text-muted">Confort: </small>
                                    <?php for ($i = 0; $i < $v['confort_score']; $i++): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Specs voyage -->
                            <div class="col-lg-4">
                                <h6 class="text-primary mb-3">Détails du voyage</h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <i class="fas fa-route text-muted"></i>
                                        <strong>Distance:</strong> <?php echo number_format($details['distance_al'], 2); ?> AL
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-clock text-muted"></i>
                                        <strong>Durée:</strong> <?php echo number_format($details['duree_jours'], 1); ?> jours
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-tachometer-alt text-muted"></i>
                                        <strong>Vitesse:</strong> <?php echo $v['vitesse_lumiere']; ?>c
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-users text-muted"></i>
                                        <strong>Capacité:</strong> <?php echo $v['capacite_passagers']; ?> passagers
                                    </li>
                                </ul>
                            </div>

                            <!-- Prix et action -->
                            <div class="col-lg-4 text-center">
                                <div class="pricing-box p-3 bg-light rounded">
                                    <h6 class="text-muted">Prix du transport</h6>
                                    <h2 class="text-primary mb-0"><?php echo formatMontant($details['cout_transport']); ?></h2>

                                    <?php if ($details['cout_adaptation'] > 0): ?>
                                        <small class="text-muted d-block mt-2">
                                            + <?php echo formatMontant($details['cout_adaptation']); ?> (adaptation)
                                        </small>
                                        <hr>
                                        <h5 class="text-success">
                                            Total: <?php echo formatMontant($details['cout_total']); ?>
                                        </h5>
                                    <?php endif; ?>

                                    <?php if (isLoggedIn()): ?>
                                        <a href="booking_confirm.php?annonce=<?php echo $id_annonce; ?>&vaisseau=<?php echo $v['id_vaisseau']; ?>&depart=<?php echo $id_planete_depart; ?>&duree=<?php echo $duree_sejour; ?>"
                                           class="btn btn-primary btn-lg w-100 mt-3">
                                            <i class="fas fa-check-circle"></i> Sélectionner
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                                           class="btn btn-outline-primary w-100 mt-3">
                                            <i class="fas fa-sign-in-alt"></i> Connexion requise
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Équipements (collapsible) -->
                        <?php if (!empty($equipements)): ?>
                            <hr>
                            <div>
                                <a class="text-decoration-none" data-bs-toggle="collapse" href="#equipements-<?php echo $v['id_vaisseau']; ?>">
                                    <i class="fas fa-list"></i> Voir les équipements inclus
                                    <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="collapse mt-2" id="equipements-<?php echo $v['id_vaisseau']; ?>">
                                    <div class="row">
                                        <?php foreach ($equipements as $eq): ?>
                                            <div class="col-md-4">
                                                <small><i class="fas fa-check text-success"></i> <?php echo htmlspecialchars($eq); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Trajets populaires -->
    <div class="card mt-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-fire"></i> Trajets Populaires</h5>
        </div>
        <div class="card-body">
            <?php
            $trajets_pop = getTrajetsPopulaires(5);
            if (!empty($trajets_pop)):
            ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($trajets_pop as $trajet): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-route text-primary"></i>
                                    <strong><?php echo htmlspecialchars($trajet['depart']); ?></strong>
                                    →
                                    <strong><?php echo htmlspecialchars($trajet['arrivee']); ?></strong>
                                </div>
                                <div>
                                    <span class="badge bg-info"><?php echo $trajet['nb_voyages']; ?> voyages</span>
                                    <small class="text-muted">
                                        Moy: <?php echo formatMontant($trajet['prix_moyen']); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Filtrage par classe
document.getElementById('filtre_classe').addEventListener('change', function() {
    const classe = this.value;
    document.querySelectorAll('.vaisseau-card').forEach(card => {
        if (classe === '' || card.dataset.classe === classe) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

// Rechargement avec durée différente
document.getElementById('duree_sejour').addEventListener('change', function() {
    const url = new URL(window.location.href);
    url.searchParams.set('duree', this.value);
    window.location.href = url.toString();
});
</script>

<?php include 'includes/footer.php'; ?>
