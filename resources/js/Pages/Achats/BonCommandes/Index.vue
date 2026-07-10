<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import UiBadge from '@/Components/UI/Badge.vue'
import { usePermission } from '@/Utils/permission'

const { can } = usePermission()

const props = defineProps({
  marches: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  categories: {
    type: Array,
    default: () => [],
  },
  fournisseurs: {
    type: Array,
    default: () => [],
  },
})

const filters = ref({
  search: props.filters?.search || props.filters?.reference || props.filters?.objet || '',
  statut: props.filters?.statut || '',
  categorie_id: props.filters?.categorie_id || '',
  fournisseur_id: props.filters?.fournisseur_id || '',
  date: props.filters?.date || '',
})

const pendingDelete = ref(null)
const pendingCancel = ref(null)
const isDeleting = ref(false)
const isCancelling = ref(false)
const showMobileFilters = ref(false)

const cancelForm = useForm({
  raison: '',
})

const statusMap = {
  cree: { label: 'Cree', tone: 'info', bar: 'bg-istaht-blue' },
  attente_livraison: { label: 'Attente livraison', tone: 'warning', bar: 'bg-istaht-amber' },
  livre_partiellement: { label: 'Livre partiellement', tone: 'warning', bar: 'bg-orange-500' },
  livre_completement: { label: 'Livre completement', tone: 'success', bar: 'bg-istaht-green' },
  annule: { label: 'Annule', tone: 'danger', bar: 'bg-istaht-red' },
}

const activeFilterCount = computed(() => Object.values(filters.value).filter(Boolean).length)

const exportParams = computed(() => Object.fromEntries(
  Object.entries(filters.value).filter(([, value]) => value !== '' && value !== null && value !== undefined)
))

const kpis = computed(() => [
  { label: 'Total marches', value: formatNumber(props.stats.total), tone: 'text-istaht-navy' },
  { label: 'Actifs', value: formatNumber(props.stats.actifs), tone: 'text-istaht-green' },
  { label: 'Attente livraison', value: formatNumber(props.stats.attente_livraison), tone: 'text-istaht-amber' },
  { label: 'Expires', value: formatNumber(props.stats.expires), tone: 'text-istaht-red' },
  { label: 'Clotures', value: formatNumber(props.stats.clotures), tone: 'text-istaht-green' },
  { label: 'Montant engage', value: formatCurrency(props.stats.montant_engage), tone: 'text-istaht-navy' },
  { label: 'Consomme', value: formatCurrency(props.stats.montant_consomme), tone: 'text-istaht-blue' },
  { label: 'Restant', value: formatCurrency(props.stats.montant_restant), tone: 'text-istaht-amber' },
])

const deleteMessage = computed(() => {
  if (!pendingDelete.value) return ''

  if (pendingDelete.value.can_delete_physical) {
    return `Le marche ${pendingDelete.value.reference} ne contient aucune donnee operationnelle. Confirmez-vous la suppression definitive ?`
  }

  return 'Ce marche contient des donnees operationnelles. Voulez-vous le desactiver ?'
})

const confirmDeleteLabel = computed(() => pendingDelete.value?.can_delete_physical ? 'Supprimer definitivement' : 'Desactiver le marche')

let filterTimer = null
watch(filters, value => {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    router.get(route('bon-commandes.index'), value, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 280)
}, { deep: true })

function resetFilters() {
  filters.value = {
    search: '',
    statut: '',
    categorie_id: '',
    fournisseur_id: '',
    date: '',
  }
}

function statusInfo(statut) {
  return statusMap[statut] || { label: 'Inconnu', tone: 'neutral', bar: 'bg-slate-400' }
}

function progressClass(percent) {
  if (Number(percent || 0) >= 90) return 'bg-istaht-red'
  if (Number(percent || 0) >= 80) return 'bg-istaht-amber'
  return 'bg-istaht-green'
}

function alertTone(type) {
  if (type === 'danger') return 'danger'
  if (type === 'warning') return 'warning'
  return 'info'
}

function canCancelMarket(marche) {
  return ['cree', 'attente_livraison'].includes(marche.statut)
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

function formatNumber(amount, fraction = 0) {
  return Number(amount || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: fraction,
    maximumFractionDigits: fraction,
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    minimumFractionDigits: 2,
  }).format(Number(amount || 0))
}

function askDelete(marche) {
  pendingDelete.value = marche
}

function closeDelete() {
  if (isDeleting.value) return
  pendingDelete.value = null
}

function askCancel(marche) {
  pendingCancel.value = marche
  cancelForm.reset()
  cancelForm.clearErrors()
}

function closeCancel() {
  if (isCancelling.value) return
  pendingCancel.value = null
  cancelForm.reset()
  cancelForm.clearErrors()
}

function confirmCancel() {
  if (!pendingCancel.value) return

  isCancelling.value = true
  cancelForm.post(route('bon-commandes.annuler', pendingCancel.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      pendingCancel.value = null
      cancelForm.reset()
    },
    onFinish: () => {
      isCancelling.value = false
    },
  })
}

function confirmDelete() {
  if (!pendingDelete.value) return

  isDeleting.value = true
  router.delete(route('bon-commandes.destroy', pendingDelete.value.id), {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false
      pendingDelete.value = null
    },
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Marches" />

    <section class="space-y-5">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
          <div>
            <p class="text-xs font-bold uppercase text-istaht-cyan">Achats et approvisionnement</p>
            <h2 class="mt-1 text-2xl font-bold text-istaht-navy">Marches</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
              Categorie, articles automatiques, fournisseur attributaire, livraisons, receptions et decomptes.
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <a
              v-if="can('export_marches')"
              :href="route('bon-commandes.export.pdf', exportParams)"
              class="ui-button ui-button-ghost"
              target="_blank"
              rel="noopener"
            >
              Export PDF
            </a>
            <a
              v-if="can('export_marches')"
              :href="route('bon-commandes.export.excel', exportParams)"
              class="ui-button ui-button-secondary"
              target="_blank"
              rel="noopener"
            >
              Export Excel
            </a>
            <ModalLink
              v-if="can('create_marches')"
              :href="route('bon-commandes.create')"
              class="ui-button ui-button-primary"
            >
              Nouveau marche
            </ModalLink>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
          <div
            v-for="item in kpis"
            :key="item.label"
            class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3"
          >
            <p class="text-xs font-bold uppercase text-slate-400">{{ item.label }}</p>
            <p class="mt-1 text-xl font-bold" :class="item.tone">{{ item.value }}</p>
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-soft">
        <div class="flex items-center justify-between gap-3 md:hidden">
          <p class="text-sm font-bold text-istaht-navy">Recherche et filtres</p>
          <button type="button" class="ui-button ui-button-ghost px-3 py-1.5 text-xs" @click="showMobileFilters = !showMobileFilters">
            {{ showMobileFilters ? 'Masquer' : 'Afficher' }}
          </button>
        </div>

        <div :class="['mt-3 grid grid-cols-1 gap-3 md:mt-0 md:grid-cols-[1.3fr_190px_190px_190px_160px_auto]', showMobileFilters ? 'grid' : 'hidden md:grid']">
          <input
            v-model="filters.search"
            type="search"
            class="ui-input"
            placeholder="Reference ou objet..."
          />

          <select v-model="filters.categorie_id" class="ui-input">
            <option value="">Toutes categories</option>
            <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
              {{ categorie.code || '-' }} - {{ categorie.nom }}
            </option>
          </select>

          <select v-model="filters.fournisseur_id" class="ui-input">
            <option value="">Tous fournisseurs</option>
            <option v-for="fournisseur in fournisseurs" :key="fournisseur.id" :value="fournisseur.id">
              {{ fournisseur.raison_sociale || fournisseur.nom }}
            </option>
          </select>

          <select v-model="filters.statut" class="ui-input">
            <option value="">Tous statuts</option>
            <option value="cree">Cree</option>
            <option value="attente_livraison">Attente livraison</option>
            <option value="livre_partiellement">Livre partiellement</option>
            <option value="livre_completement">Livre completement</option>
            <option value="annule">Annule</option>
          </select>

          <input v-model="filters.date" type="date" class="ui-input" />

          <button class="ui-button ui-button-ghost whitespace-nowrap" type="button" @click="resetFilters">
            Reinitialiser
          </button>
        </div>

        <div v-if="activeFilterCount" class="mt-3 text-sm font-semibold text-slate-500">
          {{ activeFilterCount }} filtre(s) actif(s)
        </div>
      </div>

      <div v-if="marches.data.length" class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article
          v-for="marche in marches.data"
          :key="marche.id"
          class="animate-fade-up overflow-hidden rounded-lg border border-slate-200 bg-white shadow-soft transition hover:-translate-y-0.5 hover:shadow-panel"
        >
          <div class="h-1.5" :class="statusInfo(marche.statut).bar" />

          <div class="p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-mono text-sm font-bold text-istaht-blue">{{ marche.reference }}</p>
                <h3 class="mt-1 line-clamp-2 text-lg font-bold text-istaht-navy">{{ marche.objet }}</h3>
              </div>
              <UiBadge :tone="statusInfo(marche.statut).tone">{{ statusInfo(marche.statut).label }}</UiBadge>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
              <span class="ui-badge bg-cyan-50 text-istaht-blue ring-1 ring-cyan-100">
                {{ marche.categorie?.nom || 'Sans categorie' }}
              </span>
              <span class="ui-badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">
                {{ marche.nombre_articles || 0 }} article(s)
              </span>
            </div>

            <div class="mt-4 rounded-lg bg-slate-50 p-3">
              <p class="text-xs font-bold uppercase text-slate-400">Fournisseur attributaire</p>
              <p class="mt-1 font-semibold text-slate-800">{{ marche.fournisseur?.nom_affichage || 'Non attribue' }}</p>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Date debut</p>
                <p class="mt-1 font-semibold text-slate-800">{{ formatDate(marche.date_debut) }}</p>
              </div>
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Date fin</p>
                <p class="mt-1 font-semibold text-slate-800">{{ formatDate(marche.date_fin) }}</p>
              </div>
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Montant HT</p>
                <p class="mt-1 font-bold text-istaht-navy">{{ formatCurrency(marche.total_ht) }}</p>
              </div>
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Montant TTC</p>
                <p class="mt-1 font-bold text-istaht-navy">{{ formatCurrency(marche.total_ttc) }}</p>
              </div>
            </div>

            <div class="mt-5">
              <div class="mb-2 flex items-center justify-between text-sm">
                <span class="font-semibold text-slate-600">Consommation</span>
                <span class="font-bold text-istaht-navy">{{ formatNumber(marche.consumption_percent, 1) }}%</span>
              </div>
              <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="progressClass(marche.consumption_percent)"
                  :style="{ width: `${Math.min(Number(marche.consumption_percent || 0), 100)}%` }"
                />
              </div>
              <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                <span>Consomme: {{ formatCurrency(marche.consumed_amount) }}</span>
                <span>Reste: {{ formatCurrency(marche.remaining_amount) }}</span>
              </div>
            </div>

            <div v-if="marche.alerts?.length" class="mt-4 flex flex-wrap gap-2">
              <UiBadge v-for="alert in marche.alerts" :key="alert.message" :tone="alertTone(alert.type)">
                {{ alert.message }}
              </UiBadge>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
              <Link
                v-if="can('show_marches')"
                :href="route('bon-commandes.show', marche.id)"
                class="ui-button ui-button-secondary px-3 py-1.5 text-xs"
              >
                Voir
              </Link>
              <ModalLink
                v-if="marche.statut === 'cree' && can('validate_marches')"
                :href="route('bon-commandes.edit', marche.id)"
                class="ui-button ui-button-primary px-3 py-1.5 text-xs"
              >
                Attribuer
              </ModalLink>
              <ModalLink
                v-if="marche.statut === 'cree' && can('edit_marches')"
                :href="route('bon-commandes.modify', marche.id)"
                class="ui-button ui-button-ghost px-3 py-1.5 text-xs"
              >
                Modifier
              </ModalLink>
              <a
                v-if="marche.statut !== 'cree' && marche.statut !== 'annule' && can('pdf_marches')"
                :href="route('bon-commandes.pdf', marche.id)"
                target="_blank"
                class="ui-button ui-button-ghost px-3 py-1.5 text-xs"
              >
                PDF
              </a>
              <button
                v-if="canCancelMarket(marche) && can('validate_marches')"
                type="button"
                class="ui-button bg-istaht-amber px-3 py-1.5 text-xs text-white hover:bg-orange-600"
                @click="askCancel(marche)"
              >
                Annuler
              </button>
              <button
                v-if="can('edit_marches')"
                type="button"
                class="ui-button ui-button-danger px-3 py-1.5 text-xs"
                @click="askDelete(marche)"
              >
                Supprimer
              </button>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="rounded-lg border border-slate-200 bg-white px-5 py-12 text-center shadow-soft">
        <h3 class="text-base font-bold text-istaht-navy">Aucun marche trouve</h3>
        <p class="mt-1 text-sm text-slate-500">Ajustez les filtres ou creez un nouveau marche.</p>
        <ModalLink
          v-if="can('create_marches')"
          :href="route('bon-commandes.create')"
          class="ui-button ui-button-primary mt-5"
        >
          Nouveau marche
        </ModalLink>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-soft">
        <Pagination
          :links="marches.links || []"
          :from="marches.from || 0"
          :to="marches.to || 0"
          :total="marches.total || 0"
        />
      </div>
    </section>

    <div
      v-if="pendingDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel">
        <h3 class="text-lg font-bold text-istaht-navy">Confirmer l'action</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ deleteMessage }}</p>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="ui-button ui-button-ghost" :disabled="isDeleting" @click="closeDelete">
            Annuler
          </button>
          <button
            type="button"
            class="ui-button"
            :class="pendingDelete.can_delete_physical ? 'ui-button-danger' : 'bg-istaht-amber text-white hover:bg-orange-600'"
            :disabled="isDeleting"
            @click="confirmDelete"
          >
            {{ isDeleting ? 'Traitement...' : confirmDeleteLabel }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="pendingCancel"
      class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel">
        <h3 class="text-lg font-bold text-istaht-navy">Annuler le marche</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          Indiquez le motif d'annulation du marche {{ pendingCancel.reference }}. Le motif sera conserve dans l'historique.
        </p>

        <div class="mt-4">
          <label class="block text-sm font-bold text-slate-700">Motif annulation *</label>
          <textarea
            v-model="cancelForm.raison"
            rows="4"
            class="ui-input mt-1 w-full"
            placeholder="Minimum 20 caracteres..."
          />
          <p class="mt-1 text-xs text-slate-500">Minimum 20 caracteres.</p>
          <p v-if="cancelForm.errors.raison" class="mt-1 text-sm font-semibold text-istaht-red">
            {{ cancelForm.errors.raison }}
          </p>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="ui-button ui-button-ghost" :disabled="isCancelling" @click="closeCancel">
            Retour
          </button>
          <button
            type="button"
            class="ui-button ui-button-danger"
            :disabled="isCancelling || cancelForm.raison.length < 20"
            @click="confirmCancel"
          >
            {{ isCancelling ? 'Annulation...' : 'Confirmer annulation' }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
