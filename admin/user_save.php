<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

requireLogin();
requireAdmin();

// Vérifier CSRF
if (!function_exists('verify_csrf')) {
    function verify_csrf(): void {
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Jeton CSRF invalide.');
        }
    }
}
verify_csrf();

$id = isset($_POST['id_user']) ? (int)$_POST['id_user'] : null;

// Récupérer les données
$prenom = trim($_POST['prenom']);
$nom = trim($_POST['nom']);
$email = trim($_POST['email']);
$telephone = trim($_POST['telephone']);
$date_naissance = !empty($_POST['date_de_naissance']) ? $_POST['date_de_naissance'] : null;
$role = $_POST['role'];
$actif = isset($_POST['actif']) ? 1 : 0;
$bio = trim($_POST['bio']);
$mot_de_passe = $_POST['mot_de_passe'];

try {
    if ($id) {
        // MISE À JOUR
        $sql = "UPDATE users SET prenom=:prenom, nom=:nom, email=:email, telephone=:telephone, 
                date_de_naissance=:date_naissance, role=:role, actif=:actif, bio=:bio";
        
        $params = [
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':date_naissance' => $date_naissance,
            ':role' => $role,
            ':actif' => $actif,
            ':bio' => $bio,
            ':id' => $id
        ];
        
        // Si nouveau mot de passe
        if (!empty($mot_de_passe)) {
            $sql .= ", mot_de_passe=:mot_de_passe";
            $params[':mot_de_passe'] = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id_user=:id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
    } else {
        // CRÉATION
        if (empty($mot_de_passe)) {
            header('Location: user_edit.php?error=password_required');
            exit;
        }
        
        $sql = "INSERT INTO users (prenom, nom, email, telephone, date_de_naissance, role, actif, bio, mot_de_passe) 
                VALUES (:prenom, :nom, :email, :telephone, :date_naissance, :role, :actif, :bio, :mot_de_passe)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':date_naissance' => $date_naissance,
            ':role' => $role,
            ':actif' => $actif,
            ':bio' => $bio,
            ':mot_de_passe' => password_hash($mot_de_passe, PASSWORD_DEFAULT)
        ]);
    }
    
    header('Location: users.php?success=1');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur save user: " . $e->getMessage());
    header('Location: user_edit.php?error=1' . ($id ? "&id=$id" : ''));
    exit;
}