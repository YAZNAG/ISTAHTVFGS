<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Liste des articles</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">
    <div class="flex-1 p-5">
        <!-- TITLE -->
        <div class="text-center font-bold text-2xl uppercase underline mb-2">
            Liste des articles
        </div>
        <div class="text-center font-bold mb-4">
            {{ 'de ' . $now }}
        </div>

        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-black border-collapse text-xs">
                <thead class="font-bold">
                    <tr>
                        <th class="border border-black p-1">Réf. Article</th>
                        <th class="border border-black p-1">Categorie</th>
                        <th class="border border-black p-1">Désignation</th>
                        <th class="border border-black p-1">Unité</th>
                        <th class="border border-black p-1">Qté stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td class="border border-black text-center p-1">{{ $row->reference }}</td>
                        <td class="border border-black text-center p-1">{{ $row->categorie->nom }}</td>
                        <td class="border border-black text-center p-1">{{ $row->designation }}</td>
                        <td class="border border-black text-center p-1">{{ $row->unite_mesure }}</td>
                        <td class="border border-black text-center p-1">{{ $row->quantite_stock }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>