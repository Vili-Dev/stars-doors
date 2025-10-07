<?php
// Utilitaires de gestion du transport spatial

if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

/**
 * Récupère tous les vaisseaux disponibles
 * @param string $type Filtrer par type (null = tous)
 * @return array Liste des vaisseaux
 */
function getVaisseaux($type = null) {
    global $pdo;

    try {
        $sql = "SELECT * FROM vaisseaux WHERE statut = 'actif'";
        $params = [];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY prix_base_par_al ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération vaisseaux: " . $e->getMessage());
        return [];
    }
}

/**
 * Calcule le coût et la durée d'un voyage spatial
 * @param int $id_planete_depart
 * @param int $id_planete_arrivee
 * @param int $id_vaisseau
 * @param int $id_race Race du voyageur pour compatibilité
 * @param int $duree_sejour_jours Durée du séjour pour calculer coût adaptation
 * @return array|null Détails du voyage ou null si erreur
 */
function calculerVoyage($id_planete_depart, $id_planete_arrivee, $id_vaisseau, $id_race, $duree_sejour_jours) {
    global $pdo;

    try {
        // Récupérer les infos des planètes
        $stmt = $pdo->prepare("SELECT * FROM planetes WHERE id_planete IN (?, ?)");
        $stmt->execute([$id_planete_depart, $id_planete_arrivee]);
        $planetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($planetes) !== 2) {
            return null;
        }

        $depart = $planetes[0]['id_planete'] == $id_planete_depart ? $planetes[0] : $planetes[1];
        $arrivee = $planetes[0]['id_planete'] == $id_planete_arrivee ? $planetes[0] : $planetes[1];

        // Récupérer le vaisseau
        $stmt = $pdo->prepare("SELECT * FROM vaisseaux WHERE id_vaisseau = ? AND statut = 'actif'");
        $stmt->execute([$id_vaisseau]);
        $vaisseau = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vaisseau) {
            return null;
        }

        // Calculer la distance (en AL)
        $distance = abs($arrivee['distance_terre'] - $depart['distance_terre']);

        // Calculer la durée du voyage en heures
        // Formule: (distance / vitesse_lumiere) * 365.25 jours * 24 heures
        $duree_heures = ($distance / $vaisseau['vitesse_lumiere']) * 365.25 * 24;

        // Calculer le coût du transport
        $cout_transport = $distance * $vaisseau['prix_base_par_al'];

        // Vérifier la compatibilité atmosphérique
        $stmt = $pdo->prepare("
            SELECT * FROM compatibilite_atmospherique
            WHERE id_race = ? AND id_planete = ?
        ");
        $stmt->execute([$id_race, $id_planete_arrivee]);
        $compatibilite = $stmt->fetch(PDO::FETCH_ASSOC);

        $cout_adaptation = 0;
        $equipements_requis = [];
        $niveau_compatibilite = 'inconnu';
        $duree_adaptation_heures = 0;

        if ($compatibilite) {
            $niveau_compatibilite = $compatibilite['niveau_compatibilite'];
            $cout_adaptation = $compatibilite['cout_adaptation_journalier'] * $duree_sejour_jours;
            $equipements_requis = json_decode($compatibilite['equipement_requis'] ?? '[]', true);
            $duree_adaptation_heures = $compatibilite['duree_adaptation'];
        }

        // Coût total
        $cout_total = $cout_transport + $cout_adaptation;

        return [
            'distance_al' => round($distance, 2),
            'duree_heures' => round($duree_heures, 2),
            'duree_jours' => round($duree_heures / 24, 1),
            'cout_transport' => round($cout_transport, 2),
            'cout_adaptation' => round($cout_adaptation, 2),
            'cout_total' => round($cout_total, 2),
            'vaisseau' => $vaisseau,
            'planete_depart' => $depart,
            'planete_arrivee' => $arrivee,
            'compatibilite' => $niveau_compatibilite,
            'equipements_requis' => $equipements_requis,
            'duree_adaptation_heures' => $duree_adaptation_heures,
            'risques' => $compatibilite['risques'] ?? null,
            'recommandations' => $compatibilite['recommandations'] ?? null
        ];
    } catch (PDOException $e) {
        error_log("Erreur calcul voyage: " . $e->getMessage());
        return null;
    }
}

/**
 * Crée une réservation de transport
 * @param int $id_reservation ID de la réservation principale
 * @param int $id_vaisseau
 * @param int $id_planete_depart
 * @param int $id_planete_arrivee
 * @param string $date_depart
 * @param array $details Détails du voyage (depuis calculerVoyage)
 * @return int|false ID du voyage créé ou false
 */
function creerVoyageTransport($id_reservation, $id_vaisseau, $id_planete_depart, $id_planete_arrivee, $date_depart, $details) {
    global $pdo;

    try {
        $date_arrivee = date('Y-m-d H:i:s', strtotime($date_depart) + ($details['duree_heures'] * 3600));

        $stmt = $pdo->prepare("
            INSERT INTO voyage_transport (
                id_reservation, id_vaisseau, id_planete_depart, id_planete_arrivee,
                date_depart, date_arrivee_estimee, distance_al, duree_voyage_heures,
                prix_transport, prix_adaptation, prix_total, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'planifie')
        ");

        $stmt->execute([
            $id_reservation,
            $id_vaisseau,
            $id_planete_depart,
            $id_planete_arrivee,
            $date_depart,
            $date_arrivee,
            $details['distance_al'],
            $details['duree_heures'],
            $details['cout_transport'],
            $details['cout_adaptation'],
            $details['cout_total']
        ]);

        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Erreur création voyage: " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie la compatibilité atmosphérique d'une race sur une planète
 * @param int $id_race
 * @param int $id_planete
 * @return array|null Détails de compatibilité
 */
function verifierCompatibilite($id_race, $id_planete) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT ca.*, p.nom as planete_nom, r.nom as race_nom
            FROM compatibilite_atmospherique ca
            INNER JOIN planetes p ON ca.id_planete = p.id_planete
            INNER JOIN races r ON ca.id_race = r.id_race
            WHERE ca.id_race = ? AND ca.id_planete = ?
        ");
        $stmt->execute([$id_race, $id_planete]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur vérification compatibilité: " . $e->getMessage());
        return null;
    }
}

/**
 * Affiche un badge de niveau de compatibilité
 * @param string $niveau
 * @return string HTML du badge
 */
function afficherBadgeCompatibilite($niveau) {
    $badges = [
        'natif' => '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Natif</span>',
        'compatible' => '<span class="badge bg-info"><i class="fas fa-thumbs-up"></i> Compatible</span>',
        'adaptable' => '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Adaptable</span>',
        'hostile' => '<span class="badge bg-danger"><i class="fas fa-skull-crossbones"></i> Hostile</span>',
        'mortel' => '<span class="badge bg-dark"><i class="fas fa-times-circle"></i> Mortel</span>'
    ];

    return $badges[$niveau] ?? '<span class="badge bg-secondary">Inconnu</span>';
}

/**
 * Récupère les transports d'un utilisateur
 * @param int $user_id
 * @return array Liste des voyages
 */
function getTransportsUtilisateur($user_id) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT vt.*,
                v.nom as vaisseau_nom,
                v.type as classe,
                pd.nom as depart_nom,
                pa.nom as arrivee_nom,
                r.id_annonce
            FROM voyage_transport vt
            INNER JOIN reservations r ON vt.id_reservation = r.id_reservation
            INNER JOIN vaisseaux v ON vt.id_vaisseau = v.id_vaisseau
            INNER JOIN planetes pd ON vt.id_planete_depart = pd.id_planete
            INNER JOIN planetes pa ON vt.id_planete_arrivee = pa.id_planete
            WHERE r.id_user = ?
            ORDER BY vt.date_depart DESC
        ");
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération transports: " . $e->getMessage());
        return [];
    }
}

/**
 * Affiche un sélecteur de vaisseau avec détails
 * @param array $vaisseaux Liste des vaisseaux
 * @param float $distance Distance du voyage en AL
 * @param int $duree_sejour Durée du séjour en jours
 */
function afficherSelecteurVaisseau($vaisseaux, $distance, $duree_sejour = 1) {
    foreach ($vaisseaux as $v) {
        $cout = $distance * $v['prix_base_par_al'];
        $duree = ($distance / $v['vitesse_lumiere']) * 365.25 * 24;
        $equipements = json_decode($v['equipements'] ?? '[]', true);

        $classe_badge = [
            'economique' => 'secondary',
            'business' => 'primary',
            'premiere_classe' => 'warning',
            'cargo' => 'dark',
            'luxe' => 'danger'
        ];

        echo '<div class="card mb-3 vaisseau-option" data-id="' . $v['id_vaisseau'] . '" data-prix="' . $cout . '">';
        echo '<div class="card-body">';
        echo '<div class="row align-items-center">';

        // Colonne 1: Image et nom
        echo '<div class="col-md-3">';
        echo '<h5 class="mb-1">' . htmlspecialchars($v['nom']) . '</h5>';
        echo '<span class="badge bg-' . ($classe_badge[$v['type']] ?? 'secondary') . '">';
        echo ucfirst(str_replace('_', ' ', $v['type']));
        echo '</span>';
        echo '<p class="text-muted small mb-0 mt-1">Par ' . htmlspecialchars($v['constructeur']) . '</p>';
        echo '</div>';

        // Colonne 2: Specs
        echo '<div class="col-md-3">';
        echo '<small class="text-muted">';
        echo '<i class="fas fa-rocket"></i> Vitesse: ' . $v['vitesse_lumiere'] . 'c<br>';
        echo '<i class="fas fa-clock"></i> Durée: ' . round($duree / 24, 1) . ' jours<br>';
        echo '<i class="fas fa-users"></i> ' . $v['capacite_passagers'] . ' passagers';
        echo '</small>';
        echo '</div>';

        // Colonne 3: Confort et équipements
        echo '<div class="col-md-4">';
        echo '<div class="mb-1">';
        echo '<small>Confort: </small>';
        for ($i = 0; $i < $v['confort_score']; $i++) {
            echo '<i class="fas fa-star text-warning"></i>';
        }
        echo '</div>';
        echo '<small class="text-muted">';
        if (!empty($equipements)) {
            echo implode(', ', array_slice($equipements, 0, 3));
            if (count($equipements) > 3) {
                echo '...';
            }
        }
        echo '</small>';
        echo '</div>';

        // Colonne 4: Prix
        echo '<div class="col-md-2 text-end">';
        echo '<h4 class="text-primary mb-0">' . number_format($cout, 2) . ' ₢</h4>';
        echo '<small class="text-muted">Transport</small>';
        echo '<button type="button" class="btn btn-primary btn-sm w-100 mt-2 select-vaisseau" data-id="' . $v['id_vaisseau'] . '">';
        echo '<i class="fas fa-check"></i> Sélectionner';
        echo '</button>';
        echo '</div>';

        echo '</div>'; // row
        echo '</div>'; // card-body
        echo '</div>'; // card
    }
}

/**
 * Récupère les trajets populaires
 * @param int $limit Nombre de résultats
 * @return array Liste des trajets
 */
function getTrajetsPopulaires($limit = 5) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT
                pd.nom as depart,
                pa.nom as arrivee,
                COUNT(*) as nb_voyages,
                AVG(vt.prix_total) as prix_moyen
            FROM voyage_transport vt
            INNER JOIN planetes pd ON vt.id_planete_depart = pd.id_planete
            INNER JOIN planetes pa ON vt.id_planete_arrivee = pa.id_planete
            GROUP BY vt.id_planete_depart, vt.id_planete_arrivee
            ORDER BY nb_voyages DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur trajets populaires: " . $e->getMessage());
        return [];
    }
}
?>
