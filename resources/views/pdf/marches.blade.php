@extends('pdf.layout')

@section('title', 'Liste des marchés')

@section('summary')
    <strong>{{ count($marches) }}</strong> marché(s) exporté(s) &nbsp;·&nbsp;
    Généré le {{ optional($generatedAt)->format('d/m/Y à H:i') }}
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:11%">Référence</th>
            <th style="width:28%">Objet</th>
            <th style="width:14%">Catégorie</th>
            <th style="width:13%">Fournisseur</th>
            <th style="width:12%">Période</th>
            <th style="width:9%">Statut</th>
            <th style="width:7%;text-align:right">HT (DH)</th>
            <th style="width:7%;text-align:right">TTC (DH)</th>
            <th style="width:6%;text-align:right">Consommé</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($marches as $marche)
            @php
                [$badgeClass, $badgeLabel] = match($marche['statut']) {
                    'cree'               => ['badge-info',    'Créé'],
                    'attente_livraison'  => ['badge-warning', 'En attente'],
                    'livre_partiellement'=> ['badge-warning', 'Partiel'],
                    'livre_completement' => ['badge-active',  'Livré'],
                    'annule'             => ['badge-inactive','Annulé'],
                    default              => ['badge-info',    $marche['statut']],
                };
                $pct = (float) ($marche['consumption_percent'] ?? 0);
                $pctClass = $pct >= 90 ? 'badge-inactive' : ($pct >= 60 ? 'badge-warning' : 'badge-active');
            @endphp
            <tr>
                <td><strong>{{ $marche['reference'] }}</strong></td>
                <td style="font-size:9.5px">{{ $marche['objet'] }}</td>
                <td style="font-size:9px">{{ $marche['categorie']['nom'] ?? '—' }}</td>
                <td style="font-size:9px">{{ $marche['fournisseur']['nom_affichage'] ?? 'Non attribué' }}</td>
                <td style="font-size:9px;text-align:center">
                    {{ $marche['date_debut'] ?? '—' }}<br>
                    <span style="color:#64748b">au {{ $marche['date_fin'] ?? '—' }}</span>
                </td>
                <td style="text-align:center">
                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
                <td style="text-align:right;font-variant-numeric:tabular-nums">
                    {{ number_format((float)($marche['total_ht'] ?? 0), 2, ',', ' ') }}
                </td>
                <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700">
                    {{ number_format((float)($marche['total_ttc'] ?? 0), 2, ',', ' ') }}
                </td>
                <td style="text-align:center">
                    <span class="badge {{ $pctClass }}">{{ number_format($pct, 1, ',', ' ') }}%</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align:center;color:#94a3b8;padding:20px">
                    Aucun marché à exporter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Totaux --}}
@if(count($marches) > 0)
@php
    $totalHT  = collect($marches)->sum('total_ht');
    $totalTTC = collect($marches)->sum('total_ttc');
@endphp
<table style="margin-top:10px;width:340px;margin-left:auto">
    <tr>
        <td style="padding:5px 10px;font-weight:700;font-size:10px;color:#0c3260;border:1px solid #dce4ef;background:#f4f7fb">
            Total HT
        </td>
        <td style="padding:5px 10px;text-align:right;font-variant-numeric:tabular-nums;border:1px solid #dce4ef;background:#f4f7fb">
            {{ number_format($totalHT, 2, ',', ' ') }} DH
        </td>
    </tr>
    <tr>
        <td style="padding:6px 10px;font-weight:800;font-size:11px;color:#fff;background:#0c3260;border:1px solid #071f3e">
            Total TTC
        </td>
        <td style="padding:6px 10px;text-align:right;font-weight:800;font-variant-numeric:tabular-nums;color:#fff;background:#0c3260;border:1px solid #071f3e">
            {{ number_format($totalTTC, 2, ',', ' ') }} DH
        </td>
    </tr>
</table>
@endif
@endsection
