@extends('pdf.layout')

@section('title', 'Liste des catégories')

@section('summary')
    <strong>{{ count($categories) }}</strong> catégorie(s) exportée(s)
@endsection

@section('content')
<table>
    <thead>
        <tr>
            <th style="width:12%">Code</th>
            <th style="width:35%">Nom</th>
            <th style="width:13%">Statut</th>
            <th style="width:18%">Nombre d'articles</th>
            <th style="width:22%">Date de création</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($categories as $categorie)
            <tr>
                <td><strong>{{ $categorie->code }}</strong></td>
                <td>{{ $categorie->nom }}</td>
                <td>
                    <span class="badge {{ $categorie->est_actif ? 'badge-active' : 'badge-inactive' }}">
                        {{ $categorie->est_actif ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td style="text-align:center">{{ (int) $categorie->articles_count }}</td>
                <td>{{ optional($categorie->created_at)->format('d/m/Y') ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px">
                    Aucune catégorie à exporter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
