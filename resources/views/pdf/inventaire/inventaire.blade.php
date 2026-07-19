<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inventaire {{ $inventaire->semaine }}</title>
    <style>
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }
        @page { margin-top: 122px; margin-bottom: 46px; margin-left: 24px; margin-right: 24px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1a2233; background: #fff; line-height: 1.45; }

        .pdf-header { position: fixed; top: -122px; left: 0; right: 0; height: 115px; }
        .pdf-header-img { display: block; width: 100%; height: 108px; }
        .pdf-header-navy { height: 4px; background: #0c3260; }
        .pdf-header-gold { height: 3px; background: #b8963e; }

        .pdf-footer { position: fixed; bottom: -46px; left: 0; right: 0; height: 38px; border-top: 2px solid #0c3260; padding-top: 5px; font-size: 7.5px; color: #64748b; background: #fff; display: table; width: 100%; }
        .footer-left { display: table-cell; text-align: left; } .footer-left strong { color: #0c3260; }
        .footer-center { display: table-cell; text-align: center; color: #b8963e; font-weight: 700; }
        .footer-right { display: table-cell; text-align: right; }

        .doc-title-bar { text-align: center; padding: 8px 0; border-bottom: 2px solid #0c3260; margin-bottom: 8px; }
        .doc-label { font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: .7px; }
        .doc-title { font-size: 13px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: 1px; margin-top: 3px; }
        .doc-sub { font-size: 8.5px; color: #64748b; margin-top: 3px; }

        .badges { margin-bottom: 8px; }
        .badge { display: inline-block; padding: 3px 10px; font-size: 8.5px; font-weight: 700; margin-right: 6px; }
        .badge-week { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .badge-state { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .badge-count { background: #fefce8; border: 1px solid #fde68a; color: #854d0e; }

        table.inv { width: 100%; border-collapse: collapse; font-size: 8px; margin-top: 4px; }
        table.inv thead th { background: #0c3260; color: #fff; padding: 5px 4px; font-size: 7px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; border: 0.5px solid #071f3e; text-align: center; }
        table.inv tbody td { padding: 4px 4px; border: 0.5px solid #cfdae8; }
        table.inv tbody tr:nth-child(even) td { background: #f4f7fb; }
        .ecart-pos { color: #15803d; font-weight: 700; }
        .ecart-neg { color: #b91c1c; font-weight: 700; }
        .ecart-nul { color: #64748b; }

        .signatures { display: table; width: 100%; margin-top: 28px; }
        .sig-cell { display: table-cell; width: 50%; text-align: center; padding: 0 24px; }
        .sig-label { font-size: 9px; font-weight: 800; color: #0c3260; text-transform: uppercase; }
        .sig-line { border-bottom: 1px dashed #0c3260; margin-top: 38px; padding-bottom: 4px; font-size: 7.5px; color: #64748b; }

        .text-center { text-align: center; } .text-right { text-align: right; }
        .body-wrap { padding-bottom: 44px; }
    </style>
</head>
<body>

<div class="pdf-header">
    @if(!empty($pdfHeaderSrc))<img class="pdf-header-img" src="{{ $pdfHeaderSrc }}" alt="ISTAHT Tanger">@endif
    <div class="pdf-header-navy"></div><div class="pdf-header-gold"></div>
</div>
<div class="pdf-footer">
    <div class="footer-left"><strong>ISTAHT Tanger</strong> — Gestion des stocks</div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

<div class="body-wrap">

<div class="doc-title-bar">
    <div class="doc-label">Document officiel — Institut Spécialisé de Technologie Appliquée Hôtelière et Touristique Tanger</div>
    <div class="doc-title">Inventaire Hebdomadaire</div>
    <div class="doc-sub">Semaine {{ $inventaire->semaine }}</div>
</div>

<div class="badges">
    <span class="badge badge-week">Semaine : {{ $inventaire->semaine }}</span>
    <span class="badge badge-state">{{ $inventaire->statut === 'finalized' ? 'Finalisé' : 'Brouillon' }}</span>
    <span class="badge badge-count">{{ $inventaire->lignes->count() }} article(s)</span>
</div>

<table class="inv">
    <thead>
        <tr>
            <th style="width:5%">N°</th>
            <th style="width:10%">Code</th>
            <th style="width:26%">Désignation</th>
            <th style="width:7%">Unité</th>
            <th style="width:9%">Qté entrée</th>
            <th style="width:9%">Qté sortie</th>
            <th style="width:10%">Stock théorique</th>
            <th style="width:9%">Stock réel</th>
            <th style="width:7%">Écart</th>
            <th style="width:12%">Observations</th>
        </tr>
    </thead>
    <tbody>
        @forelse($inventaire->lignes as $ligne)
            @php $ec = (float) $ligne->ecart; @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center" style="font-weight:700;color:#1d4ed8">{{ $ligne->code_article ?? '—' }}</td>
                <td><strong style="color:#0c3260">{{ $ligne->designation ?? '—' }}</strong></td>
                <td class="text-center">{{ $ligne->unite_mesure ?? '—' }}</td>
                <td class="text-right">{{ number_format((float)$ligne->qte_entree, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format((float)$ligne->qte_sortie, 2, ',', ' ') }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format((float)$ligne->stock_theorique, 2, ',', ' ') }}</td>
                <td class="text-right">{{ $ligne->stock_reel !== null ? number_format((float)$ligne->stock_reel, 2, ',', ' ') : '—' }}</td>
                <td class="text-right {{ $ec > 0 ? 'ecart-pos' : ($ec < 0 ? 'ecart-neg' : 'ecart-nul') }}">
                    {{ $ec > 0 ? '+' : '' }}{{ number_format($ec, 2, ',', ' ') }}
                </td>
                <td>{{ $ligne->observations }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="text-center" style="padding:12px;color:#64748b;font-style:italic">Aucun article dans cet inventaire.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="signatures">
    <div class="sig-cell"><div class="sig-label">Fait le {{ date('d/m/Y') }}</div></div>
    <div class="sig-cell"><div class="sig-label">Le Responsable</div><div class="sig-line">Nom &amp; Signature</div></div>
</div>

</div>
</body>
</html>
