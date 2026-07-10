<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Appel d'offre {{ $bonCommande->reference }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1a2233;
            background: #fff;
            line-height: 1.45;
        }

        /* ══ BANDEAU SOUVERAINETÉ ══ */
        .sovereignty {
            background: #0c3260;
            color: rgba(255,255,255,.9);
            text-align: center;
            padding: 6px 20px;
            font-size: 9px;
            letter-spacing: .8px;
            text-transform: uppercase;
        }
        .sovereignty .ar {
            font-size: 11.5px;
            letter-spacing: 1.5px;
            margin: 0 16px;
        }

        /* ══ FILET OR ══ */
        .gold-rule {
            height: 3px;
            background: #b8963e;
        }

        /* ══ EN-TÊTE ══ */
        .header {
            padding: 16px 24px 14px;
            border-bottom: 2px solid #0c3260;
            display: table;
            width: 100%;
        }
        .header-logo-cell {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }
        .logo-box {
            width: 62px;
            height: 62px;
            background: #0c3260;
            border-radius: 7px;
            text-align: center;
            line-height: 1;
            padding-top: 10px;
            border: 2px solid #b8963e;
        }
        .logo-init { color: #fff; font-size: 19px; font-weight: 800; letter-spacing: 1px; }
        .logo-sub  { color: #d4af62; font-size: 7.5px; letter-spacing: 2.5px; margin-top: 3px; text-transform: uppercase; }

        .header-center-cell {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 0 10px;
        }
        .header-ministere {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 3px;
        }
        .header-etab {
            font-size: 14px;
            font-weight: 800;
            color: #0c3260;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            line-height: 1.25;
        }
        .header-ville {
            font-size: 10px;
            font-weight: 700;
            color: #b8963e;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .header-right-cell {
            display: table-cell;
            width: 115px;
            text-align: right;
            vertical-align: top;
            padding-top: 4px;
        }
        .ref-label { font-size: 8px; text-transform: uppercase; letter-spacing: .8px; color: #64748b; font-weight: 700; }
        .ref-value { font-size: 13px; font-weight: 800; color: #0c3260; margin-top: 2px; }

        /* ══ TITRE DOCUMENT ══ */
        .doc-title-bar {
            text-align: center;
            padding: 11px 24px 9px;
            border-bottom: 1px solid #dce4ef;
        }
        .doc-title-label {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .8px;
        }
        .doc-title-text {
            font-size: 15px;
            font-weight: 800;
            color: #0c3260;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            margin-top: 3px;
        }

        /* ══ BODY ══ */
        .body-wrap { padding: 14px 24px 90px; }

        /* Bloc fournisseur */
        .bloc-fournisseur {
            display: table;
            width: 100%;
            border: 1px solid #dce4ef;
            border-radius: 4px;
            margin-bottom: 14px;
            background: #f4f7fb;
        }
        .bloc-left {
            display: table-cell;
            width: 50%;
            padding: 10px 14px;
            border-right: 1px solid #dce4ef;
            vertical-align: top;
        }
        .bloc-right {
            display: table-cell;
            width: 50%;
            padding: 10px 14px;
            vertical-align: top;
        }
        .bloc-section-title {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #0c3260;
            border-bottom: 1px solid #dce4ef;
            padding-bottom: 5px;
            margin-bottom: 7px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        .info-label {
            display: table-cell;
            width: 42%;
            font-size: 9px;
            font-weight: 700;
            color: #3d4f6a;
            vertical-align: top;
        }
        .info-value {
            display: table-cell;
            font-size: 10px;
            color: #1a2233;
            vertical-align: top;
        }

        /* ══ TABLEAU ARTICLES ══ */
        table.articles {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
            font-variant-numeric: tabular-nums;
        }
        table.articles thead th {
            background: #0c3260;
            color: #fff;
            padding: 7px 8px;
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            border: 1px solid #071f3e;
        }
        table.articles thead th.right { text-align: right; }

        table.articles tbody td {
            padding: 6px 8px;
            border: 1px solid #dce4ef;
            vertical-align: middle;
        }
        table.articles tbody tr:nth-child(even) td { background: #f4f7fb; }

        /* Ligne totaux */
        table.articles tbody tr.total-row td {
            background: #f4f7fb;
            font-weight: 700;
            border-color: #c5d3e4;
            font-size: 10px;
        }
        table.articles tbody tr.grand-total td {
            background: #0c3260;
            color: #fff;
            font-weight: 800;
            font-size: 11px;
            border-color: #071f3e;
        }

        /* ══ ARRÊTÉ ══ */
        .arrete {
            border: 1px solid #0c3260;
            background: #f4f7fb;
            padding: 9px 14px;
            margin-bottom: 20px;
            font-size: 10.5px;
            font-weight: 700;
            color: #0c3260;
            border-radius: 3px;
        }
        .arrete span { color: #b8963e; font-size: 12px; }

        /* ══ SIGNATURES ══ */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 28px;
        }
        .sig-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 18px;
        }
        .sig-label {
            font-size: 9.5px;
            font-weight: 700;
            color: #0c3260;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 40px;
        }
        .sig-line {
            border-bottom: 1px dashed #0c3260;
            margin-top: 36px;
            padding-bottom: 4px;
            font-size: 8.5px;
            color: #64748b;
        }

        /* ══ PIED DE PAGE ══ */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2px solid #0c3260;
            padding: 7px 24px;
            display: table;
            width: 100%;
            font-size: 8.5px;
            color: #64748b;
            background: #fff;
        }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-left strong { color: #0c3260; }
        .footer-center { display: table-cell; text-align: center; color: #b8963e; font-weight: 700; font-size: 9px; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

{{-- Bandeau Royaume --}}
<div class="sovereignty">
    <span class="ar">المملكة المغربية</span>
    —
    Royaume du Maroc &nbsp;·&nbsp; Ministère du Tourisme, de l'Artisanat et de l'Économie Sociale et Solidaire
</div>
<div class="gold-rule"></div>

{{-- En-tête établissement --}}
<div class="header">
    <div class="header-logo-cell">
        <div class="logo-box">
            <div class="logo-init">IS</div>
            <div class="logo-sub">TAHT</div>
        </div>
    </div>
    <div class="header-center-cell">
        <div class="header-ministere">Institut Spécialisé de Technologie Appliquée</div>
        <div class="header-etab">Hôtelière et Touristique</div>
        <div class="header-ville">— Tanger —</div>
    </div>
    <div class="header-right-cell">
        <div class="ref-label">Référence marché</div>
        <div class="ref-value">{{ $bonCommande->reference }}</div>
        <div style="font-size:8.5px;color:#64748b;margin-top:6px">
            {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </div>
    </div>
</div>

{{-- Titre document --}}
<div class="doc-title-bar">
    <div class="doc-title-label">Document officiel</div>
    <div class="doc-title-text">Appel d'Offre N° {{ $bonCommande->reference }}</div>
</div>

<div class="body-wrap">

    {{-- Bloc fournisseur + infos marché --}}
    <div class="bloc-fournisseur">
        <div class="bloc-left">
            <div class="bloc-section-title">Fournisseur attributaire</div>
            @if($fournisseur)
                <div class="info-row">
                    <div class="info-label">Raison sociale :</div>
                    <div class="info-value"><strong>{{ $fournisseur->raison_sociale ?? $fournisseur->nom ?? '—' }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Adresse :</div>
                    <div class="info-value">{{ $fournisseur->adresse ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tél. :</div>
                    <div class="info-value">{{ $fournisseur->telephone ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">ICE :</div>
                    <div class="info-value">{{ $fournisseur->ice ?? '—' }}</div>
                </div>
            @else
                <div style="color:#64748b;font-style:italic">Non attribué</div>
            @endif
        </div>
        <div class="bloc-right">
            <div class="bloc-section-title">Informations du marché</div>
            <div class="info-row">
                <div class="info-label">Catégorie :</div>
                <div class="info-value"><strong>{{ $bonCommande->categorie?->nom ?? '—' }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de commande :</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($bonCommande->date_mise_ligne)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Limite de réception :</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($bonCommande->date_limite_reception)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Période du marché :</div>
                <div class="info-value">
                    {{ optional($bonCommande->date_debut)->format('d/m/Y') ?? '—' }}
                    → {{ optional($bonCommande->date_fin)->format('d/m/Y') ?? '—' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Objet :</div>
                <div class="info-value" style="font-size:9.5px">{{ $bonCommande->objet }}</div>
            </div>
        </div>
    </div>

    {{-- Tableau des articles --}}
    @php
        $totalHT = $totalTVA = $totalTTC = 0;
    @endphp
    <table class="articles">
        <thead>
            <tr>
                <th style="width:9%">Code</th>
                <th style="width:32%">Désignation</th>
                <th style="width:7%">Unité</th>
                <th style="width:9%;text-align:right">Qté Min</th>
                <th style="width:9%;text-align:right">Qté Max</th>
                <th style="width:9%;text-align:right">Qté Cde</th>
                <th style="width:10%;text-align:right">P.U. HT (DH)</th>
                <th style="width:6%;text-align:center">TVA</th>
                <th style="width:10%;text-align:right">Montant TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($articles as $ligne)
                @php
                    $prixHT   = (float)($ligne->prix_unitaire_ht ?? 0);
                    $quantite = (float)($ligne->quantite_commandee ?? 0);
                    $tva      = (float)($ligne->taux_tva ?? 0);
                    $mHT      = $prixHT * $quantite;
                    $mTVA     = $mHT * ($tva / 100);
                    $mTTC     = $mHT + $mTVA;
                    $totalHT  += $mHT;
                    $totalTVA += $mTVA;
                    $totalTTC += $mTTC;
                @endphp
                <tr>
                    <td><strong style="color:#0c3260">{{ $ligne->article->reference ?? '—' }}</strong></td>
                    <td>{{ $ligne->article->designation ?? '—' }}</td>
                    <td style="text-align:center">{{ $ligne->article->unite_mesure ?? '—' }}</td>
                    <td style="text-align:right;color:#64748b">{{ number_format((float)($ligne->quantite_minimale ?? 0), 2, ',', ' ') }}</td>
                    <td style="text-align:right;color:#64748b">{{ number_format((float)($ligne->quantite_maximale ?? 0), 2, ',', ' ') }}</td>
                    <td style="text-align:right;font-weight:700">{{ number_format($quantite, 2, ',', ' ') }}</td>
                    <td style="text-align:right">{{ number_format($prixHT, 2, ',', ' ') }}</td>
                    <td style="text-align:center;color:#64748b">{{ $tva > 0 ? $tva.'%' : 'Exo.' }}</td>
                    <td style="text-align:right;font-weight:700">{{ number_format($mTTC, 2, ',', ' ') }}</td>
                </tr>
            @endforeach

            {{-- Sous-totaux --}}
            <tr class="total-row">
                <td colspan="8" style="text-align:right;font-size:9.5px;letter-spacing:.3px">
                    TOTAL HORS TAXE
                </td>
                <td style="text-align:right">{{ number_format($totalHT, 2, ',', ' ') }} DH</td>
            </tr>
            <tr class="total-row">
                <td colspan="8" style="text-align:right;font-size:9.5px;letter-spacing:.3px">
                    TOTAL TVA
                </td>
                <td style="text-align:right">{{ number_format($totalTVA, 2, ',', ' ') }} DH</td>
            </tr>
            <tr class="grand-total">
                <td colspan="8" style="text-align:right;letter-spacing:.5px">
                    TOTAL TOUTES TAXES COMPRISES
                </td>
                <td style="text-align:right">{{ number_format($totalTTC, 2, ',', ' ') }} DH</td>
            </tr>
        </tbody>
    </table>

    {{-- Arrêté en lettres --}}
    <div class="arrete">
        Arrêtée la présente commande à la somme de :
        <span>{{ number_format($totalTTC, 2, ',', ' ') }} Dirhams TTC</span>
    </div>

    {{-- Signatures --}}
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

</div>

{{-- Pied de page --}}
<div class="footer">
    <div class="footer-left">
        <strong>ISTAHT Tanger</strong> — Gestion des achats &amp; marchés
    </div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">
        Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
    </div>
</div>

</body>
</html>
