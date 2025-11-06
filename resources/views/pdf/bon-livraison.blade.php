<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Livraison - {{ $livraison['reference'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; font-size: 11px; }
        th { background-color: #f3f4f6; font-weight: 600; }
    </style>
</head>
<body class="bg-white text-gray-800">

    {{-- HEADER --}}
    <div class="flex justify-between items-start">
        <div>
            <img src="{{ $livraison['fournisseur']['logo'] }}" alt="Logo" class="h-24 mb-2">

        </div>

        <div class="text-right">
            <h2 class="text-lg font-bold">Bon de Livraison</h2>
            <p class="text-sm mt-2"><strong>Référence:</strong> {{ $livraison['reference'] }}</p>
            <p class="text-sm"><strong>Date:</strong> {{ $livraison['date_livraison'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Créé le {{ $livraison['created_at'] }}</p>
        </div>
    </div>

    {{-- STATUS --}}
    {{-- <div class="mb-4">
        @php
            $colors = [
                'cree' => 'bg-blue-100 text-blue-800',
                'livre' => 'bg-green-100 text-green-800',
                'annule' => 'bg-red-100 text-red-800',
            ];
            $color = $colors[$livraison['statut']] ?? 'bg-gray-100 text-gray-800';
        @endphp
        <span class="px-3 py-1 rounded text-xs font-medium {{ $color }}">
            {{ ucfirst(str_replace('_', ' ', $livraison['statut'])) }}
        </span>
    </div> --}}

    {{-- FOURNISSEUR & DETAILS --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <h3 class="text-sm font-semibold border-b border-gray-300 mb-1">Fournisseur</h3>
            <p class="text-sm font-semibold">{{ $livraison['fournisseur']['nom'] ?? '-' }}</p>
            <p class="text-sm">{{ $livraison['fournisseur']['adresse'] ?? '' }}</p>
            <p class="text-sm text-gray-600">
                Tél: {{ $livraison['fournisseur']['contact'] ?? '-' }}
                @if(!empty($livraison['fournisseur']['email']))
                    - {{ $livraison['fournisseur']['email'] }}
                @endif
            </p>
        </div>

        <div class="text-right">
            <h3 class="text-sm font-semibold border-b border-gray-300 mb-1">Détails</h3>
            <p class="text-sm"><strong>Réceptionné par:</strong> {{ $livraison['receptionne_par'] ?? '-' }}</p>
        </div>
    </div>

    {{-- ANNULÉ --}}
    @if($livraison['statut'] === 'annule')
        <div class="border border-red-300 bg-red-50 text-red-700 px-4 py-2 rounded mb-6 text-sm">
            <strong>Bon de livraison annulé.</strong>
            @if(!empty($livraison['reason_annulation']))
                &nbsp;Raison : {{ $livraison['reason_annulation'] }}
            @endif
        </div>
    @endif

    {{-- TABLEAU ARTICLES --}}
    <table class="mb-6">
        <thead>
            <tr>
                <th class="text-left">Désignation</th>
                <th class="text-left w-20">Unité</th>
                <th class="text-right w-20">Qté</th>
                <th class="text-right w-28">Prix U.</th>
                <th class="text-right w-28">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($livraison['lignes'] as $i => $ligne)
                <tr>
                    <td>{{ $ligne['designation'] ?? '-' }}</td>
                    <td>{{ $ligne['unite_mesure'] ?? '-' }}</td>
                    <td class="text-right">{{ $ligne['quantite_livree'] }}</td>
                    <td class="text-right">{{ $ligne['prix_unitaire'] }} DH</td>
                    <td class="text-right font-medium">{{ $ligne['total_ttc'] }} DH</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-gray-500 py-4">Aucun article livré</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-gray-50 font-semibold">
                <td colspan="4" class="text-right">Total HT</td>
                <td class="text-right">{{ number_format($livraison['totaux']['ht'], 2, ',', ' ') }} DH</td>
            </tr>
            <tr class="bg-gray-50 font-semibold">
                <td colspan="4" class="text-right">Total TVA</td>
                <td class="text-right">{{ number_format($livraison['totaux']['tva'], 2, ',', ' ') }} DH</td>
            </tr>
            <tr class="bg-gray-100 font-bold">
                <td colspan="4" class="text-right">Total TTC</td>
                <td class="text-right">{{ number_format($livraison['totaux']['ttc'], 2, ',', ' ') }} DH</td>
            </tr>
        </tfoot>
    </table>

    {{-- SIGNATURES --}}
    {{-- <div class="flex justify-between mt-12">
        <div class="w-1/2 text-center">
            <p class="text-sm font-medium">Livré par</p>
            <div class="border-t border-gray-400 mt-10 pt-2 text-sm">
                {{ $livraison['livreur'] ?? '........................' }}
            </div>
        </div>

        <div class="w-1/2 text-center">
            <p class="text-sm font-medium">Réceptionné par</p>
            <div class="border-t border-gray-400 mt-10 pt-2 text-sm">
                {{ $livraison['receptionne_par'] ?? '........................' }}
            </div>
        </div>
    </div> --}}

    {{-- FOOTER --}}

</body>
</html>
