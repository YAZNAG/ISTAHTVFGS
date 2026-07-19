<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Menus collectivité — {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}</title>
    <style>
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }
        @page { margin-top: 120px; margin-bottom: 46px; margin-left: 22px; margin-right: 22px; }
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

        table.menus { width: 100%; border-collapse: collapse; font-size: 7.5px; }
        table.menus th { background: #0c3260; color: #fff; padding: 4px 3px; font-weight: 700; border: 0.5px solid #071f3e; text-align: center; }
        table.menus td { padding: 3px 3px; border: 0.5px solid #cfdae8; text-align: center; }
        table.menus tbody tr:nth-child(even) td { background: #f4f7fb; }
        .date-cell { font-weight: 700; color: #0c3260; background: #eef2f7 !important; }
        .body-wrap { padding-bottom: 48px; }
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
    <div class="doc-title">Planning des Menus Collectivité</div>
    <div class="doc-sub">Du {{ $startDate->format('d/m/Y') }} au {{ $endDate->format('d/m/Y') }}</div>
</div>

<table class="menus">
    <thead>
        <tr>
            <th rowspan="2" style="width:8%">Date</th>
            <th colspan="4">Petit-déjeuner</th>
            <th colspan="4">Déjeuner</th>
            <th colspan="4">Dîner</th>
        </tr>
        <tr>
            <th>Entrée</th><th>Plat</th><th>Dessert</th><th>Spécial</th>
            <th>Entrée</th><th>Plat</th><th>Dessert</th><th>Spécial</th>
            <th>Entrée</th><th>Plat</th><th>Dessert</th><th>Spécial</th>
        </tr>
    </thead>
    <tbody>
        @forelse($menus as $menu)
            <tr>
                <td class="date-cell">{{ $menu['date']->format('d/m/Y') }}</td>
                <td>{{ $menu['petit_dejeuner']['hors_doeuvres'] }}</td>
                <td>{{ $menu['petit_dejeuner']['plat'] }}</td>
                <td>{{ $menu['petit_dejeuner']['dessert'] }}</td>
                <td>{{ $menu['petit_dejeuner']['plat_special'] ?? '—' }}</td>
                <td>{{ $menu['dejeuner']['hors_doeuvres'] }}</td>
                <td>{{ $menu['dejeuner']['plat'] }}</td>
                <td>{{ $menu['dejeuner']['dessert'] }}</td>
                <td>{{ $menu['dejeuner']['plat_special'] ?? '—' }}</td>
                <td>{{ $menu['diner']['hors_doeuvres'] }}</td>
                <td>{{ $menu['diner']['plat'] }}</td>
                <td>{{ $menu['diner']['dessert'] }}</td>
                <td>{{ $menu['diner']['plat_special'] ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="13" style="padding:14px;color:#64748b;font-style:italic">Aucun menu sur cette période.</td></tr>
        @endforelse
    </tbody>
</table>

</div>
</body>
</html>
