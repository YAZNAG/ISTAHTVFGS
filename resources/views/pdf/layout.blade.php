<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Export PDF')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
        }

        /* ── En-tête ── */
        .header {
            width: 100%;
            border-bottom: 2px solid #0f3b63;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-inner {
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }
        .header-logo img {
            width: 70px;
            height: auto;
        }
        .header-logo-placeholder {
            width: 60px;
            height: 60px;
            background: #0f3b63;
            border-radius: 6px;
            text-align: center;
            vertical-align: middle;
            line-height: 60px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header-center {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 0 12px;
        }
        .header-etablissement {
            font-size: 13px;
            font-weight: 700;
            color: #0f3b63;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-sous-titre {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
        }
        .header-doc-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f3b63;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-right {
            display: table-cell;
            width: 120px;
            text-align: right;
            vertical-align: top;
            font-size: 9px;
            color: #64748b;
            padding-top: 4px;
        }
        .header-right .date-label { font-weight: 700; color: #0f3b63; }

        /* ── Tableau ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        thead tr th {
            background: #0f3b63;
            color: #fff;
            text-align: left;
            padding: 7px 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #0a2d4e;
        }
        tbody tr td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        tbody tr:hover td {
            background: #eff6ff;
        }

        /* ── Badges statut ── */
        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: 700;
        }
        .badge-active   { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .badge-faible   { background: #fef9c3; color: #854d0e; }
        .badge-rupture  { background: #fee2e2; color: #991b1b; }
        .badge-normal   { background: #dcfce7; color: #166534; }

        /* ── Pied de page ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            font-size: 9px;
            color: #94a3b8;
        }
        .footer-inner {
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }

        /* ── Résumé compteur ── */
        .summary {
            margin-bottom: 10px;
            font-size: 10px;
            color: #475569;
        }
        .summary strong { color: #0f3b63; }
    </style>
</head>
<body>

    {{-- En-tête --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-logo">
                @if(file_exists(public_path('images/logo-istaht.png')))
                    <img src="{{ public_path('images/logo-istaht.png') }}" alt="Logo ISTAHT">
                @else
                    <div class="header-logo-placeholder">IS</div>
                @endif
            </div>
            <div class="header-center">
                <div class="header-etablissement">ISTAHT — Institut Spécialisé des Arts &amp; Métiers Hôteliers de Tanger</div>
                <div class="header-sous-titre">Application de gestion des stocks — Restauration collective</div>
                <div class="header-doc-title">@yield('title', 'Export PDF')</div>
            </div>
            <div class="header-right">
                <div class="date-label">Date d'export</div>
                <div>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
                <div>{{ \Carbon\Carbon::now()->format('H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Résumé optionnel --}}
    @hasSection('summary')
        <div class="summary">@yield('summary')</div>
    @endif

    {{-- Contenu (tableau) --}}
    @yield('content')

    {{-- Pied de page --}}
    <div class="footer">
        <div class="footer-inner">
            <div class="footer-left">ISTAHT — Application Stock &amp; Restauration</div>
            <div class="footer-right">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>

</body>
</html>
