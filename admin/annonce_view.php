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
    header('Location: annonces.php?error=invalid_id');
    exit;
}

// Récupérer l'annonce avec les infos du propriétaire
try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.prenom, u.nom, u.email, u.telephone
        FROM annonces a
        LEFT JOIN users u ON a.id_user = u.id_user
        WHERE a.id_annonce = :id
    ");
    $stmt->execute([':id' => $id]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$annonce) {
        header('Location: annonces.php?error=not_found');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur récupération annonce: " . $e->getMessage());
    header('Location: annonces.php?error=action_failed');
    exit;
}

$title = 'Détails de l\'annonce - Administration';

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
                <h1>Détails de l'annonce #<?= $annonce['id_annonce'] ?></h1>
                <a href="annonces.php" class="btn btn-secondary">← Retour</a>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item"><a href="annonces.php">Annonces</a></li>
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
                    <h5 class="mb-0">Informations de l'annonce</h5>
                    <?php
                    $badge = [
                        'en_attente' => 'warning',
                        'approuve' => 'success',
                        'rejete' => 'danger'
                    ];
                    $label = [
                        'en_attente' => '⏳ En attente',
                        'approuve' => '✅ Approuvée',
                        'rejete' => '❌ Rejetée'
                    ];
                    ?>
                    <span class="badge bg-<?= $badge[$annonce['statut']] ?? 'secondary' ?>">
                        <?= $label[$annonce['statut']] ?? $annonce['statut'] ?>
                    </span>
                </div>
                <div class="card-body">
                    <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Type :</strong> 
                                <span class="badge bg-info"><?= htmlspecialchars($annonce['type_logement'] ?? 'N/A') ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Prix par nuit :</strong> 
                                <span class="text-primary fw-bold"><?= number_format($annonce['prix_nuit'], 0, ',', ' ') ?> €</span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Ville :</strong> <?= htmlspecialchars($annonce['ville']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Pays :</strong> <?= htmlspecialchars($annonce['pays']) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p><strong>Chambres :</strong> <?= (int)$annonce['nb_chambres'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Lits :</strong> <?= (int)$annonce['nb_lits'] ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Salles de bain :</strong> <?= (int)$annonce['nb_salles_bain'] ?></p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p><strong>Adresse :</strong> <?= htmlspecialchars($annonce['adresse']) ?></p>
                    </div>

                    <div class="mb-3">
                        <p><strong>Description :</strong></p>
                        <p><?= nl2br(htmlspecialchars($annonce['description'])) ?></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date de création :</strong> 
                                <?= date('d/m/Y à H:i', strtotime($annonce['created_at'])) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Dernière modification :</strong> 
                                <?= date('d/m/Y à H:i', strtotime($annonce['updated_at'])) ?>
                            </p>
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
                    <p><strong>Nom :</strong> <?= htmlspecialchars($annonce['prenom'] . ' ' . $annonce['nom']) ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($annonce['email']) ?></p>
                    <p><strong>Téléphone :</strong> <?= htmlspecialchars($annonce['telephone']) ?></p>
                    <a href="user_edit.php?id=<?= $annonce['id_user'] ?>" class="btn btn-sm btn-primary">
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
                    <?php if ($annonce['statut'] === 'en_attente'): ?>
                        <a href="annonce_approve.php?id=<?= $annonce['id_annonce'] ?>" 
                           class="btn btn-success w-100 mb-2"
                           onclick="return confirm('Approuver cette annonce ?')">
                            ✓ Approuver
                        </a>
                        <a href="annonce_reject.php?id=<?= $annonce['id_annonce'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('Rejeter cette annonce ?')">
                            <i class="bi bi-x-lg"></i> Rejeté
                        </a>
                    <?php elseif ($annonce['statut'] === 'approuve'): ?>
                        <a href="annonce_reject.php?id=<?= $annonce['id_annonce'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('Rejeter cette annonce ?')">
                           <i class="bi bi-x-lg"></i> Rejeté
                        </a>
                    <?php elseif ($annonce['statut'] === 'rejete'): ?>
                        <a href="annonce_approve.php?id=<?= $annonce['id_annonce'] ?>" 
                           class="btn btn-success w-100 mb-2"
                           onclick="return confirm('Approuver cette annonce ?')">
                            ✓ Approuver
                        </a>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <a href="annonce_delete.php?id=<?= $annonce['id_annonce'] ?>" 
                       class="btn btn-danger w-100"
                       onclick="return confirm('Supprimer définitivement cette annonce ?')">
                        <i class="bi bi-trash3-fill"></i> Supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>