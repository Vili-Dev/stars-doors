<?php if (empty($messages)): ?>
  <div class="text-center text-muted py-5">
    <i class="fas fa-comments fa-3x mb-3"></i>
    <p>Aucun message. Démarrez la conversation !</p>
  </div>
<?php else: ?>
  <?php foreach ($messages as $msg): ?>
    <?php $is_me = $msg['id_expediteur'] == $user_id; ?>
    <div class="mb-3 d-flex <?php echo $is_me ? 'justify-content-end' : 'justify-content-start'; ?>">
      <div class="<?php echo $is_me ? 'bg-primary text-white' : 'bg-light'; ?> rounded p-2" style="max-width: 70%;">
        <div><?php echo htmlspecialchars($msg['contenu']); ?></div>
        <small class="<?php echo $is_me ? 'text-white-50' : 'text-muted'; ?>"><?php echo htmlspecialchars($msg['date_envoi']); ?></small>
      </div>
    </div>
  <?php endforeach; ?>
  <script>
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
  </script>
<?php endif; ?>