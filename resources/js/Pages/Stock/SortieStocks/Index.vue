<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  MagnifyingGlassIcon,
  ArrowPathIcon,
  InboxIcon,
  DocumentArrowDownIcon,
  ArrowUpTrayIcon,
} from '@heroicons/vue/24/outline'
import { Link, router, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';

const props = defineProps({
  sorties: Object,
  filters: Object,
  categories: { type: Array, default: () => [] },
});

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const filters = ref({
  search: props.filters.search || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  categorie_id: props.filters.categorie_id || '',
})

function resetFilters() {
  filters.value = { search: '', start_date: '', end_date: '', categorie_id: '' }
  router.get(route('sortie-stocks.index'))
}

function applyFilters() {
  router.get(route('sortie-stocks.index'), filters.value, { preserveState: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Sorties Stock" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <ArrowUpTrayIcon class="h-6 w-6" />
              Sorties stock
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Suivi des sorties d'articles du stock issues des bons de sortie.
            </p>
          </div>
          <ModalLink
            as="button"
            :href="route('sortie-stocks.export.create')"
            class="ui-button ui-button-primary"
          >
            <DocumentArrowDownIcon class="mr-1.5 h-4 w-4" />
            Exporter la fiche
          </ModalLink>
        </div>
      </div>

      <!-- ═══ Filtres ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
          <div class="md:col-span-2">
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Référence ou désignation..."
                class="ui-input w-full pl-9"
                @keyup.enter="applyFilters"
              />
              <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Catégorie</label>
            <select v-model="filters.categorie_id" class="ui-input w-full">
              <option value="">Toutes les catégories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date début</label>
            <input v-model="filters.start_date" type="date" class="ui-input w-full" />
          </div>

          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date fin</label>
            <input v-model="filters.end_date" type="date" class="ui-input w-full" />
          </div>
        </div>

        <div class="mt-4 flex flex-col justify-end gap-2 sm:flex-row">
          <button type="button" class="ui-button ui-button-ghost" @click="resetFilters">
            <ArrowPathIcon class="mr-1.5 h-4 w-4" />
            Réinitialiser
          </button>
          <button type="button" class="ui-button ui-button-primary" @click="applyFilters">
            <MagnifyingGlassIcon class="mr-1.5 h-4 w-4" />
            Rechercher
          </button>
        </div>
      </div>

      <!-- ═══ Tableau ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <ArrowUpTrayIcon class="h-5 w-5 text-istaht-red" />
            <h3 class="font-bold text-istaht-navy">Historique des sorties</h3>
          </div>
          <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-istaht-red ring-1 ring-red-100">
            {{ sorties?.meta?.total ?? 0 }} sortie(s)
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date sortie</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Réf. article</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Désignation</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Unité</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Stock initial</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Qté sortie</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Réf. bon sortie</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Stock actuel</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="sortie in sorties.data" :key="`${sortie.code_article}-${sortie.date_sortie}-${sortie.reference_bon_sortie}`" class="transition hover:bg-slate-50">
                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                  {{ formatDate(sortie.date_sortie) }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">
                  {{ sortie.code_article || '—' }}
                </td>
                <td class="px-5 py-3.5 text-sm font-semibold text-slate-700">
                  <div class="max-w-xs truncate" :title="sortie.designation_article">
                    {{ sortie.designation_article || '—' }}
                  </div>
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                  {{ sortie.unite_mesure || '—' }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm text-slate-600">
                  {{ sortie.stock_initial }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-right">
                  <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-istaht-red ring-1 ring-red-100">
                    −{{ sortie.quantite_sortie }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm text-slate-600">
                  {{ sortie.reference_bon_sortie || '—' }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-right">
                  <span class="rounded-md bg-blue-50 px-2.5 py-1 text-sm font-bold text-istaht-navy ring-1 ring-blue-100">
                    {{ sortie.stock_actuel }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Vide -->
        <div v-if="sorties.data.length === 0" class="py-14 text-center">
          <InboxIcon class="mx-auto h-12 w-12 text-slate-300" />
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucune sortie enregistrée</h3>
          <p class="mt-1 text-sm text-slate-500">Les sorties de stock apparaissent automatiquement après validation des bons de sortie.</p>
        </div>

        <!-- Pagination -->
        <div
          v-if="sorties?.meta?.links && sorties.meta.last_page > 1"
          class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row"
        >
          <p class="text-sm text-slate-500">
            Affichage de <strong class="text-istaht-navy">{{ sorties.meta.from }}</strong>
            à <strong class="text-istaht-navy">{{ sorties.meta.to }}</strong>
            sur <strong class="text-istaht-navy">{{ sorties.meta.total }}</strong> sorties
          </p>
          <div class="flex flex-wrap gap-1">
            <template v-for="link in sorties.meta.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                :class="[
                  'rounded-md px-3 py-1.5 text-sm font-semibold transition',
                  link.active ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100'
                ]"
                v-html="link.label"
              />
              <span
                v-else
                class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm font-semibold text-slate-300"
                v-html="link.label"
              />
            </template>
          </div>
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
