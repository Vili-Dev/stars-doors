<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    header('Location: avis.php?error=invalid_id');
    exit;
}

try {
    // Vérifier que l'avis existe
    $stmt = $pdo->prepare("SELECT id_avis, statut FROM avis WHERE id_avis = :id");
    $stmt->execute([':id' => $id]);
    $avis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$avis) {
        header('Location: avis.php?error=not_found');
        exit;
    }
    
    // Approuver l'avis
    $updateStmt = $pdo->prepare("UPDATE avis SET statut = 'approuve', visible = 1 WHERE id_avis = :id");
    $updateStmt->execute([':id' => $id]);
    
    header('Location: avis.php?success=approved');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur approbation avis: " . $e->getMessage());
    header('Location: avis.php?error=action_failed');
    exit;
}