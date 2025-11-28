<template>
    <AuthenticatedLayout>
        <Head :title="`Fournisseur ${fournisseur.raison_sociale || fournisseur.nom}`" />

        <div class="space-y-6 py-8 px-4">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        {{ fournisseur.raison_sociale || fournisseur.nom }}
                    </h1>
                    <p class="text-gray-600">Détails du fournisseur</p>
                </div>

                <div class="flex space-x-3">
                    <Link
                        :href="route('fournisseurs.index')"
                        class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2 transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                        Retour
                    </Link>

                    <button
                        v-if="fournisseur.email"
                        @click="mailtoFournisseur"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors"
                    >
                        <EnvelopeIcon class="h-5 w-5" />
                        Contacter
                    </button>
                </div>
            </div>

            <!-- Flash Messages -->

            <!-- Main info cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Fournisseur Infos -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Générales</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nom / Raison sociale:</span>
                            <span class="font-medium text-right">
                                {{ fournisseur.raison_sociale || fournisseur.nom || '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Contact:</span>
                            <span class="font-medium text-right">
                                {{ fournisseur.contact || '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Téléphone:</span>
                            <span class="font-medium text-right">
                                {{ fournisseur.telephone || '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium text-blue-600 text-right truncate max-w-[180px]">
                                {{ fournisseur.email || '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Ville:</span>
                            <span class="font-medium text-right">
                                {{ fournisseur.ville || '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Adresse & Statut -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Adresse & Statut</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-gray-600 block mb-1">Adresse:</span>
                            <p class="text-gray-800 font-medium whitespace-pre-line">
                                {{ fournisseur.adresse || 'Non spécifiée' }}
                            </p>
                        </div>

                        <InfoLine label="ICE" :value="fournisseur.ice" />

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Statut:</span>
                            <span
                                :class="fournisseur.est_actif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                class="px-2 py-1 rounded-full text-xs font-semibold"
                            >
                                {{ fournisseur.est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Logo & Dates -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex flex-col items-center justify-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Logo & Dates</h3>

                    <div v-if="fournisseur.logo" class="w-28 h-28 mb-4">
                        <img
                            :src="`/storage/${fournisseur.logo}`"
                            alt="Logo"
                            class="w-full h-full object-contain rounded-md border border-gray-200"
                        />
                    </div>
                    <div v-else class="w-28 h-28 mb-4 bg-gray-100 border border-gray-200 rounded-md flex items-center justify-center text-gray-400">
                        <BuildingStorefrontIcon class="w-10 h-10" />
                    </div>

                    <div class="text-sm text-gray-600 space-y-1">
                        <p>Créé le <span class="font-medium">{{ fournisseur.created_at }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="fournisseur.notes" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Notes</h3>
                <p class="text-gray-700 whitespace-pre-wrap">{{ fournisseur.notes }}</p>
            </div>

            <!-- Bon de commande Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex gap-2 items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <ClipboardDocumentListIcon class="w-5 h-5 text-blue-600" />
                        Les Marchés
                    </h3>
                    <span
                        class="bg-blue-100 text-blue-800 px-2 py-0.5 text-xs font-semibold rounded-full"
                    >
                        {{ fournisseur.bon_commandes_count || 0 }}
                    </span>
                </div>

                <div v-if="fournisseur.bon_commandes?.length" class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3 font-medium">N° Marché</th>
                                <th class="px-6 py-3 font-medium">Date mise ligne</th>
                                <th class="px-6 py-3 font-medium">Date limite</th>
                                <th class="px-6 py-3 font-medium">Articles</th>
                                <th class="px-6 py-3 font-medium">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(bon, index) in fournisseur.bon_commandes"
                                :key="bon.id"
                                class="border-b border-gray-100 hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-3 font-medium text-gray-800">#{{ bon.reference }}</td>
                                <td class="px-6 py-3">{{ bon.date_mise_ligne }}</td>
                                <td class="px-6 py-3">{{ bon.date_limite_reception }}</td>
                                <td class="px-6 py-3 font-semibold">{{ bon.articles_count }}</td>
                                <td class="px-6 py-3">
                                    <span
                                        :class="[getBonCommandeStatutInfo(bon.statut).color, 'px-2 py-1 rounded-full text-xs font-medium']"
                                    >
                                        {{ getBonCommandeStatutInfo(bon.statut).label }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-12 text-gray-500">
                    <ClipboardDocumentListIcon class="w-10 h-10 mb-2 text-gray-300" />
                    <p>Aucun marché trouvé pour ce fournisseur.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    EnvelopeIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    BuildingStorefrontIcon,
    ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';
import { getBonCommandeStatutInfo } from '@/Utils/labels';

const props = defineProps({
    fournisseur: Object,
});

const mailtoFournisseur = () => {
    if (props.fournisseur.email) window.location.href = `mailto:${props.fournisseur.email}`;
};

</script>
