# 🎨 Guide de Compilation SCSS - Stars Doors

## 🚀 Méthodes de compilation

### ✅ Méthode 1 : Double-cliquer sur les fichiers .bat (Recommandé)

Tu as maintenant 2 fichiers batch dans ton projet :

#### 🔄 **compile-watch.bat** - Pour le développement
- Double-clique dessus
- Laisse la fenêtre ouverte
- Modifie tes fichiers SCSS
- Sauvegarde (Ctrl+S)
- ✨ Le CSS se recompile automatiquement !
- Appuie sur Ctrl+C pour arrêter

#### 🚀 **compile-prod.bat** - Pour la production
- Double-clique dessus
- Compile en mode minifié (fichier plus petit)
- Ferme automatiquement quand c'est fini

---

### Méthode 2 : Via PowerShell

```powershell
# Aller dans le dossier
cd C:\wamp64\www\stars-doors

# Compilation avec watch (dev)
C:\wamp64\bin\php\php8.2.26\php.exe compile-sass.php dev --watch

# Compilation unique (dev)
C:\wamp64\bin\php\php8.2.26\php.exe compile-sass.php dev

# Compilation production (minifié)
C:\wamp64\bin\php\php8.2.26\php.exe compile-sass.php prod
```

---

### Méthode 3 : Via SASS direct (si installé)

```powershell
# Dev avec watch
sass assets/scss/main.scss assets/css/style.css --watch

# Production
sass assets/scss/main.scss assets/css/style.css --style compressed
```

---

## 📝 Workflow de développement

### 1. Lancer la compilation automatique
- Double-clique sur **compile-watch.bat**
- Laisse la fenêtre ouverte (ne la ferme pas !)

### 2. Modifier les SCSS
```
assets/scss/
├── abstracts/_variables.scss    ← Couleurs, tailles
├── components/_buttons.scss     ← Style des boutons
├── components/_cards.scss       ← Style des cartes
├── layout/_navigation.scss      ← Menu/nav
├── pages/_home.scss            ← Page d'accueil
└── ... autres fichiers
```

### 3. Voir les changements
- Sauvegarde ton fichier SCSS (Ctrl+S)
- Le terminal affiche : "✅ Compilation réussie !"
- Rafraîchis ton navigateur (F5)
- Profite ! 🎉

---

## 🎨 Exemples de modifications

### Changer la couleur principale
```scss
// Fichier: assets/scss/abstracts/_variables.scss

$primary-color: #7c3aed;  // Violet au lieu de bleu
```

### Modifier l'arrondi des boutons
```scss
// Fichier: assets/scss/components/_buttons.scss

.btn {
  border-radius: 20px;  // Plus arrondi
}
```

### Changer le style de la navigation
```scss
// Fichier: assets/scss/layout/_navigation.scss

.navbar {
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
```

---

## ⚠️ Important

### ❌ NE JAMAIS modifier
- `assets/css/style.css` (généré automatiquement)
- `assets/css/style.css.map` (source map)

### ✅ TOUJOURS modifier
- Les fichiers dans `assets/scss/`

---

## 🐛 Problèmes courants

### "php n'est pas reconnu"
➡️ Utilise les fichiers `.bat` au lieu de taper les commandes

### "sass n'est pas reconnu"
➡️ Utilise la méthode avec PHP (fichiers `.bat`)

### Le CSS ne se met pas à jour
1. Vérifie que compile-watch.bat est ouvert
2. Vérifie qu'il affiche "✅ Compilation réussie"
3. Vide le cache du navigateur (Ctrl+Shift+R)

### Erreur de syntaxe SCSS
➡️ Le terminal affiche l'erreur exacte avec le numéro de ligne
➡️ Corrige le fichier SCSS et sauvegarde

---

## 📦 Avant de commiter

### Mode développement
```
✅ Fichiers SCSS modifiés → Commit OK
✅ style.css généré → Commit OK
```

### Avant mise en production
1. Double-clique sur **compile-prod.bat**
2. Commit le `style.css` minifié

---

## 🆘 Besoin d'aide ?

Consulte la documentation SASS complète :
- `assets/scss/README.md`

---

**🎨 Bon design ! ✨**
