<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    header('Location: litige.php?error=invalid_id');
    exit;
}

// Récupérer l'annonce avec les infos du propriétaire
try {
    $stmt = $pdo->prepare("
        SELECT l.*, u.prenom, u.nom, u.email,u.telephone, a.titre, a.id_annonce, r.id_reservation
        FROM litiges l
        LEFT JOIN users u ON l.id_user = u.id_user
        LEFT JOIN annonces a ON l.id_annonce = a.id_annonce
        LEFT JOIN reservations r ON l.id_reservation = r.id_reservation
        WHERE l.id_litige = :id
    ");
    $stmt->execute([':id' => $id]);
    $litige = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$litige) {
        header('Location: litige.php?error=not_found');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur récupération litige: " . $e->getMessage());
    echo '<pre style="color:red;">Erreur SQL : ' . htmlspecialchars($e->getMessage()) . '</pre>';
    header('Location: litige.php?error=action_failed');
    
    exit;
}

$title = 'Détails de la litige - Administration';

include '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Stars Doors</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Admin</a>
            <a class="nav-link" href="annonces.php">Retour aux annonces</a>
            <a class="nav-link" href="../logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Détails de la litige #<?= $litige['id_litige'] ?></h1>
                <a href="litige.php" class="btn btn-secondary">← Retour</a>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item"><a href="litige.php">Litiges</a></li>
                    <li class="breadcrumb-item active">Détails</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informations de la litige</h5>
                    <?php
                    $badge = [
                        'en_attente' => 'warning',
                                    'en_cours' => 'info',
                                    'resolu' => 'success',
                                    'rejete' => 'danger'
                                ];
                                $label = [
                                    'en_attente' => '⏳ En attente',
                                    'en_cours' => '🔄 En cours',
                                    'resolu' => '✅ Résolu',
                                    'rejete' => '❌ Rejeté'
                                ];
                                ?>
                
                    <span class="badge bg-<?= $badge[$litige['statut']] ?? 'secondary' ?>">
                        <?= $label[$litige['statut']] ?? $litige['statut'] ?>
                    </span>
                </div>
                <div class="card-body">
                    <h3><?= htmlspecialchars($litige['sujet']) ?></h3>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Date_creation :</strong> 
                               <?= date('d/m/Y à H:i', strtotime($litige['date_creation'])) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Description</strong> 
                                <span class="text-primary fw-bold"><?= htmlspecialchars($litige['description']) ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <a href="annonce_view.php?id=<?= $litige['id_annonce'] ?>" class="btn btn-sm btn-primary">

                            <p><strong>Titre Annonce :</strong> <?= htmlspecialchars($litige['titre']) ?></p></a>
                        </div>
                        <div class="col-md-6">
                          <!--   <a href="user_edit.php?id=<?= $litige['id_user'] ?>" class="btn btn-sm btn-primary"> -->

                            <p><strong>Reservation :</strong> <?= htmlspecialchars($litige['id_reservation']) ?></p>
                            
                        </div>
                    </div>

                   </div>
            </div>
        </div>

        <!-- Informations propriétaire et actions -->
        <div class="col-md-4">
            <!-- Propriétaire -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Propriétaire</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nom :</strong> <?= htmlspecialchars($litige['prenom'] . ' ' . $litige['nom']) ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($litige['email']) ?></p>
                    <p><strong>Téléphone :</strong> <?= htmlspecialchars($litige['telephone']) ?></p>
                    <a href="user_edit.php?id=<?= $litige['id_user'] ?>" class="btn btn-sm btn-primary">
                        Voir le profil
                    </a>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <?php if ($litige['statut'] === 'en_attente'): ?>
                        <a href="litige_en_cours.php?id=<?= $litige['id_litige'] ?>" 
                           class="btn btn-success w-100 mb-2"
                           onclick="return confirm(' Mettre cette litige en cours ?')">
                            En cours
                        </a>
                        <a href="litige_resolu.php?id=<?= $litige['id_litige'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('cette litige est résolu ?')">
                            Résolu
                        </a>

                        <a href="litige_rejeter.php?id=<?= $litige['id_litige'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('Rejeter cette litige ?')">
                            Rejeter
                        </a>
                    <?php elseif ($litige['statut'] === 'en_cours'): ?>
                        <a href="litige_resolu.php?id=<?= $litige['id_litige'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('Cette litige est resolu')">
                            Resolu
                        </a>
                        <a href="litige_rejeter.php?id=<?= $litige['id_litige'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('Cette litige est rejetée')">
                            Rejeter
                        </a>
                    <?php elseif ($litige['statut'] === 'rejete'): ?>
                        <a href="litige_resolu.php?id=<?= $litige['id_litige'] ?>" 
                           class="btn btn-success w-100 mb-2"
                           onclick="return confirm('Cette litige est résolue ?')">
                            Résolu
                        </a>
                    <?php endif; ?>
                     <hr>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

