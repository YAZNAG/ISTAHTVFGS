<?php

namespace App\Exports;

use App\Models\Fournisseur;
use App\Exports\Concerns\WithIstahtHeader;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FournisseursExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithEvents
{
    use WithIstahtHeader;

    public function __construct(private array $filters = [])
    {
    }

    public function collection()
    {
        return $this->query()
            ->withCount(['bonCommandes as marches_count'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Raison sociale',
            'Contact',
            'Telephone',
            'Email',
            'Ville',
            'ICE',
            'Statut',
            'Nombre de marches',
        ];
    }

    public function map($fournisseur): array
    {
        return [
            $fournisseur->nom,
            $fournisseur->raison_sociale,
            $fournisseur->contact,
            $fournisseur->telephone,
            $fournisseur->email,
            $fournisseur->ville,
            $fournisseur->ice,
            $fournisseur->est_actif ? 'Actif' : 'Inactif',
            (int) $fournisseur->marches_count,
        ];
    }

    private function query(): Builder
    {
        $search = $this->filters['search'] ?? null;

        return Fournisseur::query()
            ->select([
                'id',
                'nom',
                'raison_sociale',
                'contact',
                'telephone',
                'email',
                'ville',
                'ice',
                'est_actif',
            ])
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nom', 'like', "%{$search}%")
                        ->orWhere('raison_sociale', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%")
                        ->orWhere('telephone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('ville', 'like', "%{$search}%")
                        ->orWhere('ice', 'like', "%{$search}%");
                });
            })
            ->when(($this->filters['statut'] ?? null) === 'actifs', fn (Builder $query) => $query->where('est_actif', true))
            ->when(($this->filters['statut'] ?? null) === 'inactifs', fn (Builder $query) => $query->where('est_actif', false))
            ->orderBy('raison_sociale')
            ->orderBy('nom');
    }
}
