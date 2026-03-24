# JMI 56 - Projet BTS SIO

<p align="center"><img src="public/images/logo-jmi56.png" width="180" alt="JMI 56"></p>

Projet web realise dans le cadre du BTS SIO.
Le site presente l'activite d'un reparateur informatique local (JMI 56) et integre un back-office pour traiter les demandes clients.

## 1) Contexte et objectif
Objectifs du projet:
- proposer un site vitrine moderne, responsive et lisible;
- permettre l'envoi de demandes via un formulaire;
- permettre un suivi admin des demandes (statuts, recherche, suppression);
- appliquer des bases de securite web et de conformite RGPD;
- produire une documentation technique exploitable pour la maintenance.

## 2) Fonctionnalites realisees
### Site public
- sections: accueil, presentation, services, zone, partenaire, contact, mentions legales;
- formulaire de demande: nom, telephone, message;
- carte Google Maps avec fallback iframe;
- adaptation mobile/desktop.

### Back-office admin
- affichage des demandes par statut: `En attente`, `En cours`, `Termine`;
- changement de statut;
- recherche par nom ou telephone;
- suppression d'une demande;
- code couleur des statuts renforce;
- masquage du bouton du statut deja actif.

### Comptes et connexion
- route de connexion: `/login`;
- route de creation de compte utilisateur: `/register`;
- un utilisateur standard peut se connecter normalement (email + mot de passe);
- l'admin dispose d'un identifiant unique et d'un mot de passe dedie;
- seules les sessions admin peuvent acceder aux routes `/admin*`.

### Messagerie utilisateur (nouvelle fonctionnalite)
- apres avoir cree une demande de contact, un utilisateur connecte peut discuter avec l'admin;
- l'admin peut lire et repondre aux messages de chaque demande;
- structure des messages:
  - `sender_id` (expediteur)
  - `receiver_id` (destinataire)
  - `contact_request_id` (conversation liee a la demande)
  - `message` (contenu texte)
  - `status` (`unread` / `read`)
- organisation par conversation (une conversation = une demande de contact);
- statut de lecture gere (`unread` -> `read`).

## 3) Securite et RGPD
- validation serveur Laravel sur les formulaires (`$request->validate(...)`);
- nettoyage des champs texte du formulaire contact (`trim`, `strip_tags`);
- regeneration de session a la connexion;
- protection CSRF via Blade (`@csrf`);
- purge automatique des demandes anciennes selon `GDPR_RETENTION_DAYS`.

## 4) Stack technique
- Laravel 12;
- PHP 8.3;
- MySQL (tables `users`, `contact_requests`, `messages`, `sessions`);
- Vite (build front);
- CSS personnalise.

## 5) Fichiers importants du projet
- `routes/web.php`: logique metier principale (auth, admin, contact, RGPD);
- `resources/views/welcome.blade.php`: interface publique;
- `resources/views/admin/index.blade.php`: interface admin;
- `resources/views/auth/login.blade.php`: connexion;
- `resources/views/auth/register.blade.php`: creation de compte utilisateur;
- `resources/views/messages/index.blade.php`: interface de messagerie;
- `resources/css/app.css`: styles globaux;
- `tests/Feature/WebRoutesTest.php`: tests fonctionnels des routes principales;
- `database/migrations/2026_03_24_120000_create_messages_table.php`: migration messagerie;
- `database/migrations/2026_03_24_120100_add_user_id_to_contact_requests_table.php`: liaison demande -> utilisateur;
- `database/migrations/2026_03_24_120200_add_contact_request_id_to_messages_table.php`: liaison message -> demande;
- `docs/Documentation-PHPDoc.md`: documentation technique (DocBlock + Doxygen);
- `Doxyfile`: configuration Doxygen.

## 6) Installation et lancement
1. Cloner le projet.
2. Installer les dependances PHP:
   - `composer install`
   - si besoin local: `./composer install`
3. Copier l'environnement:
   - `cp .env.example .env`
4. Configurer la base MySQL dans `.env`.
5. Generer la cle:
   - `php artisan key:generate`
6. Migrer la base:
   - `php artisan migrate`
7. Lancer le serveur:
   - `php artisan serve`

Assets front (optionnel):
- `npm install`
- `npm run dev` (developpement)
- `npm run build` (production)

## 7) Configuration utile (.env)
- admin:
  - `ADMIN_USERNAME=...`
  - `ADMIN_PASSWORD=...`
- carte:
  - `GOOGLE_MAPS_KEY=...` (optionnel)

Valeurs par defaut admin si variables absentes:
- identifiant: `admin`
- mot de passe: `admin123`

## 8) Scenario de demonstration (jury)
1. Ouvrir la page d'accueil et presenter les sections publiques.
2. Soumettre une demande via le formulaire contact.
3. Creer un compte utilisateur (`/register`) et montrer la connexion standard.
4. Montrer qu'un utilisateur standard n'a pas acces a `/admin`.
5. Se connecter en admin (`/login`) avec l'identifiant admin.
6. Montrer:
   - tri par statut;
   - passage `En attente` -> `En cours` -> `Termine`;
   - recherche;
   - suppression;
   - impact visuel des statuts.
7. Se reconnecter avec un compte utilisateur et montrer la messagerie:
   - envoi de message a l'admin dans la conversation de la demande;
   - reponse de l'admin dans la meme conversation;
   - passage du statut `unread` a `read`.

## 9) Tests et verification
Suite de tests mise en place:
- tests unitaires de base;
- tests feature Laravel;
- tests fonctionnels complets sur les routes metier:
  - accueil, register, login user/admin, logout;
  - formulaire contact (validation + sanitation);
  - protections admin;
  - recherche admin, changement de statut, suppression;
  - purge RGPD;
  - messagerie (acces, envoi, lecture, protection d'acces).

Commandes pour lancer les tests:
- `php artisan test`
- `./composer test`
- `php artisan test tests/Feature/WebRoutesTest.php`
- `php artisan test --filter=test_admin_can_login_with_unique_credentials`

Verification complementaire:
- `php -l routes/web.php`
- `php artisan route:list`

Resultat de la derniere execution:
- `30 passed (107 assertions)`

## 10) Documentation technique
- documentation projet: `docs/Documentation-PHPDoc.md`
- generation Doxygen:
  - installation: `sudo apt-get install doxygen graphviz`
  - generation: `doxygen Doxyfile`
  - sortie: `docs/doxygen/html/index.html`

## 11) Points d'evaluation BTS couverts
- analyse du besoin et formalisation des fonctionnalites;
- conception et implementation d'un service web complet;
- gestion des acces (utilisateur vs admin);
- prise en compte securite et RGPD;
- qualite logicielle: tests de base + documentation technique.
