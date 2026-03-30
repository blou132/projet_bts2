# Guide Examinateur - JMI 56

## 1) Lancement rapide (5 minutes)
Depuis la racine du projet:

```bash
cp .env.example .env
./scripts/setup-exam.sh
php artisan serve
```

Ouvrir: `http://127.0.0.1:8000`
- Mentions legales: `http://127.0.0.1:8000/mentions-legales.html`
- Politique de confidentialite: `http://127.0.0.1:8000/politique-confidentialite.html`

## 2) Comptes de demonstration
- Admin (compte interne sans acces tickets/messages clients):
  - identifiant: `admin`
  - mot de passe: `admin123`
- JMI (support client):
  - identifiant: `client`
  - mot de passe: `client123`
- Utilisateur:
  - email: `user@gmail.com`
  - mot de passe: `123456789`

## 3) Parcours de verification conseille
1. Ouvrir le site public et verifier les sections.
2. Se connecter avec le compte support (`/login`) puis verifier:
   - listing demandes;
   - changement de statut (`En attente`, `En cours`, `Termine`);
   - recherche;
   - suppression.
3. Se connecter avec le compte utilisateur demo.
4. Ouvrir la messagerie (`/messages`) et verifier:
   - conversation liee a une demande;
   - statuts `unread` / `read`.
5. Option controle d'acces:
   - se connecter avec `admin` puis verifier que le compte n'a pas acces aux tickets/messages clients.

## 4) Verification technique rapide
```bash
php artisan test --testsuite=Feature
php artisan route:list
```

## 5) Documentation technique
- PHPDoc / DocBlocks: `docs/Documentation-PHPDoc.md`
- Configuration Doxygen: `Doxyfile`
- Doxygen web (apres lancement du serveur): [http://127.0.0.1:8000/doxygen/index.html](http://127.0.0.1:8000/doxygen/index.html)
