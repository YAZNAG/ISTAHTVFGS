<template>
    <AuthenticatedLayout>

        <Head title="Gestion des Bons de Livraison" />

        <div class="space-y-6">
            <!-- En-tête avec statistiques -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Bons de Livraison</h1>
                        <p class="text-blue-100 text-lg opacity-90">Gestion complète des livraisons</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <!-- Action buttons (ex: créer nouveau BL) can be added here -->
                    </div>
                </div>
            </div>

            <!-- Filtres et Recherche -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <FunnelIcon class="h-5 w-5 text-blue-600" />
                        Filtres et Recherche
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                            <div class="relative">
                                <input v-model="filters.search" type="text" placeholder="N° Bon de Livraison ..."
                                    class="w-full border border-gray-300 rounded-xl p-3 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                <MagnifyingGlassIcon class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Magasiniers</label>
                            <div class="relative">
                                <select v-model="filters.responsable_id" 
                                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">Tous les magasiniers</option>
                                    <option v-for="magasinier in magasiniers" :key="magasinier.id" :value="magasinier.id">
                                        {{ magasinier.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-6">
                        <div class="text-sm text-gray-500">
                            {{ bonLivraisons?.total || 0 }} Bon(s) trouvé(s)
                        </div>
                        <div class="flex gap-3">
                            <button @click="resetFilters"
                                class="px-6 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition-all duration-200">
                                <ArrowPathIcon class="h-4 w-4" />
                                Réinitialiser
                            </button>
                            <button @click="applyFilters"
                                class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2 transition-all duration-200">
                                <FunnelIcon class="h-4 w-4" />
                                Appliquer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bons de livraison en attente -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <ClockIcon class="h-6 w-6 text-blue-600" />
                                Bons de Livraison en Attente
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Bons de livraison validés en attente de finalisation
                            </p>
                        </div>
                        <div class="text-sm text-blue-600 bg-white px-3 py-1 rounded-full border">
                            {{ pendingLivraisons?.length || 0 }} bon(s)
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    N°
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Fournisseur
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Date
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Articles
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Statut
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Total HT
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Total TTC
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="bon in pendingLivraisons" :key="bon.id" class="hover:bg-blue-50/30 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ bon.numero }}</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    {{ bon.fournisseur }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    N/A
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <CubeIcon class="h-4 w-4 mr-1" />
                                        {{ bon.items_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    <span
                                        :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        getStatutColor(bon.statut)
                                        ]"
                                    >
                                        {{ getStatutLabel(bon.statut) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    {{ bon.total_ht || 0 }} DH
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 font-semibold">
                                    {{ bon.total_ttc || 0 }} DH
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <ModalLink
                                        v-if="can('validate_bonLivraisons')"
                                        :href="route('bon-livraisons.edit', bon.id)"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 font-medium text-sm"
                                    >
                                        <PlusIcon class="h-4 w-4 mr-1" />
                                        Livrer
                                    </ModalLink>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!pendingLivraisons || pendingLivraisons.length === 0" class="text-center py-16">
                    <DocumentCheckIcon class="mx-auto h-16 w-16 text-green-500 mb-4" />
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Toutes les livraisons sont effectuées</h3>
                    <p class="text-gray-500">Aucun bon en attente de livraison.</p>
                </div>
            </div>

            <!-- Historique des Bons de Livraison -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <QueueListIcon class="h-6 w-6 text-blue-600" />
                            Historique des Bons de Livraison
                        </h3>
                        <div class="flex items-center gap-4">
                            <div class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border">
                                {{ bonLivraisons?.total || 0 }} résultat(s)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    N°
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Fournisseur
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Date
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Articles
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Statut
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Total HT
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Total TTC
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="bon in bonLivraisons.data" :key="bon.id" class="hover:bg-blue-50/30 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ bon.numero }}</div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    {{ bon.fournisseur }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    {{ bon.date_livraison }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <CubeIcon class="h-4 w-4 mr-1" />
                                        {{ bon.items_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    <span
                                        :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        getStatutColor(bon.statut)
                                        ]"
                                    >
                                        {{ getStatutLabel(bon.statut) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    {{ bon.total_ht || 0 }} DH
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 font-semibold">
                                    {{ bon.total_ttc || 0 }} DH
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex justify-center items-center gap-1">
                                        <Link
                                            v-if="can('show_bonLivraisons')"
                                            :href="route('bon-livraisons.show', bon.id)"
                                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Voir détails du bon"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Voir détails
                                            </div>
                                        </Link>

                                        <a
                                            v-if="can('pdf_bonLivraisons')"
                                            :href="route('bon-livraisons.pdf', bon.id)"
                                            class="p-2 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Télécharger PDF"
                                        >
                                            <DocumentArrowDownIcon class="h-5 w-5" />
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Télécharger PDF
                                            </div>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- État vide -->
                <div v-if="!bonLivraisons?.data || bonLivraisons.data.length === 0" class="text-center py-16">
                    <TruckIcon class="mx-auto h-24 w-24 text-gray-300 mb-4" />
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Aucun bon de livraison</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-6">
                        {{ filters.search || filters.statut ?
                        'Aucun résultat ne correspond à vos critères de recherche.' :
                        'Commencez par créer votre premier bon de livraison pour gérer vos livraisons.' }}
                    </p>
                    <button @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors">
                        <PlusIcon class="h-5 w-5" />
                        Créer un bon de livraison
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="bonLivraisons?.links && bonLivraisons.links.length > 3"
                    class="bg-white px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-700">
                            Affichage de {{ bonLivraisons.from }} à {{ bonLivraisons.to }} sur {{ bonLivraisons.total }}
                            bon(s)
                        </div>
                        <div class="flex items-center gap-1">
                            <template v-for="link in bonLivraisons.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border',
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                                ]" v-html="link.label" />
                                <span v-else :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 border border-gray-300 cursor-not-allowed'
                                ]" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
    PlusIcon,
    DocumentTextIcon,
    CheckBadgeIcon,
    ClipboardDocumentListIcon,
    DocumentChartBarIcon,
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    PencilIcon,
    BuildingStorefrontIcon,
    DocumentCheckIcon,
    TruckIcon,
    XMarkIcon,
    FunnelIcon,
    ArrowPathIcon,
    QueueListIcon,
    ClockIcon,
    CubeIcon
} from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';
import { getBonLivraisonInfo } from '@/Utils/labels';
import { usePermission } from '@/Utils/permission';


const { can } = usePermission();

const getStatutColor = (statut) => getBonLivraisonInfo(statut).color;
const getStatutLabel = (statut) => getBonLivraisonInfo(statut).label;


// Props
const props = defineProps({
    bonLivraisons: {
        type: Object,
    },
    pendingLivraisons: {
        type: Array,
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    magasiniers: {
        type: Array,
        default: () => ([])
    }
});


// Filtres
const filters = ref({
    responsable_id: props.filters?.responsable_id || '',
    search: props.filters?.search || '',
});


// Méthodes d'action
const applyFilters = () => {
    router.get(route('bon-livraisons.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.value = {
        responsable_id: '',
        search: '',
    };

    router.get(route('bon-livraisons.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};


</script>

<style scoped>
.hover-lift:hover {
    transform: translateY(-2px);
}

.progress-bar {
    transition: width 0.3s ease-in-out;
}
</style>