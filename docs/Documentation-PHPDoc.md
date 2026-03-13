# Documentation PHP / DocBlock - Projet JMI 56

## 1) Objectif
Cette documentation suit les principes vus dans:
- `2.1C6-DocumentationPHP.pdf`
- `2.1C6-DocBlock.pdf`

Le but est de:
- documenter le code PHP avec des DocBlocks standards;
- faciliter la maintenance;
- préparer une génération automatique avec PHPDocumentor.

## 2) Rappels DocBlock (synthèse)
Un DocBlock est un commentaire spécial écrit au format:

```php
/**
 * Résumé court.
 *
 * Description optionnelle.
 * @param type $nom Description
 * @return type Description
 */
```

Balises principales:
- `@param`: décrit un paramètre de fonction/méthode.
- `@return`: décrit la valeur de retour.
- `@var`: décrit une propriété/variable.
- `@throws`: décrit une exception possible.
- `@package`: groupe logique de classes/fichiers.
- `@see`: référence vers un autre élément.
- `@deprecated`: indique qu'un élément est obsolète.

## 3) Installation de PHPDocumentor
Option Composer:

```bash
composer require --dev phpdocumentor/phpdocumentor
```

Option PHAR (Linux):

```bash
sudo apt install php-pear graphviz
wget https://phpdoc.org/phpDocumentor.phar
sudo mv phpDocumentor.phar /usr/local/bin/phpdoc
sudo chown root:root /usr/local/bin/phpdoc
sudo chmod +x /usr/local/bin/phpdoc
```

## 4) Génération de la documentation
Depuis la racine du projet:

```bash
mkdir -p docs/api
phpdoc -d app,routes -t docs/api --ignore "vendor/,storage/,bootstrap/cache/,tests/"
```

Explication:
- `-d`: répertoires sources à documenter.
- `-t`: dossier de sortie.
- `--ignore`: éléments exclus de la génération.

## 5) Périmètre documenté du projet
Le projet contient principalement:
- des routes Laravel dans `routes/web.php`;
- des vues Blade dans `resources/views`;
- des migrations dans `database/migrations`;
- des styles dans `resources/css/app.css`.

## 6) Fonctionnement métier (résumé)
### 6.1 Authentification
- Connexion admin: identifiant/mot de passe admin uniques (`ADMIN_USERNAME` / `ADMIN_PASSWORD`, via `.env` ou valeurs par défaut).
- Connexion utilisateur: email + mot de passe (table `users`).
- Seul l'admin accède aux routes `/admin*`.

### 6.2 Gestion des demandes client
- Une demande est créée via le formulaire de contact (`name`, `phone`, `message`).
- Statuts possibles: `pending`, `in_progress`, `done`.
- L'admin peut filtrer par statut, rechercher, modifier le statut et supprimer.

### 6.3 Sécurité et conformité
- Validation Laravel sur tous les formulaires sensibles.
- Nettoyage des champs texte du formulaire de contact (`strip_tags`, `trim`).
- Purge RGPD automatique des demandes anciennes (constante `GDPR_RETENTION_DAYS`).

## 7) Exemple de DocBlock adapté au projet
Exemple pour la fonction de purge RGPD dans `routes/web.php`:

```php
/**
 * Supprime les demandes de contact dépassant la durée de conservation RGPD.
 *
 * @return void
 */
function purgeOldContactRequests(): void
{
    DB::table('contact_requests')
        ->where('created_at', '<', now()->subDays(GDPR_RETENTION_DAYS))
        ->delete();
}
```

Exemple pour une méthode de service (si ajout futur):

```php
/**
 * Met à jour le statut d'une demande.
 *
 * @param int $requestId Identifiant de la demande.
 * @param string $status Nouveau statut (pending|in_progress|done).
 * @return bool True si la mise à jour a réussi.
 * @throws \InvalidArgumentException Si le statut est invalide.
 */
public function updateStatus(int $requestId, string $status): bool
{
    // ...
}
```

## 8) Bonnes pratiques retenues
- Toujours documenter les fonctions non triviales.
- Garder un résumé d'une ligne + balises utiles.
- Rester cohérent dans les types (`string`, `int`, `bool`, `void`).
- Mettre à jour les DocBlocks en même temps que le code.

## 9) Vérification rapide avant rendu
- `php -l routes/web.php`
- `php artisan route:list`
- génération `phpdoc` sans erreur
- présence du dossier `docs/api`

## 10) Livrables
- Documentation technique: `docs/Documentation-PHPDoc.md`
- Documentation générée (si commande exécutée): `docs/api/`
