<!-- resources/js/Pages/BonsCommande/Show.vue -->
<template>
  <AuthenticatedLayout>
    <Head :title="`Bon de Commande - ${marche.reference}`" />
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 w-full mx-auto">
    <!-- En-tête -->
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
      <div class="flex space-x-3 mb-4">
        <!-- Bouton retour -->
        <Link
          :href="route('bon-commandes.index')"
          class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2 transition-colors"
        >
        <ArrowLeftIcon class="h-5 w-5" />
          Retour
        </Link>

        <!-- Bouton PDF -->
        <a
          v-if="marche.statut !== 'cree' && marche.statut !== 'annule'"
          :href="route('bon-commandes.pdf', marche.id)"
          target="_blank"
          class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2 transition-colors"
        >
          <DocumentArrowDownIcon class="h-5 w-5" />
          PDF
        </a>
      </div>
      
      <div class="flex justify-between items-start">
        <div>
            <p class="flex items-center">
                <h1 class="text-2xl font-bold text-gray-900">Marché</h1>
                <span class="ms-4 px-2 py-1 text-xs rounded-full" :class="getBonCommandeStatutInfo(marche.statut).color">{{ getBonCommandeStatutInfo(marche.statut).label }}</span>
            </p>
          <p class="text-gray-600 mt-1">Référence: {{ marche.reference }}</p>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-600">Date de création</p>
          <p class="font-semibold text-gray-900">{{ formatDate(marche.created_at) }}</p>
        </div>
      </div>
    </div>

    <!-- Informations fournisseur et dates -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Fournisseur -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Fournisseur</h3>
          <div class="space-y-1">
            <p class="text-gray-700"><span class="font-medium">Nom:</span> {{ marche.fournisseur?.nom || 'Non spécifié' }}</p>
            <p class="text-gray-700"><span class="font-medium">Contact:</span> {{ marche.fournisseur?.contact || 'N/A' }}</p>
          </div>
        </div>

        <!-- Informations Bon de Commande -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Informations du Marché</h3>
          <div class="space-y-1">
            <p class="text-gray-700">
              <span class="font-medium">Catégorie:</span> {{ marche.categorie }}
            </p>
            <p class="text-gray-700">
              <span class="font-medium">Date de début:</span> {{ formatDate(marche.date_debut) }}
            </p>
            <p class="text-gray-700">
              <span class="font-medium">Date de fin:</span> {{ formatDate(marche.date_fin) }}
            </p>
            <p class="text-gray-700">
              <span class="font-medium">Commande pour le:</span> {{ formatDate(marche.date_mise_ligne) }}
            </p>
            <p class="text-gray-700">
              <span class="font-medium">Date limite réception:</span> {{ formatDate(marche.date_limite_reception) }}
            </p>
            
            <!-- Afficher infos d'annulation si annulé -->
            <div v-if="marche.statut === 'annule'" class="mt-3 p-3 border border-red-200 rounded-lg bg-red-50">
              <p class="text-sm text-red-700">
                <span class="font-semibold">Date d’annulation:</span>
                {{ formatDate(marche.annule_at) || 'Non spécifiée' }}
              </p>
              <p class="text-sm text-red-700 mt-1">
                <span class="font-semibold">Raison:</span>
                {{ marche.reason_annulation || 'Aucune raison fournie' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tableau des articles -->
    <div class="px-6 py-4" v-if="marche.statut == 'livre_completement' || marche.statut == 'livre_partiellement' || marche.statut == 'attente_livraison'">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Articles Commandés</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Désignation</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unité</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix Unitaire</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TVA</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total TTC</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="(ligne, index) in marche.lignes" 
              :key="index"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                {{ ligne.code || 'N/A' }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-900">
                {{ ligne.designation || 'Non spécifié' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                {{ ligne.unite_mesure || 'Unité' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                {{ formatNumber(ligne.quantite) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 ">
                {{ formatMoney(ligne.prix_unitaire) }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                {{ ligne.tva }}%
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                {{ formatMoney(ligne.total_ttc) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-50 border-t-2 border-gray-200">
            <tr>
              <td colspan="6" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">
                Total HT:
              </td>
              <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">
                {{ formatMoney(marche.totalHt) }}
              </td>
            </tr>
            <tr>
              <td colspan="6" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">
                Total TVA:
              </td>
              <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">
                {{ formatMoney(marche.totalTVA) }}
              </td>
            </tr>
            <tr>
              <td colspan="6" class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                Total TTC:
              </td>
              <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                {{ formatMoney(marche.totalTTC) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div v-else class="bg-red-50 border border-red-200 rounded-md p-4">
              <div class="flex items-center">
                  <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  <h4 class="text-red-800 font-semibold">Ce Marché a été annulé ou vient d’être créé.</h4>
              </div>
          </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';
import { getBonCommandeStatutInfo } from '@/Utils/labels';

// Props
const props = defineProps({
  marche: {
    type: Object,
    required: true
  }
});

const getStatutLabel = (statut) => {
    const labels = {
        cree: 'Créé',
        attente_livraison: 'En attente livraison',
        livre_completement: 'Livré complètement',
        livre_partiellement: 'Livré partiellement',
        annule: 'Annulé',
    };
    return labels[statut] || statut;
};

// Computed
const getStatutBadgeClass = (statut) => {
    const classes = {
        cree: 'bg-blue-100 text-blue-800',
        attente_livraison: 'bg-yellow-100 text-yellow-800',
        livre_completement: 'bg-green-100 text-green-800',
        livre_partiellement: 'bg-orange-100 text-orange-800',
        annule: 'bg-red-100 text-red-800',
    };
    return classes[statut] || 'bg-gray-100 text-gray-800';
};

const parsedPiecesJointes = computed(() => {
  if (!props.marche.pieces_jointes) return [];
  try {
    return JSON.parse(props.marche.pieces_jointes);
  } catch {
    return [];
  }
});

// Methods
const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('fr-FR');
};

const formatMoney = (amount) => {
  if (!amount) return '0,00 MAD';
  return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD',
        minimumFractionDigits: 2
    }).format(amount || 0);
};


const formatNumber = (number) => {
  if (!number || isNaN(number)) return '0';
  return new Intl.NumberFormat('fr-FR').format(number);
};

const generatePDF = () => {
  // Implémentation de la génération PDF
  throw new Error("Not Implemented yet");
  
//   window.open(route('bons-commande.pdf', props.marche.id), '_blank');
};
</script>