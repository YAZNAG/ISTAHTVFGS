<?php

namespace App\Http\Controllers;

use App\Http\Resources\IndexBonSortieResource;
use App\Http\Resources\ShowBonSortieResource;
use App\Models\BonCommandeArticle;
use App\Models\MouvementStock;
use App\Models\SortieStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class BonSortieController extends Controller
{
    public function index(Request $request)
    {
        $query = SortieStock::with([
                'demandeur',
                'lignesSortie.article',
            ])->orderBy('created_at', 'desc');

            // Filtrage par statut
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            // Filtrage par date
            if ($request->filled('date_debut')) {
                $query->where('date_sortie', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->where('date_sortie', '<=', $request->date_fin);
            }

            // Recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', '%' . $search . '%')
                      ->orWhere('motif', 'like', '%' . $search . '%')
                      ->orWhereHas('demandeur', function ($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            $sortieStocks = $query->paginate(20)->withQueryString();

            return inertia('BonSortie/Index', [
                'sorties' => IndexBonSortieResource::collection($sortieStocks),
                // 'stats' => $stats,
                'filters' => $request->only(['statut', 'date_debut', 'date_fin', 'search'])
            ]);
    }


    public function show(SortieStock $bonSortie)
    {
        $bonSortie->load([
            'lignesSortie.article',
            'demandeur',
            'createdBy'
        ]);

        return Inertia::render('Stock/SortieStocks/ShowSortieModal', [
            'sortie' => ShowBonSortieResource::make($bonSortie)
        ]);
    }

    public function showApprove(SortieStock $bonSortie) {

        return Inertia::modal('BonSortie/ApproveModal', [
            'sortie' => ShowBonSortieResource::make($bonSortie)
        ])->baseRoute('bon-sorties.index');
    } 

    public function approve(Request $request, SortieStock $bonSortie) {
        // $this->authorize('approve', $demande);

        $request->validate([
            'commentaire_validation' => 'nullable|string|max:500',
        ]);


        DB::transaction(function () use ($bonSortie, $request) {
            
            $bonSortie->update([
                'statut' => SortieStock::STATUT_VALIDE,
                'commentaire_validation' => $request->input('commentaire_validation'),
                'date_validation' => now(),
            ]);

            foreach ($bonSortie->lignesSortie as $articleLine) {

                $articleLine->article->decrement('quantite_stock', $articleLine->quantite);
                    
                $nouvelleQuantiteActuelle = $articleLine->article->quantite_stock;
                
                MouvementStock::create([
                    'type' => MouvementStock::TYPE_SORTIE,
                    'article_id' => $articleLine->article_id,
                    'created_by' => auth()->id(),
                    'date_mouvement' => now(),
                    'prix_unitaire' => $articleLine->prix_unitaire,
                    'taux_tva' => $articleLine->taux_tva,
                    'type_mouvement' => MouvementStock::TYPE_SORTIE,
                    'quantite_sortie' => $articleLine->quantite,
                    'quantite_actuelle' => $nouvelleQuantiteActuelle,
                    'quantite_entree' => 0,
                    'motif' => 'Sorite de bon de Sortie n° ' . $bonSortie->numero,
                    'referenceable_id' => $bonSortie->id,
                    'referenceable_type' => SortieStock::class,
                ]);

            }
            
        });
        
        return redirect()->back();
    } 


    public function reject(Request $request, SortieStock $bonSortie) {
        // $this->authorize('approve', $bonSortie);

         $request->validate([
            'commentaire_validation' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($bonSortie, $request) {
            
            $bonSortie->update([
                'statut' => SortieStock::STATUT_ANNULE,
                'valide_par' => auth()->user()->id,
                'commentaire_validation' => $request->input('commentaire_validation'),
                'date_validation' => now(),
            ]);
            

        });

        
        return redirect()->back();
    } 
    
    /**
     * Download Bon Sorie
     */
    public function downloadPdf(SortieStock $bonSortie)
    {
        $bonSortie->load([
            'lignesSortie.article',
        ]);

        return Pdf::view('pdf.bon-sortie', [
            'sortieStock' => $bonSortie
        ])->download("bon-sortie-{$bonSortie->numero}.pdf");
    }
}
