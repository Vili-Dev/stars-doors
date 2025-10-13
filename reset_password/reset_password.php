<?php

$token = $_GET['token'] ?? '';

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

echo "Token valide.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser mot de passe</title>
</head>
<body>
    <?php if (function_exists('displayFlashMessages')) { displayFlashMessages(); } ?>
    <h1>Réinitialiser mot de passe</h1>
    <form method="post" action="process_reset_password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

        <label for="password">Nouveau mot de passe</label>
        <input type="password" name="password" id="password">

        <label for="confirm_password">Confirmer le mot de passe</label>
        <input type="password" name="confirm_password" id="confirm_password">

        <button>Valider</button>
    </form>
</body>
</html>