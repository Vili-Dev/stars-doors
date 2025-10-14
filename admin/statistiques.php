<?php
session_start(); // نفتح الدفتر / On ouvre le cahier

// نقرأو من "الدفتر"
// On lit depuis le "cahier"
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

$title = 'Statistiques - Administration';

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





