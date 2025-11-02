<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChefCommandeRequest;
use App\Http\Resources\ChefCommandeResource;
use App\Http\Resources\EditChefCommandeResource;
use App\Http\Resources\ShowChefCommandeResource;
use App\Models\Article;
use App\Models\BonCommande;
use App\Models\BonLivraison;
use App\Models\Categorie;
use App\Models\ChefCommande;
use App\Models\ChefCommandeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ChefCommandeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->get('search');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $chefCommandes = ChefCommande::withCount('articles')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                    ;
                });
            })
            ->when(!$user->isAdmin(), fn($query) => $query->where('user_id', $user->id))
            ->when($status, fn($query) => $query->where('statut', $status))
            ->when($startDate, fn($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate('created_at', '<=', $endDate))
            ->paginate(10)
            ->withQueryString();
        
        
        return Inertia::render('ChefCommande/Index', [
            'chefCommandes' => ChefCommandeResource::collection($chefCommandes),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }


    public function create()
    {
        return Inertia::modal('ChefCommande/CreateCommandeModal', [
            'articles' => Article::all(['id', 'designation', 'categorie_id', 'unite_mesure']),
            'categories' => Categorie::all(['id', 'nom']),
        ])->baseRoute('chef-commandes.index');
    }


    public function store(StoreChefCommandeRequest $request)
    {
        #FIX: it creates multiple chef commandes
        $chefCommande = ChefCommande::create([
            'numero' => ChefCommande::genererNumero(),
            'categorie_id' => $request->categorie_id,
            'note' => $request->note,
            'statut' => $request->type == 'submit' ? ChefCommande::STATUS_EN_ATTENTE_VALIDATION : ChefCommande::STATUS_CREE,
            'user_id' => auth()->user()->id,
        ]);



        foreach ($request->articles as $article) {
            ChefCommandeItem::create([
                'chef_commande_id' => $chefCommande->id,
                'article_id' => $article['article_id'],
                'quantite_commandee' => $article['quantite_commandee'],
            ]);

        }

        return redirect()->back()->with('success', 'Commande crée avec succès.');
    }

    public function show(ChefCommande $chefCommande)
    {
        $chefCommande->load('articles');
        return Inertia::modal('ChefCommande/ShowCommandeModal', [
            'chefCommande' => ShowChefCommandeResource::make($chefCommande)
        ])
        ->baseRoute('chef-commandes.index');
        ;
    }

    public function edit(ChefCommande $chefCommande)
    {
        $articles = Article::all(['id', 'designation', 'categorie_id', 'unite_mesure']);
        return Inertia::modal('ChefCommande/EditCommandeModal', [
            'chefCommande' => EditChefCommandeResource::make($chefCommande),
            'articles' => $articles,
            'categories' => Categorie::all(['id', 'nom']),
        ])
        ->baseRoute('chef-commandes.index');
        ;
    }

    public function update(StoreChefCommandeRequest $request, ChefCommande $chefCommande)
    {

        $chefCommande->update([
            'categorie_id' => $request->categorie_id,
            'note' => $request->note,
        ]);
        
        $chefCommande->items()->delete();

        foreach ($request->articles as $article) {
            ChefCommandeItem::create([
                'chef_commande_id' => $chefCommande->id,
                'article_id' => $article['article_id'],
                'quantite_commandee' => $article['quantite_commandee'],
            ]);

        }

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    }

    public function submit(ChefCommande $chefCommande)
    {

        $chefCommande->update([
            'statut' => ChefCommande::STATUS_EN_ATTENTE_VALIDATION,
        ]);

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    }

    public function cancel(ChefCommande $chefCommande)
    {

        $chefCommande->update([
            'statut' => ChefCommande::STATUS_ANNULEE  ,
        ]);

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    }


    public function showApprove(ChefCommande $chefCommande) {
        // $this->authorize('approve', $demande);
        $marches = BonCommande::select(['id', 'reference'])
            ->where('statut', BonCommande::STATUT_ATTENTE_LIVRAISON)
            ->where('categorie_id', $chefCommande->categorie_id)->get();

        return Inertia::modal('ChefCommande/ApproveModal', [
            'chefCommande' => ShowChefCommandeResource::make($chefCommande),
            'marches' => $marches
        ])->baseRoute('chef-commandes.index');
    } 

    public function approve(Request $request, ChefCommande $chefCommande) {
        // $this->authorize('approve', $demande);

        $request->validate([
            'validation_note' => 'nullable|string|max:500',
            'marche_id' => 'required|integer|exists:bon_commandes,id',
        ]);
        
        $marche = BonCommande::where('id', $request->input('marche_id'))->with('articles')->first();
        $missingArticles = $chefCommande->items()->pluck('article_id')->diff(
            $marche->articles->pluck('article_id')
        );

        if (!$missingArticles->isEmpty()) {
            $articles = Article::whereIn('id', $missingArticles)->pluck('designation');
            throw ValidationException::withMessages([
                'articlesError' => "les articles suivants sont pas dans le marché $marche->reference: " . "<strong>" . $articles->join(', ') . "<strong>",
            ]);
        }
        
        DB::transaction(function () use ($chefCommande, $request, $marche) {

            
            $chefCommande->update([
            'statut' => ChefCommande::STATUS_EN_ATTENTE_LIVRAISON,
            'validation_note' => $request->input('validation_note'),
            'bon_commande_id' => $request->input('marche_id'),
            'validation_date' => now(),
        ]);


        $bonlivraison = BonLivraison::create([
            'numero' => BonLivraison::genererNumero(),
            'statut' => BonLivraison::STATUS_EN_ATTENTE_LIVRAISON,
            'chef_commande_id' => $chefCommande->id,
            'fournisseur_id' => $marche->fournisseur_id,
        ]);

        ### Verify if all articles are belongs to the same marche

        

        foreach ($chefCommande->items as $item) {
            $marcheArticle = $marche->articles->where('article_id', $item->article_id)->first();
            $bonlivraison->items()->create([
                'quantite' => $item->quantite_commandee,
                'prix_unitaire' => $marcheArticle?->prix_unitaire_ht ?? -1,
                'taux_tva' => $marcheArticle?->taux_tva ?? -1,
                'article_id' => $item->article_id,
            ]);
        }
            
    });
          
        
        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    } 


    public function reject(Request $request, ChefCommande $chefCommande) {
        // $this->authorize('approve', $chefCommande);

         $request->validate([
            'validation_note' => 'nullable|string|max:500',
        ]);

        $chefCommande->update([
            'statut' => ChefCommande::STATUS_REJET,
            'validation_note' => $request->input('validation_note'),
            'validation_date' => now(),
        ]);

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    } 
}
