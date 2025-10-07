# 🎨 Configuration SASS - Stars Doors

## 📦 Option 1 : npm sass (Recommandé)

### Installation

```bash
# Installer les dépendances
npm install

# Ou si npm n'est pas installé, installer sass globalement
npm install -g sass
```

### Commandes disponibles

```bash
# Compilation unique (dev)
npm run sass

# Compilation avec watch (auto-recompile)
npm run sass:watch

# Compilation production (minifié)
npm run sass:build
```

### Workflow de développement

```bash
# 1. Lancer le watch
npm run sass:watch

# 2. Modifier les fichiers SCSS
# 3. Sauvegarder (Ctrl+S)
# 4. Le CSS se recompile automatiquement
# 5. Rafraîchir le navigateur (F5)
```

---

## 🔴 Option 2 : VS Code Live Sass Compiler (Plus simple)

### Installation de l'extension

1. Ouvre VS Code
2. Va dans Extensions (Ctrl+Shift+X)
3. Cherche "**Live Sass Compiler**" par Glenn Marks
4. Clique sur "Installer"

### Configuration

✅ **Déjà configuré !** Le fichier `.vscode/settings.json` est créé.

### Utilisation

#### Méthode 1 : Via le bouton (Super simple !)

1. Ouvre n'importe quel fichier `.scss` dans VS Code
2. En bas de la fenêtre, clique sur **"Watch Sass"**
3. Le bouton devient **"Watching..."** avec un cercle qui tourne
4. Modifie tes SCSS et sauvegarde
5. Le CSS se recompile automatiquement ! ✨

#### Méthode 2 : Via la palette de commandes

1. `Ctrl+Shift+P`
2. Tape "Live Sass"
3. Choisis "**Live Sass: Watch Sass**"

### Pour arrêter la surveillance

- Clique sur **"Watching..."** en bas
- Ou `Ctrl+Shift+P` → "Live Sass: Stop Watching"

---

## 📊 Comparaison

| Fonctionnalité | npm sass | Live Sass Compiler |
|---|---|---|
| Installation | Terminal | Extension VS Code |
| Lancement | Terminal | Bouton dans VS Code |
| Auto-compilation | ✅ | ✅ |
| Facile à utiliser | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Fonctionne sans VS Code | ✅ | ❌ |
| Production build | ✅ | ✅ |

---

## 🎯 Recommandation

### Pour le développement
→ **Live Sass Compiler** (extension VS Code)
- Le plus simple
- Visuel avec le bouton
- Pas besoin de terminal

### Pour la production / CI/CD
→ **npm sass**
- Scriptable
- Fonctionne partout
- Compilation minifiée

---

## 🗂️ Structure des fichiers

```
assets/
├── scss/
│   ├── abstracts/
│   │   ├── _variables.scss    ← Couleurs, tailles
│   │   ├── _mixins.scss
│   │   └── _functions.scss
│   ├── base/
│   │   ├── _reset.scss
│   │   └── _typography.scss
│   ├── components/
│   │   ├── _buttons.scss      ← Boutons
│   │   ├── _cards.scss        ← Cartes
│   │   └── _forms.scss
│   ├── layout/
│   │   ├── _navigation.scss   ← Menu
│   │   ├── _header.scss
│   │   └── _footer.scss
│   ├── pages/
│   │   ├── _home.scss         ← Page d'accueil
│   │   ├── _search.scss
│   │   └── _dashboard.scss
│   └── main.scss              ← Point d'entrée (compile celui-ci)
└── css/
    ├── style.css              ← Généré automatiquement
    └── style.css.map          ← Source map (dev)
```

---

## 🎨 Exemples de modifications

### Changer la couleur principale

```scss
// Fichier: assets/scss/abstracts/_variables.scss

$primary-color: #7c3aed;  // Change cette ligne
```

**Résultat** : Tout le site utilise la nouvelle couleur ! 🟣

### Modifier le style des boutons

```scss
// Fichier: assets/scss/components/_buttons.scss

.btn {
  border-radius: 25px;      // Plus arrondi
  padding: 12px 24px;       // Plus grand
  font-weight: 600;         // Plus gras
  text-transform: uppercase; // Texte en majuscules
}
```

### Personnaliser la navigation

```scss
// Fichier: assets/scss/layout/_navigation.scss

.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}
```

---

## ⚠️ Important

### ❌ Ne JAMAIS modifier directement
- `assets/css/style.css` (écrasé à chaque compilation)
- `assets/css/style.css.map`

### ✅ TOUJOURS modifier
- Les fichiers dans `assets/scss/`

---

## 🐛 Problèmes courants

### "npm n'est pas reconnu"
➡️ Installe Node.js : https://nodejs.org/

### "Watch Sass" n'apparaît pas
➡️ Vérifie que l'extension est bien installée
➡️ Ouvre un fichier `.scss` dans VS Code

### Les changements n'apparaissent pas
1. Vérifie que le watch est actif (bouton "Watching...")
2. Vérifie qu'il n'y a pas d'erreur dans le fichier SCSS
3. Vide le cache du navigateur (Ctrl+Shift+R)

### Erreur de syntaxe SCSS
➡️ L'erreur s'affiche dans la console VS Code
➡️ Corrige le fichier et sauvegarde à nouveau

---

## 📦 Avant de commiter

### Fichiers à commiter
```
✅ assets/scss/**/*.scss    (tes modifications)
✅ assets/css/style.css     (généré)
✅ package.json             (config npm)
✅ .vscode/settings.json    (config VS Code)
```

### Fichiers à ignorer (déjà dans .gitignore)
```
❌ node_modules/
❌ package-lock.json
❌ .sass-cache/
```

---

## 🚀 Quick Start

### Avec VS Code Live Sass Compiler (Le + simple)

1. Ouvre VS Code
2. Ouvre le dossier `stars-doors`
3. Ouvre `assets/scss/main.scss`
4. Clique sur **"Watch Sass"** en bas
5. Modifie tes SCSS
6. Profite ! 🎉

### Avec npm

1. Ouvre un terminal
2. `cd C:\wamp64\www\stars-doors`
3. `npm install` (une seule fois)
4. `npm run sass:watch`
5. Modifie tes SCSS
6. Profite ! 🎉

---

**🎨 Bon design ! ✨**
