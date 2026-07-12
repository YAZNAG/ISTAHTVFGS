<?php

namespace App\Exports\Concerns;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

trait WithIstahtHeader
{
    /**
     * Nombre de colonnes du tableau (à surcharger si besoin).
     */
    protected function columnCount(): int
    {
        return count($this->headings());
    }

    /**
     * Lettre Excel de la dernière colonne (A=1, B=2, ...).
     */
    protected function lastColumn(): string
    {
        $n = $this->columnCount();
        $letters = '';
        while ($n > 0) {
            $n--;
            $letters = chr(65 + ($n % 26)) . $letters;
            $n = intdiv($n, 26);
        }
        return $letters;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $this->lastColumn();

                // ── 1. Insérer 7 lignes en haut pour l'image header ──
                $sheet->insertNewRowBefore(1, 7);

                // ── 2. Fusionner la zone header sur toute la largeur ──
                $sheet->mergeCells("A1:{$lastCol}7");

                // ── 3. Insérer l'image header ──
                $headerPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path('images/pdf-header.jpg'));
                if (file_exists($headerPath)) {
                    $drawing = new Drawing();
                    $drawing->setName('ISTAHT Header');
                    $drawing->setDescription('En-tête officiel ISTAHT Tanger');
                    $drawing->setPath($headerPath);
                    $drawing->setHeight(115);
                    $drawing->setOffsetX(2);
                    $drawing->setOffsetY(2);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                }

                // ── 4. Ligne séparatrice navy sous le header ──
                $sheet->getRowDimension(7)->setRowHeight(3);
                $sheet->getStyle("A7:{$lastCol}7")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0C3260'],
                    ],
                ]);

                // ── 5. Ligne or sous la ligne navy ──
                $sheet->insertNewRowBefore(8, 1);
                $sheet->mergeCells("A8:{$lastCol}8");
                $sheet->getRowDimension(8)->setRowHeight(3);
                $sheet->getStyle("A8:{$lastCol}8")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'B8963E'],
                    ],
                ]);

                // ── 6. Style des en-têtes colonnes (ligne 9) ──
                $headingRow = 9;
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size'  => 10,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0C3260'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '071F3E'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(22);

                // ── 7. Style des lignes de données ──
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > $headingRow) {
                    $dataRange = "A" . ($headingRow + 1) . ":{$lastCol}{$highestRow}";
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'DCE4EF'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Lignes paires en gris clair
                    for ($row = $headingRow + 1; $row <= $highestRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType'   => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F4F7FB'],
                                ],
                            ]);
                        }
                        $sheet->getRowDimension($row)->setRowHeight(16);
                    }
                }

                // ── 8. Hauteur image ──
                for ($r = 1; $r <= 6; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(19);
                }

                // ── 9. Freeze pane sous l'en-tête ──
                $sheet->freezePane("A" . ($headingRow + 1));
            },
        ];
    }
}
