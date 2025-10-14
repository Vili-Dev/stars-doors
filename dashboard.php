<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$title = 'Tableau de bord - Stars Doors';
$current_page = 'dashboard';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Traitement de la suppression d'annonce
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_listing') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        setFlashMessage('Token de sécurité invalide.', 'danger');
    } else {
        $listing_id = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT);

        if ($listing_id) {
            try {
                // Vérifier que l'annonce appartient bien à l'utilisateur
                $stmt = $pdo->prepare("SELECT id_user FROM annonces WHERE id_annonce = ?");
                $stmt->execute([$listing_id]);
                $listing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($listing && canDelete($listing['id_user'])) {
                    // Récupérer toutes les photos avant suppression
                    $stmt = $pdo->prepare("SELECT chemin FROM photo WHERE id_annonce = ?");
                    $stmt->execute([$listing_id]);
                    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Supprimer les fichiers physiques
                    foreach ($photos as $photo) {
                        if (file_exists($photo['chemin'])) {
                            unlink($photo['chemin']);
                        }
                    }

                    // Supprimer les photos de la base
                    $stmt = $pdo->prepare("DELETE FROM photo WHERE id_annonce = ?");
                    $stmt->execute([$listing_id]);

                    // Supprimer les réservations associées (ou les mettre à jour selon la logique métier)
                    // Pour l'instant on garde les réservations pour l'historique

                    // Supprimer l'annonce
                    $stmt = $pdo->prepare("DELETE FROM annonces WHERE id_annonce = ?");
                    $stmt->execute([$listing_id]);

                    setFlashMessage('Annonce supprimée avec succès.', 'success');
                } else {
                    setFlashMessage('Vous n\'avez pas l\'autorisation de supprimer cette annonce.', 'danger');
                }
            } catch (PDOException $e) {
                setFlashMessage('Erreur lors de la suppression de l\'annonce.', 'danger');
                error_log("Erreur suppression annonce: " . $e->getMessage());
            }
        } else {
            setFlashMessage('ID d\'annonce invalide.', 'danger');
        }
    }

    // Redirection pour éviter la resoumission du formulaire
    redirect('dashboard.php#annonces');
}

// Générer un token CSRF pour les formulaires
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-4">
    <?php displayFlashMessages(); ?>

    <div class="row">
        <div class="col-12 mb-4">
            <h1>Tableau de bord</h1>
            <p class="text-muted">Bienvenue <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>
    </div>

    <div class="row">
        <!-- Navigation du dashboard -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Navigation</h6>
                    <div class="list-group list-group-flush">
                        <a href="#reservations" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar-alt"></i> Mes réservations
                        </a>
                        <?php if ($user_role === 'proprietaire' || $user_role === 'admin'): ?>
                        <a href="#annonces" class="list-group-item list-group-item-action">
                            <i class="fas fa-home"></i> Mes annonces
                        </a>
                        <?php endif; ?>
                        <a href="#messages" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                        <a href="#favoris" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart"></i> Favoris
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user"></i> Mon profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-lg-9">
            <!-- Section Réservations -->
            <section id="reservations" class="mb-5">
                <h3>Mes réservations</h3>
                <?php
                try {
                    if ($user_role === 'proprietaire' || $user_role === 'admin') {
                        // Réservations reçues pour les propriétaires
                        $stmt = $pdo->prepare("SELECT r.*, a.titre, a.ville, u.prenom, u.nom, u.email 
                                              FROM reservations r 
                                              LEFT JOIN annonces a ON r.id_annonce = a.id_annonce 
                                              LEFT JOIN users u ON r.id_user = u.id_user 
                                              WHERE a.id_user = ? 
                                              ORDER BY r.date_reservation DESC 
                                              LIMIT 5");
                        $stmt->execute([$user_id]);
                        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($reservations)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Réservations reçues</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($reservations as $reservation): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6><?php echo htmlspecialchars($reservation['titre']); ?></h6>
                                            <p class="text-muted mb-1">
                                                <?php echo htmlspecialchars($reservation['prenom'] . ' ' . $reservation['nom']); ?>
                                            </p>
                                            <small class="text-muted">
                                                Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?>
                                                au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge bg-<?php 
                                                echo $reservation['statut'] === 'confirmee' ? 'success' : 
                                                    ($reservation['statut'] === 'en_attente' ? 'warning' : 
                                                    ($reservation['statut'] === 'annulee' ? 'danger' : 'secondary')); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $reservation['statut'])); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <strong><?php echo number_format($reservation['prix_total'], 2); ?>€</strong>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <p>Aucune réservation reçue.</p>
                        <?php endif;
                    } else {
                        // Réservations effectuées pour les locataires
                        $stmt = $pdo->prepare("SELECT r.*, a.titre, a.ville 
                                              FROM reservations r 
                                              LEFT JOIN annonces a ON r.id_annonce = a.id_annonce 
                                              WHERE r.id_user = ? 
                                              ORDER BY r.date_reservation DESC 
                                              LIMIT 5");
                        $stmt->execute([$user_id]);
                        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($reservations)): ?>
                        <div class="card">
                            <div class="card-body">
                                <?php foreach ($reservations as $reservation): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6><?php echo htmlspecialchars($reservation['titre']); ?></h6>
                                            <p class="text-muted mb-1">
                                                <?php echo htmlspecialchars($reservation['ville']); ?>
                                            </p>
                                            <small class="text-muted">
                                                Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?>
                                                au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge bg-<?php 
                                                echo $reservation['statut'] === 'confirmee' ? 'success' : 
                                                    ($reservation['statut'] === 'en_attente' ? 'warning' : 
                                                    ($reservation['statut'] === 'annulee' ? 'danger' : 'secondary')); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $reservation['statut'])); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <strong><?php echo number_format($reservation['prix_total'], 2); ?>€</strong>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <p>Aucune réservation effectuée.</p>
                        <?php endif;
                    }
                } catch (PDOException $e) {
                    echo '<p class="text-danger">Erreur lors du chargement des réservations.</p>';
                    error_log("Erreur dashboard réservations: " . $e->getMessage());
                }
                ?>
            </section>

            <?php if ($user_role === 'proprietaire' || $user_role === 'admin'): ?>
            <!-- Section Annonces (pour propriétaires) -->
            <section id="annonces" class="mb-5">
                <h3>Mes annonces</h3>
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT a.*, COUNT(r.id_reservation) as nb_reservations 
                                          FROM annonces a 
                                          LEFT JOIN reservations r ON a.id_annonce = r.id_annonce 
                                          WHERE a.id_user = ? 
                                          GROUP BY a.id_annonce 
                                          ORDER BY a.date_creation DESC 
                                          LIMIT 5");
                    $stmt->execute([$user_id]);
                    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($annonces)): ?>
                    <div class="card">
                        <div class="card-body">
                            <?php foreach ($annonces as $annonce): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <h6><?php echo htmlspecialchars($annonce['titre']); ?></h6>
                                        <p class="text-muted mb-0">
                                            <?php echo htmlspecialchars($annonce['ville']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge bg-<?php echo $annonce['disponible'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $annonce['disponible'] ? 'Disponible' : 'Indisponible'; ?>
                                        </span>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div><?php echo number_format($annonce['prix_nuit'], 2); ?>€/nuit</div>
                                        <small class="text-muted"><?php echo $annonce['nb_reservations']; ?> réservation(s)</small>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="listing.php?id=<?php echo $annonce['id_annonce']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_listing.php?id=<?php echo $annonce['id_annonce']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="d-inline delete-listing-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="action" value="delete_listing">
                                            <input type="hidden" name="listing_id" value="<?php echo $annonce['id_annonce']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <p>Aucune annonce publiée.</p>
                    <?php endif;
                } catch (PDOException $e) {
                    echo '<p class="text-danger">Erreur lors du chargement des annonces.</p>';
                    error_log("Erreur dashboard annonces: " . $e->getMessage());
                }
                ?>
            </section>
            <?php endif; ?>

            <!-- Liens rapides -->
            <section class="mb-5">
                <h3>Actions rapides</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="search.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search"></i> Rechercher un logement
                        </a>
                    </div>
                    <?php if ($user_role === 'proprietaire' || $user_role === 'admin'): ?>
                    <div class="col-md-6 mb-3">
                        <a href="create_listing.php" class="btn btn-outline-success w-100">
                            <i class="fas fa-plus"></i> Ajouter une annonce
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6 mb-3">
                        <a href="conversation/chat.php" class="btn btn-outline-info w-100">
                            <i class="fas fa-envelope"></i> Mes messages
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="favorites.php" class="btn btn-outline-warning w-100">
                            <i class="fas fa-heart"></i> Mes favoris
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>