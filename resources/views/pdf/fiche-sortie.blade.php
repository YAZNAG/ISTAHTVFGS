<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche des sorties</title>
    <style>
        /* ATTENTION : ne JAMAIS mettre `html` ou `*` dans ce reset — DomPDF applique
           les marges @page via l'element racine html ; un margin:0 dessus les ecrase
           et le header fixe disparait. */
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }

        @page {
            margin-top:    130px;
            margin-bottom:  52px;
            margin-left:    28px;
            margin-right:   28px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9.5px;
            color: #1a2233;
            background: #fff;
            line-height: 1.5;
        }

        /* ══ HEADER FIXE — répété sur CHAQUE page ══ */
        .pdf-header {
            position: fixed;
            top:   -130px;
            left:  0;
            right: 0;
            height: 123px;
        }
        .pdf-header-img { display: block; width: 100%; height: 116px; }
        .pdf-header-navy { height: 4px; background: #0c3260; }
        .pdf-header-gold { height: 3px; background: #b8963e; }

        /* ══ FOOTER FIXE ══ */
        .pdf-footer {
            position: fixed;
            bottom: -52px;
            left: 0; right: 0;
            height: 44px;
            border-top: 2px solid #0c3260;
            padding-top: 6px;
            font-size: 8px;
            color: #64748b;
            background: #fff;
            display: table;
            width: 100%;
        }
        .footer-left   { display: table-cell; text-align: left; }
        .footer-left strong { color: #0c3260; }
        .footer-center { display: table-cell; text-align: center; color: #b8963e; font-weight: 700; }
        .footer-right  { display: table-cell; text-align: right; }

        /* ══ TITRE ══ */
        .doc-title-bar {
            text-align: center;
            padding: 8px 0 8px;
            border-bottom: 2px solid #0c3260;
            margin-bottom: 10px;
        }
        .doc-label { font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: .8px; }
        .doc-title { font-size: 13px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: 1px; margin-top: 3px; }
        .doc-sub   { font-size: 8px; color: #64748b; margin-top: 3px; }

        /* ══ BADGES ══ */
        .periode-badge {
            display: inline-block;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 3px 10px;
            font-size: 8.5px;
            font-weight: 700;
            margin-bottom: 4px;
            margin-right: 6px;
        }
        .cat-badge {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 3px 10px;
            font-size: 8.5px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        /* ══ TABLEAU ══ */
        table.entries {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 8px;
        }
        table.entries thead th {
            background: #0c3260;
            color: #fff;
            padding: 5px 6px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 1px solid #071f3e;
            text-align: center;
        }
        table.entries tbody td {
            padding: 4px 6px;
            border: 1px solid #dce4ef;
            vertical-align: middle;
        }
        table.entries tbody tr:nth-child(even) td { background: #f4f7fb; }

        /* ══ SIGNATURE ══ */
        .signatures { display: table; width: 100%; margin-top: 32px; }
        .sig-spacer { display: table-cell; width: 55%; }
        .sig-cell   { display: table-cell; width: 45%; text-align: center; }
        .sig-label  { font-size: 9px; font-weight: 800; color: #0c3260; text-transform: uppercase; }
        .sig-line   { border-bottom: 1px dashed #0c3260; margin-top: 42px; padding-bottom: 4px; font-size: 7.5px; color: #64748b; }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .body-wrap   { padding-bottom: 58px; }
    </style>
</head>
<body>

{{-- ════ HEADER IMAGE — position:fixed → répété sur chaque page ════ --}}
<div class="pdf-header">
    @if(!empty($pdfHeaderSrc))
        <img class="pdf-header-img" src="{{ $pdfHeaderSrc }}" alt="ISTAHT Tanger">
    @endif
    <div class="pdf-header-navy"></div>
    <div class="pdf-header-gold"></div>
</div>

{{-- ════ FOOTER FIXE ════ --}}
<div class="pdf-footer">
    <div class="footer-left"><strong>ISTAHT Tanger</strong> — Gestion des stocks &amp; restauration collective</div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

<div class="body-wrap">

{{-- ════ TITRE ════ --}}
<div class="doc-title-bar">
    <div class="doc-label">Document officiel — Institut Spécialisé de Technologie Appliquée Hôtelière et Touristique Tanger</div>
    <div class="doc-title">Fiche des Sorties de Stock</div>
    <div class="doc-sub">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

{{-- ════ PÉRIODE + CATÉGORIE ════ --}}
<div>
    <span class="periode-badge">
        Période :
        du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
    </span>
    @if(!empty($categorie))
        <span class="cat-badge">Catégorie : {{ $categorie }}</span>
    @else
        <span class="cat-badge">Toutes les catégories</span>
    @endif
    <span class="periode-badge" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
        {{ count($articles) }} sortie(s)
    </span>
</div>

{{-- ════ TABLEAU ════ --}}
<table class="entries">
    <thead>
        <tr>
            <th style="width:11%">Date sortie</th>
            <th style="width:12%">Code article</th>
            <th style="width:33%">Désignation</th>
            <th style="width:8%">Unité</th>
            <th style="width:12%">Qté sortie</th>
            <th style="width:12%">Réf. bon sortie</th>
            <th style="width:12%">Stock actuel</th>
        </tr>
    </thead>
    <tbody>
        @forelse($articles as $article)
            <tr>
                <td class="text-center">{{ $article['date_sortie'] }}</td>
                <td class="text-center" style="font-weight:700;color:#1d4ed8">{{ $article['code_article'] }}</td>
                <td class="text-left"><strong style="color:#0c3260">{{ $article['designation_article'] }}</strong></td>
                <td class="text-center">{{ $article['unite'] }}</td>
                <td class="text-right" style="font-weight:700;color:#b91c1c">−{{ $article['quantite_sortie'] }}</td>
                <td class="text-center">{{ $article['reference_bon_sortie'] }}</td>
                <td class="text-right" style="font-weight:700">{{ $article['stock_actuel'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center" style="padding:14px;color:#64748b;font-style:italic">
                    Aucune sortie de stock sur cette période.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- ════ SIGNATURE ════ --}}
<div class="signatures">
    <div class="sig-spacer"></div>
    <div class="sig-cell">
        <div class="sig-label">Le Responsable</div>
        <div class="sig-line">Nom &amp; Signature</div>
    </div>
</div>

</div>{{-- /body-wrap --}}
</body>
</html>
