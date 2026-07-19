<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Menu collectivité — {{ $menu->date->format('d/m/Y') }}</title>
    <style>
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }
        @page { margin-top: 120px; margin-bottom: 46px; margin-left: 20px; margin-right: 20px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #1a2233; background: #fff; line-height: 1.4; }

        .pdf-header { position: fixed; top: -120px; left: 0; right: 0; height: 113px; }
        .pdf-header-img { display: block; width: 100%; height: 106px; }
        .pdf-header-navy { height: 4px; background: #0c3260; }
        .pdf-header-gold { height: 3px; background: #b8963e; }

        .pdf-footer { position: fixed; bottom: -46px; left: 0; right: 0; height: 38px; border-top: 2px solid #0c3260; padding-top: 5px; font-size: 7px; color: #64748b; background: #fff; display: table; width: 100%; }
        .footer-left { display: table-cell; text-align: left; } .footer-left strong { color: #0c3260; }
        .footer-center { display: table-cell; text-align: center; color: #b8963e; font-weight: 700; }
        .footer-right { display: table-cell; text-align: right; }

        .doc-title-bar { text-align: center; padding: 6px 0; border-bottom: 2px solid #0c3260; margin-bottom: 8px; }
        .doc-label { font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: .6px; }
        .doc-title { font-size: 13px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: .8px; margin-top: 2px; }
        .doc-sub { font-size: 8px; color: #64748b; margin-top: 2px; }

        .meal-grid { display: table; width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .meal-col { display: table-cell; width: 33.33%; border: 1px solid #dce4ef; padding: 7px 9px; vertical-align: top; }
        .meal-title { font-size: 9px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #b8963e; padding-bottom: 3px; margin-bottom: 5px; }
        .meal-row { font-size: 8px; margin-bottom: 2px; }
        .meal-row b { color: #3d4f6a; }
        .eff-badge { display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 1px 7px; font-size: 7.5px; font-weight: 700; margin-top: 4px; }

        .info-line { font-size: 8px; margin-bottom: 8px; color: #3d4f6a; }
        .info-line strong { color: #0c3260; }

        table.qte { width: 100%; border-collapse: collapse; font-size: 7px; }
        table.qte th { background: #0c3260; color: #fff; padding: 3px 2px; font-weight: 700; border: 0.5px solid #071f3e; text-align: center; }
        table.qte td { padding: 3px 2px; border: 0.5px solid #cfdae8; text-align: center; }
        table.qte tbody tr:nth-child(even) td { background: #f4f7fb; }
        .cat-cell { background: #eef2f7 !important; font-weight: 700; color: #0c3260; }
        .tr-total td { background: #eef2f7; font-weight: 700; }
        .tr-ration td { background: #0c3260; color: #fff; font-weight: 700; }
        .body-wrap { padding-bottom: 48px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

<div class="pdf-header">
    @if(!empty($pdfHeaderSrc))<img class="pdf-header-img" src="{{ $pdfHeaderSrc }}" alt="ISTAHT Tanger">@endif
    <div class="pdf-header-navy"></div><div class="pdf-header-gold"></div>
</div>
<div class="pdf-footer">
    <div class="footer-left"><strong>ISTAHT Tanger</strong> — Restauration collective</div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

<div class="body-wrap">

<div class="doc-title-bar">
    <div class="doc-label">Document officiel — Institut Spécialisé de Technologie Appliquée Hôtelière et Touristique Tanger</div>
    <div class="doc-title">Menu Collectivité</div>
    <div class="doc-sub">Journée du {{ $menu->date->format('d/m/Y') }} — Responsable : {{ $menu->responsable }}</div>
</div>

@php
    $meals = [
        'petit-dejeuner' => ['Petit-déjeuner', $menu->effectif_petit_dejeuner ?? $menu->effectif],
        'dejeuner'       => ['Déjeuner',       $menu->effectif_dejeuner ?? $menu->effectif],
        'diner'          => ['Dîner',          $menu->effectif_diner ?? $menu->effectif],
    ];
@endphp

<div class="meal-grid">
    @foreach($meals as $key => [$label, $eff])
    <div class="meal-col">
        <div class="meal-title">{{ $label }}</div>
        <div class="meal-row"><b>Entrée :</b> {{ $repas[$key]['hors_doeuvre'] }}</div>
        <div class="meal-row"><b>Plat du jour :</b> {{ $repas[$key]['plat'] }}</div>
        <div class="meal-row"><b>Dessert :</b> {{ $repas[$key]['dessert'] }}</div>
        <div class="meal-row"><b>Plat spécial :</b> {{ $repas[$key]['plat_special'] }}</div>
        <div class="eff-badge">Effectif : {{ $eff }}</div>
    </div>
    @endforeach
</div>

<div class="section-title" style="font-size:8px;font-weight:800;color:#0c3260;text-transform:uppercase;border-left:3px solid #b8963e;padding-left:6px;margin-bottom:5px;">Détail des denrées &amp; quantités</div>

<table class="qte">
    <thead>
        <tr>
            <th rowspan="2" style="width:14%">Denrées &amp; nature</th>
            <th rowspan="2" style="width:6%">Code</th>
            <th colspan="4">Petit-déjeuner</th>
            <th colspan="4">Déjeuner</th>
            <th colspan="4">Dîner</th>
            <th rowspan="2" style="width:5%">Unité</th>
            <th rowspan="2" style="width:7%">P.U.</th>
            <th rowspan="2" style="width:8%">Total TTC</th>
        </tr>
        <tr>
            <th>H.œ</th><th>Plat</th><th>Des.</th><th>Spé.</th>
            <th>H.œ</th><th>Plat</th><th>Des.</th><th>Spé.</th>
            <th>H.œ</th><th>Plat</th><th>Des.</th><th>Spé.</th>
        </tr>
    </thead>
    <tbody>
        @php $total = 0; @endphp
        @foreach($data as $category => $articles)
            @foreach($articles as $i => $article)
                @php $total += $article['total_ttc']; @endphp
                <tr>
                    @if($i === 0)
                        <td class="cat-cell" rowspan="{{ count($articles) }}">{{ $category }}</td>
                    @endif
                    <td>{{ $article['article_code'] }}</td>
                    <td>{{ $article['quantites']['petit_dejeuner']['hors_doeuvres'] }}</td>
                    <td>{{ $article['quantites']['petit_dejeuner']['plats'] }}</td>
                    <td>{{ $article['quantites']['petit_dejeuner']['desserts'] }}</td>
                    <td>{{ $article['quantites']['petit_dejeuner']['plats_special'] }}</td>
                    <td>{{ $article['quantites']['dejeuner']['hors_doeuvres'] }}</td>
                    <td>{{ $article['quantites']['dejeuner']['plats'] }}</td>
                    <td>{{ $article['quantites']['dejeuner']['desserts'] }}</td>
                    <td>{{ $article['quantites']['dejeuner']['plats_special'] }}</td>
                    <td>{{ $article['quantites']['diner']['hors_doeuvres'] }}</td>
                    <td>{{ $article['quantites']['diner']['plats'] }}</td>
                    <td>{{ $article['quantites']['diner']['desserts'] }}</td>
                    <td>{{ $article['quantites']['diner']['plats_special'] }}</td>
                    <td>{{ $article['article_unite_mesure'] }}</td>
                    <td class="text-right">{{ number_format((float)$article['article_prix'], 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($article['total_ttc'], 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        @endforeach
        <tr class="tr-total">
            <td colspan="16" class="text-right">COÛT GLOBAL</td>
            <td class="text-right">{{ number_format($total, 2, ',', ' ') }} DH</td>
        </tr>
        <tr class="tr-ration">
            <td colspan="16" class="text-right">COÛT RATION (base déjeuner : {{ $menu->effectif_dejeuner ?? $menu->effectif ?? 1 }})</td>
            <td class="text-right">{{ number_format($total / max(($menu->effectif_dejeuner ?? $menu->effectif ?? 1), 1), 2, ',', ' ') }} DH</td>
        </tr>
    </tbody>
</table>

</div>
</body>
</html>
