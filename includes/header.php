<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Stars Doors - Plateforme de location de logements de courte durée. Trouvez votre logement idéal pour vos vacances.">
    <meta name="keywords" content="location, vacances, appartement, maison, hébergement, voyage">
    <meta name="author" content="Stars Doors">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <title><?php echo htmlspecialchars($title ?? 'Stars Doors'); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime('assets/css/style.css'); ?>">
    
    <!-- Security headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <?php if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'): ?>
    <meta http-equiv="Strict-Transport-Security" content="max-age=31536000; includeSubDomains">
    <?php endif; ?>
</head>
<body class="<?php echo $current_page ?? ''; ?>">
    <!-- Skip to main content pour l'accessibilité -->
    <a class="visually-hidden-focusable" href="#main-content">Aller au contenu principal</a>
    
    <?php
    // Afficher les messages flash globaux s'ils existent
    if (function_exists('displayFlashMessages') && isset($_SESSION['flash_messages'])) {
        echo '<div class="flash-messages-container">';
        displayFlashMessages();
        echo '</div>';
    }
    ?>