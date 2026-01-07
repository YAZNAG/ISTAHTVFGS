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
            <p class="text-center font-bold">du {{ $startDate->format('Y-m-d') }} au {{ $endDate->format('Y-m-d') }}</p>
        </div>

        <!-- ARTICLES TABLE -->
        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-black border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-black p-1 text-center" rowspan="2">Date</th>
                        <th class="border border-black p-1 text-center" colspan="4">Petit déjeuner</th>
                        <th class="border border-black p-1 text-center" colspan="4">Déjeuner</th>
                        <th class="border border-black p-1 text-center" colspan="4">Dîner</th>
                    </tr>
                    <tr class="bg-gray-200">
                        {{-- petit déjeuner --}}
                        <th class="border border-black p-1">Entrée</th>
                        <th class="border border-black p-1">Plat</th>
                        <th class="border border-black p-1">Dessert</th>
                        <th class="border border-black p-1">Plat spécial</th>
                        {{-- déjeuner --}}
                        <th class="border border-black p-1">Entrée</th>
                        <th class="border border-black p-1">Plat</th>
                        <th class="border border-black p-1">Dessert</th>
                        <th class="border border-black p-1">Plat spécial</th>
                        {{-- dîner --}}
                        <th class="border border-black p-1">Entrée</th>
                        <th class="border border-black p-1">Plat</th>
                        <th class="border border-black p-1">Dessert</th>
                        <th class="border border-black p-1">Plat spécial</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($menus as $menu)
                        <tr @class(['border-t-2 border-black' => $menu['date']->isStartOfWeek()])>
                            <td class="border border-black text-center p-1">{{$menu['date']->toDateString()}}</td>

                            <td class="border border-black text-center p-1">{{$menu['petit_dejeuner']['hors_doeuvres']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['petit_dejeuner']['plat']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['petit_dejeuner']['dessert']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['petit_dejeuner']['plat_special'] ?? '-----'}}</td>

                            <td class="border border-black text-center p-1">{{$menu['dejeuner']['hors_doeuvres']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['dejeuner']['plat']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['dejeuner']['dessert']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['dejeuner']['plat_special'] ?? '-----'}}</td>
                            
                            <td class="border border-black text-center p-1">{{$menu['diner']['hors_doeuvres']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['diner']['plat']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['diner']['dessert']}}</td>
                            <td class="border border-black text-center p-1">{{$menu['diner']['plat_special'] ?? '-----'}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>