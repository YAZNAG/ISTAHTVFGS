<?php

use App\Http\Controllers\ApiFicheController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleStockController;
use App\Http\Controllers\BonCommandeController;
use App\Http\Controllers\BonLivraisonController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\BonReceptionController;
use App\Http\Controllers\BonSortieController;
use App\Http\Controllers\CardexController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ChefCommandeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DecompteController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\EntreeStockController;
use App\Http\Controllers\FicheTechniqueController;
use App\Http\Controllers\InventaireController;
use App\Http\Controllers\MenuCollectiviteController;
use App\Http\Controllers\SortieStockController;
use App\Http\Controllers\MouvementStockController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RapportsController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReturnStockController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

Route::get('/test', function () {

    $chunks = \App\Models\Fournisseur::all()->chunk(20);

    return Pdf::view('pdf.test', ['chunks' => $chunks])
    ->headerView('pdf.H')
    ->footerView('pdf.F')
    // ->format(Format::A4);
    ;
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Route API pour les détails des commandes
    // Route::get('api/commandes/{id}/details', [BonReceptionController::class, 'getCommandeDetails'])
    //     ->name('bon-receptions.commande-details');
    
    // Route pour les statistiques
    // Route::get('api/bon-receptions/stats', [BonReceptionController::class, 'stats'])
    //     ->name('bon-receptions.stats');
    // Routes pour les articles
    Route::get('/articles/export/pdf', [ArticleController::class, 'exportPdf'])->name('articles.export.pdf');
    Route::get('/articles/export/excel', [ArticleController::class, 'exportExcel'])->name('articles.export.excel');
    Route::resource('articles', ArticleController::class);
    Route::get('/categories', [CategorieController::class, 'index'])->name('categories.index');
    Route::get('/categories/export/pdf', [CategorieController::class, 'exportPdf'])->name('categories.export.pdf');
    Route::get('/categories/export/excel', [CategorieController::class, 'exportExcel'])->name('categories.export.excel');
    Route::post('/categories/store', [CategorieController::class, 'store'])->name('categories.store');
    Route::get('/categories/{categorie}/edit', [CategorieController::class, 'edit'])->name('categories.edit');
    Route::get('/categories/{categorie}', [CategorieController::class, 'show'])->name('categories.show');
    Route::put('/categories/{categorie}', [CategorieController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{categorie}', [CategorieController::class, 'destroy'])->name('categories.destroy');


    // Module Achats
    Route::prefix('achats')->group(function () {
        // Bons de commande
        Route::get('marches-export/pdf', [BonCommandeController::class, 'exportPdf'])->name('bon-commandes.export.pdf');
        Route::get('marches-export/excel', [BonCommandeController::class, 'exportExcel'])->name('bon-commandes.export.excel');
        Route::resource('marches', BonCommandeController::class)
        ->parameters(['marches' => 'bonCommande'])
        ->names([
            'index' => 'bon-commandes.index',
            'create' => 'bon-commandes.create',
            'show' => 'bon-commandes.show',
            'store' => 'bon-commandes.store',
            'edit' => 'bon-commandes.edit',
            'update' => 'bon-commandes.update',
            'destroy' => 'bon-commandes.destroy',
        ]);
        Route::post('marches/{bonCommande}/statut', [BonCommandeController::class, 'updateStatut'])->name('bon-commandes.statut');
        Route::post('marches/{bonCommande}/annuler', [BonCommandeController::class, 'annuler'])->name('bon-commandes.annuler');
        Route::get('marches/{bonCommande}/pdf', [BonCommandeController::class, 'generatePdf'])->name('bon-commandes.pdf');
        Route::get('marches/{bonCommande}/modify', [BonCommandeController::class, 'modify'])->name('bon-commandes.modify');
        Route::put('marches/{bonCommande}/modify', [BonCommandeController::class, 'updateModify'])->name('bon-commandes.updateModify');

        // Route::get('/marches/{bonCommande}/debug', [BonCommandeController::class, 'debugBonCommande'])->name('bon-commandes.debug');

      // Routes pour les bons de réception
// Route::resource('bon-receptions', BonReceptionController::class);
// Route::get('bon-receptions/create/{bonCommande}', [BonReceptionController::class, 'create'])
//     ->name('bon-receptions.create-from-commande');
// Route::get('bon-receptions/{bonReception}/details', [BonReceptionController::class, 'showDetails'])
//     ->name('bon-receptions.show-details');
// Route::get('bon-receptions/{bonReception}/download-pdf', [BonReceptionController::class, 'downloadPdf'])
//     ->name('bon-receptions.download-pdf');
// Route::get('bon-receptions/{bonReception}/download-bon-livraison', [BonReceptionController::class, 'downloadBonLivraison'])
//     ->name('bon-receptions.download-bon-livraison');
// Route::get('bon-receptions/{bonReception}/download-facture', [BonReceptionController::class, 'downloadFacture'])
//     ->name('bon-receptions.download-facture');
// Route::get('bon-receptions/commande-details/{id}', [BonReceptionController::class, 'getCommandeDetails'])
//     ->name('bon-receptions.commande-details');

    

//     Route::get('/debug-commande/{id}', function ($id) {
//     $commande = \Marche::with(['articles.article'])->find($id);
    
//     if (!$commande) {
//         return response()->json(['error' => 'Commande non trouvée'], 404);
//     }

//     $debugData = [
//         'commande_id' => $commande->id,
//         'reference' => $commande->reference,
//         'articles_count' => $commande->articles->count(),
//         'articles' => $commande->articles->map(function ($article) {
//             return [
//                 'article_id' => $article->article_id,
//                 'designation' => $article->article->designation,
//                 'quantite_commandee' => $article->quantite_commandee,
//                 'prix_unitaire_ht' => $article->prix_unitaire_ht,
//                 'taux_tva' => $article->taux_tva,
//                 'montant_ht' => $article->montant_ht,
//                 'montant_tva' => $article->montant_tva,
//                 'montant_ttc' => $article->montant_ttc,
//             ];
//         })
//     ];

//     return response()->json($debugData);
// });
        // Fournisseurs
        Route::get('fournisseurs-export/pdf', [FournisseurController::class, 'exportPdf'])->name('fournisseurs.export.pdf');
        Route::get('fournisseurs-export/excel', [FournisseurController::class, 'exportExcel'])->name('fournisseurs.export.excel');
        Route::get('fournisseurs', [FournisseurController::class, 'index'])->name('fournisseurs.index');
        Route::post('fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store');
        Route::get('fournisseurs/{fournisseur}', [FournisseurController::class, 'show'])->name('fournisseurs.show');
        Route::put('fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update');
        Route::delete('fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy');
        Route::patch('fournisseurs/{fournisseur}/toggle-statut', [FournisseurController::class, 'toggleStatut'])->name('fournisseurs.toggle-statut');
        // Route::get('fournisseurs/stats', [FournisseurController::class, 'stats'])->name('fournisseurs.stats');
    });

    // Routes pour la Gestion du Stock
    // Route::prefix('stock')->group(function () {
    //     Route::get('/entrees', [EntreeStockController::class, 'index'])->name('entree-stocks.index');
        // Routes des Sorties de Stock
        // Route::get('/sorties', [SortieStockController::class, 'index'])->name('sortie-stocks.index');
        // Route::get('/sorties/create', [SortieStockController::class, 'create'])->name('sortie-stocks.create');
        // Route::post('/sorties', [SortieStockController::class, 'store'])->name('sortie-stocks.store');
        // Route::get('/sorties/{sortieStock}', [SortieStockController::class, 'show'])->name('sortie-stocks.show');
        // Route::get('/sorties/{sortieStock}/edit', [SortieStockController::class, 'edit'])->name('sortie-stocks.edit');
        // Route::put('/sorties/{sortieStock}', [SortieStockController::class, 'update'])->name('sortie-stocks.update');
        // Route::delete('/sorties/{sortieStock}', [SortieStockController::class, 'destroy'])->name('sortie-stocks.destroy');
        // Route::get('/sorties/{sortieStock}/download-pdf', [SortieStockController::class, 'downloadPdf'])->name('sortie-stocks.download-pdf');

        // Routes des Mouvements de Stock
        // Route::get('/mouvements', [MouvementStockController::class, 'index'])->name('mouvement-stocks.index');
        // Route::get('/mouvements/export', [MouvementStockController::class, 'export'])->name('mouvement-stocks.export');
        // Route::get('/mouvements/stats', [MouvementStockController::class, 'stats'])->name('mouvement-stocks.stats');
        // Route::get('/mouvements/article/{article}', [MouvementStockController::class, 'byArticle'])->name('mouvement-stocks.by-article');
    // });



    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');


    Route::get('/demandes', [DemandeController::class, 'index'])->name('demandes.index');
    Route::get('/mes-demandes', [DemandeController::class, 'mesDemandes'])->name('demandes.mes-demandes');
    Route::get('/demandes/create', [DemandeController::class, 'create'])->name('demandes.create');
    Route::post('/demandes', [DemandeController::class, 'store'])->name('demandes.store');
    Route::get('/demandes/{demande}', [DemandeController::class, 'show'])->name('demandes.show');
    Route::get('/demandes/{demande}/edit', [DemandeController::class, 'edit'])->name('demandes.edit');
    Route::put('/demandes/{demande}', [DemandeController::class, 'update'])->name('demandes.update');
    Route::delete('/demandes/{demande}', [DemandeController::class, 'destroy'])->name('demandes.destroy');
    
    Route::delete('/demandes/{demande}/annuler', [DemandeController::class, 'cancel'])->name('demandes.cancel');
    Route::get('/demandes/{demande}/approve', [DemandeController::class, 'showApprove'])->name('demandes.show.approve');
    Route::put('/demandes/{demande}/approve', [DemandeController::class, 'approve'])->name('demandes.approve');
    Route::put('/demandes/{demande}/reject', [DemandeController::class, 'reject'])->name('demandes.reject');
    Route::patch('/demandes/{demande}/submit', [DemandeController::class, 'submit'])->name('demandes.submit');

    

    ##### Fiches Techniques ####
    Route::get('/fiches-techniques/collectivite', [FicheTechniqueController::class, 'collectivite'])->name('fiches-techniques.collectivite');
    Route::get('/fiches-techniques/pedagogique', [FicheTechniqueController::class, 'pedagogique'])->name('fiches-techniques.pedagogique');
    Route::get('/fiches-techniques/create', [FicheTechniqueController::class, 'create'])->name('fiches-techniques.create');
    Route::post('/fiches-techniques', [FicheTechniqueController::class, 'store'])->name('fiches-techniques.store');
    Route::get('/fiches-techniques/{fiche}', [FicheTechniqueController::class, 'show'])->name('fiches-techniques.show');
    Route::get('/fiches-techniques/{fiche}/edit', [FicheTechniqueController::class, 'edit'])->name('fiches-techniques.edit');
    Route::put('/fiches-techniques/{fiche}', [FicheTechniqueController::class, 'update'])->name('fiches-techniques.update');
    Route::delete('/fiches-techniques/{fiche}', [FicheTechniqueController::class, 'destroy'])->name('fiches-techniques.destroy');
    Route::get('/fiches-techniques/{fiche}/export', [FicheTechniqueController::class, 'export'])->name('fiches-techniques.export');

    Route::get('/fiches-techniques/{fiche}/duplicate', [FicheTechniqueController::class, 'duplicate'])->name('fiches-techniques.duplicate');


    ##### Sortie Stock #####
    // Route::get('/sorties/{sortieStock}/approve', [SortieStockController::class, 'showApprove'])->name('sortie-stocks.show.approve');
    // Route::put('/sorties/{sortieStock}/approve', [SortieStockController::class, 'approve'])->name('sortie-stocks.approve');
    // Route::put('/sorties/{sortieStock}/reject', [SortieStockController::class, 'reject'])->name('sortie-stocks.reject');
    // Route::put('/sorties/{sortieStock}/livrer', [SortieStockController::class, 'livrer'])->name('sortie-stocks.livrer');

    #### Chef Bon Commandes ####
    Route::get('/chef-commandes', [ChefCommandeController::class, 'index'])->name('chef-commandes.index');
    Route::get('/chef-commandes/create', [ChefCommandeController::class, 'create'])->name('chef-commandes.create');
    Route::post('/chef-commandes', [ChefCommandeController::class, 'store'])->name('chef-commandes.store');
    Route::get('/chef-commandes/{chefCommande}', [ChefCommandeController::class, 'show'])->name('chef-commandes.show');
    Route::get('/chef-commandes/{chefCommande}/edit', [ChefCommandeController::class, 'edit'])->name('chef-commandes.edit');
    Route::put('/chef-commandes/{chefCommande}', [ChefCommandeController::class, 'update'])->name('chef-commandes.update');
    Route::delete('/chef-commandes/{chefCommande}', [ChefCommandeController::class, 'destroy'])->name('chef-commandes.destroy');
    Route::patch('/chef-commandes/{chefCommande}/submit', [ChefCommandeController::class, 'submit'])->name('chef-commandes.submit');
    Route::patch('/chef-commandes/{chefCommande}/cancel', [ChefCommandeController::class, 'cancel'])->name('chef-commandes.cancel');
    Route::get('/chef-commandes/{chefCommande}/approve', [ChefCommandeController::class, 'showApprove'])->name('chef-commandes.showApprove');
    Route::put('/chef-commandes/{chefCommande}/approve', [ChefCommandeController::class, 'approve'])->name('chef-commandes.approve');
    Route::put('/chef-commandes/{chefCommande}/reject', [ChefCommandeController::class, 'reject'])->name('chef-commandes.reject');
    Route::get('/chef-commandes/{chefCommande}/pdf', [ChefCommandeController::class, 'generatePdf'])->name('chef-commandes.download-pdf');
    

    #### Bon Livraison ####
    Route::get('/bon-livraisons', [BonLivraisonController::class, 'index'])->name('bon-livraisons.index');
    // Route::get('/bon-livraisons/create', [BonLivraisonController::class, 'create'])->name('bon-livraisons.create');
    // Route::post('/bon-livraisons', [BonLivraisonController::class, 'store'])->name('bon-livraisons.store');
    Route::get('/bon-livraisons/{bonLivraison}/edit', [BonLivraisonController::class, 'edit'])->name('bon-livraisons.edit');
    Route::put('/bon-livraisons/{bonLivraison}', [BonLivraisonController::class, 'update'])->name('bon-livraisons.update');
    Route::get('/bon-livraisons/{bonLivraison}', [BonLivraisonController::class, 'show'])->name('bon-livraisons.show');
    // Route::delete('/bon-livraisons/{bonLivraison}', [BonLivraisonController::class, 'destroy'])->name('bon-livraisons.destroy');
    Route::get('/bon-livraisons/{bonLivraison}/pdf', [BonLivraisonController::class, 'export'])->name('bon-livraisons.pdf');
    // Route::get('/bon-livraisons/{bonLivraison}/debug', [BonLivraisonController::class, 'debugBonLivraison'])->name('bon-livraisons.debug');

    #### Bon Reception ####
    Route::get('/bon-receptions', [ReceptionController::class, 'index'])->name('bon-receptions.index');
    Route::get('/bon-receptions/create', [ReceptionController::class, 'create'])->name('bon-receptions.create');
    Route::post('/bon-receptions', [ReceptionController::class, 'store'])->name('bon-receptions.store');
    Route::get('/bon-receptions/{reception}', [ReceptionController::class, 'show'])->name('bon-receptions.show');
    Route::delete('/bon-receptions/{reception}', [ReceptionController::class, 'destroy'])->name('bon-receptions.destroy');
    Route::get('/bon-receptions/{reception}/pdf', [ReceptionController::class, 'export'])->name('bon-receptions.pdf');

    #### Bon Sortie ####
    Route::get('/bon-sorties', [BonSortieController::class, 'index'])->name('bon-sorties.index');
    Route::get('/bon-sorties/{bonSortie}', [BonSortieController::class, 'show'])->name('bon-sorties.show');
    Route::get('/bon-sorties/{bonSortie}/showApprove', [BonSortieController::class, 'showApprove'])->name('bon-sorties.showApprove');
    Route::put('/bon-sorties/{bonSortie}/approve', [BonSortieController::class, 'approve'])->name('bon-sorties.approve');
    Route::get('/bon-sorties/{bonSortie}/reject', [BonSortieController::class, 'reject'])->name('bon-sorties.reject');

    Route::get('/bon-sorties/{bonSortie}/download-pdf', [BonSortieController::class, 'downloadPdf'])->name('bon-sorties.download-pdf');


    #### Notifications ####
    Route::get('/notification/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/notification/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');


    #### Stock Entrees ####
    Route::get('stock/entrees', [EntreeStockController::class, 'index'])->name('entree-stocks.index');
    Route::get('entree-stocks/export/create', [EntreeStockController::class, 'createExport'])->name('entree-stocks.export.create');
    Route::get('entree-stocks/export', [EntreeStockController::class, 'export'])->name('entree-stocks.export');


    #### Stock Sorties ####
    Route::get('stock/sorties', [SortieStockController::class, 'index'])->name('sortie-stocks.index');
    Route::get('sortie-stocks/export/create', [SortieStockController::class, 'createExport'])->name('sortie-stocks.export.create');
    Route::get('sortie-stocks/export', [SortieStockController::class, 'export'])->name('sortie-stocks.export');
    

    #### Articles Stock ####
    Route::get('/stock/articles', [ArticleStockController::class, 'index'])->name('articles-stocks.index');
    Route::get('/stock/articles/export', [ArticleStockController::class, 'export'])->name('articles-stocks.export');
    
    Route::get('/rapports', [RapportsController::class, 'index'])->name('rapports.index');

    Route::get('/cardex/create', [CardexController::class, 'create'])->name('cardex.create');
    
    #### Restaurants ####
    Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('/restaurants/create', [RestaurantController::class, 'create'])->name('restaurants.create');
    Route::post('/restaurants', [RestaurantController::class, 'store'])->name('restaurants.store');
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');
    Route::get('/restaurants/{restaurant}/edit', [RestaurantController::class, 'edit'])->name('restaurants.edit');
    Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update'])->name('restaurants.update');
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy'])->name('restaurants.destroy');
    // Route::get('/restaurants/{restaurant}/export', [RestaurantController::class, 'export'])->name('restaurants.export');

    #### Roles & Permissions ####
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    #### Returns ####
    Route::get('stock/returns', [ReturnStockController::class, 'index'])->name('returns.index');
    Route::get('stock/returns/create', [ReturnStockController::class, 'create'])->name('returns.create');
    Route::post('stock/returns', [ReturnStockController::class, 'store'])->name('returns.store');
    Route::get('stock/returns/{returnStock}/edit', [ReturnStockController::class, 'edit'])->name('returns.edit');
    Route::put('stock/returns/{returnStock}', [ReturnStockController::class, 'update'])->name('returns.update');
    Route::get('stock/returns/{returnStock}', [ReturnStockController::class, 'show'])->name('returns.show');
    Route::delete('stock/returns/{returnStock}', [ReturnStockController::class, 'destroy'])->name('returns.destroy');

    
    ##### Exports #####
    Route::get('fournisseurs/export', [FournisseurController::class, 'export'])->name('fournisseurs.export');

    Route::get('marches/export', [BonCommandeController::class, 'export'])->name('bon-commandes.export');


    Route::get('cardex/{article}', [CardexController::class, 'export'])->name('cardex.export');

    #### Inventaire ####
    Route::get('inventaires', [InventaireController::class, 'index'])->name('inventaires.index');
    Route::post('inventaires', [InventaireController::class, 'store'])->name('inventaires.store');
    Route::get('inventaires/{inventaire}/edit', [InventaireController::class, 'edit'])->name('inventaires.edit');
    Route::get('inventaires/{inventaire}/pdf', [InventaireController::class, 'generatePdf'])->name('inventaires.pdf');
    Route::patch('inventaires/{inventaire}/unlock', [InventaireController::class, 'unlock'])->name('inventaires.unlock');

    Route::patch('/inventaires/{inventaire}/finalise', [InventaireController::class, 'finalize'])->name('inventaires.finalize');
    Route::patch('/inventaires/ligne/{ligne}', [InventaireController::class, 'updateLingne'])->name('inventaires.ligne.update');
    
    #### Decompte
    Route::get('/marches/{bonCommande}/decompte/create', [DecompteController::class, 'create'])->name('decompte.create');
    Route::post('/marches/{bonCommande}/decompte', [DecompteController::class, 'store'])->name('decompte.store');
    Route::get('/marches/{decompte}/download', [DecompteController::class, 'download'])->name('decompte.download-pdf');
    Route::get('/marches/{decompte}/download-excel', [DecompteController::class, 'exportExcel'])->name('decompte.download-excel');
    Route::delete('/marches/decompte/{decompte}', [DecompteController::class, 'destroy'])->name('decompte.destroy');

    #### Menu Collectivite ####
    Route::get('/menu-collectivite', [MenuCollectiviteController::class, 'index'])->name('menus.index');
    Route::get('/menu-collectivite/create', [MenuCollectiviteController::class, 'create'])->name('menus.create');
    Route::post('/menu-collectivite', [MenuCollectiviteController::class, 'store'])->name('menus.store');
    Route::get('/menu-collectivite/{menu}/edit', [MenuCollectiviteController::class, 'edit'])->name('menus.edit');
    Route::put('/menu-collectivite/{menu}', [MenuCollectiviteController::class, 'update'])->name('menus.update');

    Route::get('/menu-collectivite/{menu}/download', [MenuCollectiviteController::class, 'download'])->name('menus.download');
    Route::get('/menu-collectivite/export/create', [MenuCollectiviteController::class, 'createExport'])->name('menus.createExport');
    Route::get('/menu-collectivite/export', [MenuCollectiviteController::class, 'export'])->name('menus.export');


    Route::delete('/menu-collectivite/{MenuCollectivite}', [MenuCollectiviteController::class, 'destroy'])->name('menus.destroy');


    #### API ####
    Route::get('/fiches/type/{type}', [ApiFicheController::class, 'getFicheByType'])->name('fiches.byType');
    
    
});

require __DIR__.'/auth.php';
