<?php
/**
 * Composant : Carte de race
 * Affiche les informations d'une race de manière cohérente
 *
 * Variables requises :
 * @var array $race - Données de la race
 */

// Labels pour l'affichage
$tech_labels = [
    'primitif' => 'Primitif',
    'moderne' => 'Moderne',
    'avance' => 'Avancé',
    'futuriste' => 'Futuriste'
];

$social_labels = [
    'solitaire' => 'Solitaire',
    'normale' => 'Normale',
    'tres_sociale' => 'Très sociale'
];
?>

<div class="card h-100 shadow-sm hover-lift">
    <div class="card-body">
        <!-- En-tête avec nom et niveau technologique -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-user-astronaut text-primary"></i>
                <?php echo htmlspecialchars($race['nom']); ?>
            </h5>
            <span class="badge bg-info">
                <?php echo $tech_labels[$race['niveau_technologie']] ?? $race['niveau_technologie']; ?>
            </span>
        </div>

        <!-- Description -->
        <p class="card-text small text-muted mb-3">
            <?php echo htmlspecialchars($race['description'] ?? 'Description non disponible'); ?>
        </p>

        <hr>

        <!-- Caractéristiques -->
        <div class="mb-2">
            <strong><i class="fas fa-dna"></i> Caractéristiques :</strong>
            <p class="small mb-0"><?php echo htmlspecialchars($race['caracteristiques'] ?? 'N/A'); ?></p>
        </div>

        <!-- Besoins spéciaux -->
        <div class="mb-2">
            <strong><i class="fas fa-wind"></i> Besoins spéciaux :</strong>
            <p class="small mb-0"><?php echo htmlspecialchars($race['besoins_speciaux'] ?? 'Aucun'); ?></p>
        </div>

        <!-- Atmosphère préférée -->
        <?php if ($race['atmosphere_preferee']): ?>
        <div class="mb-2">
            <strong><i class="fas fa-cloud"></i> Atmosphère préférée :</strong>
            <span class="badge bg-info"><?php echo ucfirst($race['atmosphere_preferee']); ?></span>
        </div>
        <?php endif; ?>

        <hr>

        <!-- Planète d'origine et nombre d'utilisateurs -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <?php if (!empty($race['planete_origine_nom'])): ?>
                <span class="badge bg-secondary">
                    <i class="fas fa-home"></i> <?php echo htmlspecialchars($race['planete_origine_nom']); ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <small class="text-muted">
                    <i class="fas fa-users"></i> <?php echo number_format($race['nb_utilisateurs'] ?? 0); ?> membres
                </small>
            </div>
        </div>

        <!-- Sociabilité -->
        <div class="mt-2">
            <small class="text-muted">
                <i class="fas fa-heart"></i>
                Sociabilité: <?php echo $social_labels[$race['sociabilite']] ?? $race['sociabilite']; ?>
            </small>
        </div>

        <!-- Langue -->
        <?php if (!empty($race['langue_principale'])): ?>
        <div class="mt-2">
            <small class="text-primary">
                <i class="fas fa-language"></i>
                Langue: <?php echo htmlspecialchars($race['langue_principale']); ?>
            </small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer avec statistiques -->
    <div class="card-footer bg-light">
        <div class="row text-center small">
            <div class="col">
                <strong><?php echo $race['esperance_vie'] ?? 'N/A'; ?></strong><br>
                <span class="text-muted">Espérance vie (ans)</span>
            </div>
            <div class="col">
                <strong><?php echo $race['taille_moyenne'] ? number_format($race['taille_moyenne'], 2) . 'm' : 'N/A'; ?></strong><br>
                <span class="text-muted">Taille moyenne</span>
            </div>
        </div>
    </div>
</div>
