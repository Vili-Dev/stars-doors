<form id="chat-form" method="POST" action="" class="d-flex gap-2">
  <input type="hidden" name="toId" value="<?php echo isset($to_id) ? (int)$to_id : 0; ?>">
  <input type="hidden" name="listingId" value="<?php echo isset($listing_id) ? (int)$listing_id : 0; ?>">
  <input type="hidden" name="subject" value="Chat">
  <input type="text" id="chat-input" name="content" class="form-control" placeholder="Écrire un message..." autocomplete="off">
  <button class="btn btn-primary" type="submit">
    <i class="fas fa-paper-plane"></i> Envoyer
  </button>
</form>