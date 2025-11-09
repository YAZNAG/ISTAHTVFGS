<?php
// app/Http/Controllers/BonCommandeController.php
namespace App\Http\Controllers;

use App\Http\Resources\ListBonCommandesExport;
use App\Http\Resources\ShowBonCommandeResource;
use App\Models\BonCommande;
use App\Models\Fournisseur;
use App\Models\CategoriePrincipale;
use App\Models\NaturePrestation;
use App\Models\Article;
use App\Models\HistoriqueStatutBc;
use App\Models\BonCommandeArticle;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Log;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf as FacadesPdf;

class BonCommandeController extends Controller
{
    // Ajouter cette propriété pour les taux de TVA
   
    private $tauxTVA = [0, 5, 7, 10, 14, 20];


 public function index(Request $request)
{
    // CORRECTION : Charger toutes les relations nécessaires pour les articles avec leurs détails
    $query = BonCommande::with([
        'categorie',
        'fournisseur', 
        'articles.article.categorie',
        'articles.article.categoriePrincipale',
        'articles.article.naturePrestation',
        'articles.article.images'
    ])->orderBy('created_at', 'desc');

    if ($request->has('statut') && $request->statut) {
        $query->where('statut', $request->statut);
    }

    if ($request->has('categorie_id') && $request->categorie_id) {
        $query->where('categorie_id', $request->categorie_id);
    }

    if ($request->has('date_limite') && $request->date_limite) {
        $query->where('date_limite_reception', '>=', $request->date_limite);
    }

    if ($request->has('reference') && $request->reference) {
        $query->where('reference', 'like', '%' . $request->reference . '%');
    }

    $bonCommandes = $query->paginate(10);

    // CORRECTION : Calculer les totaux pour chaque bon de commande
    $bonCommandes->getCollection()->transform(function ($bonCommande) {
        // Calculer le total TTC en additionnant les montants TTC des articles
        $bonCommande->total_ttc = $bonCommande->articles->sum('montant_ttc');
        $bonCommande->total_ht = $bonCommande->articles->sum('montant_ht');
        $bonCommande->total_tva = $bonCommande->articles->sum('montant_tva');
        
        // CORRECTION : Ajouter le comptage des articles pour les statistiques
        $bonCommande->nombre_articles = $bonCommande->articles->count();
        
        return $bonCommande;
    });

    $articles = Article::with(['categorie', 'categoriePrincipale', 'naturePrestation'])
        ->withNonExists()
        ->where('est_actif', true)
        ->get();

    return Inertia::render('Achats/BonCommandes/Index', [
        'marches' => $bonCommandes,
        'categoriesPrincipales' => CategoriePrincipale::where('est_actif', true)->get(),
        'categories' => Categorie::where('est_actif', true)->get(),
        'naturesPrestation' => NaturePrestation::where('est_actif', true)->get(),
        'articles' => $articles,
        'articlesGroupes' => $articles->groupBy('categorie_principale_id'),
        'fournisseurs' => Fournisseur::where('est_actif', true)->get(),
        'tauxTVA' => $this->tauxTVA,
        'filters' => $request->only(['statut', 'categorie_principale_id', 'date_limite', 'reference']),
        'stats' => $this->getStats(),
    ]);
}

    
//    public function index(Request $request)
// {
//     // CORRECTION : Charger toutes les relations nécessaires pour les articles
//     $query = BonCommande::with([
//         'categoriePrincipale', 
//         'naturePrestation', 
//         'fournisseur', 
//         'articles.article.categorie',
//         'articles.article.categoriePrincipale',
//         'articles.article.naturePrestation',
//         'articles.article.images'
//     ])->orderBy('created_at', 'desc');

//     if ($request->has('statut') && $request->statut) {
//         $query->where('statut', $request->statut);
//     }

//     if ($request->has('categorie_principale_id') && $request->categorie_principale_id) {
//         $query->where('categorie_principale_id', $request->categorie_principale_id);
//     }

//     if ($request->has('date_limite') && $request->date_limite) {
//         $query->where('date_limite_reception', '<=', $request->date_limite);
//     }

//     if ($request->has('reference') && $request->reference) {
//         $query->where('reference', 'like', '%' . $request->reference . '%');
//     }

//     $bonCommandes = $query->paginate(10);

//     $articles = Article::with(['categorie', 'categoriePrincipale', 'naturePrestation'])
//         ->where('est_actif', true)
//         ->get();

//     // CORRECTION : Ajouter les statistiques et les filtres
//     return Inertia::render('Achats/BonCommandes/Index', [
//         'bonCommandes' => $bonCommandes,
//         'categoriesPrincipales' => CategoriePrincipale::where('est_actif', true)->get(),
//         'naturesPrestation' => NaturePrestation::where('est_actif', true)->get(),
//         'articles' => $articles,
//         'articlesGroupes' => $articles->groupBy('categorie_principale_id'),
//         'fournisseurs' => Fournisseur::where('est_actif', true)->get(),
//         'tauxTVA' => $this->tauxTVA,
//         'filters' => $request->only(['statut', 'categorie_principale_id', 'date_limite', 'reference']),
//         'stats' => $this->getStats(),
//     ]);
// }

/**
 * Récupère les statistiques des bons de commande
 */
private function getStats()
{
    return [
        'cree' => BonCommande::where('statut', 'cree')->count(),
        'attente_livraison' => BonCommande::where('statut', 'attente_livraison')->count(),
        'livre_completement' => BonCommande::where('statut', 'livre_completement')->count(),
        'livre_partiellement' => BonCommande::where('statut', 'livre_partiellement')->count(),
        'montant_total' => BonCommande::whereIn('statut', ['attente_livraison', 'livre_completement', 'livre_partiellement'])
            ->withSum('articles as total_ttc_sum', 'montant_ttc')
            ->get()
            ->sum('total_ttc_sum'),
    ];
}
    public function create()
    {
        $articles = Article::with(['categorie', 'categoriePrincipale', 'naturePrestation'])
            ->where('est_actif', true)
            ->get();

        return Inertia::render('Achats/BonCommandes/Create', [
            'categoriesPrincipales' => CategoriePrincipale::where('est_actif', true)->get(),
            'naturesPrestation' => NaturePrestation::where('est_actif', true)->get(),
            'articles' => $articles,
            'articlesGroupes' => $articles->groupBy('categorie_principale_id'),
            'tauxTVA' => $this->tauxTVA,
        ]);
    }

      public function store(Request $request)
    {
        Log::info('Données reçues pour création bon de commande:', $request->all());
        
        $request->validate([
            'reference' => 'required|unique:bon_commandes',
            'objet' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'categorie_id' => 'required|exists:categories,id',
            'date_mise_ligne' => 'required|date',
            'date_limite_reception' => 'required|date|after_or_equal:date_mise_ligne',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite_commandee' => 'required|numeric|min:0.01',
            'articles.*.taux_tva' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $piecesJointes = [];
            if ($request->hasFile('pieces_jointes')) {
                foreach ($request->file('pieces_jointes') as $file) {
                    $path = $file->store('pieces_jointes/bon_commandes', 'public');
                    $piecesJointes[] = $path;
                }
            }

            $bonCommande = BonCommande::create([
                'reference' => $request->reference,
                'objet' => $request->objet,
                'description' => $request->description,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'date_mise_ligne' => $request->date_mise_ligne,
                'categorie_id' => $request->categorie_id,
                'date_limite_reception' => $request->date_limite_reception,
                'pieces_jointes' => $piecesJointes,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'statut' => 'cree',
            ]);

            foreach ($request->articles as $article) {
                // Utiliser create() sur le modèle pivot
                BonCommandeArticle::create([
                    'bon_commande_id' => $bonCommande->id,
                    'article_id' => $article['article_id'],
                    'quantite_commandee' => $article['quantite_commandee'],
                    'taux_tva' => $article['taux_tva'],
                    'prix_unitaire_ht' => null,
                    'montant_ht' => null,
                    'montant_tva' => null,
                    'montant_ttc' => null,
                ]);
            }

            // try {
            //     HistoriqueStatutBc::create([
            //         'bon_commande_id' => $bonCommande->id,
            //         'ancien_statut' => 'nouveau',
            //         'nouveau_statut' => 'cree',
            //         'raison' => 'Création du bon de commande',
            //         'changed_by' => auth()->id(),
            //     ]);
            // } catch (\Exception $e) {
            //     Log::error('Erreur création historique:', ['error' => $e->getMessage()]);
            // }
        });

        return redirect()->route('bon-commandes.index')
            ->with('success', 'Le Marché créé avec succès.');
    }

 public function updateStatut(Request $request, BonCommande $bonCommande)
{
    Log::info('Début updateStatut', $request->all());

    // CORRECTION : Charger les articles avant la validation
    $bonCommande->load('articles');

    if ($request->statut === 'annule') {
        $request->validate([
            'statut' => 'required|in:annule',
            'raison' => 'required|string|min:20',
        ]);

        if (!$bonCommande->statut == 'cree' || !$bonCommande->statut == 'attente_livraison') {
            return back()->withErrors([
                'statut' => 'Impossible d\'annuler un bon de commande avec le statut "' . $bonCommande->statut_formate . '".'
            ]);
        }

    } else {
        // CORRECTION : Validation améliorée pour les prix
        $request->validate([
            'statut' => 'required|in:attente_livraison,livre_completement,livre_partiellement',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'articles' => 'required|array|min:1',
            'articles.*.id' => 'required|exists:bon_commande_articles,id',
            'articles.*.prix_unitaire_ht' => 'required|numeric|min:0',
        ], [
            'articles.*.prix_unitaire_ht.required' => 'Le prix unitaire HT est obligatoire pour tous les articles',
            'articles.*.prix_unitaire_ht.min' => 'Le prix unitaire HT doit être supérieur à 0',
        ]);
    }

    try {
        DB::beginTransaction();

        $ancienStatut = $bonCommande->statut;

        if ($request->statut === 'annule') {
            $bonCommande->update([
                'statut' => BonCommande::STATUT_ANNULE,
                'annule_at' => now(),
                'reason_annulation' => $request->raison,
            ]);
                
            // CORRECTION : Réinitialiser les prix via la relation articles
            // foreach ($bonCommande->articles as $articlePivot) {
            //     $articlePivot->update([
            //         'prix_unitaire_ht' => null,
            //         'montant_ht' => null,
            //         'montant_tva' => null,
            //         'montant_ttc' => null,
            //     ]);
            // }
        } else {
            // METTRE À JOUR LES PRIX
            $this->updateArticlesPrix($bonCommande, $request);
            
            $bonCommande->update([
                'statut' => BonCommande::STATUT_ATTENTE_LIVRAISON,
                'fournisseur_id' => $request->fournisseur_id,
            ]);
        }

        // CORRECTION : Créer l'historique avec raison
        // HistoriqueStatutBc::create([
        //     'bon_commande_id' => $bonCommande->id,
        //     'ancien_statut' => $ancienStatut,
        //     'nouveau_statut' => $request->statut,
        //     'raison' => $request->raison ?? ($request->statut === 'annule' ? 'Annulation du bon de commande' : 'Mise à jour du statut avec attribution fournisseur'),
        //     'changed_by' => auth()->id(),
        // ]);

        DB::commit();

        Log::info('Statut et prix mis à jour avec succès', [
            'bon_commande_id' => $bonCommande->id,
            'statut' => $request->statut,
            'fournisseur_id' => $request->fournisseur_id
        ]);

        return back()->with('success', 'Statut et prix mis à jour avec succès.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur lors de la mise à jour du statut et des prix', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->withErrors([
            'error' => 'Une erreur est survenue lors de la mise à jour: ' . $e->getMessage()
        ]);
    }
}
  /**
     * Met à jour les prix des articles
     *//**
 * Met à jour les prix des articles
 */
private function updateArticlesPrix(BonCommande $bonCommande, Request $request)
{
    Log::info('Mise à jour des prix des articles', [
        'bon_commande_id' => $bonCommande->id,
        'articles_data' => $request->articles
    ]);

    // CORRECTION : Vérifier que les articles appartiennent bien au bon de commande
    $validatedArticles = [];
    
    foreach ($request->articles as $articleData) {

        // Trouver l'enregistrement dans la table pivot
        $articlePivot = BonCommandeArticle::where('id', $articleData['id'])
            ->where('bon_commande_id', $bonCommande->id)
            ->first();
        
        if (!$articlePivot) {
            Log::warning('Article pivot non trouvé ou n\'appartient pas au bon de commande', [
                'article_id' => $articleData['id'],
                'bon_commande_id' => $bonCommande->id
            ]);
            continue;
        }

        Article::onlyNonExists()->find($articlePivot->article_id)?->update([
            'in_marche' => true
        ]);
        $prixUnitaireHT = floatval($articleData['prix_unitaire_ht']);
        $quantite = floatval($articlePivot->quantite_commandee);
        $tauxTVA = floatval($articlePivot->taux_tva);
        
        $montantHT = $prixUnitaireHT * $quantite;
        $montantTVA = $montantHT * ($tauxTVA / 100);
        $montantTTC = $montantHT + $montantTVA;

        Log::info('Mise à jour article', [
            'article_pivot_id' => $articlePivot->id,
            'article_id' => $articlePivot->article_id,
            'prix_unitaire_ht' => $prixUnitaireHT,
            'quantite' => $quantite,
            'taux_tva' => $tauxTVA,
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC
        ]);

        $articlePivot->update([
            'prix_unitaire_ht' => $prixUnitaireHT,
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);

        $validatedArticles[] = $articlePivot;
    }

    // CORRECTION : Vérifier que tous les articles ont été traités
    if (count($validatedArticles) !== count($request->articles)) {
        Log::warning('Certains articles n\'ont pas pu être mis à jour', [
            'attendus' => count($request->articles),
            'traités' => count($validatedArticles)
        ]);
    }

    Log::info('Mise à jour des prix terminée', [
        'articles_traités' => count($validatedArticles)
    ]);
}

   

public function show(BonCommande $bonCommande)
{
    // CORRECTION : Charger toutes les relations nécessaires
    $bonCommande->load([
        'categoriePrincipale', 
        'naturePrestation', 
        'fournisseur', 
        'articles.article',
    ]);

    // return response()->json(ShowBonCommandeResource::make($bonCommande));
    // CORRECTION : Logger pour débogage
    Log::info('Affichage du bon de commande', [
        'bon_commande_id' => $bonCommande->id,
        'nombre_articles' => $bonCommande->articles->count(),
        'articles' => $bonCommande->articles->map(function($article) {
            return [
                'id' => $article->id,
                'article_id' => $article->article_id,
                'designation' => $article->article->designation ?? 'N/A',
                'quantite_commandee' => $article->quantite_commandee,
                'taux_tva' => $article->taux_tva,
            ];
        })
    ]);

    return Inertia::render('Achats/BonCommandes/Show', [
        'marche' => ShowBonCommandeResource::make($bonCommande),
        'fournisseurs' => Fournisseur::where('est_actif', true)->get(),
    ]);
}

/**
 * Méthode de débogage pour vérifier les données des articles
 * (À supprimer en production)
 */
public function debugBonCommande(BonCommande $bonCommande)
{
    $bonCommande->load([
        'articles.article.categoriePrincipale',
        'articles.article.naturePrestation',
        'articles.article.categorie'
    ]);

    $debugData = [
        'bon_commande' => [
            'id' => $bonCommande->id,
            'reference' => $bonCommande->reference,
            'statut' => $bonCommande->statut,
        ],
        'articles' => $bonCommande->articles->map(function($articlePivot) {
            return [
                'pivot_id' => $articlePivot->id,
                'article_id' => $articlePivot->article_id,
                'quantite_commandee' => $articlePivot->quantite_commandee,
                'taux_tva' => $articlePivot->taux_tva,
                'prix_unitaire_ht' => $articlePivot->prix_unitaire_ht,
                'article' => $articlePivot->article ? [
                    'id' => $articlePivot->article->id,
                    'reference' => $articlePivot->article->reference,
                    'designation' => $articlePivot->article->designation,
                    'unite_mesure' => $articlePivot->article->unite_mesure,
                ] : null
            ];
        })
    ];

    return response()->json($debugData);
}

private function processAnnulation(BonCommande $bonCommande, string $raison)
{
    \Log::info('Début processAnnulation simplifiée', ['bon_commande_id' => $bonCommande->id]);

    try {
        // 1. Mettre à jour le statut et retirer le fournisseur
        $bonCommande->update([
            'statut' => 'annule',
            'fournisseur_id' => null, // Retirer le fournisseur attribué
        ]);

        \Log::info('Bon de commande mis à jour avec succès', [
            'bon_commande_id' => $bonCommande->id,
            'nouveau_statut' => 'annule',
            'fournisseur_id' => null
        ]);

        // 2. PAS de modification des articles - ils n'ont pas de champs de prix
        // Les quantités et taux TVA sont conservés pour l'historique

        // 3. Journaliser l'annulation
        \Log::info("Bon de commande annulé avec succès", [
            'bon_commande_id' => $bonCommande->id,
            'reference' => $bonCommande->reference,
            'raison' => $raison,
            'user_id' => auth()->id(),
            'note' => 'Les articles n\'ont pas été modifiés (pas de champs de prix)'
        ]);

        return true;

    } catch (\Exception $e) {
        \Log::error('Erreur lors de l\'annulation', [
            'bon_commande_id' => $bonCommande->id,
            'erreur' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}



private function processStatutLivraison(BonCommande $bonCommande, Request $request)
{
    // Mettre à jour les prix des articles
    foreach ($request->articles as $articleData) {
        $article = $bonCommande->articles()->find($articleData['id']);
        if ($article) {
            $montantHT = $articleData['prix_unitaire_ht'] * $article->quantite_commandee;
            
            // UTILISER LE TAUX TVA ORIGINAL DE L'ARTICLE, PAS CELUI ENVOYÉ
            $tauxTVA = $article->taux_tva;
            $montantTVA = $montantHT * ($tauxTVA / 100);
            
            $article->update([
                'prix_unitaire_ht' => $articleData['prix_unitaire_ht'],
                // NE PAS METTRE À JOUR LE TAUX TVA - garder celui de la création
                'montant_ht' => $montantHT,
                'montant_tva' => $montantTVA,
                'montant_ttc' => $montantHT + $montantTVA,
            ]);
        }
    }

    // Mettre à jour le bon de commande
    $bonCommande->update([
        'statut' => $request->statut,
        'fournisseur_id' => $request->fournisseur_id,
    ]);
}
/**
 * Envoie des notifications pour l'annulation
 */
private function notifyAnnulation(BonCommande $bonCommande, string $raison)
{
    // Exemple d'envoi de notification par email
    try {
        $usersToNotify = \App\Models\User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'responsable_achats']);
        })->get();

        foreach ($usersToNotify as $user) {
            // Utiliser votre système de notification
            $user->notify(new \App\Notifications\BonCommandeAnnule(
                $bonCommande, 
                $raison, 
                auth()->user()
            ));
        }
    } catch (\Exception $e) {
        \Log::error('Erreur lors de l\'envoi des notifications d\'annulation: ' . $e->getMessage());
    }
}


// public function downloadPdf(BonCommande $bon_commande)
// {
//     dd($bon_commande);
//     // Vérifier que le statut n'est pas "cree" ou "annule"
//     if ($bon_commande->statut === 'cree' || $bon_commande->statut === 'annule') {
//         abort(403, 'PDF non disponible pour ce statut');
//     }

//     $data = [
//         'bonCommande' => $bon_commande,
//         'articles' => $bon_commande->articles,
//         'fournisseur' => $bon_commande->fournisseur,
//     ];

//     $pdf = PDF::loadView('bon-commandes.pdf', $data);
    
//     return $pdf->download("bon-commande-{$bon_commande->reference}.pdf");
// }
/**
 * Méthode spécifique pour annuler sans passer par updateStatut
 * (Optionnel - pour une annulation directe)
 */
public function annuler(Request $request, BonCommande $bonCommande)
{
    $request->validate([
        'raison' => 'required|string|min:20',
    ]);

    // Vérifier que le bon de commande peut être annulé
    if (!in_array($bonCommande->statut, ['cree', 'attente_livraison'])) {
        return back()->withErrors([
            'statut' => 'Impossible d\'annuler un bon de commande déjà livré.'
        ]);
    }

    DB::transaction(function () use ($request, $bonCommande) {
        $ancienStatut = $bonCommande->statut;
        
        $this->processAnnulation($bonCommande, $request->raison);

        // Historique spécifique pour l'annulation
        HistoriqueStatutBc::create([
            'bon_commande_id' => $bonCommande->id,
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => 'annule',
            'raison' => $request->raison,
            'changed_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    return redirect()->route('bon-commandes.index')
        ->with('success', 'Bon de commande annulé avec succès.');
}


private function generateAndStorePdf(BonCommande $bonCommande)
{
    try {
        // Vérifier si la classe PDF existe
        if (!class_exists('PDF')) {
            \Log::warning('Classe PDF non disponible');
            return null;
        }
        
        $bonCommande->load([
            'articles.article.categoriePrincipale', 
            'articles.article.naturePrestation',
            'fournisseur', 
            'categoriePrincipale', 
            'naturePrestation'
        ]);
        
        $pdf = PDF::loadView('pdf.bon-commande', compact('bonCommande'));
        
        // Stocker le PDF
        $fileName = "bon-commande-{$bonCommande->reference}.pdf";
        Storage::disk('public')->put("bon-commandes-pdf/{$fileName}", $pdf->output());
        
        return $fileName;
    } catch (\Exception $e) {
        \Log::error('Erreur génération PDF', [
            'bon_commande_id' => $bonCommande->id,
            'erreur' => $e->getMessage()
        ]);
        return null;
    }
}

public function storeFournisseur(Request $request)
{
    $request->validate([
        'nom' => 'required|string|max:255',
        'raison_sociale' => 'nullable|string|max:255',
        'contact' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'adresse' => 'nullable|string',
        'ville' => 'nullable|string|max:255',
        'ice' => 'nullable|string|max:50',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        DB::beginTransaction();

        $fournisseurData = [
            'nom' => $request->nom,
            'raison_sociale' => $request->raison_sociale,
            'contact' => $request->contact,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'ice' => $request->ice,
            'est_actif' => true,
            'notes' => $request->notes,
        ];

        $fournisseur = Fournisseur::create($fournisseurData);

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            $fournisseur->uploadLogo($request->file('logo'));
        }

        DB::commit();

        return response()->json($fournisseur);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erreur création fournisseur', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Erreur lors de la création du fournisseur'], 500);
    }
}
// Ajouter une méthode pour mettre à jour le logo
public function updateFournisseurLogo(Request $request, Fournisseur $fournisseur)
{
    $request->validate([
        'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($request->hasFile('logo')) {
        $path = $fournisseur->uploadLogo($request->file('logo'));
        return response()->json(['logo_url' => $fournisseur->logo_url]);
    }

    return response()->json(['error' => 'Aucun fichier uploadé'], 400);
}



 public function generatePdf(BonCommande $bonCommande)
    {
        if ($bonCommande->statut === 'cree' || $bonCommande->statut === 'annule') {
            abort(403, 'PDF non disponible pour ce statut');
        }

        $bonCommande->load([
            'articles.article.categoriePrincipale', 
            'articles.article.naturePrestation',
            'fournisseur', 
            'categoriePrincipale', 
            'naturePrestation'
        ]);

        $data = [
            'bonCommande' => $bonCommande,
            'articles' => $bonCommande->articles,
            'fournisseur' => $bonCommande->fournisseur,
        ];

        
        $cleanReference = preg_replace('/[\/\\\\]/', '-', $bonCommande->reference);
        $fileName = "bon-commande-{$cleanReference}.pdf";
        
        // return view('pdf.bon-commande.bon-commande', $data);

        return FacadesPdf::view('pdf.bon-commande.bon-commande', $data)
            ->headerView('pdf.H')
            ->footerView('pdf.bon-commande.bon-commande-footer')
            ->margins(45, 5, 40,5)
            ->format(Format::A4)
            ->download($fileName);
    }


    function export(Request $request) 
    {
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();

        $query = BonCommande::with(['categoriePrincipale', 'naturePrestation', 'fournisseur'])->orderBy('created_at', 'desc');

        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;
        
        if ($request->end_date) {
            $data = $query->whereBetween('created_at', [$startDate, $endDate])->get();
        } else {
            $data = $query->whereDate('created_at', '>=', $startDate)->get();
        }

        $data = ListBonCommandesExport::collection($data)->toArray($request);

        
        return Pdf::view('pdf.list-bon-commandes', [
            'bonCommandes' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->format(Format::A4)
            ->headerView('pdf.H')
            ->footerView('pdf.F')
            ->margins(45, 5, 35, 5)
            ->download('list-bon-commandes.pdf')
            ;
    }
 
}