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
    $stmt = $pdo->prepare("INSERT INTO messages (id_expediteur, id_destinataire, contenu, priorite, date_envoi)
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $toId, $content, 'normale']);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    error_log('Erreur save_message: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB error']);
}
?>