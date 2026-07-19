<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermission } from '@/Utils/permission';
import {
  DocumentArrowDownIcon, DocumentTextIcon, MagnifyingGlassIcon, ArrowPathIcon,
  PencilIcon, PlusIcon, ClipboardDocumentListIcon, InboxArrowDownIcon, CalendarDaysIcon,
} from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import { ModalLink } from '@inertiaui/modal-vue';
import { ref } from 'vue';

const { can } = usePermission();

const props = defineProps({
  menus: Object,
  filters: Object,
});

const filters = ref({
  date: props.filters?.date || '',
  search: props.filters?.search || '',
});

function resetFilters() {
  filters.value = { search: '', date: '' };
  router.get(route('menus.index'));
}
function applyFilters() {
  router.get(route('menus.index'), filters.value, { preserveState: true, replace: true });
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Menu Collectivité" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <CalendarDaysIcon class="h-6 w-6" />
              Menus collectivité
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Un menu par jour couvrant les 3 services (petit-déjeuner, déjeuner, dîner), chacun avec son effectif.
            </p>
          </div>
          <div class="flex flex-wrap gap-2">
            <Link v-if="can('create_menus')" :href="route('menus.create')" class="ui-button ui-button-primary">
              <PlusIcon class="mr-1.5 h-4 w-4" />
              Nouveau menu
            </Link>
            <ModalLink v-if="can('export_menus')" as="button" :href="route('menus.createExport')" class="ui-button ui-button-secondary">
              <DocumentArrowDownIcon class="mr-1.5 h-4 w-4" />
              Exporter
            </ModalLink>
          </div>
        </div>
      </div>

      <!-- ═══ Filtres ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div class="md:col-span-2">
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
            <div class="relative">
              <input v-model="filters.search" type="text" placeholder="Responsable…" class="ui-input w-full pl-9" @keyup.enter="applyFilters" />
              <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date</label>
            <input v-model="filters.date" type="date" class="ui-input w-full" />
          </div>
        </div>
        <div class="mt-4 flex flex-col justify-end gap-2 sm:flex-row">
          <button type="button" class="ui-button ui-button-ghost" @click="resetFilters">
            <ArrowPathIcon class="mr-1.5 h-4 w-4" /> Réinitialiser
          </button>
          <button type="button" class="ui-button ui-button-primary" @click="applyFilters">
            <MagnifyingGlassIcon class="mr-1.5 h-4 w-4" /> Rechercher
          </button>
        </div>
      </div>

      <!-- ═══ Tableau ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <ClipboardDocumentListIcon class="h-5 w-5 text-istaht-blue" />
            <h3 class="font-bold text-istaht-navy">Liste des menus</h3>
          </div>
          <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
            {{ menus?.total ?? 0 }} menu(s)
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">#</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Responsable</th>
                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Petit-déj</th>
                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Déjeuner</th>
                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Dîner</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="menu in menus.data" :key="menu.id" class="transition hover:bg-slate-50">
                <td class="px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">#{{ menu.id }}</td>
                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-700">{{ formatDate(menu.date) }}</td>
                <td class="px-5 py-3.5 text-sm text-slate-600">{{ menu.responsable }}</td>
                <td class="px-5 py-3.5 text-center">
                  <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-100">{{ menu.effectif_petit_dejeuner ?? menu.effectif ?? '—' }}</span>
                </td>
                <td class="px-5 py-3.5 text-center">
                  <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">{{ menu.effectif_dejeuner ?? menu.effectif ?? '—' }}</span>
                </td>
                <td class="px-5 py-3.5 text-center">
                  <span class="rounded-full bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-700 ring-1 ring-purple-100">{{ menu.effectif_diner ?? menu.effectif ?? '—' }}</span>
                </td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center justify-end gap-1">
                    <ModalLink
                      v-if="can('create_demandes')"
                      :href="route('demandes.create', { demandable_type: 'collectivite', demandable_id: menu.id })"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-green-50 hover:text-istaht-green"
                      title="Créer une demande de collectivité à partir de ce menu">
                      <InboxArrowDownIcon class="h-5 w-5" />
                    </ModalLink>
                    <Link v-if="can('edit_menus')" :href="route('menus.edit', menu.id)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue" title="Modifier">
                      <PencilIcon class="h-5 w-5" />
                    </Link>
                    <a v-if="can('pdf_menus')" :href="route('menus.download', menu.id)" target="_blank"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-purple-50 hover:text-purple-600" title="Télécharger PDF">
                      <DocumentTextIcon class="h-5 w-5" />
                    </a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="menus.data.length === 0" class="py-14 text-center">
          <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-slate-300" />
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun menu trouvé</h3>
          <p class="mt-1 text-sm text-slate-500">Commencez par créer votre premier menu collectivité.</p>
          <div class="mt-5">
            <Link v-if="can('create_menus')" :href="route('menus.create')" class="ui-button ui-button-primary">
              <PlusIcon class="mr-1.5 h-4 w-4" /> Nouveau menu
            </Link>
          </div>
        </div>

        <!-- Pagination (paginate brute : links/from/to/total au niveau racine) -->
        <div v-if="menus.links && menus.links.length > 3"
          class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row">
          <p class="text-sm text-slate-500">
            Affichage de <strong class="text-istaht-navy">{{ menus.from }}</strong>
            à <strong class="text-istaht-navy">{{ menus.to }}</strong>
            sur <strong class="text-istaht-navy">{{ menus.total }}</strong> menus
          </p>
          <div class="flex flex-wrap gap-1">
            <template v-for="link in menus.links" :key="link.label">
              <Link v-if="link.url" :href="link.url"
                :class="['rounded-md px-3 py-1.5 text-sm font-semibold transition', link.active ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100']"
                v-html="link.label" />
              <span v-else class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm font-semibold text-slate-300" v-html="link.label" />
            </template>
          </div>
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
