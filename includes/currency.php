<?php
// Utilitaires de gestion des monnaies galactiques

if (!defined('PHP_VERSION_ID')) {
    die('Accès direct interdit');
}

/**
 * Convertit un montant d'une monnaie à une autre
 * @param float $montant
 * @param string $code_source Code de la monnaie source
 * @param string $code_cible Code de la monnaie cible
 * @return float Montant converti
 */
function convertirMonnaie($montant, $code_source = 'CRG', $code_cible = 'CRG') {
    global $pdo;

    if ($code_source === $code_cible) {
        return $montant;
    }

    try {
        // Utiliser la fonction SQL pour la conversion
        $stmt = $pdo->prepare("SELECT convertir_monnaie(?, ?, ?) as montant_converti");
        $stmt->execute([$montant, $code_source, $code_cible]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (float)$result['montant_converti'] : $montant;
    } catch (PDOException $e) {
        error_log("Erreur conversion monnaie: " . $e->getMessage());
        return $montant; // Retourner le montant original en cas d'erreur
    }
}

/**
 * Récupère toutes les monnaies actives
 * @return array Liste des monnaies
 */
function getMonnaies() {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM monnaies WHERE statut = 'active' ORDER BY code ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération monnaies: " . $e->getMessage());
        return [];
    }
}

/**
 * Formate un montant avec le symbole de la monnaie
 * @param float $montant
 * @param string $code_monnaie
 * @return string Montant formaté
 */
function formatMontant($montant, $code_monnaie = 'CRG') {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT symbole FROM monnaies WHERE code = ?");
        $stmt->execute([$code_monnaie]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $symbole = $result ? $result['symbole'] : $code_monnaie;
        return number_format($montant, 2, ',', ' ') . ' ' . $symbole;
    } catch (PDOException $e) {
        return number_format($montant, 2, ',', ' ') . ' ' . $code_monnaie;
    }
}

/**
 * Récupère le taux de change vers CRG
 * @param string $code_monnaie
 * @return float Taux de change
 */
function getTauxChange($code_monnaie) {
    global $pdo;

    if ($code_monnaie === 'CRG') {
        return 1.0;
    }

    try {
        $stmt = $pdo->prepare("SELECT taux_vers_credit_galactique FROM monnaies WHERE code = ?");
        $stmt->execute([$code_monnaie]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (float)$result['taux_vers_credit_galactique'] : 1.0;
    } catch (PDOException $e) {
        error_log("Erreur récupération taux: " . $e->getMessage());
        return 1.0;
    }
}

/**
 * Affiche un widget de conversion de monnaie
 * @param float $montant Montant en CRG par défaut
 * @param string $monnaie_source
 */
function afficherConvertisseur($montant, $monnaie_source = 'CRG') {
    $monnaies = getMonnaies();

    echo '<div class="currency-converter card">';
    echo '<div class="card-body">';
    echo '<h6 class="card-title"><i class="fas fa-exchange-alt"></i> Convertisseur de Monnaie</h6>';
    echo '<div class="row g-2">';
    echo '<div class="col-md-5">';
    echo '<input type="number" class="form-control" id="montant_source" value="' . $montant . '" step="0.01">';
    echo '<select class="form-select mt-1" id="monnaie_source">';

    foreach ($monnaies as $m) {
        $selected = ($m['code'] === $monnaie_source) ? 'selected' : '';
        echo '<option value="' . $m['code'] . '" ' . $selected . '>' . $m['symbole'] . ' ' . $m['nom'] . '</option>';
    }

    echo '</select>';
    echo '</div>';
    echo '<div class="col-md-2 text-center d-flex align-items-center justify-content-center">';
    echo '<i class="fas fa-arrow-right text-primary"></i>';
    echo '</div>';
    echo '<div class="col-md-5">';
    echo '<input type="number" class="form-control" id="montant_cible" readonly>';
    echo '<select class="form-select mt-1" id="monnaie_cible">';

    foreach ($monnaies as $m) {
        echo '<option value="' . $m['code'] . '">' . $m['symbole'] . ' ' . $m['nom'] . '</option>';
    }

    echo '</select>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Script de conversion en temps réel
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const montantSource = document.getElementById("montant_source");
        const montantCible = document.getElementById("montant_cible");
        const monnaieSource = document.getElementById("monnaie_source");
        const monnaieCible = document.getElementById("monnaie_cible");

        function convertir() {
            const montant = parseFloat(montantSource.value) || 0;
            const source = monnaieSource.value;
            const cible = monnaieCible.value;

            fetch("ajax_convert_currency.php?montant=" + montant + "&source=" + source + "&cible=" + cible)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        montantCible.value = data.montant_converti.toFixed(2);
                    }
                })
                .catch(error => console.error("Erreur conversion:", error));
        }

        montantSource.addEventListener("input", convertir);
        monnaieSource.addEventListener("change", convertir);
        monnaieCible.addEventListener("change", convertir);

        convertir(); // Conversion initiale
    });
    </script>';
}

/**
 * Crédite le compte d'un utilisateur
 * @param int $user_id
 * @param float $montant
 * @param string $code_monnaie
 * @return bool Succès
 */
function crediterCompte($user_id, $montant, $code_monnaie = 'CRG') {
    global $pdo;

    try {
        // Convertir en CRG
        $montant_crg = convertirMonnaie($montant, $code_monnaie, 'CRG');

        $stmt = $pdo->prepare("UPDATE users SET solde_credits_galactiques = solde_credits_galactiques + ? WHERE id_user = ?");
        return $stmt->execute([$montant_crg, $user_id]);
    } catch (PDOException $e) {
        error_log("Erreur crédit compte: " . $e->getMessage());
        return false;
    }
}

/**
 * Débite le compte d'un utilisateur
 * @param int $user_id
 * @param float $montant
 * @param string $code_monnaie
 * @return bool Succès
 */
function debiterCompte($user_id, $montant, $code_monnaie = 'CRG') {
    global $pdo;

    try {
        // Convertir en CRG
        $montant_crg = convertirMonnaie($montant, $code_monnaie, 'CRG');

        // Vérifier le solde
        $stmt = $pdo->prepare("SELECT solde_credits_galactiques FROM users WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['solde_credits_galactiques'] < $montant_crg) {
            return false; // Solde insuffisant
        }

        $stmt = $pdo->prepare("UPDATE users SET solde_credits_galactiques = solde_credits_galactiques - ? WHERE id_user = ?");
        return $stmt->execute([$montant_crg, $user_id]);
    } catch (PDOException $e) {
        error_log("Erreur débit compte: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère le solde d'un utilisateur
 * @param int $user_id
 * @param string $code_monnaie Monnaie dans laquelle afficher le solde
 * @return float Solde
 */
function getSolde($user_id, $code_monnaie = 'CRG') {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT solde_credits_galactiques FROM users WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return 0;
        }

        $solde_crg = (float)$user['solde_credits_galactiques'];

        if ($code_monnaie === 'CRG') {
            return $solde_crg;
        }

        return convertirMonnaie($solde_crg, 'CRG', $code_monnaie);
    } catch (PDOException $e) {
        error_log("Erreur récupération solde: " . $e->getMessage());
        return 0;
    }
}
?>
