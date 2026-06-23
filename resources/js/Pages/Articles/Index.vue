<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { usePermission } from '@/Utils/permission'

const { can } = usePermission()

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
  search: props.filters?.search || '',
  categorie_id: props.filters?.categorie_id || '',
  status: props.filters?.status || '',
  stock: props.filters?.stock || '',
})

const showMobileFilters = ref(false)
const pendingDelete = ref(null)
const isDeleting = ref(false)

const activeFilterCount = computed(() => Object.values(filters.value).filter(Boolean).length)

const exportParams = computed(() => Object.fromEntries(
  Object.entries(filters.value).filter(([, value]) => value !== '' && value !== null && value !== undefined)
))

const summary = computed(() => [
  { label: 'Articles', value: props.stats.total || props.articles.total || 0, className: 'text-istaht-navy' },
  { label: 'Actifs', value: props.stats.active || 0, className: 'text-istaht-green' },
  { label: 'Stock faible', value: props.stats.lowStock || 0, className: 'text-istaht-amber' },
  { label: 'Rupture', value: props.stats.rupture || 0, className: 'text-istaht-red' },
])

const deleteMessage = computed(() => {
  if (!pendingDelete.value) return ''

  if (pendingDelete.value.is_used) {
    return 'Cet article est deja utilise dans des marches ou mouvements de stock. Il ne peut pas etre supprime definitivement. Voulez-vous le desactiver ?'
  }

  return 'Cet article ne contient aucune relation. Confirmez-vous la suppression definitive ?'
})

const confirmLabel = computed(() => pendingDelete.value?.is_used ? 'Desactiver article' : 'Supprimer definitivement')

let filterTimer = null
watch(filters, value => {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    router.get(route('articles.index'), value, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 280)
}, { deep: true })

function resetFilters() {
  filters.value = {
    search: '',
    categorie_id: '',
    status: '',
    stock: '',
  }
}

function formatNumber(value, fraction = 0) {
  return Number(value || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: fraction,
    maximumFractionDigits: fraction,
  })
}

function statusClasses(active) {
  return active
    ? 'bg-green-50 text-istaht-green ring-green-100'
    : 'bg-red-50 text-istaht-red ring-red-100'
}

function stockClasses(status) {
  if (status?.type === 'danger') return 'bg-red-50 text-istaht-red ring-red-100'
  if (status?.type === 'warning') return 'bg-amber-50 text-istaht-amber ring-amber-100'
  return 'bg-green-50 text-istaht-green ring-green-100'
}

function askDelete(article) {
  pendingDelete.value = article
}

function closeDelete() {
  if (isDeleting.value) return
  pendingDelete.value = null
}

function confirmDelete() {
  if (!pendingDelete.value) return

  isDeleting.value = true
  router.delete(route('articles.destroy', pendingDelete.value.id), {
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
    <Head title="Articles" />

    <section class="space-y-5">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-xs font-bold uppercase text-istaht-cyan">Referentiel articles</p>
            <h2 class="mt-1 text-2xl font-bold text-istaht-navy">Articles</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">
              Recherche, filtres, stock et actions administratives sur les articles alimentaires.
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link :href="route('articles.export.pdf', exportParams)" class="ui-button ui-button-ghost">
              Export PDF
            </Link>
            <Link :href="route('articles.export.excel', exportParams)" class="ui-button ui-button-secondary">
              Export Excel
            </Link>
            <ModalLink
              v-if="can('create_articles')"
              :href="route('articles.create')"
              class="ui-button ui-button-primary"
            >
              Ajouter article
            </ModalLink>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
          <div
            v-for="item in summary"
            :key="item.label"
            class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3"
          >
            <p class="text-xs font-bold uppercase text-slate-400">{{ item.label }}</p>
            <p class="mt-1 text-2xl font-bold" :class="item.className">{{ formatNumber(item.value) }}</p>
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

        <div :class="['mt-3 grid grid-cols-1 gap-3 md:mt-0 md:grid-cols-[1.4fr_1fr_180px_180px_auto]', showMobileFilters ? 'grid' : 'hidden md:grid']">
          <input
            v-model="filters.search"
            class="ui-input"
            type="search"
            placeholder="Reference ou designation..."
          />

          <select v-model="filters.categorie_id" class="ui-input">
            <option value="">Toutes les categories</option>
            <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
              {{ categorie.code || '-' }} - {{ categorie.nom }}
            </option>
          </select>

          <select v-model="filters.status" class="ui-input">
            <option value="">Tous statuts</option>
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
          </select>

          <select v-model="filters.stock" class="ui-input">
            <option value="">Tous les stocks</option>
            <option value="normal">Stock normal</option>
            <option value="faible">Stock faible</option>
            <option value="rupture">Rupture</option>
          </select>

          <button class="ui-button ui-button-ghost whitespace-nowrap" type="button" @click="resetFilters">
            Reinitialiser
          </button>
        </div>

        <div v-if="activeFilterCount" class="mt-3 text-sm font-semibold text-slate-500">
          {{ activeFilterCount }} filtre(s) actif(s)
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-base font-bold text-istaht-navy">Liste des articles</h3>
            <p class="text-sm text-slate-500">{{ articles.total || 0 }} article(s)</p>
          </div>
        </div>

        <div v-if="articles.data.length" class="hidden overflow-x-auto md:block">
          <table>
            <thead>
              <tr>
                <th>Reference</th>
                <th>Designation</th>
                <th>Categorie</th>
                <th>Unite</th>
                <th class="text-right">Stock actuel</th>
                <th class="text-right">Seuil minimal</th>
                <th>Statut</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="article in articles.data" :key="article.id" class="animate-fade-up hover:bg-slate-50/80">
                <td class="font-bold text-istaht-blue">{{ article.reference || '-' }}</td>
                <td>
                  <p class="font-semibold text-slate-800">{{ article.designation }}</p>
                  <p class="mt-1 text-xs text-slate-400">TVA {{ formatNumber(article.taux_tva, 2) }}%</p>
                </td>
                <td>
                  <span class="ui-badge bg-blue-50 text-istaht-blue ring-1 ring-blue-100">
                    {{ article.categorie?.nom || 'Non classe' }}
                  </span>
                </td>
                <td class="font-semibold text-slate-700">{{ article.unite_mesure || '-' }}</td>
                <td class="text-right font-bold text-istaht-navy">{{ formatNumber(article.quantite_stock, 2) }}</td>
                <td class="text-right text-slate-600">{{ formatNumber(article.seuil_minimal, 2) }}</td>
                <td>
                  <div class="flex flex-wrap gap-2">
                    <span :class="['ui-badge ring-1', statusClasses(article.est_actif)]">
                      {{ article.est_actif ? 'Actif' : 'Inactif' }}
                    </span>
                    <span :class="['ui-badge ring-1', stockClasses(article.stock_status)]">
                      {{ article.stock_status.label }}
                    </span>
                  </div>
                </td>
                <td>
                  <div class="flex justify-end gap-2">
                    <Link
                      v-if="can('show_articles')"
                      :href="route('articles.show', article.id)"
                      class="ui-button ui-button-secondary px-3 py-1.5 text-xs"
                    >
                      Voir detail
                    </Link>
                    <ModalLink
                      v-if="can('edit_articles')"
                      :href="route('articles.edit', article.id)"
                      class="ui-button ui-button-ghost px-3 py-1.5 text-xs"
                    >
                      Modifier
                    </ModalLink>
                    <button
                      v-if="can('edit_articles')"
                      type="button"
                      class="ui-button ui-button-danger px-3 py-1.5 text-xs"
                      @click="askDelete(article)"
                    >
                      Supprimer
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="articles.data.length" class="grid grid-cols-1 gap-3 p-4 md:hidden">
          <article
            v-for="article in articles.data"
            :key="article.id"
            class="animate-fade-up rounded-lg border border-slate-200 bg-white p-4 shadow-soft"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xs font-bold uppercase text-istaht-cyan">{{ article.reference || '-' }}</p>
                <h4 class="mt-1 text-base font-bold text-istaht-navy">{{ article.designation }}</h4>
                <p class="mt-1 text-sm text-slate-500">{{ article.categorie?.nom || 'Non classe' }}</p>
              </div>
              <span :class="['ui-badge ring-1', statusClasses(article.est_actif)]">
                {{ article.est_actif ? 'Actif' : 'Inactif' }}
              </span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-400">Stock</p>
                <p class="mt-1 font-bold text-istaht-navy">{{ formatNumber(article.quantite_stock, 2) }} {{ article.unite_mesure }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-400">Seuil min.</p>
                <p class="mt-1 font-bold text-slate-700">{{ formatNumber(article.seuil_minimal, 2) }}</p>
              </div>
            </div>

            <div class="mt-3">
              <span :class="['ui-badge ring-1', stockClasses(article.stock_status)]">
                {{ article.stock_status.label }}
              </span>
            </div>

            <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-3">
              <Link v-if="can('show_articles')" :href="route('articles.show', article.id)" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                Voir
              </Link>
              <ModalLink v-if="can('edit_articles')" :href="route('articles.edit', article.id)" class="ui-button ui-button-ghost px-3 py-1.5 text-xs">
                Modifier
              </ModalLink>
              <button v-if="can('edit_articles')" type="button" class="ui-button ui-button-danger px-3 py-1.5 text-xs" @click="askDelete(article)">
                Supprimer
              </button>
            </div>
          </article>
        </div>

        <div v-if="!articles.data.length" class="px-5 py-12 text-center">
          <h3 class="text-base font-bold text-istaht-navy">Aucun article trouve</h3>
          <p class="mt-1 text-sm text-slate-500">Ajustez la recherche ou les filtres.</p>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
          <Pagination
            :links="articles.links || []"
            :from="articles.from || 0"
            :to="articles.to || 0"
            :total="articles.total || 0"
          />
        </div>
      </div>
    </section>

    <div
      v-if="pendingDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel transition">
        <h3 class="text-lg font-bold text-istaht-navy">Confirmer la suppression</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ deleteMessage }}</p>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="ui-button ui-button-ghost" :disabled="isDeleting" @click="closeDelete">
            Annuler
          </button>
          <button
            type="button"
            class="ui-button"
            :class="pendingDelete.is_used ? 'bg-istaht-amber text-white hover:bg-orange-600' : 'ui-button-danger'"
            :disabled="isDeleting"
            @click="confirmDelete"
          >
            {{ isDeleting ? 'Traitement...' : confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
