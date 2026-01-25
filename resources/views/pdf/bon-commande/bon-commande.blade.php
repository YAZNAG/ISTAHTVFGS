<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Appel d'offre {{ $bonCommande->reference }}</title>
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
            Appel d'offre N° {{ $bonCommande->reference }}
        </div>

        <!-- SUPPLIER INFO -->
        <div class="border border-black p-4 mb-4 bg-gray-100 space-y-1">
            <div class=""><span class="font-bold">Fournisseur:</span> <span>{{ $fournisseur->nom ?? $fournisseur->raison_sociale ?? 'Non spécifié' }}</span></div>
            <div class=""><span class="font-bold">Adresse:</span> <span>{{ $fournisseur->adresse ?? 'Non spécifié' }}</span></div>
            <div class=""><span class="font-bold">Catégorie des articles:</span> <span>{{ $bonCommande->categoriePrincipale->nom ?? 'N/A' }}</span></div>
            <div class=""><span class="font-bold">Commande pour le:</span> <span>{{ \Carbon\Carbon::parse($bonCommande->date_mise_ligne)->format('d/m/Y') }}</span></div>
            <div class=""><span class="font-bold">Date limite réception:</span> <span>{{ \Carbon\Carbon::parse($bonCommande->date_limite_reception)->format('d/m/Y') }}</span></div>
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
                    @foreach($articles as $article)
                        @php
                            $prixHT = $article->prix_unitaire_ht ?? 0;
                            $quantite = $article->quantite_commandee ?? 0;
                            $tauxTVA = $article->taux_tva ?? 0;

                            $montantHT = $prixHT * $quantite;
                            $montantTVA = $montantHT * ($tauxTVA / 100);
                            $montantTTC = $montantHT + $montantTVA;

                            $totalHT += $montantHT;
                            $totalTVA += $montantTVA;
                            $totalTTC += $montantTTC;
                        @endphp
                        <tr>
                            <td class="border border-black p-1 text-center">{{ $article->article->reference ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-left">{{ $article->article->designation ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-center">{{ $article->article->unite_mesure ?? 'N/A' }}</td>
                            <td class="border border-black p-1 text-center">{{ number_format($quantite, 2) }}</td>
                            <td class="border border-black p-1 text-right">{{ number_format($prixHT, 2, ',', ' ') }} DH</td>
                            <td class="border border-black p-1 text-center">{{ $tauxTVA }}%</td>
                            <td class="border border-black p-1 text-right">{{ number_format($montantTTC, 2, ',', ' ') }} DH</td>
                        </tr>
                    @endforeach

                    <!-- TOTALS -->
                    <tr class="bg-gray-200 font-bold">
                        <td colspan="4" class="text-right border border-black p-1">TOTAL HT:</td>
                        <td class="text-right border border-black p-1">{{ number_format($totalHT, 2, ',', ' ') }} DH</td>
                        <td class="text-center border border-black p-1">-</td>
                        <td class="text-right border border-black p-1">{{ number_format($totalHT, 2, ',', ' ') }} DH</td>
                    </tr>
                    <tr class="bg-gray-200 font-bold">
                        <td colspan="4" class="text-right border border-black p-1">TOTAL TVA:</td>
                        <td class="text-right border border-black p-1">{{ number_format($totalTVA, 2, ',', ' ') }} DH</td>
                        <td class="text-center border border-black p-1">-</td>
                        <td class="text-right border border-black p-1">{{ number_format($totalTVA, 2, ',', ' ') }} DH</td>
                    </tr>
                    <tr class="bg-gray-300 font-bold">
                        <td colspan="4" class="text-right border border-black p-1">TOTAL TTC:</td>
                        <td class="text-right border border-black p-1">{{ number_format($totalTTC, 2, ',', ' ') }} DH</td>
                        <td class="text-center border border-black p-1">-</td>
                        <td class="text-right border border-black p-1">{{ number_format($totalTTC, 2, ',', ' ') }} DH</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- TOTAL IN WORDS -->
        <div class="border border-black p-2 bg-gray-100 font-bold">
            Arrêtée la présente commande à la somme de : {{ number_format($totalTTC, 2, ',', ' ') }} Dirhams
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
