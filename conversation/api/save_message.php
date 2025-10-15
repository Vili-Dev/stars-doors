<?php
// Added: JSON API to persist chat messages (WebSocket companion)
// Auth required; validates payload; inserts into existing `messages` schema.
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$toId = filter_var($input['toId'] ?? null, FILTER_VALIDATE_INT);
$content = trim($input['content'] ?? '');
$listingId = filter_var($input['listingId'] ?? null, FILTER_VALIDATE_INT);
$subject = isset($input['subject']) && is_string($input['subject']) ? trim($input['subject']) : 'Chat';
$userId = $_SESSION['user_id'];

// Content is required; recipient can be missing → default to self (draft/self-chat)
if ($content === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Content required']);
    exit;
}

if (!$toId || $toId === $userId) {
    $toId = $userId; // Allow self messages when interlocutor is unknown
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (id_expediteur, id_destinataire, id_annonce, sujet, contenu, lu, date_envoi, date_lecture)
                           VALUES (?, ?, ?, ?, ?, 0, NOW(), NULL)");
    $stmt->execute([$userId, $toId, $listingId, $subject, $content]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    error_log('Erreur save_message: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB error']);
}
?>