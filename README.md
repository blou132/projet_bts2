# JMI 56 - Projet BTS SIO

Projet web Laravel pour la vitrine JMI 56, avec gestion des demandes clients et messagerie.

## Demarrage rapide
```bash
cp .env.example .env
./scripts/setup-exam.sh
php artisan serve
```

URL:
- `http://127.0.0.1:8000`

## Comptes de demo
- `admin` / `admin123` (compte interne, sans acces tickets/messages clients)
- `jmi` / `jmi123` (support client: tickets + messages)
- `exemple@gmail.com` / `123456789` (utilisateur demo)

## Fonctions principales
- formulaire de contact (`nom`, `telephone`, `message`)
- gestion des demandes par statut (`En attente`, `En cours`, `Termine`)
- recherche et suppression des demandes
- messagerie utilisateur <-> support JMI
- filtrage anti-profanite sur les champs texte
- purge RGPD des anciennes demandes

## Tests
```bash
php artisan test --testsuite=Feature
```

## Fichiers utiles
- `routes/web.php`
- `resources/views/welcome.blade.php`
- `resources/views/admin/index.blade.php`
- `resources/views/messages/index.blade.php`
- `resources/css/app.css`
- `tests/Feature/WebRoutesTest.php`
- `scripts/setup-exam.sh`

## Documentation
- guide examinateur: `docs/Guide-Examinateur.md`
- doc technique: `docs/Documentation-PHPDoc.md`
- doxygen (cliquable apres `php artisan serve`): [Ouvrir la doc Doxygen](http://127.0.0.1:8000/doxygen/index.html)
