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
        <!-- <div class="text-center font-bold text-2xl uppercase underline mb-2">
            Decompte
        </div> -->
        <div class="mb-4">
            <div class="grid grid-cols-2">
                <p>Marché cadre: {{ $marche->reference }}</p>
                <p>Objet: {{ $marche->objet}}</p>
            </div>
            
            <div class="grid grid-cols-4">
                <p class="col-span-2">Titulaire : {{ $marche->fournisseur->nom }}</p>
                <p class="col-span-2">Adresse: {{ $marche->fournisseur->adresse }}</p>
                <p>TP: {{ $marche->fournisseur->tp }}</p>
                <p>IF: {{ $marche->fournisseur->if }}</p>
                <p>RC: {{ $marche->fournisseur->rc }}</p>
                <p>CNSS: {{ $marche->fournisseur->cnss }}</p>
                <p class="col-span-2">CB: {{ $marche->fournisseur->cb }}</p>

            </div>
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
                        <th class="px-3 py-2 border border-black text-right">Montant TVA</th>
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
                        <td class="border border-black text-center p-1 text-right">{{ $row['montant_tva'] }}</td>
                        <td class="border border-black text-center p-1 text-right">{{ $row['montant_ttc'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- RECAPITULATION TABLE -->
        <div class="mt-6">
            <div class="text-center font-bold text-lg uppercase underline mb-3">
                Recapitulation
            </div>

            <table class="w-full border border-black border-collapse text-xs">
                <thead class="font-bold">
                    <tr>
                        <th class="px-3 py-2 border border-black text-center underline">Nature des dépenses</th>
                        <th class="px-3 py-2 border border-black text-center underline">Dépenses faites</th>
                        <th class="px-3 py-2 border border-black text-center underline">Retenues de Garanties</th>
                        <th class="px-3 py-2 border border-black text-center underline">Reste</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black p-2">Travaux terminés</td>
                        <td class="border border-black p-2 text-right font-mono font-bold"></td>
                        <td class="border border-black p-2 text-right font-mono font-bold"></td>
                        <td class="border border-black p-2 text-right font-mono font-bold">{{ $travaux_termine }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black p-2">Travaux non terminés</td>
                        <td class="border border-black p-2 text-right font-mono"></td>
                        <td class="border border-black p-2 text-right font-mono"></td>
                        <td class="border border-black p-2 text-right font-mono">{{ $travaux_non_termine }}</td>
                    </tr>
                    <!-- <tr>
                        <td class="border border-black p-2">Approvisionnement</td>
                        <td class="border border-black p-2 text-right font-mono"></td>
                        <td class="border border-black p-2 text-right font-mono"></td>
                        <td class="border border-black p-2 text-right font-mono"></td>
                    </tr> -->
                    <tr>
                        <td class="border border-black p-2 italic" colspan="3">A Déduire les dépenses des acomptes precedement payées</td>
                        <td class="border border-black p-2 text-right font-mono font-bold">{{ $travaux_termine }}</td>
                    </tr>
                    <!-- <tr>
                        <td class="border border-black p-2" colspan="3">Reste à payer sur l'exercice en cours</td>
                        <td class="border border-black p-2 text-right font-mono"></td>
                    </tr> -->
                    <tr class="bg-gray-100 font-bold">
                        <td class="border border-black p-2 border-2" colspan="3">Montant de l'acompte à délivrer</td>
                        <td class="border border-black p-2 text-right font-mono border-2">{{ $decompte_total }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</body>

</html>