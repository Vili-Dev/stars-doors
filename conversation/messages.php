<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

/**
 * Messagerie Intergalactique
 * - Sécurise l'accès (requireLogin)
 * - Envoi et affichage des messages et conversations
 * - Marquage des messages comme lus à l'ouverture
 *
 * Note: envisager l'ajout d'une protection CSRF pour le formulaire.
 */
requireLogin();

$title = 'Messagerie Intergalactique - Stars Doors';
$current_page = 'messages';
$user_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

// Traitement de l'envoi de message (validation, insertion, feedback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Action du formulaire: 'send' pour envoyer un message
    if ($_POST['action'] === 'send') {
        // Récupération et nettoyage des champs du formulaire
        $destinataire_id = filter_input(INPUT_POST, 'destinataire_id', FILTER_VALIDATE_INT);
        $contenu = trim($_POST['contenu'] ?? '');
        $sujet = trim($_POST['sujet'] ?? 'Chat');
        $annonce_id = filter_input(INPUT_POST, 'annonce_id', FILTER_VALIDATE_INT);

        if ($destinataire_id && $contenu) {
            // Insertion en base si validation réussie
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, sujet, contenu, lu, date_envoi, date_lecture)
                    VALUES (?, ?, ?, ?, ?, 0, NOW(), NULL)
                ");
                $stmt->execute([$user_id, $destinataire_id, $annonce_id, $sujet, $contenu]);
                setFlashMessage('Message envoyé avec succès !', 'success');
            } catch (PDOException $e) {
                error_log("Erreur envoi message: " . $e->getMessage());
                setFlashMessage('Erreur lors de l\'envoi du message.', 'danger');
            }
        }
    }
}

// Récupération des conversations (interlocuteurs, dernier message, non lus, métadonnées)
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            CASE
                WHEN m.id_expediteur = ? THEN m.id_destinataire
                ELSE m.id_expediteur
            END as interlocuteur_id,
            u.nom,
            u.prenom,
            u.avatar,
            r.nom as race_nom,
            p.nom as planete_nom,
            p.distance_terre_al AS distance_terre,
            (SELECT contenu FROM messages m2
             WHERE (m2.id_expediteur = ? AND m2.id_destinataire = interlocuteur_id)
                OR (m2.id_destinataire = ? AND m2.id_expediteur = interlocuteur_id)
             ORDER BY m2.date_envoi DESC LIMIT 1) as dernier_message,
            (SELECT date_envoi FROM messages m2
             WHERE (m2.id_expediteur = ? AND m2.id_destinataire = interlocuteur_id)
                OR (m2.id_destinataire = ? AND m2.id_expediteur = interlocuteur_id)
             ORDER BY m2.date_envoi DESC LIMIT 1) as date_dernier_message,
            (SELECT COUNT(*) FROM messages m2
             WHERE m2.id_destinataire = ? AND m2.id_expediteur = interlocuteur_id AND m2.lu = 0) as nb_non_lus
        FROM messages m
        INNER JOIN users u ON (CASE WHEN m.id_expediteur = ? THEN m.id_destinataire ELSE m.id_expediteur END) = u.id_user
        LEFT JOIN races r ON u.id_race = r.id_race
        LEFT JOIN planetes p ON u.planete_residence = p.id_planete
        WHERE m.id_expediteur = ? OR m.id_destinataire = ?
        ORDER BY date_dernier_message DESC
    ");
    // Exécution avec ID utilisateur pour sous-requêtes corrélées
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur récupération conversations: " . $e->getMessage());
    $conversations = [];
}

// Conversation sélectionnée (via paramètre GET `with`)
$conversation_id = filter_input(INPUT_GET, 'with', FILTER_VALIDATE_INT);
$messages_conversation = [];
$interlocuteur = null;

if ($conversation_id) {
    try {
        // Récupérer l'interlocuteur
        $stmt = $pdo->prepare("
            SELECT u.*, r.nom as race_nom, p.nom as planete_nom, p.distance_terre_al AS distance_terre, p.galaxie
            FROM users u
            LEFT JOIN races r ON u.id_race = r.id_race
            LEFT JOIN planetes p ON u.planete_residence = p.id_planete
            WHERE u.id_user = ?
        ");
        $stmt->execute([$conversation_id]);
        $interlocuteur = $stmt->fetch(PDO::FETCH_ASSOC);

        // Récupérer les messages
        $stmt = $pdo->prepare("
            SELECT m.*, u.prenom, u.nom
            FROM messages m
            INNER JOIN users u ON m.id_expediteur = u.id_user
            WHERE (m.id_expediteur = ? AND m.id_destinataire = ?)
               OR (m.id_expediteur = ? AND m.id_destinataire = ?)
            ORDER BY m.date_envoi ASC
        ");
        // Tri chronologique ascendant pour l'affichage
        $stmt->execute([$user_id, $conversation_id, $conversation_id, $user_id]);
        $messages_conversation = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Marquer comme lus
        $stmt = $pdo->prepare("
            UPDATE messages SET lu = 1, date_lecture = NOW()
            WHERE id_destinataire = ? AND id_expediteur = ? AND lu = 0
        ");
        $stmt->execute([$user_id, $conversation_id]);

    } catch (PDOException $e) {
        error_log("Erreur récupération messages: " . $e->getMessage());
    }
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="container-fluid py-4">
    <div class="row">
        <!-- Liste des conversations -->
        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-comments"></i> Conversations
                    </h5>
                </div>
                <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($conversations)): ?>
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Aucune conversation</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="?with=<?php echo $conv['interlocuteur_id']; ?>"
                               class="list-group-item list-group-item-action <?php echo $conversation_id == $conv['interlocuteur_id'] ? 'active' : ''; ?>">
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <?php if ($conv['avatar']): ?>
                                            <img src="<?php echo htmlspecialchars($conv['avatar']); ?>"
                                                 class="rounded-circle"
                                                 width="40" height="40" alt="">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($conv['prenom'] . ' ' . $conv['nom']); ?></strong>
                                            <?php if ($conv['nb_non_lus'] > 0): ?>
                                                <span class="badge bg-danger rounded-pill"><?php echo $conv['nb_non_lus']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-user-astronaut"></i> <?php echo htmlspecialchars($conv['race_nom'] ?? 'Inconnu'); ?>
                                        </small>
                                        <small class="text-truncate d-block" style="max-width: 200px;">
                                            <?php echo htmlspecialchars(substr($conv['dernier_message'] ?? '', 0, 50)); ?>
                                        </small>
                                        <?php if ($conv['distance_terre']): ?>
                                            <small class="text-warning">
                                                <i class="fas fa-satellite-dish"></i>
                                                <?php
                                                // Estimation fictive du délai de transmission selon la distance
                                                $delai = ($conv['distance_terre'] > 100) ? floor($conv['distance_terre'] - 100) : 0;
                                                if ($delai > 0) {
                                                    echo "Délai: {$delai}s";
                                                } else {
                                                    echo "Instantané";
                                                }
                                                ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Zone de conversation -->
        <div class="col-md-8 col-lg-9">
            <?php if ($interlocuteur): ?>
                <div class="card shadow-sm">
                    <!-- En-tête conversation -->
                    <div class="card-header bg-light">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <?php if ($interlocuteur['avatar']): ?>
                                    <img src="<?php echo htmlspecialchars($interlocuteur['avatar']); ?>"
                                         class="rounded-circle me-3" width="50" height="50" alt="">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3"
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-user fa-lg text-white"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($interlocuteur['prenom'] . ' ' . $interlocuteur['nom']); ?></h5>
                                    <small class="text-muted">
                                        <i class="fas fa-user-astronaut"></i> <?php echo htmlspecialchars($interlocuteur['race_nom'] ?? 'Inconnu'); ?>
                                        <?php if ($interlocuteur['planete_nom']): ?>
                                            | <i class="fas fa-globe"></i> <?php echo htmlspecialchars($interlocuteur['planete_nom']); ?>
                                            (<?php echo htmlspecialchars($interlocuteur['galaxie'] ?? ''); ?>)
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div>
                                <?php if ($interlocuteur['distance_terre']): ?>
                                    <?php
                                    // Badge d'état de transmission basé sur la distance
                                    $delai = ($interlocuteur['distance_terre'] > 100) ? floor($interlocuteur['distance_terre'] - 100) : 0;
                                    ?>
                                    <span class="badge <?php echo $delai > 0 ? 'bg-warning' : 'bg-success'; ?>">
                                        <i class="fas fa-satellite-dish"></i>
                                        <?php echo $delai > 0 ? "Délai: {$delai}s" : "Transmission instantanée"; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="card-body" style="height: 500px; overflow-y: auto;" id="messages-container">
                        <?php if (empty($messages_conversation)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-comments fa-3x mb-3"></i>
                                <p>Aucun message. Démarrez la conversation !</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages_conversation as $msg): ?>
                                <?php $is_me = $msg['id_expediteur'] == $user_id; ?>
                                <div class="mb-3 d-flex <?php echo $is_me ? 'justify-content-end' : 'justify-content-start'; ?>">
                                    <div class="<?php echo $is_me ? 'bg-primary text-white' : 'bg-light'; ?> rounded p-3"
                                         style="max-width: 70%;">
                                        <?php if (!$is_me): ?>
                                            <strong class="d-block mb-1"><?php echo htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']); ?></strong>
                                        <?php endif; ?>
                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($msg['contenu'])); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="<?php echo $is_me ? 'text-white-50' : 'text-muted'; ?>">
                                                <?php
                                                // Formatage de la date d'envoi (jour/mois/année heure:minute)
                                                $date = new DateTime($msg['date_envoi']);
                                                echo $date->format('d/m/Y H:i');
                                                ?>
                                            </small>
                                            <?php if ($msg['priorite'] === 'urgente'): ?>
                                                <span class="badge bg-warning text-dark ms-2">
                                                    <i class="fas fa-exclamation"></i> Urgent
                                                </span>
                                            <?php elseif ($msg['priorite'] === 'critique'): ?>
                                                <span class="badge bg-danger ms-2">
                                                    <i class="fas fa-exclamation-triangle"></i> Critique
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Indicateurs supplémentaires: délai de transmission et traduction -->
                                        <?php if ($msg['delai_transmission_secondes'] > 0): ?>
                                            <small class="<?php echo $is_me ? 'text-white-50' : 'text-muted'; ?> d-block mt-1">
                                                <i class="fas fa-clock"></i>
                                                Délai transmission: <?php echo $msg['delai_transmission_secondes']; ?>s
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($msg['traduit_automatiquement']): ?>
                                            <small class="<?php echo $is_me ? 'text-white-50' : 'text-muted'; ?> d-block mt-1">
                                                <i class="fas fa-language"></i> Traduit automatiquement
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Formulaire d'envoi -->
                    <div class="card-footer">
                        <form method="POST" action="">
                            <!-- Paramètres du formulaire: action et destinataire -->
                            <input type="hidden" name="action" value="send">
                            <input type="hidden" name="destinataire_id" value="<?php echo $interlocuteur['id_user']; ?>">
                            <input type="hidden" name="annonce_id" value="0">

                            <div class="row g-2">
                                <div class="col-12">
                                    <!-- Sujet du message (optionnel) -->
                                    <input type="text" name="sujet" class="form-control" placeholder="Sujet (optionnel)">
                                </div>
                                <div class="col-12">
                                    <!-- Contenu du message -->
                                    <div class="input-group">
                                        <textarea name="contenu" class="form-control" rows="2"
                                                  placeholder="Votre message..." required></textarea>
                                    </div>
                                </div>
                                <div class="col">
                                    <!-- Bouton d'envoi -->
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Sélectionnez une conversation</h4>
                        <p class="text-muted">Choisissez un contact pour voir vos messages</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Auto-scroll vers le bas des messages
const container = document.getElementById('messages-container');
if (container) {
    container.scrollTop = container.scrollHeight;
}

// Rechargement automatique toutes les 30 secondes
<?php if ($conversation_id): ?>
    setInterval(() => {
        window.location.reload();
    }, 30000);
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
