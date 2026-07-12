<?php

namespace App\Exports;

use App\Exports\Concerns\WithIstahtHeader;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EntreeExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    use WithIstahtHeader;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            "Date d'entree",
            "Code d'article",
            "Designation d'article",
            "Stock initial",
            "Quantite entree",
            "Reference du bon de reception",
            "Stock actuel",
            "Unite",
            "Prix unitaire (Dhs)",
            "TVA applique",
            "Montant total",
        ];
    }
}
