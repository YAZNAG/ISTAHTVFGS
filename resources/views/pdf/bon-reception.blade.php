<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Réception {{ $reception->numero }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">

    <!-- Main Content -->
    <div class="page">

        <!-- DOCUMENT INFO -->
        <div class="mb-6">
            <div class="text-center font-bold text-lg underline uppercase mb-4">Bon de réception N° {{ $reception->numero }}</div>
        </div>

        <!-- Fournisseur -->
        <div class="mb-6">
            <div class="font-bold text-base mb-1">Fournisseur: {{ $reception->BonLivraison->fournisseur->nom ?? $reception->BonLivraison->fournisseur->raison_sociale ?? '........................................' }}</div>
            <div class="font-bold">
                Date : {{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}
            </div>
        </div>

        <!-- Articles Table -->
        <div class="overflow-x-auto mb-6">
            <table class="w-full border border-black border-collapse text-xs">
                <thead>
                    <tr class="text-center font-bold">
                        <th class=" border border-black p-1">Code d'article</th>
                        <th class=" border border-black p-1 text-left">Désignation</th>
                        <th class="border border-black p-1">Quantité</th>
                        <th class=" border border-black p-1">Prix unitaire</th>
                        <th class=" border border-black p-1">Montant TVA</th>
                        <th class=" border border-black p-1">Prix total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reception->BonLivraison->items as $item)
                        <tr>
                            <td class="border border-black text-center p-1">{{ $item->article->reference ?? 'N/A' }}</td>
                            <td class="border border-black p-1">{{ $item->article->designation ?? 'Article non trouvé' }}</td>
                            <td class="border border-black text-center p-1">{{ number_format($item->quantite, 2) }}</td>
                            <td class="border border-black text-right p-1">{{ number_format($item->prix_unitaire, 2, ',', ' ') }} DH</td>
                            <td class="border border-black text-right p-1">{{ number_format($item->montant_tva, 2, ',', ' ') }} DH</td>
                            <td class="border border-black text-right p-1">{{ number_format($item->montant_ttc, 2, ',', ' ') }} DH</td>
                        </tr>
                    @endforeach

                    <tr class="font-bold">
                        <td colspan="5" class="border border-black text-right p-1">Total</td>
                        <td class="border border-black text-right p-1">{{ number_format($reception->BonLivraison->total_ttc, 2, ',', ' ') }} DH</td>
                    </tr>
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

    <!-- FOOTER -->

</body>
</html>
