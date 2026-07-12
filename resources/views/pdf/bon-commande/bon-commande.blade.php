<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Appel d'offre {{ $bonCommande->reference }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Espace réservé pour l'image header sur chaque page ── */
        @page { margin-top: 120px; margin-bottom: 52px; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1a2233;
            background: #fff;
            line-height: 1.45;
        }

        /* ══ EN-TÊTE IMAGE FIXE (répété sur chaque page) ══ */
        .pdf-header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 116px;
        }
        .pdf-header img {
            width: 100%;
            height: 108px;
            display: block;
        }
        .pdf-header-navy { height: 3px; background: #0c3260; }
        .pdf-header-gold { height: 2px; background: #b8963e; }

        /* ══ TITRE DOCUMENT ══ */
        .doc-title-bar {
            text-align: center;
            padding: 10px 24px 9px;
            border-bottom: 1px solid #dce4ef;
            margin-bottom: 12px;
        }
        .doc-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .8px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: 800;
            color: #0c3260;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            margin-top: 3px;
        }

        /* ══ BODY ══ */
        .body-wrap { padding: 0 24px 90px; }

        /* Bloc fournisseur / infos marché */
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

        /* ══ NUMÉRO COMMANDE (badge) ══ */
        .ref-badge {
            display: inline-block;
            background: #0c3260;
            color: #fff;
            border-radius: 4px;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .5px;
            margin-bottom: 10px;
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
        table.articles thead th.center { text-align: center; }

        table.articles tbody td {
            padding: 6px 8px;
            border: 1px solid #dce4ef;
            vertical-align: middle;
        }
        table.articles tbody tr:nth-child(even) td { background: #f4f7fb; }

        tr.total-row td {
            background: #f4f7fb;
            font-weight: 700;
            border-color: #c5d3e4;
            font-size: 10px;
        }
        tr.grand-total td {
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
        .signatures { display: table; width: 100%; margin-top: 30px; }
        .sig-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 18px;
        }
        .sig-label {
            font-size: 9.5px;
            font-weight: 800;
            color: #0c3260;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .sig-line {
            border-bottom: 1px dashed #0c3260;
            margin-top: 40px;
            padding-bottom: 4px;
            font-size: 8.5px;
            color: #64748b;
        }

        /* ══ PIED DE PAGE FIXE ══ */
        .pdf-footer {
            position: fixed;
            bottom: -52px;
            left: 0;
            right: 0;
            height: 42px;
            border-top: 2px solid #0c3260;
            padding-top: 7px;
            font-size: 8.5px;
            color: #64748b;
            background: #fff;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; text-align: left; color: #475569; }
        .footer-left strong { color: #0c3260; }
        .footer-center { display: table-cell; text-align: center; color: #b8963e; font-weight: 700; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

{{-- ════ IMAGE HEADER FIXE (répétée sur chaque page) ════ --}}
@php
    $headerPath = public_path('images/pdf-header.jpg');
    $headerSrc  = file_exists($headerPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($headerPath))
        : null;
@endphp
<div class="pdf-header">
    @if($headerSrc)
        <img src="{{ $headerSrc }}" alt="En-tête officiel ISTAHT Tanger">
    @endif
    <div class="pdf-header-navy"></div>
    <div class="pdf-header-gold"></div>
</div>

{{-- ════ TITRE DOCUMENT ════ --}}
<div class="doc-title-bar">
    <div class="doc-label">Document officiel</div>
    <div class="doc-title">Appel d'Offre N° {{ $bonCommande->reference }}</div>
</div>

<div class="body-wrap">

    {{-- Numéro de référence --}}
    <div>
        <span class="ref-badge">Réf. : {{ $bonCommande->reference }}</span>
        <span style="font-size:9px;color:#64748b;margin-left:8px">
            Édité le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
        </span>
    </div>

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
                    <div class="info-label">Téléphone :</div>
                    <div class="info-value">{{ $fournisseur->telephone ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">ICE :</div>
                    <div class="info-value">{{ $fournisseur->ice ?? '—' }}</div>
                </div>
            @else
                <div style="color:#64748b;font-style:italic;font-size:10px">Non attribué</div>
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
                <div class="info-label">Limite réception :</div>
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
    @php $totalHT = $totalTVA = $totalTTC = 0; @endphp
    <table class="articles">
        <thead>
            <tr>
                <th style="width:9%">Code</th>
                <th style="width:30%">Désignation</th>
                <th class="center" style="width:7%">Unité</th>
                <th class="right" style="width:8%">Qté Min</th>
                <th class="right" style="width:8%">Qté Max</th>
                <th class="right" style="width:8%">Qté Cde</th>
                <th class="right" style="width:11%">P.U. HT (DH)</th>
                <th class="center" style="width:6%">TVA</th>
                <th class="right" style="width:13%">Montant TTC</th>
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
            <tr class="total-row">
                <td colspan="8" style="text-align:right;font-size:9.5px;letter-spacing:.3px">TOTAL HORS TAXE</td>
                <td style="text-align:right">{{ number_format($totalHT, 2, ',', ' ') }} DH</td>
            </tr>
            <tr class="total-row">
                <td colspan="8" style="text-align:right;font-size:9.5px;letter-spacing:.3px">TOTAL TVA</td>
                <td style="text-align:right">{{ number_format($totalTVA, 2, ',', ' ') }} DH</td>
            </tr>
            <tr class="grand-total">
                <td colspan="8" style="text-align:right;letter-spacing:.5px">TOTAL TOUTES TAXES COMPRISES</td>
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

{{-- ════ PIED DE PAGE FIXE ════ --}}
<div class="pdf-footer">
    <div class="footer-left"><strong>ISTAHT Tanger</strong> — Gestion des achats &amp; marchés</div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

</body>
</html>
