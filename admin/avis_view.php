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
    header('Location: avis.php?error=invalid_id');
    exit;
}

// Récupérer l'avis complet
try {
    $stmt = $pdo->prepare("
        SELECT av.*, 
               u.prenom as user_prenom, u.nom as user_nom, u.email as user_email,
               an.titre as annonce_titre, an.id_annonce,
               prop.prenom as prop_prenom, prop.nom as prop_nom
        FROM avis av
        LEFT JOIN users u ON av.id_user = u.id_user
        LEFT JOIN annonces an ON av.id_annonce = an.id_annonce
        LEFT JOIN users prop ON an.id_user = prop.id_user
        WHERE av.id_avis = :id
    ");
    $stmt->execute([':id' => $id]);
    $avis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$avis) {
        header('Location: avis.php?error=not_found');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erreur récupération avis: " . $e->getMessage());
    header('Location: avis.php?error=action_failed');
    exit;
}

$title = 'Détails de l\'avis - Administration';

include '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../index.php">Stars Doors</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Admin</a>
            <a class="nav-link" href="avis.php">Retour aux avis</a>
            <a class="nav-link" href="../logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Détails de l'avis #<?= $avis['id_avis'] ?></h1>
                <a href="avis.php" class="btn btn-secondary">← Retour</a>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Administration</a></li>
                    <li class="breadcrumb-item"><a href="avis.php">Avis</a></li>
                    <li class="breadcrumb-item active">Détails</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Contenu de l'avis -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Contenu de l'avis</h5>
                    <?php
                    $badge = [
                        'en_attente' => 'warning',
                        'approuve' => 'success',
                        'rejete' => 'danger'
                    ];
                    $label = [
                        'en_attente' => '⏳ En attente',
                        'approuve' => '✅ Approuvé',
                        'rejete' => '<i class="bi bi-x-lg"></i> Rejeté'
                    ];
                    ?>
                    <span class="badge bg-<?= $badge[$avis['statut']] ?? 'secondary' ?>">
                        <?= $label[$avis['statut']] ?? $avis['statut'] ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h4>Note globale : 
                            <span class="badge bg-warning text-dark fs-5">
                                ⭐ <?= number_format($avis['note'], 1) ?>/5
                            </span>
                        </h4>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>🧹 Propreté :</strong> <?= (int)$avis['proprete'] ?>/5</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>💬 Communication :</strong> <?= (int)$avis['communication'] ?>/5</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>📍 Emplacement :</strong> <?= (int)$avis['emplacement'] ?>/5</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>💰 Rapport qualité/prix :</strong> <?= (int)$avis['rapport_qualite_prix'] ?>/5</p>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h5>Commentaire :</h5>
                        <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($avis['commentaire'])) ?></p>
                    </div>

                    <?php if (!empty($avis['reponse_proprietaire'])): ?>
                        <div class="mb-3">
                            <h5>Réponse du propriétaire :</h5>
                            <p class="bg-info bg-opacity-10 p-3 rounded">
                                <?= nl2br(htmlspecialchars($avis['reponse_proprietaire'])) ?>
                            </p>
                            <small class="text-muted">
                                Répondu le <?= date('d/m/Y', strtotime($avis['date_reponse'])) ?>
                            </small>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-12">
                            <p><strong>Date de l'avis :</strong> 
                                <?= date('d/m/Y à H:i', strtotime($avis['date_avis'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annonce concernée -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Annonce concernée</h5>
                </div>
                <div class="card-body">
                    <h6><?= htmlspecialchars($avis['annonce_titre']) ?></h6>
                    <p><strong>Propriétaire :</strong> <?= htmlspecialchars($avis['prop_prenom'] . ' ' . $avis['prop_nom']) ?></p>
                    <a href="annonce_view.php?id=<?= $avis['id_annonce'] ?>" class="btn btn-sm btn-primary">
                        Voir l'annonce
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Auteur de l'avis -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Auteur de l'avis</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nom :</strong> <?= htmlspecialchars($avis['user_prenom'] . ' ' . $avis['user_nom']) ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($avis['user_email']) ?></p>
                    <a href="user_edit.php?id=<?= $avis['id_user'] ?>" class="btn btn-sm btn-primary">
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
                    <?php if ($avis['statut'] === 'en_attente'): ?>
                        <a href="avis_approve.php?id=<?= $avis['id_avis'] ?>" 
                           class="btn btn-success w-100 mb-2"
                           onclick="return confirm('Approuver cet avis ?')">
                            <i class="bi bi-check-lg"></i> Approuver
                        </a>
                        <a href="avis_reject.php?id=<?= $avis['id_avis'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('Rejeter cet avis ?')">
                            <i class="bi bi-x-lg"></i> Rejeter
                        </a>
                    <?php elseif ($avis['statut'] === 'approuve'): ?>
                        <a href="avis_reject.php?id=<?= $avis['id_avis'] ?>" 
                           class="btn btn-warning w-100 mb-2"
                           onclick="return confirm('Rejeter cet avis ?')">
                            <i class="bi bi-x-lg"></i> Rejeter
                        </a>
                    <?php elseif ($avis['statut'] === 'rejete'): ?>
                        <a href="avis_approve.php?id=<?= $avis['id_avis'] ?>" 
                           class="btn btn-success w-100 mb-2"
                           onclick="return confirm('Approuver cet avis ?')">
                            ✓ Approuver
                        </a>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <a href="avis_delete.php?id=<?= $avis['id_avis'] ?>" 
                       class="btn btn-danger w-100"
                       onclick="return confirm('Supprimer définitivement cet avis ?')">
                        <i class="bi bi-trash3"></i> Supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>