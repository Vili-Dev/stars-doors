<?php
// Added: Message service helpers for chat module (load users, history, mark read, send)
function getInterlocutor(PDO $pdo, int $userId): ?array {
    $stmt = $pdo->prepare("SELECT u.*, r.nom as race_nom, p.nom as planete_nom, p.distance_terre_al AS distance_terre, p.galaxie
                           FROM users u
                           LEFT JOIN races r ON u.id_race = r.id_race
                           LEFT JOIN planetes p ON u.planete_residence = p.id_planete
                           WHERE u.id_user = ?");
    $stmt->execute([$userId]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res ?: null;
}

function getConversationMessages(PDO $pdo, int $meId, int $otherId, int $limit = 100): array {
    if (!$otherId) {
        // Fallback: self-chat when interlocutor is missing
        $stmt = $pdo->prepare("SELECT m.*, u.prenom, u.nom
                               FROM messages m
                               INNER JOIN users u ON m.id_expediteur = u.id_user
                               WHERE m.id_expediteur = ? AND m.id_destinataire = ?
                               ORDER BY m.date_envoi ASC
                               LIMIT ?");
        $stmt->execute([$meId, $meId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $stmt = $pdo->prepare("SELECT m.*, u.prenom, u.nom
                           FROM messages m
                           INNER JOIN users u ON m.id_expediteur = u.id_user
                           WHERE (m.id_expediteur = ? AND m.id_destinataire = ?)
                              OR (m.id_expediteur = ? AND m.id_destinataire = ?)
                           ORDER BY m.date_envoi ASC
                           LIMIT ?");
    $stmt->execute([$meId, $otherId, $otherId, $meId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markAsRead(PDO $pdo, int $meId, int $otherId): void {
    $stmt = $pdo->prepare("UPDATE messages SET lu = 1
                           WHERE id_destinataire = ? AND id_expediteur = ? AND lu = 0");
    $stmt->execute([$meId, $otherId]);
}

function sendMessage(PDO $pdo, int $fromId, int $toId, string $content, string $priority = 'normale'): void {
    $stmt = $pdo->prepare("INSERT INTO messages (id_expediteur, id_destinataire, contenu, priorite, date_envoi)
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$fromId, $toId, $content, $priority]);
}
?>