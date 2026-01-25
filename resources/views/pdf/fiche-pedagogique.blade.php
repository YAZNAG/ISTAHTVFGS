<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche technique pédagogique </title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">

    <!-- Main Content -->
    <div class="flex-1 p-5">

        <!-- HEADER -->
        @include('pdf.header')

        <!-- TITLE -->
        <div class="text-center font-bold text-2xl uppercase underline mb-4">
            Fiche technique pédagogique
        </div>
        <div class="text-left mt-8">
            <p><span class="font-bold">Module de formation:</span> {{ $fiche->nom }}</p>
            <p><span class="font-bold">Plat:</span> {{ $fiche->plat }}</p>
            <p><span class="font-bold">Nom du formateur:</span> {{ $fiche->responsable }}</p>
            <p><span class="font-bold">Nombre d'effectif:</span> {{ $fiche->effectif }}</p>
        </div>


        <!-- ARTICLES TABLE -->
        <div class="my-8">
            <table border="1" cellspacing="0" cellpadding="5" class="w-full border border-black border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black p-1">Procédé de préparation</th>
                        <th class="border border-black p-1">Ingrédient</th>
                        <th class="border border-black p-1">Code d'article</th>
                        <th class="border border-black p-1">Quantité</th>
                        <th class="border border-black p-1">Unité de mesure</th>
                        <th class="border border-black p-1">Prix unitaire HT</th>
                        <th class="border border-black p-1">Coût total TTC</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($fiche->ingredients as $ingredient)
                    <tr>

                        <td class="border border-black p-1 text-left">{{ $ingredient->article->designation }}</td>
                        <td class="border border-black p-1">{{ $ingredient->article->reference }}</td>
                        <td class="border border-black p-1">{{ $ingredient->quantite }}</td>
                        <td class="border border-black p-1">{{ $ingredient->article->unite_mesure }}</td>
                        <td class="border border-black p-1">{{ $ingredient->prix_unitaire }}</td>
                        <td class="border border-black p-1">{{ $ingredient->total_ttc }}</td>
                    </tr>
                @endforeach


                    <!-- Total rows -->
                    <tr>
                        <td colspan="5" class="border border-black p-1 text-right font-bold">Coût total</td>
                        <td class="border border-black p-1">{{ $totalTtc }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="border border-black p-1 text-right font-bold">Coût total / effectif</td>
                        <td class="border border-black p-1">{{ $total_effectif }}</td>
                    </tr>
                </tbody>
            </table>






        <!-- Signatures -->
        <div class="flex justify-evenly" style="margin-top: 36px;">
            <div class="font-bold text-base">Tanger, le .............</div>
            <div class="font-bold text-base">Le formateur </div>
        </div>

    </div>
    </div>

    <!-- FOOTER -->
    @include('pdf.footer')

</body>
</html>
