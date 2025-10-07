<?php
// S'assurer que auth.php est chargé pour les fonctions de vérification
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/auth.php';
}
?>
<!-- Navigation mise à jour - Version finale sans Races/Covoiturage/Fidélité -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-primary" href="index.php">
            <i class="fas fa-star"></i> Stars Doors
        </a>
        
        <!-- Toggle button pour mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation principale -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page ?? '') === 'home' ? 'active' : ''; ?>"
                       href="index.php">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page ?? '') === 'search' ? 'active' : ''; ?>"
                       href="search.php">
                        <i class="fas fa-search"></i> Rechercher
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page ?? '') === 'planetes' ? 'active' : ''; ?>"
                       href="planetes.php">
                        <i class="fas fa-globe"></i> Planètes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page ?? '') === 'about' ? 'active' : ''; ?>"
                       href="about.php">
                        <i class="fas fa-info-circle"></i> À propos
                    </a>
                </li>
                <?php if (function_exists('isProprietaire') && isProprietaire()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="proprietaireDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-building"></i> Propriétaire
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="proprietaireDropdown">
                        <li><a class="dropdown-item" href="create_listing.php">
                            <i class="fas fa-plus"></i> Ajouter une annonce
                        </a></li>
                        <li><a class="dropdown-item" href="dashboard.php?section=mes-annonces">
                            <i class="fas fa-list"></i> Mes annonces
                        </a></li>
                        <li><a class="dropdown-item" href="dashboard.php?section=reservations-recues">
                            <i class="fas fa-calendar-check"></i> Réservations reçues
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Navigation utilisateur -->
            <ul class="navbar-nav">
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <!-- Utilisateur connecté -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> 
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Mon compte'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Tableau de bord
                            </a></li>
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user"></i> Mon profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="booking.php">
                                <i class="fas fa-calendar-check"></i> Mes réservations
                            </a></li>
                            <li><a class="dropdown-item" href="voyages.php">
                                <i class="fas fa-rocket"></i> Mes voyages
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="messages.php">
                                <i class="fas fa-envelope"></i> Messages
                                <?php
                                // TODO: Afficher le nombre de messages non lus
                                // $unread_count = getUnreadMessagesCount();
                                // if ($unread_count > 0) echo '<span class="badge bg-danger ms-1">'.$unread_count.'</span>';
                                ?>
                            </a></li>
                            <li><a class="dropdown-item" href="favorites.php">
                                <i class="fas fa-heart"></i> Mes favoris
                            </a></li>
                            <li><a class="dropdown-item" href="reviews.php">
                                <i class="fas fa-star"></i> Mes avis
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (function_exists('isAdmin') && isAdmin()): ?>
                            <li><a class="dropdown-item" href="admin/">
                                <i class="fas fa-cog"></i> Administration
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Utilisateur non connecté -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-2" href="register.php">
                            <i class="fas fa-user-plus"></i> Inscription
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Barre de notifications (si nécessaire) -->
<?php if (function_exists('hasMaintenanceMode') && hasMaintenanceMode()): ?>
<div class="alert alert-warning alert-dismissible mb-0 text-center" role="alert">
    <i class="fas fa-exclamation-triangle"></i> 
    Maintenance programmée le [DATE]. Certaines fonctionnalités peuvent être temporairement indisponibles.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Conteneur principal avec ID pour l'accessibilité -->
<div id="main-content"><?php // Fermé dans footer.php ?>