<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche des entrées</title>
    <style>
        /* ATTENTION : ne JAMAIS mettre `html` ou `*` dans ce reset — DomPDF applique
           les marges @page via l'element racine html ; un margin:0 dessus les ecrase
           et le header fixe disparait. */
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }

        @page {
            margin-top:    120px;
            margin-bottom:  50px;
            margin-left:    24px;
            margin-right:   24px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px;
            color: #1a2233;
            background: #fff;
            line-height: 1.45;
        }

        /* ══ HEADER FIXE — répété sur CHAQUE page ══ */
        .pdf-header {
            position: fixed;
            top:   -120px;
            left:  0;
            right: 0;
            height: 113px;
        }
        .pdf-header-img { display: block; width: 100%; height: 106px; }
        .pdf-header-navy { height: 4px; background: #0c3260; }
        .pdf-header-gold { height: 3px; background: #b8963e; }

        /* ══ FOOTER FIXE ══ */
        .pdf-footer {
            position: fixed;
            bottom: -50px;
            left: 0; right: 0;
            height: 42px;
            border-top: 2px solid #0c3260;
            padding-top: 6px;
            font-size: 7.5px;
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
            font-size: 8px;
            margin-top: 8px;
        }
        table.entries thead th {
            background: #0c3260;
            color: #fff;
            padding: 4px 5px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 1px solid #071f3e;
            text-align: center;
        }
        table.entries tbody td {
            padding: 3.5px 5px;
            border: 1px solid #dce4ef;
            vertical-align: middle;
        }
        table.entries tbody tr:nth-child(even) td { background: #f4f7fb; }

        /* ══ SIGNATURE ══ */
        .signatures { display: table; width: 100%; margin-top: 30px; }
        .sig-spacer { display: table-cell; width: 60%; }
        .sig-cell   { display: table-cell; width: 40%; text-align: center; }
        .sig-label  { font-size: 9px; font-weight: 800; color: #0c3260; text-transform: uppercase; }
        .sig-line   { border-bottom: 1px dashed #0c3260; margin-top: 40px; padding-bottom: 4px; font-size: 7px; color: #64748b; }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .body-wrap   { padding-bottom: 55px; }
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
    <div class="doc-title">Fiche des Entrées en Stock</div>
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
    <span class="periode-badge" style="background:#fefce8;border-color:#fde68a;color:#854d0e">
        {{ count($articles) }} entrée(s)
    </span>
</div>

{{-- ════ TABLEAU ════ --}}
<table class="entries">
    <thead>
        <tr>
            <th style="width:8%">Date entrée</th>
            <th style="width:9%">Code article</th>
            <th style="width:23%">Désignation</th>
            <th style="width:6%">Unité</th>
            <th style="width:8%">Stock initial</th>
            <th style="width:8%">Qté entrée</th>
            <th style="width:9%">Réf. BR</th>
            <th style="width:8%">Stock actuel</th>
            <th style="width:9%">P.U. (DH)</th>
            <th style="width:5%">TVA</th>
            <th style="width:10%">Montant TTC</th>
        </tr>
    </thead>
    <tbody>
        @forelse($articles as $article)
            <tr>
                <td class="text-center">{{ $article['date_entree'] }}</td>
                <td class="text-center" style="font-weight:700;color:#1d4ed8">{{ $article['code_article'] }}</td>
                <td class="text-left"><strong style="color:#0c3260">{{ $article['designation_article'] }}</strong></td>
                <td class="text-center">{{ $article['unite'] }}</td>
                <td class="text-right">{{ $article['stock_initial'] }}</td>
                <td class="text-right" style="font-weight:700;color:#15803d">+{{ $article['quantite_entree'] }}</td>
                <td class="text-center">{{ $article['reference_bon_reception'] }}</td>
                <td class="text-right" style="font-weight:700">{{ $article['stock_actuel'] }}</td>
                <td class="text-right">{{ number_format((float) $article['prix_unitaire'], 2, ',', ' ') }}</td>
                <td class="text-center">{{ $article['tva_appliquee'] }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format((float) $article['total_ttc'], 2, ',', ' ') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center" style="padding:14px;color:#64748b;font-style:italic">
                    Aucune entrée en stock sur cette période.
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
