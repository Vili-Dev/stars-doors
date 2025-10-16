<?php
/**
 * Script intelligent d'attribution d'images aux annonces
 * Basé sur l'analyse visuelle et les correspondances thématiques
 */

require_once 'includes/config.php';
require_once 'includes/database.php';

// Dossier source des images
$sourceDir = __DIR__ . '/assets/images/';
$destDir = __DIR__ . '/uploads/annonces/';

// Créer le dossier destination s'il n'existe pas
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

// Récupérer toutes les images disponibles (exclure bg.jpeg et bgsite.jpeg)
$allImages = glob($sourceDir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
$allImages = array_filter($allImages, function($img) {
    $basename = basename($img);
    return !in_array($basename, ['bg.jpeg', 'bgsite.jpeg', 'bgsite1.jpeg', 'bgsite1.webp']);
});
$allImages = array_values($allImages);

echo "Images disponibles : " . count($allImages) . "\n\n";

// MAPPING INTELLIGENT basé sur l'analyse visuelle
// Catégorisation des images par thème

$imageCategories = [
    // Images de capsules spatiales / stations orbitales
    'space_capsule' => [
        'mg7wmgqc3aprx5.jpeg', 'mg7wp84hc2hdhc.jpeg', 'mg7wpa8efet9x3.jpeg',
        'mg7x0tx06tfnkd.jpeg', 'mg7x15hx6rp132.jpeg', 'mg7x5y16pq2sjx.jpeg',
        'mg7x5z1edcq6k4.jpeg', 'mg7wyaypev9ktg.jpeg', 'mg7wzfjwunwlpt.jpeg',
        'mgf2pswh6sa1ec.jpeg'
    ],

    // Images sous-marines / aquatiques
    'underwater' => [
        'mg7xgd9yebfuip.jpeg', 'mg7y9wn8v1io68.jpeg', 'mg7yabwdep5qak.jpeg'
    ],

    // Images de nature / forêt / écologique
    'nature_forest' => [
        'mg7y9wn8v1io68.jpeg', 'mg7yabwdep5qak.jpeg', 'mg9n2b52f7k11g.jpeg'
    ],

    // Images de tunnels / grottes / souterrain
    'cave_tunnel' => [
        'mg7x5hhr9aly5s.jpeg'
    ],

    // Images futuristes / ville / tech
    'futuristic_city' => [
        'mg6jshx1puedgl.jpeg', 'mgf2jxytffgtqa.jpeg'
    ],

    // Images de dômes / observatoires
    'dome_observatory' => [
        'mg7xhh0saqp7o2.jpeg', 'mg7xpc9l4y8lgv.jpeg'
    ],

    // Images d'aliens / personnages (décoration)
    'aliens_characters' => [
        'mg9n4c50pvklp8.jpeg', 'mg9n5q99fdwkbf.jpeg', 'mg9n8n9uzzlh9x.jpeg',
        'mgf2mth87if3kn.jpeg', 'mgf2mvaor27enr.jpeg', 'mgf2n3d8l6ny1k.jpeg',
        'mgf2nzrthle1s0.jpeg', 'mgf2o7refjxtkx.jpeg', 'mgf2pba5g54ju3.jpeg'
    ]
];

// MAPPING ANNONCES → CATÉGORIES D'IMAGES
$listingImageMapping = [
    // TERRE (1-4)
    1 => ['futuristic_city', 'nature_forest', 'aliens_characters'], // Loft Parisien
    2 => ['nature_forest', 'underwater', 'aliens_characters'], // Villa Vue Mer
    3 => ['futuristic_city', 'space_capsule', 'aliens_characters'], // Studio Tokyo
    4 => ['nature_forest', 'dome_observatory', 'aliens_characters'], // Chalet Alpin

    // MARS (5-9)
    5 => ['dome_observatory', 'space_capsule', 'futuristic_city'], // Dôme Prestige
    6 => ['cave_tunnel', 'space_capsule', 'aliens_characters'], // Habitat Souterrain
    7 => ['space_capsule', 'futuristic_city', 'aliens_characters'], // Studio Recherche
    8 => ['nature_forest', 'dome_observatory', 'aliens_characters'], // Eco-Dôme
    9 => ['space_capsule', 'aliens_characters', 'futuristic_city'], // Tiny House Mobile

    // PROXIMA CENTAURI B (10-13)
    10 => ['futuristic_city', 'space_capsule', 'dome_observatory'], // Tour Céleste
    11 => ['space_capsule', 'dome_observatory', 'aliens_characters'], // Capsule Orbite
    12 => ['nature_forest', 'dome_observatory', 'aliens_characters'], // Éco-Habitat
    13 => ['futuristic_city', 'space_capsule', 'dome_observatory'], // Penthouse IA

    // KEPLER-442B (14-17)
    14 => ['nature_forest', 'dome_observatory', 'aliens_characters'], // Cabane Suspendue
    15 => ['underwater', 'nature_forest', 'aliens_characters'], // Villa Flottante Lac
    16 => ['cave_tunnel', 'dome_observatory', 'nature_forest'], // Grotte Cristaux
    17 => ['dome_observatory', 'space_capsule', 'nature_forest'], // Dôme Astronomique

    // TITAN (18-21)
    18 => ['space_capsule', 'cave_tunnel', 'aliens_characters'], // Station Polaire
    19 => ['underwater', 'space_capsule', 'aliens_characters'], // Bungalow Lac Méthane
    20 => ['cave_tunnel', 'space_capsule', 'dome_observatory'], // Refuge Géothermique
    21 => ['dome_observatory', 'space_capsule', 'futuristic_city'], // Dôme Vue Saturne

    // NABOO (22-25)
    22 => ['underwater', 'futuristic_city', 'nature_forest'], // Suite Royale Palais Eaux
    23 => ['nature_forest', 'dome_observatory', 'aliens_characters'], // Villa Pastorale
    24 => ['futuristic_city', 'dome_observatory', 'nature_forest'], // Penthouse Royal Theed
    25 => ['nature_forest', 'underwater', 'dome_observatory'], // Cottage Romantique Lac

    // CORUSCANT (26-29)
    26 => ['futuristic_city', 'space_capsule', 'dome_observatory'], // Sky Penthouse
    27 => ['futuristic_city', 'space_capsule', 'aliens_characters'], // Studio Fonctionnel
    28 => ['futuristic_city', 'nature_forest', 'dome_observatory'], // Loft d'Artiste
    29 => ['futuristic_city', 'nature_forest', 'aliens_characters'], // Résidence Familiale

    // BUDGET (30-31)
    30 => ['space_capsule', 'aliens_characters', 'futuristic_city'], // Capsule Économique
    31 => ['space_capsule', 'aliens_characters', 'futuristic_city']  // Chambre Partagée
];

// Fonction pour obtenir des images d'une catégorie
function getImagesFromCategory($category, $allImages, $imageCategories, $count = 2) {
    $categoryImages = [];
    if (isset($imageCategories[$category])) {
        foreach ($imageCategories[$category] as $imageName) {
            foreach ($allImages as $fullPath) {
                if (basename($fullPath) === $imageName) {
                    $categoryImages[] = $fullPath;
                }
            }
        }
    }

    // Si pas assez d'images dans la catégorie, prendre au hasard
    if (count($categoryImages) < $count) {
        shuffle($allImages);
        while (count($categoryImages) < $count && count($allImages) > 0) {
            $randomImage = array_shift($allImages);
            if (!in_array($randomImage, $categoryImages)) {
                $categoryImages[] = $randomImage;
            }
        }
    }

    return array_slice($categoryImages, 0, $count);
}

// Récupérer toutes les annonces
try {
    $stmt = $pdo->query("SELECT id_annonce, titre, type_logement FROM annonces ORDER BY id_annonce");
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Annonces trouvées : " . count($annonces) . "\n\n";

    if (empty($annonces)) {
        die("Aucune annonce trouvée dans la base de données.\n");
    }

    $usedImages = [];
    $imagePool = $allImages; // Pool d'images restantes

    foreach ($annonces as $annonce) {
        $annonceId = $annonce['id_annonce'];
        echo "Traitement de l'annonce #{$annonceId}: {$annonce['titre']}\n";

        // Supprimer les anciennes photos
        $stmtDelete = $pdo->prepare("SELECT chemin FROM photo WHERE id_annonce = ?");
        $stmtDelete->execute([$annonceId]);
        $oldPhotos = $stmtDelete->fetchAll(PDO::FETCH_COLUMN);

        foreach ($oldPhotos as $oldPhoto) {
            if (file_exists($oldPhoto)) {
                unlink($oldPhoto);
            }
        }

        $pdo->prepare("DELETE FROM photo WHERE id_annonce = ?")->execute([$annonceId]);

        // Obtenir les catégories pour cette annonce
        $categories = $listingImageMapping[$annonceId] ?? ['space_capsule', 'futuristic_city', 'aliens_characters'];

        $assignedImages = [];
        $numImages = rand(5, 7);

        // Assigner des images de chaque catégorie
        foreach ($categories as $category) {
            $imagesNeeded = ceil($numImages / count($categories));
            $categoryImages = getImagesFromCategory($category, $imagePool, $imageCategories, $imagesNeeded);
            $assignedImages = array_merge($assignedImages, $categoryImages);

            // Retirer les images utilisées du pool
            $imagePool = array_diff($imagePool, $categoryImages);
            $imagePool = array_values($imagePool);
        }

        // Compléter avec des images aléatoires si nécessaire
        while (count($assignedImages) < $numImages && count($imagePool) > 0) {
            $randomImage = array_shift($imagePool);
            $assignedImages[] = $randomImage;
        }

        // Limiter au nombre d'images souhaité
        $assignedImages = array_slice($assignedImages, 0, $numImages);

        // Insérer les images
        foreach ($assignedImages as $i => $sourceImage) {
            $extension = pathinfo($sourceImage, PATHINFO_EXTENSION);
            $newFilename = 'img_' . $annonceId . '_' . uniqid() . '.' . $extension;
            $destPath = $destDir . $newFilename;

            if (copy($sourceImage, $destPath)) {
                $relativePath = 'uploads/annonces/' . $newFilename;
                $isMainPhoto = ($i === 0) ? 1 : 0;

                $stmtInsert = $pdo->prepare("
                    INSERT INTO photo (id_annonce, nom_fichier, chemin, description, ordre, photo_principale, date_upload)
                    VALUES (?, ?, ?, '', ?, ?, NOW())
                ");
                $stmtInsert->execute([
                    $annonceId,
                    $newFilename,
                    $relativePath,
                    $i,
                    $isMainPhoto
                ]);

                echo "  ✓ Image $i: " . basename($sourceImage) . " -> $newFilename" . ($isMainPhoto ? " [PRINCIPALE]" : "") . "\n";
                $usedImages[] = $sourceImage;
            } else {
                echo "  ✗ Erreur lors de la copie de: " . basename($sourceImage) . "\n";
            }
        }

        echo "  Total: " . count($assignedImages) . " images ajoutées\n\n";

        // Réalimenter le pool si on manque d'images
        if (count($imagePool) < 10) {
            $imagePool = array_merge($imagePool, array_diff($allImages, $usedImages));
            shuffle($imagePool);
        }
    }

    echo "\n========================================\n";
    echo "TERMINÉ !\n";
    echo "========================================\n";
    echo "Total annonces traitées: " . count($annonces) . "\n";
    echo "Images uniques utilisées: " . count(array_unique($usedImages)) . "\n";

} catch (PDOException $e) {
    die("Erreur base de données: " . $e->getMessage() . "\n");
}
