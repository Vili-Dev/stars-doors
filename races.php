<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'app/Models/Race.php';

$title = 'Les Races Galactiques - Stars Doors';
$current_page = 'races';

// Initialiser le modèle
$raceModel = new Race($pdo);

// Récupérer les filtres
$filters = [
    'sociabilite' => $_GET['sociabilite'] ?? '',
    'technologie' => $_GET['technologie'] ?? ''
];

// Récupérer les races avec filtres
$races = $raceModel->getAll(array_filter($filters));

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-5">
    <!-- En-tête -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold mb-3">
            <i class="fas fa-users text-primary"></i> Les Races Galactiques
        </h1>
        <p class="lead text-muted">Découvrez les différentes races qui peuplent notre galaxie</p>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-comment-dots"></i> Sociabilité</label>
                    <select name="sociabilite" class="form-select">
                        <option value="">Toutes</option>
                        <option value="solitaire" <?php echo $filters['sociabilite'] === 'solitaire' ? 'selected' : ''; ?>>Solitaire</option>
                        <option value="normale" <?php echo $filters['sociabilite'] === 'normale' ? 'selected' : ''; ?>>Normale</option>
                        <option value="sociale" <?php echo $filters['sociabilite'] === 'sociale' ? 'selected' : ''; ?>>Sociale</option>
                        <option value="tres_sociale" <?php echo $filters['sociabilite'] === 'tres_sociale' ? 'selected' : ''; ?>>Très sociale</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-microchip"></i> Technologie</label>
                    <select name="technologie" class="form-select">
                        <option value="">Toutes</option>
                        <option value="primitif" <?php echo $filters['technologie'] === 'primitif' ? 'selected' : ''; ?>>Primitif</option>
                        <option value="moderne" <?php echo $filters['technologie'] === 'moderne' ? 'selected' : ''; ?>>Moderne</option>
                        <option value="avance" <?php echo $filters['technologie'] === 'avance' ? 'selected' : ''; ?>>Avancé</option>
                        <option value="futuriste" <?php echo $filters['technologie'] === 'futuriste' ? 'selected' : ''; ?>>Futuriste</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h3 class="text-primary"><?php echo count($races); ?></h3>
                    <p class="mb-0 text-muted">Races répertoriées</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success">
                        <?php
                        $total_users = array_sum(array_column($races, 'nb_utilisateurs'));
                        echo number_format($total_users);
                        ?>
                    </h3>
                    <p class="mb-0 text-muted">Utilisateurs inscrits</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h3 class="text-info">
                        <?php
                        $galaxies_count = count(array_unique(array_column($races, 'id_planete_origine')));
                        echo $galaxies_count;
                        ?>
                    </h3>
                    <p class="mb-0 text-muted">Planètes d'origine</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="text-warning">
                        <?php
                        $futuriste_count = count(array_filter($races, fn($r) => $r['niveau_technologie'] === 'futuriste'));
                        echo $futuriste_count;
                        ?>
                    </h3>
                    <p class="mb-0 text-muted">Races futuristes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des races -->
    <?php if (empty($races)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> Aucune race ne correspond à vos critères.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($races as $race): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <?php include 'views/partials/race-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<style>
.hover-lift {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<?php include 'includes/footer.php'; ?>
