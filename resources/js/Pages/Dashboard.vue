<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import Apexchart from 'vue3-apexcharts'
import {
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
  BellAlertIcon,
  ClipboardDocumentListIcon,
  ExclamationTriangleIcon,
  ShoppingCartIcon,
} from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import KpiCard from '@/Components/KpiCard.vue'
import UiBadge from '@/Components/UI/Badge.vue'
import UiCard from '@/Components/UI/Card.vue'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
  bonCommandeStatus: {
    type: Object,
    default: () => ({}),
  },
  topUsedArticles: {
    type: Array,
    default: () => [],
  },
  recentDemandes: {
    type: Array,
    default: () => [],
  },
  recentSorties: {
    type: Array,
    default: () => [],
  },
  recentEntrees: {
    type: Array,
    default: () => [],
  },
  ficheCollectivePerMonth: {
    type: Array,
    default: () => new Array(12).fill(0),
  },
  marchesPerMonth: {
    type: Array,
    default: () => new Array(12).fill(0),
  },
  receptionsPerMonth: {
    type: Array,
    default: () => new Array(12).fill(0),
  },
  stockEntreesPerMonth: {
    type: Array,
    default: () => new Array(12).fill(0),
  },
  stockSortiesPerMonth: {
    type: Array,
    default: () => new Array(12).fill(0),
  },
  consumptionByMarket: {
    type: Array,
    default: () => [],
  },
  categoryDistribution: {
    type: Array,
    default: () => [],
  },
  dashboardAlerts: {
    type: Object,
    default: () => ({}),
  },
})

const months = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec']

const statusLabels = {
  cree: 'Cree',
  attente_livraison: 'En attente livraison',
  livre_partiellement: 'Livre partiellement',
  livre_completement: 'Livre completement',
  annule: 'Annule',
}

const statusTone = {
  cree: 'info',
  attente_livraison: 'warning',
  livre_partiellement: 'warning',
  livre_completement: 'success',
  annule: 'danger',
}

const currencyFormatter = new Intl.NumberFormat('fr-MA', {
  style: 'currency',
  currency: 'MAD',
  minimumFractionDigits: 0,
  maximumFractionDigits: 0,
})

const statusEntries = computed(() => Object.entries(props.bonCommandeStatus || {}))
const statusLabelsList = computed(() => statusEntries.value.map(([key]) => statusLabels[key] || key))
const statusValues = computed(() => statusEntries.value.map(([, value]) => Number(value || 0)))
const totalStatus = computed(() => statusValues.value.reduce((sum, value) => sum + value, 0))

const stockValueFormatted = computed(() => currencyFormatter.format(Number(props.stats.stockValue || 0)))
const engagedAmountFormatted = computed(() => currencyFormatter.format(Number(props.stats.engagedAmount || 0)))
const consumedAmountFormatted = computed(() => currencyFormatter.format(Number(props.stats.consumedAmount || 0)))
const remainingAmountFormatted = computed(() => currencyFormatter.format(Number(props.stats.remainingAmount || 0)))

const marcheStatusOptions = computed(() => ({
  chart: {
    type: 'donut',
    toolbar: { show: false },
    fontFamily: 'Figtree, system-ui, sans-serif',
    animations: { enabled: true, speed: 450 },
  },
  labels: statusLabelsList.value,
  colors: ['#00AEEF', '#F5A623', '#14b8a6', '#4CAF50', '#E53935'],
  stroke: { width: 4, colors: ['#ffffff'] },
  legend: {
    position: 'bottom',
    labels: { colors: '#475569' },
    markers: { radius: 6 },
  },
  dataLabels: { style: { fontSize: '12px', fontWeight: 700 } },
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Marches',
            color: '#64748b',
            formatter: () => totalStatus.value,
          },
        },
      },
    },
  },
}))

const monthlyOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false },
    fontFamily: 'Figtree, system-ui, sans-serif',
    animations: { enabled: true, speed: 420 },
  },
  colors: ['#1B2D6B', '#00AEEF'],
  plotOptions: {
    bar: {
      borderRadius: 5,
      borderRadiusApplication: 'end',
      columnWidth: '45%',
    },
  },
  dataLabels: { enabled: false },
  grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
  xaxis: {
    categories: months,
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#64748b' } },
  },
  yaxis: {
    labels: {
      style: { colors: '#64748b' },
      formatter: value => Math.round(value),
    },
  },
  tooltip: { shared: true, intersect: false },
}))

const monthlySeries = computed(() => [
  { name: 'Marches', data: props.marchesPerMonth },
  { name: 'Receptions', data: props.receptionsPerMonth },
])

const marketConsumptionOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false },
    fontFamily: 'Figtree, system-ui, sans-serif',
    animations: { enabled: true, speed: 420 },
  },
  colors: ['#1B2D6B', '#00AEEF'],
  plotOptions: {
    bar: {
      borderRadius: 5,
      columnWidth: '48%',
    },
  },
  dataLabels: { enabled: false },
  grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
  xaxis: {
    categories: props.consumptionByMarket.map(marche => marche.reference),
    labels: { style: { colors: '#64748b' } },
  },
  yaxis: {
    labels: {
      style: { colors: '#64748b' },
      formatter: value => currencyFormatter.format(Number(value || 0)),
    },
  },
  tooltip: {
    y: {
      formatter: value => currencyFormatter.format(Number(value || 0)),
    },
  },
}))

const marketConsumptionSeries = computed(() => [
  { name: 'Engage', data: props.consumptionByMarket.map(marche => Number(marche.engage || 0)) },
  { name: 'Consomme', data: props.consumptionByMarket.map(marche => Number(marche.consomme || 0)) },
])

const categoryDistributionOptions = computed(() => ({
  chart: {
    type: 'donut',
    toolbar: { show: false },
    fontFamily: 'Figtree, system-ui, sans-serif',
    animations: { enabled: true, speed: 420 },
  },
  labels: props.categoryDistribution.map(item => item.categorie),
  colors: ['#1B2D6B', '#00AEEF', '#4CAF50', '#F5A623', '#E53935', '#14b8a6', '#64748b', '#8b5cf6'],
  stroke: { width: 4, colors: ['#ffffff'] },
  legend: { position: 'bottom', labels: { colors: '#475569' } },
  dataLabels: { style: { fontSize: '12px', fontWeight: 700 } },
}))

const categoryDistributionSeries = computed(() => props.categoryDistribution.map(item => Number(item.total || 0)))

const stockOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    fontFamily: 'Figtree, system-ui, sans-serif',
    animations: { enabled: true, speed: 420 },
  },
  colors: ['#4CAF50', '#E53935'],
  stroke: { curve: 'smooth', width: 3 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 0.15,
      opacityFrom: 0.28,
      opacityTo: 0.04,
      stops: [0, 100],
    },
  },
  dataLabels: { enabled: false },
  grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
  xaxis: {
    categories: months,
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#64748b' } },
  },
  yaxis: {
    labels: {
      style: { colors: '#64748b' },
      formatter: value => Math.round(value),
    },
  },
}))

const stockSeries = computed(() => [
  { name: 'Entrees', data: props.stockEntreesPerMonth },
  { name: 'Sorties', data: props.stockSortiesPerMonth },
])

const topArticlesOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false },
    fontFamily: 'Figtree, system-ui, sans-serif',
    animations: { enabled: true, speed: 420 },
  },
  colors: ['#00AEEF'],
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 5,
      barHeight: '58%',
    },
  },
  dataLabels: {
    enabled: true,
    formatter: (value, opts) => {
      const unit = props.topUsedArticles[opts.dataPointIndex]?.unite_mesure || ''
      return `${value} ${unit}`.trim()
    },
    style: { fontSize: '12px', colors: ['#102a43'] },
  },
  xaxis: {
    categories: props.topUsedArticles.map(article => article.designation),
    labels: { style: { colors: '#64748b' } },
  },
  yaxis: { labels: { style: { colors: '#475569', fontSize: '12px' } } },
  grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
}))

const topArticlesSeries = computed(() => [{
  name: 'Quantite utilisee',
  data: props.topUsedArticles.map(article => Number(article.total_sorties || 0)),
}])

const ficheOptions = computed(() => ({
  chart: {
    type: 'line',
    toolbar: { show: false },
    fontFamily: 'Figtree, system-ui, sans-serif',
    animations: { enabled: true, speed: 420 },
  },
  colors: ['#1B2D6B'],
  stroke: { width: 3, curve: 'smooth' },
  markers: { size: 4, colors: ['#1B2D6B'], strokeColors: '#ffffff', strokeWidth: 2 },
  dataLabels: { enabled: false },
  grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
  xaxis: {
    categories: months,
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#64748b' } },
  },
  yaxis: {
    labels: {
      style: { colors: '#64748b' },
      formatter: value => Math.round(value),
    },
  },
}))

const ficheSeries = computed(() => [{
  name: 'Fiches collectivite',
  data: props.ficheCollectivePerMonth,
}])

const operationAlerts = computed(() => [
  {
    label: 'Articles sous seuil',
    value: props.stats.lowStockArticles || 0,
    tone: 'warning',
    href: '/stock/articles',
    icon: ExclamationTriangleIcon,
  },
  {
    label: 'Articles en rupture',
    value: props.stats.ruptureArticles || 0,
    tone: 'danger',
    href: '/stock/articles',
    icon: BellAlertIcon,
  },
  {
    label: 'Demandes a valider',
    value: props.stats.pendingDemandes || 0,
    tone: 'info',
    href: '/demandes',
    icon: ClipboardDocumentListIcon,
  },
  {
    label: 'Marches en attente',
    value: props.stats.pendingMarches || 0,
    tone: 'warning',
    href: '/achats/marches',
    icon: ShoppingCartIcon,
  },
  {
    label: 'Marches expirant sous 30 jours',
    value: props.dashboardAlerts.expire_30_days || 0,
    tone: 'warning',
    href: '/achats/marches',
    icon: ExclamationTriangleIcon,
  },
  {
    label: 'Marches consommes a 80%',
    value: props.dashboardAlerts.consumed_80 || 0,
    tone: 'warning',
    href: '/achats/marches',
    icon: BellAlertIcon,
  },
  {
    label: 'Marches consommes a 90%',
    value: props.dashboardAlerts.consumed_90 || 0,
    tone: 'danger',
    href: '/achats/marches',
    icon: BellAlertIcon,
  },
  {
    label: 'Marches totalement consommes',
    value: props.dashboardAlerts.consumed_100 || 0,
    tone: 'danger',
    href: '/achats/marches',
    icon: ExclamationTriangleIcon,
  },
  {
    label: 'Sans reception depuis 30 jours',
    value: props.dashboardAlerts.without_reception_30_days || 0,
    tone: 'info',
    href: '/achats/marches',
    icon: ClipboardDocumentListIcon,
  },
])

function demandeTone(statut) {
  if (['validee', 'livree'].includes(statut)) return 'success'
  if (['rejetee', 'annulee'].includes(statut)) return 'danger'
  if (statut === 'en_attente_validation') return 'warning'
  return 'info'
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Tableau de bord" />

    <section class="space-y-6">
      <div class="erp-hero">
        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-bold uppercase tracking-wide text-cyan-100">Pilotage ERP ISTAHT</p>
            <h2 class="mt-2 text-3xl font-bold tracking-normal text-white md:text-4xl">Tableau de bord operationnel</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-cyan-50/90">
              Vue consolidee des marches, fournisseurs, articles, receptions, stocks et demandes internes.
            </p>
          </div>
          <div class="grid grid-cols-2 gap-2 sm:flex">
            <UiBadge tone="info">Achats</UiBadge>
            <UiBadge tone="success">Stock</UiBadge>
            <UiBadge tone="warning">Demandes</UiBadge>
            <UiBadge tone="neutral">Inventaires</UiBadge>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <KpiCard title="Nombre de marches" :value="stats.totalBCs || 0" icon="DocumentTextIcon" color="blue" caption="Tous statuts confondus" />
        <KpiCard title="Marches actifs" :value="stats.activeMarches || 0" icon="ShoppingCartIcon" color="green" caption="Dans la periode courante" />
        <KpiCard title="Marches en attente" :value="stats.pendingMarches || 0" icon="ClockIcon" color="orange" caption="Creation ou livraison a suivre" />
        <KpiCard title="Fournisseurs" :value="stats.activeFournisseurs || 0" icon="BuildingOfficeIcon" color="cyan" caption="Fournisseurs actifs" />
        <KpiCard title="Marches expires" :value="stats.expiredMarches || 0" icon="ExclamationTriangleIcon" color="red" caption="Date fin depassee" />
        <KpiCard title="Bientot expires" :value="stats.expiringSoonMarches || 0" icon="ClockIcon" color="orange" caption="Expiration sous 30 jours" />
        <KpiCard title="Montant engage" :value="engagedAmountFormatted" icon="BanknotesIcon" color="blue" caption="Marches attribues" />
        <KpiCard title="Montant consomme" :value="consumedAmountFormatted" icon="ArrowTrendingUpIcon" color="green" caption="Receptions validees" />
        <KpiCard title="Montant restant" :value="remainingAmountFormatted" icon="ScaleIcon" color="cyan" caption="Engage moins consomme" />
        <KpiCard title="Articles" :value="stats.totalArticles || 0" icon="ArchiveBoxIcon" color="blue" caption="Catalogue disponible" />
        <KpiCard title="Articles sous seuil" :value="stats.lowStockArticles || 0" icon="ExclamationTriangleIcon" color="orange" caption="Stock faible" />
        <KpiCard title="Receptions du mois" :value="stats.receptionsThisMonth || 0" icon="ArchiveBoxArrowDownIcon" color="green" caption="Receptionnees ce mois" />
        <KpiCard title="Valeur du stock" :value="stockValueFormatted" icon="BanknotesIcon" color="cyan" caption="Estimation prix marche courant" />
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <UiCard class="xl:col-span-2">
          <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="erp-section-title">Evolution marches et receptions</h3>
              <p class="erp-section-subtitle">Volume mensuel sur l'exercice courant</p>
            </div>
            <UiBadge tone="info">{{ stats.receptionsThisMonth || 0 }} reception(s) ce mois</UiBadge>
          </div>
          <apexchart type="bar" height="330" :options="monthlyOptions" :series="monthlySeries" />
        </UiCard>

        <UiCard>
          <div class="mb-4 flex items-start justify-between gap-3">
            <div>
              <h3 class="erp-section-title">Statuts des marches</h3>
              <p class="erp-section-subtitle">Cycle creation, attribution, livraison, annulation</p>
            </div>
            <UiBadge tone="info">{{ totalStatus }} total</UiBadge>
          </div>
          <apexchart type="donut" height="300" :options="marcheStatusOptions" :series="statusValues" />

          <div class="mt-4 grid grid-cols-1 gap-2">
            <div
              v-for="[status, value] in statusEntries"
              :key="status"
              class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-sm"
            >
              <UiBadge :tone="statusTone[status] || 'neutral'">{{ statusLabels[status] || status }}</UiBadge>
              <span class="font-bold text-istaht-navy">{{ value }}</span>
            </div>
          </div>
        </UiCard>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <UiCard class="xl:col-span-2">
          <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="erp-section-title">Consommation par marche</h3>
              <p class="erp-section-subtitle">Montant engage, consomme et restant par marche attribue</p>
            </div>
            <UiBadge tone="success">{{ remainingAmountFormatted }} restant</UiBadge>
          </div>
          <apexchart type="bar" height="330" :options="marketConsumptionOptions" :series="marketConsumptionSeries" />
        </UiCard>

        <UiCard>
          <div class="mb-4">
            <h3 class="erp-section-title">Repartition des categories</h3>
            <p class="erp-section-subtitle">Nombre de marches par categorie metier</p>
          </div>
          <apexchart type="donut" height="300" :options="categoryDistributionOptions" :series="categoryDistributionSeries" />
        </UiCard>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <UiCard class="xl:col-span-2">
          <div class="mb-4">
            <h3 class="erp-section-title">Mouvements de stock</h3>
            <p class="erp-section-subtitle">Entrees et sorties mensuelles, utiles pour anticiper les seuils</p>
          </div>
          <apexchart type="area" height="330" :options="stockOptions" :series="stockSeries" />
        </UiCard>

        <UiCard>
          <div class="mb-4">
            <h3 class="erp-section-title">Centre d'alertes</h3>
            <p class="erp-section-subtitle">Priorites administratives a traiter</p>
          </div>

          <div class="space-y-3">
            <Link
              v-for="alert in operationAlerts"
              :key="alert.label"
              :href="alert.href"
              class="group flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50 p-3 transition hover:border-cyan-200 hover:bg-cyan-50"
            >
              <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-istaht-navy shadow-sm transition group-hover:text-istaht-blue">
                  <component :is="alert.icon" class="h-5 w-5" />
                </span>
                <div>
                  <p class="text-sm font-bold text-istaht-navy">{{ alert.label }}</p>
                  <p class="text-xs text-slate-500">Acces rapide au module concerne</p>
                </div>
              </div>
              <UiBadge :tone="alert.tone">{{ alert.value }}</UiBadge>
            </Link>
          </div>
        </UiCard>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <UiCard>
          <div class="mb-4">
            <h3 class="erp-section-title">Articles les plus utilises</h3>
            <p class="erp-section-subtitle">Consommation recente issue des sorties</p>
          </div>
          <apexchart type="bar" height="360" :options="topArticlesOptions" :series="topArticlesSeries" />
        </UiCard>

        <UiCard>
          <div class="mb-4">
            <h3 class="erp-section-title">Fiches collectivite</h3>
            <p class="erp-section-subtitle">Creation mensuelle des fiches techniques</p>
          </div>
          <apexchart type="line" height="360" :options="ficheOptions" :series="ficheSeries" />
        </UiCard>

        <UiCard>
          <div class="mb-4">
            <h3 class="erp-section-title">Dernieres demandes</h3>
            <p class="erp-section-subtitle">Activite recente a surveiller</p>
          </div>

          <div v-if="recentDemandes.length" class="space-y-3">
            <div
              v-for="demande in recentDemandes"
              :key="demande.numero"
              class="rounded-lg border border-slate-100 bg-slate-50/80 p-3 transition hover:border-cyan-200 hover:bg-cyan-50/70"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <p class="truncate text-sm font-bold text-istaht-navy">{{ demande.numero }} - {{ demande.demandeur }}</p>
                  <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ demande.motif || 'Sans motif renseigne' }}</p>
                </div>
                <UiBadge :tone="demandeTone(demande.statut)">{{ demande.statut }}</UiBadge>
              </div>
              <p class="mt-2 text-xs font-medium text-slate-400">{{ demande.date }}</p>
            </div>
          </div>

          <div v-else class="ui-empty-state py-10">
            <ClipboardDocumentListIcon class="mb-3 h-10 w-10 text-slate-300" />
            <p class="text-sm font-bold text-istaht-navy">Aucune demande recente</p>
          </div>
        </UiCard>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <UiCard :padded="false">
          <div class="ui-panel-header flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-istaht-red">
              <ArrowUpTrayIcon class="h-5 w-5" />
            </span>
            <div>
              <h3 class="erp-section-title">Dernieres sorties stock</h3>
              <p class="erp-section-subtitle">Articles consommes ou livres</p>
            </div>
          </div>

          <div v-if="recentSorties.length" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Article</th>
                  <th class="text-right">Quantite sortie</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sortie in recentSorties" :key="`${sortie.designation_article}-${sortie.date_sortie}`">
                  <td>{{ sortie.date_sortie }}</td>
                  <td class="font-semibold text-slate-800">{{ sortie.designation_article }}</td>
                  <td class="text-right font-bold text-istaht-red">
                    {{ sortie.quantite_sortie }} <span class="text-xs text-slate-400">{{ sortie.unite_mesure }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="ui-empty-state">
            <ClipboardDocumentListIcon class="mb-3 h-10 w-10 text-slate-300" />
            <p class="text-sm font-bold text-istaht-navy">Aucune sortie recente</p>
          </div>
        </UiCard>

        <UiCard :padded="false">
          <div class="ui-panel-header flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-istaht-green">
              <ArrowDownTrayIcon class="h-5 w-5" />
            </span>
            <div>
              <h3 class="erp-section-title">Dernieres entrees stock</h3>
              <p class="erp-section-subtitle">Articles receptionnes</p>
            </div>
          </div>

          <div v-if="recentEntrees.length" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Article</th>
                  <th class="text-right">Quantite entree</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="entree in recentEntrees" :key="`${entree.designation_article}-${entree.date_entree}`">
                  <td>{{ entree.date_entree }}</td>
                  <td class="font-semibold text-slate-800">{{ entree.designation_article }}</td>
                  <td class="text-right font-bold text-istaht-green">
                    {{ entree.quantite_entree }} <span class="text-xs text-slate-400">{{ entree.unite_mesure }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="ui-empty-state">
            <ClipboardDocumentListIcon class="mb-3 h-10 w-10 text-slate-300" />
            <p class="text-sm font-bold text-istaht-navy">Aucune entree recente</p>
          </div>
        </UiCard>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
