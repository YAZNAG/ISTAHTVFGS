<template>
  <AuthenticatedLayout>
    <Head title="Gestion des Bons de Réception" />

    <div class="space-y-6">
      <!-- En-tête avec statistiques -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex-1">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">Bons de Réception</h1>
            <p class="text-blue-100 text-lg opacity-90">Gestion complète des réceptions de marchandises</p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <ModalLink
              v-if="can('create_bonReceptions')"
              :href="route('bon-receptions.create')"
              class="bg-white text-indigo-600 px-6 py-3 rounded-xl hover:bg-indigo-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl"
            >
              <PlusIcon class="h-5 w-5" />
              Nouveau Bon de Réception
            </ModalLink>
          </div>
        </div>
      </div>

      <!-- Filtres et Recherche -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <FunnelIcon class="h-5 w-5 text-blue-600" />
            Filtres et Recherche
          </h3>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
              <div class="relative">
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="N° Bon de Réception ..."
                  class="w-full border border-gray-300 rounded-xl p-3 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                />
                <MagnifyingGlassIcon class="absolute left-3 top-3.5 h-5 w-5 text-gray-400" />
              </div>
            </div>
          </div>

          <div class="flex justify-between items-center mt-6">
            <div class="text-sm text-gray-500">
              {{ bonReceptions?.total || 0 }} Bon(s) trouvé(s)
            </div>
            <div class="flex gap-3">
              <button
                @click="resetFilters"
                class="px-6 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition-all duration-200"
              >
                <ArrowPathIcon class="h-4 w-4" />
                Réinitialiser
              </button>
              <button
                @click="applyFilters"
                class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2 transition-all duration-200"
              >
                <FunnelIcon class="h-4 w-4" />
                Appliquer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Historique des Bons de Réception -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
              <ClipboardDocumentListIcon class="h-6 w-6 text-blue-600" />
              Historique des Bons de Réception
            </h3>
            <div class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border">
              {{ bonReceptions?.total || 0 }} résultat(s)
            </div>
          </div>
        </div>

        <!-- Tableau -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">N°</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">Fournisseur</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">Date Réception</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">Articles</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">Total HT</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">Total TTC</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-100/50">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="bon in bonReceptions.data"
                :key="bon.id"
                class="hover:bg-blue-50/30 transition-all duration-200"
              >
                <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ bon.numero }}</td>
                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">{{ bon.fournisseur }}</td>
                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">{{ bon.date_livraison }}</td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                    <CubeIcon class="h-4 w-4 mr-1" />
                    {{ bon.items_count }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">{{ bon.total_ht || 0 }} DH</td>
                <td class="px-6 py-4 text-center text-sm text-green-600 font-semibold">{{ bon.total_ttc || 0 }} DH</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex justify-center items-center gap-1">
                    <button
                          v-if="can('destroy_bonReceptions')"
                          @click="openDeleteModal(bon.id)"
                          class="text-red-600 hover:text-red-800"
                          title="supprimer le bon"
                      >
                          <TrashIcon class="h-5 w-5" />
                          <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                              Supprimer
                          </div>
                      </button>
                    
                    <Link
                      v-if="can('show_bonReceptions')"
                      :href="route('bon-receptions.show', bon.id)"
                      class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-xl transition-all duration-200 group/tooltip relative"
                      title="Voir détails du bon"
                    >
                      <EyeIcon class="h-5 w-5" />
                      <div
                        class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap"
                      >
                        Voir détails
                      </div>
                    </Link>

                    <a
                      v-if="can('pdf_bonReceptions')"
                      :href="route('bon-receptions.pdf', bon.id)"
                      class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-xl transition-all duration-200 group/tooltip relative"
                      title="Télécharger PDF"
                      target="_blank"
                    >
                      <DocumentArrowDownIcon class="h-5 w-5" />
                      <div
                        class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap"
                      >
                        Télécharger PDF
                      </div>
                    </a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- État vide -->
        <div
          v-if="!bonReceptions?.data || bonReceptions.data.length === 0"
          class="text-center py-16"
        >
          <ClipboardDocumentListIcon class="mx-auto h-24 w-24 text-gray-300 mb-4" />
          <h3 class="text-xl font-medium text-gray-900 mb-2">Aucun bon de réception</h3>
          <p class="text-gray-500 max-w-md mx-auto mb-6">
            {{
              filters.search || filters.statut
                ? 'Aucun résultat ne correspond à vos critères de recherche.'
                : 'Créez votre premier bon de réception pour enregistrer les marchandises reçues.'
            }}
          </p>
          
        </div>

        <!-- Pagination -->
        <div
          v-if="bonReceptions?.links && bonReceptions.links.length > 3"
          class="bg-white px-6 py-4 border-t border-gray-200"
        >
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-700">
              Affichage de {{ bonReceptions.from }} à {{ bonReceptions.to }} sur {{ bonReceptions.total }} bon(s)
            </div>
            <div class="flex items-center gap-1">
              <template v-for="link in bonReceptions.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border',
                    link.active
                      ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                      : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 border border-gray-300 cursor-not-allowed'
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmationModal
      :show="showDeleteModal"
      title="Êtes-vous sûr(e) de vouloir supprimer ce bon de réception ?"
      message="Cette action est irréversible et entraînera la perte de toutes les informations associées à ce bon"
      :onConfirm="deleteReception"
      @close="showDeleteModal = false"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
  PlusIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  FunnelIcon,
  ArrowPathIcon,
  ClipboardDocumentListIcon,
  CubeIcon,
  XMarkIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import { usePermission } from '@/Utils/permission'

const { can } = usePermission();

const props = defineProps({
  bonReceptions: Object,
  filters: Object,
})

const showDeleteModal = ref(false);
const receptionIdToCancel = ref(false);

const filters = ref({
  search: props.filters?.search || '',
})

const applyFilters = () => {
  router.get(route('bon-receptions.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

const resetFilters = () => {
  filters.value = { search: '' }
  router.get(route('bon-receptions.index'), filters.value, {
    preserveState: true,
    replace: true,
  })
}

function openDeleteModal(id) {
  receptionIdToCancel.value = id
  showDeleteModal.value = true
}

const deleteReception = () => {
  router.delete(route('bon-receptions.destroy', receptionIdToCancel.value), {
    onSuccess: () => (showDeleteModal.value = false),
  })
}
</script>
