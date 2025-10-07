# Guide de Refactoring - Stars Doors

## 📋 Objectif
Éliminer le "code spaghetti" en séparant les responsabilités et en créant une architecture propre et maintenable.

---

## 🏗️ Nouvelle Architecture

```
stars-doors/
├── app/
│   ├── Models/          # Couche d'accès aux données
│   ├── Services/        # Logique métier
│   └── Helpers/         # Fonctions utilitaires
├── views/
│   └── partials/        # Composants de vue réutilisables
├── includes/            # Fichiers d'inclusion (config, auth, etc.)
└── *.php               # Pages principales (contrôleurs légers)
```

---

## ✅ Exemple Concret : races.php

### ❌ AVANT (269 lignes - Code spaghetti)

```php
<?php
// SQL directement dans la page
$sql = "SELECT r.*,
        p.nom as planete_origine_nom,
        COUNT(DISTINCT u.id_user) as nb_utilisateurs
        FROM races r
        LEFT JOIN planetes p ON r.id_planete_origine = p.id_planete
        LEFT JOIN users u ON r.id_race = u.id_race
        WHERE 1=1";

$params = [];
if ($sociabilite_filter) {
    $sql .= " AND r.sociabilite = ?";
    $params[] = $sociabilite_filter;
}
// ... 250 lignes de HTML mélangé avec du PHP
?>
<div class="card h-100 shadow-sm hover-lift">
    <div class="card-body">
        <h5><?php echo htmlspecialchars($race['nom']); ?></h5>
        <!-- 100+ lignes de HTML répété pour chaque race -->
    </div>
</div>
```

**Problèmes:**
- SQL mélangé avec la logique de page
- HTML répété (code dupliqué)
- Impossible de tester la logique
- Difficile à maintenir

---

### ✅ APRÈS (156 lignes - Code propre)

#### 1️⃣ **Modèle** (`app/Models/Race.php`)
```php
class Race {
    private $pdo;

    public function getAll($filters = []) {
        $sql = "SELECT r.*, p.nom as planete_origine_nom,
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

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

#### 2️⃣ **Composant Vue** (`views/partials/race-card.php`)
```php
<!-- Carte réutilisable pour afficher une race -->
<div class="card h-100 shadow-sm hover-lift">
    <div class="card-body">
        <h5 class="card-title">
            <i class="fas fa-user-astronaut text-primary"></i>
            <?php echo htmlspecialchars($race['nom']); ?>
        </h5>
        <!-- ... reste du HTML structuré -->
    </div>
</div>
```

#### 3️⃣ **Page principale** (`races.php` - seulement 25 lignes de logique)
```php
<?php
require_once 'app/Models/Race.php';

$raceModel = new Race($pdo);

$filters = [
    'sociabilite' => $_GET['sociabilite'] ?? '',
    'technologie' => $_GET['technologie'] ?? ''
];

$races = $raceModel->getAll(array_filter($filters));

include 'includes/header.php';
?>

<!-- HTML de la page -->
<div class="row">
    <?php foreach ($races as $race): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <?php include 'views/partials/race-card.php'; ?>
        </div>
    <?php endforeach; ?>
</div>
```

---

## 📊 Gains du Refactoring

| Métrique | Avant | Après | Amélioration |
|----------|-------|-------|--------------|
| **Lignes de code** | 269 | 156 (réparti) | -42% |
| **SQL dans la page** | ✅ Oui | ❌ Non | ✅ Séparé |
| **Code dupliqué** | ✅ 100+ lignes | ❌ 0 ligne | ✅ Éliminé |
| **Testabilité** | ❌ Impossible | ✅ Facile | ✅ Améliorée |
| **Maintenabilité** | ❌ Difficile | ✅ Simple | ✅ Améliorée |

---

## 🎯 Pattern à Appliquer

### Pour chaque fichier problématique :

1. **Créer un Model** pour les opérations de base de données
2. **Créer des composants** pour les parties HTML réutilisables
3. **Alléger la page principale** en utilisant Model + composants
4. **Tester** que tout fonctionne correctement

---

## 📂 Fichiers Prioritaires à Refactorer

| Fichier | Lignes | Priorité | Action |
|---------|--------|----------|--------|
| `create_listing.php` | 553 | 🔴 CRITIQUE | Créer Model Listing + composants form |
| `planetes.php` | 521 | 🔴 CRITIQUE | Créer Model Planet + planet-card.php |
| `search.php` | 453 | 🔴 CRITIQUE | Créer SearchService + search-filters.php |
| `planet_detail.php` | 443 | 🔴 CRITIQUE | Utiliser Model Planet + detail-card.php |
| `functions.php` | 423 | 🟡 WARNING | Découper en Helpers séparés |
| `races.php` | 156 | ✅ FAIT | ✅ Exemple terminé |

---

## 💡 Règles d'Or

1. **Une classe = Une responsabilité**
2. **Un fichier < 300 lignes**
3. **Une fonction < 50 lignes**
4. **Pas de SQL dans les pages**
5. **Composants réutilisables pour l'UI**
6. **Tester après chaque modification**

---

## 🚀 Prochaines Étapes

- [ ] Créer `app/Models/User.php`
- [ ] Créer `app/Models/Planet.php`
- [ ] Créer `app/Models/Listing.php`
- [ ] Créer `views/partials/planet-card.php`
- [ ] Créer `views/partials/listing-card.php`
- [ ] Refactorer `planetes.php`
- [ ] Refactorer `search.php`
- [ ] Refactorer `create_listing.php`

---

**Date:** Phase 4 - Refactoring structurel
**Objectif:** Code propre, maintenable et évolutif
