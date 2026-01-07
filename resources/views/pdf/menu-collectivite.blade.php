<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Fiche technique ( Menu collectivité) </title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">

    <!-- Main Content -->
    <div class="flex-1 p-5">

        <!-- TITLE -->
        <div class="text-center  mb-2">
            <h2 class="font-bold text-lg uppercase underline">Fiche technique ( Menu collectivité)</h2>
            <p class="text-center font-bold">journée du {{ $menu->date->format('Y-m-d') }}</p>
        </div>

        <div class="mt-4 grid grid-cols-3 gap-4">
            <div>
                <h3 class="font-bold text-md mb-2">Petit déjeuner</h3>
                <p>Hors d'œuvre /Entrée : {{ $repas['petit-dejeuner']['hors_doeuvre'] }}</p>
                <p>Plat du jour : {{ $repas['petit-dejeuner']['plat'] }}</p>
                <p>Dessert : {{ $repas['petit-dejeuner']['dessert'] }} </p>
                <p>Plat spécial : {{ $repas['petit-dejeuner']['plat_special'] }}</p>
                
            </div>
            <div>
                <h3 class="font-bold mb-2">Déjeuner</h3>
                <p>Hors d'œuvre /Entrée : {{ $repas['dejeuner']['hors_doeuvre'] }}</p>
                <p>Plat du jour : {{ $repas['dejeuner']['plat'] }}</p>
                <p>Dessert : {{ $repas['dejeuner']['dessert'] }} </p>
                <p>Plat spécial : {{ $repas['dejeuner']['plat_special'] }}</p>
            </div>
            <div>
                <h3 class="font-bold mb-2">Dîner</h3>
                <p>Hors d'œuvre /Entrée : {{ $repas['diner']['hors_doeuvre'] }}</p>
                <p>Plat du jour : {{ $repas['diner']['plat'] }}</p>
                <p>Dessert : {{ $repas['diner']['dessert'] }} </p>
                <p>Plat spécial : {{ $repas['diner']['plat_special'] }}</p>
            </div>
        </div>

        <div class="my-4">
            <p>Nombre de couvert : {{ $menu->effectif }}</p>
            <p>Formateur responsable: {{ $menu->responsable }}</p>
        </div>


        <!-- ARTICLES TABLE -->
        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-black border-collapse text-xs">
                <thead class="bg-gray-200">
                    <!-- First row: Main headers -->
                    <tr>
                        <th rowspan="3" class="border border-black p-1">Denrées et nature</th>
                        <th rowspan="3" class="border border-black p-1">Code d'article</th>
                        <th colspan="12" class="border border-black p-1 text-center font-bold">Quantité</th>
                        <th rowspan="3" class="border border-black p-1">Unité</th>
                        <th rowspan="3" class="border border-black p-1">Prix unitaire</th>
                        <th rowspan="3" class="border border-black p-1">Cout total TTC</th>
                    </tr>

                    <!-- Second row: Meal headers -->
                    <tr>
                        <th colspan="4" class="border border-black p-1 text-center">Petit déjeuner</th>
                        <th colspan="4" class="border border-black p-1 text-center">Déjeuner</th>
                        <th colspan="4" class="border border-black p-1 text-center">Dîner</th>
                    </tr>

                    <!-- Third row: Repas/Plat headers -->
                    <tr>
                        <th class="border border-black p-1">Hors d'œuvre</th>
                        <th class="border border-black p-1">Plat du jour</th>
                        <th class="border border-black p-1">Dessert</th>
                        <th class="border border-black p-1">Plat spécial</th>
                        <th class="border border-black p-1">Hors d'œuvre</th>
                        <th class="border border-black p-1">Plat du jour</th>
                        <th class="border border-black p-1">Dessert</th>
                        <th class="border border-black p-1">Plat spécial</th>
                        <th class="border border-black p-1">Hors d'œuvre</th>
                        <th class="border border-black p-1">Plat du jour</th>
                        <th class="border border-black p-1">Dessert</th>
                        <th class="border border-black p-1">Plat spécial</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @foreach($data as $category => $d)
                    <tr >
                        <td class="border border-black text-center p-1" rowspan="{{count($d) + 1}}">{{$category}}</td>
                        @foreach($d as $article)
                        <tr>
                            @php
                                $total += $article['total_ttc'];
                            @endphp
                            <td class="border border-black text-center p-1">{{$article['article_code']}}</td>

                            <td class="border border-black text-center p-1">{{$article['quantites']['petit_dejeuner']['hors_doeuvres']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['petit_dejeuner']['plats']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['petit_dejeuner']['desserts']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['petit_dejeuner']['plats_special']}}</td>

                            <td class="border border-black text-center p-1">{{$article['quantites']['dejeuner']['hors_doeuvres']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['dejeuner']['plats']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['dejeuner']['desserts']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['dejeuner']['plats_special']}}</td>

                            <td class="border border-black text-center p-1">{{$article['quantites']['diner']['hors_doeuvres']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['diner']['plats']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['diner']['desserts']}}</td>
                            <td class="border border-black text-center p-1">{{$article['quantites']['diner']['plats_special']}}</td>

                            <td class="border border-black text-center p-1">{{$article['article_unite_mesure']}}</td>
                            <td class="border border-black text-center p-1">{{$article['article_prix']}} DH</td>
                            <td class="border border-black text-center p-1">{{ number_format($article['total_ttc'], 2, '.', ' ')}} DH</td>
                        </tr>
                        @endforeach
                    </tr>
                    @endforeach
                    <tr >
                        <td colspan="16" class="border border-black text-right p-1 font-bold">Cout Global </td>
                        <td class="border border-black text-center p-1 font-bold">{{number_format($total, 2, '.', ' ')}} DH</td>
                    </tr>
                    <tr>
                        <td colspan="16" class="border border-black text-right p-1 font-bold">Cout Ration</td>
                        <td class="border border-black text-center p-1 font-bold">{{number_format($total / $menu->effectif, 2, '.', ' ')}} DH</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>