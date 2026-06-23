<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Les fournisseurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="m-0 p-0 text-black text-sm leading-snug">
    <div>
        <div class="mb-2 text-center text-[12pt] font-bold uppercase underline">
            Liste des fournisseurs
        </div>

        @isset($generatedAt)
            <div class="mb-2 text-right text-[7pt]">
                Genere le {{ $generatedAt->format('d/m/Y H:i') }}
            </div>
        @endisset

        <table class="w-full border border-black border-collapse text-[8pt]">
            <thead class="bg-gray-200 font-bold">
                <tr>
                    <th class="border border-black p-1">Nom</th>
                    <th class="border border-black p-1">Raison sociale</th>
                    <th class="border border-black p-1">Contact</th>
                    <th class="border border-black p-1">Telephone</th>
                    <th class="border border-black p-1">Email</th>
                    <th class="border border-black p-1">Ville</th>
                    <th class="border border-black p-1">ICE</th>
                    <th class="border border-black p-1">Statut</th>
                    <th class="border border-black p-1">Marches</th>
                </tr>
            </thead>

            <tbody>
                @foreach($fournisseurs as $fournisseur)
                    <tr>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->nom }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->raison_sociale ?? '-----' }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->contact ?? '-----' }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->telephone ?? '-----' }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->email ?? '-----' }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->ville ?? '-----' }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->ice ?? '-----' }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->est_actif ? 'Actif' : 'Inactif' }}</td>
                        <td class="border border-black p-1 text-center">{{ $fournisseur->marches_count ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
