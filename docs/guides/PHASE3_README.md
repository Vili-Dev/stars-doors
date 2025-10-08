# 🚀 PHASE 3 : SYSTÈME DE TRANSPORT SPATIAL & FONCTIONNALITÉS AVANCÉES

## 📋 Vue d'ensemble

La Phase 3 transforme Stars Doors en une plateforme complète de voyage intergalactique en ajoutant :
- **Transport spatial** avec vaisseaux de différentes classes
- **Système monétaire galactique** avec 14 monnaies et conversion en temps réel
- **Compatibilité atmosphérique** race→planète avec alertes et équipements requis
- **Messagerie intergalactique** avec délai de transmission selon la distance
- **Calculs automatiques** de coûts, durées et compatibilités

---

## 🗄️ NOUVELLES TABLES

### 1. `monnaies` - Monnaies Galactiques
Système de conversion multi-monnaies avec 14 devises galactiques.

**Colonnes principales :**
- `code` : CRG, TRN, MRS, etc.
- `taux_vers_credit_galactique` : Taux de conversion
- `symbole` : ₢, $, Ɱ, etc.
- `id_planete` : Planète principale utilisant cette monnaie

**Monnaies disponibles :**
- CRG (Crédit Galactique Universel) - monnaie de référence
- TRN (Terran Dollar) - Terre
- MRS (Mars Credit) - Mars
- VNS (Venusian Crown) - Venus
- Et 10 autres monnaies galactiques

### 2. `vaisseaux` - Vaisseaux Spatiaux
8 vaisseaux disponibles dans 5 classes différentes.

**Colonnes principales :**
- `type` : economique, business, premiere_classe, cargo, luxe
- `vitesse_lumiere` : Vitesse en multiple de c (1.5c à 6.5c)
- `prix_base_par_al` : Prix par année-lumière en CRG
- `confort_score` : 1 à 10
- `equipements` : JSON des équipements disponibles
- `capacite_passagers` : 10 à 200 passagers

**Vaisseaux :**
1. **StarHopper Basic** (Économique) - 1.5c, 25₢/AL
2. **Nebula Express** (Économique) - 2.0c, 40₢/AL
3. **Cosmic Comfort** (Business) - 3.5c, 85₢/AL
4. **Galaxy Premier** (Business) - 4.0c, 120₢/AL
5. **Stardust Luxury** (Première Classe) - 5.0c, 280₢/AL
6. **Quantum Velocity** (Première Classe) - 6.5c, 500₢/AL
7. **FreightMaster 3000** (Cargo) - 1.2c, 15₢/AL
8. **Celestial Dream** (Luxe) - 5.5c, 450₢/AL

### 3. `compatibilite_atmospherique` - Compatibilité Race-Planète
Matrice 15 races × 15 planètes = 225 entrées générées automatiquement.

**Niveaux de compatibilité :**
- `natif` : Race sur sa planète d'origine
- `compatible` : Séjour sans équipement
- `adaptable` : Équipements légers requis
- `hostile` : Équipements lourds, risques
- `mortel` : Extrêmement dangereux

**Colonnes :**
- `equipement_requis` : JSON (masques, combinaisons, etc.)
- `cout_adaptation_journalier` : 0 à 200₢ par jour
- `duree_adaptation` : 0 à 72 heures
- `risques` : Description des dangers
- `recommandations` : Conseils de sécurité

### 4. `voyage_transport` - Réservations de Transport
Lié aux réservations d'annonces.

**Colonnes :**
- `id_reservation` : Référence à la réservation
- `id_vaisseau` : Vaisseau sélectionné
- `id_planete_depart` / `id_planete_arrivee`
- `distance_al` : Distance en années-lumière
- `duree_voyage_heures` : Durée calculée
- `prix_transport` : Coût du voyage
- `prix_adaptation` : Coût des équipements atmosphériques
- `prix_total` : Somme automatique
- `date_arrivee_estimee` : Calculé automatiquement

---

## 🔧 MODIFICATIONS DES TABLES EXISTANTES

### Table `users`
**Nouvelles colonnes :**
- `solde_credits_galactiques` : Solde en CRG (1000₢ de bonus initial)
- `monnaie_preferee` : Monnaie d'affichage
- `portefeuille_json` : Soldes dans différentes monnaies

### Table `annonces`
**Nouvelles colonnes :**
- `include_transport` : Transport inclus dans le prix
- `id_vaisseau_recommande` : Vaisseau suggéré

### Table `messages`
**Nouvelles colonnes :**
- `delai_transmission_secondes` : Calculé automatiquement selon distance
- `date_envoi_reel` : Date envoi par expéditeur
- `date_reception_estimee` : Date réception estimée
- `traduit_automatiquement` : Message traduit par IA
- `langue_originale` : Langue d'origine
- `priorite` : normale, urgente, critique

---

## 🛠️ FONCTIONS SQL

### 1. `calcul_delai_transmission(distance_al)`
Calcule le délai de transmission d'un message.
- Transmission instantanée jusqu'à 100 AL
- 1 seconde par AL supplémentaire au-delà

**Exemple :**
```sql
SELECT calcul_delai_transmission(150.5); -- Retourne 50s
```

### 2. `calcul_cout_voyage(distance, prix_par_al, duree_jours, cout_adaptation_jour)`
Calcule le coût total d'un voyage (transport + adaptation).

**Exemple :**
```sql
SELECT calcul_cout_voyage(42.0, 85.00, 7, 20.00); -- Transport + adaptation
```

### 3. `convertir_monnaie(montant, code_source, code_cible)`
Convertit un montant d'une monnaie à une autre via CRG.

**Exemple :**
```sql
SELECT convertir_monnaie(100.00, 'TRN', 'MRS'); -- Convertit 100$ en Mars Credit
```

---

## ⚡ TRIGGERS

### 1. `before_insert_message_delai`
Calcule automatiquement le délai de transmission lors de l'envoi d'un message.
- Récupère les planètes de résidence des utilisateurs
- Calcule la distance
- Définit `delai_transmission_secondes` et `date_reception_estimee`

### 2. `before_insert_voyage_transport` & `before_update_voyage_transport`
Met à jour automatiquement `prix_total` = `prix_transport` + `prix_adaptation`

---

## 📊 VUES

### 1. `vue_trajets_disponibles`
Liste tous les trajets possibles avec prix et durée calculés.

**Colonnes :**
- `planete_depart`, `planete_arrivee`
- `distance_al`
- `vaisseau_nom`, `classe_voyage`
- `duree_heures`, `prix_transport`
- `confort_score`, `equipements`

### 2. `vue_user_planete_compatibilite`
Compatibilité de chaque utilisateur avec toutes les planètes.

**Colonnes :**
- Infos utilisateur et race
- Infos planète
- `niveau_compatibilite`
- `equipement_requis`, `cout_adaptation_journalier`
- `risques`, `recommandations`

---

## 📂 NOUVEAUX FICHIERS PHP

### 1. `includes/currency.php` - Utilitaires Monétaires

**Fonctions principales :**
```php
convertirMonnaie($montant, $code_source, $code_cible)
getMonnaies() // Liste des monnaies actives
formatMontant($montant, $code_monnaie) // Formatage avec symbole
getTauxChange($code_monnaie)
afficherConvertisseur($montant, $monnaie_source) // Widget conversion
crediterCompte($user_id, $montant, $code_monnaie)
debiterCompte($user_id, $montant, $code_monnaie)
getSolde($user_id, $code_monnaie)
```

### 2. `includes/transport.php` - Utilitaires Transport

**Fonctions principales :**
```php
getVaisseaux($type = null) // Liste vaisseaux
calculerVoyage($depart, $arrivee, $vaisseau, $race, $duree_sejour)
// Retourne: distance, durée, coûts, compatibilité, équipements requis

creerVoyageTransport($reservation, $vaisseau, $depart, $arrivee, $date, $details)
verifierCompatibilite($id_race, $id_planete)
afficherBadgeCompatibilite($niveau) // Badge HTML
getTransportsUtilisateur($user_id)
afficherSelecteurVaisseau($vaisseaux, $distance, $duree_sejour)
getTrajetsPopulaires($limit)
```

### 3. `ajax_convert_currency.php` - API Conversion
Endpoint AJAX pour conversion en temps réel.

**Paramètres GET :**
- `montant` : Montant à convertir
- `source` : Code monnaie source
- `cible` : Code monnaie cible

**Réponse JSON :**
```json
{
  "success": true,
  "montant_original": 100.00,
  "montant_converti": 95.00,
  "monnaie_source": "CRG",
  "monnaie_cible": "TRN",
  "taux": 0.95
}
```

### 4. `transport.php` - Page Sélection de Transport

Permet de choisir un vaisseau pour voyager vers une annonce.

**Fonctionnalités :**
- Affichage de tous les vaisseaux disponibles
- Calcul automatique : distance, durée, prix
- Vérification compatibilité atmosphérique
- Alertes de sécurité (hostile/mortel)
- Affichage du coût d'adaptation
- Filtrage par classe de voyage
- Trajets populaires

**URL :**
```
transport.php?annonce=123&depart=1&duree=7
```

### 5. `messages.php` (MISE À JOUR COMPLÈTE) - Messagerie Intergalactique

**Nouvelles fonctionnalités :**
- Liste des conversations avec dernier message
- Affichage du délai de transmission selon distance
- Badge "Instantané" ou "Délai: Xs"
- Système de priorité (normale, urgente, critique)
- Indication des messages non lus
- Interface style messagerie moderne
- Auto-scroll et rechargement automatique
- Affichage de la race et planète de l'interlocuteur

---

## 🎨 FONCTIONNALITÉS UTILISATEUR

### 1. Système de Transport

**Workflow :**
1. Utilisateur sélectionne une annonce
2. Clique sur "Réserver avec transport"
3. Redirigé vers `transport.php`
4. Voit tous les vaisseaux disponibles avec :
   - Prix calculé selon distance
   - Durée du voyage
   - Niveau de confort
   - Équipements inclus
5. Système vérifie compatibilité atmosphérique
6. Affiche alertes si nécessaire
7. Calcule coût d'adaptation
8. Utilisateur sélectionne vaisseau
9. Réservation créée avec transport inclus

**Alertes de compatibilité :**
- 🟢 **Natif/Compatible** : Aucun équipement
- 🟡 **Adaptable** : Équipements légers (20-50₢/jour)
- 🟠 **Hostile** : Équipements lourds (80₢/jour)
- 🔴 **Mortel** : Déconseillé (150-200₢/jour)

### 2. Système Monétaire

**Fonctionnalités :**
- Solde en CRG affiché dans le header
- Conversion automatique dans monnaie préférée
- Widget de conversion en temps réel
- 14 monnaies galactiques disponibles
- Bonus de bienvenue : 1000 CRG
- Transactions automatiques lors des réservations

**Widget de conversion :**
```html
<div class="currency-converter">
  <input type="number" value="100"> CRG
  →
  <output>95.00</output> TRN
</div>
```

### 3. Messagerie Intergalactique

**Fonctionnalités :**
- Délai de transmission calculé automatiquement
  - Instantané jusqu'à 100 AL
  - 1s par AL supplémentaire au-delà
- Affichage du délai dans chaque message
- Priorités : normale, urgente, critique
- Badge de traduction automatique
- Interface conversationnelle moderne
- Notifications de messages non lus

**Exemple de délai :**
- Terre → Mars (0.000158 AL) : Instantané
- Terre → Proxima b (4.24 AL) : Instantané
- Terre → Kepler-452b (1400 AL) : 1300 secondes ≈ 21 minutes

---

## 🔐 SÉCURITÉ

### Validations ajoutées :
- Vérification du solde avant débit
- Contrôle des montants de conversion
- Protection CSRF sur formulaires
- Validation des compatibilités atmosphériques
- Vérification de disponibilité des vaisseaux

### Logs :
- Toutes les transactions monétaires
- Erreurs de conversion
- Échecs de compatibilité

---

## 📱 INTÉGRATIONS

### Pages modifiées :
- `listing.php` : Ajout bouton "Réserver avec transport"
- `dashboard.php` : Affichage solde CRG
- `profile.php` : Gestion monnaie préférée
- `header.php` : Widget solde dans navbar

### Nouvelles pages :
- `transport.php` : Sélection de vaisseau
- Page conversions : `ajax_convert_currency.php`

---

## 📈 STATISTIQUES & ANALYSES

### Nouvelles métriques :
- Trajets les plus populaires
- Vaisseaux les plus réservés
- Monnaies les plus utilisées
- Taux de compatibilité par race
- Coût moyen par année-lumière
- Durée moyenne des voyages

### Vues pour analytics :
```sql
SELECT * FROM vue_trajets_disponibles
WHERE distance_al BETWEEN 10 AND 100
ORDER BY prix_transport ASC;

SELECT * FROM vue_user_planete_compatibilite
WHERE niveau_compatibilite = 'mortel';
```

---

## 🚀 INSTALLATION

### 1. Exécuter la migration
```bash
# Dans phpMyAdmin ou MySQL Workbench
# Exécuter : database/migration_phase3_transport.sql
```

### 2. Vérifications
```sql
-- Vérifier les tables
SHOW TABLES LIKE '%monnaies%';
SHOW TABLES LIKE '%vaisseaux%';
SHOW TABLES LIKE '%compatibilite%';
SHOW TABLES LIKE '%voyage_transport%';

-- Vérifier les données
SELECT COUNT(*) FROM monnaies; -- 14
SELECT COUNT(*) FROM vaisseaux; -- 8
SELECT COUNT(*) FROM compatibilite_atmospherique; -- 225

-- Vérifier les fonctions
SELECT calcul_delai_transmission(150); -- 50
SELECT convertir_monnaie(100, 'CRG', 'TRN'); -- 105.26
```

### 3. Test des fonctionnalités
1. ✅ Aller sur `transport.php?annonce=1&duree=7`
2. ✅ Vérifier affichage des vaisseaux
3. ✅ Tester le convertisseur de monnaie
4. ✅ Envoyer un message et vérifier le délai
5. ✅ Vérifier le solde CRG dans le profil

---

## 🐛 RÉSOLUTION DE PROBLÈMES

### Erreur : "Function convertir_monnaie does not exist"
```sql
-- Vérifier que les fonctions sont créées
SHOW FUNCTION STATUS WHERE Db = 'stars_doors';

-- Recréer si nécessaire
SOURCE database/migration_phase3_transport.sql;
```

### Erreur : "Column solde_credits_galactiques doesn't exist"
```sql
-- La migration n'a pas été appliquée
-- Exécuter migration_phase3_transport.sql
```

### Prix de transport = 0
```sql
-- Vérifier les données planètes
SELECT id_planete, nom, distance_terre FROM planetes WHERE distance_terre IS NULL;

-- Mettre à jour si nécessaire
UPDATE planetes SET distance_terre = 0 WHERE distance_terre IS NULL;
```

---

## 📚 EXEMPLES D'UTILISATION

### Calculer un voyage
```php
require_once 'includes/transport.php';

$details = calculerVoyage(
    $id_planete_depart = 1,    // Terre
    $id_planete_arrivee = 8,   // Proxima b
    $id_vaisseau = 3,          // Cosmic Comfort
    $id_race = 1,              // Humain
    $duree_sejour = 7          // 7 jours
);

echo "Distance: {$details['distance_al']} AL\n";
echo "Durée: {$details['duree_jours']} jours\n";
echo "Coût transport: {$details['cout_transport']} CRG\n";
echo "Coût adaptation: {$details['cout_adaptation']} CRG\n";
echo "TOTAL: {$details['cout_total']} CRG\n";
```

### Convertir des monnaies
```php
require_once 'includes/currency.php';

$montant_crg = 1000;
$montant_trn = convertirMonnaie($montant_crg, 'CRG', 'TRN');

echo formatMontant($montant_trn, 'TRN'); // 1,052.63 $
```

### Vérifier compatibilité
```php
require_once 'includes/transport.php';

$compat = verifierCompatibilite($id_race = 1, $id_planete = 12);

if ($compat['niveau_compatibilite'] === 'mortel') {
    echo "⚠️ DANGER : {$compat['risques']}\n";
    echo "Équipements: " . json_encode($compat['equipement_requis']) . "\n";
    echo "Coût: {$compat['cout_adaptation_journalier']} CRG/jour\n";
}
```

---

## 🎯 ROADMAP FUTURE (Phase 4 ?)

Idées pour phases futures :
- [ ] Assurance voyage spatiale
- [ ] Système de fidélité (miles galactiques)
- [ ] Alerte météorites/tempêtes spatiales
- [ ] Co-voiturage spatial
- [ ] Escales multiples
- [ ] Réservation groupe/famille
- [ ] Calendrier galactique universel
- [ ] Conversion fuseaux horaires planétaires
- [ ] Système de douanes galactiques
- [ ] Visa inter-planétaire

---

## ✅ CHECKLIST POST-MIGRATION

- [ ] Migration SQL exécutée sans erreur
- [ ] 14 monnaies dans la table `monnaies`
- [ ] 8 vaisseaux dans la table `vaisseaux`
- [ ] 225 entrées dans `compatibilite_atmospherique`
- [ ] Tous les users ont 1000 CRG de solde initial
- [ ] Fonctions SQL créées (calcul_delai_transmission, convertir_monnaie, calcul_cout_voyage)
- [ ] Triggers créés (before_insert_message_delai, before_insert_voyage_transport)
- [ ] Vues créées (vue_trajets_disponibles, vue_user_planete_compatibilite)
- [ ] Page transport.php accessible
- [ ] Convertisseur AJAX fonctionne
- [ ] Messages affichent le délai de transmission
- [ ] Solde CRG affiché dans le profil

---

## 📝 NOTES IMPORTANTES

1. **Performance** : Les 225 entrées de compatibilité sont générées automatiquement. Index créés pour optimisation.

2. **Calculs** : Tous les calculs (distance, durée, prix) sont faits en PHP ET SQL pour cohérence.

3. **Triggers** : Le délai de transmission est calculé automatiquement à l'insertion d'un message.

4. **Bonus** : Chaque nouvel utilisateur reçoit 1000 CRG de bonus de bienvenue.

5. **Réalisme** : Les vitesses des vaisseaux (1.5c à 6.5c) et délais de transmission sont basés sur des hypothèses sci-fi cohérentes.

---

**Phase 3 terminée ! 🎉**

Le système de transport spatial est maintenant pleinement fonctionnel avec gestion des monnaies, compatibilité atmosphérique et messagerie intergalactique.
