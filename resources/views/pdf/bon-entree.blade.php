<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>L'entrée de stock </title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">

    <!-- Main Content -->
    <div class="flex-1 p-5">

        <!-- HEADER -->
        @include('pdf.header')

        <!-- TITLE -->
        <div class="text-center font-bold text-4xl uppercase underline mb-4">
            L'entrée de stock N° {{ $entree->numero }}
        </div>


        <!-- ARTICLES TABLE -->
        <div class="max-w-6xl mx-auto p-6">

            <!-- Stock Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded p-6 border">
                    <h2 class="font-semibold text-gray-800 mb-3">Informations générales</h2>
                    <p><strong class="text-gray-700">Numéro :</strong> {{ $entree->numero }}</p>
                    <p><strong class="text-gray-700">Statut :</strong> {{ $entree->statut }}</p>
                    <p><strong class="text-gray-700">Date d'entrée :</strong> {{ $entree->date_entree->format('d/m/Y') }}</p>
                    <p><strong class="text-gray-700">Référence bon de réception :</strong> {{ $entree->bonReception->numero ?? '—' }}</p>
                </div>

                <div class="bg-white rounded p-6 border">
                    <h2 class="font-semibold text-gray-800 mb-3">Fournisseur</h2>
                    @if($entree->fournisseur)
                        <p><strong class="text-gray-700">Nom :</strong> {{ $entree->fournisseur->nom }}</p>
                        <p><strong class="text-gray-700">Téléphone :</strong> {{ $entree->fournisseur->telephone ?? '—' }}</p>
                        <p><strong class="text-gray-700">Email :</strong> {{ $entree->fournisseur->email ?? '—' }}</p>
                        <p><strong class="text-gray-700">Adresse :</strong> {{ $entree->fournisseur->adresse ?? '—' }}</p>
                    @else
                        <p class="text-gray-500 italic">Aucun fournisseur lié.</p>
                    @endif
                </div>
            </div>

            <!-- Articles Table -->
            <div class="bg-white rounded border">
                <table class="w-full border border-black border-collapse text-xs">
                    <thead class="bg-gray-200 font-bold">
                        <tr class="">
                            <th class="border border-black p-1">Code Article</th>
                            <th class="border border-black p-1">Désignation</th>
                            <th class="border border-black p-1">Quantité</th>
                            <th class="border border-black p-1">Prix unitaire (DH)</th>
                            <th class="border border-black p-1">Montant HT</th>
                            <th class="border border-black p-1">TVA (%)</th>
                            <th class="border border-black p-1">Montant TTC</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($entree->lignesEntree as $ligne)
                        <tr class="text-sm border-b hover:bg-gray-50">
                            <td class="border border-black p-1">{{ $ligne->article->reference }}</td>
                            <td class="border border-black p-1 text-left">{{ $ligne->article->designation }}</td>
                            <td class="border border-black p-1">{{ number_format($ligne->quantite, 2) }}</td>
                            <td class="border border-black p-1">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }}</td>
                            <td class="border border-black p-1 text-right">{{ number_format($ligne->prix_total, 2, ',', ' ') }}</td>
                            <td class="border border-black p-1 text-right">{{ $ligne->taux_tva ?? 0 }}%</td>
                            <td class="border border-black p-1 text-right">{{ number_format($ligne->total_ttc, 2, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-300 font-bold">
                            <td colspan="4" class="text-right border border-black p-1">Total</td>
                            <td class="text-right border border-black p-1">
                                {{ number_format($entree->lignesEntree->sum('prix_total'), 2, ',', ' ') }}
                            </td>
                            <td></td>
                            <td class="text-right border border-black p-1">
                                {{ number_format($entree->lignesEntree->sum('total_ttc'), 2, ',', ' ') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    @include('pdf.footer')

</body>
</html>
