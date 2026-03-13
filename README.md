# JMI 56 - Projet BTS

<p align="center"><img src="public/images/logo-jmi56.png" width="180" alt="JMI 56"></p>

Site vitrine responsive pour un reparateur informatique (Ploermel), avec formulaire de contact et espace d'administration des demandes.

## Objectif du projet
- Proposer une page publique claire (presentation, services, zone d'intervention, contact).
- Permettre le suivi des demandes clients dans une interface admin.
- Appliquer des bases de securite web et de conformite RGPD.

## Fonctionnalites principales
- Page publique:
  - Hero + sections presentation/services/zone/contact/partenaire/mentions legales.
  - Carte Google Maps (fallback iframe si cle absente).
  - Formulaire de demande (nom, telephone, message).
- Espace admin:
  - Liste des demandes par statut: `En attente`, `En cours`, `Termine`.
  - Recherche par nom ou telephone.
  - Changement de statut.
  - Suppression d'une demande.
  - Couleurs de statut plus visibles.
  - Le bouton du statut courant est masque (on ne peut changer que vers les autres statuts).

## Authentification
- Route de connexion: `/login`
- Route de creation de compte utilisateur: `/register`
- Connexion utilisateur classique:
  - `login` = email utilisateur
  - `password` = mot de passe du compte
- Connexion admin:
  - `login` = identifiant admin unique
  - `password` = mot de passe admin
- Seul l'admin a acces aux routes `/admin*`.

### Identifiants admin
- Par defaut:
  - identifiant: `admin`
  - mot de passe: `admin123`
- Recommande en production via `.env`:
  - `ADMIN_USERNAME=...`
  - `ADMIN_PASSWORD=...`

## Securite et conformite
- Validation serveur sur les formulaires (Laravel `validate`).
- Nettoyage des champs texte du contact (`trim`, `strip_tags`) pour limiter les injections.
- Purge RGPD automatique des demandes anciennes:
  - constante `GDPR_RETENTION_DAYS` dans `routes/web.php`
  - suppression des demandes depassant la duree de conservation.

## Stack technique
- Laravel 12
- PHP 8.3
- MySQL (sessions + demandes + utilisateurs)
- Vite
- CSS personnalise

## Installation rapide
1. Installer les dependances PHP: `composer install`
2. Copier l'environnement: `cp .env.example .env`
3. Configurer MySQL dans `.env`
4. Generer la cle Laravel: `php artisan key:generate`
5. Migrer la base: `php artisan migrate`
6. Lancer le serveur: `php artisan serve`

## Assets front
- Installer les dependances front: `npm install`
- Dev: `npm run dev`
- Build: `npm run build`

## Carte Google Maps
- Ajouter la cle API dans `.env`:
  - `GOOGLE_MAPS_KEY=...`
- Si la cle est absente, la carte fallback iframe est affichee.

## Documentation projet
- Documentation PHPDoc/DocBlock:
  - `docs/Documentation-PHPDoc.md`
- Generation automatique de documentation (optionnel):
  - `phpdoc -d app,routes -t docs/api --ignore "vendor/,storage/,bootstrap/cache/,tests/"`

## Notes pour l'evaluation
- Le projet montre:
  - integration front + back Laravel
  - gestion de statut metier
  - authentification differenciee user/admin
  - prise en compte RGPD et securite de base
  - documentation technique exploitable (DocBlock/PHPDoc)
