<template>
    <AuthenticatedLayout>
        <Head :title="`Bon Réception ${bonReception.numero_affichage}`" />

        <div class="space-y-6 py-8 px-4">
            <!-- En-tête -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        Bon de Réception {{ bonReception.numero }}
                    </h1>
                    <p class="text-gray-600">Détails complets de la réception</p>
                </div>
                <div class="flex space-x-3">
                    <!-- Bouton retour -->
                    <Link
                        :href="route('bon-receptions.index')"
                        class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2 transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                        Retour
                    </Link>
                    
                    <!-- Bouton PDF -->
                    <button
                        @click="downloadPdf"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2 transition-colors"
                    >
                        <DocumentArrowDownIcon class="h-5 w-5" />
                        PDF
                    </button>
                </div>
            </div>

            <!-- Alertes -->
            <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-5 w-5 text-green-400 mr-2" />
                    <span class="text-green-800">{{ $page.props.flash?.success }}</span>
                </div>
            </div>

            <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-5 w-5 text-red-400 mr-2" />
                    <span class="text-red-800">{{ $page.props.flash?.error }}</span>
                </div>
            </div>

            <!-- Cartes d'informations principales -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Informations de base -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations Générales</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Numéro:</span>
                            <span class="font-medium">{{ bonReception.numero }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date réception:</span>
                            <span class="font-medium">{{ formatDate(bonReception.date_reception) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Type:</span>
                            <span :class="getTypeBadgeClass(bonReception.type_reception)" 
                                  class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ getTypeLabel(bonReception.type_reception) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Statut:</span>
                            <span :class="getStatutBadgeClass(bonReception.statut)" 
                                  class="px-2 py-1 rounded-full text-xs font-medium">
                                {{ getStatutLabel(bonReception.statut) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Créé par:</span>
                            <span class="font-medium">{{ bonReception.created_by?.name || 'Système' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Informations commande -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Commande Associée</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">N° Commande:</span>
                            <span class="font-medium text-blue-600">
                                {{ bonReception.bon_commande?.reference || 'Aucune' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fournisseur:</span>
                            <span class="font-medium text-right">
                                {{ bonReception.fournisseur?.raison_sociale || bonReception.fournisseur?.nom || 'Non spécifié' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Contact:</span>
                            <span class="font-medium">{{ bonReception.fournisseur?.email || bonReception.fournisseur?.telephone || '-' }}</span>
                        </div>
                        <div v-if="bonReception.bon_commande" class="pt-2 border-t">
                            <Link
                                :href="route('bon-commandes.show', bonReception.bon_commande.id)"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1"
                            >
                                Voir la commande
                                <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Bon de livraison:</span>
                            <div>
                                <button
                                    v-if="bonReception.fichier_bonlivraison"
                                    @click="downloadBonLivraison"
                                    class="text-blue-600 hover:text-blue-800 flex items-center gap-1 text-sm font-medium"
                                >
                                    <DocumentArrowDownIcon class="h-4 w-4" />
                                    Télécharger
                                </button>
                                <span v-else class="text-gray-400 text-sm">Non disponible</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Facture:</span>
                            <div>
                                <button
                                    v-if="bonReception.fichier_facture"
                                    @click="downloadFacture"
                                    class="text-green-600 hover:text-green-800 flex items-center gap-1 text-sm font-medium"
                                >
                                    <DocumentArrowDownIcon class="h-4 w-4" />
                                    Télécharger
                                </button>
                                <span v-else class="text-gray-400 text-sm">Non disponible</span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Responsable:</span>
                            <span class="font-medium">{{ bonReception.responsable_reception?.name || 'Non spécifié' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totaux -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="text-blue-600 font-semibold">Quantité Totale</div>
                    <div class="text-2xl font-bold text-blue-700">{{ totaux.quantite_totale }}</div>
                    <div class="text-blue-600 text-sm">Articles reçus</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="text-green-600 font-semibold">Total HT</div>
                    <div class="text-2xl font-bold text-green-700">{{ formatCurrency(totaux.total_ht) }}</div>
                    <div class="text-green-600 text-sm">Hors taxes</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                    <div class="text-orange-600 font-semibold">Total TVA</div>
                    <div class="text-2xl font-bold text-orange-700">{{ formatCurrency(totaux.total_tva) }}</div>
                    <div class="text-orange-600 text-sm">Montant TVA</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <div class="text-purple-600 font-semibold">Total TTC</div>
                    <div class="text-2xl font-bold text-purple-700">{{ formatCurrency(totaux.total_ttc) }}</div>
                    <div class="text-purple-600 text-sm">Toutes taxes</div>
                </div>
            </div>

            <!-- Articles reçus -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Articles Reçus</h3>
                    <p class="text-gray-600 text-sm">Détail des articles réceptionnés</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité Reçue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unité</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix Unitaire HT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TVA %</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant HT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant TVA</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total TTC</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="ligne in bonReception.lignes_reception" :key="ligne.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ ligne.article?.designation }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ligne.article?.reference || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    <span class="font-semibold">{{ ligne.quantite_receptionnee }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ ligne.article?.unite_mesure || 'Pièce' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ formatCurrency(ligne.prix_unitaire) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    {{ ligne.taux_tva }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ formatCurrency(ligne.montant_ht) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    {{ formatCurrency(ligne.montant_tva) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">
                                    {{ formatCurrency(ligne.montant_ttc) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">
                                    Totaux:
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">
                                    {{ formatCurrency(totaux.total_ht) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">
                                    {{ formatCurrency(totaux.total_tva) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">
                                    {{ formatCurrency(totaux.total_ttc) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Comparaison avec la commande -->
            <div v-if="comparaison && comparaison.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Comparaison avec la Commande</h3>
                    <p class="text-gray-600 text-sm">État d'avancement par rapport à la commande initiale</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité Commandée</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité Reçue (Cette réception)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité Totale Reçue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reste à Recevoir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progression</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in comparaison" :key="item.article_id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    {{ item.article_designation }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ item.article_reference }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    {{ item.quantite_commandee }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    <span class="font-semibold text-blue-600">{{ item.quantite_recue_cette_reception }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    {{ item.quantite_totale_recue }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    <span :class="item.reste_a_recevoir > 0 ? 'text-orange-600 font-semibold' : 'text-green-600'">
                                        {{ item.reste_a_recevoir }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" 
                                                 :style="{ width: Math.min(item.pourcentage_recu, 100) + '%' }"></div>
                                        </div>
                                        <span class="ml-2 text-xs font-medium text-gray-700">{{ Math.min(item.pourcentage_recu, 100) }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="item.est_complet ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'" 
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        <CheckBadgeIcon v-if="item.est_complet" class="h-3 w-3 mr-1" />
                                        {{ item.est_complet ? 'Complet' : 'En cours' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="bonReception.notes" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Notes</h3>
                <p class="text-gray-700 whitespace-pre-wrap">{{ bonReception.notes }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    DocumentArrowDownIcon,
    ArrowLeftIcon,
    ArrowTopRightOnSquareIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    CheckBadgeIcon
} from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';

const props = defineProps({
    bonReception: {
        type: Object,
        required: true
    },
    totaux: {
        type: Object,
        default: () => ({})
    },
    comparaison: {
        type: Array,
        default: () => []
    }
});

// Méthodes de téléchargement
const downloadPdf = () => {
    window.open(route('bon-receptions.download-pdf', props.bonReception.id), '_blank');
};

const downloadBonLivraison = () => {
    window.open(route('bon-receptions.download-bon-livraison', props.bonReception.id), '_blank');
};

const downloadFacture = () => {
    window.open(route('bon-receptions.download-facture', props.bonReception.id), '_blank');
};

// Méthodes utilitaires
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
    if (!amount) return '0,00 DH';
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MAD' }).format(amount);
};

const getTypeBadgeClass = (type) => {
    const classes = {
        'complet': 'bg-green-100 text-green-800',
        'partiel': 'bg-orange-100 text-orange-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const getTypeLabel = (type) => {
    const labels = {
        'complet': 'Complet',
        'partiel': 'Partiel'
    };
    return labels[type] || type;
};

const getStatutBadgeClass = (statut) => {
    const classes = {
        'valide': 'bg-green-100 text-green-800',
        'brouillon': 'bg-yellow-100 text-yellow-800',
        'annule': 'bg-red-100 text-red-800'
    };
    return classes[statut] || 'bg-gray-100 text-gray-800';
};

const getStatutLabel = (statut) => {
    const labels = {
        'valide': 'Validé',
        'brouillon': 'Brouillon',
        'annule': 'Annulé'
    };
    return labels[statut] || statut;
};
</script>