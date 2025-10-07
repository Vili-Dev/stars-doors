-- =====================================================
-- AirCMS Sample Data - 20 records per table
-- Respecting Foreign Key Dependencies
-- =====================================================

-- 1. USERS TABLE (No dependencies)
INSERT INTO users (nom, prenom, email, mot_de_passe, telephone, date_de_naissance, race, role, date_inscription, actif) VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456789', '1985-03-15', 'Caucasien', 'proprietaire', '2023-01-15 10:30:00', TRUE),
('Martin', 'Marie', 'mari.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456790', '1990-07-22', 'Caucasien', 'locataire', '2023-01-16 11:45:00', TRUE),
('Bernard', 'Pierre', 'pierre.bernard@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456791', '1988-12-03', 'Caucasien', 'proprietaire', '2023-01-17 09:15:00', TRUE),
('Dubois', 'Sophie', 'sophie.dubois@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456792', '1992-05-18', 'Caucasien', 'locataire', '2023-01-18 14:20:00', TRUE),
('Moreau', 'Luc', 'luc.moreau@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456793', '1987-09-10', 'Caucasien', 'proprietaire', '2023-01-19 16:30:00', TRUE),
('Laurent', 'Emma', 'emma.laurent@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456794', '1995-02-28', 'Caucasien', 'locataire', '2023-01-20 08:45:00', TRUE),
('Simon', 'Thomas', 'thomas.simon@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456795', '1983-11-14', 'Caucasien', 'proprietaire', '2023-01-21 12:00:00', TRUE),
('Michel', 'Julie', 'julie.michel@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456796', '1991-08-07', 'Caucasien', 'locataire', '2023-01-22 15:15:00', TRUE),
('Leroy', 'Antoine', 'antoine.leroy@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456797', '1986-04-25', 'Caucasien', 'proprietaire', '2023-01-23 10:45:00', TRUE),
('Roux', 'Camille', 'camille.roux@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456798', '1993-01-12', 'Caucasien', 'locataire', '2023-01-24 13:30:00', TRUE),
('David', 'Nicolas', 'nicolas.david@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456799', '1989-06-30', 'Caucasien', 'proprietaire', '2023-01-25 09:00:00', TRUE),
('Bertrand', 'Laura', 'laura.bertrand@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456800', '1994-10-05', 'Caucasien', 'locataire', '2023-01-26 11:15:00', TRUE),
('Petit', 'Maxime', 'maxime.petit@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456801', '1984-03-20', 'Caucasien', 'proprietaire', '2023-01-27 14:45:00', TRUE),
('Robert', 'Chloe', 'chloe.robert@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456802', '1996-12-08', 'Caucasien', 'locataire', '2023-01-28 16:00:00', TRUE),
('Richard', 'Hugo', 'hugo.richard@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456803', '1982-07-17', 'Caucasien', 'proprietaire', '2023-01-29 08:30:00', TRUE),
('Durand', 'Lea', 'lea.durand@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456804', '1997-04-02', 'Caucasien', 'locataire', '2023-01-30 12:45:00', TRUE),
('Moreau', 'Alexandre', 'alexandre.moreau@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456805', '1981-11-26', 'Caucasien', 'proprietaire', '2023-01-31 15:20:00', TRUE),
('Girard', 'Manon', 'manon.girard@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456806', '1998-08-13', 'Caucasien', 'locataire', '2023-02-01 10:10:00', TRUE),
('Bonnet', 'Julien', 'julien.bonnet@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456807', '1980-05-09', 'Caucasien', 'proprietaire', '2023-02-02 13:55:00', TRUE),
('Admin', 'System', 'admin@aircms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0123456808', '1975-01-01', 'Caucasien', 'admin', '2023-01-01 00:00:00', TRUE);

-- 2. PHOTO TABLE (Insert without id_annonce first to avoid circular dependency)
INSERT INTO photo (nom_fichier, type_logement, description, photo_principale, ordre_affichage, date_upload) VALUES
('appartement_paris_01.jpg', 'appartement', 'Vue principale de lappartement parisien', TRUE, 1, '2023-02-01 10:00:00'),
('maison_lyon_01.jpg', 'maison', 'Facade de la maison lyonnaise', TRUE, 1, '2023-02-02 11:00:00'),
('studio_marseille_01.jpg', 'studio', 'Studio moderne à Marseille', TRUE, 1, '2023-02-03 12:00:00'),
('villa_nice_01.jpg', 'villa', 'Villa avec piscine à Nice', TRUE, 1, '2023-02-04 13:00:00'),
('chalet_chamonix_01.jpg', 'chalet', 'Chalet de montagne à Chamonix', TRUE, 1, '2023-02-05 14:00:00'),
('appartement_bordeaux_01.jpg', 'appartement', 'Appartement centre-ville Bordeaux', TRUE, 1, '2023-02-06 15:00:00'),
('maison_toulouse_01.jpg', 'maison', 'Maison familiale Toulouse', TRUE, 1, '2023-02-07 16:00:00'),
('studio_nantes_01.jpg', 'studio', 'Studio étudiant Nantes', TRUE, 1, '2023-02-08 17:00:00'),
('villa_cannes_01.jpg', 'villa', 'Villa de luxe Cannes', TRUE, 1, '2023-02-09 18:00:00'),
('chalet_megeve_01.jpg', 'chalet', 'Chalet ski Megève', TRUE, 1, '2023-02-10 19:00:00'),
('appartement_lille_01.jpg', 'appartement', 'Appartement moderne Lille', TRUE, 1, '2023-02-11 10:30:00'),
('maison_strasbourg_01.jpg', 'maison', 'Maison alsacienne Strasbourg', TRUE, 1, '2023-02-12 11:30:00'),
('studio_montpellier_01.jpg', 'studio', 'Studio proche université', TRUE, 1, '2023-02-13 12:30:00'),
('villa_biarritz_01.jpg', 'villa', 'Villa vue océan Biarritz', TRUE, 1, '2023-02-14 13:30:00'),
('chalet_annecy_01.jpg', 'chalet', 'Chalet lac Annecy', TRUE, 1, '2023-02-15 14:30:00'),
('appartement_rennes_01.jpg', 'appartement', 'Appartement Rennes centre', TRUE, 1, '2023-02-16 15:30:00'),
('maison_dijon_01.jpg', 'maison', 'Maison bourguignonne Dijon', TRUE, 1, '2023-02-17 16:30:00'),
('studio_grenoble_01.jpg', 'studio', 'Studio Grenoble', TRUE, 1, '2023-02-18 17:30:00'),
('villa_saint_tropez_01.jpg', 'villa', 'Villa Saint-Tropez', TRUE, 1, '2023-02-19 18:30:00'),
('chalet_val_thorens_01.jpg', 'chalet', 'Chalet Val Thorens', TRUE, 1, '2023-02-20 19:30:00');



INSERT INTO annonces (id_user, titre, description, adresse, ville, pays, code_postal, prix_nuit, nb_chambres, nb_lits, nb_salles_bain, capacite_max, type_logement, wifi, parking, climatisation, lave_linge, television, animaux_acceptes, type_dair, bouteille_air, date_creation, disponible) VALUES
(1, 'Appartement Paris Centre', 'Magnifique appartement au coeur de Paris', '15 Rue de Rivoli', 'Paris', 'France', '75001', 120, 2, 2, 1, 4, 'appartement', TRUE, FALSE, TRUE, TRUE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-01 10:00:00', TRUE),
(3, 'Maison Lyon Presquîle', 'Belle maison dans le vieux Lyon', '8 Rue du Boeuf', 'Lyon', 'France', '69005', 95, 3, 4, 2, 6, 'maison', TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, 'oxygene', 'azote', '2023-02-02 11:00:00', TRUE),
(5, 'Studio Marseille Vieux-Port', 'Studio moderne proche du port', '22 Quai du Port', 'Marseille', 'France', '13002', 65, 1, 1, 1, 2, 'studio', TRUE, FALSE, TRUE, FALSE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-03 12:00:00', TRUE),
(7, 'Villa Nice Côte dAzur', 'Villa avec piscine et vue mer', '45 Promenade des Anglais', 'Nice', 'France', '06000', 250, 4, 6, 3, 8, 'villa', TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, 'oxygene', 'helium', '2023-02-04 13:00:00', TRUE),
(9, 'Chalet Chamonix Mont-Blanc', 'Chalet authentique face au Mont-Blanc', '12 Route des Praz', 'Chamonix', 'France', '74400', 180, 3, 5, 2, 6, 'chalet', TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, 'oxygene', 'azote', '2023-02-05 14:00:00', TRUE),
(11, 'Appartement Bordeaux Chartrons', 'Appartement rénové quartier branché', '33 Cours Portal', 'Bordeaux', 'France', '33000', 85, 2, 2, 1, 4, 'appartement', TRUE, FALSE, TRUE, TRUE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-06 15:00:00', TRUE),
(13, 'Maison Toulouse Capitole', 'Maison de ville proche du Capitole', '18 Rue des Changes', 'Toulouse', 'France', '31000', 110, 3, 4, 2, 6, 'maison', TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, 'oxygene', 'azote', '2023-02-07 16:00:00', TRUE),
(15, 'Studio Nantes Île de Nantes', 'Studio design quartier créatif', '7 Rue de la Biscuiterie', 'Nantes', 'France', '44000', 55, 1, 1, 1, 2, 'studio', TRUE, FALSE, FALSE, TRUE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-08 17:00:00', TRUE),
(17, 'Villa Cannes Croisette', 'Villa de prestige sur la Croisette', '28 Boulevard de la Croisette', 'Cannes', 'France', '06400', 350, 5, 8, 4, 10, 'villa', TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, 'oxygene', 'helium', '2023-02-09 18:00:00', TRUE),
(19, 'Chalet Megève Rochebrune', 'Chalet ski-in ski-out', '15 Route de Rochebrune', 'Megève', 'France', '74120', 220, 4, 6, 3, 8, 'chalet', TRUE, TRUE, TRUE, TRUE, TRUE, FALSE, 'oxygene', 'azote', '2023-02-10 19:00:00', TRUE),
(1, 'Appartement Lille Vieux-Lille', 'Appartement dans le Vieux-Lille', '25 Rue de la Monnaie', 'Lille', 'France', '59000', 75, 2, 2, 1, 4, 'appartement', TRUE, FALSE, FALSE, TRUE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-11 10:30:00', TRUE),
(3, 'Maison Strasbourg Petite France', 'Maison alsacienne authentique', '12 Rue du Bain-aux-Plantes', 'Strasbourg', 'France', '67000', 90, 3, 4, 2, 6, 'maison', TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, 'oxygene', 'azote', '2023-02-12 11:30:00', TRUE),
(5, 'Studio Montpellier Antigone', 'Studio moderne quartier Antigone', '8 Place du Nombre dOr', 'Montpellier', 'France', '34000', 60, 1, 1, 1, 2, 'studio', TRUE, FALSE, TRUE, FALSE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-13 12:30:00', TRUE),
(7, 'Villa Biarritz Grande Plage', 'Villa face à locéan', '35 Avenue de lImpératrice', 'Biarritz', 'France', '64200', 280, 4, 6, 3, 8, 'villa', TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, 'oxygene', 'helium', '2023-02-14 13:30:00', TRUE),
(9, 'Chalet Annecy Lac', 'Chalet vue lac dAnnecy', '20 Route du Semnoz', 'Annecy', 'France', '74000', 160, 3, 5, 2, 6, 'chalet', TRUE, TRUE, FALSE, TRUE, TRUE, FALSE, 'oxygene', 'azote', '2023-02-15 14:30:00', TRUE),
(11, 'Appartement Rennes Centre', 'Appartement hypercentre Rennes', '14 Place des Lices', 'Rennes', 'France', '35000', 70, 2, 2, 1, 4, 'appartement', TRUE, FALSE, FALSE, TRUE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-16 15:30:00', TRUE),
(13, 'Maison Dijon Secteur Sauvegardé', 'Maison bourguignonne', '22 Rue Verrerie', 'Dijon', 'France', '21000', 80, 3, 4, 2, 6, 'maison', TRUE, TRUE, FALSE, TRUE, TRUE, TRUE, 'oxygene', 'azote', '2023-02-17 16:30:00', TRUE),
(15, 'Studio Grenoble Hyper-Centre', 'Studio proche tramway', '5 Place Grenette', 'Grenoble', 'France', '38000', 50, 1, 1, 1, 2, 'studio', TRUE, FALSE, FALSE, TRUE, TRUE, FALSE, 'oxygene', 'oxygene', '2023-02-18 17:30:00', TRUE),
(17, 'Villa Saint-Tropez Port', 'Villa de luxe au port', '18 Quai Jean Jaurès', 'Saint-Tropez', 'France', '83990', 400, 5, 8, 4, 10, 'villa', TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, 'oxygene', 'helium', '2023-02-19 18:30:00', TRUE),
(19, 'Chalet Val Thorens Pistes', 'Chalet pied des pistes', '8 Rue du Soleil', 'Val Thorens', 'France', '73440', 300, 4, 6, 3, 8, 'chalet', TRUE, TRUE, TRUE, TRUE, TRUE, FALSE, 'oxygene', 'azote', '2023-02-20 19:30:00', TRUE);

-- Update photo table with correct annonce references
UPDATE photo SET id_annonce = 1 WHERE nom_fichier = 'appartement_paris_01.jpg';
UPDATE photo SET id_annonce = 2 WHERE nom_fichier = 'maison_lyon_01.jpg';
UPDATE photo SET id_annonce = 3 WHERE nom_fichier = 'studio_marseille_01.jpg';
UPDATE photo SET id_annonce = 4 WHERE nom_fichier = 'villa_nice_01.jpg';
UPDATE photo SET id_annonce = 5 WHERE nom_fichier = 'chalet_chamonix_01.jpg';
UPDATE photo SET id_annonce = 6 WHERE nom_fichier = 'appartement_bordeaux_01.jpg';
UPDATE photo SET id_annonce = 7 WHERE nom_fichier = 'maison_toulouse_01.jpg';
UPDATE photo SET id_annonce = 8 WHERE nom_fichier = 'studio_nantes_01.jpg';
UPDATE photo SET id_annonce = 9 WHERE nom_fichier = 'villa_cannes_01.jpg';
UPDATE photo SET id_annonce = 10 WHERE nom_fichier = 'chalet_megeve_01.jpg';
UPDATE photo SET id_annonce = 11 WHERE nom_fichier = 'appartement_lille_01.jpg';
UPDATE photo SET id_annonce = 12 WHERE nom_fichier = 'maison_strasbourg_01.jpg';
UPDATE photo SET id_annonce = 13 WHERE nom_fichier = 'studio_montpellier_01.jpg';
UPDATE photo SET id_annonce = 14 WHERE nom_fichier = 'villa_biarritz_01.jpg';
UPDATE photo SET id_annonce = 15 WHERE nom_fichier = 'chalet_annecy_01.jpg';
UPDATE photo SET id_annonce = 16 WHERE nom_fichier = 'appartement_rennes_01.jpg';
UPDATE photo SET id_annonce = 17 WHERE nom_fichier = 'maison_dijon_01.jpg';
UPDATE photo SET id_annonce = 18 WHERE nom_fichier = 'studio_grenoble_01.jpg';
UPDATE photo SET id_annonce = 19 WHERE nom_fichier = 'villa_saint_tropez_01.jpg';
UPDATE photo SET id_annonce = 20 WHERE nom_fichier = 'chalet_val_thorens_01.jpg';


UPDATE annonces SET id_photo = 1 WHERE id_annonce = 1;
UPDATE annonces SET id_photo = 2 WHERE id_annonce = 2;
UPDATE annonces SET id_photo = 3 WHERE id_annonce = 3;
UPDATE annonces SET id_photo = 4 WHERE id_annonce = 4;
UPDATE annonces SET id_photo = 5 WHERE id_annonce = 5;
UPDATE annonces SET id_photo = 6 WHERE id_annonce = 6;
UPDATE annonces SET id_photo = 7 WHERE id_annonce = 7;
UPDATE annonces SET id_photo = 8 WHERE id_annonce = 8;
UPDATE annonces SET id_photo = 9 WHERE id_annonce = 9;
UPDATE annonces SET id_photo = 10 WHERE id_annonce = 10;
UPDATE annonces SET id_photo = 11 WHERE id_annonce = 11;
UPDATE annonces SET id_photo = 12 WHERE id_annonce = 12;
UPDATE annonces SET id_photo = 13 WHERE id_annonce = 13;
UPDATE annonces SET id_photo = 14 WHERE id_annonce = 14;
UPDATE annonces SET id_photo = 15 WHERE id_annonce = 15;
UPDATE annonces SET id_photo = 16 WHERE id_annonce = 16;
UPDATE annonces SET id_photo = 17 WHERE id_annonce = 17;
UPDATE annonces SET id_photo = 18 WHERE id_annonce = 18;
UPDATE annonces SET id_photo = 19 WHERE id_annonce = 19;
UPDATE annonces SET id_photo = 20 WHERE id_annonce = 20;

-- 4. RESERVATIONS TABLE (Depends on: annonces, users)
INSERT INTO reservations (id_annonce, id_user, date_debut, date_fin, nb_personnes, prix_total, statut, date_reservation, message) VALUES
(1, 2, '2023-03-15', '2023-03-20', 2, 600, 'confirmee', '2023-02-15 14:30:00', 'Première visite à Paris, très excité!'),
(2, 4, '2023-04-10', '2023-04-15', 4, 475, 'confirmee', '2023-03-10 10:15:00', 'Voyage en famille à Lyon'),
(3, 6, '2023-05-05', '2023-05-08', 2, 195, 'terminee', '2023-04-05 16:45:00', 'Weekend romantique'),
(4, 8, '2023-06-20', '2023-06-27', 6, 1750, 'confirmee', '2023-05-20 09:30:00', 'Vacances dété en famille'),
(5, 10, '2023-07-15', '2023-07-22', 4, 1260, 'confirmee', '2023-06-15 11:20:00', 'Randonnées en montagne'),
(6, 12, '2023-08-10', '2023-08-13', 3, 255, 'terminee', '2023-07-10 13:45:00', 'Découverte de Bordeaux'),
(7, 14, '2023-09-05', '2023-09-10', 5, 550, 'confirmee', '2023-08-05 15:10:00', 'Réunion de famille'),
(8, 16, '2023-10-12', '2023-10-15', 2, 165, 'en_attente', '2023-09-12 08:25:00', 'Weekend à Nantes'),
(9, 18, '2023-11-20', '2023-11-25', 8, 1750, 'confirmee', '2023-10-20 12:40:00', 'Événement professionnel'),
(10, 2, '2023-12-15', '2023-12-22', 6, 1540, 'confirmee', '2023-11-15 14:55:00', 'Vacances de Noël au ski'),
(11, 4, '2024-01-10', '2024-01-13', 3, 225, 'en_attente', '2023-12-10 10:30:00', 'Visite de Lille'),
(12, 6, '2024-02-14', '2024-02-17', 4, 270, 'confirmee', '2024-01-14 16:20:00', 'Saint-Valentin à Strasbourg'),
(13, 8, '2024-03-20', '2024-03-23', 2, 180, 'confirmee', '2024-02-20 11:45:00', 'Weekend à Montpellier'),
(14, 10, '2024-04-25', '2024-04-30', 6, 1400, 'en_attente', '2024-03-25 09:15:00', 'Vacances de Pâques'),
(15, 12, '2024-05-15', '2024-05-20', 4, 800, 'confirmee', '2024-04-15 13:30:00', 'Découverte dAnnecy'),
(16, 14, '2024-06-10', '2024-06-13', 3, 210, 'confirmee', '2024-05-10 15:45:00', 'Weekend à Rennes'),
(17, 16, '2024-07-05', '2024-07-10', 5, 400, 'en_attente', '2024-06-05 08:50:00', 'Visite de Dijon'),
(18, 18, '2024-08-12', '2024-08-15', 2, 150, 'confirmee', '2024-07-12 12:25:00', 'Weekend à Grenoble'),
(19, 2, '2024-09-20', '2024-09-25', 8, 2000, 'en_attente', '2024-08-20 14:10:00', 'Événement spécial Saint-Tropez'),
(20, 4, '2024-10-15', '2024-10-22', 6, 2100, 'confirmee', '2024-09-15 16:35:00', 'Vacances de ski à Val Thorens');

-- 5. AVIS TABLE (Depends on: reservations, annonces, users)
INSERT INTO Avis (id_reservation, id_annonce, id_user, note, commentaire, date_avis, visible) VALUES
(1, 1, 2, 5, 'Appartement magnifique, très bien situé au coeur de Paris. Propriétaire très accueillant!', '2023-03-21 10:30:00', TRUE),
(2, 2, 4, 4, 'Belle maison à Lyon, quartier charmant. Quelques petits détails à améliorer mais globalement très satisfait.', '2023-04-16 14:15:00', TRUE),
(3, 3, 6, 5, 'Studio parfait pour un weekend romantique à Marseille. Vue sur le port exceptionnelle!', '2023-05-09 09:45:00', TRUE),
(4, 4, 8, 5, 'Villa de rêve à Nice! Piscine, vue mer, tout était parfait pour nos vacances en famille.', '2023-06-28 16:20:00', TRUE),
(5, 5, 10, 4, 'Chalet très bien situé à Chamonix. Vue sur le Mont-Blanc à couper le souffle. Petit bémol sur léquipement cuisine.', '2023-07-23 11:10:00', TRUE),
(6, 6, 12, 5, 'Appartement impeccable à Bordeaux. Quartier des Chartrons très sympa, proche de tout.', '2023-08-14 13:25:00', TRUE),
(7, 7, 14, 4, 'Maison familiale parfaite à Toulouse. Bien équipée, proche du centre. Recommandé!', '2023-09-11 15:40:00', TRUE),
(8, 8, 16, 3, 'Studio correct à Nantes mais un peu petit. Bien situé néanmoins.', '2023-10-16 08:55:00', TRUE),
(9, 9, 18, 5, 'Villa exceptionnelle à Cannes! Luxe et confort au rendez-vous. Expérience inoubliable.', '2023-11-26 12:30:00', TRUE),
(10, 10, 2, 5, 'Chalet parfait pour les vacances de Noël au ski. Très bien équipé, accès pistes facile.', '2023-12-23 14:45:00', TRUE),
(12, 12, 6, 4, 'Maison alsacienne authentique à Strasbourg. Cadre magnifique, quelques équipements à moderniser.', '2024-02-18 10:20:00', TRUE),
(13, 13, 8, 4, 'Studio moderne à Montpellier, bien situé. Parfait pour un court séjour.', '2024-03-24 16:35:00', TRUE),
(15, 15, 12, 5, 'Chalet avec vue lac dAnnecy absolument magnifique! Cadre idyllique, très reposant.', '2024-05-21 11:50:00', TRUE),
(16, 16, 14, 4, 'Appartement bien situé à Rennes, centre-ville accessible à pied. Bon rapport qualité-prix.', '2024-06-14 13:15:00', TRUE),
(18, 18, 18, 3, 'Studio basique à Grenoble mais fonctionnel. Bien pour une nuit ou deux.', '2024-08-16 09:40:00', TRUE),
(20, 20, 4, 5, 'Chalet exceptionnel à Val Thorens! Pied des pistes, équipement haut de gamme. À recommander absolument!', '2024-10-23 15:25:00', TRUE),
(1, 1, 2, 5, 'Deuxième séjour dans cet appartement parisien, toujours aussi parfait!', '2024-01-15 12:00:00', TRUE),
(2, 2, 4, 5, 'Retour à Lyon dans cette belle maison. Propriétaire a pris en compte nos remarques précédentes.', '2024-02-20 14:30:00', TRUE),
(4, 4, 8, 4, 'Villa Nice toujours au top. Parfait pour les vacances dété.', '2024-07-15 16:45:00', TRUE),
(6, 6, 12, 5, 'Bordeaux et cet appartement, un combo gagnant! Troisième séjour et toujours ravi.', '2024-09-10 10:15:00', TRUE);

-- 6. MESSAGES TABLE (Depends on: users, annonces)
INSERT INTO Messages (id_expediteur, id_destintaire, id_annonce, objet, date_envoi, lu) VALUES
(2, 1, 1, 'Demande de réservation appartement Paris', '2023-02-10 14:30:00', TRUE),
(1, 2, 1, 'Confirmation disponibilité appartement Paris', '2023-02-10 15:45:00', TRUE),
(4, 3, 2, 'Question sur la maison Lyon', '2023-03-05 10:20:00', TRUE),
(3, 4, 2, 'Réponse informations maison Lyon', '2023-03-05 11:30:00', TRUE),
(6, 5, 3, 'Réservation studio Marseille', '2023-04-01 16:15:00', TRUE),
(5, 6, 3, 'Confirmation studio Marseille', '2023-04-01 17:00:00', TRUE),
(8, 7, 4, 'Demande villa Nice été', '2023-05-15 09:45:00', TRUE),
(7, 8, 4, 'Disponibilité villa Nice confirmée', '2023-05-15 10:30:00', TRUE),
(10, 9, 5, 'Chalet Chamonix juillet', '2023-06-10 13:20:00', TRUE),
(9, 10, 5, 'Réservation chalet Chamonix OK', '2023-06-10 14:15:00', TRUE),
(12, 11, 6, 'Appartement Bordeaux août', '2023-07-05 11:50:00', TRUE),
(11, 12, 6, 'Confirmation Bordeaux', '2023-07-05 12:45:00', TRUE),
(14, 13, 7, 'Maison Toulouse septembre', '2023-08-01 15:30:00', TRUE),
(13, 14, 7, 'Disponibilité Toulouse OK', '2023-08-01 16:20:00', TRUE),
(16, 15, 8, 'Studio Nantes octobre', '2023-09-10 08:40:00', FALSE),
(18, 17, 9, 'Villa Cannes novembre', '2023-10-15 12:25:00', TRUE),
(17, 18, 9, 'Confirmation villa Cannes', '2023-10-15 13:10:00', TRUE),
(2, 19, 10, 'Chalet Megève décembre', '2023-11-10 14:55:00', TRUE),
(19, 2, 10, 'Réservation Megève confirmée', '2023-11-10 15:40:00', TRUE),
(4, 1, 11, 'Question appartement Lille', '2023-12-05 10:15:00', FALSE);

-- 7. FAVORIS TABLE (Depends on: users, annonces)
INSERT INTO Favoris (id_user, id_annonce, date_ajout) VALUES
(2, 1, '2023-02-05 10:30:00'),
(2, 4, '2023-02-10 14:20:00'),
(2, 9, '2023-02-15 16:45:00'),
(4, 2, '2023-03-01 09:15:00'),
(4, 7, '2023-03-05 11:30:00'),
(4, 12, '2023-03-10 13:50:00'),
(6, 3, '2023-03-20 15:25:00'),
(6, 8, '2023-03-25 17:40:00'),
(6, 13, '2023-03-30 19:10:00'),
(8, 4, '2023-04-05 08:35:00'),
(8, 9, '2023-04-10 10:50:00'),
(8, 14, '2023-04-15 12:15:00'),
(10, 5, '2023-04-20 14:40:00'),
(10, 10, '2023-04-25 16:55:00'),
(10, 15, '2023-04-30 18:20:00'),
(12, 6, '2023-05-05 09:45:00'),
(12, 11, '2023-05-10 11:10:00'),
(12, 16, '2023-05-15 13:35:00'),
(14, 7, '2023-05-20 15:00:00'),
(14, 12, '2023-05-25 17:25:00');

-- =====================================================
-- End of Sample Data
-- =====================================================