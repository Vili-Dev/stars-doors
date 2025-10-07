<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/fidelite.php';

$title = 'Programme de Fidélité - Stars Doors';
$current_page = 'fidelite';

// Récupérer les niveaux du programme
try {
    $stmt = $pdo->query("SELECT * FROM programmes_fidelite ORDER BY points_minimum ASC");
    $niveaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $niveaux = [];
}

// Info fidélité utilisateur si connecté
$fidelite_user = null;
if (isLoggedIn()) {
    $fidelite_user = getFideliteUser($_SESSION['user_id']);
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold mb-3">
            <i class="fas fa-gem text-primary"></i> Programme Miles Galactiques
        </h1>
        <p class="lead text-muted">
            Voyagez plus, gagnez plus, profitez d'avantages exclusifs à travers la galaxie
        </p>
    </div>

    <!-- Mon Niveau (si connecté) -->
    <?php if ($fidelite_user): ?>
        <div class="card shadow-lg mb-5" style="border: 3px solid #FFD700;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <?php echo afficherBadgeFidelite($fidelite_user['niveau_fidelite']); ?>
                        <h2 class="mt-3 mb-0"><?php echo ucfirst($fidelite_user['niveau_fidelite']); ?></h2>
                    </div>
                    <div class="col-md-6">
                        <h5>Vos Miles Galactiques</h5>
                        <h1 class="text-primary mb-3"><?php echo number_format($fidelite_user['total_points_fidelite']); ?> points</h1>

                        <?php
                        // Prochain niveau
                        $niveau_actuel_idx = array_search($fidelite_user['niveau_fidelite'], array_column($niveaux, 'niveau'));
                        if ($niveau_actuel_idx !== false && isset($niveaux[$niveau_actuel_idx + 1])) {
                            $prochain_niveau = $niveaux[$niveau_actuel_idx + 1];
                            $points_manquants = $prochain_niveau['points_minimum'] - $fidelite_user['total_points_fidelite'];
                            $progression = ($fidelite_user['total_points_fidelite'] / $prochain_niveau['points_minimum']) * 100;
                        ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    Plus que <?php echo number_format($points_manquants); ?> points pour atteindre
                                    <strong><?php echo ucfirst($prochain_niveau['niveau']); ?></strong>
                                </small>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo min($progression, 100); ?>%"></div>
                                </div>
                            </div>
                        <?php } else: ?>
                            <p class="text-success"><i class="fas fa-crown"></i> Niveau maximum atteint !</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3 text-center">
                        <h6>Vos Avantages</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge bg-success">
                                    -<?php echo $fidelite_user['reduction_pourcentage']; ?>% sur réservations
                                </span>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-info">
                                    x<?php echo $fidelite_user['multiplicateur_points']; ?> points bonus
                                </span>
                            </li>
                            <?php if ($fidelite_user['acces_lounge_spatial']): ?>
                                <li class="mb-2">
                                    <span class="badge bg-warning">
                                        <i class="fas fa-couch"></i> Accès Lounge VIP
                                    </span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Code Parrainage -->
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-gift"></i> Votre Code Parrainage</h6>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($fidelite_user['code_parrainage']); ?>" readonly id="codeParrainage">
                            <button class="btn btn-outline-primary" onclick="copierCode()">
                                <i class="fas fa-copy"></i> Copier
                            </button>
                        </div>
                        <small class="text-muted">Partagez ce code et gagnez 500 points par filleul !</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Niveaux du Programme -->
    <h2 class="text-center mb-4">
        <i class="fas fa-layer-group"></i> Les Niveaux du Programme
    </h2>

    <div class="row">
        <?php foreach ($niveaux as $idx => $niveau): ?>
            <?php
            $colors = [
                'bronze' => '#CD7F32',
                'silver' => '#C0C0C0',
                'gold' => '#FFD700',
                'platinum' => '#E5E4E2',
                'diamond' => '#b9f2ff'
            ];
            $color = $colors[$niveau['niveau']] ?? '#6c757d';
            $is_current = (isset($fidelite_user) && $fidelite_user['niveau_fidelite'] === $niveau['niveau']);
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 <?php echo $is_current ? 'border-primary' : ''; ?>" style="border-width: <?php echo $is_current ? '3px' : '1px'; ?>;">
                    <div class="card-header text-white text-center" style="background: <?php echo $color; ?>;">
                        <h4 class="mb-0">
                            <?php if ($niveau['niveau'] === 'diamond'): ?>
                                <i class="fas fa-gem"></i>
                            <?php else: ?>
                                <i class="fas fa-medal"></i>
                            <?php endif; ?>
                            <?php echo ucfirst($niveau['niveau']); ?>
                        </h4>
                        <?php if ($is_current): ?>
                            <small class="badge bg-light text-dark">Votre niveau actuel</small>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="text-center mb-3">
                            <?php echo number_format($niveau['points_minimum']); ?>+ points
                        </h5>

                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-percent text-success"></i>
                                <strong><?php echo $niveau['reduction_pourcentage']; ?>%</strong> de réduction
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-star text-warning"></i>
                                <strong>x<?php echo $niveau['multiplicateur_points']; ?></strong> points bonus
                            </li>
                            <?php if ($niveau['acces_lounge_spatial']): ?>
                                <li class="mb-2">
                                    <i class="fas fa-couch text-primary"></i>
                                    Accès Lounge VIP
                                </li>
                            <?php endif; ?>
                            <?php if ($niveau['upgrade_gratuit']): ?>
                                <li class="mb-2">
                                    <i class="fas fa-arrow-up text-info"></i>
                                    Surclassement gratuit
                                </li>
                            <?php endif; ?>
                            <?php if ($niveau['support_prioritaire']): ?>
                                <li class="mb-2">
                                    <i class="fas fa-headset text-danger"></i>
                                    Support 24/7 prioritaire
                                </li>
                            <?php endif; ?>
                        </ul>

                        <?php
                        $badges = json_decode($niveau['badges_speciaux'] ?? '[]', true);
                        if (!empty($badges)):
                        ?>
                            <hr>
                            <h6 class="text-muted">Badges :</h6>
                            <?php foreach ($badges as $badge): ?>
                                <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($badge); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Comment gagner des points -->
    <div class="card mt-5 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-trophy"></i> Comment gagner des Miles Galactiques ?</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="text-center p-3 bg-light rounded">
                        <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                        <h5>Réservations</h5>
                        <p class="text-muted mb-0">1 point par CRG dépensé</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center p-3 bg-light rounded">
                        <i class="fas fa-rocket fa-3x text-success mb-3"></i>
                        <h5>Voyages Complétés</h5>
                        <p class="text-muted mb-0">Bonus selon destination</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="text-center p-3 bg-light rounded">
                        <i class="fas fa-user-friends fa-3x text-warning mb-3"></i>
                        <h5>Parrainage</h5>
                        <p class="text-muted mb-0">500 points par filleul</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <?php if (!isLoggedIn()): ?>
        <div class="text-center mt-5 p-5 bg-light rounded">
            <h3 class="mb-3">Prêt à accumuler des Miles Galactiques ?</h3>
            <p class="lead text-muted mb-4">Inscrivez-vous maintenant et recevez 200 points de bienvenue !</p>
            <a href="register.php" class="btn btn-primary btn-lg">
                <i class="fas fa-rocket"></i> S'inscrire Gratuitement
            </a>
        </div>
    <?php endif; ?>
</main>

<script>
function copierCode() {
    const code = document.getElementById('codeParrainage');
    code.select();
    document.execCommand('copy');

    // Feedback visuel
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copié !';
    setTimeout(() => {
        btn.innerHTML = originalText;
    }, 2000);
}
</script>

<?php include 'includes/footer.php'; ?>
