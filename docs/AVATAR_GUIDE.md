# Guide des Avatars - Stars Doors

## 🌟 Nouvelles Fonctionnalités

### 1. Upload d'Avatar dans le Profil

- **Localisation** : Page "Mon profil" (`profile.php`)
- **Fonctionnalité** : Les utilisateurs peuvent maintenant uploader une photo de profil
- **Formats acceptés** : JPG, PNG, GIF, WebP (max 5MB)
- **Stockage** : Dans le dossier `uploads/avatars/`

### 2. Affichage des Avatars dans les Cartes

#### Dashboard (Tableau de bord)

- **En-tête** : Avatar de l'utilisateur connecté avec sa race
- **Réservations reçues** : Avatar des locataires avec leur race
- **Mes annonces** : Avatar du propriétaire avec sa race

#### Page d'Accueil

- **Cartes d'annonces** : Avatar du propriétaire avec sa race
- **Informations** : Nom du propriétaire et badge de race

#### Profil Utilisateur

- **Section avatar** : Photo de profil avec possibilité de changement
- **Aperçu** : Affichage de l'avatar actuel (120px)

### 3. Avatars par Défaut avec Couleurs par Race

Les utilisateurs sans avatar ont un avatar par défaut avec des couleurs selon leur race :

| Race       | Couleur    | Classe CSS     |
| ---------- | ---------- | -------------- |
| Humain     | Bleu       | `bg-primary`   |
| Alien      | Vert       | `bg-success`   |
| Robot      | Gris       | `bg-secondary` |
| Vulcain    | Rouge      | `bg-danger`    |
| Klingon    | Noir       | `bg-dark`      |
| Andorien   | Bleu clair | `bg-info`      |
| Betazoid   | Jaune      | `bg-warning`   |
| Ferengi    | Vert       | `bg-success`   |
| Bajoran    | Bleu clair | `bg-info`      |
| Cardassian | Gris       | `bg-secondary` |
| Romulan    | Noir       | `bg-dark`      |
| Borg       | Rouge      | `bg-danger`    |
| Jedi       | Bleu       | `bg-primary`   |
| Sith       | Rouge      | `bg-danger`    |
| Wookiee    | Jaune      | `bg-warning`   |

## 🛠️ Fonctions Utilitaires

### `generateAvatarHtml($avatar, $race, $size, $additionalClasses)`

Génère le HTML complet pour un avatar (image ou placeholder).

**Paramètres :**

- `$avatar` : Chemin vers l'image (peut être vide)
- `$race` : Race de l'utilisateur (pour la couleur par défaut)
- `$size` : Taille en pixels
- `$additionalClasses` : Classes CSS supplémentaires

### `getAvatarColorClass($race)`

Retourne la classe CSS Bootstrap correspondant à la race.

## 📁 Structure des Fichiers

```
uploads/
└── avatars/
    ├── img_1234567890_1234567890.jpg
    ├── img_1234567890_1234567891.png
    └── ...
```

## 🔧 Installation

1. **Base de données** : Exécuter le script `database/add_avatar_column.sql`
2. **Dossier uploads** : S'assurer que le dossier `uploads/avatars/` existe et est accessible en écriture
3. **Permissions** : Vérifier les permissions du dossier d'upload (755 ou 777 selon la configuration)

## 🎨 Personnalisation

### Ajouter de nouvelles races avec couleurs

Modifier la fonction `getAvatarColorClass()` dans `includes/functions.php` :

```php
$raceColors = [
    'NouvelleRace' => 'bg-warning',
    // ... autres races
];
```

### Modifier la taille des avatars

Les avatars s'adaptent automatiquement selon la taille demandée :

- **35px** : Petite icône (0.8rem)
- **50px** : Icône standard (1rem)
- **120px** : Grande icône (1.5rem)

## 🔒 Sécurité

- **Validation des fichiers** : Vérification du type MIME et de l'extension
- **Taille limitée** : Maximum 5MB par image
- **Noms uniques** : Génération de noms de fichiers uniques
- **Nettoyage** : Suppression de l'ancien avatar lors du changement

## 🐛 Dépannage

### Avatar ne s'affiche pas

1. Vérifier que le fichier existe dans `uploads/avatars/`
2. Vérifier les permissions du dossier
3. Vérifier que le chemin dans la base de données est correct

### Upload échoue

1. Vérifier la taille du fichier (< 5MB)
2. Vérifier le format (JPG, PNG, GIF, WebP)
3. Vérifier les permissions d'écriture du dossier
4. Consulter les logs d'erreur PHP
