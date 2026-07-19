<?php

namespace App\Exports;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\Concerns\WithIstahtHeader;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ArticlesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    use WithIstahtHeader;

    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        return $this->query()
            ->with('categorie:id,nom,code')
            ->orderBy('designation')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Designation',
            'Categorie',
            'Unite',
            'Stock actuel',
            'Seuil minimal',
            'Seuil maximal',
            'Statut',
        ];
    }

    public function map($article): array
    {
        return [
            $article->reference,
            $article->designation,
            $article->categorie?->nom,
            $article->unite_mesure,
            (float) ($article->quantite_stock ?? 0),
            (float) ($article->seuil_minimal ?? 0),
            (float) ($article->seuil_maximal ?? 0),
            $article->est_actif ? 'Actif' : 'Inactif',
        ];
    }

    private function query(): Builder
    {
        return Article::withNonExists()
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhereHas('categorie', fn ($q) => $q->where('nom', 'like', "%{$search}%"));
                });
            })
            ->when($this->filters['categorie_id'] ?? null, fn ($query, $categoryId) => $query->where('categorie_id', $categoryId))
            ->when($this->filters['status'] ?? null, fn ($query, $status) => $query->where('est_actif', $status === 'actif'))
            ->when($this->filters['stock'] ?? null, function ($query, $stock) {
                if ($stock === 'rupture') {
                    $query->where('quantite_stock', '<=', 0);
                }

                if ($stock === 'faible') {
                    $query->where('quantite_stock', '>', 0)
                        ->whereRaw('quantite_stock <= seuil_minimal * 0.8');
                }

                if ($stock === 'normal') {
                    $query->whereRaw('quantite_stock > seuil_minimal * 0.8')
                        ->where('quantite_stock', '>', 0);
                }
            });
    }
}
