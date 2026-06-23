<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Export categories</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #132238;
            font-size: 12px;
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
            padding: 8px;
        }

        td {
            border-bottom: 1px solid #d8e0ea;
            padding: 8px;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 11px;
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
    <h1>Liste des categories</h1>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Statut</th>
                <th>Nombre articles</th>
                <th>Date creation</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $categorie)
                <tr>
                    <td>{{ $categorie->code }}</td>
                    <td>{{ $categorie->nom }}</td>
                    <td>
                        <span class="badge {{ $categorie->est_actif ? 'active' : 'inactive' }}">
                            {{ $categorie->est_actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>{{ (int) $categorie->articles_count }}</td>
                    <td>{{ optional($categorie->created_at)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
