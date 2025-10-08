# 🔐 Sécurité - Stars Doors

## ⚠️ Fichiers sensibles

Les fichiers suivants ne doivent **JAMAIS** être commités sur Git :

### 🚫 Fichiers de configuration

- `config/.env` - Contient les mots de passe et secrets
- Tout fichier contenant des clés API
- Fichiers de credentials

### 🚫 Uploads utilisateurs

- `uploads/annonces/*` - Photos uploadées par les utilisateurs
- `uploads/avatars/*` - Avatars des utilisateurs
- Tout contenu généré par les utilisateurs

### 🚫 Logs et cache

- `logs/*.log` - Peuvent contenir des informations sensibles
- `cache/*` - Données temporaires

### 🚫 Backups de base de données

- `*.sql` - Contiennent toutes les données utilisateurs
- `database/backups/*`

## ✅ Configuration sécurisée

### 1. Copier le fichier d'exemple

```bash
cp config/.env.example config/.env
```

### 2. Modifier les valeurs sensibles

Éditez `config/.env` et changez :

- `DB_PASS` - Mot de passe MySQL
- `SECRET_KEY` - Générez une clé aléatoire longue
- `ADMIN_EMAIL` - Votre email

### 3. Générer une clé secrète sécurisée

```php
<?php
echo bin2hex(random_bytes(32));
?>
```

## 🛡️ Bonnes pratiques

### Production

1. ✅ `ENVIRONMENT=production`
2. ✅ `DEBUG_MODE=false`
3. ✅ Changez `SECRET_KEY`
4. ✅ Utilisez des mots de passe forts
5. ✅ Activez HTTPS
6. ✅ Configurez les permissions des dossiers :
   ```bash
   chmod 755 uploads/
   chmod 755 cache/
   chmod 755 logs/
   ```

### Sauvegardes

1. ⚠️ Ne commitez JAMAIS les backups SQL sur Git
2. ✅ Stockez-les dans un emplacement sécurisé séparé
3. ✅ Chiffrez les backups contenant des données sensibles

### Uploads

1. ✅ Le dossier `uploads/` est protégé par `.htaccess`
2. ✅ Les fichiers PHP ne peuvent pas s'exécuter dans `uploads/`
3. ✅ Validation MIME type + taille + extension

## 🔍 Vérifier avant commit

Avant chaque commit, vérifiez :

```bash
# Vérifier qu'aucun fichier sensible n'est ajouté
git status

# Voir le contenu exact qui sera commité
git diff --cached

# Vérifier le .gitignore
cat .gitignore
```

## 🚨 En cas de fuite

Si vous avez accidentellement commité un fichier sensible :

```bash
# Supprimer le fichier de l'historique
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch config/.env" \
  --prune-empty --tag-name-filter cat -- --all

# Force push (ATTENTION : coordonnez avec l'équipe)
git push origin --force --all

# Changez IMMÉDIATEMENT tous les secrets exposés
# - Mots de passe BDD
# - Clés API
# - Tokens
```

## 📋 Checklist avant déploiement

- [ ] `config/.env` configuré et sécurisé
- [ ] `SECRET_KEY` changé
- [ ] `ENVIRONMENT=production`
- [ ] `DEBUG_MODE=false`
- [ ] HTTPS activé
- [ ] Permissions des dossiers correctes
- [ ] `install.php` supprimé
- [ ] Logs d'erreurs activés mais pas affichés
- [ ] Backups réguliers configurés

## 🆘 Support

Pour toute question de sécurité, consultez la documentation ou créez une issue.

---

**🔒 La sécurité est l'affaire de tous !**
