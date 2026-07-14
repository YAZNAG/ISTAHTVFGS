<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Export PDF')</title>
    <style>
        /* PAS de reset universel `* { margin:0 }` : dans DomPDF il ecrase les marges @page
           et fait disparaitre le header fixe. Reset cible uniquement. */
        html, body, div, p, h1, h2, h3, table, thead, tbody, tfoot, tr, th, td, span, img {
            box-sizing: border-box; margin: 0; padding: 0;
        }

        /* ── Espace réservé pour l'en-tête sur chaque page ── */
        @page { margin-top: 115px; margin-bottom: 50px; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
        }

        /* ══════════════════════════════════════
           EN-TÊTE FIXE — répété sur chaque page
        ══════════════════════════════════════ */
        .pdf-header {
            position: fixed;
            top: -115px;
            left: 0;
            right: 0;
            height: 110px;
        }
        .pdf-header-img {
            display: block;
            width: 100%;
            height: 100px;
        }
        .pdf-header-border {
            height: 3px;
            background: #0c3260;
        }
        .pdf-header-gold {
            height: 2px;
            background: #b8963e;
        }

        /* ══════════════════════════════════════
           TITRE DOCUMENT
        ══════════════════════════════════════ */
        .doc-title-bar {
            text-align: center;
            padding: 8px 0 10px;
            border-bottom: 1px solid #dce4ef;
            margin-bottom: 4px;
        }
        .doc-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .8px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: 800;
            color: #0c3260;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 2px;
        }
        .doc-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        /* ══════════════════════════════════════
           TABLEAU
        ══════════════════════════════════════ */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        thead tr th {
            background: #0c3260;
            color: #fff;
            text-align: left;
            padding: 7px 8px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            border: 1px solid #071f3e;
        }
        tbody tr td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 10px;
        }
        tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        tbody tr:last-child td {
            border-bottom: 2px solid #0c3260;
        }

        /* ══════════════════════════════════════
           BADGES
        ══════════════════════════════════════ */
        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 8.5px;
            font-weight: 700;
        }
        .badge-active   { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .badge-faible   { background: #fef9c3; color: #854d0e; }
        .badge-rupture  { background: #fee2e2; color: #991b1b; }
        .badge-normal   { background: #dcfce7; color: #166534; }
        .badge-info     { background: #dbeafe; color: #1e40af; }
        .badge-warning  { background: #fef3c7; color: #92400e; }

        /* ══════════════════════════════════════
           RÉSUMÉ COMPTEUR
        ══════════════════════════════════════ */
        .summary {
            background: #eff6ff;
            border-left: 3px solid #0c3260;
            padding: 5px 10px;
            margin-bottom: 10px;
            font-size: 10px;
            color: #334155;
            border-radius: 0 4px 4px 0;
        }
        .summary strong { color: #0c3260; }

        /* ══════════════════════════════════════
           PIED DE PAGE FIXE
        ══════════════════════════════════════ */
        .pdf-footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 2px solid #0c3260;
            padding-top: 6px;
            font-size: 8.5px;
            color: #94a3b8;
            background: #fff;
        }
        .pdf-footer-inner {
            display: table;
            width: 100%;
        }
        .footer-left  {
            display: table-cell;
            text-align: left;
            color: #475569;
        }
        .footer-left strong { color: #0c3260; }
        .footer-center {
            display: table-cell;
            text-align: center;
            color: #b8963e;
            font-weight: 700;
            font-size: 8.5px;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- ════ EN-TÊTE OFFICIEL FIXE (répété sur chaque page) ════ --}}
    @php
        if (empty($pdfHeaderSrc)) {
            $__p = public_path('images/pdf-header.jpg');
            $__d = file_exists($__p) ? @file_get_contents($__p) : false;
            $pdfHeaderSrc = $__d !== false ? 'data:image/jpeg;base64,' . base64_encode($__d) : null;
        }
    @endphp
    <div class="pdf-header">
        @if(!empty($pdfHeaderSrc))
            <img class="pdf-header-img" src="{{ $pdfHeaderSrc }}" alt="En-tête ISTAHT Tanger">
        @endif
        <div class="pdf-header-border"></div>
        <div class="pdf-header-gold"></div>
    </div>

    {{-- ════ TITRE DOCUMENT ════ --}}
    <div class="doc-title-bar">
        <div class="doc-label">Document exporté</div>
        <div class="doc-title">@yield('title', 'Export PDF')</div>
        <div class="doc-meta">Généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
    </div>

    {{-- ════ RÉSUMÉ OPTIONNEL ════ --}}
    @hasSection('summary')
        <div class="summary">@yield('summary')</div>
    @endif

    {{-- ════ CONTENU ════ --}}
    @yield('content')

    {{-- ════ PIED DE PAGE FIXE ════ --}}
    <div class="pdf-footer">
        <div class="pdf-footer-inner">
            <div class="footer-left">
                <strong>ISTAHT Tanger</strong> — Gestion des stocks &amp; restauration collective
            </div>
            <div class="footer-center">stock.istahttanger.ma</div>
            <div class="footer-right">
                Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

</body>
</html>
