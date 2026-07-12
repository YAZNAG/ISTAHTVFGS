<?php

namespace App\Exports;

use App\Models\Decompte;
use App\Exports\Concerns\WithIstahtHeader;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DecompteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    use WithIstahtHeader;

    public function __construct(protected Decompte $decompte)
    {
    }

    public function collection()
    {
        return $this->decompte->items;
    }

    public function headings(): array
    {
        return [
            'Marche',
            'Categorie',
            'Article',
            'Unite',
            'Quantite livree',
            'Prix unitaire',
            'Montant HT',
            'TVA',
            'Montant TVA',
            'Montant TTC',
        ];
    }

    public function map($item): array
    {
        return [
            $this->decompte->marche?->reference,
            $item->article?->categorie?->nom,
            $item->article?->designation,
            $item->article?->unite_mesure,
            (float) $item->quantite,
            (float) $item->prix_unitaire,
            (float) $item->montant_ht,
            (float) $item->taux_tva,
            (float) $item->montant_tva,
            (float) $item->montant_ttc,
        ];
    }
}
