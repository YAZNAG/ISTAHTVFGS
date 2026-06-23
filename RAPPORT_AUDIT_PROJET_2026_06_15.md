# Rapport d'audit projet ISTAHT / ISPITSRK

Date d'audit : 2026-06-15  
Projet audite : `C:\ISTHT2026\ispitsrk.ma`  
Stack : Laravel 12, Vue 3, Inertia.js, Vite, Tailwind CSS, Spatie Permission, MySQL/MariaDB

## 1. Synthese executive

L'application couvre un perimetre ERP large pour un etablissement ISTAHT : categories, articles, marches, fournisseurs, bons de commande internes, bons de livraison, receptions, stock, demandes, bons de sortie, fiches techniques, inventaires, rapports, utilisateurs et roles.

Le projet est fonctionnellement riche et bien avance. Les derniers travaux ont simplifie l'architecture metier des achats autour du flux :

```text
Categories -> Articles -> Marches -> Fournisseur -> Bon de commande interne -> Bon de livraison -> Reception -> Stock -> Decompte
```

Note globale audit : **7.3 / 10**

Points forts :

- Couverture metier large et coherente avec achats/stock/restauration.
- 170 routes Laravel declarees.
- Permissions Spatie appliquees sur la majorite des modules.
- Build frontend valide.
- Tests Laravel existants passent.
- Refonte recente des categories/articles/marches dans le bon sens metier.

Points faibles :

- Configuration MySQL locale bloquante : `.env` pointe sur `127.0.0.1:3308`, mais aucun serveur ne repond sur ce port.
- `SESSION_DRIVER`, `CACHE_STORE` et `QUEUE_CONNECTION` utilisent la base, donc une base indisponible bloque aussi certaines commandes artisan.
- Anciennes structures encore presentes dans l'historique de migrations et certains modules legacy.
- Couverture de tests limitee aux tests Laravel/Breeze de base, peu de tests metier.
- Certains workflows restent longs et meritiens d'etre verrouilles par tests de bout en bout.

## 2. Verification technique executee

| Verification | Resultat | Commentaire |
|---|---:|---|
| `php artisan route:list` | OK | 170 routes detectees. |
| `php artisan test` | OK | 25 tests passes, 62 assertions. |
| `npm.cmd run build` | OK | Build Vite produit avec avertissements de taille de chunks. |
| `php artisan optimize:clear` | Partiel | Config nettoyee, cache a d'abord echoue car MySQL `127.0.0.1:3308` refusait la connexion. |
| Connexion DB via Laravel | KO | Port configure `3308` indisponible. |
| MySQL detecte sur machine | Partiel | Processus MySQL actif sur `3306`, mais acces root sans mot de passe refuse. |
| Lancement local | OK partiel | Serveur actif sur `http://127.0.0.1:8001/login`, page login en HTTP 200. |

## 3. Etat de configuration locale

Configuration `.env` detectee :

```text
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3308
DB_DATABASE=optizaworks_gs
DB_USERNAME=root
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Correction locale appliquee pendant l'audit :

- `SESSION_DRIVER` est passe de `database` a `file`.
- `CACHE_STORE` est passe de `database` a `file`.
- `QUEUE_CONNECTION` est passe de `database` a `sync`.

Cette correction permet d'afficher la page login sans dependre de MySQL pour les sessions/cache. Risque restant : tant que MySQL n'est pas accessible sur `3308` avec les identifiants du `.env`, l'authentification et les pages metier qui lisent la base peuvent encore echouer.

## 4. Notes par module

| Module | Note /10 | Etat | Justification courte |
|---|---:|---|---|
| Authentification | 8.0 | Bon | Breeze standard, tests auth OK, login/register/reset couverts. |
| Roles et permissions | 7.5 | Bon | Spatie bien integre, mais roles non-admin a configurer finement. |
| Dashboard | 7.5 | Bon | KPI et graphiques modernes, depend fortement de requetes DB complexes. |
| Categories | 8.5 | Tres bon | Module simplifie, suppression ajoutee, couleur sans icone, UX allegee. |
| Articles | 8.0 | Bon | Relation categorie claire, filtres utiles, gestion stock visible. |
| Import bordereau | 7.0 | Correct | Service dedie, utile, mais necessite tests sur fichiers reels. |
| Fournisseurs | 7.5 | Bon | CRUD, statut, export, validations completes, soft delete. |
| Marches / bons de commande marche | 7.0 | Correct | Coeur metier present, mais workflow encore dense et historique a consolider. |
| Attribution fournisseur | 7.0 | Correct | Prix HT, HT/TVA/TTC et fournisseur geres, wizard a stabiliser par tests. |
| Bons de commande internes | 7.5 | Bon | Creation, validation, rejet, PDF et notifications presents. |
| Bons de livraison | 7.0 | Correct | Module utile, prix/articles relies au marche, controle livraison a renforcer. |
| Receptions modernes | 6.8 | Correct | Flux actif present vers stock, mais coexistence legacy a clarifier. |
| Stock articles | 7.5 | Bon | Vue claire stock/rupture/seuil, mouvements en entree/sortie. |
| Entrees stock legacy | 6.0 | Moyen | Encore present mais doit rester secondaire face au flux reception moderne. |
| Sorties stock | 7.2 | Correct | Lie aux demandes et bons de sortie, validation presente. |
| Retours stock | 6.5 | Correct | Module present, perimetre moins central, tests metier a ajouter. |
| Demandes internes | 7.5 | Bon | Workflow demande/soumission/validation/rejet coherent. |
| Bons de sortie | 7.0 | Correct | Validation/PDF presents, lie au stock. |
| Fiches techniques | 7.0 | Correct | Pedagogique/collectivite, ingredients, duplication et PDF. |
| Menus collectivite | 6.5 | Moyen | Module present, donnees et tests a renforcer. |
| Restaurants | 6.0 | Moyen | CRUD present, mais module peu connecte au flux principal. |
| Inventaires | 7.0 | Correct | Creation, saisie stock reel, finalisation, PDF, deverrouillage. |
| Rapports / exports PDF | 6.8 | Correct | Beaucoup d'exports, mais controle qualite PDF a systematiser. |
| Notifications | 6.8 | Correct | Notifications metier presentes, suivi lu/non lu basique. |
| UI/UX globale | 8.0 | Bon | Layout ERP modernise, sidebar, badges, composants; quelques pages restent chargees. |

## 5. Notes par workflow metier

| Workflow | Note /10 | Evaluation |
|---|---:|---|
| Connexion -> dashboard | 8.0 | Fluide si DB disponible, auth testee. |
| Creation categorie -> articles rattaches | 8.5 | Architecture simplifiee et alignement metier fort. |
| Import bordereau -> categories/articles | 7.0 | Gain important, mais besoin de tests fichiers limites/erreurs. |
| Creation marche par categorie | 7.5 | Bon alignement avec le besoin alimentaire, articles charges automatiquement. |
| Attribution marche -> fournisseur -> prix HT | 7.0 | Fonctionnel, mais doit etre verifie en E2E sur cas reels. |
| Marche -> bon de commande interne | 7.0 | Logique presente, depend du bon parametrage marche actif/fournisseur. |
| Bon commande interne -> bon de livraison | 7.0 | Flux coherent, recuperation fournisseur/articles/prix attendue. |
| Bon de livraison -> reception | 7.0 | Flux moderne actif, mais legacy reception a nettoyer. |
| Reception -> mouvement stock entree | 7.5 | Bonne logique d'alimentation stock par reception. |
| Demande interne -> validation -> sortie stock | 7.5 | Workflow complet avec validation et bon de sortie. |
| Fiche technique -> demande | 7.0 | Utile pour restauration/formation, necessite plus de tests metier. |
| Sortie stock -> mouvement stock sortie | 7.0 | Fonctionnel, a auditer sur gestion des ruptures. |
| Inventaire -> ecarts -> finalisation | 7.0 | Present, mais controle d'ecarts et verrouillage a renforcer. |
| Marche -> livraisons -> receptions -> decompte | 6.8 | Decompte present, mais source unique a consolider. |
| Roles -> permissions -> menus/actions | 7.5 | Bon socle Spatie, mais profils non-admin a regler. |
| PDF/export administratif | 6.8 | Large couverture, mais verification visuelle systematique absente. |

## 6. Audit architecture metier

### Architecture cible

```text
Categorie
  -> Articles
    -> Marche
      -> Fournisseur attributaire
        -> Bon de commande interne
          -> Bon de livraison
            -> Reception
              -> Mouvement stock entree
                -> Stock
                  -> Decompte
```

Evaluation : **8 / 10**

L'architecture cible est bonne et correspond au besoin metier reel. La suppression des couches `categorie_principales`, `nature_prestations` et `marche_categories` simplifie fortement le modele. Il reste a surveiller les migrations historiques et les traces legacy dans certains documents/code.

### Donnees et compatibilite

Evaluation : **7 / 10**

Les migrations recentes prevoient une migration non destructive avec colonnes legacy/archive. C'est une bonne approche. La prochaine priorite est de faire un audit DB apres reconnexion MySQL pour confirmer :

- nombre de categories,
- nombre d'articles,
- marches par categorie,
- lignes marche,
- bons internes,
- receptions,
- mouvements stock,
- decomptes.

## 7. Audit technique

### Backend Laravel

Note : **7.5 / 10**

Points positifs :

- Controllers separes par domaine.
- Resources Inertia/PHP presentes.
- Middleware permissions explicites.
- Seeders de permissions organises.
- Tests Laravel passent.

Risques :

- Plusieurs controllers contiennent beaucoup de logique metier directement.
- Tests metier insuffisants pour achats, stock, receptions et decompte.
- Quelques routes resource peuvent exposer des actions non prioritaires si les methodes ne sont pas completes.

### Frontend Vue/Inertia

Note : **7.8 / 10**

Points positifs :

- Pages Inertia structurees par module.
- Layout ERP moderne.
- Build Vite valide.
- Composants UI reutilisables presents.

Risques :

- Certains chunks JS sont volumineux (`app` proche de 930 kB minifie).
- Plusieurs pages restent denses et doivent etre simplifiees progressivement.
- Controle responsive/visuel a automatiser avec screenshots Playwright.

### Base de donnees

Note : **6.5 / 10**

Points positifs :

- Migrations nombreuses et domaine bien couvert.
- Relations principales coherentes.
- Migrations recentes de simplification prevues.

Risques :

- Configuration locale DB actuellement bloquante.
- Legacy reception encore present.
- Cache/session/queue en base compliquent le lancement quand MySQL est indisponible.

## 8. Risques prioritaires

| Priorite | Risque | Impact | Recommandation |
|---:|---|---|---|
| P1 | MySQL local indisponible sur `3308` | Bloque login/pages DB | Corriger `DB_PORT` ou demarrer MySQL sur `3308`. |
| P1 | Cache/session/queue en base | Commandes artisan fragiles sans DB | Corrige localement pendant l'audit : `file/file/sync`. |
| P1 | Tests metier absents | Regressions possibles | Ajouter tests feature pour categories, articles, marches, reception, stock. |
| P2 | Legacy reception | Risque double logique stock | Finaliser choix `receptions` et archiver/supprimer ancien flux apres audit. |
| P2 | Chunks frontend volumineux | Performance initiale | Code-splitting manuel et lazy loading modules lourds. |
| P2 | Roles non-admin | Utilisateurs sans droits utiles | Creer profils type : manager, magasinier, formateur, validateur. |
| P3 | Exports PDF peu testes | Documents administratifs fragiles | Ajouter verification visuelle et tests de generation. |

## 9. Priorites recommandees

### Court terme

1. Corriger la connexion MySQL locale.
2. Mettre `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` en developpement si la DB n'est pas garantie.
3. Verifier login admin et parcours categories/articles/marches dans le navigateur.
4. Ajouter des tests pour `categories.destroy` et creation/modification categorie.
5. Ajouter un test creation marche par categorie avec chargement articles.

### Moyen terme

1. Tests E2E du flux complet achat :
   `categorie -> articles -> marche -> attribution fournisseur -> bon interne -> BL -> reception -> stock`.
2. Nettoyage definitif du legacy reception.
3. Decouper les controllers lourds en services metier.
4. Optimiser les chunks frontend.
5. Stabiliser les permissions par role metier.

### Long terme

1. Dashboard qualite avec alertes marche/stock/decompte.
2. Historique statut global pour creation, modification, validation, reception et decompte.
3. Audit PDF automatique.
4. Journalisation metier detaillee pour tracabilite administrative.

## 10. Logins de test detectes

Source : `database/seeders/AdminUserSeeder.php` et `database/seeders/UsersSeeder.php`.

Mot de passe seeders : **`123456789`**

| Role attendu | Email | Mot de passe | Remarque |
|---|---|---|---|
| Admin / Manager | `admin@email.com` | `123456789` | Cree par `AdminUserSeeder`, roles `manager` et `admin`. |
| Test simple | `test@user.com` | `123456789` | Cree par `UsersSeeder`, role non garanti. |
| Utilisateur | `mstitou@tourisme.gov.ma` | `123456789` | Role a confirmer en base active. |
| Utilisateur | `elghali.ali2015@gmail.com` | `123456789` | Role a confirmer en base active. |
| Utilisateur | `meryemjanati20@gmail.com` | `123456789` | Role a confirmer en base active. |

Important : ces logins sont ceux declares dans les seeders. Je n'ai pas pu confirmer les roles dans la base active parce que la connexion MySQL locale est bloquee sur `127.0.0.1:3308`.

## 11. Conclusion

Le projet est solide fonctionnellement et proche d'un ERP metier exploitable. La meilleure note revient aux modules `Categories`, `Articles`, `Permissions`, `Dashboard` et au flux simplifie achats/marches.

La priorite absolue n'est pas le code mais l'environnement : remettre la base MySQL en ligne sur le port configure ou corriger `.env`. Une fois la DB accessible, il faut faire un audit dynamique des donnees et ajouter les tests metier des workflows critiques.
