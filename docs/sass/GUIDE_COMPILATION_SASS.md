# 🔨 Guide de Compilation SASS - Stars Doors

## 📚 Qu'est-ce que la compilation SASS ?

**SASS** (`.scss`) → **Compilation** → **CSS** (`.css`)

Le navigateur ne comprend que le CSS, pas le SCSS. La compilation transforme ton code SCSS en CSS.

```
assets/scss/main.scss  ──►  assets/css/style.css
(Tu modifies ici)          (Le navigateur lit ici)
```

---

## 🚀 Méthode 1 : Live Sass Compiler (⭐ RECOMMANDÉ)

### 📦 Installation

1. **Ouvre VS Code**
2. Va dans l'onglet **Extensions** (Ctrl+Shift+X)
3. Cherche **"Live Sass Compiler"**
4. Clique sur **Install**
5. Redémarre VS Code

### ▶️ Utilisation

#### Démarrer la compilation automatique

1. Ouvre n'importe quel fichier `.scss` dans VS Code
2. Regarde en bas de l'écran
3. Clique sur le bouton **"Watch Sass"**

```
┌─────────────────────────────────────────┐
│ VS Code - Barre du bas                  │
│                                          │
│  [Watch Sass]  ←── Clique ici           │
└─────────────────────────────────────────┘
```

#### Quand c'est activé

Le bouton change :

```
[Watching...] ✅  ←── Compilation automatique active
```

**Maintenant** :
- ✅ Chaque fois que tu **sauvegardes** (Ctrl+S) un fichier SCSS
- ✅ Le CSS est **recompilé automatiquement**
- ✅ Tu vois les résultats dans la console en bas

#### Arrêter la compilation

Clique sur **"Watching..."** pour arrêter.

### 📂 Fichiers générés

Après compilation, tu verras :

```
assets/css/
├── style.css           ← CSS compilé (utilisé par le site)
└── style.css.map       ← Source map (pour le debug)
```

### ✅ Vérifier que ça marche

1. Ouvre `assets/scss/abstracts/_variables.scss`
2. Change une couleur :
   ```scss
   $primary-color: #ff0000;  // Rouge pour tester
   ```
3. Sauvegarde (Ctrl+S)
4. Regarde la console en bas de VS Code :
   ```
   Success: style.css compiled ✓
   ```
5. Ouvre `assets/css/style.css` → La couleur a changé !
6. Rafraîchis ton navigateur (F5) → Le site est rouge !
7. Remets la bonne couleur :
   ```scss
   $primary-color: #2c5aa0;
   ```

---

## 🚀 Méthode 2 : npm (Ligne de commande)

### 📦 Prérequis

Node.js doit être installé. Pour vérifier :

```bash
node --version
# Doit afficher : v18.x.x ou supérieur
```

Si pas installé → [Télécharger Node.js](https://nodejs.org)

### 📦 Installation

**Une seule fois** :

```bash
# Ouvre le terminal dans le dossier du projet
cd C:\wamp64\www\stars-doors

# Installe les dépendances
npm install
```

### ▶️ Compilation unique

Compile une seule fois :

```bash
npm run sass
```

**Résultat** :
```
> sass assets/scss/main.scss assets/css/style.css
Compiled assets/scss/main.scss to assets/css/style.css
```

### 🔄 Compilation automatique (watch mode)

Lance le watch mode (comme Live Sass Compiler) :

```bash
npm run sass:watch
```

**Résultat** :
```
Compiled assets/scss/main.scss to assets/css/style.css
Watching... (Press Ctrl+C to exit)
```

Maintenant :
- ✅ Chaque modification SCSS → Recompilation automatique
- ✅ Laisse le terminal ouvert
- ⏹️ Appuie sur **Ctrl+C** pour arrêter

### 🎯 Compilation pour production

Compile avec minification (fichier plus petit) :

```bash
npm run sass:build
```

**Résultat** : CSS minifié (compact, sans espaces)

---

## 📊 Comparaison des méthodes

| Critère | Live Sass Compiler | npm sass |
|---------|-------------------|----------|
| **Installation** | Extension VS Code | Terminal |
| **Facilité** | ⭐⭐⭐⭐⭐ Très facile | ⭐⭐⭐ Moyen |
| **Interface** | Bouton visuel | Ligne de commande |
| **Watch auto** | ✅ | ✅ |
| **Fonctionne hors VS Code** | ❌ | ✅ |
| **Production build** | ✅ | ✅ |
| **Recommandé pour** | Étudiants, débutants | Experts, CI/CD |

**→ Pour ce projet : utilise Live Sass Compiler (bouton)** ✅

---

## 🎯 Workflow de développement

### Workflow recommandé

```
1. Ouvre VS Code
   │
2. Clique sur "Watch Sass"
   │
3. Modifie les fichiers SCSS
   │
4. Sauvegarde (Ctrl+S)
   │
5. ✅ CSS recompilé automatiquement
   │
6. Rafraîchis le navigateur (F5)
   │
7. Vois les changements !
```

### Fichiers à modifier

```
assets/scss/
│
├── abstracts/_variables.scss  ← 🎨 Couleurs et tailles
├── components/_buttons.scss   ← 🔘 Boutons
├── components/_cards.scss     ← 🃏 Cartes
├── layout/_navigation.scss    ← 🧭 Menu
├── pages/_home.scss           ← 🏠 Page d'accueil
└── base/_typography.scss      ← ✍️ Texte et titres
```

**⚠️ NE PAS MODIFIER** :
- ❌ `assets/css/style.css` (généré automatiquement)
- ❌ `assets/css/style.css.map` (généré automatiquement)

---

## 🐛 Dépannage

### ❌ Problème : Le bouton "Watch Sass" n'apparaît pas

**Solution** :
1. Vérifie que l'extension est installée
2. Ouvre un fichier `.scss`
3. Redémarre VS Code
4. Vérifie en bas à droite de VS Code

### ❌ Problème : Erreur de compilation

**Exemple** :
```
Error: Undefined variable: $ma-couleur
```

**Solution** :
1. Lis le message d'erreur (indique le fichier et la ligne)
2. Ouvre le fichier mentionné
3. Va à la ligne indiquée
4. Corrige l'erreur :
   - Variable non définie ? → Ajoute-la dans `_variables.scss`
   - Accolade manquante ? → Ajoute `{` ou `}`
   - Point-virgule manquant ? → Ajoute `;`

### ❌ Problème : Les changements n'apparaissent pas

**Solutions** :

1. **Vérifie que Watch Sass est actif**
   ```
   [Watching...] ✅  ← Doit être affiché
   ```

2. **Sauvegarde le fichier SCSS**
   ```
   Ctrl+S
   ```

3. **Vérifie la console**
   - Regarde en bas de VS Code
   - Des erreurs ? Corrige-les
   - "Success" ? C'est bon !

4. **Vide le cache du navigateur**
   ```
   Ctrl+Shift+R  (Chrome/Edge)
   Ctrl+F5       (Firefox)
   ```

5. **Vérifie le fichier HTML**
   ```html
   <!-- Dans ton <head> -->
   <link rel="stylesheet" href="assets/css/style.css">
   ✅ Bon chemin
   ```

### ❌ Problème : npm: command not found

**Solution** :
- Node.js n'est pas installé
- [Télécharge et installe Node.js](https://nodejs.org)
- Redémarre le terminal

### ❌ Problème : Warnings (darken, @import)

**C'est normal !** ✅

```
DEPRECATION WARNING [import]: @import is deprecated
WARNING: darken() is deprecated
```

**Ce ne sont PAS des erreurs** :
- ⚠️ Juste des avertissements pour le futur
- ✅ Le code fonctionne parfaitement
- ✅ Le CSS est bien généré
- 📚 Voir `SASS_WARNINGS.md` pour plus d'infos

---

## 📝 Commandes npm disponibles

| Commande | Description |
|----------|-------------|
| `npm run sass` | Compile une fois |
| `npm run sass:watch` | Compile automatiquement (watch) |
| `npm run sass:build` | Compile pour production (minifié) |

---

## 💡 Astuces

### 1. Vérifier si le CSS est à jour

```bash
# Windows
dir assets\css\style.css

# Regarde la date et l'heure
# Doit être récente (après ta dernière modif SCSS)
```

### 2. Forcer une recompilation

Si le CSS ne se met pas à jour :

1. Arrête "Watch Sass"
2. Supprime `assets/css/style.css`
3. Relance "Watch Sass"
4. Sauvegarde un fichier SCSS (Ctrl+S)

### 3. Voir les fichiers compilés en temps réel

Dans VS Code :
1. Ouvre `assets/css/style.css` dans un onglet
2. Lance "Watch Sass"
3. Modifie un SCSS
4. Regarde `style.css` → Il se met à jour en direct !

### 4. Développement avec deux écrans

**Configuration idéale** :
```
Écran 1 : VS Code avec SCSS ouvert
          + "Watch Sass" actif

Écran 2 : Navigateur avec le site
          + Rafraîchissement auto (Live Server)
```

---

## 🎓 Comprendre la compilation

### Qu'est-ce qui est compilé ?

```scss
// ===== SCSS (ce que tu écris) =====
// assets/scss/components/_buttons.scss

$primary-color: #2c5aa0;

.btn-primary {
  background-color: $primary-color;

  &:hover {
    background-color: darken($primary-color, 10%);
  }
}
```

**↓ COMPILATION ↓**

```css
/* ===== CSS (ce que le navigateur lit) ===== */
/* assets/css/style.css */

.btn-primary {
  background-color: #2c5aa0;
}

.btn-primary:hover {
  background-color: #234a7d;
}
```

### Avantages du SCSS

| SCSS | CSS |
|------|-----|
| Variables (`$primary-color`) | Couleurs en dur |
| Imbrication (`&:hover`) | Sélecteurs répétés |
| Mixins responsive | Media queries répétées |
| Organisé en fichiers | Un seul gros fichier |

---

## 🔍 Structure de compilation

```
main.scss                 ← Point d'entrée
├── @import variables     ← Charge _variables.scss
├── @import functions     ← Charge _functions.scss
├── @import mixins        ← Charge _mixins.scss
├── @import reset         ← Charge _reset.scss
├── @import typography    ← Charge _typography.scss
├── @import buttons       ← Charge _buttons.scss
├── @import cards         ← Charge _cards.scss
└── Classes utilitaires   ← Code dans main.scss

         ↓ COMPILATION ↓

style.css                 ← UN SEUL fichier CSS
```

---

## ✅ Checklist avant de travailler

- [ ] Extension "Live Sass Compiler" installée
- [ ] "Watch Sass" activé en bas
- [ ] Console ouverte (pour voir les erreurs)
- [ ] Navigateur ouvert sur le site

## ✅ Checklist après modification

- [ ] Fichier SCSS sauvegardé (Ctrl+S)
- [ ] "Success" dans la console VS Code
- [ ] Navigateur rafraîchi (F5)
- [ ] Changements visibles sur le site

---

## 🎯 Résumé rapide

**Pour compiler le SASS** :

1. **Clique sur "Watch Sass"** (en bas de VS Code)
2. **Modifie tes fichiers SCSS**
3. **Sauvegarde** (Ctrl+S)
4. **C'est tout !** ✅

**Le CSS se recompile automatiquement !**

---

## 📚 Ressources

- [GUIDE_SCSS_SIMPLE.md](GUIDE_SCSS_SIMPLE.md) - Comment modifier les styles
- [SIMPLIFICATION_COMPLETE.md](SIMPLIFICATION_COMPLETE.md) - Récapitulatif
- [SASS_WARNINGS.md](SASS_WARNINGS.md) - Comprendre les warnings
- [Documentation SASS](https://sass-lang.com/documentation)

---

**Bonne compilation ! 🚀**
