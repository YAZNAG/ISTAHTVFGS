<?php

namespace App\Exports;

use App\Models\Categorie;
use App\Exports\Concerns\WithIstahtHeader;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    use WithIstahtHeader;

    public function collection()
    {
        return Categorie::query()
            ->withCount([
                'articles as articles_count' => fn ($query) => $query->withNonExists(),
            ])
            ->orderBy('nom')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Nom',
            'Statut',
            'Nombre articles',
            'Date creation',
        ];
    }

    public function map($categorie): array
    {
        return [
            $categorie->code,
            $categorie->nom,
            $categorie->est_actif ? 'Actif' : 'Inactif',
            (int) $categorie->articles_count,
            optional($categorie->created_at)->format('d/m/Y'),
        ];
    }
}
