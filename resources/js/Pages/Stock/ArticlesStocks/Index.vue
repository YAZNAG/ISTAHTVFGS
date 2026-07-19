<script setup>
import { computed, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import {
  ArrowPathIcon,
  ArchiveBoxIcon,
  BellAlertIcon,
  DocumentArrowDownIcon,
  ExclamationTriangleIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import KpiCard from '@/Components/KpiCard.vue'
import Pagination from '@/Components/Pagination.vue'
import UiBadge from '@/Components/UI/Badge.vue'
import UiCard from '@/Components/UI/Card.vue'

const props = defineProps({
  articles: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  categories: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
})

const filters = ref({
  search: props.filters.search || '',
  categorie: props.filters.categorie || '',
})

const activeFilterCount = computed(() => Object.values(filters.value).filter(Boolean).length)

let filterTimer = null
watch(filters, value => {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    router.get(route('articles-stocks.index'), value, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 280)
}, { deep: true })

function resetFilters() {
  filters.value = { search: '', categorie: '' }
}

function stockInfo(article) {
  const quantity = Number(article.quantite_stock || 0)
  const threshold = Number(article.seuil_minimal || 0)

  if (quantity <= 0) return { label: 'Rupture', tone: 'danger', border: 'status-border-danger' }
  // Stock faible : quantite <= 80% du seuil minimal (-20%)
  if (threshold > 0 && quantity <= threshold * 0.8) return { label: 'Stock faible', tone: 'warning', border: 'status-border-warning' }
  return { label: 'Stock normal', tone: 'success', border: 'status-border-success' }
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Gestion du Stock" />

    <section class="space-y-6">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <ArchiveBoxIcon class="h-6 w-6" />
              Niveaux de stock
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Consultation rapide des stocks, seuils critiques et export PDF pour suivi administratif.
            </p>
          </div>

          <a
            :href="route('articles-stocks.export', filters.categorie ? { categorie: filters.categorie } : {})"
            target="_blank"
            class="ui-button ui-button-primary"
          >
            <DocumentArrowDownIcon class="mr-1.5 h-4 w-4" />
            Export PDF
          </a>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <KpiCard title="Articles suivis" :value="stats.total || articles.total || 0" icon="ArchiveBoxIcon" color="blue" caption="Catalogue stock" />
        <KpiCard title="Quantite totale" :value="stats.stockTotal || 0" icon="CubeIcon" color="cyan" caption="Toutes unites confondues" />
        <KpiCard title="Sous seuil" :value="stats.lowStock || 0" icon="ExclamationTriangleIcon" color="orange" caption="A reapprovisionner" />
        <KpiCard title="Rupture" :value="stats.rupture || 0" icon="BellAlertIcon" color="red" caption="Critique" />
      </div>

      <UiCard>
        <div class="erp-table-toolbar">
          <div class="grid flex-1 grid-cols-1 gap-3 md:grid-cols-2">
            <div class="relative">
              <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-slate-400" />
              <input
                v-model="filters.search"
                type="search"
                placeholder="Reference ou designation..."
                class="ui-input w-full pl-10"
              />
            </div>

            <select v-model="filters.categorie" class="ui-input">
              <option value="">Toutes les categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
            </select>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <UiBadge v-if="activeFilterCount" tone="info">{{ activeFilterCount }} filtre(s)</UiBadge>
            <button class="ui-button ui-button-ghost px-4 py-2 text-sm" type="button" @click="resetFilters">
              <ArrowPathIcon class="h-4 w-4" />
              Reinitialiser
            </button>
          </div>
        </div>
      </UiCard>

      <UiCard :padded="false" class="overflow-hidden">
        <div class="ui-panel-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="erp-section-title">Etat du stock</h3>
            <p class="erp-section-subtitle">Lecture directe des articles normaux, faibles ou en rupture.</p>
          </div>
          <UiBadge tone="info">{{ articles.total || 0 }} article(s)</UiBadge>
        </div>

        <div v-if="articles.data.length" class="overflow-x-auto">
          <table>
            <thead>
              <tr>
                <th>Reference</th>
                <th>Categorie</th>
                <th>Designation</th>
                <th>Unite</th>
                <th>Stock</th>
                <th>Etat</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="article in articles.data"
                :key="article.id"
                class="animate-fade-up"
                :class="stockInfo(article).border"
              >
                <td class="font-mono font-bold text-istaht-navy">{{ article.reference || 'N/A' }}</td>
                <td><UiBadge tone="info">{{ article.categorie?.nom || 'N/A' }}</UiBadge></td>
                <td class="font-semibold text-slate-800">{{ article.designation || 'N/A' }}</td>
                <td class="font-semibold text-slate-600">{{ article.unite_mesure || '-' }}</td>
                <td class="text-lg font-bold text-istaht-navy">{{ article.quantite_stock || 0 }}</td>
                <td><UiBadge :tone="stockInfo(article).tone">{{ stockInfo(article).label }}</UiBadge></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="ui-empty-state py-14">
          <ArchiveBoxIcon class="mb-3 h-12 w-12 text-slate-300" />
          <h3 class="text-sm font-bold text-istaht-navy">Aucun article trouve</h3>
          <p class="mt-1 text-sm text-slate-500">Aucun resultat ne correspond aux filtres.</p>
        </div>

        <Pagination
          :links="articles.links || []"
          :from="articles.from || 0"
          :to="articles.to || 0"
          :total="articles.total || 0"
        />
      </UiCard>
    </section>
  </AuthenticatedLayout>
</template>
