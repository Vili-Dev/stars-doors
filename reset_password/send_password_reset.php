<?php
$email = $_POST["email"];

$token = bin2hex(random_bytes(16));

$token_hash = hash("sha256", $token);


require_once __DIR__ . '/../includes/config.php';
require_once __DIR__."/../includes/database.php";

$sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 MINUTE) WHERE email = ?";

$stmt = $pdo->prepare($sql);

// $stmt->bind_param("sss", $token_hash, $expiry, $email);

$stmt->execute([$token_hash, $email]);

if ($stmt->rowCount() === 0) {
    echo "Aucun utilisateur trouvé avec cet email.";
    exit;
}

require_once __DIR__ . '/mailer.php';

$mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
$mail->addAddress($email);
$mail->Subject = "Réinitialisation de mot de passe";
$resetUrl = rtrim(SITE_URL, '/') . "/reset_password/reset_password.php?token=" . urlencode($token);
$mail->Body = <<<END

    Bonjour,

    Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour procéder.
    $resetUrl Réinitialiser le mot de passe. 

    Si vous n'avez pas demandé de réinitialisation de mot de passe, ignorez cet e-mail.
    
    Cordialement,
    L'équipe Stars Doors
END;

$sent = $mail->send();
if ($sent) {
    echo "E-mail envoyé avec succès. Le message a été envoyé à votre inbox.";
} else {
    echo "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>password reset</title>
</head>
<body>
    
</body>
</html>