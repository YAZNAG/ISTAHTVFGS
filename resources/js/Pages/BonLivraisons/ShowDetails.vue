<template>
  <AuthenticatedLayout>
    <Head :title="`Bon de Livraison - ${bonLivraison.numero}`" />

    <div class="bg-white rounded-lg shadow-lg border border-gray-200 w-full mx-auto">
      <!-- En-tête -->
      <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <div class="flex space-x-3 mb-4">
          <!-- Bouton retour -->
          <Link
            :href="route('bon-livraisons.index')"
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2 transition-colors"
          >
            <ArrowLeftIcon class="h-5 w-5" />
            Retour
          </Link>

          <!-- Bouton PDF -->
          <!-- <a
            v-if="bonLivraison.statut == 'livree'"
            target="_blank"
            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2 transition-colors"
          >
            <DocumentArrowDownIcon class="h-5 w-5" />
            PDF
          </a> -->
        </div>

        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Bon de Livraison</h1>
            <span
              class="ms-4 px-2 py-1 text-xs font-medium rounded-full"
              :class="getBonLivraisonInfo(bonLivraison.statut).color"
            >
              {{ getBonLivraisonInfo(bonLivraison.statut).label }}
            </span>
            <p class="text-gray-600 mt-1">Référence: {{ bonLivraison.numero }}</p>
          </div>

          <div class="text-right">
            <p class="text-sm text-gray-600">Date de création</p>
            <p class="font-semibold text-gray-900">{{ formatDate(bonLivraison.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Informations Livraison -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Fournisseur -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Fournisseur</h3>
            <div class="space-y-1">
              <p class="text-gray-700"><span class="font-medium">Nom:</span> {{ bonLivraison.fournisseur?.nom || 'Non spécifié' }}</p>
              <p class="text-gray-700"><span class="font-medium">Contact:</span> {{ bonLivraison.fournisseur?.contact || 'N/A' }}</p>
            </div>
          </div>

          <!-- Informations Livraison -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Détails de la Livraison</h3>
            <div class="space-y-1">
              <p class="text-gray-700"><span class="font-medium">Date de Livraison:</span> {{ bonLivraison.date_livraison }}</p>
              <p class="text-gray-700"><span class="font-medium">Réceptionné par:</span> {{ bonLivraison.receptionne_par || 'Non spécifié' }}</p>
              <p class="text-gray-700"><span class="font-medium">Statut:</span > <span :class="['px-2 py-1 text-xs font-medium rounded-full', getBonLivraisonInfo(bonLivraison.statut).color]">{{ getBonLivraisonInfo(bonLivraison.statut).label }}</span></p>

              <!-- Si annulé -->
              <div
                v-if="bonLivraison.statut === 'annule'"
                class="mt-3 p-3 border border-red-200 rounded-lg bg-red-50"
              >
                <p class="text-sm text-red-700">
                  <span class="font-semibold">Date d’annulation:</span>
                  {{ formatDate(bonLivraison.annule_at) || 'Non spécifiée' }}
                </p>
                <p class="text-sm text-red-700 mt-1">
                  <span class="font-semibold">Raison:</span>
                  {{ bonLivraison.reason_annulation || 'Aucune raison fournie' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tableau des articles livrés -->
      <div
        class="px-6 py-4"
        v-if="bonLivraison.items && bonLivraison.items.length > 0"
      >
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Articles Livrés</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <!-- <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th> -->
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Désignation</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unité</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantité Livrée</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix Unitaire</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taux TVA</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="(ligne, index) in bonLivraison.items"
                :key="index"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <!-- <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ ligne.code || 'N/A' }}</td> -->
                <td class="px-4 py-3 text-sm text-gray-900">{{ ligne.designation || 'Non spécifié' }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ ligne.unite_mesure || 'Unité' }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(ligne.quantite) }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatMoney(ligne.prix_unitaire) }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ ligne.taux_tva }}%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div
        v-else
        class="bg-red-50 border border-red-200 rounded-md p-4"
      >
        <div class="flex items-center">
          <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
          <h4 class="text-red-800 font-semibold">
            Aucun article livré ou ce Bon de Livraison est annulé.
          </h4>
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
import { getBonLivraisonInfo } from '@/Utils/labels';
import Dump from '@/Components/Dump.vue';

// Props
const props = defineProps({
  bonLivraison: {
    type: Object,
    required: true,
  },
});


const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('fr-FR');
};

const formatMoney = (amount) => {
  if (!amount) return '0,00 MAD';
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    minimumFractionDigits: 2,
  }).format(amount || 0);
};

const formatNumber = (number) => {
  if (!number || isNaN(number)) return '0';
  return new Intl.NumberFormat('fr-FR').format(number);
};
</script>
