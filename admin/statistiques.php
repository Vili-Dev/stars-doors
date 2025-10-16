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

// 1. Nombre d'utilisateurs
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();
$title = 'Statistiques - Administration';



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

<main class="container py-4">
    <h1>Statistiques</h1>

    <div class="row mb-4">
           <!-- البطاقة 1 : Total Utilisateurs -->
          <div class="col-md-3">
            <div class="card text-center">
                 <div class="card-body">
                    <h3 class="text-primary">0</h3>
                      <p class="mb-0"><?echo $total_useres;?></p>
   </div>
</div>
</div>
            <!-- البطاقة 2 : Total Annonces -->
             <div class="col-md-3">
             <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">0</h3>
                    <p class="mb-0">Total Annonces</p>
</div>
</div>
</div>
                <!-- البطاقة 3 : Total Avis -->
                <div class="col-md-3">
                <div class="card text-center">
                <div class="card-body">
                <h3 class="text-warning">0</h3>
                  <p class="mb-0">Total Avis</p>
                 </div>
                </div>
                </div>
            
                 <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger">0</h3>
                            <p class="mb-0">Annonces en attente</p>
                </div>
                    </div>
                        </div>
    </div>

</main>

<?php include '../includes/footer.php'; ?>
      
            
       









