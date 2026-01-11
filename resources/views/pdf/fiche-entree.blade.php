<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiche des entrées </title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">

    <!-- Main Content -->
    <div class="flex-1 p-5">

        <!-- TITLE -->
        <div class="text-center font-bold text-2xl uppercase underline mb-2">
            Fiche des entrées
        </div>
        <div class="text-center font-bold mb-4">
            @if ($endDate)
                {{ 'de ' . \Carbon\Carbon::parse($startDate)->format('Y-m-d') . ' à ' . \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}
            @else
                {{ 'du mois de ' . \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}
            @endif
        </div>


        <!-- ARTICLES TABLE -->
        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-black border-collapse text-xs">
                <thead class="bg-gray-200 font-bold">
                    <tr>
                        <th class="border border-black p-1">Date d'entrée</th>
                        <th class="border border-black p-1">Code d'article</th>
                        <th class="border border-black p-1">Désignation d'article</th>
                        <th class="border border-black p-1">Stock initial</th>
                        <th class="border border-black p-1">Quantité entrée</th>
                        <th class="border border-black p-1">Réf bon de récep</th>
                        <th class="border border-black p-1">Stock actuel</th>
                        <th class="border border-black p-1">Unité</th>
                        <th class="border border-black p-1">Prix unitaire</th>
                        <th class="border border-black p-1">TVA appliqué</th>
                        <th class="border border-black p-1">Montant total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($articles as $article)
                        <tr>
                            <td class="border border-black text-center p-1">{{ $article['date_entree'] }}</td>
                            <td class="border border-black text-center p-1">{{ $article['code_article'] }}</td>
                            <td class="border border-black text-center p-1">{{ $article['designation_article'] }}</td>
                            <td class="border border-black text-center p-1">{{ $article['stock_initial'] }}</td>
                            <td class="border border-black text-center p-1">{{ $article['quantite_entree'] }}</td>
                            <td class="border border-black text-center p-1">{{ $article['reference_bon_reception'] }}</td>
                            <td class="border border-black text-center p-1">{{ $article['stock_actuel'] }}</td>
                            <td class="border border-black text-center p-1">{{ $article['unite'] }}</td>
                            <td class="border border-black text-center p-1">{{ number_format($article['prix_unitaire'], 2) }} DH</td>
                            <td class="border border-black text-center p-1">{{ $article['tva_appliquee'] }}</td>
                            <td class="border border-black text-center p-1">{{ number_format($article['total_ttc'], 2) }} DH</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>




        <!-- Signatures -->
        <div class="flex justify-end" style="margin-top: 36px;">
            <div class=" ">Le responsable </div>
        </div>

    </div>

</body>
</html>
