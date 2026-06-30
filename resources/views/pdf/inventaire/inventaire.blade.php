<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inventaire {{ $inventaire->semaine }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: "Noto Sans", sans-serif;
        }
    </style>
</head>

<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">

    <!-- Main Content -->
    <div class="page">

        <!-- TITLE -->
        <div class="text-center font-bold text-lg uppercase underline mb-4">
            Inventaire N° {{ $inventaire->numero }}
        </div>

        <p class="text-center mb-4"><span class="font-bold">Semaine:</span> {{ $inventaire->semaine }}</p>

        <!-- ARTICLES TABLE -->
        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-black border-collapse text-xs">
                <thead class="bg-gray-200 font-bold">
                    <tr>
                        <th class="border border-black p-1">Code d'article</th>
                        <th class="border border-black p-1">Désignation</th>
                        <th class="border border-black p-1">Unité</th>
                        <th class="border border-black p-1">Quantité entrée</th>
                        <th class="border border-black p-1">Quantité sortie</th>
                        <th class="border border-black p-1">Stock réel</th>
                        <th class="border border-black p-1">Stock théorique </th>
                        <th class="border border-black p-1">Ecart </th>
                        <th class="border border-black p-1">Observations</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach($inventaire->lignes as $ligne)
                        <tr>
                            <td class="border border-black p-1 text-center">{{ $ligne->code_article ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-left">{{ $ligne->designation ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-center">{{ $ligne->unite_mesure ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-center">{{ number_format($ligne->qte_entree, 2) }}</td>
                            <td class="border border-black p-1 text-center">{{ number_format($ligne->qte_sortie, 2) }}</td>
                            <td class="border border-black p-1 text-center">{{ number_format($ligne->stock_theorique, 2) }}</td>
                            <td class="border border-black p-1 text-center">{{ number_format($ligne->stock_reel, 2) }}</td>
                            <td class="border border-black p-1 text-center">{{ number_format($ligne->ecart, 2) }}</td>

                            <td class="border border-black p-1 text-center">{{ $ligne->observations }} qsdqsd qsdqsdqs</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="flex justify-between px-16" style="margin-top: 36px;">
            <div class="text-base">Date: {{ date('d/m/Y') }}</div>
            <div class="text-base text-center border-dashed border-b border-black pb-8 w-[200px]">Le responsable </div>
            </div>
        </div>



    </div>

</body>
</html>
