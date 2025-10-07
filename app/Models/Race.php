<?php
/**
 * Model Race
 * Gère toutes les opérations liées aux races galactiques
 */
class Race
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les races avec filtres optionnels
     * @param array $filters ['sociabilite' => 'normale', 'technologie' => 'futuriste']
     * @return array
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT r.*,
                p.nom as planete_origine_nom,
                COUNT(DISTINCT u.id_user) as nb_utilisateurs
                FROM races r
                LEFT JOIN planetes p ON r.id_planete_origine = p.id_planete
                LEFT JOIN users u ON r.id_race = u.id_race
                WHERE 1=1";

        $params = [];

        if (!empty($filters['sociabilite'])) {
            $sql .= " AND r.sociabilite = ?";
            $params[] = $filters['sociabilite'];
        }

        if (!empty($filters['technologie'])) {
            $sql .= " AND r.niveau_technologie = ?";
            $params[] = $filters['technologie'];
        }

        $sql .= " GROUP BY r.id_race ORDER BY r.nom ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur Race::getAll() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère une race par son ID
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT r.*,
                       p.nom as planete_origine_nom,
                       COUNT(DISTINCT u.id_user) as nb_utilisateurs
                FROM races r
                LEFT JOIN planetes p ON r.id_planete_origine = p.id_planete
                LEFT JOIN users u ON r.id_race = u.id_race
                WHERE r.id_race = ?
                GROUP BY r.id_race
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erreur Race::getById() - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les races pour un select (id + nom)
     * @return array
     */
    public function getAllForSelect()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT id_race, nom, description, image_race
                FROM races
                ORDER BY nom ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur Race::getAllForSelect() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si une race existe
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM races WHERE id_race = ?");
            $stmt->execute([$id]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur Race::exists() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compte le nombre total de races
     * @return int
     */
    public function count()
    {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM races")->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur Race::count() - " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compte les races par niveau technologique
     * @param string $niveau
     * @return int
     */
    public function countByTechLevel($niveau)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM races WHERE niveau_technologie = ?");
            $stmt->execute([$niveau]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur Race::countByTechLevel() - " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les statistiques des races
     * @return array
     */
    public function getStatistics()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT
                    COUNT(DISTINCT r.id_race) as total_races,
                    COUNT(DISTINCT u.id_user) as total_users,
                    COUNT(DISTINCT r.id_planete_origine) as total_origin_planets,
                    SUM(CASE WHEN r.niveau_technologie = 'futuriste' THEN 1 ELSE 0 END) as futuristic_count
                FROM races r
                LEFT JOIN users u ON r.id_race = u.id_race
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur Race::getStatistics() - " . $e->getMessage());
            return [
                'total_races' => 0,
                'total_users' => 0,
                'total_origin_planets' => 0,
                'futuristic_count' => 0
            ];
        }
    }
}

