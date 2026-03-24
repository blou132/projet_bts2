# Guide Examinateur - JMI 56

## 1) Lancement rapide (5 minutes)
Depuis la racine du projet:

```bash
cp .env.example .env
./scripts/setup-exam.sh
php artisan serve
```

Ouvrir: `http://127.0.0.1:8000`

## 2) Comptes de demonstration
- Admin:
  - identifiant: `admin`
  - mot de passe: `admin123`
- Utilisateur:
  - email: `demo@jmi56.local`
  - mot de passe: `demo12345`

## 3) Parcours de verification conseille
1. Ouvrir le site public et verifier les sections.
2. Se connecter en admin (`/login`) puis verifier:
   - listing demandes;
   - changement de statut (`En attente`, `En cours`, `Termine`);
   - recherche;
   - suppression.
3. Se connecter avec le compte utilisateur demo.
4. Ouvrir la messagerie (`/messages`) et verifier:
   - conversation liee a une demande;
   - statuts `unread` / `read`.

## 4) Verification technique rapide
```bash
php artisan test --testsuite=Feature
php artisan route:list
```

## 5) Documentation technique
- PHPDoc / DocBlocks: `docs/Documentation-PHPDoc.md`
- Configuration Doxygen: `Doxyfile`
