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

    <!-- Recent Sortie + Recent Entrie -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Sorties -->
      <div class="bg-white rounded-2xl shadow-sm p-4">
        <h2 class="text-lg font-semibold mb-4">Dernières sorties stock</h2>

        <template v-if="recentSorties.length">
          <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200 rounded-md">
              <thead class="bg-gray-100 text-gray-600">
                <tr>
                  <th class="p-2 text-left">Date</th>
                  <th class="p-2 text-left">Article</th>
                  <th class="p-2 text-center">Qté sortie</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="s in recentSorties"
                  :key="s.reference_bon_sortie + s.designation_article + s.date_sortie"
                  class="border-t hover:bg-gray-50 even:bg-gray-50"
                >
                  <td class="p-2">{{ s.date_sortie }}</td>
                  <td class="p-2">
                    <p class="font-medium">{{ s.designation_article }}</p>
                  </td>
                  <td class="p-2 text-center font-semibold">{{ s.quantite_sortie }} <span class="text-xs text-gray-500">{{ s.unite_mesure }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
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
            <p class="text-sm">Aucune sortie récente</p>
          </div>
        </template>
      </div>

      <!-- Dernières entrées stock -->
      <div class="bg-white rounded-2xl shadow-sm p-4">
        <h2 class="text-lg font-semibold mb-4">Dernières entrées stock</h2>

        <template v-if="recentEntrees.length">
          <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200 rounded-md">
              <thead class="bg-gray-100 text-gray-600">
                <tr>
                  <th class="p-2 text-left">Date</th>
                  <th class="p-2 text-left">Article</th>
                  <th class="p-2 text-center">Qté entrée</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="e in recentEntrees"
                  :key="e.reference_bon_entree + e.designation_article + e.date_entree"
                  class="border-t hover:bg-gray-50 even:bg-gray-50"
                >
                  <td class="p-2">{{ e.date_entree }}</td>
                  <td class="p-2">
                    <p class="font-medium">{{ e.designation_article }}</p>
                  </td>
                  <td class="p-2 text-center font-semibold">
                    {{ e.quantite_entree }} <span class="text-xs text-gray-500">{{ e.unite_mesure }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
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
            <p class="text-sm">Aucune entrée récente</p>
          </div>
        </template>
      </div>

    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4">
      <h2 class="text-lg font-semibold mb-4">Articles les plus utilisés</h2>
      <apexchart 
        type="bar" 
        height="350" 
        :options="topArticlesChartOptions" 
        :series="topArticlesChartSeries" 
      />
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4">
      <h2 class="text-lg font-semibold mb-4">Fiches collectives créées par mois</h2>
      <apexchart 
        type="bar" 
        height="360" 
        :options="ficheCollectiveMonthlyChartOptions" 
        :series="ficheCollectiveMonthlyChartSeries" 
      />
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

const props = defineProps({
  stats: Object,
  bonCommandeStatus: Object,
  topUsedArticles: Array,
  recentDemandes: Array,
  recentSorties: Array,
  recentEntrees: Array,
  ficheCollectivePerMonth: {
    type: Array,
    default: () => new Array(12).fill(0)
  }
  
})

// --- Bon Commande Chart ---
const commandeChartOptions = computed(() => ({
  chart: { type: 'pie' },
  labels: Object.keys(props.bonCommandeStatus),
  legend: { position: 'bottom' },
  colors: ['#60A5FA', '#FACC15', '#34D399', '#F87171', '#A78BFA'],
}))

const commandeChartSeries = computed(() => Object.values(props.bonCommandeStatus))



const topArticlesChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false }
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 4,
      dataLabels: {
        position: 'top',
      },
    }
  },
  dataLabels: {
    enabled: true,
    formatter: function (val, opts) {
      const unit = props.topUsedArticles[opts.dataPointIndex]?.unite_mesure || '';
      return val + ' ' + unit;
    },
    style: {
      fontSize: '12px',
      colors: ['#334155']
    }
  },
  xaxis: {
    categories: props.topUsedArticles.map(a => a.designation),
    labels: {
      style: {
        fontSize: '12px'
      }
    }
  },
  yaxis: {
    labels: {
      style: {
        fontSize: '12px'
      }
    }
  },
  colors: ['#60A5FA'],
  grid: {
    borderColor: '#f1f5f9',
    xaxis: {
      lines: {
        show: true
      }
    }
  },
  tooltip: {
    y: {
      formatter: function (val, opts) {
        const unit = props.topUsedArticles[opts.dataPointIndex]?.unite_mesure || '';
        return val + ' ' + unit;
      }
    }
  }
}))

const topArticlesChartSeries = computed(() => [{
  name: 'Quantité utilisée',
  data: props.topUsedArticles.map(a => a.total_sorties)
}])
const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

const ficheCollectiveMonthlyChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false },
    fontFamily: 'Inter, system-ui, sans-serif'
  },
  plotOptions: {
    bar: {
      columnWidth: '60%',
      borderRadius: 6,
      borderRadiusApplication: 'end',
      dataLabels: {
        position: 'top'
      }
    }
  },
  dataLabels: {
    enabled: true,
    formatter: (val) => val > 0 ? val : '',
    offsetY: -20,
    style: {
      fontSize: '12px',
      colors: ['#64748b']
    }
  },
  colors: ['#6366f1'],
  fill: {
    type: 'gradient',
    gradient: {
      shade: 'light',
      type: 'vertical',
      shadeIntensity: 0.5,
      gradientToColors: ['#3b82f6'],
      inverseColors: false,
      opacityFrom: 1,
      opacityTo: 0.8,
      stops: [0, 100]
    }
  },
  xaxis: {
    categories: months,
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
      style: {
        colors: '#64748b',
        fontSize: '12px'
      }
    },
    tooltip: { enabled: false }
  },
  yaxis: {
    labels: {
      style: {
        colors: '#64748b',
        fontSize: '12px'
      },
      formatter: (val) => Math.round(val)
    }
  },
  grid: {
    borderColor: '#e2e8f0',
    strokeDashArray: 4,
    yaxis: { lines: { show: true } },
    padding: { top: 0, right: 0, bottom: 0, left: 10 }
  },
  tooltip: {
    y: {
      formatter: (val) => `${val} fiche${val > 1 ? 's' : ''} collective${val > 1 ? 's' : ''}`
    }
  },
  states: {
    hover: {
      filter: {
        type: 'lighten',
        value: 0.1
      }
    }
  }
}))

const ficheCollectiveMonthlyChartSeries = computed(() => [{
  name: 'Fiches créées',
  data: props.ficheCollectivePerMonth
}])
</script>
