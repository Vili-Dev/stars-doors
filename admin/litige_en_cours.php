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
    header('Location: litige.php?error=invalid_id');
    exit;
}

try {
    // Vérifier que la litige existe
    $stmt = $pdo->prepare("SELECT id_litige, statut FROM litiges WHERE id_litige = :id");
    $stmt->execute([':id' => $id]);
    $litige = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$litige) {
        header('Location: litige.php?error=not_found');
        exit;
    }
    
    // Mettre la litige en cours
    $updateStmt = $pdo->prepare("UPDATE litiges SET statut = 'en_cours' WHERE id_litige = :id");
    $updateStmt->execute([':id' => $id]);
    
    header('Location: litige.php?success=en_cours');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur approbation litige: " . $e->getMessage());
    header('Location: litige.php?error=action_failed');
    exit;
}