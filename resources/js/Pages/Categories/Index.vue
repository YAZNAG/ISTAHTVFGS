<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CreateCategorieModal from './CreateCategorieModal.vue'
import { usePermission } from '@/Utils/permission'

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const { can } = usePermission()

const search = ref(props.filters?.search || '')
const selectedStatus = ref(props.filters?.status || 'all')
const pendingDelete = ref(null)
const isDeleting = ref(false)

const filteredCategories = computed(() => {
  const term = search.value.trim().toLowerCase()

  return props.categories.filter((categorie) => {
    const matchesSearch = !term
      || [categorie.code, categorie.nom]
        .filter(Boolean)
        .some(value => String(value).toLowerCase().includes(term))

    const matchesStatus = selectedStatus.value === 'all'
      || (selectedStatus.value === 'active' && categorie.est_actif)
      || (selectedStatus.value === 'inactive' && !categorie.est_actif)

    return matchesSearch && matchesStatus
  })
})

const summary = computed(() => [
  { label: 'Categories', value: props.stats.total || 0, className: 'text-istaht-navy' },
  { label: 'Actives', value: props.stats.actives || 0, className: 'text-istaht-green' },
  { label: 'Inactives', value: props.stats.inactives || 0, className: 'text-istaht-red' },
  { label: 'Articles lies', value: props.stats.articles || 0, className: 'text-istaht-blue' },
])

const deleteMessage = computed(() => {
  if (!pendingDelete.value) return ''

  if (Number(pendingDelete.value.articles_count || 0) > 0) {
    return 'Cette categorie contient des articles. Elle ne peut pas etre supprimee. Voulez-vous la desactiver ?'
  }

  return 'Cette categorie ne contient aucun article. Confirmez-vous la suppression definitive ?'
})

const confirmLabel = computed(() => Number(pendingDelete.value?.articles_count || 0) > 0 ? 'Desactiver' : 'Supprimer')

function formatNumber(value) {
  return Number(value || 0).toLocaleString('fr-FR')
}

function resetFilters() {
  search.value = ''
  selectedStatus.value = 'all'
}

function askDelete(categorie) {
  pendingDelete.value = categorie
}

function closeDelete() {
  if (isDeleting.value) return
  pendingDelete.value = null
}

function confirmDelete() {
  if (!pendingDelete.value) return

  isDeleting.value = true
  router.delete(route('categories.destroy', pendingDelete.value.id), {
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
    <Head title="Categories" />

    <section class="space-y-5">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-xs font-bold uppercase text-istaht-cyan">Referentiel articles</p>
            <h2 class="mt-1 text-2xl font-bold text-istaht-navy">Categories</h2>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">
              Classement simple des articles par code, nom, couleur et statut.
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <a :href="route('categories.export.pdf')" class="ui-button ui-button-ghost" target="_blank" rel="noopener">
              Export PDF
            </a>
            <a :href="route('categories.export.excel')" class="ui-button ui-button-secondary" target="_blank" rel="noopener">
              Export Excel
            </a>
            <ModalLink
              v-if="can('create_categories')"
              href="#create-categorie-modal"
              class="ui-button ui-button-primary"
            >
              Nouvelle categorie
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
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_220px_auto]">
          <input
            v-model="search"
            class="ui-input"
            type="search"
            placeholder="Rechercher par code ou nom..."
          />

          <select v-model="selectedStatus" class="ui-input">
            <option value="all">Toutes</option>
            <option value="active">Actives</option>
            <option value="inactive">Inactives</option>
          </select>

          <button class="ui-button ui-button-ghost whitespace-nowrap" type="button" @click="resetFilters">
            Reinitialiser
          </button>
        </div>
      </div>

      <div>
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
          <div>
            <h3 class="text-base font-bold text-istaht-navy">Liste des categories</h3>
            <p class="text-sm text-slate-500">{{ filteredCategories.length }} categorie(s) affichee(s)</p>
          </div>
        </div>

        <div v-if="filteredCategories.length" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article
            v-for="categorie in filteredCategories"
            :key="categorie.id"
            class="animate-fade-up rounded-lg border border-slate-200 bg-white p-5 shadow-soft transition duration-200 hover:-translate-y-0.5 hover:border-istaht-cyan/40 hover:shadow-panel"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold uppercase text-istaht-navy">
                    {{ categorie.code }}
                  </span>
                  <span
                    class="ui-badge ring-1"
                    :class="categorie.est_actif ? 'bg-green-50 text-istaht-green ring-green-100' : 'bg-red-50 text-istaht-red ring-red-100'"
                  >
                    {{ categorie.est_actif ? 'Actif' : 'Inactif' }}
                  </span>
                </div>
                <h4 class="mt-3 truncate text-lg font-bold text-istaht-navy">{{ categorie.nom }}</h4>
              </div>

              <span
                class="h-11 w-11 shrink-0 rounded-lg border border-slate-200"
                :style="{ backgroundColor: categorie.couleur || '#155e9f' }"
                :title="categorie.couleur || '#155e9f'"
              ></span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
              <div class="rounded-lg bg-slate-50 px-3 py-2">
                <p class="text-xs font-bold uppercase text-slate-400">Articles</p>
                <p class="mt-1 text-xl font-bold text-istaht-blue">{{ formatNumber(categorie.articles_count) }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 px-3 py-2">
                <p class="text-xs font-bold uppercase text-slate-400">Couleur</p>
                <p class="mt-1 text-sm font-bold text-slate-700">{{ categorie.couleur || '#155e9f' }}</p>
              </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
              <Link :href="route('categories.show', categorie.id)" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                Voir detail
              </Link>
              <ModalLink
                v-if="can('edit_categories')"
                :href="route('categories.edit', categorie.id)"
                class="ui-button ui-button-ghost px-3 py-1.5 text-xs"
              >
                Modifier
              </ModalLink>
              <button
                v-if="can('delete_categories')"
                type="button"
                class="ui-button ui-button-danger px-3 py-1.5 text-xs"
                @click="askDelete(categorie)"
              >
                Supprimer
              </button>
            </div>
          </article>
        </div>

        <div v-else class="rounded-lg border border-dashed border-slate-200 bg-white px-5 py-12 text-center shadow-soft">
          <h3 class="text-base font-bold text-istaht-navy">Aucune categorie trouvee</h3>
          <p class="mt-1 text-sm text-slate-500">Ajustez la recherche ou le filtre de statut.</p>
        </div>
      </div>
    </section>

    <div
      v-if="pendingDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel transition">
        <h3 class="text-lg font-bold text-istaht-navy">Confirmer l'action</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ deleteMessage }}</p>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="ui-button ui-button-ghost" :disabled="isDeleting" @click="closeDelete">
            Annuler
          </button>
          <button
            type="button"
            class="ui-button"
            :class="Number(pendingDelete.articles_count || 0) > 0 ? 'bg-istaht-amber text-white hover:bg-orange-600' : 'ui-button-danger'"
            :disabled="isDeleting"
            @click="confirmDelete"
          >
            {{ isDeleting ? 'Traitement...' : confirmLabel }}
          </button>
        </div>
      </div>
    </div>

    <CreateCategorieModal />
  </AuthenticatedLayout>
</template>
