<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChefCommandeRequest;
use App\Http\Resources\ChefCommandeResource;
use App\Http\Resources\EditChefCommandeResource;
use App\Http\Resources\ShowChefCommandeResource;
use App\Models\Article;
use App\Models\ChefCommande;
use App\Models\ChefCommandeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                    ->orWhere('objet', 'like', "%{$search}%");
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
            'articles' => Article::all(['id', 'designation'])
        ])->baseRoute('chef-commandes.index');
    }


    public function store(StoreChefCommandeRequest $request)
    {
        #FIX: it creates multiple chef commandes
        $chefCommande = ChefCommande::create([
            'numero' => ChefCommande::genererNumero(),
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
        $articles = Article::all(['id', 'designation']);
        return Inertia::modal('ChefCommande/EditCommandeModal', [
            'chefCommande' => EditChefCommandeResource::make($chefCommande),
            'articles' => $articles
        ])
        ->baseRoute('chef-commandes.index');
        ;
    }

    public function update(StoreChefCommandeRequest $request, ChefCommande $chefCommande)
    {

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
}
