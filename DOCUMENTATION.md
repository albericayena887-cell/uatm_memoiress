# Documentation complète — UATM GASA

**Plateforme de gestion, consultation, validation et archivage des mémoires soutenus**

Version document : juin 2026  
Environnement cible : WAMP (Windows) / PHP 8 / MySQL  
Localisation : Université au **Bénin** (villes : Cotonou, Porto-Novo, Parakou, etc.)

---

## Table des matières

1. [Présentation du projet](#1-présentation-du-projet)
2. [Technologies utilisées](#2-technologies-utilisées)
3. [Architecture MVC](#3-architecture-mvc)
4. [Installation et déploiement](#4-installation-et-déploiement)
5. [Configuration](#5-configuration)
6. [Base de données](#6-base-de-données)
7. [Rôles et permissions](#7-rôles-et-permissions)
8. [Fonctionnalités par profil](#8-fonctionnalités-par-profil)
9. [Processus métier](#9-processus-métier)
10. [Routes et URLs](#10-routes-et-urls)
11. [Sécurité](#11-sécurité)
12. [E-mails et notifications](#12-e-mails-et-notifications)
13. [Fichiers uploadés](#13-fichiers-uploadés)
14. [Interface et thème](#14-interface-et-thème)
15. [Dépannage](#15-dépannage)
16. [Annexes](#16-annexes)

---

## 1. Présentation du projet

### 1.1 Objectif

UATM GASA permet à une université de :

- Centraliser les mémoires soutenus (L3, M2, etc.)
- Contrôler qui peut déposer, consulter, valider ou administrer
- Consulter les PDF **en ligne uniquement** (sans téléchargement public)
- Suivre les validations, statistiques, commentaires et notes
- Gérer les comptes **exclusivement via l’administrateur** (pas d’inscription publique)

### 1.2 Principes clés

| Principe | Détail |
|----------|--------|
| Pas d’inscription libre | Tous les comptes sont créés par l’admin |
| Consultation sécurisée | PDF servi via PHP, iframe protégée |
| Validation encadrée | Professeur encadreur valide ou rejette |
| Contexte Bénin | Centres et mémoires utilisent le champ **Ville** |
| Promotion académique | L2→L3 et M1→M2 ouvrent le dépôt de mémoire |

---

## 2. Technologies utilisées

| Couche | Technologie |
|--------|-------------|
| Front-end | HTML5, CSS3, Bootstrap 5, JavaScript |
| Animations | AOS (Animate On Scroll) |
| Back-end | PHP 8 (mode strict `declare(strict_types=1)`) |
| Base de données | MySQL / MariaDB (utf8mb4) |
| Serveur local | Apache (WAMP) |
| Accès données | PDO (requêtes préparées) |
| Sessions | PHP natives (`session_name: UATM_MEMOIRES`) |

---

## 3. Architecture MVC

```
uatm_memoires/
├── index.php                 # Point d'entrée, helpers, autoload
├── .htaccess                 # Réécriture + protection storage
├── routes/web.php            # Routeur (?page= & ?action=)
├── app/
│   ├── config.php            # Configuration application
│   ├── core/                 # Database, Controller, Mailer, FileUpload
│   ├── models/               # Accès données (User, Memoire, etc.)
│   ├── controllers/          # Logique métier
│   └── views/                # Templates PHP + layouts
├── public/assets/            # CSS, JS (accessibles)
├── storage/                  # Fichiers protégés (mémoires, logs)
└── database/uatm_memoires.sql
```

### 3.1 Flux d’une requête

1. `index.php` démarre la session et charge les helpers
2. `routes/web.php` lit `$_GET['page']` et `$_GET['action']`
3. Le contrôleur correspondant est instancié
4. La méthode est exécutée (ex. `AdminController::users()`)
5. La vue est rendue via `Controller::view()` et le layout `main` ou `auth`

### 3.2 Modèles principaux

| Modèle | Rôle |
|--------|------|
| `User` | Comptes, promotion de niveau, import CSV |
| `Memoire` | CRUD mémoires, recherche, statistiques |
| `Role` | Rôles système |
| `Filiere` / `Centre` | Référentiels académiques |
| `Commentaire` / `Note` / `Favori` | Interactions utilisateurs |
| `Validation` | Historique validation professeur |
| `Notification` | Alertes in-app |
| `StatistiqueConsultation` | Compteur de vues |
| `ActivityLog` | Journalisation des actions |

---

## 4. Installation et déploiement

### 4.1 Prérequis

- WAMP, XAMPP ou LAMP
- PHP **8.0+** avec extensions : `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`
- MySQL **5.7+** ou MariaDB **10.3+**
- Apache avec `mod_rewrite` (recommandé)

### 4.2 Étapes d’installation

1. **Copier** le dossier dans `C:\wamp64\www\uatm_memoires\`

2. **Créer la base de données**  
   Via phpMyAdmin ou ligne de commande :
   ```bash
   mysql -u root -p < database/uatm_memoires.sql
   ```

3. **Configurer** `app/config.php` si besoin (hôte, utilisateur, mot de passe MySQL)

4. **Droits d’écriture** sur :
   - `storage/memoires/`
   - `storage/couvertures/`
   - `storage/logs/`

5. **Accéder** à l’application :
   ```
   http://localhost/uatm_memoires/
   ```

6. **Vider le cache navigateur** (Ctrl+F5) après mise à jour CSS/JS

### 4.3 Migration base existante

Si la base était créée avec l’ancienne colonne `pays` :

```sql
USE uatm_memoires;
ALTER TABLE centres CHANGE pays ville VARCHAR(100) NULL;
ALTER TABLE memoires CHANGE pays ville VARCHAR(100) NOT NULL;
ALTER TABLE users ADD COLUMN niveau_etude ENUM('L1','L2','L3','M1','M2') NULL AFTER password;
```

---

## 5. Configuration

Fichier : `app/config.php`

| Clé | Description | Valeur par défaut |
|-----|-------------|-------------------|
| `app_name` | Nom affiché | UATM GASA |
| `app_url` | URL de base (liens e-mail) | http://localhost/uatm_memoires |
| `db_host` | Serveur MySQL | 127.0.0.1 |
| `db_name` | Nom de la base | uatm_memoires |
| `db_user` / `db_pass` | Identifiants MySQL | root / vide |
| `mail_from` | Expéditeur e-mail | noreply@uatm.edu |
| `upload_max_size` | Taille max upload mémoire (PDF/Word) | 10 Mo |

Variables d’environnement supportées : `APP_URL`, `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `MAIL_FROM`.

---

## 6. Base de données

### 6.1 Schéma relationnel (résumé)

```
roles ──< users ──< memoires >── filieres
                  │              centres
                  ├── commentaires
                  ├── favoris
                  ├── notes
                  ├── validations
                  └── notifications

statistiques_consultation >── memoires
activity_logs (journal système)
```

### 6.2 Tables principales

#### `users`
| Colonne | Description |
|---------|-------------|
| `role_id` | Lien vers `roles` |
| `matricule` | Identifiant unique |
| `email` | Connexion + récupération OTP |
| `password` | Hash bcrypt |
| `niveau_etude` | L1, L2, L3, M1, M2 |
| `must_change_password` | Forcer changement à la 1ère connexion |
| `actif` | Compte activé ou non |

#### `memoires`
| Colonne | Description |
|---------|-------------|
| `statut` | `en_attente`, `valide`, `rejete` |
| `ville` | Ville (ex. Cotonou) |
| `fichier_path` | Chemin relatif dans `storage/` |
| `fichier_type` | `pdf` ou `word` |
| `nb_vues` | Compteur consultations |
| `note_moyenne` / `nb_notes` | Notation communautaire |

#### `centres`
| Colonne | Description |
|---------|-------------|
| `code` | Ex. UATM-CTN |
| `nom` | Ex. UATM Cotonou |
| `ville` | Ex. Cotonou |

### 6.3 Données de démonstration

| Rôle | E-mail | Mot de passe initial |
|------|--------|----------------------|
| Administrateur | admin@uatm.edu | `password` |
| Directeur des études | directeur@uatm.edu | `password` |
| Professeur | f.diallo@uatm.edu | `password` |
| Étudiant diplômé (L3) | i.traore@uatm.edu | `password` |
| Étudiant consultant (L2) | a.bamba@uatm.edu | `password` |

> À la première connexion, l’utilisateur est invité à changer son mot de passe.

### 6.4 Centres exemple (Bénin)

| Code | Nom | Ville |
|------|-----|-------|
| UATM-CTN | UATM Cotonou | Cotonou |
| UATM-PNO | UATM Porto-Novo | Porto-Novo |
| UATM-PAR | UATM Parakou | Parakou |

---

## 7. Rôles et permissions

### 7.1 Codes de rôles

| Code | Libellé |
|------|---------|
| `admin` | Administrateur |
| `directeur_etudes` | Directeur des Études |
| `professeur` | Professeur |
| `etudiant_diplome` | Étudiant Diplômé (L3/M2) |
| `etudiant_consultant` | Étudiant Consultant (L1/L2/M1) |

### 7.2 Matrice des droits

| Action | Admin | Directeur | Professeur | Diplômé | Consultant |
|--------|:-----:|:---------:|:----------:|:-------:|:----------:|
| Créer comptes | ✅ | ❌ | ❌ | ❌ | ❌ |
| Gérer filières/centres | ✅ | ❌ | ❌ | ❌ | ❌ |
| Promouvoir niveau (L2→L3…) | ✅ | ❌ | ❌ | ❌ | ❌ |
| Déposer mémoire | ❌* | ❌ | ❌ | ✅ | ❌ |
| Modifier son mémoire (avant validation) | — | — | — | ✅ | ❌ |
| Valider / rejeter | ❌ | ❌ | ✅** | ❌ | ❌ |
| Recherche mémoires validés | ✅ | ✅ | ✅ | ✅ | ✅ |
| Consulter PDF en ligne | ✅ | ✅ | ✅ | ✅ (validés) | ✅ (validés) |
| Télécharger PDF | ❌*** | ❌ | ❌ | ❌ | ❌ |
| Commenter | ✅† | ✅† | ✅† | ❌‡ | ✅† |
| Noter (étoiles) | ✅ | ✅ | ✅ | ❌ | ✅ |
| Favoris | ❌ | ❌ | ❌ | ❌ | ✅ |
| Tableau de bord stats | ✅ | ✅ | Partiel | Partiel | Partiel |
| Export rapport CSV | ✅ | ✅ | ❌ | ❌ | ❌ |

\* L’admin peut importer des mémoires manuellement.  
\** Uniquement les mémoires dont il est encadreur assigné.  
\*** Consultation en ligne uniquement (iframe, pas de bouton téléchargement).  
† Sauf sur son propre mémoire (auteur ne peut pas commenter).  
‡ L’étudiant diplômé ne peut pas commenter son propre dépôt.

---

## 8. Fonctionnalités par profil

### 8.1 Administrateur

- **Utilisateurs** : créer, modifier, supprimer, importer CSV
- **Promotion** : bouton « Promouvoir » (L1→L2, L2→L3, M1→M2)
- **Filières** et **Centres (villes Bénin)**
- **Mémoires** : liste complète, suppression, import unitaire
- **Tableau de bord** : statistiques globales + export CSV

**Import utilisateurs CSV** (séparateur `;`) :

```csv
nom;prenom;matricule;email;role;password
Dupont;Jean;ETU2025001;jean@uatm.edu;etudiant_consultant;Temp@2026
```

Rôles valides : `admin`, `directeur_etudes`, `professeur`, `etudiant_diplome`, `etudiant_consultant`.

### 8.2 Directeur des études

- Consulter tous les mémoires (tous statuts en recherche)
- Statistiques par filière et par année
- Export rapport CSV
- Pas de gestion des comptes

### 8.3 Professeur

- Liste des mémoires **en attente** dont il est encadreur
- Consultation PDF + validation ou rejet (motif obligatoire si rejet)
- E-mail automatique à l’étudiant après décision
- Historique des validations

### 8.4 Étudiant diplômé (L3 / M2)

- **Déposer** un mémoire (PDF ou Word + couverture optionnelle)
- **Modifier** tant que statut = `en_attente` ou `rejete`
- Suivre le statut et le motif de rejet
- **Ne peut pas** commenter son propre mémoire

Champs du dépôt : titre, auteur, filière, centre, année académique, **ville**, résumé, mots-clés, encadreur.

### 8.5 Étudiant consultant (L1 / L2 / M1)

- Recherche avancée sur mémoires **validés uniquement**
- Consultation en ligne (PDF)
- Favoris, commentaires, notation
- **Interdictions** : dépôt, modification, validation, téléchargement

---

## 9. Processus métier

### 9.1 Cycle de vie d’un mémoire

```
[Dépôt étudiant]
      │
      ▼
 en_attente ──► E-mail au professeur encadreur
      │
      ├──► valide ──► Visible en recherche + e-mail étudiant
      │
      └──► rejete ──► Motif + e-mail étudiant ──► Modification possible ──► resoumission
```

### 9.2 Promotion académique automatique

| Niveau actuel | Niveau suivant | Rôle après promotion |
|---------------|----------------|----------------------|
| L1 | L2 | etudiant_consultant |
| L2 | L3 | **etudiant_diplome** (dépôt mémoire) |
| L3 | L3 | etudiant_diplome |
| M1 | M2 | **etudiant_diplome** |
| M2 | M2 | etudiant_diplome |

Action admin : bouton **Promouvoir** sur la liste des utilisateurs.

### 9.3 Récupération mot de passe (OTP)

1. L’utilisateur saisit son **e-mail** sur « Mot de passe oublié »
2. Un code OTP à 6 chiffres est envoyé (valide **10 minutes**)
3. Saisie OTP + nouveau mot de passe
4. Connexion avec le nouveau mot de passe

Les e-mails sont journalisés dans `storage/logs/emails.log` si `mail()` PHP n’est pas configuré.

### 9.4 Consultation PDF sécurisée

- Fichiers stockés dans `storage/memoires/` (hors accès direct web)
- Affichage via `MemoireController::viewer()` avec en-têtes `Content-Disposition: inline`
- Modal avec iframe `#toolbar=0`
- Clic droit désactivé côté interface (protection partielle)

---

## 10. Routes et URLs

Format général : `index.php?page=<page>&action=<action>&id=<id>`

### 10.1 Pages publiques / authentification

| URL | Description |
|-----|-------------|
| `?page=home` | Page d’accueil |
| `?page=login` | Connexion |
| `?page=forgot-password` | Mot de passe oublié (OTP) |
| `?page=logout` | Déconnexion |
| `?page=change-password` | Changer le mot de passe |

### 10.2 Espace connecté

| URL | Description |
|-----|-------------|
| `?page=dashboard` | Tableau de bord |
| `?page=dashboard&action=report` | Export CSV (admin/directeur) |
| `?page=memoires` | Recherche mémoires |
| `?page=memoires&action=show&id=N` | Détail mémoire |
| `?page=memoires&action=viewer&id=N` | Flux PDF |
| `?page=memoires&action=comment` | POST commentaire |
| `?page=memoires&action=favorite` | POST favori |
| `?page=memoires&action=rate` | POST notation |

### 10.3 Administration

| URL | Description |
|-----|-------------|
| `?page=admin&action=users` | Liste utilisateurs |
| `?page=admin&action=userCreate` | Nouveau compte |
| `?page=admin&action=userEdit&id=N` | Modifier compte |
| `?page=admin&action=userDelete` | POST suppression |
| `?page=admin&action=userPromote` | POST promotion niveau |
| `?page=admin&action=userImport` | Import CSV |
| `?page=admin&action=filieres` | Filières |
| `?page=admin&action=centres` | Centres / villes |
| `?page=admin&action=memoires` | Gestion mémoires |
| `?page=admin&action=memoireImport` | Import mémoire |

### 10.4 Professeur / Étudiant

| URL | Description |
|-----|-------------|
| `?page=professeur` | Validations en attente |
| `?page=professeur&action=validation&id=N` | Examiner et décider |
| `?page=professeur&action=validate` | POST validation/rejet |
| `?page=etudiant` | Mes mémoires (diplômé) |
| `?page=etudiant&action=deposit` | Déposer |
| `?page=etudiant&action=edit&id=N` | Modifier |
| `?page=etudiant&action=favoris` | Favoris (consultant) |

---

## 11. Sécurité

| Mesure | Implémentation |
|--------|----------------|
| Injection SQL | PDO + requêtes préparées |
| XSS | `htmlspecialchars()` via helper `e()` |
| CSRF | Jeton sur tous les formulaires POST |
| Mots de passe | `password_hash()` / `password_verify()` |
| Sessions | Clé dédiée `uatm_user`, nom `UATM_MEMOIRES` |
| Contrôle d’accès | `requireRole()`, `requireAuth()` |
| Fichiers | Stockage hors `public/`, `.htaccess` sur `storage/` |
| Upload | Vérification MIME (`finfo`), taille max, noms aléatoires |
| Journalisation | Table `activity_logs` |

### Bonnes pratiques en production

- Changer tous les mots de passe par défaut
- Utiliser HTTPS
- Configurer un vrai serveur SMTP (au lieu de `mail()`)
- Restreindre les permissions du compte MySQL
- Sauvegardes régulières de la base et de `storage/`

---

## 12. E-mails et notifications

### 12.1 E-mails automatiques

| Événement | Destinataire | Objet |
|-----------|--------------|-------|
| Dépôt mémoire | Professeur encadreur | Nouveau mémoire soumis pour validation |
| Validation | Étudiant | Votre mémoire a été validé |
| Rejet | Étudiant | Votre mémoire nécessite des corrections |
| OTP | Utilisateur | Code OTP de réinitialisation |

Classe : `app/core/Mailer.php`  
Log local : `storage/logs/emails.log`

### 12.2 Notifications in-app

Table `notifications` : alertes visibles sur le tableau de bord (validation, rejet, reset password, etc.).

---

## 13. Fichiers uploadés

| Type | Dossier | Formats |
|------|---------|---------|
| Mémoire PDF | `storage/memoires/` | application/pdf |
| Mémoire Word | `storage/memoires/` | .doc, .docx |
| Couverture | `storage/couvertures/` | jpeg, png, webp |

Taille maximale mémoire (PDF/Word) : **10 Mo** (configurable dans `app/config.php` → `upload_max_size`). Couverture : 5 Mo.

Les noms de fichiers sont générés aléatoirement (pas le nom original) pour limiter les devinations.

---

## 14. Interface et thème

- **Thème actuel** : Noir + doré (`#121212`, `#b08d57`)
- **Framework** : Bootstrap 5.3
- **Icônes** : Bootstrap Icons
- **Animations** : AOS (cartes, sections ; lignes de tableaux forcées visibles pour éviter l’invisibilité)

Fichiers de style :
- `public/assets/css/style.css`
- `public/assets/js/app.js`

---

## 15. Dépannage

### La liste utilisateurs est vide / texte invisible

- Cause fréquente : animation AOS sur les lignes du tableau
- Solution : Ctrl+F5 ; le correctif est dans `app.js` + `style.css`

### Caractères accentués en `??`

- Réimporter la base en **utf8mb4**
- Vérifier que PDO utilise `SET NAMES utf8mb4` (déjà dans `Database.php`)

### E-mails non reçus

- Consulter `storage/logs/emails.log`
- Configurer `sendmail` ou SMTP sur WAMP

### Erreur « colonne pays introuvable »

Exécuter la migration :
```sql
ALTER TABLE centres CHANGE pays ville VARCHAR(100) NULL;
ALTER TABLE memoires CHANGE pays ville VARCHAR(100) NOT NULL;
```

### Impossible de supprimer un utilisateur

L’utilisateur est lié à des mémoires, commentaires ou validations. Supprimer ou réassigner ces données d’abord, ou désactiver le compte (`actif = 0`).

### Session / mauvais menu affiché

La session utilise la clé `uatm_user` (pas `user`) pour éviter les conflits avec d’autres apps sur localhost.

---

## 16. Annexes

### 16.1 Recherche avancée — critères

- Titre, auteur, filière, centre, année académique, **ville**, encadreur, mots-clés
- Filtre statut (admin / directeur uniquement)

### 16.2 Statuts mémoire

| Code | Affichage |
|------|-----------|
| `en_attente` | En attente de validation |
| `valide` | Validé |
| `rejete` | Rejeté |

### 16.3 Structure des rôles en base

```sql
SELECT code, nom FROM roles ORDER BY id;
```

### 16.4 Contact technique (déploiement)

- Chemin projet : `C:\wamp64\www\uatm_memoires\`
- URL locale type : `http://localhost/uatm_memoires/`
- Base : `uatm_memoires`

---

**Fin de la documentation — UATM GASA**
