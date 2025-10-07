<?php
// Fonctions de validation côté serveur

// Empêcher l'accès direct
if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

/**
 * Valide une adresse email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false &&
           strlen($email) <= 100;
}

/**
 * Valide un numéro de téléphone français
 * @param string $phone
 * @return bool
 */
function validatePhone($phone) {
    // Supprimer les espaces et caractères spéciaux
    $clean_phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Formats acceptés: 0XXXXXXXXX, +33XXXXXXXXX, 33XXXXXXXXX
    return preg_match('/^(?:(?:\+|00)33|0)[1-9](?:[0-9]{8})$/', $clean_phone);
}

/**
 * Valide un mot de passe selon les critères de sécurité
 * @param string $password
 * @return bool
 */
function validatePassword($password) {
    // Au moins 8 caractères, une majuscule, une minuscule, un chiffre
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

/**
 * Valide un nom ou prénom
 * @param string $name
 * @return bool
 */
function validateName($name) {
    return strlen($name) >= 2 && 
           strlen($name) <= 80 &&
           preg_match('/^[a-zA-ZÀ-ÿ\-\s\']+$/', $name);
}

/**
 * Valide une date au format Y-m-d
 * @param string $date
 * @return bool
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Valide une date de naissance (doit être dans le passé et âge >= 18 ans)
 * @param string $birthdate
 * @return bool
 */
function validateBirthdate($birthdate) {
    if (!validateDate($birthdate)) {
        return false;
    }
    
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $age = $birth->diff($today)->y;
    
    return $age >= 18 && $birth < $today;
}

/**
 * Valide un prix (positif avec maximum 2 décimales)
 * @param mixed $price
 * @return bool
 */
function validatePrice($price) {
    return is_numeric($price) && 
           $price > 0 && 
           preg_match('/^\d+(\.\d{1,2})?$/', (string)$price);
}

/**
 * Valide un entier positif
 * @param mixed $number
 * @param int $min Valeur minimale (défaut: 1)
 * @param int $max Valeur maximale (défaut: PHP_INT_MAX)
 * @return bool
 */
function validatePositiveInt($number, $min = 1, $max = PHP_INT_MAX) {
    return filter_var($number, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => $min,
            'max_range' => $max
        ]
    ]) !== false;
}

/**
 * Valide une adresse
 * @param string $address
 * @return bool
 */
function validateAddress($address) {
    return strlen($address) >= 5 && 
           strlen($address) <= 255 &&
           preg_match('/^[a-zA-Z0-9À-ÿ\-\s\',\.]+$/', $address);
}

/**
 * Valide un nom de ville
 * @param string $city
 * @return bool
 */
function validateCity($city) {
    return strlen($city) >= 2 && 
           strlen($city) <= 150 &&
           preg_match('/^[a-zA-ZÀ-ÿ\-\s\']+$/', $city);
}

/**
 * Valide un code postal français
 * @param string $postalCode
 * @return bool
 */
function validatePostalCode($postalCode) {
    return preg_match('/^[0-9]{5}$/', $postalCode);
}

/**
 * Valide une URL
 * @param string $url
 * @return bool
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Valide un nom de fichier (pour les uploads)
 * @param string $filename
 * @return bool
 */
function validateFilename($filename) {
    return strlen($filename) <= 255 &&
           preg_match('/^[a-zA-Z0-9._-]+$/', $filename) &&
           !preg_match('/\.\./', $filename); // Pas de directory traversal
}

/**
 * Valide une extension de fichier image
 * @param string $extension
 * @return bool
 */
function validateImageExtension($extension) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    return in_array(strtolower($extension), $allowed);
}

/**
 * Valide la taille d'un fichier
 * @param int $size Taille en bytes
 * @param int $maxSize Taille maximale en bytes (défaut: 5MB)
 * @return bool
 */
function validateFileSize($size, $maxSize = 5242880) { // 5MB par défaut
    return $size > 0 && $size <= $maxSize;
}

/**
 * Valide un type MIME d'image
 * @param string $mimeType
 * @return bool
 */
function validateImageMimeType($mimeType) {
    $allowed = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/webp'
    ];
    return in_array($mimeType, $allowed);
}

/**
 * Valide un titre d'annonce
 * @param string $title
 * @return bool
 */
function validateListingTitle($title) {
    return strlen($title) >= 10 && 
           strlen($title) <= 50 &&
           trim($title) !== '';
}

/**
 * Valide une description d'annonce
 * @param string $description
 * @return bool
 */
function validateListingDescription($description) {
    return strlen($description) >= 50 && 
           strlen($description) <= 1000 &&
           trim($description) !== '';
}

/**
 * Valide un type de logement
 * @param string $type
 * @return bool
 */
function validateAccommodationType($type) {
    $allowed = ['appartement', 'maison', 'studio', 'villa', 'chambre'];
    return in_array($type, $allowed);
}

/**
 * Valide un rôle utilisateur
 * @param string $role
 * @return bool
 */
function validateUserRole($role) {
    $allowed = ['locataire', 'proprietaire', 'admin'];
    return in_array($role, $allowed);
}

/**
 * Valide un statut de réservation
 * @param string $status
 * @return bool
 */
function validateReservationStatus($status) {
    $allowed = ['en_attente', 'confirmee', 'annulee', 'terminee'];
    return in_array($status, $allowed);
}

/**
 * Valide une note d'avis (1-5)
 * @param mixed $rating
 * @return bool
 */
function validateRating($rating) {
    return validatePositiveInt($rating, 1, 5);
}

/**
 * Sanitise une chaîne de caractères
 * @param string $input
 * @param bool $allowHtml Autoriser le HTML (défaut: false)
 * @return string
 */
function sanitizeString($input, $allowHtml = false) {
    if ($allowHtml) {
        // Autoriser seulement certaines balises HTML sûres
        return strip_tags($input, '<p><br><strong><em><u><a>');
    } else {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Sanitise un entier
 * @param mixed $input
 * @return int
 */
function sanitizeInt($input) {
    return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitise un float
 * @param mixed $input
 * @return float
 */
function sanitizeFloat($input) {
    return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Sanitise un email
 * @param string $email
 * @return string
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitise une URL
 * @param string $url
 * @return string
 */
function sanitizeUrl($url) {
    return filter_var(trim($url), FILTER_SANITIZE_URL);
}

/**
 * Valide et sanitise plusieurs champs à la fois
 * @param array $data Données à valider
 * @param array $rules Règles de validation
 * @return array ['valid' => bool, 'errors' => array, 'data' => array]
 */
function validateData($data, $rules) {
    $errors = [];
    $sanitized = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        $required = $rule['required'] ?? false;
        $type = $rule['type'] ?? 'string';
        $validator = $rule['validator'] ?? null;
        
        // Vérifier si requis
        if ($required && empty($value)) {
            $errors[$field] = $rule['error'] ?? "Le champ $field est requis.";
            continue;
        }
        
        // Si vide et pas requis, passer
        if (empty($value) && !$required) {
            $sanitized[$field] = '';
            continue;
        }
        
        // Sanitisation selon le type
        switch ($type) {
            case 'email':
                $sanitized[$field] = sanitizeEmail($value);
                break;
            case 'int':
                $sanitized[$field] = sanitizeInt($value);
                break;
            case 'float':
                $sanitized[$field] = sanitizeFloat($value);
                break;
            case 'url':
                $sanitized[$field] = sanitizeUrl($value);
                break;
            default:
                $sanitized[$field] = sanitizeString($value);
        }
        
        // Validation personnalisée
        if ($validator && is_callable($validator)) {
            if (!$validator($sanitized[$field])) {
                $errors[$field] = $rule['error'] ?? "Le champ $field n'est pas valide.";
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}
?>