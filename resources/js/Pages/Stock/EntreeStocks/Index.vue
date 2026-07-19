<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  MagnifyingGlassIcon,
  ArrowPathIcon,
  InboxIcon,
  DocumentArrowDownIcon,
  ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'
import { Link, router, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';

const props = defineProps({
  entrees: Object,
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
  router.get(route('entree-stocks.index'))
}

function applyFilters() {
  router.get(route('entree-stocks.index'), filters.value, { preserveState: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Entrées Stock" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <ArrowDownTrayIcon class="h-6 w-6" />
              Entrées stock
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Suivi des entrées d'articles en stock issues des bons de réception.
            </p>
          </div>
          <ModalLink
            as="button"
            :href="route('entree-stocks.export.create')"
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
                placeholder="Référence, désignation ou n° BR..."
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
            <ArrowDownTrayIcon class="h-5 w-5 text-istaht-green" />
            <h3 class="font-bold text-istaht-navy">Historique des entrées</h3>
          </div>
          <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-istaht-green ring-1 ring-green-100">
            {{ entrees?.meta?.total ?? 0 }} entrée(s)
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date entrée</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Réf. article</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Désignation</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Unité</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Stock initial</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Qté entrée</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Réf. BR</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Stock actuel</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="entree in entrees.data" :key="entree.id" class="transition hover:bg-slate-50">
                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                  {{ formatDate(entree.date_entree) }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">
                  {{ entree.code_article || '—' }}
                </td>
                <td class="px-5 py-3.5 text-sm font-semibold text-slate-700">
                  <div class="max-w-xs truncate" :title="entree.designation_article">
                    {{ entree.designation_article || '—' }}
                  </div>
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                  {{ entree.unite_mesure || '—' }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm text-slate-600">
                  {{ entree.stock_initial }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-right">
                  <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-istaht-green ring-1 ring-green-100">
                    +{{ entree.quantite_entree }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm text-slate-600">
                  {{ entree.reference_bon_reception || '—' }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-right">
                  <span class="rounded-md bg-blue-50 px-2.5 py-1 text-sm font-bold text-istaht-navy ring-1 ring-blue-100">
                    {{ entree.stock_actuel }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Vide -->
        <div v-if="entrees.data.length === 0" class="py-14 text-center">
          <InboxIcon class="mx-auto h-12 w-12 text-slate-300" />
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucune entrée enregistrée</h3>
          <p class="mt-1 text-sm text-slate-500">Les entrées en stock apparaissent automatiquement après réception des livraisons.</p>
        </div>

        <!-- Pagination -->
        <div
          v-if="entrees?.meta?.links && entrees.meta.last_page > 1"
          class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row"
        >
          <p class="text-sm text-slate-500">
            Affichage de <strong class="text-istaht-navy">{{ entrees.meta.from }}</strong>
            à <strong class="text-istaht-navy">{{ entrees.meta.to }}</strong>
            sur <strong class="text-istaht-navy">{{ entrees.meta.total }}</strong> entrées
          </p>
          <div class="flex flex-wrap gap-1">
            <template v-for="link in entrees.meta.links" :key="link.label">
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
