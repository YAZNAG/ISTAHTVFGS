<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de commande N° {{ $chefCommande->numero }}</title>
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
            Bon de commande N° {{ $chefCommande->numero }}
        </div>


        <!-- ARTICLES TABLE -->
        <div class="overflow-x-auto mb-4">
            <table class="w-full border border-black border-collapse text-xs">
                <thead class="bg-gray-200 font-bold">
                    <tr>
                        <th class="border border-black p-1">Code d'article</th>
                        <th class="border border-black p-1">Désignation</th>
                        <th class="border border-black p-1">Unité</th>
                        <th class="border border-black p-1">Quantité</th>
                        <th class="border border-black p-1">Prix unitaire</th>
                        <th class="border border-black p-1">TVA appliquée</th>
                        <th class="border border-black p-1">Montant total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalHT = $totalTVA = $totalTTC = 0;
                    @endphp
                    @foreach($chefCommande->items as $item)
                        @php
                            $prixHT = $item->article->currentBonCommandeArticle->prix_unitaire_ht ?? 0;
                            $quantite = $item->quantite_commandee ?? 0;
                            $tauxTVA = $item->article->currentBonCommandeArticle->taux_tva ?? 0;

                            $montantHT = $prixHT * $quantite;
                            $montantTVA = $montantHT * ($tauxTVA / 100);
                            $montantTTC = $montantHT + $montantTVA;
                        @endphp
                        <tr>
                            <td class="border border-black p-1 text-center">{{ $item->article->reference ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-center">{{ $item->article->designation ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-center">{{ $item->article->unite_mesure ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-center">{{ number_format((float) $quantite, 2) }}</td>
                            <td class="border border-black p-1 text-right">{{ $prixHT }} DH</td>
                            <td class="border border-black p-1 text-center">{{ $tauxTVA }}%</td>
                            <td class="border border-black p-1 text-right">{{ number_format((float) $montantTTC, 2, ',', ' ') }} DH</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-3 gap-8 text-center px-16" style="margin-top: 36px;">
            <div class="text-base border-dashed border-b border-black pb-8">Le magasinier</div>
            <div class="text-base border-dashed border-b border-black pb-8">L'économe</div>
            <div class="text-base border-dashed border-b border-black pb-8">Le directeur</div>
            </div>
        </div>



    </div>

</body>
</html>
