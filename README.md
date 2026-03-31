# Demo Site - Projet BTS SIO

Projet web Laravel (site vitrine + gestion de demandes + messagerie client/support) prepare pour une demonstration BTS.

## Objectif du README
Ce document donne a l examinateur toutes les etapes pour lancer et verifier le projet rapidement.

## Lancement rapide (recommande)
```bash
cp .env.example .env
./scripts/setup-exam.sh
php artisan serve
```

Ce que fait `./scripts/setup-exam.sh`:
- configure `.env` pour SQLite
- cree la base locale
- lance `migrate:fresh --seed`
- injecte des donnees fictives pretes a tester

## URLs utiles
- Site: `http://127.0.0.1:8000`
- Connexion: `http://127.0.0.1:8000/login`
- Mentions legales: `http://127.0.0.1:8000/mentions-legales.html`
- Politique de confidentialite: `http://127.0.0.1:8000/politique-confidentialite.html`
- Doxygen (apres lancement du serveur): [http://127.0.0.1:8000/doxygen/index.html](http://127.0.0.1:8000/doxygen/index.html)

## Comptes de demonstration
- `admin` / `admin123` : compte interne (sans acces tickets/messages clients)
- `client` / `client123` : compte support (acces tickets + messagerie)
- `user@gmail.com` / `123456789` : utilisateur demo

## Donnees fictives generees
- 9 demandes de contact (3 `En attente`, 3 `En cours`, 3 `Termine`)
- messages de discussion lies aux demandes utilisateurs
- plusieurs comptes clients fictifs
- 1 demande ancienne (> 365 jours) pour verifier la purge RGPD

Regenerer les donnees:
```bash
php artisan migrate:fresh --seed
```

## Parcours de verification conseille (5 a 10 min)
1. Ouvrir la page d accueil et verifier la navigation, le formulaire de contact et les pages legales.
2. Se connecter avec `client` puis verifier les tickets (`En attente`, `En cours`, `Termine`), la recherche et la suppression.
3. Ouvrir la messagerie avec `client` et verifier les conversations liees aux demandes.
4. Se connecter avec `user@gmail.com` et verifier l echange de messages avec le support.
5. Se connecter avec `admin` et verifier que ce compte ne peut pas acceder aux tickets/messages clients.

## Tests automatiques
```bash
php artisan test
```

## Fichiers importants
- `routes/web.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/views/welcome.blade.php`
- `resources/views/admin/index.blade.php`
- `resources/views/messages/index.blade.php`
- `tests/Feature/WebRoutesTest.php`
- `scripts/setup-exam.sh`

## Documentation complementaire
- Guide examinateur: `docs/Guide-Examinateur.md`
- Documentation PHPDoc/DocBlock: `docs/Documentation-PHPDoc.md`
- Diagrammes UML (classe, sequence, utilisation): `docs/Diagrammes-UML.md`
