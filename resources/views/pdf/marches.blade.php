<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Export marches</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #132238;
            font-size: 10px;
        }

        h1 {
            margin: 0 0 6px;
            color: #0f3b63;
            font-size: 22px;
        }

        .muted {
            color: #64748b;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #0f3b63;
            color: #fff;
            text-align: left;
            padding: 7px;
        }

        td {
            border-bottom: 1px solid #d8e0ea;
            padding: 7px;
            vertical-align: top;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .badge {
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: 700;
        }

        .info { background: #e0f2fe; color: #075985; }
        .warning { background: #fef3c7; color: #92400e; }
        .success { background: #dcfce7; color: #166534; }
        .danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>Liste des marches</h1>
    <div class="muted">Genere le {{ optional($generatedAt)->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Objet</th>
                <th>Categorie</th>
                <th>Fournisseur</th>
                <th>Dates</th>
                <th>Statut</th>
                <th class="amount">HT</th>
                <th class="amount">TTC</th>
                <th class="amount">Consomme</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($marches as $marche)
                @php
                    $tone = match($marche['statut']) {
                        'cree' => 'info',
                        'attente_livraison', 'livre_partiellement' => 'warning',
                        'livre_completement' => 'success',
                        'annule' => 'danger',
                        default => 'info',
                    };
                @endphp
                <tr>
                    <td>{{ $marche['reference'] }}</td>
                    <td>{{ $marche['objet'] }}</td>
                    <td>{{ $marche['categorie']['nom'] ?? '-' }}</td>
                    <td>{{ $marche['fournisseur']['nom_affichage'] ?? 'Non attribue' }}</td>
                    <td>
                        {{ $marche['date_debut'] ?? '-' }}<br>
                        {{ $marche['date_fin'] ?? '-' }}
                    </td>
                    <td><span class="badge {{ $tone }}">{{ $marche['statut'] }}</span></td>
                    <td class="amount">{{ number_format($marche['total_ht'], 2, ',', ' ') }}</td>
                    <td class="amount">{{ number_format($marche['total_ttc'], 2, ',', ' ') }}</td>
                    <td class="amount">{{ number_format($marche['consumption_percent'], 1, ',', ' ') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
