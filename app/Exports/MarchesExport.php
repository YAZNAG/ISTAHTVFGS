<?php

namespace App\Exports;

use App\Models\BonCommande;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MarchesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        return $this->query()
            ->with(['categorie:id,nom,code', 'fournisseur:id,nom,raison_sociale', 'articles'])
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Objet',
            'Categorie',
            'Fournisseur',
            'Date debut',
            'Date fin',
            'Statut',
            'Montant HT',
            'Montant TVA',
            'Montant TTC',
            'Nombre articles',
        ];
    }

    public function map($marche): array
    {
        return [
            $marche->reference,
            $marche->objet,
            $marche->categorie?->nom,
            $marche->fournisseur?->raison_sociale ?: $marche->fournisseur?->nom ?: 'Non attribue',
            optional($marche->date_debut)->format('d/m/Y'),
            optional($marche->date_fin)->format('d/m/Y'),
            $this->statusLabel($marche->statut),
            (float) $marche->articles->sum('montant_ht'),
            (float) $marche->articles->sum('montant_tva'),
            (float) $marche->articles->sum('montant_ttc'),
            $marche->articles->count(),
        ];
    }

    private function query(): Builder
    {
        $search = $this->filters['search'] ?? $this->filters['reference'] ?? $this->filters['objet'] ?? null;

        return BonCommande::query()
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('objet', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['statut'] ?? null, fn (Builder $query, $statut) => $query->where('statut', $statut))
            ->when($this->filters['categorie_id'] ?? null, fn (Builder $query, $categorieId) => $query->where('categorie_id', $categorieId))
            ->when($this->filters['fournisseur_id'] ?? null, fn (Builder $query, $fournisseurId) => $query->where('fournisseur_id', $fournisseurId))
            ->when($this->filters['date'] ?? null, function (Builder $query, $date) {
                $query->whereDate('date_debut', '<=', $date)
                    ->whereDate('date_fin', '>=', $date);
            });
    }

    private function statusLabel(?string $status): string
    {
        return [
            'cree' => 'Cree',
            'attente_livraison' => 'Attente livraison',
            'livre_partiellement' => 'Livre partiellement',
            'livre_completement' => 'Livre completement',
            'annule' => 'Annule',
        ][$status] ?? 'Inconnu';
    }
}
