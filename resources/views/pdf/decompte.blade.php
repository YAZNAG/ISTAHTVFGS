<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Décompte — {{ $marche->reference }}</title>
    <style>
        /* ATTENTION : ne JAMAIS mettre `html` ou `*` dans ce reset — DomPDF applique
           les marges @page via l'element racine html ; un margin:0 dessus les ecrase
           et le header fixe disparait. */
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }

        /*
         * DomPDF fixed header pattern :
         *   @page margin-top = hauteur réservée pour le header
         *   .pdf-header top  = négatif de ce même espace
         * → l'élément se place dans la marge et se répète sur CHAQUE page.
         */
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

        /* ══ BADGE PÉRIODE ══ */
        .periode-badge {
            display: inline-block;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 4px 12px;
            font-size: 9px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        /* ══ BLOC INFO 2 colonnes ══ */
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

        /* ══ TABLEAU RÉCAPITULATIF ══ */
        table.recap {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 6px;
        }
        table.recap thead th {
            background: #1e3a5f;
            color: #fff;
            padding: 5px 9px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 1px solid #071f3e;
        }
        table.recap tbody td {
            padding: 6px 9px;
            border: 1px solid #dce4ef;
        }
        .recap-highlight td {
            background: #0c3260;
            color: #fff;
            font-weight: 800;
            font-size: 10.5px;
            border-color: #071f3e;
        }

        /* ══ ZONE SIGNATURE ══ */
        .signatures { display: table; width: 100%; margin-top: 32px; }
        .sig-cell   { display: table-cell; width: 50%; text-align: center; padding: 0 20px; }
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
    <div class="doc-title">Décompte de Marché N° {{ $marche->reference }}</div>
    <div class="doc-sub">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

{{-- ════ PÉRIODE ════ --}}
<div class="periode-badge">
    Période couverte :
    @if(!empty($date_debut))
        du {{ \Carbon\Carbon::parse($date_debut)->format('d/m/Y') }}
    @endif
    au {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
    &nbsp;—&nbsp;
    @if($decompte_final)
        <strong>Décompte DÉFINITIF</strong>
    @else
        Décompte provisoire
    @endif
</div>

{{-- ════ BLOC MARCHÉ + FOURNISSEUR ════ --}}
<div class="info-grid">
    <div class="info-col">
        <div class="info-section-title">Informations du marché</div>
        <div class="info-row">
            <div class="info-lbl">Référence :</div>
            <div class="info-val"><strong>{{ $marche->reference }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Objet :</div>
            <div class="info-val">{{ $marche->objet }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Catégorie :</div>
            <div class="info-val">{{ $marche->categorie?->nom ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Montant marché :</div>
            <div class="info-val"><strong>{{ number_format((float)$marche->total_ttc, 2, ',', ' ') }} DH TTC</strong></div>
        </div>
    </div>
    <div class="info-col info-col-right">
        <div class="info-section-title">Fournisseur attributaire</div>
        @php $f = $marche->fournisseur; @endphp
        <div class="info-row">
            <div class="info-lbl">Raison sociale :</div>
            <div class="info-val"><strong>{{ $f->raison_sociale ?? $f->nom ?? '—' }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Adresse :</div>
            <div class="info-val">{{ $f->adresse ?? '—' }}</div>
        </div>
        @if(!empty($f->tp))
        <div class="info-row">
            <div class="info-lbl">TP :</div><div class="info-val">{{ $f->tp }}</div>
        </div>
        @endif
        @if(!empty($f->if))
        <div class="info-row">
            <div class="info-lbl">IF :</div><div class="info-val">{{ $f->if }}</div>
        </div>
        @endif
        @if(!empty($f->rc))
        <div class="info-row">
            <div class="info-lbl">RC :</div><div class="info-val">{{ $f->rc }}</div>
        </div>
        @endif
        @if(!empty($f->ice))
        <div class="info-row">
            <div class="info-lbl">ICE :</div><div class="info-val">{{ $f->ice }}</div>
        </div>
        @endif
        @if(!empty($f->cb))
        <div class="info-row">
            <div class="info-lbl">RIB / CB :</div><div class="info-val">{{ $f->cb }}</div>
        </div>
        @endif
    </div>
</div>

{{-- ════ TABLEAU DES ARTICLES LIVRÉS ════ --}}
<div class="section-title">Articles livrés sur la période</div>

@php $totalHT = 0; $totalTVA = 0; $totalTTC = 0; @endphp

<table class="articles">
    <thead>
        <tr>
            <th style="width:4%"  class="c">N°</th>
            <th style="width:28%">Désignation</th>
            <th style="width:7%"  class="c">Unité</th>
            <th style="width:9%"  class="r">Qté livrée</th>
            <th style="width:11%" class="r">P.U. HT (DH)</th>
            <th style="width:7%"  class="c">TVA %</th>
            <th style="width:11%" class="r">Montant HT</th>
            <th style="width:11%" class="r">Montant TVA</th>
            <th style="width:12%" class="r">Total TTC (DH)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            @php
                $mHT  = (float)$row['montant_ht'];
                $mTVA = (float)$row['montant_tva'];
                $mTTC = (float)$row['montant_ttc'];
                $totalHT  += $mHT;
                $totalTVA += $mTVA;
                $totalTTC += $mTTC;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td><strong style="color:#0c3260">{{ $row['designation'] }}</strong></td>
                <td class="text-center">{{ $row['unite_mesure'] }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format((float)$row['quantite'], 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format((float)$row['prix_unitaire'], 2, ',', ' ') }}</td>
                <td class="text-center" style="color:#475569">{{ (float)$row['taux_tva'] > 0 ? $row['taux_tva'].'%' : 'Exo.' }}</td>
                <td class="text-right">{{ number_format($mHT, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($mTVA, 2, ',', ' ') }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format($mTTC, 2, ',', ' ') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center" style="padding:14px;color:#64748b;font-style:italic">
                    Aucun article livré sur cette période.
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="tr-total-ht">
            <td colspan="6" class="text-right" style="font-size:8px;letter-spacing:.3px;color:#3d4f6a">TOTAL HORS TAXE</td>
            <td class="text-right">{{ number_format($totalHT, 2, ',', ' ') }}</td>
            <td></td>
            <td class="text-right">{{ number_format($totalHT, 2, ',', ' ') }} DH</td>
        </tr>
        <tr class="tr-total-tva">
            <td colspan="6" class="text-right" style="font-size:8px;letter-spacing:.3px;color:#3d4f6a">TOTAL TVA</td>
            <td></td>
            <td class="text-right">{{ number_format($totalTVA, 2, ',', ' ') }}</td>
            <td class="text-right">{{ number_format($totalTVA, 2, ',', ' ') }} DH</td>
        </tr>
        <tr class="tr-grand-ttc">
            <td colspan="8" class="text-right" style="letter-spacing:.5px">TOTAL TOUTES TAXES COMPRISES</td>
            <td class="text-right">{{ number_format($totalTTC, 2, ',', ' ') }} DH</td>
        </tr>
    </tfoot>
</table>

{{-- ════ RÉCAPITULATIF ACOMPTES ════ --}}
<div class="section-title" style="margin-top:18px">Récapitulation — Acomptes</div>

<table class="recap">
    <thead>
        <tr>
            <th style="width:52%;text-align:left">Nature des dépenses</th>
            <th style="width:16%;text-align:right">Dépenses faites</th>
            <th style="width:16%;text-align:right">Retenues garantie</th>
            <th style="width:16%;text-align:right">Reste</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Travaux terminés (décomptes antérieurs)</td>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td class="text-right" style="font-weight:700">{{ number_format((float)$travaux_termine, 2, ',', ' ') }} DH</td>
        </tr>
        <tr>
            <td>Travaux non terminés (solde restant)</td>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td class="text-right">{{ number_format((float)$travaux_non_termine, 2, ',', ' ') }} DH</td>
        </tr>
        <tr>
            <td colspan="3" style="font-style:italic;color:#475569">
                À déduire : acomptes précédemment payés
            </td>
            <td class="text-right" style="font-weight:700">{{ number_format((float)$travaux_termine, 2, ',', ' ') }} DH</td>
        </tr>
        <tr class="recap-highlight">
            <td colspan="3">Montant de l'acompte à délivrer</td>
            <td class="text-right">{{ number_format((float)$decompte_total, 2, ',', ' ') }} DH</td>
        </tr>
    </tbody>
</table>

{{-- ════ ARRÊTÉ ════ --}}
<div style="border:1px solid #0c3260;background:#f4f7fb;padding:8px 14px;margin-top:14px;font-size:9.5px;font-weight:700;color:#0c3260;">
    Arrêté le présent décompte à la somme de :
    <span style="color:#b8963e;font-size:11.5px">
        {{ number_format((float)$decompte_total, 2, ',', ' ') }} Dirhams TTC
    </span>
</div>

{{-- ════ SIGNATURES ════ --}}
<div class="signatures">
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
