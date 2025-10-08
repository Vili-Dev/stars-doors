# 📚 Documentation SASS - Stars Doors

## 🎯 Guides disponibles

Le code SASS a été simplifié pour être plus facile à modifier. Voici tous les guides disponibles :

---

## 📖 Pour débuter

### 1. [GUIDE_COMPILATION_SASS.md](GUIDE_COMPILATION_SASS.md) ⭐ COMMENCER ICI
**→ Comment compiler le SASS**

- Installation de Live Sass Compiler
- Utilisation du bouton "Watch Sass"
- Méthode npm (alternative)
- Dépannage des erreurs
- Workflow de développement

**À lire en premier !**

---

### 2. [GUIDE_SCSS_SIMPLE.md](GUIDE_SCSS_SIMPLE.md) ⭐ MODIFIER LES STYLES
**→ Comment modifier l'apparence du site**

- Changer les couleurs
- Modifier les boutons
- Personnaliser les cartes
- Adapter la page d'accueil
- Exemples concrets (thème violet, boutons arrondis, etc.)

**À lire pour modifier le design !**

---

## 📊 Comprendre la simplification

### 3. [SIMPLIFICATION_COMPLETE.md](SIMPLIFICATION_COMPLETE.md)
**→ Récapitulatif complet de la simplification**

- Statistiques (60% de code en moins)
- Comparaison avant/après
- Ce qui a été supprimé
- Ce qui a été conservé
- Avantages pour les étudiants

**Pour comprendre ce qui a changé**

---

### 4. [SASS_SIMPLIFICATION.md](SASS_SIMPLIFICATION.md)
**→ Plan détaillé de la simplification**

- Problèmes identifiés
- Objectifs de simplification
- Plan technique détaillé
- Changements fichier par fichier

**Documentation technique**

---

## 🎨 Structure du projet

```
stars-doors/
│
├── 📚 DOCUMENTATION SASS
│   ├── README_SASS.md                    ← Ce fichier (index)
│   ├── GUIDE_COMPILATION_SASS.md         ← ⭐ Comment compiler
│   ├── GUIDE_SCSS_SIMPLE.md              ← ⭐ Comment modifier
│   ├── SIMPLIFICATION_COMPLETE.md        ← Récapitulatif
│   └── SASS_SIMPLIFICATION.md            ← Plan technique
│
├── 📁 CODE SCSS (À MODIFIER)
│   └── assets/scss/
│       ├── abstracts/
│       │   ├── _variables.scss           ← 🎨 Couleurs, tailles
│       │   ├── _mixins.scss              ← 3 mixins responsive
│       │   └── _functions.scss           ← Vide (simplifié)
│       ├── base/
│       │   ├── _reset.scss               ← Reset CSS
│       │   └── _typography.scss          ← Texte, titres, liens
│       ├── components/
│       │   ├── _buttons.scss             ← Boutons
│       │   └── _cards.scss               ← Cartes
│       ├── layout/
│       │   └── _navigation.scss          ← Navigation
│       ├── pages/
│       │   └── _home.scss                ← Page d'accueil
│       └── main.scss                     ← Point d'entrée
│
└── 📁 CSS GÉNÉRÉ (NE PAS MODIFIER)
    └── assets/css/
        ├── style.css                     ← CSS compilé
        └── style.css.map                 ← Source map
```

---

## 🚀 Quick Start (Démarrage rapide)

### 1. Installer

```bash
# Option 1 : Live Sass Compiler (Recommandé)
# → Installer l'extension dans VS Code

# Option 2 : npm
npm install
```

### 2. Lancer la compilation

**Méthode visuelle (recommandée)** :
1. Ouvre un fichier `.scss` dans VS Code
2. Clique sur **"Watch Sass"** en bas
3. ✅ C'est tout !

**Méthode terminal** :
```bash
npm run sass:watch
```

### 3. Modifier les styles

Ouvre un fichier SCSS et modifie :

```scss
// assets/scss/abstracts/_variables.scss

// Change la couleur principale
$primary-color: #7c3aed;  // Violet

// Change la taille du texte
$font-size-base: 1.125rem;  // Plus gros
```

**Sauvegarde** (Ctrl+S) → Le CSS se recompile automatiquement ✨

### 4. Voir le résultat

Rafraîchis ton navigateur (F5) → Le site a changé ! 🎉

---

## 🎯 Parcours d'apprentissage

### Niveau 1 : Débuter
1. ✅ Lis [GUIDE_COMPILATION_SASS.md](GUIDE_COMPILATION_SASS.md)
2. ✅ Lance "Watch Sass"
3. ✅ Lis [GUIDE_SCSS_SIMPLE.md](GUIDE_SCSS_SIMPLE.md)
4. ✅ Change une couleur dans `_variables.scss`
5. ✅ Sauvegarde et regarde le résultat

### Niveau 2 : Personnaliser
1. ✅ Change toutes les couleurs du thème
2. ✅ Modifie les boutons (`_buttons.scss`)
3. ✅ Personnalise les cartes (`_cards.scss`)
4. ✅ Adapte la page d'accueil (`_home.scss`)

### Niveau 3 : Maîtriser
1. ✅ Comprends la structure complète
2. ✅ Utilise les mixins responsive
3. ✅ Crée tes propres composants
4. ✅ Lis [SIMPLIFICATION_COMPLETE.md](SIMPLIFICATION_COMPLETE.md)

---

## 💡 Exemples rapides

### Changer le thème en violet

```scss
// assets/scss/abstracts/_variables.scss
$primary-color: #7c3aed;      // Violet
$secondary-color: #fbbf24;    // Jaune doré
```

### Boutons arrondis

```scss
// assets/scss/components/_buttons.scss
.btn {
  border-radius: 50rem;  // Complètement arrondi
  padding: 0.75rem 1.5rem;
}
```

### Hero plus haut

```scss
// assets/scss/pages/_home.scss
.hero-section {
  min-height: 600px;  // Au lieu de 400px
  padding: 8rem 0;    // Au lieu de 5rem
}
```

---

## 🐛 Problèmes courants

### ❌ Les changements n'apparaissent pas
1. Vérifie que "Watch Sass" est actif
2. Sauvegarde le fichier (Ctrl+S)
3. Rafraîchis avec Ctrl+Shift+R (vide le cache)

### ❌ Erreur de compilation
1. Lis l'erreur (indique le fichier et la ligne)
2. Ouvre le fichier
3. Corrige l'erreur (accolade, point-virgule, variable)

### ⚠️ Warnings (darken, @import)
**C'est normal !** Ces warnings ne cassent rien, le code fonctionne.

---

## 📊 Statistiques

| Métrique | Valeur |
|----------|--------|
| **Fichiers SCSS** | 10 fichiers |
| **Lignes de code** | ~1417 lignes (-60%) |
| **Fonctions complexes** | 0 (supprimées) |
| **Mixins** | 3 simples uniquement |
| **Variables** | 50+ variables simples |
| **Temps de compilation** | < 1 seconde |

---

## ✅ Checklist

### Avant de commencer
- [ ] Node.js installé (optionnel)
- [ ] VS Code installé
- [ ] Extension "Live Sass Compiler" installée
- [ ] Documentation lue

### Pour développer
- [ ] "Watch Sass" activé
- [ ] Console ouverte (pour voir les erreurs)
- [ ] Fichiers SCSS ouverts
- [ ] Navigateur ouvert

### Après modification
- [ ] Fichier sauvegardé (Ctrl+S)
- [ ] "Success" dans la console
- [ ] Navigateur rafraîchi (F5)
- [ ] Résultat vérifié

---

## 🎓 Compétences nécessaires

### Pour utiliser ce projet

**Requis** ✅
- Connaître les bases du CSS
- Savoir utiliser VS Code
- Comprendre les variables

**Optionnel** ⭐
- Connaître l'imbrication SCSS
- Comprendre les media queries
- Savoir utiliser le terminal

**Pas nécessaire** ❌
- Fonctions SASS avancées
- Mixins complexes
- Boucles @each/@for
- Maps SASS

**→ Le code a été simplifié pour être accessible !**

---

## 🆘 Support

### Documentation
- Consulte les guides ci-dessus
- Lis les commentaires dans le code SCSS
- Regarde les exemples dans les guides

### Ressources externes
- [Documentation SASS officielle](https://sass-lang.com/documentation)
- [Guide SASS en français](https://www.alsacreations.com/tuto/lire/1717-guide-sass-debutant.html)

---

## 📝 Notes importantes

### ⚠️ NE PAS MODIFIER
- `assets/css/style.css` (généré automatiquement)
- `assets/css/style.css.map` (généré automatiquement)

### ✅ À MODIFIER
- Tous les fichiers dans `assets/scss/`
- Surtout `abstracts/_variables.scss` pour les couleurs

### 🎯 Workflow recommandé
1. Active "Watch Sass"
2. Modifie un fichier SCSS
3. Sauvegarde (Ctrl+S)
4. Rafraîchis le navigateur (F5)
5. Recommence !

---

## 🎉 C'est parti !

**Tu es prêt à modifier le style du site !**

1. Commence par [GUIDE_COMPILATION_SASS.md](GUIDE_COMPILATION_SASS.md)
2. Puis [GUIDE_SCSS_SIMPLE.md](GUIDE_SCSS_SIMPLE.md)
3. Amuse-toi bien ! 🚀

---

**Dernière mise à jour** : Octobre 2025
**Version** : SASS Simplifié v1.0
