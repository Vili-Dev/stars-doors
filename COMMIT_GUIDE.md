# 📝 Guide pour commiter le projet

## ✅ Vérifications de sécurité effectuées

1. ✅ `.gitignore` créé - Protège les fichiers sensibles
2. ✅ `.env.example` créé - Template de configuration
3. ✅ `SECURITY.md` créé - Guide de sécurité
4. ✅ `.gitkeep` ajoutés - Préserve la structure des dossiers
5. ✅ Aucun fichier sensible trouvé dans git

## 🚀 Commandes pour commiter

### 1. Vérifier ce qui sera commité
```bash
git status
```

### 2. Ajouter les nouveaux fichiers
```bash
# Ajouter tous les fichiers (le .gitignore protège les fichiers sensibles)
git add .

# OU ajouter sélectivement
git add .gitignore
git add SECURITY.md
git add config/.env.example
git add README.md
# ... etc
```

### 3. Vérifier qu'aucun fichier sensible n'est ajouté
```bash
# Vérifier les fichiers qui seront commités
git status

# Voir le détail
git diff --cached --name-only | grep -E "(\.env$|\.sql$|uploads/.*\.(jpg|jpeg|png))"

# Si cette commande retourne quelque chose, NE COMMITEZ PAS !
```

### 4. Faire le commit
```bash
git commit -m "feat: Ajout système de gestion d'annonces avec upload d'images

- Création, modification et suppression d'annonces
- Upload multiple d'images (JPG/PNG/WEBP)
- Correction affichage images (.htaccess)
- Dashboard propriétaire complet
- Sécurité : validation uploads, vérification propriété
- Documentation : README.md, SECURITY.md, SESSION_SUMMARY.md

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"
```

### 5. Push vers le repository
```bash
git push origin main
```

## ⚠️ Fichiers qui SERONT ignorés (c'est normal)

Ces fichiers ne seront PAS commités grâce au `.gitignore` :

- ❌ `config/.env` (si vous en avez créé un)
- ❌ `uploads/annonces/*.jpg` (images uploadées)
- ❌ `uploads/avatars/*`
- ❌ `logs/*.log`
- ❌ `cache/*`
- ❌ `*.sql` (backups de base de données)
- ❌ Fichiers de test temporaires (`test_*.php`)

## ✅ Fichiers qui SERONT commités (c'est normal)

- ✅ Tous les fichiers `.php` du projet
- ✅ `README.md` et autres documentation
- ✅ `.gitignore`
- ✅ `SECURITY.md`
- ✅ `config/.env.example` (pas le vrai .env !)
- ✅ Structure des dossiers (`uploads/annonces/.gitkeep`)
- ✅ `.htaccess` (sécurité)

## 🔍 Checklist avant de commiter

- [ ] Ai-je vérifié `git status` ?
- [ ] Aucun fichier `.env` dans la liste ?
- [ ] Aucun fichier `.sql` dans la liste ?
- [ ] Aucune image d'utilisateur (`uploads/annonces/*.jpg`) ?
- [ ] Le message de commit est descriptif ?
- [ ] Tous les fichiers nécessaires sont inclus ?

## 🆘 En cas de problème

Si vous voyez des fichiers sensibles dans `git status` :

```bash
# Annuler l'ajout
git reset

# Vérifier le .gitignore
cat .gitignore

# Réessayer
git add .
git status
```

---

**Vous êtes prêt à commiter en toute sécurité ! 🎉**
