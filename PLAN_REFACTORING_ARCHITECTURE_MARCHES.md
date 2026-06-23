# Plan de refactoring architecture Achats / Marches

## Architecture cible

Flux metier unique :

```text
Categories -> Articles -> Marches -> Fournisseur attributaire -> Bon de commande interne -> Bon de livraison -> Reception -> Stock -> Decompte
```

## Phase 1 - Migration non destructive

Objectif : simplifier les ecrans actifs sans perdre les donnees existantes.

- `categories` devient le referentiel metier principal.
- `articles.categorie_id` devient la seule relation fonctionnelle article -> categorie.
- `bon_commandes.categorie_id` est migre de `marche_categories.id` vers `categories.id`.
- `chef_commandes.categorie_id` suit la meme migration.
- Les anciennes valeurs sont conservees dans :
  - `categories.legacy_marche_category_id`
  - `articles.legacy_marche_category_id`
  - `bon_commandes.legacy_marche_category_id`
  - `chef_commandes.legacy_marche_category_id`
- Les lignes de marche recoivent :
  - `quantite_minimale`
  - `quantite_maximale`
- `quantite_commandee` reste alimentee avec la quantite maximale pour conserver les calculs existants.

## Phase 2 - Code actif simplifie

- Creation de marche :
  - choix de la categorie,
  - chargement automatique des articles actifs de cette categorie,
  - saisie uniquement de la quantite minimale, quantite maximale et TVA.
- Attribution fournisseur :
  - fournisseur renseigne sur le marche,
  - prix HT saisis sur les lignes,
  - calcul HT, TVA et TTC conserve.
- Bons de commande internes :
  - filtrage par `categories.id`,
  - marche actif attribue requis,
  - bon de livraison cree avec fournisseur, articles, prix et TVA du marche.
- Categories et articles :
  - les interfaces ne demandent plus `marche_category_id`,
  - les anciennes colonnes sont remplies automatiquement uniquement si elles existent.
- Receptions :
  - les listes recentes utilisent le flux moderne `receptions`.

## Phase 3 - Suppressions definitives appliquees

La migration `2026_06_07_170000_drop_legacy_category_layers.php` archive les anciennes tables avant suppression physique :

- `archive_categorie_principales`
- `archive_nature_prestations`
- `archive_marche_categories`

Puis elle supprime :

- `categorie_principales`
  - model `CategoriePrincipale`
  - relations Eloquent historiques
- `nature_prestations`
  - model `NaturePrestation`
  - relations et filtres historiques
- `marche_categories`
  - model `MarcheCategory`
  - colonne `marche_category_id`
  - colonne `legacy_marche_category_id`

Les migrations historiques sont conservees dans le depot pour ne pas casser les bases deja migrees et les installations qui rejouent toute la chronologie. La migration finale neutralise ces tables en fin de chaine.

## Phase 4 - Reception legacy a traiter separement

Les tables suivantes restent conservees tant que des scopes et relations les lisent encore dans certains modules historiques :

- `bon_receptions`
- `ligne_receptions`
- `entree_stocks`
- `ligne_entree_stocks`

Le flux moderne actif est `receptions`. La suppression du legacy reception doit etre faite dans une migration separee apres verification des exports PDF et des fonctions de suivi qui lisent encore `bon_receptions`.

## Controle avant suppression

- Exporter les tables historiques.
- Verifier que tous les articles ont un `categorie_id`.
- Verifier que tous les marches ont un `categorie_id` qui existe dans `categories`.
- Verifier que tous les bons internes ont un `categorie_id` qui existe dans `categories`.
- Comparer les compteurs avant/apres :
  - nombre d'articles,
  - nombre de marches,
  - nombre de commandes internes,
  - montants HT/TVA/TTC,
  - mouvements de stock.
- Executer :
  - `php artisan migrate`
  - `npm.cmd run build`
  - `php artisan test`

## Historique

`historique_statut_bcs` est reactive pour :

- creation de marche,
- modification de marche,
- attribution fournisseur,
- annulation.

Les evenements reception/decompte doivent etre raccordes dans une phase suivante sur les controleurs dedies afin de conserver une seule source d'historique metier.
