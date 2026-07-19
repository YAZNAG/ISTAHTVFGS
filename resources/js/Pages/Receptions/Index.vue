<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ModalLink } from '@inertiaui/modal-vue'
import {
  PlusIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  ArrowPathIcon,
  ClipboardDocumentListIcon,
  CubeIcon,
  TrashIcon,
  TruckIcon,
} from '@heroicons/vue/24/outline'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import { usePermission } from '@/Utils/permission'

const { can } = usePermission();

const props = defineProps({
  bonReceptions: Object,
  filtres: Object,
})

const filters = ref({
  search: props.filtres?.search || '',
})

const showDeleteModal = ref(false);
const receptionIdToDelete = ref(null);

function openDeleteModal(id) {
  receptionIdToDelete.value = id
  showDeleteModal.value = true
}

function deleteReception() {
  return router.delete(route('bon-receptions.destroy', receptionIdToDelete.value), {
    onSuccess: () => (showDeleteModal.value = false),
  })
}

function applyFilters() {
  router.get(route('bon-receptions.index'), filters.value, { preserveState: true, replace: true })
}

function resetFilters() {
  filters.value = { search: '' }
  router.get(route('bon-receptions.index'), filters.value, { preserveState: true, replace: true })
}

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Bons de Réception" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-istaht-navy">Bons de réception</h2>
            <p class="mt-1 text-sm text-slate-500">
              Chaque bon de réception est lié à un bon de livraison livré : il valide l'entrée en stock des articles.
            </p>
          </div>
          <ModalLink
            v-if="can('create_bonReceptions')"
            :href="route('bon-receptions.create')"
            class="ui-button ui-button-primary"
          >
            <PlusIcon class="mr-1.5 h-4 w-4" />
            Nouveau bon de réception
          </ModalLink>
        </div>
      </div>

      <!-- ═══ Filtres ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 md:flex-row md:items-end">
          <div class="flex-1">
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="N° de bon de réception..."
                class="ui-input w-full pl-9"
                @keyup.enter="applyFilters"
              />
              <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
          </div>
          <div class="flex gap-2">
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
      </div>

      <!-- ═══ Tableau ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <ClipboardDocumentListIcon class="h-5 w-5 text-istaht-blue" />
            <h3 class="font-bold text-istaht-navy">Historique des bons de réception</h3>
          </div>
          <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
            {{ bonReceptions?.meta?.total ?? 0 }} résultat(s)
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Numéro</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Bon de livraison lié</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Fournisseur</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date réception</th>
                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Articles</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="bon in bonReceptions.data" :key="bon.id" class="transition hover:bg-slate-50">
                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">
                  {{ bon.numero }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5">
                  <Link
                    :href="route('bon-livraisons.show', bon.bon_livraison_id)"
                    class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 font-mono text-xs font-bold text-istaht-green ring-1 ring-green-100 transition hover:bg-green-100"
                    title="Voir le bon de livraison"
                  >
                    <TruckIcon class="h-3.5 w-3.5" />
                    {{ bon.bon_livraison_numero }}
                  </Link>
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-700">
                  {{ bon.fournisseur }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                  {{ formatDate(bon.date_reception) }}
                </td>
                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                  <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                    <CubeIcon class="h-3.5 w-3.5" />
                    {{ bon.items_count }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-5 py-3.5">
                  <div class="flex items-center justify-end gap-1">
                    <Link
                      v-if="can('show_bonReceptions')"
                      :href="route('bon-receptions.show', bon.id)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue"
                      title="Voir détails"
                    >
                      <EyeIcon class="h-5 w-5" />
                    </Link>
                    <a
                      v-if="can('pdf_bonReceptions')"
                      :href="route('bon-receptions.pdf', bon.id)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-purple-50 hover:text-purple-600"
                      title="Télécharger PDF"
                      target="_blank"
                    >
                      <DocumentArrowDownIcon class="h-5 w-5" />
                    </a>
                    <button
                      v-if="can('destroy_bonReceptions')"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-red-50 hover:text-istaht-red"
                      title="Supprimer le bon"
                      @click="openDeleteModal(bon.id)"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Vide -->
        <div v-if="!bonReceptions?.data || bonReceptions.data.length === 0" class="py-14 text-center">
          <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-slate-300" />
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun bon de réception</h3>
          <p class="mt-1 text-sm text-slate-500">
            {{ filters.search ? 'Aucun résultat ne correspond à vos critères.' : 'Créez un bon de réception à partir d\'un bon de livraison livré.' }}
          </p>
          <div class="mt-5">
            <ModalLink
              v-if="can('create_bonReceptions')"
              :href="route('bon-receptions.create')"
              class="ui-button ui-button-primary"
            >
              <PlusIcon class="mr-1.5 h-4 w-4" />
              Nouveau bon de réception
            </ModalLink>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="bonReceptions?.meta?.links && bonReceptions.meta.last_page > 1"
          class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row"
        >
          <p class="text-sm text-slate-500">
            Affichage de <strong class="text-istaht-navy">{{ bonReceptions.meta.from }}</strong>
            à <strong class="text-istaht-navy">{{ bonReceptions.meta.to }}</strong>
            sur <strong class="text-istaht-navy">{{ bonReceptions.meta.total }}</strong> bons
          </p>
          <div class="flex flex-wrap gap-1">
            <template v-for="link in bonReceptions.meta.links" :key="link.label">
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

    <ConfirmationModal
      :show="showDeleteModal"
      title="Supprimer ce bon de réception ?"
      message="Cette action est irréversible : les quantités entrées en stock via ce bon seront retirées et les mouvements de stock associés supprimés."
      :onConfirm="deleteReception"
      @close="showDeleteModal = false"
    />
  </AuthenticatedLayout>
</template>
