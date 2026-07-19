<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Cardex {{ $article->reference ?? '' }} — {{ $year }}</title>
    <style>
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }
        @page { margin-top: 118px; margin-bottom: 42px; margin-left: 20px; margin-right: 20px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #1a2233; background: #fff; line-height: 1.4; }

        .pdf-header { position: fixed; top: -118px; left: 0; right: 0; height: 111px; }
        .pdf-header-img { display: block; width: 100%; height: 104px; }
        .pdf-header-navy { height: 4px; background: #0c3260; }
        .pdf-header-gold { height: 3px; background: #b8963e; }

        .pdf-footer { position: fixed; bottom: -42px; left: 0; right: 0; height: 34px; border-top: 2px solid #0c3260; padding-top: 5px; font-size: 7px; color: #64748b; background: #fff; display: table; width: 100%; }
        .footer-left { display: table-cell; text-align: left; } .footer-left strong { color: #0c3260; }
        .footer-center { display: table-cell; text-align: center; color: #b8963e; font-weight: 700; }
        .footer-right { display: table-cell; text-align: right; }

        .doc-title-bar { text-align: center; padding: 6px 0 6px; border-bottom: 2px solid #0c3260; margin-bottom: 6px; }
        .doc-label { font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: .6px; }
        .doc-title { font-size: 12px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: .8px; margin-top: 2px; }
        .doc-sub { font-size: 8px; color: #64748b; margin-top: 2px; }

        table.cardex { width: 100%; border-collapse: collapse; font-size: 7.5px; }
        table.cardex thead th { background: #0c3260; color: #fff; padding: 3px 2px; font-weight: 700; border: 0.5px solid #071f3e; text-align: center; text-transform: uppercase; letter-spacing: .2px; }
        table.cardex thead tr:nth-child(2) th { background: #1e3a5f; font-size: 6.5px; }
        table.cardex tbody td { padding: 2.5px 2px; border: 0.5px solid #cfdae8; text-align: right; }
        table.cardex tbody td.jour { text-align: center; font-weight: 700; color: #0c3260; background: #eef2f7; }
        table.cardex tbody tr:nth-child(even) td { background: #f4f7fb; }
        table.cardex tbody tr:nth-child(even) td.jour { background: #e4ebf3; }
        table.cardex tfoot td { background: #0c3260; color: #fff; font-weight: 700; padding: 3px 2px; border: 0.5px solid #071f3e; text-align: right; }
        table.cardex tfoot td.jour { text-align: center; }
        .col-stock { color: #0c3260; font-weight: 700; }
    </style>
</head>
<body>

<div class="pdf-header">
    @if(!empty($pdfHeaderSrc))<img class="pdf-header-img" src="{{ $pdfHeaderSrc }}" alt="ISTAHT Tanger">@endif
    <div class="pdf-header-navy"></div><div class="pdf-header-gold"></div>
</div>
<div class="pdf-footer">
    <div class="footer-left"><strong>ISTAHT Tanger</strong> — Fiche de stock (Cardex)</div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

@foreach ($pages as $i => $page)
    <div @if($i > 0) style="page-break-before: always;" @endif>
        <div class="doc-title-bar">
            <div class="doc-label">Document officiel — Institut Spécialisé de Technologie Appliquée Hôtelière et Touristique Tanger</div>
            <div class="doc-title">Fiche de Stock — Cardex {{ $year }}</div>
            <div class="doc-sub">
                {{ $article->reference ? $article->reference . ' — ' : '' }}{{ $article->designation }}
                &nbsp;·&nbsp; jours {{ min($page['days']) }} à {{ max($page['days']) }}
            </div>
        </div>

        <table class="cardex">
            <thead>
                <tr>
                    <th rowspan="2" style="width:4%">Jour</th>
                    @foreach ($page['months'] as $monthNumber => $monthName)
                        <th colspan="3">{{ $monthName }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($page['months'] as $monthNumber => $monthName)
                        <th>Entrées</th><th>Sorties</th><th>Stock</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($page['days'] as $day)
                    <tr>
                        <td class="jour">{{ $day }}</td>
                        @foreach ($page['months'] as $monthNumber => $monthName)
                            @php $d = $cardex[$monthNumber][$day] ?? null; @endphp
                            <td>{{ $d && $d['entrees'] > 0 ? number_format($d['entrees'], 2, ',', ' ') : '' }}</td>
                            <td>{{ $d && $d['sorties'] > 0 ? number_format($d['sorties'], 2, ',', ' ') : '' }}</td>
                            <td class="col-stock">{{ $d ? number_format($d['stock_final'], 2, ',', ' ') : '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            @if (max($page['days']) >= 30)
                <tfoot>
                    <tr>
                        <td class="jour">Totaux</td>
                        @foreach ($page['months'] as $monthNumber => $monthName)
                            @php $t = $monthTotals[$monthNumber] ?? ['total_entrees'=>0,'total_sorties'=>0,'stock_final'=>0]; @endphp
                            <td>{{ number_format($t['total_entrees'], 2, ',', ' ') }}</td>
                            <td>{{ number_format($t['total_sorties'], 2, ',', ' ') }}</td>
                            <td>{{ number_format($t['stock_final'], 2, ',', ' ') }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endforeach

</body>
</html>
