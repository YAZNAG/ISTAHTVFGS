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

        /* ══════════════════════════════════════
           EN-TÊTE ÉTABLISSEMENT
        ══════════════════════════════════════ */
        .header {
            width: 100%;
            margin-bottom: 16px;
            border-bottom: 3px solid #0f3b63;
            padding-bottom: 10px;
        }

        /* Bandeau supérieur : Royaume du Maroc */
        .header-royaume {
            text-align: center;
            font-size: 9px;
            color: #475569;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .header-royaume strong {
            color: #0f3b63;
            font-size: 9.5px;
        }

        /* Corps principal de l'en-tête : logo | infos | date */
        .header-inner {
            display: table;
            width: 100%;
        }

        /* Colonne logo */
        .header-logo {
            display: table-cell;
            width: 75px;
            vertical-align: middle;
            padding-right: 10px;
        }
        .header-logo img {
            width: 65px;
            height: auto;
        }
        .header-logo-placeholder {
            width: 65px;
            height: 65px;
            background: #0f3b63;
            border-radius: 8px;
            text-align: center;
            line-height: 65px;
            color: #fff;
            font-size: 22px;
            font-weight: 700;
        }

        /* Colonne centrale : nom établissement + titre document */
        .header-center {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 0 8px;
        }
        .header-ministere {
            font-size: 8.5px;
            color: #64748b;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .header-etablissement {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f3b63;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            line-height: 1.35;
        }
        .header-ville {
            font-size: 10px;
            font-weight: 700;
            color: #1e6a9e;
            margin-top: 2px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header-divider {
            width: 60px;
            height: 2px;
            background: #1e6a9e;
            margin: 5px auto;
            border-radius: 2px;
        }
        .header-doc-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f3b63;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 4px;
        }

        /* Colonne droite : date + ref */
        .header-right {
            display: table-cell;
            width: 110px;
            text-align: right;
            vertical-align: top;
            font-size: 8.5px;
            color: #64748b;
            padding-top: 2px;
        }
        .header-right .date-label {
            font-weight: 700;
            color: #0f3b63;
            font-size: 9px;
            text-transform: uppercase;
        }
        .header-right .date-value {
            font-size: 10px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2px;
        }
        .header-right .time-value {
            font-size: 9px;
            color: #94a3b8;
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
            background: #0f3b63;
            color: #fff;
            text-align: left;
            padding: 7px 8px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #0a2d4e;
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

        /* ══════════════════════════════════════
           BADGES STATUT
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

        /* ══════════════════════════════════════
           RÉSUMÉ / COMPTEUR
        ══════════════════════════════════════ */
        .summary {
            background: #eff6ff;
            border-left: 3px solid #0f3b63;
            padding: 5px 10px;
            margin-bottom: 10px;
            font-size: 10px;
            color: #334155;
            border-radius: 0 4px 4px 0;
        }
        .summary strong { color: #0f3b63; }

        /* ══════════════════════════════════════
           PIED DE PAGE
        ══════════════════════════════════════ */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2px solid #0f3b63;
            padding-top: 5px;
            font-size: 8.5px;
            color: #94a3b8;
            background: #fff;
        }
        .footer-inner {
            display: table;
            width: 100%;
        }
        .footer-left  {
            display: table-cell;
            text-align: left;
            color: #475569;
        }
        .footer-left strong { color: #0f3b63; }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
        .footer-center {
            display: table-cell;
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
        }
    </style>
</head>
<body>

    {{-- ════ EN-TÊTE ════ --}}
    <div class="header">

        {{-- Bandeau Royaume du Maroc --}}
        <div class="header-royaume">
            <strong>Royaume du Maroc</strong>
            &nbsp;—&nbsp;
            Ministère du Tourisme, de l'Artisanat et de l'Économie Sociale et Solidaire
        </div>

        {{-- Logo | Nom établissement | Date --}}
        <div class="header-inner">

            <div class="header-logo">
                @if(file_exists(public_path('images/logo-istaht.png')))
                    <img src="{{ public_path('images/logo-istaht.png') }}" alt="Logo ISTAHT">
                @else
                    <div class="header-logo-placeholder">IS</div>
                @endif
            </div>

            <div class="header-center">
                <div class="header-ministere">
                    Institut Spécialisé de Technologie Appliquée
                </div>
                <div class="header-etablissement">
                    Hôtelière et Touristique
                </div>
                <div class="header-ville">— Tanger —</div>
                <div class="header-divider"></div>
                <div class="header-doc-title">@yield('title', 'Export PDF')</div>
            </div>

            <div class="header-right">
                <div class="date-label">Date d'export</div>
                <div class="date-value">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
                <div class="time-value">{{ \Carbon\Carbon::now()->format('H:i') }}</div>
            </div>

        </div>
    </div>

    {{-- ════ RÉSUMÉ OPTIONNEL ════ --}}
    @hasSection('summary')
        <div class="summary">@yield('summary')</div>
    @endif

    {{-- ════ CONTENU ════ --}}
    @yield('content')

    {{-- ════ PIED DE PAGE ════ --}}
    <div class="footer">
        <div class="footer-inner">
            <div class="footer-left">
                <strong>ISTAHT Tanger</strong> — Gestion des stocks &amp; restauration
            </div>
            <div class="footer-center">
                stock.istahttanger.ma
            </div>
            <div class="footer-right">
                Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

</body>
</html>
