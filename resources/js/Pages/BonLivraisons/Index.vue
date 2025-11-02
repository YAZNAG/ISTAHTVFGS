<template>
    <AuthenticatedLayout>

        <Head title="Gestion des Bons de Livraisons" />

        <div class="space-y-6">
            <!-- En-tête avec statistiques -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Bons de Livraison</h1>
                        <p class="text-blue-100 text-lg opacity-90">Gestion complète des livraisons</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <ModalLink
                            :href="route('bon-livraisons.create')"
                            class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl">
                            <PlusIcon class="h-5 w-5" />
                            Nouvelle Livraison
                        </ModalLink>
                        <!-- <button
                            @click="exportData"
                            class="bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-400 flex items-center justify-center gap-3 transition-all duration-200 font-semibold border border-blue-400"
                        >
                            <DocumentArrowDownIcon class="h-5 w-5" />
                            Exporter
                        </button> -->
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                            <select v-model="filters.statut"
                                class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                <option value="">Tous les statuts</option>
                                <option value="valide">✅ Validé</option>
                                <option value="brouillon">📝 Brouillon</option>
                                <option value="annule">❌ Annulé</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                            <div class="relative">
                                <input v-model="filters.search" type="text" placeholder="N° Livraison ..."
                                    class="w-full border border-gray-300 rounded-xl p-3 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                <MagnifyingGlassIcon class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-6">
                        <div class="text-sm text-gray-500">
                            {{ bonLivraisons?.total || 0 }} réception(s) trouvée(s)
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

            <!-- Commandes en attente -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <ClockIcon class="h-6 w-6 text-blue-600" />
                                Commandes en Attente de Livraison
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">Commandes validées en attente de réception complète
                            </p>
                        </div>
                        <div class="text-sm text-blue-600 bg-white px-3 py-1 rounded-full border">
                            {{ pendingLivraisions?.length || 0 }} commande(s)
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    N° Commande
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Fournisseur
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Date
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Articles
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Quantité
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Reçue
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Reste
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Progression
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- <tr v-for="commande in pendingLivraisions" :key="commande.id" class="hover:bg-blue-50/30 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ commande.reference }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ formatDate(commande.date_mise_ligne) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center border-2 border-white shadow-sm">
                                                <BuildingStorefrontIcon class="h-5 w-5 text-blue-600" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ commande.fournisseur?.nom_affichage || commande.fournisseur?.raison_sociale || 'Non spécifié' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ commande.fournisseur?.contact || '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(commande.date_mise_ligne) }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <CubeIcon class="h-4 w-4 mr-1" />
                                        {{ commande.articles?.length || 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">
                                    {{ commande.quantite_totale_commandee || 0 }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-green-600 font-semibold">
                                    {{ commande.quantite_totale_recue || 0 }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                        {{ commande.reste_a_recevoir || 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div 
                                                class="h-2.5 rounded-full transition-all duration-300" 
                                                :class="getProgressBarClass(commande.pourcentage_recu)"
                                                :style="{ width: Math.min(commande.pourcentage_recu || 0, 100) + '%' }"
                                            ></div>
                                        </div>
                                        <span class="ml-2 text-xs font-medium text-gray-700">
                                            {{ Math.min(commande.pourcentage_recu || 0, 100) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="openCreateForm(commande.id)"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 font-medium text-sm"
                                    >
                                        <PlusIcon class="h-4 w-4 mr-1" />
                                        Créer réception
                                    </button>
                                </td>
                            </tr> -->
                            <Dump :data="pendingLivraisions" />
                        </tbody>
                    </table>
                </div>
                <div v-if="!pendingLivraisions || pendingLivraisions.length === 0" class="text-center py-16">
                    <DocumentCheckIcon class="mx-auto h-16 w-16 text-green-500 mb-4" />
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Toutes les commandes sont livrées</h3>
                    <p class="text-gray-500">Aucune commande en attente de livraison.</p>
                </div>
            </div>

            <!-- Liste des bons de réception -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <QueueListIcon class="h-6 w-6 text-blue-600" />
                            Historique des Réceptions
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
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    N° Réception
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Commande
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Fournisseur
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Date
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Type
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Documents
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Statut
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- <tr 
                                v-for="reception in bonLivraisons?.data || []" 
                                :key="reception.id" 
                                class="hover:bg-blue-50/30 transition-all duration-200 group"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div :class="getStatusIconBg(reception.statut)" class="p-2 rounded-lg">
                                            <DocumentTextIcon class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">
                                                {{ reception.numero_affichage || reception.numero }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Par {{ reception.created_by?.name || 'Système' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="reception.bon_commande" class="text-blue-600 font-medium">
                                        {{ reception.bon_commande.reference }}
                                    </div>
                                    <div v-else class="text-gray-400 text-sm">
                                        Sans commande
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center border-2 border-white shadow-sm">
                                                <BuildingStorefrontIcon class="h-5 w-5 text-blue-600" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ reception.fournisseur?.nom_affichage || reception.fournisseur?.raison_sociale || 'Non spécifié' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ reception.fournisseur?.ville || '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">
                                        {{ formatDate(reception.date_reception) }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span :class="getTypeBadgeClass(reception.type_livraison)" 
                                          class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold transition-all duration-200">
                                        <component :is="getTypeIcon(reception.type_livraison)" class="h-4 w-4 mr-1.5" />
                                        {{ getTypeLabel(reception.type_livraison) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button 
                                            v-if="reception.fichier_bonlivraison" 
                                            @click="downloadBonLivraison(reception)"
                                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Télécharger BL"
                                        >
                                            <DocumentTextIcon class="h-5 w-5" />
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Bon de Livraison
                                            </div>
                                        </button>
                                        <button v-else class="p-2 text-gray-300" title="Aucun BL">
                                            <DocumentTextIcon class="h-5 w-5" />
                                        </button>
                                        
                                        <button 
                                            v-if="reception.fichier_facture" 
                                            @click="downloadFacture(reception)"
                                            class="p-2 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Télécharger Facture"
                                        >
                                            <DocumentChartBarIcon class="h-5 w-5" />
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Facture
                                            </div>
                                        </button>
                                        <button v-else class="p-2 text-gray-300" title="Aucune facture">
                                            <DocumentChartBarIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span :class="getStatutBadgeClass(reception.statut)" 
                                          class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold transition-all duration-200">
                                        <component :is="getStatutIcon(reception.statut)" class="h-4 w-4 mr-1.5" />
                                        {{ getStatutLabel(reception.statut) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex justify-center items-center gap-1">
                                        <Link
                                            :href="route('bon-receptions.show-details', reception.id)"
                                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Voir détails complets"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Voir détails
                                            </div>
                                        </Link>

                                        <button
                                            @click="downloadPdf(reception)"
                                            class="p-2 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Télécharger PDF"
                                        >
                                            <DocumentArrowDownIcon class="h-5 w-5" />
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Télécharger PDF
                                            </div>
                                        </button>

                                        <Link
                                            v-if="reception.statut === 'brouillon'"
                                            :href="route('bon-receptions.edit', reception.id)"
                                            class="p-2 text-orange-600 hover:text-orange-800 hover:bg-orange-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Modifier"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Modifier
                                            </div>
                                        </Link>
                                    </div>
                                </td>
                            </tr> -->

                            <Dump :data="bonLivraisons" />
                        </tbody>
                    </table>
                </div>

                <!-- État vide -->
                <div v-if="!bonLivraisons?.data || bonLivraisons.data.length === 0" class="text-center py-16">
                    <div class="text-gray-400">
                        <TruckIcon class="mx-auto h-24 w-24 text-gray-300 mb-4" />
                        <h3 class="text-xl font-medium text-gray-900 mb-2">Aucun bon de réception</h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-6">
                            {{ filters.search || filters.type_livraison || filters.statut ||
                                filters.responsable_reception_id ?
                                'Aucun résultat ne correspond à vos critères de recherche.' :
                            'Commencez par créer votre première réception pour gérer vos livraisons.' }}
                        </p>
                        <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors">
                            <PlusIcon class="h-5 w-5" />
                            Créer une réception
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="bonLivraisons?.links && bonLivraisons.links.length > 3"
                    class="bg-white px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-700">
                            Affichage de {{ bonLivraisons.from }} à {{ bonLivraisons.to }} sur {{ bonLivraisons.total }}
                            réceptions
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

// /////////////////////////////////////////
// Dans votre composant Vue.js - CORRECTIONS
const showCreateModal = ref(false);
const isSubmitting = ref(false);
const formErrors = ref([]);
const selectedCommande = ref(null);

const form = ref({
    bon_commande_id: '',
    date_reception: new Date().toISOString().split('T')[0],
    responsable_reception_id: '',
    lignes_reception: [],
    notes: '',
    fichier_bonlivraison: null,
    fichier_facture: null
});

// Ouvrir le modal avec une commande spécifique
const openCreateForm = (commandeId) => {
    form.value.bon_commande_id = commandeId;
    showCreateModal.value = true;
    onCommandeSelected(); // Charger automatiquement les détails
};

// Quand une commande est sélectionnée - CORRIGÉ
const onCommandeSelected = async () => {
    if (!form.value.bon_commande_id) {
        selectedCommande.value = null;
        form.value.lignes_reception = [];
        return;
    }

    try {
        // Afficher un indicateur de chargement
        formErrors.value = ['Chargement des détails de la commande...'];

        const response = await axios.get(`/api/commandes/${form.value.bon_commande_id}/details`);

        if (response.data.success) {
            selectedCommande.value = response.data.commande;

            // Initialiser les lignes de réception avec 0 par défaut
            form.value.lignes_reception = selectedCommande.value.articles.map(article => ({
                article_id: article.article_id,
                quantite_receptionnee: 0
            }));

            formErrors.value = [];
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        console.error('Erreur chargement commande:', error);
        selectedCommande.value = null;
        form.value.lignes_reception = [];
        formErrors.value = ['Erreur lors du chargement de la commande: ' + (error.response?.data?.message || error.message)];
    }
};

// Validation de la quantité - CORRIGÉ
// Dans la section methods du composant Vue
const validateQuantite = (article, index) => {
    const quantite = parseFloat(form.value.lignes_reception[index].quantite_receptionnee) || 0;

    if (quantite < 0) {
        form.value.lignes_reception[index].quantite_receptionnee = 0;
        return;
    }

    // CORRECTION : Conversion explicite en nombre
    const resteARecevoir = parseFloat(article.reste_a_recevoir) || 0;

    if (quantite > resteARecevoir) {
        form.value.lignes_reception[index].quantite_receptionnee = resteARecevoir;
    }

    // Forcer la mise à jour pour le computed isReceptionComplete
    form.value.lignes_reception = [...form.value.lignes_reception];
};

// Soumission du formulaire - CORRIGÉ
const submitReceptionForm = async () => {
    if (!isFormValid.value) {
        formErrors.value = ['Veuillez corriger les erreurs dans le formulaire'];
        return;
    }

    // Validation finale de la facture
    if (form.value.fichier_facture && !isReceptionComplete.value) {
        formErrors.value = ['La facture ne peut être ajoutée que pour une réception complète'];
        return;
    }

    isSubmitting.value = true;
    formErrors.value = [];

    try {
        const formData = new FormData();

        // Ajouter les champs simples
        formData.append('bon_commande_id', form.value.bon_commande_id);
        formData.append('date_reception', form.value.date_reception);
        formData.append('responsable_reception_id', form.value.responsable_reception_id);
        formData.append('notes', form.value.notes || '');

        // Ajouter les lignes de réception (seulement celles avec quantité > 0)
        let ligneIndex = 0;
        form.value.lignes_reception.forEach((ligne) => {
            const quantite = parseFloat(ligne.quantite_receptionnee) || 0;
            if (quantite > 0) {
                formData.append(`lignes_reception[${ligneIndex}][article_id]`, ligne.article_id);
                formData.append(`lignes_reception[${ligneIndex}][quantite_receptionnee]`, quantite);
                ligneIndex++;
            }
        });

        // Vérifier qu'il y a au moins une ligne avec quantité > 0
        if (ligneIndex === 0) {
            throw new Error('Au moins une quantité doit être supérieure à 0');
        }

        // Ajouter les fichiers
        if (form.value.fichier_bonlivraison) {
            formData.append('fichier_bonlivraison', form.value.fichier_bonlivraison);
        }
        if (form.value.fichier_facture && isReceptionComplete.value) {
            formData.append('fichier_facture', form.value.fichier_facture);
        }

        // Utiliser Inertia pour la soumission
        await router.post(route('bon-receptions.store'), formData, {
            forceFormData: true,
            onSuccess: () => {
                closeCreateModal();
                showNotification('Bon de réception créé avec succès', 'success');
            },
            onError: (errors) => {
                formErrors.value = Object.values(errors).flat();
            }
        });

    } catch (error) {
        console.error('Erreur création bon réception:', error);
        formErrors.value = ['Une erreur est survenue lors de la création: ' + error.message];
    } finally {
        isSubmitting.value = false;
    }
};

// Recharger les données
const reloadData = async () => {
    await router.reload({ only: ['bonLivraisons', 'pendingLivraisions', 'stats'] });
};

// Afficher une notification
const showNotification = (message, type = 'success') => {
    // Implémentez votre système de notifications
    console.log(`${type}: ${message}`);
};

// ////////////////////////////////////////////////////////////////////////////////////////
// Dans votre composant Vue.js


// Computed pour vérifier si la réception est complète
const isReceptionComplete = computed(() => {
    if (!selectedCommande.value) return false;

    return calculateNewProgression() === 100;

    // Vérifier si tous les articles sont complètement reçus
    return selectedCommande.value.articles.every(article => {
        const ligneReception = form.value.lignes_reception.find(
            l => l.article_id === article.article_id
        );
        const quantiteRecue = parseFloat(ligneReception?.quantite_receptionnee) || 0;

        // La réception est complète si la quantité reçue + déjà reçue = quantité commandée
        return (quantiteRecue + (article.quantite_deja_recue || 0)) >= article.quantite_commandee;
    });
});

// Vérifier si une commande a des articles à recevoir
const hasArticlesToReceive = (commande) => {
    return commande.articles.some(article => article.reste_a_recevoir > 0);
};

// Ouvrir le modal
const openCreateModal = () => {
    showCreateModal.value = true;
    resetForm();
};

// Fermer le modal
const closeCreateModal = () => {
    showCreateModal.value = false;
    resetForm();
};

// Réinitialiser le formulaire
const resetForm = () => {
    form.value = {
        bon_commande_id: '',
        date_reception: new Date().toISOString().split('T')[0],
        responsable_reception_id: '',
        lignes_reception: [],
        notes: '',
        fichier_bonlivraison: null,
        fichier_facture: null
    };
    selectedCommande.value = null;
    formErrors.value = [];
};


// Vérifier s'il y a une erreur de réception
const hasReceptionError = (articleId) => {
    const article = selectedCommande.value.articles.find(a => a.article_id === articleId);
    const ligne = form.value.lignes_reception.find(l => l.article_id === articleId);

    if (!article || !ligne) return false;

    const quantite = parseFloat(ligne.quantite_receptionnee) || 0;
    return quantite > article.reste_a_recevoir;
};

// Obtenir la quantité reçue pour un article
const getQuantiteRecue = (articleId) => {
    const ligne = form.value.lignes_reception.find(l => l.article_id === articleId);
    return parseFloat(ligne?.quantite_receptionnee) || 0;
};

// Gestion des fichiers
const onFileSelected = (field, event) => {
    const file = event.target.files[0];
    if (file) {
        // Vérification spécifique pour la facture
        if (field === 'fichier_facture' && !isReceptionComplete.value) {
            formErrors.value = ['La facture ne peut être ajoutée que pour une réception complète'];
            event.target.value = ''; // Réinitialiser l'input file
            return;
        }

        form.value[field] = file;
        formErrors.value = formErrors.value.filter(error =>
            !error.includes('facture')
        );
    }
};

// Calculs et helpers
const getArticlesWithRemainingQuantity = () => {
    if (!selectedCommande.value) return 0;
    return selectedCommande.value.articles.filter(article =>
        article.reste_a_recevoir > 0
    ).length;
};

const getTotalQuantiteRecue = () => {
    return form.value.lignes_reception.reduce((total, ligne) => {
        return total + (parseFloat(ligne.quantite_receptionnee) || 0);
    }, 0);
};

const getReceptionType = () => {
    if (!selectedCommande.value) return 'Aucune réception';

    if (isReceptionComplete.value) {
        return 'Réception complète';
    }

    const totalRecu = getTotalQuantiteRecue();
    if (totalRecu > 0) {
        return 'Réception partielle';
    }

    return 'Aucune réception';
};

const getReceptionTypeClass = () => {
    const type = getReceptionType();
    switch (type) {
        case 'Réception complète': return 'text-green-600';
        case 'Réception partielle': return 'text-orange-600';
        default: return 'text-gray-600';
    }
};

const calculateNewProgression = () => {
    if (!selectedCommande.value) return 0;

    const quantiteTotaleRecueAvant = selectedCommande.value.quantite_totale_recue;
    const quantiteRecueCetteReception = getTotalQuantiteRecue();
    const quantiteTotaleCommandee = selectedCommande.value.quantite_totale_commandee;

    if (quantiteTotaleCommandee === 0) return 0;

    const nouvelleProgression = ((quantiteTotaleRecueAvant + quantiteRecueCetteReception) / quantiteTotaleCommandee) * 100;
    return Math.min(100, Math.round(nouvelleProgression * 100) / 100);
};

// Validation du formulaire
const isFormValid = computed(() => {
    // Validation de base
    if (!form.value.bon_commande_id || !form.value.date_reception || !form.value.responsable_reception_id) {
        return false;
    }

    // Au moins une quantité > 0
    if (!form.value.lignes_reception.some(ligne => parseFloat(ligne.quantite_receptionnee) > 0)) {
        return false;
    }

    // Vérifier que les quantités ne dépassent pas le reste à recevoir
    for (const ligne of form.value.lignes_reception) {
        const article = selectedCommande.value?.articles.find(a => a.article_id === ligne.article_id);
        if (article && parseFloat(ligne.quantite_receptionnee) > article.reste_a_recevoir) {
            return false;
        }
    }

    // Vérification spécifique pour la facture
    if (form.value.fichier_facture && !isReceptionComplete.value) {
        return false;
    }

    return true;
});


// Helper pour formater les prix
const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'MAD'
    }).format(price);
};

// Helper pour formater les dates
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR');
};

// Props
const props = defineProps({
    bonLivraisons: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    pendingLivraisions: {
        type: Array,
        default: () => ({ data: [], links: [] })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({})
    },
});


// Filtres
const filters = ref({
    type_livraison: props.filters?.type_livraison || '',
    statut: props.filters?.statut || '',
    responsable_reception_id: props.filters?.responsable_reception_id || '',
    search: props.filters?.search || '',
});

// Formulaire
const receptionForm = useForm({
    bon_commande_id: '',
    date_reception: new Date().toISOString().split('T')[0],
    responsable_reception_id: '',
    fichier_bonlivraison: null,
    fichier_facture: null,
    lignes_reception: [],
    notes: '',
});


const getTypeBadgeClass = (type) => {
    const classes = {
        'complet': 'bg-green-100 text-green-800 border border-green-200',
        'partiel': 'bg-orange-100 text-orange-800 border border-orange-200'
    };
    return classes[type] || 'bg-gray-100 text-gray-800 border border-gray-200';
};

const getTypeLabel = (type) => {
    const labels = {
        'complet': 'Complet',
        'partiel': 'Partiel'
    };
    return labels[type] || type;
};

const getTypeIcon = (type) => {
    const icons = {
        'complet': CheckBadgeIcon,
        'partiel': ClipboardDocumentListIcon
    };
    return icons[type] || ClipboardDocumentListIcon;
};

const getStatutBadgeClass = (statut) => {
    const classes = {
        'valide': 'bg-green-100 text-green-800 border border-green-200',
        'brouillon': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        'annule': 'bg-red-100 text-red-800 border border-red-200'
    };
    return classes[statut] || 'bg-gray-100 text-gray-800 border border-gray-200';
};

const getStatutLabel = (statut) => {
    const labels = {
        'valide': 'Validé',
        'brouillon': 'Brouillon',
        'annule': 'Annulé'
    };
    return labels[statut] || statut;
};

const getStatutIcon = (statut) => {
    const icons = {
        'valide': CheckBadgeIcon,
        'brouillon': ClockIcon,
        'annule': XMarkIcon
    };
    return icons[statut] || ClockIcon;
};

const getStatusIconBg = (statut) => {
    const classes = {
        'valide': 'bg-green-500',
        'brouillon': 'bg-yellow-500',
        'annule': 'bg-red-500'
    };
    return classes[statut] || 'bg-gray-500';
};

const getProgressBarClass = (percentage) => {
    if (percentage >= 100) return 'bg-green-500';
    if (percentage >= 75) return 'bg-blue-500';
    if (percentage >= 50) return 'bg-yellow-500';
    if (percentage >= 25) return 'bg-orange-500';
    return 'bg-red-500';
};

// Méthodes d'action
const applyFilters = () => {
    router.get(route('bon-receptions.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.value = {
        type_livraison: '',
        statut: '',
        responsable_reception_id: '',
        search: '',
    };
};

const exportData = () => {
    console.log('Export des données');
};

const downloadPdf = (reception) => {
    window.open(route('bon-receptions.download-pdf', reception.id), '_blank');
};

const downloadBonLivraison = (reception) => {
    window.open(route('bon-receptions.download-bon-livraison', reception.id), '_blank');
};

const downloadFacture = (reception) => {
    window.open(route('bon-receptions.download-facture', reception.id), '_blank');
};

// Watch pour les filtres
watch(filters, (value) => {
    // Debounce pour éviter trop de requêtes
    clearTimeout(window.filterTimeout);
    window.filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}, { deep: true });




</script>

<style scoped>
.hover-lift:hover {
    transform: translateY(-2px);
}

.progress-bar {
    transition: width 0.3s ease-in-out;
}
</style>