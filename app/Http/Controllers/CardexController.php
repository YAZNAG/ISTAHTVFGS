<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class CardexController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_raports', only: ['create', 'export']),
        ];
    }

    
    public function create(Request $request) {
        $articles = Article::all(['id', 'designation']);
        return Inertia::render('Cardex/Create', ['articles' => $articles]);
    }

    public function export(Request $request, Article $article)
    {
        $year = $request->input('year', now()->year);

        $months = [
            1 => 'JANVIER',
            2 => 'FEVRIER',
            3 => 'MARS',
            4 => 'AVRIL',
            5 => 'MAI',
            6 => 'JUIN',
            7 => 'JUILLET',
            8 => 'AOUT',
            9 => 'SEPTEMBRE',
            10 => 'OCTOBRE',
            11 => 'NOVEMBRE',
            12 => 'DECEMBRE'
        ];

        // 1 query for the whole year, grouped by month+day
        $typeEntree = MouvementStock::TYPE_ENTREE;
        $typeSortie = MouvementStock::TYPE_SORTIE;

        $movements = MouvementStock::parArticle($article->id)
            ->whereYear('date_mouvement', $year)
            ->selectRaw(
                'MONTH(date_mouvement) as mois, DAY(date_mouvement) as jour,
                 SUM(CASE WHEN type = ? THEN quantite_entree ELSE 0 END) as entrees,
                 SUM(CASE WHEN type = ? THEN quantite_sortie ELSE 0 END) as sorties',
                [$typeEntree, $typeSortie]
            )
            ->groupBy(\Illuminate\Support\Facades\DB::raw('MONTH(date_mouvement)'), \Illuminate\Support\Facades\DB::raw('DAY(date_mouvement)'))
            ->orderBy(\Illuminate\Support\Facades\DB::raw('MONTH(date_mouvement)'))
            ->orderBy(\Illuminate\Support\Facades\DB::raw('DAY(date_mouvement)'))
            ->get()
            ->keyBy(fn ($r) => "{$r->mois}-{$r->jour}");

        $cardex = [];
        $monthTotals = [];
        $stockPreviousDay = 0;

        for ($month = 1; $month <= 12; $month++) {
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $totalEntrees = 0;
            $totalSorties = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $row = $movements->get("{$month}-{$day}");
                $entrees = $row ? (float) $row->entrees : 0;
                $sorties = $row ? (float) $row->sorties : 0;
                $stockFinal = $stockPreviousDay + $entrees - $sorties;

                $cardex[$month][$day] = [
                    'entrees' => $entrees,
                    'sorties' => $sorties,
                    'stock_final' => $stockFinal,
                ];

                $totalEntrees += $entrees;
                $totalSorties += $sorties;
                $stockPreviousDay = $stockFinal;
            }

            $monthTotals[$month] = [
                'total_entrees' => $totalEntrees,
                'total_sorties' => $totalSorties,
                'stock_final' => $stockPreviousDay,
            ];
        }

        // === Split months and days for the PDF structure ===
        $firstHalfMonths = array_slice($months, 0, 6, true);  // 1 → 6
        $secondHalfMonths = array_slice($months, 6, 6, true); // 7 → 12

        // Each “section” for one page of the PDF
        $pages = [
            [
                'months' => $firstHalfMonths,
                'days' => range(1, 15),
            ],
            [
                'months' => $firstHalfMonths,
                'days' => range(16, 31),
            ],
            [
                'months' => $secondHalfMonths,
                'days' => range(1, 15),
            ],
            [
                'months' => $secondHalfMonths,
                'days' => range(16, 31),
            ],
        ];

        $fileName = "cardex-{$article->reference}-{$year}.pdf";

        return Pdf::loadView('pdf.cardex', [
            'article'      => $article,
            'year'         => $year,
            'cardex'       => $cardex,
            'monthTotals'  => $monthTotals,
            'pages'        => $pages,
            'pdfHeaderSrc' => $this->pdfHeaderBase64(),
        ])
            ->setPaper('a4', 'landscape')
            ->download($fileName);
    }
}
