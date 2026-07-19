<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Réception — {{ $reception->numero }}</title>
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
            font-size: 10px;
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
            padding: 10px 0 8px;
            border-bottom: 2px solid #0c3260;
            margin-bottom: 14px;
        }
        .doc-label { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: .8px; }
        .doc-title { font-size: 14px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: 1px; margin-top: 3px; }
        .doc-sub   { font-size: 8.5px; color: #64748b; margin-top: 4px; }

        /* ══ BLOC INFO ══ */
        .info-grid         { display: table; width: 100%; margin-bottom: 14px; border: 1px solid #dce4ef; }
        .info-col          { display: table-cell; width: 50%; padding: 9px 12px; vertical-align: top; }
        .info-col-right    { border-left: 1px solid #dce4ef; }
        .info-section-title {
            font-size: 7.5px; font-weight: 800; text-transform: uppercase;
            letter-spacing: .7px; color: #0c3260;
            border-bottom: 1px solid #dce4ef;
            padding-bottom: 4px; margin-bottom: 6px;
        }
        .info-row { display: table; width: 100%; margin-bottom: 3px; }
        .info-lbl { display: table-cell; width: 45%; font-size: 8.5px; font-weight: 700; color: #3d4f6a; }
        .info-val { display: table-cell; font-size: 9px; color: #1a2233; }

        /* ══ BADGE BL LIÉ ══ */
        .bl-badge {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 4px 12px;
            font-size: 9px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        /* ══ SECTION TITLE ══ */
        .section-title {
            font-size: 9px; font-weight: 800; color: #0c3260;
            text-transform: uppercase; letter-spacing: .5px;
            border-left: 3px solid #b8963e;
            padding-left: 7px;
            margin-bottom: 8px; margin-top: 14px;
        }

        /* ══ TABLEAU ARTICLES ══ */
        table.articles {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 6px;
        }
        table.articles thead th {
            background: #0c3260;
            color: #fff;
            padding: 5px 8px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 1px solid #071f3e;
        }
        table.articles thead th.r { text-align: right; }
        table.articles thead th.c { text-align: center; }
        table.articles tbody td {
            padding: 5px 8px;
            border: 1px solid #dce4ef;
            vertical-align: middle;
        }
        table.articles tbody tr:nth-child(even) td { background: #f4f7fb; }

        /* ══ SIGNATURES ══ */
        .signatures { display: table; width: 100%; margin-top: 36px; }
        .sig-cell   { display: table-cell; width: 33%; text-align: center; padding: 0 16px; }
        .sig-label  { font-size: 9.5px; font-weight: 800; color: #0c3260; text-transform: uppercase; }
        .sig-line   { border-bottom: 1px dashed #0c3260; margin-top: 44px; padding-bottom: 4px; font-size: 7.5px; color: #64748b; }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .body-wrap   { padding-bottom: 60px; }
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
    <div class="footer-left"><strong>ISTAHT Tanger</strong> — Gestion des marchés &amp; achats</div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

<div class="body-wrap">

{{-- ════ TITRE ════ --}}
<div class="doc-title-bar">
    <div class="doc-label">Document officiel — Institut Spécialisé de Technologie Appliquée Hôtelière et Touristique Tanger</div>
    <div class="doc-title">Bon de Réception N° {{ $reception->numero }}</div>
    <div class="doc-sub">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

@php
    $bl = $reception->bonLivraison;
    $f  = $bl?->fournisseur;
@endphp

{{-- ════ LIEN BL ════ --}}
<div class="bl-badge">
    Réception liée au bon de livraison N° <strong>{{ $bl?->numero ?? '—' }}</strong>
    @if($bl?->date_livraison)
        — livré le {{ \Carbon\Carbon::parse($bl->date_livraison)->format('d/m/Y') }}
    @endif
</div>

{{-- ════ BLOC FOURNISSEUR + DÉTAILS ════ --}}
<div class="info-grid">
    <div class="info-col">
        <div class="info-section-title">Fournisseur</div>
        <div class="info-row">
            <div class="info-lbl">Raison sociale :</div>
            <div class="info-val"><strong>{{ $f->raison_sociale ?? $f->nom ?? '—' }}</strong></div>
        </div>
        @if(!empty($f?->adresse))
        <div class="info-row">
            <div class="info-lbl">Adresse :</div>
            <div class="info-val">{{ $f->adresse }}</div>
        </div>
        @endif
    </div>
    <div class="info-col info-col-right">
        <div class="info-section-title">Détails de la réception</div>
        <div class="info-row">
            <div class="info-lbl">N° réception :</div>
            <div class="info-val"><strong>{{ $reception->numero }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Date de réception :</div>
            <div class="info-val">{{ optional($reception->created_at)->format('d/m/Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Bon de livraison :</div>
            <div class="info-val">{{ $bl?->numero ?? '—' }}</div>
        </div>
    </div>
</div>

{{-- ════ TABLEAU ARTICLES (sans montants) ════ --}}
<div class="section-title">Articles réceptionnés</div>

<table class="articles">
    <thead>
        <tr>
            <th style="width:6%"  class="c">N°</th>
            <th style="width:14%" class="c">Code</th>
            <th style="width:50%">Désignation</th>
            <th style="width:14%" class="c">Unité</th>
            <th style="width:16%" class="r">Quantité reçue</th>
        </tr>
    </thead>
    <tbody>
        @forelse(($bl?->items ?? []) as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->article->reference ?? '—' }}</td>
                <td><strong style="color:#0c3260">{{ $item->article->designation ?? '—' }}</strong></td>
                <td class="text-center">{{ $item->article->unite_mesure ?? '—' }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format((float) $item->quantite, 2, ',', ' ') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center" style="padding:14px;color:#64748b;font-style:italic">
                    Aucun article réceptionné.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- ════ SIGNATURES ════ --}}
<div class="signatures">
    <div class="sig-cell">
        <div class="sig-label">Le Magasinier</div>
        <div class="sig-line">Nom &amp; Signature</div>
    </div>
    <div class="sig-cell">
        <div class="sig-label">L'Économe</div>
        <div class="sig-line">Nom &amp; Signature</div>
    </div>
    <div class="sig-cell">
        <div class="sig-label">Le Directeur</div>
        <div class="sig-line">Cachet &amp; Signature</div>
    </div>
</div>

</div>{{-- /body-wrap --}}
</body>
</html>
