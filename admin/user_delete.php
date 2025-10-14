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
    header('Location: users.php?error=invalid_id');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_user, email FROM users WHERE id_user = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: users.php?error=user_not_found');
        exit;
    }
    
    // Empêcher suppression de son propre compte
    if ($id === (int)$_SESSION['user_id']) {
        header('Location: users.php?error=cannot_delete_self');
        exit;
    }
    
    // Supprimer
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
    $deleteStmt->execute([':id' => $id]);
    
    header('Location: users.php?success=deleted');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur suppression: " . $e->getMessage());
    header('Location: users.php?error=delete_failed');
    exit;
}