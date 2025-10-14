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


