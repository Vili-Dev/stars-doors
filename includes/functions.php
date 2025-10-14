<?php
// Fonctions utilitaires générales

// Empêcher l'accès direct
if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

/**
 * Redirection sécurisée
 * @param string $url URL de destination
 * @param int $code Code de statut HTTP (défaut: 302)
 */
function redirect($url, $code = 302) {
    // Si c'est une URL relative (fichier local comme "dashboard.php")
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        // C'est un chemin relatif, utiliser directement
        if (!headers_sent()) {
            header("Location: $url", true, $code);
            exit;
        } else {
            echo "<script>window.location.href='$url';</script>";
            exit;
        }
    }

    // Si c'est une URL complète, vérifier la sécurité
    $parsed_url = parse_url($url);
    $site_url = parse_url(SITE_URL);

    // Autoriser seulement les redirections vers le même domaine
    if (isset($parsed_url['host']) && isset($site_url['host'])) {
        if ($parsed_url['host'] !== $site_url['host']) {
            $url = SITE_URL . '/dashboard.php';
        }
    }

    if (!headers_sent()) {
        header("Location: $url", true, $code);
        exit;
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit;
    }
}

/**
 * Définit un message flash
 * @param string $message
 * @param string $type Type: success, danger, warning, info
 */
function setFlashMessage($message, $type = 'info') {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Récupère et supprime les messages flash
 * @return array
 */
function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Affiche les messages flash en HTML
 */
function displayFlashMessages() {
    $messages = getFlashMessages();
    
    foreach ($messages as $flash) {
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        
        echo "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">";
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

/**
 * Génère un nom de fichier unique pour les uploads
 * @param string $originalName Nom original du fichier
 * @param string $prefix Préfixe optionnel
 * @return string
 */
function generateUniqueFilename($originalName, $prefix = '') {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    
    return $prefix . $timestamp . '_' . $random . '.' . strtolower($extension);
}

/**
 * Upload sécurisé d'un fichier image
 * @param array $file $_FILES['field']
 * @param string $uploadDir Dossier de destination
 * @param int $maxSize Taille maximale en bytes
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadImage($file, $uploadDir, $maxSize = 5242880) {
    $result = ['success' => false, 'filename' => '', 'error' => ''];
    
    // Vérifications de base
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Erreur lors de l\'upload du fichier.';
        return $result;
    }
    
    // Vérifier la taille
    if ($file['size'] > $maxSize) {
        $result['error'] = 'Le fichier est trop volumineux (' . formatFileSize($maxSize) . ' max).';
        return $result;
    }
    
    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!validateImageMimeType($mimeType)) {
        $result['error'] = 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.';
        return $result;
    }
    
    // Vérifier l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!validateImageExtension($extension)) {
        $result['error'] = 'Extension de fichier non autorisée.';
        return $result;
    }
    
    // Créer le dossier si nécessaire
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $result['error'] = 'Impossible de créer le dossier de destination.';
        return $result;
    }
    
    // Générer un nom unique
    $filename = generateUniqueFilename($file['name'], 'img_');
    $filepath = $uploadDir . '/' . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Vérifier que c'est bien une image en essayant de la lire
        $imageInfo = getimagesize($filepath);
        if ($imageInfo === false) {
            unlink($filepath); // Supprimer le fichier invalide
            $result['error'] = 'Le fichier n\'est pas une image valide.';
            return $result;
        }
        
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'Erreur lors de la sauvegarde du fichier.';
    }
    
    return $result;
}

/**
 * Supprime un fichier de manière sécurisée
 * @param string $filepath Chemin du fichier
 * @param string $allowedDir Dossier autorisé (sécurité)
 * @return bool
 */
function deleteFile($filepath, $allowedDir) {
    // Vérifier que le fichier est dans le dossier autorisé
    $realpath = realpath($filepath);
    $allowedPath = realpath($allowedDir);
    
    if (!$realpath || !$allowedPath || strpos($realpath, $allowedPath) !== 0) {
        return false;
    }
    
    return file_exists($realpath) ? unlink($realpath) : true;
}

/**
 * Formate une taille de fichier en unités lisibles
 * @param int $bytes
 * @return string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Pagination simple
 * @param int $currentPage Page actuelle
 * @param int $totalItems Nombre total d'éléments
 * @param int $itemsPerPage Éléments par page
 * @param string $baseUrl URL de base pour les liens
 * @return array Informations de pagination
 */
function paginate($currentPage, $totalItems, $itemsPerPage = 20, $baseUrl = '') {
    $currentPage = max(1, (int)$currentPage);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage - 1,
        'next_page' => $currentPage + 1,
        'base_url' => $baseUrl
    ];
}

/**
 * Génère les liens de pagination HTML
 * @param array $pagination Données de pagination
 * @return string HTML
 */
function renderPagination($pagination) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
    
    // Lien précédent
    if ($pagination['has_previous']) {
        $url = $pagination['base_url'] . '?page=' . $pagination['previous_page'];
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">Précédent</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Précédent</span></li>';
    }
    
    // Numéros de pages
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    if ($start > 1) {
        $url = $pagination['base_url'] . '?page=1';
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i === $pagination['current_page']) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $url = $pagination['base_url'] . '?page=' . $i;
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $url = $pagination['base_url'] . '?page=' . $pagination['total_pages'];
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">' . $pagination['total_pages'] . '</a></li>';
    }
    
    // Lien suivant
    if ($pagination['has_next']) {
        $url = $pagination['base_url'] . '?page=' . $pagination['next_page'];
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">Suivant</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Suivant</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Nettoie et formate un numéro de téléphone
 * @param string $phone
 * @return string
 */
function formatPhone($phone) {
    $clean = preg_replace('/[^0-9+]/', '', $phone);
    
    // Format français 0X XX XX XX XX
    if (preg_match('/^0([1-9])(\d{8})$/', $clean, $matches)) {
        return '0' . $matches[1] . ' ' . 
               substr($matches[2], 0, 2) . ' ' .
               substr($matches[2], 2, 2) . ' ' .
               substr($matches[2], 4, 2) . ' ' .
               substr($matches[2], 6, 2);
    }
    
    return $phone;
}

/**
 * Calcule la distance entre deux dates en jours
 * @param string $date1
 * @param string $date2
 * @return int
 */
function daysBetween($date1, $date2) {
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    return abs($d1->diff($d2)->days);
}

/**
 * Formate une date selon le format français
 * @param string $date
 * @param bool $includeTime Inclure l'heure
 * @return string
 */
function formatDate($date, $includeTime = false) {
    $format = $includeTime ? 'd/m/Y H:i' : 'd/m/Y';
    return date($format, strtotime($date));
}

/**
 * Génère un token aléatoire sécurisé
 * @param int $length Longueur en bytes
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Log une action utilisateur
 * @param string $action
 * @param array $data Données supplémentaires
 */
function logUserAction($action, $data = []) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => getCurrentUserId(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'action' => $action,
        'data' => $data
    ];
    
    // Log dans un fichier (ou base de données selon les besoins)
    error_log('USER_ACTION: ' . json_encode($logData));
}

/**
 * Vérifie si une chaîne contient des mots interdits
 * @param string $text
 * @return bool
 */
function containsProfanity($text) {
    $badWords = ['spam', 'test_bad_word']; // À compléter selon les besoins
    $text = strtolower($text);
    
    foreach ($badWords as $word) {
        if (strpos($text, strtolower($word)) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Tronque un texte avec des points de suspension
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length - strlen($suffix)) . $suffix;
}

/**
 * Convertit un slug en titre lisible
 * @param string $slug
 * @return string
 */
function slugToTitle($slug) {
    return ucwords(str_replace('-', ' ', $slug));
}

/**
 * Convertit un titre en slug URL-friendly
 * @param string $title
 * @return string
 */
function titleToSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[àáâãäå]/', 'a', $slug);
    $slug = preg_replace('/[èéêë]/', 'e', $slug);
    $slug = preg_replace('/[ìíîï]/', 'i', $slug);
    $slug = preg_replace('/[òóôõö]/', 'o', $slug);
    $slug = preg_replace('/[ùúûü]/', 'u', $slug);
    $slug = preg_replace('/[ç]/', 'c', $slug);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Retourne la classe CSS pour l'avatar par défaut selon la race
 * @param string $race Race de l'utilisateur
 * @return string Classe CSS Bootstrap
 */
function getAvatarColorClass($race) {
    $raceColors = [
        'Humain' => 'bg-primary',
        'Alien' => 'bg-success',
        'Robot' => 'bg-secondary',
        'Vulcain' => 'bg-danger',
        'Klingon' => 'bg-dark',
        'Andorien' => 'bg-info',
        'Betazoid' => 'bg-warning',
        'Ferengi' => 'bg-success',
        'Bajoran' => 'bg-info',
        'Cardassian' => 'bg-secondary',
        'Romulan' => 'bg-dark',
        'Borg' => 'bg-danger',
        'Jedi' => 'bg-primary',
        'Sith' => 'bg-danger',
        'Wookiee' => 'bg-warning'
    ];
    
    return $raceColors[$race] ?? 'bg-primary';
}

/**
 * Génère le HTML pour un avatar (image ou placeholder)
 * @param string $avatar Chemin vers l'avatar (peut être vide)
 * @param string $race Race de l'utilisateur
 * @param int $size Taille de l'avatar en pixels
 * @param string $additionalClasses Classes CSS supplémentaires
 * @return string HTML de l'avatar
 */
function generateAvatarHtml($avatar, $race = '', $size = 50, $additionalClasses = '') {
    if (!empty($avatar) && file_exists($avatar)) {
        return sprintf(
            '<img src="%s" class="rounded-circle %s" width="%d" height="%d" alt="Avatar" style="object-fit: cover;">',
            htmlspecialchars($avatar),
            htmlspecialchars($additionalClasses),
            $size,
            $size
        );
    } else {
        $colorClass = getAvatarColorClass($race);
        $iconSize = $size <= 35 ? '0.8rem' : ($size <= 50 ? '1rem' : '1.5rem');
        
        return sprintf(
            '<div class="rounded-circle %s d-flex align-items-center justify-content-center %s" style="width: %dpx; height: %dpx;">
                <i class="fas fa-user text-white" style="font-size: %s;"></i>
            </div>',
            $colorClass,
            htmlspecialchars($additionalClasses),
            $size,
            $size,
            $iconSize
        );
    }
}

/**
 * Redimensionne et optimise une image pour les annonces
 * @param string $source_file Chemin vers le fichier source
 * @param string $original_name Nom original du fichier
 * @return array Résultat avec success, tmp_file et error
 */
function resizeAndOptimizeImage($source_file, $original_name) {
    $result = ['success' => false, 'tmp_file' => '', 'error' => ''];
    
    // Dimensions standard pour les annonces
    $target_width = 1200;
    $target_height = 800;
    $quality = 85;
    
    // Créer un fichier temporaire
    $tmp_file = tempnam(sys_get_temp_dir(), 'resize_');
    
    try {
        // Détecter le type d'image
        $image_info = getimagesize($source_file);
        if (!$image_info) {
            $result['error'] = 'Impossible de détecter le type d\'image';
            return $result;
        }
        
        $source_width = $image_info[0];
        $source_height = $image_info[1];
        $mime_type = $image_info['mime'];
        
        // Créer l'image source selon le type
        switch ($mime_type) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_file);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($source_file);
                break;
            case 'image/webp':
                $source_image = imagecreatefromwebp($source_file);
                break;
            default:
                $result['error'] = 'Type d\'image non supporté';
                return $result;
        }
        
        if (!$source_image) {
            $result['error'] = 'Impossible de créer l\'image source';
            return $result;
        }
        
        // Calculer les nouvelles dimensions en gardant les proportions
        $ratio = min($target_width / $source_width, $target_height / $source_height);
        $new_width = round($source_width * $ratio);
        $new_height = round($source_height * $ratio);
        
        // Créer l'image redimensionnée
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        
        // Préserver la transparence pour les PNG
        if ($mime_type === 'image/png') {
            imagealphablending($resized_image, false);
            imagesavealpha($resized_image, true);
            $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
            imagefilledrectangle($resized_image, 0, 0, $new_width, $new_height, $transparent);
        }
        
        // Redimensionner
        imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, 
                          $new_width, $new_height, $source_width, $source_height);
        
        // Sauvegarder en JPG optimisé
        $save_result = imagejpeg($resized_image, $tmp_file, $quality);
        
        // Nettoyer la mémoire
        imagedestroy($source_image);
        imagedestroy($resized_image);
        
        if (!$save_result) {
            $result['error'] = 'Impossible de sauvegarder l\'image redimensionnée';
            return $result;
        }
        
        $result['success'] = true;
        $result['tmp_file'] = $tmp_file;
        $result['error'] = '';
        
    } catch (Exception $e) {
        $result['error'] = 'Erreur lors du redimensionnement: ' . $e->getMessage();
    }
    
    return $result;
}
?>