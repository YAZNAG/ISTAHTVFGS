@extends('pdf.layout')

@section('title', 'Liste des articles')

@section('summary')
    <strong>{{ count($articles) }}</strong> article(s) exporté(s)
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:10%">Référence</th>
            <th style="width:25%">Désignation</th>
            <th style="width:14%">Catégorie</th>
            <th style="width:8%">Unité</th>
            <th style="width:10%;text-align:right">Stock actuel</th>
            <th style="width:10%;text-align:right">Seuil min.</th>
            <th style="width:10%;text-align:right">Seuil max.</th>
            <th style="width:13%">Statut</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($articles as $article)
            <tr>
                <td><strong>{{ $article->reference }}</strong></td>
                <td>{{ $article->designation }}</td>
                <td>{{ $article->categorie?->nom ?? '—' }}</td>
                <td>{{ $article->unite_mesure }}</td>
                <td style="text-align:right">{{ number_format((float)($article->quantite_stock ?? 0), 2, ',', ' ') }}</td>
                <td style="text-align:right">{{ number_format((float)($article->seuil_minimal ?? 0), 2, ',', ' ') }}</td>
                <td style="text-align:right">{{ number_format((float)($article->seuil_maximal ?? 0), 2, ',', ' ') }}</td>
                <td>
                    @php
                        $stock = (float)($article->quantite_stock ?? 0);
                        $seuilMin = (float)($article->seuil_minimal ?? 0);
                        if (!$article->est_actif) {
                            $badgeClass = 'badge-inactive'; $badgeLabel = 'Inactif';
                        } elseif ($stock <= 0) {
                            $badgeClass = 'badge-rupture'; $badgeLabel = 'Rupture';
                        } elseif ($seuilMin > 0 && $stock <= $seuilMin * 0.8) {
                            $badgeClass = 'badge-faible'; $badgeLabel = 'Stock faible';
                        } else {
                            $badgeClass = 'badge-normal'; $badgeLabel = 'Normal';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center;color:#94a3b8;padding:20px">
                    Aucun article à exporter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
