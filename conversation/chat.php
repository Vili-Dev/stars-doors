<?php
// Added: Modular chat page using WebSockets
// - Loads interlocutor and history; marks incoming as read
// - Injects CHAT_CONFIG for client (wsUrl, ids, saveUrl)
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/lib/messages_service.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$to_id = filter_input(INPUT_GET, 'to', FILTER_VALIDATE_INT);
$listing_id = filter_input(INPUT_GET, 'listing', FILTER_VALIDATE_INT);

// Relaxed: do not redirect if recipient missing or self; show conversation anyway
if (!$to_id || $to_id === $user_id) {
    setFlashMessage('Destinataire introuvable ou non connecté. Affichage en mode consultation.', 'warning');
    $to_id = (int)($to_id ?: 0);
}

// Load interlocutor and existing messages
$interlocutor = getInterlocutor($pdo, $to_id);
$messages = getConversationMessages($pdo, $user_id, $to_id, 100);
if ($to_id) {
  markAsRead($pdo, $user_id, $to_id);
}

$title = 'Chat';
$current_page = 'chat';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/nav.php';
?>

<main class="container py-4">
  <?php displayFlashMessages(); ?>
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <?php include __DIR__ . '/partials/chat_header.php'; ?>
      <div class="card shadow-sm">
        <div class="card-body" id="chat-messages" style="height: 500px; overflow-y: auto;">
          <?php include __DIR__ . '/partials/chat_messages.php'; ?>
        </div>
        <div class="card-footer">
          <?php include __DIR__ . '/partials/chat_form.php'; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  window.CHAT_CONFIG = {
    wsUrl: 'ws://127.0.0.1:8080/',
    userId: <?php echo (int)$user_id; ?>,
    toId: <?php echo (int)$to_id; ?>,
    listingId: <?php echo $listing_id ? (int)$listing_id : 0; ?>,
    saveUrl: '/conversation/api/save_message.php'
  };
</script>
<script src="/assets/js/chat.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>