<?php
// Utilitaires du système de fidélité

if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

/**
 * Récupère les informations de fidélité d'un utilisateur
 */
function getFideliteUser($user_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT u.*, pf.*
            FROM users u
            INNER JOIN programmes_fidelite pf ON u.niveau_fidelite = pf.niveau
            WHERE u.id_user = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur fidélité: " . $e->getMessage());
        return null;
    }
}

/**
 * Ajoute des points de fidélité
 */
function ajouterPointsFidelite($user_id, $points, $type, $reference_id = null, $description = '') {
    global $pdo;

    try {
        $date_expiration = date('Y-m-d', strtotime('+2 years'));

        $stmt = $pdo->prepare("
            INSERT INTO points_fidelite (id_user, points, type, reference_id, description, date_expiration)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$user_id, $points, $type, $reference_id, $description, $date_expiration]);
    } catch (PDOException $e) {
        error_log("Erreur ajout points: " . $e->getMessage());
        return false;
    }
}

/**
 * Utilise des points de fidélité
 */
function utiliserPointsFidelite($user_id, $points, $description = '') {
    global $pdo;

    try {
        $fidelite = getFideliteUser($user_id);

        if ($fidelite['total_points_fidelite'] < $points) {
            return false; // Pas assez de points
        }

        return ajouterPointsFidelite($user_id, -$points, 'depense', null, $description);
    } catch (PDOException $e) {
        error_log("Erreur utilisation points: " . $e->getMessage());
        return false;
    }
}

/**
 * Calcule la réduction fidélité applicable
 */
function calculerReductionFidelite($user_id, $montant) {
    $fidelite = getFideliteUser($user_id);

    if (!$fidelite) {
        return 0;
    }

    $reduction_pourcent = (float)$fidelite['reduction_pourcentage'];
    return ($montant * $reduction_pourcent) / 100;
}

/**
 * Affiche le badge de niveau
 */
function afficherBadgeFidelite($niveau) {
    $badges = [
        'bronze' => '<span class="badge" style="background: #CD7F32;"><i class="fas fa-medal"></i> Bronze</span>',
        'silver' => '<span class="badge" style="background: #C0C0C0;"><i class="fas fa-medal"></i> Silver</span>',
        'gold' => '<span class="badge" style="background: #FFD700; color: #000;"><i class="fas fa-medal"></i> Gold</span>',
        'platinum' => '<span class="badge" style="background: #E5E4E2;"><i class="fas fa-medal"></i> Platinum</span>',
        'diamond' => '<span class="badge" style="background: linear-gradient(45deg, #b9f2ff, #81d4fa); color: #000;"><i class="fas fa-gem"></i> Diamond</span>'
    ];

    return $badges[$niveau] ?? '<span class="badge bg-secondary">Bronze</span>';
}

/**
 * Vérifie si l'utilisateur a un parrain via code
 */
function appliquerCodeParrainage($user_id, $code_parrainage) {
    global $pdo;

    try {
        // Trouver le parrain
        $stmt = $pdo->prepare("SELECT id_user FROM users WHERE code_parrainage = ? AND id_user != ?");
        $stmt->execute([$code_parrainage, $user_id]);
        $parrain = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$parrain) {
            return false;
        }

        // Mettre à jour l'utilisateur
        $stmt = $pdo->prepare("UPDATE users SET id_parrain = ? WHERE id_user = ?");
        return $stmt->execute([$parrain['id_user'], $user_id]);

    } catch (PDOException $e) {
        error_log("Erreur parrainage: " . $e->getMessage());
        return false;
    }
}
?>
