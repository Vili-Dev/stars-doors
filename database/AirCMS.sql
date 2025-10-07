CREATE TABLE IF NOT EXISTS `users` (
	`id_user` int AUTO_INCREMENT NOT NULL UNIQUE,
	`nom` varchar(80),
	`prenom` varchar(80),
	`email` varchar(100),
	`mot_de_passe` varchar(255),
	`telephone` varchar(20) NOT NULL,
	`date_de_naissance` varchar(255) NOT NULL,
	`race` varchar(255) NOT NULL,
	`role` enum('locataire', 'proprietaire', 'admin') NOT NULL DEFAULT 'locataire',
	`date_inscription` datetime NOT NULL,
	`actif` boolean NOT NULL DEFAULT TRUE,
	PRIMARY KEY (`id_user`)
);

CREATE TABLE IF NOT EXISTS `Avis` (
	`id_avis` int AUTO_INCREMENT NOT NULL UNIQUE,
	`id_reservation` int NOT NULL UNIQUE,
	`id_annonce` int NOT NULL,
	`id_user` int NOT NULL,
	`note` int NOT NULL,
	`commentaire` text NOT NULL,
	`date_avis` datetime NOT NULL DEFAULT NOW(),
	`visible` boolean NOT NULL DEFAULT TRUE,
	PRIMARY KEY (`id_avis`)
);

CREATE TABLE IF NOT EXISTS `annonces` (
	`id_annonce` int AUTO_INCREMENT NOT NULL UNIQUE,
	`id_user` int NOT NULL,
	`id_photo` int NOT NULL,
	`titre` varchar(50) NOT NULL,
	`description` varchar(255),
	`adresse` varchar(255),
	`ville` varchar(150) NOT NULL,
	`pays` varchar(50) NOT NULL DEFAULT 'France',
	`code_postal` varchar(10),
	`prix_nuit` decimal(10,0) NOT NULL,
	`nb_chambres` int NOT NULL DEFAULT '1',
	`nb_lits` int NOT NULL DEFAULT '1',
	`nb_salles_bain` int NOT NULL DEFAULT '1',
	`capacite_max` int NOT NULL,
	`type_logement` enum('appartement', 'maison', 'studio', 'villa', 'chalet', 'autre') NOT NULL,
	`wifi` boolean NOT NULL DEFAULT FALSE,
	`parking` boolean NOT NULL DEFAULT FALSE,
	`climatisation` boolean NOT NULL DEFAULT FALSE,
	`lave_linge` boolean NOT NULL DEFAULT FALSE,
	`television` boolean NOT NULL DEFAULT FALSE,
	`animaux_acceptes` boolean NOT NULL DEFAULT FALSE,
	`type_dair` varchar(255) NOT NULL DEFAULT 'oxygene',
	`bouteille_air` enum('azote', 'oxygene', 'helium', 'air_comprime') NOT NULL DEFAULT 'azote',
	`date_creation` datetime NOT NULL,
	`disponible` boolean NOT NULL DEFAULT TRUE,
	PRIMARY KEY (`id_annonce`)
);

CREATE TABLE IF NOT EXISTS `photo` (
	`id_photo` int AUTO_INCREMENT NOT NULL UNIQUE,
	`id_annonce` int NOT NULL,
	`nom_fichier` varchar(255) NOT NULL,
	`type_logement` varchar(255),
	`description` varchar(255),
	`photo_principale` boolean DEFAULT FALSE,
	`ordre_affichage` int NOT NULL DEFAULT '1',
	`date_upload` datetime NOT NULL DEFAULT NOW(),
	PRIMARY KEY (`id_photo`)
);

CREATE TABLE IF NOT EXISTS `Messages` (
	`id_message` int AUTO_INCREMENT NOT NULL UNIQUE,
	`id_expediteur` int NOT NULL,
	`id_destintaire` int NOT NULL,
	`id_annonce` int NOT NULL,
	`objet` varchar(200) NOT NULL,
	`date_envoi` datetime NOT NULL DEFAULT NOW(),
	`lu` boolean NOT NULL DEFAULT FALSE,
	PRIMARY KEY (`id_message`)
);

CREATE TABLE IF NOT EXISTS `reservations` (
	`id_reservation` int AUTO_INCREMENT NOT NULL UNIQUE,
	`id_annonce` int NOT NULL,
	`id_user` int NOT NULL,
	`date_debut` date NOT NULL,
	`date_fin` date NOT NULL,
	`nb_personnes` int NOT NULL,
	`prix_total` decimal(10,0) NOT NULL,
	`statut` enum('en_attente', 'confirmee', 'annulee', 'terminee') NOT NULL DEFAULT 'en_attente',
	`date_reservation` datetime NOT NULL DEFAULT NOW(),
	`message` text,
	PRIMARY KEY (`id_reservation`)
);

CREATE TABLE IF NOT EXISTS `Favoris` (
	`id_favori` int AUTO_INCREMENT NOT NULL UNIQUE,
	`id_user` int NOT NULL,
	`id_annonce` int NOT NULL,
	`date_ajout` datetime NOT NULL DEFAULT NOW(),
	PRIMARY KEY (`id_favori`)
);

-- ALTER TABLE `users` ADD CONSTRAINT `users_fk0` FOREIGN KEY (`id_user`) REFERENCES `annonces`(`id_user`);
ALTER TABLE `Avis` ADD CONSTRAINT `Avis_fk1` FOREIGN KEY (`id_reservation`) REFERENCES `reservations`(`id_reservation`);
ALTER TABLE `Avis` ADD CONSTRAINT `Avis_fk2` FOREIGN KEY (`id_annonce`) REFERENCES `annonces`(`id_annonce`);
ALTER TABLE `Avis` ADD CONSTRAINT `Avis_fk3` FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`);

ALTER TABLE `annonces` ADD CONSTRAINT `annonces_fk1` FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`);
ALTER TABLE `annonces` ADD CONSTRAINT `annonces_fk2` FOREIGN KEY (`id_photo`) REFERENCES `photo`(`id_photo`);

-- ALTER TABLE `annonces` ADD CONSTRAINT `annonces_fk14` FOREIGN KEY (`type_logement`) REFERENCES `recherche`(`type_logement`);
ALTER TABLE `photo` ADD CONSTRAINT `photo_fk1` FOREIGN KEY (`id_annonce`) REFERENCES `annonces`(`id_annonce`);

ALTER TABLE `Messages` ADD CONSTRAINT `Messages_fk1` FOREIGN KEY (`id_expediteur`) REFERENCES `users`(`id_user`);
ALTER TABLE `Messages` ADD CONSTRAINT `Messages_fk2` FOREIGN KEY (`id_destintaire`) REFERENCES `users`(`id_user`);
ALTER TABLE `Messages` ADD CONSTRAINT `Messages_fk3` FOREIGN KEY (`id_annonce`) REFERENCES `annonces`(`id_annonce`);

ALTER TABLE `reservations` ADD CONSTRAINT `reservations_fk1` FOREIGN KEY (`id_annonce`) REFERENCES `annonces`(`id_annonce`);
ALTER TABLE `reservations` ADD CONSTRAINT `reservations_fk2` FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`);

ALTER TABLE `Favoris` ADD CONSTRAINT `Favoris_fk1` FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`);
ALTER TABLE `Favoris` ADD CONSTRAINT `Favoris_fk2` FOREIGN KEY (`id_annonce`) REFERENCES `annonces`(`id_annonce`);