<?php
// Endpoint AJAX pour conversion de monnaie en temps réel
session_start();
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/currency.php';

header('Content-Type: application/json');

// Récupération des paramètres
$montant = filter_input(INPUT_GET, 'montant', FILTER_VALIDATE_FLOAT);
$source = filter_input(INPUT_GET, 'source', FILTER_SANITIZE_STRING);
$cible = filter_input(INPUT_GET, 'cible', FILTER_SANITIZE_STRING);

if ($montant === false || !$source || !$cible) {
    echo json_encode([
        'success' => false,
        'error' => 'Paramètres invalides'
    ]);
    exit;
}

try {
    $montant_converti = convertirMonnaie($montant, $source, $cible);

    echo json_encode([
        'success' => true,
        'montant_original' => $montant,
        'montant_converti' => $montant_converti,
        'monnaie_source' => $source,
        'monnaie_cible' => $cible,
        'taux' => $montant > 0 ? ($montant_converti / $montant) : 0
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la conversion'
    ]);
}
?>
