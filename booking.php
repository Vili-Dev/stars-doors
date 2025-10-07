<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$listing_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$listing_id) {
    redirect('search.php');
}

$title = 'Réservation - Stars Doors';
$errors = [];
$success = false;

// Récupération du logement
try {
    $stmt = $pdo->prepare("SELECT * FROM annonces WHERE id_annonce = ? AND disponible = 1");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$listing) {
        redirect('search.php');
    }
} catch (PDOException $e) {
    error_log("Erreur récupération logement: " . $e->getMessage());
    redirect('search.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide.';
    } else {
        $date_debut = $_POST['date_debut'] ?? '';
        $date_fin = $_POST['date_fin'] ?? '';
        $nb_personnes = filter_input(INPUT_POST, 'nb_personnes', FILTER_VALIDATE_INT);
        $message = trim($_POST['message'] ?? '');

        // Validation
        if (empty($date_debut)) {
            $errors[] = 'La date de début est requise.';
        }
        if (empty($date_fin)) {
            $errors[] = 'La date de fin est requise.';
        }
        if (!$nb_personnes || $nb_personnes < 1) {
            $errors[] = 'Le nombre de personnes doit être d\'au moins 1.';
        } elseif ($nb_personnes > $listing['capacite_max']) {
            $errors[] = 'Le nombre de personnes ne peut pas dépasser ' . $listing['capacite_max'] . '.';
        }

        if (empty($errors) && strtotime($date_debut) >= strtotime($date_fin)) {
            $errors[] = 'La date de fin doit être postérieure à la date de début.';
        }

        if (empty($errors) && strtotime($date_debut) < strtotime('today')) {
            $errors[] = 'La date de début ne peut pas être dans le passé.';
        }

        // Vérification disponibilité
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                                      WHERE id_annonce = ? 
                                      AND statut IN ('confirmee', 'en_attente')
                                      AND ((date_debut <= ? AND date_fin > ?) 
                                           OR (date_debut < ? AND date_fin >= ?))");
                $stmt->execute([$listing_id, $date_debut, $date_debut, $date_fin, $date_fin]);
                
                if ($stmt->fetchColumn() > 0) {
                    $errors[] = 'Ces dates ne sont pas disponibles.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de la vérification des disponibilités.';
                error_log("Erreur vérification disponibilité: " . $e->getMessage());
            }
        }

        // Calcul du prix total
        if (empty($errors)) {
            $date1 = new DateTime($date_debut);
            $date2 = new DateTime($date_fin);
            $nb_nuits = $date1->diff($date2)->days;
            $prix_total = $nb_nuits * $listing['prix_nuit'];

            // Insertion de la réservation
            try {
                $stmt = $pdo->prepare("INSERT INTO reservations (id_annonce, id_user, date_debut, date_fin, nb_personnes, prix_total, message) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$listing_id, $_SESSION['user_id'], $date_debut, $date_fin, $nb_personnes, $prix_total, $message]);
                
                $success = true;
                setFlashMessage('Votre demande de réservation a été envoyée avec succès !', 'success');
            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de la réservation. Veuillez réessayer.';
                error_log("Erreur insertion réservation: " . $e->getMessage());
            }
        }
    }
}

// Génération token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1>Réservation</h1>
            
            <?php displayFlashMessages(); ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <h4>Réservation envoyée !</h4>
                <p>Votre demande de réservation a été transmise au propriétaire. Vous recevrez une confirmation dans les plus brefs délais.</p>
                <a href="dashboard.php" class="btn btn-primary">Voir mes réservations</a>
            </div>
            <?php else: ?>
            
            <!-- Résumé du logement -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><?php echo htmlspecialchars($listing['titre']); ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($listing['ville']); ?>
                            </p>
                            <p class="text-primary fw-bold">
                                <?php echo number_format($listing['prix_nuit'], 2); ?>€ / nuit
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Formulaire de réservation -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Détails de la réservation</h5>
                    
                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut" class="form-label">Date d'arrivée</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                       value="<?php echo htmlspecialchars($_POST['date_debut'] ?? ''); ?>" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_fin" class="form-label">Date de départ</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                       value="<?php echo htmlspecialchars($_POST['date_fin'] ?? ''); ?>"
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nb_personnes" class="form-label">Nombre de personnes</label>
                            <select class="form-select" id="nb_personnes" name="nb_personnes" required>
                                <?php for ($i = 1; $i <= $listing['capacite_max']; $i++): ?>
                                <option value="<?php echo $i; ?>" 
                                        <?php echo ($_POST['nb_personnes'] ?? 1) == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> personne<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message au propriétaire (optionnel)</label>
                            <textarea class="form-control" id="message" name="message" rows="3" 
                                      placeholder="Présentez-vous ou posez vos questions..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Envoyer la demande de réservation</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const prixNuit = <?php echo $listing['prix_nuit']; ?>;
    
    function updatePrice() {
        if (dateDebut.value && dateFin.value) {
            const debut = new Date(dateDebut.value);
            const fin = new Date(dateFin.value);
            const diffTime = fin - debut;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 0) {
                const total = diffDays * prixNuit;
                // Ici on pourrait afficher le prix total calculé
            }
        }
    }
    
    dateDebut.addEventListener('change', updatePrice);
    dateFin.addEventListener('change', updatePrice);
});
</script>

<?php include 'includes/footer.php'; ?>