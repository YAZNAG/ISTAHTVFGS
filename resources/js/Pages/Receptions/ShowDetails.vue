<template>
  <AuthenticatedLayout>
    <Head :title="`Bon de Reception - ${bonReception.numero}`" />

    <div class="bg-white rounded-lg shadow-lg border border-gray-200 w-full mx-auto">
      <!-- En-tête -->
      <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <div class="flex space-x-3 mb-4">
          <!-- Bouton retour -->
          <Link
            :href="route('bon-receptions.index')"
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center gap-2 transition-colors"
          >
            <ArrowLeftIcon class="h-5 w-5" />
            Retour
          </Link>

          <!-- Bouton PDF -->
          <!-- <a
            v-if="bonReception.statut == 'livree'"
            target="_blank"
            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2 transition-colors"
          >
            <DocumentArrowDownIcon class="h-5 w-5" />
            PDF
          </a> -->
        </div>

        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Bon de Reception</h1>
            <p class="text-gray-600 mt-1">Référence: {{ bonReception.numero }}</p>
          </div>

          <div class="text-right">
            <p class="text-sm text-gray-600">Date de création</p>
            <p class="font-semibold text-gray-900">{{ bonReception.created_at }}</p>
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
              <p class="text-gray-700"><span class="font-medium">Nom:</span> {{ bonReception.fournisseur?.nom || 'Non spécifié' }}</p>
              <p class="text-gray-700"><span class="font-medium">Contact:</span> {{ bonReception.fournisseur?.contact || 'N/A' }}</p>
            </div>
          </div>

          <!-- Informations Livraison -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Détails de la Livraison</h3>
            <div class="space-y-1">
              <p class="text-gray-700"><span class="font-medium">Date de Livraison:</span> {{ bonReception.date_livraison }}</p>
              <p class="text-gray-700"><span class="font-medium">Réceptionné par:</span> {{ bonReception.receptionne_par || 'Non spécifié' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tableau des articles livrés -->
      <div
        class="px-6 py-4"
      >
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Articles</h3>
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
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total TTC</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="(ligne, index) in bonReception.items"
                :key="index"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <!-- <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ ligne.code || 'N/A' }}</td> -->
                <td class="px-4 py-3 text-sm text-gray-900">{{ ligne.designation || 'Non spécifié' }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ ligne.unite_mesure || 'Unité' }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(ligne.quantite) }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatMoney(ligne.prix_unitaire) }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ ligne.taux_tva }}%</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                  {{ formatMoney(ligne.total_ttc) }}
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
              <tr>
                <td colspan="5" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">
                  Total TTC:
                </td>
                <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                  {{ formatMoney(bonReception.total_ttc) }}
                </td>
              </tr>
            </tfoot>
          </table>
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
import Dump from '@/Components/Dump.vue';

// Props
const props = defineProps({
  bonReception: {
    type: Object,
    required: true,
  },
});

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
