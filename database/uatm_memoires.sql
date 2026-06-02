-- UATM - Gestion des Mémoires Soutenus
-- Base de données MySQL (Version nettoyée pour InfinityFree)

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  nom VARCHAR(100) NOT NULL,
  description TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  matricule VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  niveau_etude ENUM('L1', 'L2', 'L3', 'M1', 'M2') NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  must_change_password TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
  INDEX idx_users_role (role_id),
  INDEX idx_users_actif (actif)
) ENGINE=InnoDB;

CREATE TABLE filieres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  nom VARCHAR(150) NOT NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE centres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,
  nom VARCHAR(150) NOT NULL,
  ville VARCHAR(100) NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE memoires (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  filiere_id INT NOT NULL,
  centre_id INT NOT NULL,
  encadreur_id INT NULL,
  encadreur_nom VARCHAR(200) NOT NULL,
  titre VARCHAR(300) NOT NULL,
  auteur VARCHAR(200) NOT NULL,
  annee_academique VARCHAR(20) NOT NULL,
  ville VARCHAR(100) NOT NULL,
  resume TEXT NOT NULL,
  mots_cles VARCHAR(500) NOT NULL,
  fichier_path VARCHAR(500) NOT NULL,
  fichier_type ENUM('pdf', 'word') NOT NULL DEFAULT 'pdf',
  couverture_path VARCHAR(500) NULL,
  statut ENUM('en_attente', 'valide', 'rejete') NOT NULL DEFAULT 'en_attente',
  motif_rejet TEXT NULL,
  nb_vues INT NOT NULL DEFAULT 0,
  note_moyenne DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  nb_notes INT NOT NULL DEFAULT 0,
  date_depot DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_validation DATETIME NULL,
  CONSTRAINT fk_memoires_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_memoires_filiere FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE RESTRICT,
  CONSTRAINT fk_memoires_centre FOREIGN KEY (centre_id) REFERENCES centres(id) ON DELETE RESTRICT,
  CONSTRAINT fk_memoires_encadreur FOREIGN KEY (encadreur_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_memoires_statut (statut),
  INDEX idx_memoires_filiere (filiere_id),
  INDEX idx_memoires_centre (centre_id),
  INDEX idx_memoires_annee (annee_academique),
  FULLTEXT idx_memoires_search (titre, auteur, resume, mots_cles, encadreur_nom)
) ENGINE=InnoDB;

CREATE TABLE commentaires (
  id INT AUTO_INCREMENT PRIMARY KEY,
  memoire_id INT NOT NULL,
  user_id INT NOT NULL,
  contenu TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commentaires_memoire FOREIGN KEY (memoire_id) REFERENCES memoires(id) ON DELETE CASCADE,
  CONSTRAINT fk_commentaires_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_commentaires_memoire (memoire_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE favoris (
  id INT AUTO_INCREMENT PRIMARY KEY,
  memoire_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_favoris_memoire FOREIGN KEY (memoire_id) REFERENCES memoires(id) ON DELETE CASCADE,
  CONSTRAINT fk_favoris_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uk_favoris (memoire_id, user_id)
) ENGINE=InnoDB;

CREATE TABLE notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  memoire_id INT NOT NULL,
  user_id INT NOT NULL,
  note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_notes_memoire FOREIGN KEY (memoire_id) REFERENCES memoires(id) ON DELETE CASCADE,
  CONSTRAINT fk_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uk_notes (memoire_id, user_id)
) ENGINE=InnoDB;

CREATE TABLE validations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  memoire_id INT NOT NULL,
  professeur_id INT NOT NULL,
  action ENUM('valide', 'rejete') NOT NULL,
  motif TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_validations_memoire FOREIGN KEY (memoire_id) REFERENCES memoires(id) ON DELETE CASCADE,
  CONSTRAINT fk_validations_prof FOREIGN KEY (professeur_id) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_validations_memoire (memoire_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  memoire_id INT NULL,
  type VARCHAR(50) NOT NULL,
  titre VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  lu TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_notifications_memoire FOREIGN KEY (memoire_id) REFERENCES memoires(id) ON DELETE SET NULL,
  INDEX idx_notifications_user (user_id, lu, created_at)
) ENGINE=InnoDB;

CREATE TABLE statistiques_consultation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  memoire_id INT NOT NULL,
  user_id INT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  consulted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_stats_memoire FOREIGN KEY (memoire_id) REFERENCES memoires(id) ON DELETE CASCADE,
  CONSTRAINT fk_stats_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_stats_memoire (memoire_id, consulted_at)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_logs_action (action, created_at)
) ENGINE=InnoDB;

-- Rôles
INSERT INTO roles (code, nom, description) VALUES
('admin', 'Administrateur', 'Gestion complète de la plateforme'),
('directeur_etudes', 'Directeur des Études', 'Consultation, statistiques et rapports'),
('professeur', 'Professeur', 'Validation et encadrement des mémoires'),
('etudiant_diplome', 'Étudiant Diplômé', 'Dépôt et suivi de mémoire (L3/M2)'),
('etudiant_consultant', 'Étudiant Consultant', 'Consultation et recherche (L1/L2/M1)');

-- Filières exemple
INSERT INTO filieres (code, nom) VALUES
('INFO', 'Informatique'),
('GEST', 'Gestion'),
('DROIT', 'Droit'),
('COM', 'Communication');

-- Centres exemple
INSERT INTO centres (code, nom, ville) VALUES
('UATM-CTN', 'UATM Cotonou', 'Cotonou'),
('UATM-PNO', 'UATM Porto-Novo', 'Porto-Novo'),
('UATM-PAR', 'UATM Parakou', 'Parakou');

-- Administrateur par défaut (mot de passe: Admin@2026)
INSERT INTO users (role_id, nom, prenom, matricule, email, password, must_change_password, niveau_etude) VALUES
(1, 'Admin', 'UATM', 'ADM001', 'admin@uatm.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL);

-- Directeur des études (mot de passe: Directeur@2026)
INSERT INTO users (role_id, nom, prenom, matricule, email, password, must_change_password, niveau_etude) VALUES
(2, 'Koné', 'Amadou', 'DIR001', 'directeur@uatm.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL);

-- Professeur exemple (mot de passe: Prof@2026)
INSERT INTO users (role_id, nom, prenom, matricule, email, password, must_change_password, niveau_etude) VALUES
(3, 'Diallo', 'Fatou', 'PROF001', 'f.diallo@uatm.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL);

-- Étudiant diplômé exemple (mot de passe: Etudiant@2026)
INSERT INTO users (role_id, nom, prenom, matricule, email, password, must_change_password, niveau_etude) VALUES
(4, 'Traoré', 'Ibrahim', 'ETU2024001', 'i.traore@uatm.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'L3');

-- Étudiant consultant exemple (mot de passe: Consultant@2026)
INSERT INTO users (role_id, nom, prenom, matricule, email, password, must_change_password, niveau_etude) VALUES
(5, 'Bamba', 'Aïcha', 'ETU2024101', 'a.bamba@uatm.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'L2');