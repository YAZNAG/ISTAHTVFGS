<?php

namespace App\Http\Controllers;

use App\Http\Resources\IndexReceptionResource;
use App\Http\Resources\ShowReceptionResource;
use App\Models\BonLivraison;
use App\Models\MouvementStock;
use App\Models\Reception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceptionController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_bonReceptions', only: ['index']),
            new Middleware('permission:show_bonReceptions', only: ['show', 'downloadDocument']),
            new Middleware('permission:create_bonReceptions', only: ['create', 'store']),
            new Middleware('permission:destroy_bonReceptions', only: ['destroy']),
            new Middleware('permission:pdf_bonReceptions', only: ['export']),


        ];
    }
    
    public function index(Request $request)
    {
        $search = $request->search;

        $receptions = Reception::with([
                // total_ht/tva/ttc sont des accesseurs calcules, PAS des colonnes SQL — ne jamais les mettre dans un select()
                'bonLivraison' => fn ($q) => $q
                    ->select(['id', 'numero', 'date_livraison', 'statut', 'fournisseur_id'])
                    ->withCount('items'),
                'bonLivraison.fournisseur:id,nom',
            ])
            ->when($search, function ($query, $search) {
                return $query->where('numero', 'like', '%' . $search . '%');
            })
            ->paginate(10)->withQueryString();

        return Inertia::render('Receptions/Index', [
            'bonReceptions' => IndexReceptionResource::collection($receptions),
            'filtres' => [
                'search' => $search,
            ]
        ]);
    }

    public function create()
    {
        $bonLivraisons = BonLivraison::livree()->whereDoesntHave('reception')->get(['id', 'numero']);

        return Inertia::modal('Receptions/Create', [
            'bonLivraisons' => $bonLivraisons
        ])->baseRoute('bon-receptions.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bon_livraison_id' => 'required|exists:bon_livraisons,id',
            'bon' => 'required|file',
        ]);

        DB::transaction(function () use ($request) {
            
            $reception = Reception::create([
                'bon_livraison_id' => $request->bon_livraison_id,
                'numero' => Reception::genererNumero(),
            ]);

            $reception->addMediaFromRequest('bon')->usingName("Bon de réception {$reception->numero}")->toMediaCollection('bon');

            $reception->load(['bonLivraison.items.article']);
            
            foreach ($reception->bonLivraison->items as $item) {
                $item->article->increment('quantite_stock', $item->quantite);
                        
                $nouvelleQuantiteActuelle = $item->article->quantite_stock;
                MouvementStock::create([
                    'type' => MouvementStock::TYPE_ENTREE,
                    'article_id' => $item->article_id,
                    'date_mouvement' => now(),
                    'prix_unitaire' => $item->prix_unitaire,
                    'taux_tva' => $item->taux_tva,
                    'type_mouvement' => MouvementStock::TYPE_ENTREE,
                    'quantite_entree' => $item->quantite,
                    'quantite_sortie' => 0,
                    'quantite_actuelle' => $nouvelleQuantiteActuelle,
                    'motif' => 'Reception de bon de livraison n° ' . $reception->bonLivraison->numero,
                    'referenceable_id' => $reception->id,
                    'referenceable_type' => Reception::class,
                ]);
            }
        });
        return redirect()->back()->with('success', 'Bon de reception ajouté avec succès');
    }

    public function show(Request $request, Reception $reception)
    {
        
        return Inertia::render('Receptions/ShowDetails', [
            'bonReception' => ShowReceptionResource::make($reception)
        ]);
    }

    public function destroy(Request $request, Reception $reception)
    {
        DB::transaction(function () use ($reception) {
            
            # TODO: delete reception and the related mouvement stock
            $reception->load(['bonLivraison.items.article', 'mouvements']);
            
            foreach ($reception->bonLivraison->items as $item) {
                $item->article->decrement('quantite_stock', $item->quantite);
                
                $reception->mouvements()->delete();
            }
            
        
            $reception->clearMediaCollection('bon');
            
            $reception->delete();
        });

        return redirect()->back()->with('success', 'Bon de reception supprimé avec succès');
    }

    public function downloadDocument(Reception $reception)
    {
        $media = $reception->getFirstMedia('bon');

        if (! $media) {
            abort(404, 'Aucun document joint a ce bon de reception.');
        }

        return response()->download($media->getPath(), $media->file_name);
    }

    public function export(Reception $reception)
{
        $reception->load([
            'BonLivraison.items.article', 
            'BonLivraison.fournisseur:id,nom,raison_sociale',
        ]);

        $fileName = "bon-reception-{$reception->numero}.pdf";

        return Pdf::loadView('pdf.bon-reception', [
            'reception'    => $reception,
            'pdfHeaderSrc' => $this->pdfHeaderBase64(),
        ])
        ->setPaper('a4', 'portrait')
        ->download($fileName);
}
}
