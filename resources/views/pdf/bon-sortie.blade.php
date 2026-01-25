<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Sortie {{ $sortieStock->numero }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="m-0 p-0 text-black text-sm leading-snug relative min-h-screen flex flex-col">

    <!-- Main Content -->
    <div class="flex-1 p-5 flex flex-col">

        <!-- HEADER -->
        @include('pdf.header')

        <!-- DOCUMENT INFO -->
        <div class="mb-6">
            <div class="text-center font-bold text-lg underline uppercase py-2">Bon de sortie N° {{ $sortieStock->numero }}</div>
        </div>

        <!--  -->
        <div class="mb-6 flex justify-between">
            <div>
                <div class="font-bold text-base mb-1">Service: ...................... </div>
                <div class="font-bold text-base mb-1">Nom du formateur: ...................... </div>
            </div>
            <div class="font-bold">
                Date : {{ $sortieStock->date_sortie->toDateString() }}
            </div>
        </div>

        <!-- Articles Table -->
        <div class="overflow-x-auto mb-6">
            <table class="w-full border border-black border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-200 text-center font-bold">
                        <th class=" border border-black p-1">Code d'article</th>
                        <th class=" border border-black p-1">Désignation</th>
                        <th class=" border border-black p-1">Unité</th>
                        <th class="border border-black p-1">Quantité</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($sortieStock->lignesSortie as $ligne)
                        <tr>
                            <td class="border border-black text-center p-1">{{ $ligne->article->reference }}</td>
                            <td class="border border-black text-center p-1 text-left">{{ $ligne->article->designation }}</td>
                            <td class="border border-black text-center p-1">{{ $ligne->article->unite_mesure }}</td>
                            <td class="border border-black text-center p-1">{{ $ligne->quantite }}</td>

                        </tr>
                    @endforeach
                    
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-3 gap-8 text-center mt-8 mt-auto mb-24">
            <div>
                <div class="font-bold text-base">Le magasinier</div>
            </div>
            <div>
                <div class="font-bold text-base">Le formateur</div>
            </div>
            <div>
                <div class="font-bold text-base">Le directeur</div>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    @include('pdf.footer')

</body>
</html>
