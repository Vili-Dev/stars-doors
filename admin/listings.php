<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

$title = 'Gestion des annonces - Administration';

include '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Stars Doors</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Admin</a>
            <a class="nav-link" href="../dashboard.php">Retour au site</a>
            <a class="nav-link" href="../logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <h1>Gestion des annonces</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Annonces</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                La gestion complète des annonces sera développée prochainement.
                <br>Fonctionnalités prévues :
                <ul class="mt-2 mb-0">
                    <li>Liste et recherche d'annonces</li>
                    <li>Modération et validation</li>
                    <li>Suspension d'annonces</li>
                    <li>Gestion des photos</li>
                    <li>Statistiques de performance</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>