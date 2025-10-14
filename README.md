# 🚀 STARS DOORS - Plateforme Intergalactique de Réservation

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)
![License](https://img.shields.io/badge/license-MIT-green)

**Stars Doors** est une plateforme révolutionnaire de réservation de logements à travers la galaxie. Voyagez entre planètes, découvrez de nouvelles races aliens, réservez des vaisseaux spatiaux et profitez d'avantages exclusifs avec notre programme de fidélité !

## 🆕 Dernières Mises à Jour (Octobre 2025)

✅ **Gestion complète des annonces**

- Upload multiple d'images (JPG/PNG/WEBP)
- Modification d'annonces existantes
- Suppression sécurisée avec confirmation

✅ **Corrections importantes**

- Problème d'affichage des images résolu (.htaccess corrigé)
- Carousel d'images sur les pages de détail
- Dashboard propriétaire fonctionnel

---

## ✨ Fonctionnalités Principales

### 🌍 Système Spatial Complet

- **15 planètes** réparties sur 5 galaxies (Voie Lactée, Andromède, Triangulum, etc.)
- **15 races aliens** avec caractéristiques uniques
- Système de **compatibilité atmosphérique** automatique
- Alertes de sécurité selon niveau de compatibilité

### 🚀 Transport Spatial

- **8 vaisseaux** de classe économique à luxe
- Vitesses de 1.2c à 6.5c (multiples vitesse lumière)
- Calcul automatique : distance, durée, coût
- Prix de 15₢ à 500₢ par année-lumière
- **Co-voiturage spatial** pour économiser

### 💰 Système Monétaire Galactique

- **14 monnaies** avec conversion en temps réel
- Crédit Galactique Universel (CRG) comme référence
- Widget de conversion interactif
- Bonus de bienvenue : 1000 CRG

### 🏆 Programme de Fidélité

- **5 niveaux** : Bronze → Silver → Gold → Platinum → Diamond
- Accumulation de **Miles Galactiques**
- Réductions jusqu'à 25%
- Accès Lounge VIP, surclassements gratuits
- **Système de parrainage** : 500 points/filleul

### 🛡️ Assurance Voyage

- **4 formules** : Basique, Standard, Premium, Platinium
- Couvertures : annulation, médical, accident spatial, radiation, piraterie
- Remboursements jusqu'à 500,000 CRG

### 📨 Messagerie Intergalactique

- Délai de transmission selon distance
- Instantané jusqu'à 100 AL
- Système de priorité (normale/urgente/critique)
- Traduction automatique entre races

### 🌦️ Météo Spatiale & Événements

- Alertes : tempêtes solaires, pluies de météorites
- Événements galactiques (festivals, célébrations)
- Niveau de sévérité et recommandations

### 🛂 Système de Visas

- **5 types de visas** : Tourisme, Travail, Étudiant, Diplomatique, Affaires
- Gestion automatique des validités
- Contrôles douaniers planétaires

### 👨‍👩‍👧‍👦 Réservations Groupe/Famille

- Réservations groupées avec réductions
- Gestion des membres
- Types : famille, amis, entreprise, scolaire

### 🏠 Gestion des Annonces (Propriétaires)

- **Création d'annonces** avec upload multiple d'images
- **Modification d'annonces** existantes (edit_listing.php)
- **Suppression d'annonces** avec confirmation sécurisée
- Gestion des équipements spatiaux spécifiques
- Preview JavaScript des images avant upload
- Toggle disponibilité
- Dashboard propriétaire complet
- Support formats : JPG, PNG, WEBP (2MB max par image)
- Première image définie automatiquement comme photo principale

---

## 📦 Installation

### Méthode 1 : Installation Automatique (Recommandée) 🎯

1. **Téléchargez** le projet
2. **Placez-le** dans votre dossier web (htdocs, www, etc.)
3. **Accédez** à : `http://localhost/stars-doors/install.php`
4. **Remplissez** le formulaire avec vos informations MySQL
5. **Cliquez** sur "Lancer l'Installation"
6. **Attendez** (environ 30 secondes)
7. **C'est prêt !** Supprimez `install.php`

### Méthode 2 : Installation Manuelle

#### Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extension PDO activée
- Extension JSON activée

#### Étapes

1. **Créer la base de données**

```sql
CREATE DATABASE stars_doors CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stars_doors;
```

2. **Exécuter les migrations dans l'ordre**

```bash
# 1. Structure de base
mysql -u root -p stars_doors < database/schema.sql

# 2. Système spatial
mysql -u root -p stars_doors < database/migration_phase1_spatial.sql

# 3. Transport et monnaies
mysql -u root -p stars_doors < database/migration_phase3_transport.sql

# 4. Fonctionnalités avancées
mysql -u root -p stars_doors < database/migration_phase4_advanced.sql
```

3. **Configurer la connexion**

Créez `config/.env` (optionnel, sinon utilise les valeurs par défaut) :

```env
DB_HOST=localhost
DB_NAME=stars_doors
DB_USER=root
DB_PASS=
DB_PORT=3306

SITE_URL=http://localhost/projets/stars-doors
ENVIRONMENT=development
```

4. **Tester l'installation**

- Accédez à `http://localhost/projets/stars-doors`
- Créez votre premier compte
- Explorez les planètes !

---

## 🗂️ Structure du Projet

```
stars-doors/
├── assets/
│   ├── css/
│   ├── js/
│   └── scss/
├── config/
│   ├── constants.php
│   └── .env (à créer)
├── database/
│   ├── schema.sql
│   ├── migration_phase1_spatial.sql
│   ├── migration_phase3_transport.sql
│   ├── migration_phase4_advanced.sql
│   └── INSTALL_COMPLETE.sql
├── includes/
│   ├── config.php
│   ├── database.php
│   ├── auth.php
│   ├── functions.php
│   ├── validation.php
│   ├── currency.php
│   ├── transport.php
│   └── fidelite.php
├── admin/
│   └── (panel admin - à venir)
├── uploads/
│   ├── annonces/          # Images des annonces
│   ├── listings/
│   └── avatars/
├── index.php              # Page d'accueil
├── search.php             # Recherche avancée
├── listing.php            # Détails d'une annonce
├── create_listing.php     # Créer une annonce (+ upload images)
├── edit_listing.php       # Modifier une annonce
├── dashboard.php          # Tableau de bord utilisateur
├── planetes.php           # Explorer les planètes
├── planet_detail.php      # Détails d'une planète
├── races.php              # Découvrir les races
├── transport.php          # Sélection de transport
├── covoiturage.php        # Co-voiturage spatial
├── fidelite.php           # Programme de fidélité
├── voyages.php            # Historique des voyages
├── messages.php           # Messagerie intergalactique
├── booking.php            # Mes réservations
├── profile.php            # Mon profil
├── register.php           # Inscription
├── login.php              # Connexion
├── logout.php             # Déconnexion
├── install.php            # Installation automatique
├── README.md              # Ce fichier
└── docs/                  # 📚 Documentation
    ├── GUIDE_COMPILATION_SASS.md
    ├── GUIDE_SCSS_SIMPLE.md
    ├── README_SASS.md
    ├── PHASE1_README.md
    ├── PHASE2_README.md
    └── PHASE3_README.md
```

---

## 📊 Base de Données

### Tables Principales (40+)

**Utilisateurs & Authentification**

- `users` - Comptes utilisateurs
- `points_fidelite` - Miles galactiques
- `programmes_fidelite` - Niveaux de fidélité

**Spatial**

- `planetes` (15 entrées) - Planètes galactiques
- `races` (15 entrées) - Races aliens
- `compatibilite_atmospherique` (225 entrées) - Matrice 15×15

**Logements**

- `annonces` - Annonces de logement
- `photo` - Photos des annonces (multi-upload)
- `reservations` - Réservations
- `reservations_groupe` - Groupes/familles
- `reservations_assurances` - Assurances

**Transport**

- `vaisseaux` (8 entrées) - Vaisseaux spatiaux
- `voyage_transport` - Transports réservés
- `covoiturage_spatial` - Co-voiturage
- `covoiturage_participants` - Participants
- `voyages_multi_escales` - Voyages multi-étapes
- `escales` - Escales

**Monnaies**

- `monnaies` (14 entrées) - Devises galactiques

**Administratif**

- `visas` - Visas inter-planétaires
- `types_visa` (5 entrées)
- `douanes_planetaires` - Contrôles douaniers
- `declarations_douanieres` - Déclarations

**Événements**

- `meteo_spatiale` - Météo et alertes
- `evenements_galactiques` - Festivals, célébrations
- `calendriers_planetaires` - Calendriers locaux

**Assurances**

- `assurances_voyage` (4 entrées) - Formules d'assurance

**Communication**

- `messages` - Messagerie avec délais transmission

### Fonctions SQL

- `calcul_delai_transmission(distance)` - Délai messages
- `calcul_cout_voyage(...)` - Coût total voyage
- `convertir_monnaie(montant, source, cible)` - Conversion
- `calculer_points_fidelite(montant, niveau)` - Points fidélité

### Triggers Automatiques

- `before_insert_message_delai` - Calcul délai auto
- `before_insert_voyage_transport` - Prix total auto
- `after_insert_points_fidelite` - Mise à jour niveau
- `before_insert_user_parrainage` - Code parrainage
- `after_insert_user_bonus_parrain` - Bonus parrain/filleul

---

## 🎮 Utilisation

### Créer un Compte

1. Cliquez sur "Inscription"
2. Remplissez le formulaire
3. **Sélectionnez votre race** (Humain, Martien, Vénusien, etc.)
4. **Choisissez votre planète de résidence**
5. Validez
6. Recevez automatiquement :
   - 1000 CRG de bonus
   - Niveau Bronze de fidélité
   - Code de parrainage unique

### Rechercher un Logement

1. Utilisez la barre de recherche
2. Filtres disponibles :
   - Planète/Galaxie
   - Type de logement
   - Prix
   - Dates
   - Capacité
   - Atmosphère
   - Gravité
   - Équipements spatiaux
3. Visualisez les résultats
4. Cliquez sur une annonce

### Réserver avec Transport

1. Sur une annonce, cliquez "Réserver avec transport"
2. Le système vérifie automatiquement :
   - Compatibilité atmosphérique
   - Distance et durée
   - Coût du transport
   - Équipements requis
3. Choisissez votre vaisseau :
   - Économique (lent mais pas cher)
   - Business (confortable)
   - Première Classe (rapide et luxe)
4. Ajoutez une assurance (optionnel)
5. Confirmez et payez

### Gagner des Miles Galactiques

**Méthodes :**

- 1 point par CRG dépensé (× multiplicateur niveau)
- Bonus voyage complété
- Parrainage : 500 points/filleul
- Événements spéciaux

**Avantages :**

- Bronze : 0% réduction
- Silver : -5% + x1.25 points
- Gold : -10% + x1.50 points + Lounge VIP
- Platinum : -15% + x2.00 points + Upgrades
- Diamond : -25% + x3.00 points + Tous avantages

### Co-voiturage Spatial

**Proposer un trajet :**

1. "Co-voiturage" → "Proposer un trajet"
2. Sélectionnez départ/arrivée
3. Choisissez vaisseau et date
4. Définissez places disponibles et prix
5. Ajoutez règles (optionnel)
6. Publiez

**Rejoindre un trajet :**

1. Recherchez un co-voiturage
2. Consultez les détails
3. Envoyez une demande
4. Attendez validation
5. Voyagez et économisez !

### Gérer vos Annonces (Propriétaires)

**Créer une annonce :**

1. Dashboard → "Ajouter une annonce"
2. Remplissez les informations de base (titre, description, planète)
3. Définissez le type de logement et les caractéristiques
4. Sélectionnez les équipements classiques et spatiaux
5. **Uploadez plusieurs photos** (JPG/PNG/WEBP, 2MB max)
   - Prévisualisez les images avant validation
   - La première image devient automatiquement la photo principale
6. Publiez votre annonce

**Modifier une annonce :**

1. Dashboard → "Mes annonces" → Cliquer sur l'icône ✏️ Modifier
2. Modifiez les informations souhaitées
3. Ajoutez de nouvelles photos si besoin
4. Changez la disponibilité (disponible/indisponible)
5. Enregistrez les modifications

**Supprimer une annonce :**

1. Dashboard → "Mes annonces" → Cliquer sur l'icône 🗑️ Supprimer
2. Confirmez la suppression dans la popup
3. ⚠️ **Attention :** Cette action est irréversible
   - Toutes les photos seront supprimées (base + fichiers)
   - Les réservations passées sont conservées pour l'historique

---

## 🔐 Sécurité

### Fonctionnalités Implémentées

- ✅ Tokens CSRF sur tous les formulaires
- ✅ Mots de passe hashés (bcrypt)
- ✅ Sessions sécurisées (httponly, samesite)
- ✅ Validation des entrées (filter_input)
- ✅ Protection XSS (htmlspecialchars)
- ✅ Protection injection SQL (PDO prepared statements)
- ✅ Headers de sécurité (X-Frame-Options, etc.)
- ✅ Vérification des clés étrangères
- ✅ Logs d'erreurs
- ✅ Rate limiting (optionnel)
- ✅ **Upload sécurisé** : validation MIME type, taille, extension
- ✅ **Dossier uploads/** protégé (.htaccess bloque PHP)
- ✅ **Vérification de propriété** avant modification/suppression
- ✅ **Suppression sécurisée** avec confirmation obligatoire

### Bonnes Pratiques

- Changez les mots de passe par défaut
- Désactivez `install.php` après installation
- Configurez `.env` pour production
- Activez HTTPS en production
- Sauvegardez régulièrement la base

---

## 🌟 Roadmap

### ✅ Phase 4 - Complétée (Octobre 2025)

- [x] **Gestion complète des annonces pour propriétaires**
  - [x] Création d'annonces avec upload multiple d'images
  - [x] Modification d'annonces (edit_listing.php)
  - [x] Suppression d'annonces avec confirmation
  - [x] Preview JavaScript des images
  - [x] Gestion sécurisée des fichiers (MIME type, taille, extension)
- [x] **Affichage des images**
  - [x] Correction .htaccess pour autoriser les images
  - [x] Carousel d'images sur les pages de détail
  - [x] Photos principales sur les listings
- [x] **Dashboard propriétaire**
  - [x] Vue d'ensemble des annonces
  - [x] Statistiques des réservations
  - [x] Actions rapides (voir/modifier/supprimer)

### 🚧 Phase 5 - En cours / Propositions

- [ ] Panel administration complet
- [ ] Système de paiement réel (Stripe, PayPal)
- [ ] Notifications push et emails
- [ ] Suppression individuelle de photos dans edit_listing.php
- [ ] Changement de photo principale
- [ ] Application mobile
- [ ] API RESTful
- [ ] Système de reviews avec photos
- [ ] Chat en temps réel
- [ ] Système de recommandations IA
- [ ] Marketplace galactique
- [ ] NFT de propriétés spatiales
- [ ] Métaverse intégration
- [ ] Tests unitaires et intégration

---

## 🐛 Résolution de Problèmes

### Erreur : "Table doesn't exist"

```bash
# Réexécutez les migrations dans l'ordre
mysql -u root -p stars_doors < database/schema.sql
mysql -u root -p stars_doors < database/migration_phase1_spatial.sql
# etc.
```

### Erreur : "Function convertir_monnaie does not exist"

```sql
-- Vérifiez les fonctions
SHOW FUNCTION STATUS WHERE Db = 'stars_doors';

-- Si vide, réexécutez Phase 3
SOURCE database/migration_phase3_transport.sql;
```

### Erreur : "Cannot connect to database"

- Vérifiez que MySQL est démarré
- Vérifiez les identifiants dans `config.php` ou `.env`
- Testez la connexion manuellement

### Page blanche

- Activez display_errors dans `php.ini`
- Consultez les logs d'erreur PHP
- Vérifiez que toutes les `includes/` sont présentes

### Images ne s'affichent pas

Si les images uploadées ne s'affichent pas :

1. Vérifiez que le dossier `uploads/annonces/` existe et a les bonnes permissions
2. Vérifiez le fichier `uploads/.htaccess` - il doit autoriser les images
3. Testez l'accès direct : `http://localhost/stars-doors/uploads/annonces/nom_image.jpg`
4. Utilisez `test_photos.php` pour diagnostiquer le problème
5. Vérifiez que les chemins en base de données sont corrects (relatifs, pas absolus)

```bash
# Vérifier les permissions (Linux/Mac)
chmod 755 uploads/annonces/

# Tester l'affichage
php test_photos.php
```

### Erreur 500 sur les images

Le `.htaccess` dans `uploads/` peut bloquer l'accès. Vérifiez qu'il utilise la syntaxe Apache 2.4 :

```apache
# Bloquer les fichiers PHP
<FilesMatch "\.(php|phtml|php3|php4|php5)$">
    Require all denied
</FilesMatch>
```

---

## 📖 Documentation Complète

👉 **[`docs/README.md`](docs/README.md)** - Index de toute la documentation

### 🎨 Guides SASS
- [`docs/sass/README_SASS.md`](docs/sass/README_SASS.md) - ⭐ Index documentation SASS
- [`docs/sass/GUIDE_COMPILATION_SASS.md`](docs/sass/GUIDE_COMPILATION_SASS.md) - Comment compiler
- [`docs/sass/GUIDE_SCSS_SIMPLE.md`](docs/sass/GUIDE_SCSS_SIMPLE.md) - Comment modifier les styles

### 📚 Guides du projet
- [`docs/guides/PHASE1_README.md`](docs/guides/PHASE1_README.md) - Système spatial (planètes, races)
- [`docs/guides/PHASE2_README.md`](docs/guides/PHASE2_README.md) - Recherche et exploration
- [`docs/guides/PHASE3_README.md`](docs/guides/PHASE3_README.md) - Transport et monnaies

### 📋 Autres
- [`docs/PROJET_COMPLET.md`](docs/PROJET_COMPLET.md) - État complet du projet
- [`docs/SECURITY.md`](docs/SECURITY.md) - Guide de sécurité

---

## 🤝 Contribution

Ce projet est un projet scolaire/d'apprentissage. Les contributions sont les bienvenues !

**Comment contribuer :**

1. Fork le projet
2. Créez une branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

---

## 📜 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

## 👨‍💻 Auteurs

- **Équipe Stars Doors** - Projet 2025
- Technologies utilisées : PHP, MySQL, Bootstrap 5, Font Awesome

---

## 🙏 Remerciements

- Claude (Anthropic) pour l'assistance au développement
- Bootstrap pour le framework CSS
- Font Awesome pour les icônes
- La communauté PHP/MySQL

---

## 📞 Support

Pour toute question ou problème :

- Consultez la documentation dans les fichiers README
- Vérifiez les logs d'erreur
- Créez une issue sur GitHub (si applicable)

---

**🚀 Bon voyage à travers la galaxie ! 🌌**

---

_Stars Doors v1.0.0 - "One small step for code, one giant leap for intergalactic hospitality"_
