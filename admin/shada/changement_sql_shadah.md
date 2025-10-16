ALTER TABLE annonces 
ADD COLUMN statut ENUM('en_attente', 'approuve', 'rejete') 
NOT NULL DEFAULT 'en_attente' 
AFTER type_logement;


UPDATE annonces SET statut = 'approuve';


ALTER TABLE avis 
ADD COLUMN statut ENUM('en_attente', 'approuve', 'rejete') 
NOT NULL DEFAULT 'approuve' 
AFTER commentaire;

UPDATE avis 
SET statut = CASE 
    WHEN visible = 1 THEN 'approuve'
    WHEN visible = 0 THEN 'rejete'
    ELSE 'en_attente'
END;

15/10/2025

CREATE TABLE litiges (
    id_litige INT AUTO_INCREMENT PRIMARY KEY, 
   	id_annonce INT NULL,
    id_user INT NOT NULL,
    id_reservation INT NULL,
    sujet VARCHAR(255) NOT NULL,
    description text NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'en_cours', 'resolu', 'rejete') DEFAULT 'en_attente',
    FOREIGN KEY (id_annonce) REFERENCES annonces(id_annonce),
    FOREIGN KEY (id_user) REFERENCES users(id_user),
    FOREIGN KEY (id_reservation) REFERENCES reservations(id_reservation)
    );
    


