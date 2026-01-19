# JMI 56 - Projet BTS

<p align="center"><img src="public/images/logo-jmi56.png" width="180" alt="JMI 56"></p>

Site moderne et responsive pour un reparateur informatique (Ploermel). Le formulaire de contact alimente un espace admin pour suivre les demandes.

## Fonctionnalites
- Page publique avec sections (presentation, services, zone, contact)
- Carte Google Maps centree sur Ploermel avec rayon de 30 km (fallback iframe)
- Connexion admin simple
- Gestion des demandes : statuts (Demandes, En cours, Termine), recherche, suppression

## Stack
- Laravel 12, PHP 8.3
- MySQL (sessions + demandes)
- Vite (optionnel pour les assets)

## Installation rapide
1. Installer les dependances : `composer install`
2. Copier l'environnement : `cp .env.example .env`
3. Configurer la base MySQL dans `.env`
4. Generer la cle : `php artisan key:generate`
5. Migrer la base : `php artisan migrate`
6. Lancer le serveur : `php artisan serve`

## Admin
- URL : `/login`
- Identifiants : `admin` / `admin123`
- Acces aux onglets : Demandes, En cours, Termine

## Carte Google Maps
- Ajouter une cle dans `.env` : `GOOGLE_MAPS_KEY=...`
- Sans cle, un fallback iframe est affiche

## Assets front (optionnel)
- Dev : `npm install` puis `npm run dev`
- Build : `npm run build`
