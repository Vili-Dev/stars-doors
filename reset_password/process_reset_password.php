<?php

$token = $_POST['token'] ?? '';

$token_hash = hash('sha256', $token);

require __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$sql = "SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > UTC_TIMESTAMP()";

$stmt = $pdo->prepare($sql);
$stmt->execute([$token_hash]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user === false) {
    echo "Token invalide ou expiré.";
    exit;
}

// Valider les entrées
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
if (empty($password) || strlen($password) < PASSWORD_MIN_LENGTH) {
    setFlashMessage('Les mots de passe trop court.', 'danger');
    redirect('reset_password.php?token=' . urlencode($token));
}
if ($password !== $confirm) {
    setFlashMessage('Les mots de passe ne correspondent pas.', 'danger');
    redirect('reset_password.php?token=' . urlencode($token));
}

// Hash et mise à jour
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$sql = "UPDATE users SET mot_de_passe = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$hashed_password, $user['id_user']]);

// Message et redirection vers la connexion
setFlashMessage('Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.', 'success');
redirect('../login.php');
?>