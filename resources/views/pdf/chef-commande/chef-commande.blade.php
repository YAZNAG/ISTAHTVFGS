<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de commande N° {{ $chefCommande->numero }}</title>
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
        .pdf-header-img {
            display: block;
            width: 100%;
            height: 116px;
        }
        .pdf-header-navy { height: 4px; background: #0c3260; }
        .pdf-header-gold { height: 3px; background: #b8963e; }

        /* ══ FOOTER FIXE ══ */
        .pdf-footer {
            position: fixed;
            bottom: -52px;
            left:  0;
            right: 0;
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

        /* ══ TITRE DOCUMENT ══ */
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
        .info-lbl { display: table-cell; width: 38%; font-size: 8.5px; font-weight: 700; color: #3d4f6a; }
        .info-val { display: table-cell; font-size: 9px; color: #1a2233; }

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
            font-size: 8.5px;
            margin-bottom: 6px;
        }
        table.articles thead th {
            background: #0c3260;
            color: #fff;
            padding: 5px 6px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 1px solid #071f3e;
        }
        table.articles thead th.r { text-align: right; }
        table.articles thead th.c { text-align: center; }
        table.articles tbody td {
            padding: 4px 6px;
            border: 1px solid #dce4ef;
            vertical-align: middle;
        }
        table.articles tbody tr:nth-child(even) td { background: #f4f7fb; }
        table.articles tfoot td {
            padding: 5px 6px;
            border: 1px solid #c5d3e4;
            font-weight: 700;
        }
        .tr-total-ht  td { background: #eef2f7; }
        .tr-total-tva td { background: #eef2f7; }
        .tr-grand-ttc td { background: #0c3260; color: #fff; font-size: 10px; border-color: #071f3e; }

        /* ══ NOTE ══ */
        .note-box {
            border: 1px solid #dce4ef;
            background: #f8fafc;
            padding: 8px 12px;
            margin-top: 12px;
            font-size: 9px;
            color: #475569;
        }
        .note-box strong { color: #0c3260; }

        /* ══ ZONE SIGNATURE ══ */
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

{{-- ════ TITRE DOCUMENT ════ --}}
<div class="doc-title-bar">
    <div class="doc-label">Document officiel — Institut Spécialisé de Technologie Appliquée Hôtelière et Touristique Tanger</div>
    <div class="doc-title">Bon de Commande N° {{ $chefCommande->numero }}</div>
    <div class="doc-sub">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

{{-- ════ BLOC INFOS ════ --}}
<div class="info-grid">
    <div class="info-col">
        <div class="info-section-title">Informations de la commande</div>
        <div class="info-row">
            <div class="info-lbl">Numéro :</div>
            <div class="info-val"><strong>{{ $chefCommande->numero }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Date de création :</div>
            <div class="info-val">{{ optional($chefCommande->created_at)->format('d/m/Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Catégorie :</div>
            <div class="info-val">{{ $chefCommande->categorie?->nom ?? '—' }}</div>
        </div>
    </div>
    <div class="info-col info-col-right">
        <div class="info-section-title">Demandeur</div>
        <div class="info-row">
            <div class="info-lbl">Nom :</div>
            <div class="info-val"><strong>{{ $chefCommande->user?->name ?? '—' }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Nombre d'articles :</div>
            <div class="info-val">{{ $chefCommande->items->count() }}</div>
        </div>
    </div>
</div>

{{-- ════ TABLEAU ARTICLES ════ --}}
<div class="section-title">Articles commandés</div>

@php $totalHT = 0; $totalTVA = 0; $totalTTC = 0; @endphp

<table class="articles">
    <thead>
        <tr>
            <th style="width:4%"  class="c">N°</th>
            <th style="width:11%" class="c">Code</th>
            <th style="width:29%">Désignation</th>
            <th style="width:8%"  class="c">Unité</th>
            <th style="width:10%" class="r">Quantité</th>
            <th style="width:12%" class="r">P.U. HT (DH)</th>
            <th style="width:8%"  class="c">TVA %</th>
            <th style="width:18%" class="r">Montant TTC (DH)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($chefCommande->items as $item)
            @php
                $prixHT   = (float) ($item->article->currentBonCommandeArticle->prix_unitaire_ht ?? 0);
                $quantite = (float) ($item->quantite_commandee ?? 0);
                $tauxTVA  = (float) ($item->article->currentBonCommandeArticle->taux_tva ?? 0);

                $mHT  = $prixHT * $quantite;
                $mTVA = $mHT * ($tauxTVA / 100);
                $mTTC = $mHT + $mTVA;

                $totalHT  += $mHT;
                $totalTVA += $mTVA;
                $totalTTC += $mTTC;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->article->reference ?? '—' }}</td>
                <td><strong style="color:#0c3260">{{ $item->article->designation ?? '—' }}</strong></td>
                <td class="text-center">{{ $item->article->unite_mesure ?? '—' }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format($quantite, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($prixHT, 2, ',', ' ') }}</td>
                <td class="text-center" style="color:#475569">{{ $tauxTVA > 0 ? $tauxTVA.'%' : 'Exo.' }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format($mTTC, 2, ',', ' ') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center" style="padding:14px;color:#64748b;font-style:italic">
                    Aucun article dans cette commande.
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="tr-total-ht">
            <td colspan="7" class="text-right" style="font-size:8px;letter-spacing:.3px;color:#3d4f6a">TOTAL HORS TAXE</td>
            <td class="text-right">{{ number_format($totalHT, 2, ',', ' ') }} DH</td>
        </tr>
        <tr class="tr-total-tva">
            <td colspan="7" class="text-right" style="font-size:8px;letter-spacing:.3px;color:#3d4f6a">TOTAL TVA</td>
            <td class="text-right">{{ number_format($totalTVA, 2, ',', ' ') }} DH</td>
        </tr>
        <tr class="tr-grand-ttc">
            <td colspan="7" class="text-right" style="letter-spacing:.5px">TOTAL TOUTES TAXES COMPRISES</td>
            <td class="text-right">{{ number_format($totalTTC, 2, ',', ' ') }} DH</td>
        </tr>
    </tfoot>
</table>

@if(!empty($chefCommande->note))
<div class="note-box">
    <strong>Note :</strong> {{ $chefCommande->note }}
</div>
@endif

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
