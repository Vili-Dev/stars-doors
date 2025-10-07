<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$title = 'Favoris - Stars Doors';
$current_page = 'favorites';

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-4">
    <h1>Mes favoris</h1>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        Cette fonctionnalité sera développée prochainement.
    </div>
    <a href="dashboard.php" class="btn btn-secondary">Retour au tableau de bord</a>
</main>

<?php include 'includes/footer.php'; ?>