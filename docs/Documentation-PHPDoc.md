# Documentation du projet JMI 56 avec Doxygen

## 1. Objectif
Cette documentation explique comment documenter le code PHP du projet avec des DocBlocks et generer une documentation HTML avec Doxygen.

## 2. Principe des DocBlocks
Les DocBlocks sont des commentaires structures places au-dessus des classes, proprietes et methodes.

Format de base:

```php
/**
 * Resume court.
 *
 * Description detaillee (optionnelle).
 * @param type $param Description
 * @return type Description
 */
```

Balises importantes:
- `@param`
- `@return`
- `@var`
- `@throws`
- `@see`
- `@deprecated`

## 3. Exemple adapte au projet
Exemple reel dans `routes/web.php`:

```php
/**
 * Supprime les demandes de contact depassant la duree de conservation RGPD.
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

## 4. Installation de Doxygen
Sous Linux:

```bash
sudo apt-get update
sudo apt-get install doxygen graphviz
```

## 5. Configuration Doxygen du projet
Un fichier `Doxyfile` est fourni a la racine du projet avec:
- `INPUT = app routes`
- `FILE_PATTERNS = *.php`
- `RECURSIVE = YES`
- exclusion des dossiers non utiles (`vendor`, `storage`, `tests`, etc.)
- sortie dans `docs/doxygen/html`

## 6. Generation de la documentation
Depuis la racine du projet:

```bash
doxygen Doxyfile
```

Resultat attendu:
- index principal: `docs/doxygen/html/index.html`

## 7. Verifications avant rendu
- `php -l routes/web.php`
- `php artisan route:list`
- `doxygen Doxyfile`
- ouverture de `docs/doxygen/html/index.html`

## 8. Ce que l'examinateur doit voir
- le code contient des DocBlocks sur les elements metier importants;
- la doc est generee automatiquement avec Doxygen;
- la structure du projet est comprise rapidement (routes, modele user, logique admin, RGPD).

## 9. Conclusion
Doxygen permet d'obtenir une documentation claire, navigable en HTML et facile a maintenir. Combine avec des DocBlocks propres, c'est une base solide pour le projet BTS.
