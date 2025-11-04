<?php

namespace App\Http\Controllers;

use App\Exports\FournisseursExport;
use App\Http\Resources\ShowFournisseurResource;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Fournisseur::withCount(['bonCommandes as bon_commandes_count'])
                ->orderBy('created_at', 'desc');

            // Filtrage par statut
            if ($request->has('est_actif') && $request->est_actif !== '') {
                $query->where('est_actif', $request->boolean('est_actif'));
            }

            // Filtrage par ville
            if ($request->filled('ville')) {
                $query->where('ville', 'like', '%' . $request->ville . '%');
            }

            // Recherche globale
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', '%' . $search . '%')
                      ->orWhere('raison_sociale', 'like', '%' . $search . '%')
                      ->orWhere('contact', 'like', '%' . $search . '%')
                      ->orWhere('telephone', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhere('ville', 'like', '%' . $search . '%')
                      ->orWhere('ice', 'like', '%' . $search . '%');
                });
            }

            $fournisseurs = $query->paginate(20)->withQueryString();

            // Statistiques
            $stats = [
                'total' => Fournisseur::count(),
                'actifs' => Fournisseur::where('est_actif', true)->count(),
                'inactifs' => Fournisseur::where('est_actif', false)->count(),
                'bons_commande' => DB::table('bon_commandes')->count(),
            ];

            // Villes distinctes pour les filtres
            $villes = Fournisseur::whereNotNull('ville')
                ->distinct()
                ->pluck('ville')
                ->sort()
                ->values()
                ->toArray();

            return inertia('Achats/Fournisseurs/Index', [
                'fournisseurs' => $fournisseurs,
                'stats' => $stats,
                'villes' => $villes,
                'filters' => $request->only(['est_actif', 'ville', 'search'])
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Erreur lors du chargement des fournisseurs: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'raison_sociale' => 'nullable|string|max:255',
                'contact' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'adresse' => 'nullable|string|max:500',
                'ville' => 'nullable|string|max:100',
                'ice' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'est_actif' => 'boolean',
            ]);

            // Gestion du logo
            $fournisseur = Fournisseur::create($validated);
            if ($request->hasFile('logo')) {
                // Supprimer l'ancien logo
               $fournisseur->addMediaFromRequest('logo')->toMediaCollection('logo');
            }

            DB::commit();

            return redirect()->route('fournisseurs.index')
                ->with('success', 'Fournisseur créé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->load(['bonCommandes' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->loadCount('bonCommandes');

        return inertia('Achats/Fournisseurs/Show', [
            'fournisseur' => ShowFournisseurResource::make($fournisseur)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fournisseur $fournisseur)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'raison_sociale' => 'nullable|string|max:255',
                'contact' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'adresse' => 'nullable|string|max:500',
                'ville' => 'nullable|string|max:100',
                'ice' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'est_actif' => 'boolean',
            ]);

            // Gestion du logo
            if ($request->hasFile('logo')) {
                // Supprimer l'ancien logo
               $fournisseur->addMediaFromRequest('logo')->toMediaCollection('logo');
            }

            $fournisseur->update($validated);

            DB::commit();

            return redirect()->route('fournisseurs.index')
                ->with('success', 'Fournisseur modifié avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fournisseur $fournisseur)
    {
        try {
            DB::beginTransaction();

            // Vérifier s'il y a des bons de commande associés
            if ($fournisseur->bonCommandes()->exists()) {
                throw new \Exception('Impossible de supprimer ce fournisseur car il a des bons de commande associés.');
            }

            // Supprimer le logo s'il existe
            if ($fournisseur->logo && Storage::disk('public')->exists($fournisseur->logo)) {
                Storage::disk('public')->delete($fournisseur->logo);
            }

            $fournisseur->delete();

            DB::commit();

            return redirect()->route('fournisseurs.index')
                ->with('success', 'Fournisseur supprimé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatut(Fournisseur $fournisseur)
    {
        $fournisseur->update([
            'est_actif' => !$fournisseur->est_actif
        ]);

        return redirect()->back();
    }

    /**
     * Export fournisseurs to CSV/Excel
     */
    // public function export(Request $request): StreamedResponse
    // {
    //     try {
    //         $query = Fournisseur::withCount('bonCommandes');

    //         // Appliquer les mêmes filtres que pour l'index
    //         if ($request->has('est_actif') && $request->est_actif !== '') {
    //             $query->where('est_actif', $request->boolean('est_actif'));
    //         }

    //         if ($request->filled('ville')) {
    //             $query->where('ville', 'like', '%' . $request->ville . '%');
    //         }

    //         if ($request->filled('search')) {
    //             $search = $request->search;
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('nom', 'like', '%' . $search . '%')
    //                   ->orWhere('raison_sociale', 'like', '%' . $search . '%')
    //                   ->orWhere('contact', 'like', '%' . $search . '%')
    //                   ->orWhere('ville', 'like', '%' . $search . '%');
    //             });
    //         }

    //         $fournisseurs = $query->get();

    //         $fileName = 'fournisseurs_' . now()->format('Y-m-d_H-i-s') . '.csv';

    //         return response()->streamDownload(function () use ($fournisseurs) {
    //             $handle = fopen('php://output', 'w');
                
    //             // En-têtes
    //             fputcsv($handle, [
    //                 'Nom',
    //                 'Raison Sociale',
    //                 'Contact',
    //                 'Téléphone',
    //                 'Email',
    //                 'Adresse',
    //                 'Ville',
    //                 'ICE',
    //                 'Bons de Commande',
    //                 'Statut',
    //                 'Date Création'
    //             ]);

    //             // Données
    //             foreach ($fournisseurs as $fournisseur) {
    //                 fputcsv($handle, [
    //                     $fournisseur->nom,
    //                     $fournisseur->raison_sociale,
    //                     $fournisseur->contact,
    //                     $fournisseur->telephone,
    //                     $fournisseur->email,
    //                     $fournisseur->adresse,
    //                     $fournisseur->ville,
    //                     $fournisseur->ice,
    //                     $fournisseur->bon_commandes_count,
    //                     $fournisseur->est_actif ? 'Actif' : 'Inactif',
    //                     $fournisseur->created_at->format('d/m/Y H:i')
    //                 ]);
    //             }

    //             fclose($handle);
    //         }, $fileName);

    //     } catch (\Exception $e) {
    //         // Fallback pour les erreurs d'export
    //         return response()->streamDownload(function () use ($e) {
    //             echo "Erreur lors de l'export: " . $e->getMessage();
    //         }, 'erreur_export.txt');
    //     }
    // }

    /**
     * Get fournisseur statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Fournisseur::count(),
                'actifs' => Fournisseur::where('est_actif', true)->count(),
                'inactifs' => Fournisseur::where('est_actif', false)->count(),
                'bons_commande' => DB::table('bon_commandes')->count(),
                'villes_count' => Fournisseur::whereNotNull('ville')->distinct('ville')->count(),
            ];

            return response()->json(['stats' => $stats]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du chargement des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function export() 
    {
        $chunks = Fournisseur::all();

        // return view('pdf.list-fournisseur', ['chunks' => $chunks]);
        return Pdf::view('pdf.list-fournisseur', ['fournisseurs' => $chunks])
        // ->headerHtml("<h1>Header</h1>")
        ->headerView('pdf.H')
        ->footerView('pdf.F')
        ->margins(45, 5, 40,5)
        ->format(Format::A4)
        ->orientation(Orientation::Landscape)
        ->download('fournisseurs.pdf')
    ;
        
    }
}