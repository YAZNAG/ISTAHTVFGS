<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche technique pédagogique</title>
    <style>
        body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }
        @page { margin-top: 130px; margin-bottom: 52px; margin-left: 28px; margin-right: 28px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a2233; background: #fff; line-height: 1.5; }

        .pdf-header { position: fixed; top: -130px; left: 0; right: 0; height: 123px; }
        .pdf-header-img { display: block; width: 100%; height: 116px; }
        .pdf-header-navy { height: 4px; background: #0c3260; }
        .pdf-header-gold { height: 3px; background: #b8963e; }

        .pdf-footer { position: fixed; bottom: -52px; left: 0; right: 0; height: 44px; border-top: 2px solid #0c3260; padding-top: 6px; font-size: 8px; color: #64748b; background: #fff; display: table; width: 100%; }
        .footer-left { display: table-cell; text-align: left; } .footer-left strong { color: #0c3260; }
        .footer-center { display: table-cell; text-align: center; color: #b8963e; font-weight: 700; }
        .footer-right { display: table-cell; text-align: right; }

        .doc-title-bar { text-align: center; padding: 10px 0 8px; border-bottom: 2px solid #0c3260; margin-bottom: 12px; }
        .doc-label { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: .8px; }
        .doc-title { font-size: 14px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: 1px; margin-top: 3px; }
        .doc-sub { font-size: 8.5px; color: #64748b; margin-top: 4px; }

        .info-grid { display: table; width: 100%; margin-bottom: 12px; border: 1px solid #dce4ef; }
        .info-col { display: table-cell; width: 50%; padding: 9px 12px; vertical-align: top; }
        .info-col-right { border-left: 1px solid #dce4ef; }
        .info-row { display: table; width: 100%; margin-bottom: 3px; }
        .info-lbl { display: table-cell; width: 42%; font-size: 8.5px; font-weight: 700; color: #3d4f6a; }
        .info-val { display: table-cell; font-size: 9px; color: #1a2233; }

        .section-title { font-size: 9px; font-weight: 800; color: #0c3260; text-transform: uppercase; letter-spacing: .5px; border-left: 3px solid #b8963e; padding-left: 7px; margin: 14px 0 8px; }

        table.tb { width: 100%; border-collapse: collapse; font-size: 9px; }
        table.tb thead th { background: #0c3260; color: #fff; padding: 5px 7px; font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; border: 1px solid #071f3e; }
        table.tb thead th.r { text-align: right; } table.tb thead th.c { text-align: center; }
        table.tb tbody td { padding: 4px 7px; border: 1px solid #dce4ef; }
        table.tb tbody tr:nth-child(even) td { background: #f4f7fb; }
        table.tb tfoot td { padding: 5px 7px; border: 1px solid #c5d3e4; font-weight: 700; }
        .tr-total td { background: #eef2f7; }
        .tr-ration td { background: #0c3260; color: #fff; border-color: #071f3e; }

        .etape { margin-bottom: 5px; padding-left: 7px; border-left: 2px solid #dce4ef; }
        .etape-title { font-size: 9px; font-weight: 700; color: #0c3260; }
        .etape-desc { font-size: 8.5px; color: #475569; }

        .signatures { display: table; width: 100%; margin-top: 32px; }
        .sig-cell { display: table-cell; width: 50%; text-align: center; padding: 0 20px; }
        .sig-label { font-size: 9.5px; font-weight: 800; color: #0c3260; text-transform: uppercase; }
        .sig-line { border-bottom: 1px dashed #0c3260; margin-top: 40px; padding-bottom: 4px; font-size: 7.5px; color: #64748b; }

        .text-right { text-align: right; } .text-center { text-align: center; }
        .body-wrap { padding-bottom: 58px; }
    </style>
</head>
<body>

<div class="pdf-header">
    @if(!empty($pdfHeaderSrc))<img class="pdf-header-img" src="{{ $pdfHeaderSrc }}" alt="ISTAHT Tanger">@endif
    <div class="pdf-header-navy"></div><div class="pdf-header-gold"></div>
</div>
<div class="pdf-footer">
    <div class="footer-left"><strong>ISTAHT Tanger</strong> — Formation hôtelière &amp; touristique</div>
    <div class="footer-center">stock.istahttanger.ma</div>
    <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
</div>

<div class="body-wrap">

<div class="doc-title-bar">
    <div class="doc-label">Document officiel — Institut Spécialisé de Technologie Appliquée Hôtelière et Touristique Tanger</div>
    <div class="doc-title">Fiche Technique Pédagogique</div>
    <div class="doc-sub">{{ $fiche->plat->nom ?? '—' }}</div>
</div>

<div class="info-grid">
    <div class="info-col">
        <div class="info-row"><div class="info-lbl">Plat :</div><div class="info-val"><strong>{{ $fiche->plat->nom ?? '—' }}</strong></div></div>
        <div class="info-row"><div class="info-lbl">Catégorie :</div><div class="info-val">{{ $fiche->repas->nom ?? '—' }}</div></div>
    </div>
    <div class="info-col info-col-right">
        <div class="info-row"><div class="info-lbl">Formateur :</div><div class="info-val"><strong>{{ $fiche->responsable }}</strong></div></div>
        <div class="info-row"><div class="info-lbl">Effectif :</div><div class="info-val">{{ $fiche->effectif }}</div></div>
    </div>
</div>

<div class="section-title">Ingrédients</div>
<table class="tb">
    <thead>
        <tr>
            <th style="width:5%" class="c">N°</th>
            <th style="width:34%">Ingrédient</th>
            <th style="width:12%" class="c">Code</th>
            <th style="width:11%" class="r">Quantité</th>
            <th style="width:9%" class="c">Unité</th>
            <th style="width:13%" class="r">P.U. HT</th>
            <th style="width:16%" class="r">Coût TTC</th>
        </tr>
    </thead>
    <tbody>
        @forelse($fiche->ingredients as $ing)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td><strong style="color:#0c3260">{{ $ing->article->designation ?? '—' }}</strong></td>
                <td class="text-center">{{ $ing->article->reference ?? '—' }}</td>
                <td class="text-right" style="font-weight:700">{{ number_format((float)$ing->quantite, 2, ',', ' ') }}</td>
                <td class="text-center">{{ $ing->article->unite_mesure ?? '—' }}</td>
                <td class="text-right">{{ number_format((float)$ing->prix_unitaire, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format((float)$ing->total_ttc, 2, ',', ' ') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center" style="padding:12px;color:#64748b;font-style:italic">Aucun ingrédient.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="tr-total"><td colspan="6" class="text-right">COÛT TOTAL TTC</td><td class="text-right">{{ number_format((float)$totalTtc, 2, ',', ' ') }} DH</td></tr>
        <tr class="tr-ration"><td colspan="6" class="text-right">COÛT PAR EFFECTIF</td><td class="text-right">{{ number_format((float)$total_effectif, 2, ',', ' ') }} DH</td></tr>
    </tfoot>
</table>

@if($fiche->etapes->count())
<div class="section-title">Procédé de préparation</div>
@foreach($fiche->etapes as $etape)
    <div class="etape">
        <div class="etape-title">{{ $loop->iteration }}. {{ $etape->title }}</div>
        @if($etape->description)<div class="etape-desc">{{ $etape->description }}</div>@endif
    </div>
@endforeach
@endif

<div class="signatures">
    <div class="sig-cell"><div class="sig-label">Tanger, le …………</div></div>
    <div class="sig-cell"><div class="sig-label">Le Formateur</div><div class="sig-line">Nom &amp; Signature</div></div>
</div>

</div>
</body>
</html>
