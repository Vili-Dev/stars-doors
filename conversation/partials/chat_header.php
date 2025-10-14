<?php if ($interlocutor): ?>
  <div class="d-flex align-items-center mb-3">
    <img src="<?php echo htmlspecialchars($interlocutor['avatar'] ?? '/assets/img/default-avatar.png'); ?>" alt="avatar" class="rounded-circle me-3" width="48" height="48">
    <div>
      <h5 class="mb-0">Chat avec <?php echo htmlspecialchars(($interlocutor['prenom'] ?? '') . ' ' . ($interlocutor['nom'] ?? '')); ?></h5>
      <small class="text-muted">
        <?php echo htmlspecialchars($interlocutor['race_nom'] ?? ''); ?> · <?php echo htmlspecialchars($interlocutor['planete_nom'] ?? ''); ?>
      </small>
    </div>
    <span id="ws-status" class="badge bg-secondary ms-auto">Hors ligne</span>
  </div>
<?php else: ?>
  <div class="d-flex align-items-center mb-3">
    <img src="/assets/img/default-avatar.png" alt="avatar" class="rounded-circle me-3" width="48" height="48">
    <div>
      <h5 class="mb-0">Rédiger un message</h5>
      <small class="text-muted">L'interlocuteur est introuvable. Vos messages seront enregistrés.</small>
    </div>
    <span id="ws-status" class="badge bg-secondary ms-auto">Hors ligne</span>
  </div>
<?php endif; ?>