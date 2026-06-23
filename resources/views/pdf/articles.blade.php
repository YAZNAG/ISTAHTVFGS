<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Export articles</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #132238;
            font-size: 11px;
        }

        h1 {
            margin: 0 0 18px;
            color: #0f3b63;
            font-size: 22px;
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
        }

        .badge {
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .active {
            background: #dcfce7;
            color: #166534;
        }

        .inactive {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <h1>Liste des articles</h1>

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Designation</th>
                <th>Categorie</th>
                <th>Unite</th>
                <th>Stock actuel</th>
                <th>Seuil min.</th>
                <th>Seuil max.</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($articles as $article)
                <tr>
                    <td>{{ $article->reference }}</td>
                    <td>{{ $article->designation }}</td>
                    <td>{{ $article->categorie?->nom }}</td>
                    <td>{{ $article->unite_mesure }}</td>
                    <td>{{ (float) ($article->quantite_stock ?? 0) }}</td>
                    <td>{{ (float) ($article->seuil_minimal ?? 0) }}</td>
                    <td>{{ (float) ($article->seuil_maximal ?? 0) }}</td>
                    <td>
                        <span class="badge {{ $article->est_actif ? 'active' : 'inactive' }}">
                            {{ $article->est_actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
