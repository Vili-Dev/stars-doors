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

<main class="container py-4">
    <h1>Statistiques</h1>

    <div class="row mb-4">
           <!-- البطاقة 1 : Total Utilisateurs -->
          <div class="col-md-3">
            <div class="card text-center">
                 <div class="card-body">
                    <h3 class="text-primary">0</h3>
                      <p class="mb-0">Total Utilisateurs</p>
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
                 <!-- Début colonne : prend un quart d'écran (3 sur 12) -->
                <div class="col-md-3">
                <!-- بداية البطاقة : شكل مستطيل مع حدود، النص في الوسط -->
                <!-- Début carte : forme rectangulaire avec bordures, texte centré -->
                <div class="card text-center">
                <!-- بداية جسم البطاقة : المحتوى الداخلي مع مسافات (padding) -->
                <!-- Début corps carte : contenu intérieur avec espaces (padding) -->
                 <div class="card-body">
                <!-- العنوان : رقم كبير بلون أصفر/برتقالي (warning) -->
                 <!-- Titre : grand nombre en couleur jaune/orange (warning) -->
                  <h3 class="text-warning">0</h3>
                  <!-- فقرة : نص توضيحي صغير، بدون مسافة سفلية (mb-0) -->
                  <!-- Paragraphe : petit texte descriptif, sans marge basse (mb-0) -->
                   <p class="mb-0">Total Avis</p>
                 </div>
                </div>
                </div>

                    <!-- البطاقة 4 : Annonces en attente -->
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
      
            
       









