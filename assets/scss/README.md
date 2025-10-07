# 🎨 Structure SASS - Stars Doors

## 📁 Architecture des fichiers

```
assets/scss/
├── abstracts/           # Variables, fonctions, mixins (pas de CSS généré)
│   ├── _variables.scss  # Toutes les variables du projet
│   ├── _functions.scss  # Fonctions SASS personnalisées
│   └── _mixins.scss     # Mixins réutilisables
├── base/               # Styles de base
│   ├── _reset.scss     # Reset CSS moderne
│   └── _typography.scss # Styles typographiques
├── components/         # Composants réutilisables
│   ├── _buttons.scss   # Tous les styles de boutons
│   ├── _cards.scss     # Cartes et composants similaires
│   ├── _forms.scss     # Éléments de formulaire
│   └── _modals.scss    # Modales et overlays
├── layout/             # Mise en page
│   ├── _navigation.scss # Navigation et menus
│   ├── _header.scss    # En-tête
│   ├── _footer.scss    # Pied de page
│   └── _grid.scss      # Système de grille
├── pages/              # Styles spécifiques aux pages
│   ├── _home.scss      # Page d'accueil
│   ├── _search.scss    # Page de recherche
│   └── _dashboard.scss # Tableau de bord
├── vendors/            # Code tiers
│   └── _bootstrap-overrides.scss
└── main.scss          # Point d'entrée principal
```

## 🚀 Compilation SASS

### Option 1: Node.js + npm

1. **Installation des dépendances :**
```bash
npm install -g sass
```

2. **Compilation en développement :**
```bash
sass assets/scss/main.scss assets/css/style.css --watch --source-map
```

3. **Compilation pour production :**
```bash
sass assets/scss/main.scss assets/css/style.css --style compressed --no-source-map
```

### Option 2: Extension VS Code

1. Installer l'extension "Live Sass Compiler"
2. Configurer dans `settings.json` :
```json
{
  "liveSassCompile.settings.formats": [
    {
      "format": "expanded",
      "extensionName": ".css",
      "savePath": "/assets/css"
    }
  ],
  "liveSassCompile.settings.generateMap": true,
  "liveSassCompile.settings.autoprefix": ["> 1%", "last 2 versions"]
}
```

### Option 3: Script PHP simple

Créer un fichier `compile-sass.php` :
```php
<?php
// Compilation SASS simple avec PHP
$command = 'sass assets/scss/main.scss assets/css/style.css --style compressed';
exec($command, $output, $return_code);

if ($return_code === 0) {
    echo "✅ SASS compilé avec succès !\n";
} else {
    echo "❌ Erreur de compilation SASS\n";
    print_r($output);
}
?>
```

## 🎯 Variables importantes

### Couleurs
```scss
$primary-color: #2c5aa0;      // Bleu principal
$secondary-color: #f8c146;    // Jaune secondaire
$success-color: #28a745;      // Vert succès
$danger-color: #dc3545;       // Rouge danger
```

### Espacements
```scss
$spacer: 1rem;
$spacers: (
  0: 0,
  1: 0.25rem,  // 4px
  2: 0.5rem,   // 8px
  3: 1rem,     // 16px
  4: 1.5rem,   // 24px
  5: 3rem      // 48px
);
```

### Breakpoints
```scss
$breakpoints: (
  xs: 0,
  sm: 576px,
  md: 768px,
  lg: 992px,
  xl: 1200px,
  xxl: 1400px
);
```

## 🔧 Mixins utiles

### Responsive
```scss
@include media-breakpoint-up(md) {
  // Styles pour écrans >= 768px
}

@include media-breakpoint-down(sm) {
  // Styles pour écrans < 576px
}
```

### Flexbox
```scss
@include flex-center;           // Centre avec flexbox
@include flex(column, wrap);    // Flex avec paramètres
```

### Boutons
```scss
@include button-variant($primary-color);
@include button-outline-variant($secondary-color);
```

### Cartes
```scss
@include card();                // Carte de base
@include card-hover();          // Avec effet hover
```

## 📱 Classes utilitaires

### Espacement
```html
<div class="m-3 p-2">           <!-- margin: 1rem, padding: 0.5rem -->
<div class="mt-4 pb-3">         <!-- margin-top: 1.5rem, padding-bottom: 1rem -->
<div class="mx-auto">           <!-- margin: 0 auto -->
```

### Flexbox
```html
<div class="d-flex justify-content-center align-items-center">
<div class="flex-column flex-wrap">
```

### Couleurs
```html
<div class="bg-primary text-white">
<p class="text-muted">
<div class="border border-success">
```

## 🎨 Personnalisation

### Modifier les couleurs
Dans `_variables.scss` :
```scss
$primary-color: #votre-couleur;
$secondary-color: #autre-couleur;
```

### Ajouter un composant
1. Créer `components/_mon-composant.scss`
2. L'importer dans `main.scss` :
```scss
@import 'components/mon-composant';
```

### Styles spécifiques à une page
1. Créer `pages/_ma-page.scss`
2. L'importer dans `main.scss`

## 🐛 Débogage

### Erreurs courantes
1. **"Undefined variable"** : Vérifier l'ordre d'import des variables
2. **"Undefined mixin"** : Importer les mixins avant utilisation
3. **CSS non mis à jour** : Vérifier que la compilation s'exécute

### Tips
- Utiliser `@debug $variable;` pour déboguer les variables
- Compiler avec source maps pour le développement
- Minifier pour la production

## 📦 Intégration avec le projet

Le fichier CSS compilé doit être dans :
```
assets/css/style.css
```

Et inclus dans `includes/header.php` :
```html
<link rel="stylesheet" href="assets/css/style.css">
```

## 🚀 Workflow recommandé

1. **Développement :**
   - Compiler avec `--watch` et source maps
   - Utiliser le navigateur dev tools

2. **Production :**
   - Compiler avec `--style compressed`
   - Supprimer les source maps
   - Tester sur différents navigateurs

## 🔄 Mise à jour

Pour ajouter Bootstrap 5 personnalisé :
1. `npm install bootstrap`
2. Créer `vendors/_bootstrap-custom.scss`
3. Importer seulement les modules nécessaires

Pour plus d'infos : https://sass-lang.com/guide