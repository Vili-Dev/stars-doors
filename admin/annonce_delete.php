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
    header('Location: annonces.php?error=invalid_id');
    exit;
}

try {
    // Vérifier que l'annonce existe
    $stmt = $pdo->prepare("SELECT id_annonce, titre FROM annonces WHERE id_annonce = :id");
    $stmt->execute([':id' => $id]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$annonce) {
        header('Location: annonces.php?error=not_found');
        exit;
    }
    
    // Supprimer l'annonce
    $deleteStmt = $pdo->prepare("DELETE FROM annonces WHERE id_annonce = :id");
    $deleteStmt->execute([':id' => $id]);
    
    header('Location: annonces.php?success=deleted');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur suppression annonce: " . $e->getMessage());
    header('Location: annonces.php?error=action_failed');
    exit;
}