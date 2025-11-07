<template>
  <AuthenticatedLayout>
    <Head title="Tableau de bord" />

  <div class="min-h-screen bg-gray-50 p-6 space-y-8">

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <KpiCard
        title="Utilisateurs"
        :value="stats.totalUsers"
        icon="UserGroupIcon"
        color="blue"
      />
      <KpiCard
        title="Fournisseurs actifs"
        :value="stats.activeFournisseurs"
        icon="TruckIcon"
        color="green"
      />
      <KpiCard
        title="Articles"
        :value="stats.totalArticles"
        icon="ArchiveBoxIcon"
        color="orange"
      />
      <KpiCard
        title="Bons de commande"
        :value="stats.totalBCs"
        icon="DocumentTextIcon"
        color="purple"
      />
      <KpiCard
        title="Demandes en attente"
        :value="stats.pendingDemandes"
        icon="ClockIcon"
        color="red"
      />
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Bon Commande Status -->
      <div class="bg-white rounded-2xl shadow-sm p-4">
        <h2 class="text-lg font-semibold mb-4">Statuts des bons de commande</h2>
        <apexchart type="pie" height="300" :options="commandeChartOptions" :series="commandeChartSeries" />
      </div>

      <!-- Top Fournisseurs -->
      <!-- Recent Demandes -->
      <div class="bg-white rounded-2xl shadow-sm p-4">
        <h2 class="text-lg font-semibold mb-4">Dernières demandes</h2>

        <template v-if="recentDemandes.length">
          <ul class="divide-y">
            <li
              v-for="d in recentDemandes"
              :key="d.numero"
              class="py-3 flex justify-between text-sm"
            >
              <div>
                <p class="font-medium">{{ d.numero }} — {{ d.demandeur }}</p>
                <p class="text-gray-500">{{ d.motif }}</p>
              </div>
              <span class="text-gray-700 text-xs">{{ d.date }}</span>
            </li>
          </ul>
        </template>

        <template v-else>
          <div class="flex flex-col items-center justify-center py-6 text-gray-500">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-10 w-10 text-gray-400 mb-2"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="1.5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"
              />
            </svg>
            <p class="text-sm">Aucune demande récente</p>
          </div>
        </template>
      </div>
    </div>

    <!-- Low Stock + Max Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Low Stock -->
      <div class="bg-white rounded-2xl shadow-sm p-4">
        <h2 class="text-lg font-semibold mb-4">Articles en stock faible</h2>
        <table class="w-full text-sm border border-gray-200 rounded-md">
          <thead class="bg-gray-100 text-gray-600">
            <tr>
              <th class="p-2 text-left">Référence</th>
              <th class="p-2 text-left">Désignation</th>
              <th class="p-2 text-center">Stock</th>
              <th class="p-2 text-center">Seuil</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="a in lowStockArticles"
              :key="a.reference"
              class="border-t text-gray-700"
            >
              <td class="p-2">{{ a.reference }}</td>
              <td class="p-2">{{ a.designation }}</td>
              <td class="p-2 text-center">{{ a.quantite_stock }} <span class="text-red-600">(-{{ a.seuil_minimal - a.quantite_stock }})</span></td>
              <td class="p-2 text-center text-green-600 font-semibold">{{ a.seuil_minimal }}</td>
            </tr>
            <tr v-if="!lowStockArticles.length">
              <td colspan="4" class="p-2 text-center text-gray-400">
                Aucun article en stock faible
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Low Stock -->
      <div class="bg-white rounded-2xl shadow-sm p-4">
        <h2 class="text-lg font-semibold mb-4">Articles dépassant la quantité maximale</h2>
        <table class="w-full text-sm border border-gray-200 rounded-md">
          <thead class="bg-gray-100 text-gray-600">
            <tr>
              <th class="p-2 text-left">Référence</th>
              <th class="p-2 text-left">Désignation</th>
              <th class="p-2 text-center">Stock</th>
              <th class="p-2 text-center">Maximum</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="a in overstockedArticles"
              :key="a.reference"
              class="border-t text-gray-700"
            >
              <td class="p-2">{{ a.reference }}</td>
              <td class="p-2">{{ a.designation }}</td>
              <td class="p-2 text-center">
                {{ a.quantite_stock }}
                <span class="text-green-600">(+{{ a.quantite_stock - a.seuil_maximal }})</span>
              </td>
              <td class="p-2 text-center text-red-600 font-semibold">{{ a.seuil_maximal }}</td>
            </tr>
            <tr v-if="!overstockedArticles.length">
              <td colspan="4" class="p-2 text-center text-gray-400">
                Aucun article dépassant la quantité maximale
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Top Articles -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
      <h2 class="text-lg font-semibold mb-4">Articles les plus utilisés</h2>
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div
          v-for="a in topUsedArticles"
          :key="a.designation"
          class="p-3 rounded-lg bg-gray-50 text-center"
        >
          <p class="font-semibold text-gray-800">{{ a.designation }}</p>
          <p class="text-sm text-gray-600">{{ a.total_sorties }} {{ a.unite_mesure}}</p>
        </div>
      </div>
    </div>
  </div>
  </AuthenticatedLayout>  
</template>

<script setup>
import { computed } from 'vue'
import ApexChart from 'vue3-apexcharts'
import * as HeroIcons from '@heroicons/vue/24/outline'
import KpiCard from '@/Components/KpiCard.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import Dump from '@/Components/Dump.vue'

const props = defineProps({
  stats: Object,
  bonCommandeStatus: Object,
  topUsedArticles: Array,
  lowStockArticles: Array,
  recentDemandes: Array,
  overstockedArticles: Array,
})

// --- Bon Commande Chart ---
const commandeChartOptions = computed(() => ({
  chart: { type: 'pie' },
  labels: Object.keys(props.bonCommandeStatus),
  legend: { position: 'bottom' },
  colors: ['#60A5FA', '#FACC15', '#34D399', '#F87171', '#A78BFA'],
}))

const commandeChartSeries = computed(() => Object.values(props.bonCommandeStatus))


</script>
