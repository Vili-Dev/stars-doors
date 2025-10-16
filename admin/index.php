<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

$title = 'Administration - Stars Doors';

// Statistiques rapides
try {
    // Total utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE actif = 1");
    $total_users = $stmt->fetchColumn();
    
    // Total annonces
    $stmt = $pdo->query("SELECT COUNT(*) FROM annonces WHERE disponible = 1");
    $total_listings = $stmt->fetchColumn();
    
    // Total réservations
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
    $total_reservations = $stmt->fetchColumn();
    
    // Réservations en attente
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente'");
    $pending_reservations = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    error_log("Erreur stats admin: " . $e->getMessage());
    $total_users = $total_listings = $total_reservations = $pending_reservations = 0;
}

include '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Stars Doors</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="../dashboard.php">Retour au site</a>
            <a class="nav-link" href="../logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <h1>Administration</h1>
            <p class="text-muted">Tableau de bord administrateur</p>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo number_format($total_users); ?></h4>
                            <p class="mb-0">Utilisateurs actifs</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo number_format($total_listings); ?></h4>
                            <p class="mb-0">Annonces actives</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-home fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo number_format($total_reservations); ?></h4>
                            <p class="mb-0">Réservations totales</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?php echo number_format($pending_reservations); ?></h4>
                            <p class="mb-0">En attente</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu de navigation -->
    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>Gestion des utilisateurs</h5>
                    <p class="text-muted">Gérer les comptes utilisateurs, rôles et permissions</p>
                    <a href="users.php" class="btn btn-primary">Accéder</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-home fa-3x text-success mb-3"></i>
                    <h5>Gestion des annonces</h5>
                    <p class="text-muted">Modérer et gérer les annonces de logements</p>
                    <a href="annonces.php" class="btn btn-success">Accéder</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-star-fill fa-3x text-secondary mb-3" style="color:#FFC107 !important"></i>
                    <h5>Avis</h5>
                    <p class="text-muted">Modérer et gérer les avis</p>
                    <a href="avis.php" class="btn btn-secondary" style="background:#FFC107 !important;border:1px solid #FFC107 ">Accéder</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-list-check fa-3x text-secondary mb-3" style="color:red !important"></i>
                    <h5>Litiges</h5>
                    <p class="text-muted">Gérer les litiges</p>
                    <a href="litige.php" class="btn btn-secondary" style="background:red !important;border:1px solid red ">Accéder</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Activité récente</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Les logs d'activité seront affichés ici dans une version future.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>