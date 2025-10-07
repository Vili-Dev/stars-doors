<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$title = 'À propos - Stars Doors';
$current_page = 'about';

// Statistiques globales
try {
    $stats = [];

    // Nombre de planètes
    $stmt = $pdo->query("SELECT COUNT(*) FROM planetes WHERE statut = 'active'");
    $stats['planetes'] = $stmt->fetchColumn();

    // Nombre de races
    $stmt = $pdo->query("SELECT COUNT(*) FROM races");
    $stats['races'] = $stmt->fetchColumn();

    // Nombre d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE actif = 1");
    $stats['users'] = $stmt->fetchColumn();

    // Nombre d'annonces
    $stmt = $pdo->query("SELECT COUNT(*) FROM annonces WHERE disponible = 1");
    $stats['annonces'] = $stmt->fetchColumn();

    // Nombre de réservations
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut IN ('confirmee', 'terminee')");
    $stats['reservations'] = $stmt->fetchColumn();

    // Nombre de galaxies
    $stmt = $pdo->query("SELECT COUNT(DISTINCT galaxie) FROM planetes");
    $stats['galaxies'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    $stats = [
        'planetes' => 0,
        'races' => 0,
        'users' => 0,
        'annonces' => 0,
        'reservations' => 0,
        'galaxies' => 0
    ];
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main>
    <!-- Hero Section -->
    <div class="bg-primary text-white py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-3 fw-bold mb-4">
                        <i class="fas fa-star"></i> Stars Doors
                    </h1>
                    <p class="lead mb-4">
                        La première plateforme intergalactique de réservation de logements.
                        Voyagez entre les planètes, découvrez de nouvelles races et vivez des expériences uniques à travers la galaxie.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket"></i> Commencer l'aventure
                        </a>
                        <a href="planetes.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-globe"></i> Explorer les planètes
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-globe-americas" style="font-size: 15rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Notre Galaxie en Chiffres</h2>
            <div class="row text-center">
                <div class="col-md-2 col-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-globe text-primary" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-3 mb-0"><?php echo number_format($stats['planetes']); ?></h3>
                            <p class="text-muted mb-0">Planètes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-dharmachakra text-success" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-3 mb-0"><?php echo number_format($stats['galaxies']); ?></h3>
                            <p class="text-muted mb-0">Galaxies</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-users text-info" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-3 mb-0"><?php echo number_format($stats['races']); ?></h3>
                            <p class="text-muted mb-0">Races</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-user-astronaut text-warning" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-3 mb-0"><?php echo number_format($stats['users']); ?></h3>
                            <p class="text-muted mb-0">Voyageurs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-home text-danger" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-3 mb-0"><?php echo number_format($stats['annonces']); ?></h3>
                            <p class="text-muted mb-0">Logements</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="fas fa-calendar-check text-purple" style="font-size: 2.5rem;"></i>
                            <h3 class="mt-3 mb-0"><?php echo number_format($stats['reservations']); ?></h3>
                            <p class="text-muted mb-0">Réservations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission -->
    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Notre Mission</h2>
                <p class="lead">
                    Stars Doors a pour mission de connecter les voyageurs de toute la galaxie et de faciliter
                    les échanges culturels entre les différentes races.
                </p>
                <p>
                    Nous croyons que le voyage spatial ne devrait pas se limiter aux élites. C'est pourquoi nous
                    offrons une plateforme accessible à tous, permettant de découvrir des milliers de planètes
                    et de vivre des expériences authentiques auprès des habitants locaux.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <h4 class="mb-4"><i class="fas fa-rocket text-primary"></i> Nos Valeurs</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Diversité</strong> - Célébrer toutes les formes de vie
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Sécurité</strong> - Protection des voyageurs et hôtes
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Accessibilité</strong> - Voyage pour tous les budgets
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Authenticité</strong> - Expériences locales uniques
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Innovation</strong> - Technologies de pointe
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fonctionnalités -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Comment ça marche ?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <i class="fas fa-user-plus text-primary" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="mb-3">1. Inscrivez-vous</h4>
                            <p class="text-muted">
                                Créez votre compte en quelques clics. Choisissez votre race et votre planète d'origine.
                                L'inscription est gratuite et rapide.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <i class="fas fa-search text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="mb-3">2. Explorez</h4>
                            <p class="text-muted">
                                Parcourez des milliers de logements à travers la galaxie. Filtrez par planète,
                                atmosphère, gravité et équipements spatiaux.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="mb-4">
                                <i class="fas fa-calendar-check text-info" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="mb-3">3. Réservez</h4>
                            <p class="text-muted">
                                Réservez en toute sécurité avec notre système de paiement intergalactique.
                                Confirmation instantanée et support 24/7.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technologies -->
    <div class="container py-5">
        <h2 class="text-center mb-5">Technologies de Pointe</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="fas fa-shield-alt text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5>Sécurité Quantique</h5>
                        <p class="text-muted">
                            Nos systèmes utilisent le cryptage quantique pour protéger vos données
                            et transactions à travers l'espace-temps.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="fas fa-language text-success" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5>Traduction Universelle</h5>
                        <p class="text-muted">
                            Communiquez avec n'importe quelle race grâce à notre système de traduction
                            en temps réel intégré.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="fas fa-satellite text-info" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5>Navigation Galactique</h5>
                        <p class="text-muted">
                            Calcul automatique des routes spatiales optimales et estimations précises
                            des temps de voyage.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="fas fa-heart text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5>Adaptation Biologique</h5>
                        <p class="text-muted">
                            Recommandations personnalisées basées sur votre race et vos besoins
                            physiologiques spécifiques.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-primary text-white py-5">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Prêt à explorer la galaxie ?</h2>
            <p class="lead mb-4">
                Rejoignez des milliers de voyageurs et commencez votre aventure intergalactique dès aujourd'hui.
            </p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="register.php" class="btn btn-light btn-lg">
                    <i class="fas fa-rocket"></i> S'inscrire gratuitement
                </a>
                <a href="planetes.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-globe"></i> Explorer les planètes
                </a>
                <a href="races.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-users"></i> Découvrir les races
                </a>
            </div>
        </div>
    </div>

    <!-- Contact -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">Besoin d'aide ?</h2>
                <p class="lead text-muted mb-4">
                    Notre équipe de support intergalactique est disponible 24/7 pour répondre à vos questions.
                </p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <i class="fas fa-envelope text-primary" style="font-size: 2rem;"></i>
                                <h5 class="mt-3">Email</h5>
                                <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="text-decoration-none">
                                    <?php echo ADMIN_EMAIL; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <i class="fas fa-comments text-success" style="font-size: 2rem;"></i>
                                <h5 class="mt-3">Messages</h5>
                                <?php if (isLoggedIn()): ?>
                                    <a href="messages.php" class="btn btn-success btn-sm">Accéder</a>
                                <?php else: ?>
                                    <p class="text-muted mb-0 small">Connectez-vous pour envoyer un message</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
