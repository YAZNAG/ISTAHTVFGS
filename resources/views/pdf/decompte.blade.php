<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Decompte</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">
    <div class="flex-1 p-5">
        <!-- TITLE -->
        <div class="text-center font-bold text-2xl uppercase underline mb-2">
            Decompte
        </div>

        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-black border-collapse text-xs">
                <thead class="font-bold">
                    <tr>
                        <th class="px-3 py-2 border border-black text-center ">N°</th>
                        <th class="px-3 py-2 border border-black text-center ">Désignation</th>
                        <th class="px-3 py-2 border border-black text-center ">Unité</th>
                        <th class="px-3 py-2 border border-black text-center">Qté</th>
                        <th class="px-3 py-2 border border-black text-right">PU HT</th>
                        <th class="px-3 py-2 border border-black text-right">TVA</th>
                        <th class="px-3 py-2 border border-black text-right">Total HT</th>
                        <th class="px-3 py-2 border border-black text-right">Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $row)
                    <tr>
                        <td class="border border-black text-center p-1">{{ $loop->iteration }}</td>
                        <td class="border border-black text-center p-1">{{ $row['designation'] }}</td>
                        <td class="border border-black text-center p-1">{{ $row['unite_mesure'] }}</td>
                        <td class="border border-black text-center p-1">{{ $row['quantite'] }}</td>
                        <td class="border border-black text-center p-1">{{ $row['prix_unitaire'] }}</td>
                        <td class="border border-black text-center p-1 text-right">{{ $row['taux_tva'] }}</td>
                        <td class="border border-black text-center p-1 text-right">{{ $row['montant_ht'] }}</td>
                        <td class="border border-black text-center p-1 text-right">{{ $row['montant_ttc'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>