<?php
// app/Http/Controllers/BonReceptionController.php

namespace App\Http\Controllers;

use App\Http\Resources\ShowBonReceptionResource;
use App\Models\BonReception;
use App\Models\BonCommande;
use App\Models\LigneReception;
use App\Models\MouvementStock;
use App\Models\Article;
use App\Models\User;
use App\Models\Fournisseur; // Ajouter si nécessaire
use App\Models\BonCommandeArticle; // AJOUTER CET IMPORT
use App\Models\EntreeStock;
use App\Models\LigneEntreeStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class BonReceptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            Log::info('Accessing bon-receptions index', ['user_id' => Auth::id()]);

            $query = BonReception::with([
                'bonCommande',
                'fournisseur',
                'responsableReception',
                'lignesReception.article',
                'createdBy'
            ])->orderBy('created_at', 'desc');

            // Filtrage par type de réception
            if ($request->filled('type_reception')) {
                $query->where('type_reception', $request->type_reception);
            }

            // Filtrage par statut
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            // Filtrage par responsable
            if ($request->filled('responsable_reception_id')) {
                $query->where('responsable_reception_id', $request->responsable_reception_id);
            }

            // Recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', '%' . $search . '%')
                      ->orWhereHas('bonCommande', function ($q) use ($search) {
                          $q->where('reference', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('fournisseur', function ($q) use ($search) {
                          $q->where('nom', 'like', '%' . $search . '%')
                            ->orWhere('raison_sociale', 'like', '%' . $search . '%');
                      });
                });
            }

            $bonReceptions = $query->paginate(20)->withQueryString();

            // Commandes en attente de livraison - SIMPLIFIÉ : utiliser seulement le statut
            $commandesEnAttente = BonCommande::whereIn('statut', ['attente_livraison', 'livre_partiellement'])
                ->with(['fournisseur', 'articles.article'])
                ->get()
                ->map(function ($commande) {
                    // Calculer les statistiques de réception basées sur les données existantes
                    $commande->quantite_totale_commandee = $commande->articles->sum('quantite_commandee');
                    
                    // Calculer la quantité totale reçue à partir des réceptions existantes
                    $quantiteTotaleRecue = 0;
                    foreach ($commande->articles as $ligne) {
                        $quantiteRecue = LigneReception::whereHas('bonReception', function($query) use ($commande) {
                                $query->where('bon_commande_id', $commande->id);
                            })
                            ->where('article_id', $ligne->article_id)
                            ->sum('quantite_receptionnee');
                        
                        $ligne->quantite_deja_recue = $quantiteRecue;
                        $ligne->reste_a_recevoir = max(0, $ligne->quantite_commandee - $quantiteRecue);
                        $quantiteTotaleRecue += $quantiteRecue;
                    }
                    
                    $commande->quantite_totale_recue = $quantiteTotaleRecue;
                    $commande->reste_a_recevoir = $commande->quantite_totale_commandee - $quantiteTotaleRecue;
                    $commande->pourcentage_recu = $commande->quantite_totale_commandee > 0 ? 
                        round(($quantiteTotaleRecue / $commande->quantite_totale_commandee) * 100, 2) : 0;
                    
                    return $commande;
                });

            // Statistiques
            $stats = [
                'total' => BonReception::count(),
                'complets' => BonReception::complets()->count(),
                'partiels' => BonReception::partiels()->count(),
                'avec_bon_livraison' => BonReception::avecBonLivraison()->count(),
                'avec_facture' => BonReception::avecFacture()->count(),
            ];

            // Responsables pour les filtres
             $magasiniers = User::where('role', 'MAGASINIER')
    ->where('status', 1) // Si vous avez un champ status pour les utilisateurs actifs
    ->orderBy('name')
    ->get(['id', 'name', 'email']);


            Log::info('Returning bon-receptions data', [
                'count' => $bonReceptions->count(),
                'commandes_en_attente' => $commandesEnAttente->count(),
                'stats' => $stats
            ]);

            return inertia('Achats/BonReceptions/Index', [
                'bonReceptions' => $bonReceptions,
                'commandesEnAttente' => $commandesEnAttente,
                'stats' => $stats,
                'magasiniers' => $magasiniers,
                'filters' => $request->only(['type_reception', 'statut', 'responsable_reception_id', 'search'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error in BonReceptionController@index: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Erreur lors du chargement des bons de réception: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
   


    public function show(BonReception $bonReception)
    {

        try {
            $bonReception->load([
                'bonCommande',
                'fournisseur',
                'responsableReception',
                'lignesReception.article',
                'createdBy'
            ]);

            // Ajouter les informations de comparaison avec la commande
            if ($bonReception->bonCommande) {
                $bonReception->bonCommande->load(['articles.article']);
                
                $bonReception->comparaison_articles = $bonReception->bonCommande->articles->map(function ($ligneCommande) use ($bonReception) {
                    $quantiteRecueDansCetteReception = $bonReception->lignesReception
                        ->where('article_id', $ligneCommande->article_id)
                        ->sum('quantite_receptionnee');
                    
                    $quantiteTotaleRecue = LigneReception::whereHas('bonReception', function($query) use ($bonReception) {
                            $query->where('bon_commande_id', $bonReception->bon_commande_id);
                        })
                        ->where('article_id', $ligneCommande->article_id)
                        ->sum('quantite_receptionnee');
                    
                    return [
                        'article_id' => $ligneCommande->article_id,
                        'article_designation' => $ligneCommande->article->designation,
                        'quantite_commandee' => $ligneCommande->quantite_commandee,
                        'quantite_recue_cette_reception' => $quantiteRecueDansCetteReception,
                        'quantite_totale_recue' => $quantiteTotaleRecue,
                        'reste_a_recevoir' => $ligneCommande->quantite_commandee - $quantiteTotaleRecue,
                        'est_complet' => $quantiteTotaleRecue >= $ligneCommande->quantite_commandee
                    ];
                });
            }

            return inertia('Achats/BonReceptions/Show', [
                'bonReception' => $bonReception,
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing bon reception: ' . $e->getMessage());
            
            return redirect()->route('bon-receptions.index')
                ->with('error', 'Erreur lors du chargement du bon de réception: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BonReception $bonReception)
    {
        try {
            $bonReception->load([
                'bonCommande',
                'fournisseur',
                'responsableReception',
                'lignesReception.article',
                'createdBy'
            ]);

            $responsables = User::where('is_active', true)->get();

            return inertia('Achats/BonReceptions/Edit', [
                'bonReception' => $bonReception,
                'responsables' => $responsables,
            ]);

        } catch (\Exception $e) {
            Log::error('Error editing bon reception: ' . $e->getMessage());
            
            return redirect()->route('bon-receptions.index')
                ->with('error', 'Erreur lors du chargement de l\'édition: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BonReception $bonReception)
    {
        try {
            $validated = $request->validate([
                'responsable_reception_id' => 'required|exists:users,id',
                'date_reception' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $bonReception->update($validated);

            Log::info('Bon reception updated successfully', ['id' => $bonReception->id]);

            return redirect()->route('bon-receptions.show', $bonReception->id)
                ->with('success', 'Bon de réception mis à jour avec succès');

        } catch (\Exception $e) {
            Log::error('Error updating bon reception: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BonReception $bonReception)
    {
        try {
            DB::beginTransaction();

            $bonCommande = $bonReception->bonCommande;
            
            // Supprimer les lignes de réception (les mouvements de stock seront supprimés automatiquement via les events)
            $bonReception->lignesReception()->delete();
            
            // Supprimer le bon de réception
            $bonReception->delete();

            // Mettre à jour le statut de la commande
            if ($bonCommande) {
                $this->mettreAJourStatutCommande($bonCommande);
            }

            DB::commit();

            Log::info('Bon reception deleted successfully', ['id' => $bonReception->id]);

            return redirect()->route('bon-receptions.index')
                ->with('success', 'Bon de réception supprimé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting bon reception: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Update documents for bon reception
     */
    public function updateDocuments(Request $request, BonReception $bonReception)
    {
        try {
            $validated = $request->validate([
                'responsable_reception_id' => 'nullable|exists:users,id',
                'fichier_bonlivraison' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'fichier_facture' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'supprimer_fichier_bonlivraison' => 'nullable|boolean',
                'supprimer_fichier_facture' => 'nullable|boolean',
                'notes' => 'nullable|string'
            ]);

            // Gestion des fichiers bon de livraison
            if ($request->hasFile('fichier_bonlivraison')) {
                $bonReception->uploadFichierBonlivraison($request->file('fichier_bonlivraison'));
            } elseif ($request->boolean('supprimer_fichier_bonlivraison')) {
                $bonReception->supprimerFichierBonlivraison();
            }

            // Gestion des fichiers facture
            if ($request->hasFile('fichier_facture')) {
                $bonReception->uploadFichierFacture($request->file('fichier_facture'));
            } elseif ($request->boolean('supprimer_fichier_facture')) {
                $bonReception->supprimerFichierFacture();
            }

            // Mettre à jour les autres champs
            $bonReception->update([
                'responsable_reception_id' => $validated['responsable_reception_id'] ?? $bonReception->responsable_reception_id,
                'notes' => $validated['notes'] ?? $bonReception->notes,
            ]);

            Log::info('Bon reception documents updated', ['id' => $bonReception->id]);

            return redirect()->back()->with('success', 'Documents et informations mis à jour avec succès');

        } catch (\Exception $e) {
            Log::error('Error updating bon reception documents: ' . $e->getMessage());
            
            return redirect()->back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

  
    /**
     * Get statistics for bon receptions
     */
    public function stats()
    {
        try {
            $stats = [
                'total' => BonReception::count(),
                'complets' => BonReception::complets()->count(),
                'partiels' => BonReception::partiels()->count(),
                'avec_bon_livraison' => BonReception::avecBonLivraison()->count(),
                'avec_facture' => BonReception::avecFacture()->count(),
                'this_month' => BonReception::whereMonth('created_at', now()->month)->count(),
                'last_month' => BonReception::whereMonth('created_at', now()->subMonth()->month)->count(),
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error getting bon reception stats: ' . $e->getMessage());
            
            return response()->json(['error' => 'Erreur lors de la récupération des statistiques'], 500);
        }
    }

    /**
     * Récupérer les détails d'une commande pour AJAX
     */



 
    /**
     * Méthode de débogage pour vérifier les données des commandes
     */


    public function debugCommandes()
    {
        $commandes = BonCommande::whereIn('statut', ['attente_livraison', 'livre_partiellement'])->get();

        $debugData = $commandes->map(function($commande) {
            $commande->load(['articles.article', 'bonReceptions.lignesReception']);
            
            $articlesDebug = $commande->articles->map(function($article) use ($commande) {
                $quantiteRecue = LigneReception::whereHas('bonReception', function($query) use ($commande) {
                        $query->where('bon_commande_id', $commande->id);
                    })
                    ->where('article_id', $article->article_id)
                    ->sum('quantite_receptionnee');
                
                return [
                    'article_id' => $article->article_id,
                    'designation' => $article->article->designation,
                    'quantite_commandee' => $article->quantite_commandee,
                    'quantite_recue' => $quantiteRecue,
                    'reste_a_recevoir' => $article->quantite_commandee - $quantiteRecue,
                    'a_recevoir' => ($article->quantite_commandee - $quantiteRecue) > 0
                ];
            });

            return [
                'commande_id' => $commande->id,
                'reference' => $commande->reference,
                'statut' => $commande->statut,
                'fournisseur' => $commande->fournisseur?->raison_sociale,
                'articles' => $articlesDebug,
                'a_des_articles_a_recevoir' => $articlesDebug->where('a_recevoir', true)->count() > 0,
                'bon_receptions_count' => $commande->bonReceptions->count()
            ];
        });

        return response()->json($debugData);
    }


/**
 * Show bon reception avec tous les détails
 */
public function showDetails(BonReception $bonReception)
{
    
    try {
        $bonReception->load([
            'fournisseur',
            'responsableReception',
            'createdBy'
        ]);

        // Calculer les totaux détaillés
        $totaux = [
            'total_ht' => 0,
            'total_tva' => 0,
            'total_ttc' => 0,
            'quantite_totale' => 0
        ];

        foreach ($bonReception->lignesReception as $ligne) {
            $montantHT = $ligne->quantite_receptionnee * $ligne->prix_unitaire;
            $montantTVA = $montantHT * ($ligne->taux_tva / 100);
            $montantTTC = $montantHT + $montantTVA;

            $totaux['total_ht'] += $montantHT;
            $totaux['total_tva'] += $montantTVA;
            $totaux['total_ttc'] += $montantTTC;
            $totaux['quantite_totale'] += $ligne->quantite_receptionnee;
        }

        // Comparaison avec la commande
        $comparaison = [];
        if ($bonReception->bonCommande) {
            $comparaison = $bonReception->bonCommande->articles->map(function ($ligneCommande) use ($bonReception) {
                $quantiteRecueDansCetteReception = $bonReception->lignesReception
                    ->where('article_id', $ligneCommande->article_id)
                    ->sum('quantite_receptionnee');
                
                $quantiteTotaleRecue = LigneReception::whereHas('bonReception', function($query) use ($bonReception) {
                        $query->where('bon_commande_id', $bonReception->bon_commande_id);
                    })
                    ->where('article_id', $ligneCommande->article_id)
                    ->sum('quantite_receptionnee');
                
                return [
                    'article_id' => $ligneCommande->article_id,
                    'article_designation' => $ligneCommande->article->designation,
                    'article_reference' => $ligneCommande->article->reference,
                    'quantite_commandee' => $ligneCommande->quantite_commandee,
                    'quantite_recue_cette_reception' => $quantiteRecueDansCetteReception,
                    'quantite_totale_recue' => $quantiteTotaleRecue,
                    'reste_a_recevoir' => max(0, $ligneCommande->quantite_commandee - $quantiteTotaleRecue),
                    'est_complet' => $quantiteTotaleRecue >= $ligneCommande->quantite_commandee,
                    'pourcentage_recu' => $ligneCommande->quantite_commandee > 0 ? 
                        round(($quantiteTotaleRecue / $ligneCommande->quantite_commandee) * 100, 2) : 0
                ];
            });
        }

        return inertia('Achats/BonReceptions/ShowDetails', [
            'bonReception' => ShowBonReceptionResource::make($bonReception),
            'totaux' => $totaux,
            'comparaison' => $comparaison,
        ]);

    } catch (\Exception $e) {
        Log::error('Error showing bon reception details: ' . $e->getMessage());
        
        return redirect()->route('bon-receptions.index')
            ->with('error', 'Erreur lors du chargement des détails: ' . $e->getMessage());
    }
}


/**
 * Download bon de livraison
 */
public function downloadBonLivraison(BonReception $bonReception)
{
    try {
        if (!$bonReception->fichier_bonlivraison) {
            return redirect()->back()->with('error', 'Aucun bon de livraison disponible');
        }

        $filePath = storage_path('app/public/' . $bonReception->fichier_bonlivraison);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Fichier bon de livraison introuvable');
        }

        $fileName = "bon-livraison-{$bonReception->numero}." . pathinfo($filePath, PATHINFO_EXTENSION);
        
        return response()->download($filePath, $fileName);

    } catch (\Exception $e) {
        Log::error('Error downloading bon livraison: ' . $e->getMessage());
        
        return redirect()->back()->with('error', 'Erreur lors du téléchargement: ' . $e->getMessage());
    }
}

/**
 * Download facture
 */
public function downloadFacture(BonReception $bonReception)
{
    try {
        if (!$bonReception->fichier_facture) {
            return redirect()->back()->with('error', 'Aucune facture disponible');
        }

        $filePath = storage_path('app/public/' . $bonReception->fichier_facture);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Fichier facture introuvable');
        }

        $fileName = "facture-{$bonReception->numero}." . pathinfo($filePath, PATHINFO_EXTENSION);
        
        return response()->download($filePath, $fileName);

    } catch (\Exception $e) {
        Log::error('Error downloading facture: ' . $e->getMessage());
        
        return redirect()->back()->with('error', 'Erreur lors du téléchargement: ' . $e->getMessage());
    }
}

/**
 */


/**
 * Download PDF for bon reception
 */
// app/Http/Controllers/BonReceptionController.php

/**
 * Download PDF for bon reception
 */
public function downloadPdf(BonReception $bonReception)
{
        // Charger les relations nécessaires SANS les relations circulaires
        $bonReception->load([
            'bonCommande:id,reference,date_mise_ligne,statut', // Seulement les champs nécessaires
            'fournisseur:id,raison_sociale,nom',
            'responsableReception:id,name',
            'lignesReception.article:id,reference,designation,unite_mesure',
            'createdBy:id,name'
        ]);

        // DEBUG: Vérifier les données chargées
        Log::info('Generating PDF for bon reception', [
            'bon_reception_id' => $bonReception->id,
            'numero' => $bonReception->numero,
            'lignes_count' => $bonReception->lignesReception->count(),
            'fournisseur' => $bonReception->fournisseur ? $bonReception->fournisseur->raison_sociale : 'Non trouvé'
        ]);

        // Calculer les totaux
        $totaux = $this->calculerTotaux($bonReception);


        $fileName = "bon-reception-{$bonReception->numero}.pdf";

        Log::info('PDF generated successfully', [
            'file_name' => $fileName,
            'total_lignes' => $bonReception->lignesReception->count()
        ]);

        return Pdf::view('pdf.bon-reception', compact('bonReception'))->name($fileName)
            ->headerView('pdf.H')
            ->footerView('pdf.F')
            ->margins(45, 5, 40,5)
            ->format(Format::A4)
            ->download();
            ;
}

/**
 * Calculer les totaux de manière séparée
 */
private function calculerTotaux($bonReception)
{
    $totaux = [
        'total_ht' => 0,
        'total_tva' => 0,
        'total_ttc' => 0,
        'quantite_totale' => 0
    ];

    foreach ($bonReception->lignesReception as $ligne) {
        $montantHT = $ligne->quantite_receptionnee * $ligne->prix_unitaire;
        $montantTVA = $montantHT * ($ligne->taux_tva / 100);
        $montantTTC = $montantHT + $montantTVA;

        $totaux['total_ht'] += $montantHT;
        $totaux['total_tva'] += $montantTVA;
        $totaux['total_ttc'] += $montantTTC;
        $totaux['quantite_totale'] += $ligne->quantite_receptionnee;
    }

    return $totaux;
}
/**
 * Preview PDF for bon reception (ouvrir dans le navigateur)
 */
public function previewPdf(BonReception $bonReception)
{
    try {
        $bonReception->load([
            'bonCommande',
            'fournisseur',
            'responsableReception',
            'lignesReception.article.unite',
            'createdBy'
        ]);

        // Calculer les totaux
        $totaux = [
            'total_ht' => 0,
            'total_tva' => 0,
            'total_ttc' => 0,
            'quantite_totale' => 0
        ];

        foreach ($bonReception->lignesReception as $ligne) {
            $montantHT = $ligne->quantite_receptionnee * $ligne->prix_unitaire;
            $montantTVA = $montantHT * ($ligne->taux_tva / 100);
            $montantTTC = $montantHT + $montantTVA;

            $totaux['total_ht'] += $montantHT;
            $totaux['total_tva'] += $montantTVA;
            $totaux['total_ttc'] += $montantTTC;
            $totaux['quantite_totale'] += $ligne->quantite_receptionnee;
        }

        $pdf = PDF::loadView('bon-reception.pdf', [
            'bonReception' => $bonReception,
            'totaux' => $totaux
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'dejavu sans',
            'dpi' => 96,
        ]);

        return $pdf->stream("bon-reception-{$bonReception->numero}.pdf");

    } catch (\Exception $e) {
        Log::error('Error previewing PDF for bon reception: ' . $e->getMessage());
        
        return redirect()->back()->with('error', 'Erreur lors de la prévisualisation du PDF: ' . $e->getMessage());
    }
}


// :::::::::::::::::::::::



public function create(Request $request)
{
    try {
        $bonCommande = null;
        
        if ($request->has('bon_commande')) {
            $bonCommande = BonCommande::with(['fournisseur', 'articles.article'])
                ->findOrFail($request->bon_commande);
            
            // Préparer les données pour le formulaire - CORRECTION ICI
            $bonCommande->articles->each(function ($ligneCommande) use ($bonCommande) {
                $quantiteRecue = LigneReception::whereHas('bonReception', function($query) use ($bonCommande) {
                        $query->where('bon_commande_id', $bonCommande->id);
                    })
                    ->where('article_id', $ligneCommande->article_id)
                    ->sum('quantite_receptionnee');
                
                $ligneCommande->quantite_deja_recue = $quantiteRecue;
                $ligneCommande->reste_a_recevoir = max(0, $ligneCommande->quantite_commandee - $quantiteRecue);
                $ligneCommande->quantite_max_receptionnable = $ligneCommande->reste_a_recevoir;
                
                // AJOUTER LES CHAMPS MANQUANTS
                $ligneCommande->prix_unitaire_ht = $ligneCommande->prix_unitaire_ht ?? 0;
                $ligneCommande->taux_tva = $ligneCommande->taux_tva ?? 0;
            });
        }

        // Récupérer les commandes avec des articles à recevoir - CORRECTION ICI
        $bonCommandes = BonCommande::whereIn('statut', ['attente_livraison', 'livre_partiellement'])
            ->with(['fournisseur', 'articles.article'])
            ->get()
            ->filter(function ($commande) {
                // Filtrer seulement les commandes qui ont encore des articles à recevoir
                foreach ($commande->articles as $ligne) {
                    $quantiteRecue = LigneReception::whereHas('bonReception', function($query) use ($commande) {
                            $query->where('bon_commande_id', $commande->id);
                        })
                        ->where('article_id', $ligne->article_id)
                        ->sum('quantite_receptionnee');
                    
                    if (($ligne->quantite_commandee - $quantiteRecue) > 0) {
                        return true;
                    }
                }
                return false;
            })
            ->map(function ($commande) {
                // Calculer les statistiques de réception
                $commande->quantite_totale_commandee = $commande->articles->sum('quantite_commandee');
                $quantiteTotaleRecue = 0;
                
                foreach ($commande->articles as $ligne) {
                    $quantiteRecue = LigneReception::whereHas('bonReception', function($query) use ($commande) {
                            $query->where('bon_commande_id', $commande->id);
                        })
                        ->where('article_id', $ligne->article_id)
                        ->sum('quantite_receptionnee');
                    
                    $ligne->quantite_deja_recue = $quantiteRecue;
                    $ligne->reste_a_recevoir = max(0, $ligne->quantite_commandee - $quantiteRecue);
                    $quantiteTotaleRecue += $quantiteRecue;
                    
                    // AJOUTER LES CHAMPS MANQUANTS
                    $ligne->prix_unitaire_ht = $ligne->prix_unitaire_ht ?? 0;
                    $ligne->taux_tva = $ligne->taux_tva ?? 0;
                }
                
                $commande->quantite_totale_recue = $quantiteTotaleRecue;
                $commande->reste_a_recevoir = $commande->quantite_totale_commandee - $quantiteTotaleRecue;
                $commande->pourcentage_recu = $commande->quantite_totale_commandee > 0 ? 
                    round(($quantiteTotaleRecue / $commande->quantite_totale_commandee) * 100, 2) : 0;
                
                return $commande;
            });

        $magasiniers = User::where('role', 'MAGASINIER')
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return inertia('Achats/BonReceptions/Create', [
            'bonCommandes' => $bonCommandes,
            'selectedBonCommande' => $bonCommande,
            'magasiniers' => $magasiniers,
        ]);

    } catch (\Exception $e) {
        Log::error('Error in BonReceptionController@create: ' . $e->getMessage());
        
        return redirect()->route('bon-receptions.index')
            ->with('error', 'Erreur lors du chargement du formulaire: ' . $e->getMessage());
    }
}

  
    /**
     * Déterminer le type de réception
     */
 
    /**
     * Mettre à jour le statut de la commande
     */
    private function mettreAJourStatutCommande(BonCommande $bonCommande): void
    {
        $toutesLignesLivrees = true; // Assume all lines delivered initially
        $auMoinsUneLigneRecue = false;

        foreach ($bonCommande->articles as $ligneCommande) {
            $quantiteRecue = LigneReception::whereHas('bonReception', function($query) use ($bonCommande) {
                    $query->where('bon_commande_id', $bonCommande->id);
                })
                ->where('article_id', $ligneCommande->article_id)
                ->sum('quantite_receptionnee');

            if ($quantiteRecue < $ligneCommande->quantite_commandee) {
                $toutesLignesLivrees = false; // This line not fully delivered
            }

            if ($quantiteRecue > 0) {
                $auMoinsUneLigneRecue = true; // At least one line received
            }
        }

        if ($toutesLignesLivrees) {
            $nouveauStatut = 'livre_completement';
        } elseif ($auMoinsUneLigneRecue) {
            $nouveauStatut = 'livre_partiellement';
        } else {
            $nouveauStatut = 'attente_livraison';
        }

        $bonCommande->update(['statut' => $nouveauStatut]);
        
        Log::info('Statut commande mis à jour', [
            'commande_id' => $bonCommande->id,
            'ancien_statut' => $bonCommande->getOriginal('statut'),
            'nouveau_statut' => $nouveauStatut
        ]);
    }



     public function getCommandeDetails($id)
    {
        try {
            $commande = BonCommande::with(['fournisseur', 'articles.article'])
                ->findOrFail($id);
            
            // Préparer les données pour le formulaire
            $commande->articles->each(function ($ligneCommande) use ($commande) {
                $quantiteRecue = LigneReception::whereHas('bonReception', function($query) use ($commande) {
                        $query->where('bon_commande_id', $commande->id);
                    })
                    ->where('article_id', $ligneCommande->article_id)
                    ->sum('quantite_receptionnee');
                
                $ligneCommande->quantite_deja_recue = $quantiteRecue;
                $ligneCommande->reste_a_recevoir = max(0, $ligneCommande->quantite_commandee - $quantiteRecue);
                $ligneCommande->quantite_max_receptionnable = $ligneCommande->reste_a_recevoir;
                
                // Assurer que les champs prix existent
                $ligneCommande->prix_unitaire_ht = $ligneCommande->prix_unitaire_ht ?? 0;
                $ligneCommande->taux_tva = $ligneCommande->taux_tva ?? 0;
            });

            // Calculer les totaux
            $commande->quantite_totale_commandee = $commande->articles->sum('quantite_commandee');
            $commande->quantite_totale_recue = $commande->articles->sum('quantite_deja_recue');
            $commande->reste_a_recevoir = $commande->quantite_totale_commandee - $commande->quantite_totale_recue;
            $commande->pourcentage_recu = $commande->quantite_totale_commandee > 0 ? 
                round(($commande->quantite_totale_recue / $commande->quantite_totale_commandee) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'commande' => $commande
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting commande details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage - CORRIGÉ
     */
    /**
     * Store a newly created resource in storage - CORRIGÉ
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('=== DÉBUT CRÉATION BON RÉCEPTION ===');

            // Validation des données
            $validated = $request->validate([
                'bon_commande_id' => 'required|exists:bon_commandes,id',
                'date_reception' => 'required|date',
                'responsable_reception_id' => 'required|exists:users,id',
                'fichier_bonlivraison' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'fichier_facture' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'lignes_reception' => 'required|array|min:1',
                'lignes_reception.*.article_id' => 'required|exists:articles,id',
                'lignes_reception.*.quantite_receptionnee' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            Log::info('Données validées:', $validated);

            // Vérifier qu'il y a au moins une ligne avec quantité > 0
            $hasQuantitePositive = collect($validated['lignes_reception'])->contains(function ($ligne) {
                return floatval($ligne['quantite_receptionnee']) > 0;
            });

            if (!$hasQuantitePositive) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['quantites' => 'Au moins une quantité doit être supérieure à 0']);
            }

            $bonCommande = BonCommande::with(['fournisseur', 'articles.article'])
                ->findOrFail($validated['bon_commande_id']);

            // Vérifier les quantités
            foreach ($validated['lignes_reception'] as $index => $ligneReception) {
                $quantite = floatval($ligneReception['quantite_receptionnee']);
                
                if ($quantite > 0) {
                    $quantiteDejaRecue = LigneReception::whereHas('bonReception', function($query) use ($bonCommande) {
                            $query->where('bon_commande_id', $bonCommande->id);
                        })
                        ->where('article_id', $ligneReception['article_id'])
                        ->sum('quantite_receptionnee');
                    
                    $ligneCommande = $bonCommande->articles->firstWhere('article_id', $ligneReception['article_id']);
                    
                    if (!$ligneCommande) {
                        throw new \Exception("Article non trouvé dans la commande");
                    }
                    
                    $resteARecevoir = max(0, $ligneCommande->quantite_commandee - $quantiteDejaRecue);
                    
                    if ($quantite > $resteARecevoir) {
                        $article = Article::find($ligneReception['article_id']);
                        throw new \Exception("{$article->designation} : quantité maximale = {$resteARecevoir}");
                    }
                }
            }

            // Déterminer le type de réception
            $typeReception = $this->determinerTypeReception($bonCommande, $validated['lignes_reception']);

            // CORRECTION : Générer le numéro correctement
            $numero = BonReception::genererNumero();

            // Créer le bon de réception
            $bonReceptionData = [
                'numero' => $numero, // Utiliser la variable générée
                'bon_commande_id' => $validated['bon_commande_id'],
                'fournisseur_id' => $bonCommande->fournisseur_id,
                'date_reception' => $validated['date_reception'],
                'type_reception' => $typeReception,
                'statut' => BonReception::STATUT_VALIDE,
                'responsable_reception_id' => $validated['responsable_reception_id'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ];

            Log::info('Création du bon réception avec données:', $bonReceptionData);

            if ($typeReception === BonReception::TYPE_COMPLET && !$request->hasFile('fichier_facture')) {
                // Vérifier si la réception est complète avant d'autoriser la facture
                throw new \Exception("La facture ne peut être ajoutée que pour une réception complète");
            }
            
            $bonReception = BonReception::create($bonReceptionData);

            
            if (!$bonReception) {
                throw new \Exception("Échec de la création du bon de réception");
            }

            // Gestion des fichiers
            if ($request->hasFile('fichier_bonlivraison')) {
                $bonReception->uploadFichierBonlivraison($request->file('fichier_bonlivraison'));
            }

            if ($typeReception === BonReception::TYPE_COMPLET && $request->hasFile('fichier_facture')) {
                $bonReception->uploadFichierFacture($request->file('fichier_facture'));
            }

            // Créer les lignes de réception
            $lignesCreees = 0;
            foreach ($validated['lignes_reception'] as $ligneData) {
                $quantite = floatval($ligneData['quantite_receptionnee']);
                
                if ($quantite > 0) {
                    $ligneCommande = $bonCommande->articles->firstWhere('article_id', $ligneData['article_id']);
                    
                    if ($ligneCommande) {
                        $prixUnitaire = $ligneCommande->prix_unitaire_ht ?? 0;
                        $tauxTva = $ligneCommande->taux_tva ?? 0;
                        
                        // CORRECTION : Calculs corrects des montants
                        $prixTotalHt = $quantite * $prixUnitaire;
                        $montantTva = $prixTotalHt * ($tauxTva / 100);
                        $prixTotalTtc = $prixTotalHt + $montantTva;
                        
                        LigneReception::create([
                            'bon_reception_id' => $bonReception->id,
                            'article_id' => $ligneData['article_id'],
                            'quantite_receptionnee' => $quantite,
                            'prix_unitaire' => $prixUnitaire,
                            'taux_tva' => $tauxTva,
                            'montant_tva' => $montantTva,
                            'prix_total' => $prixTotalTtc,
                        ]);

                        $lignesCreees++;
                    }
                }
            }

            // Mettre à jour le statut de la commande
            $this->mettreAJourStatutCommande($bonCommande);

            if ($bonReception->statut == BonReception::STATUT_VALIDE) {
                try {
                    DB::transaction(function () use ($bonReception) {
                        // Vérifier si une entrée de stock existe déjà
                        $existingEntree = EntreeStock::where('bon_reception_id', $bonReception->id)->first();
                        
                        if ($existingEntree) {
                            Log::info("Une entrée de stock existe déjà pour ce bon de réception: {$bonReception->id}");
                            return;
                        }

                        // Créer l'entrée de stock
                        $entreeStock = EntreeStock::create([
                            'numero' => EntreeStock::genererNumero(),
                            'bon_reception_id' => $bonReception->id,
                            'fournisseur_id' => $bonReception->fournisseur_id,
                            'date_entree' => $bonReception->date_reception,
                            'statut' => 'attente_validation',
                            'notes' => $bonReception->notes . "Créé automatiquement à partir du bon de réception " . $bonReception->numero_affichage,
                            'created_by' => $bonReception->created_by,
                        ]);

                        // Créer les lignes d'entrée de stock à partir des lignes de réception
                        foreach ($bonReception->lignesReception as $ligneReception) {
                            LigneEntreeStock::create([
                                'entree_stock_id' => $entreeStock->id,
                                'article_id' => $ligneReception->article_id,
                                'quantite' => $ligneReception->quantite_receptionnee,
                                'prix_unitaire' => $ligneReception->prix_unitaire,
                                'taux_tva' => $ligneReception->taux_tva,
                                'montant_tva' => $ligneReception->montant_tva,
                                'prix_total' => $ligneReception->prix_total,
                            ]);
                        }

                        Log::info("Entrée de stock créée automatiquement pour le bon de réception: {$bonReception->id}");
                    });

                } catch (\Exception $e) {
                    Log::error('Erreur création entrée stock automatique: ' . $e->getMessage());
                }
            }

            DB::commit();

            Log::info('=== BON RÉCEPTION CRÉÉ AVEC SUCCÈS ===', [
                'id' => $bonReception->id,
                'numero' => $bonReception->numero,
                'lignes_creees' => $lignesCreees,
                'type_reception' => $typeReception
            ]);

            return redirect()->route('bon-receptions.index')
                ->with('success', 'Bon de réception ' . $bonReception->numero_affichage . ' créé avec succès - Type: ' . 
                    ($typeReception === BonReception::TYPE_COMPLET ? 'Complet' : 'Partiel'));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('=== ERREUR CRÉATION BON RÉCEPTION ===');
            Log::error('Message: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Déterminer le type de réception - CORRIGÉ
     */
    private function determinerTypeReception(BonCommande $bonCommande, array $lignesReception): string
    {
        foreach ($bonCommande->articles as $ligneCommande) {
            $quantityCommanded = $ligneCommande->quantite_commandee;
            $previousReception = LigneReception::whereHas('bonReception', function($query) use ($bonCommande) {
                    $query->where('bon_commande_id', $bonCommande->id);
                })
                ->where('article_id', $ligneCommande->article_id)
                ->sum('quantite_receptionnee');
            $currentReception = collect($lignesReception)->firstWhere('article_id', $ligneCommande->article_id)['quantite_receptionnee'] ?? 0;

            $isPartialReception = floatval($quantityCommanded) > (floatval($previousReception) + floatval($currentReception));
            
            if ($isPartialReception) {
                return BonReception::TYPE_PARTIEL;
            }
        }

        return BonReception::TYPE_COMPLET;
    }

}