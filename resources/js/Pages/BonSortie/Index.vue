<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  EyeIcon,
  ClipboardDocumentListIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  QuestionMarkCircleIcon,
  XCircleIcon,
  CheckCircleIcon,
  ClockIcon,
  CheckBadgeIcon,
} from '@heroicons/vue/24/outline'
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { getSortieStatutInfo, getSortieTypeInfo } from '@/Utils/labels';

const props = defineProps({
  sorties: Object,
  filters: Object,
  stats: Object
})

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const filters = ref({
  search: props.filters.search || '',
  statut: props.filters.statut || '',
  date_debut: props.filters.date_debut || '',
  date_fin: props.filters.date_fin || '',
})

function resetFilters() {
  filters.value = { search: '', statut: '', date_debut: '', date_fin: '' }
  router.get(route('bon-sorties.index'))
}

function applyFilters() {
  router.get(route('bon-sorties.index'), filters.value)
}

const showDelivredModal = ref(false)
const sortieIdToDelivred = ref(null)

</script>

<template>
  <AuthenticatedLayout>
    <Head title="Bons de Sortie" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex-1">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">Bons de Sortie</h1>
            <p class="text-blue-100 text-lg opacity-90">Gérez et suivez tous vos bons de sortie</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtrer les bons de sortie</h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="N° ou motif..."
                class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
            </div>
          </div>

          <!-- <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select
              v-model="filters.statut"
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Tous</option>
              <option value="cree">Créé</option>
              <option value="attente_validation">En attente de validation</option>
              <option value="attente_livraison">En attente de livraison</option>
              <option value="livree">Livré</option>
              <option value="annulee">Annulé</option>
            </select>
          </div> -->

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
            <input
              v-model="filters.date_debut"
              type="date"
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
            <input
              v-model="filters.date_fin"
              type="date"
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <div class="mt-5 flex flex-col sm:flex-row justify-end gap-3">
          <button
            @click="resetFilters"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all"
          >
            Réinitialiser
          </button>

          <button
            @click="applyFilters"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center gap-2"
          >
            <MagnifyingGlassIcon class="w-4 h-4" />
            Rechercher
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th> -->
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Demandeur</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créé par</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="sortie in sorties.data" :key="sortie.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ sortie.numero }}</td>
                <!-- <td class="px-6 py-4 text-sm text-gray-600">
                    <span :class="`px-2 py-1 rounded text-xs font-medium ${getSortieTypeInfo(sortie.type).color}`">
                        {{ getSortieTypeInfo(sortie.type).label }}
                    </span>
                </td> -->
                <td class="px-6 py-4 text-sm text-gray-600">{{ sortie.demandeur }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ sortie.date }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ sortie.notes || '-' }}</td>
                <td class="px-6 py-4">
                    <span :class="`px-2 py-1 rounded text-xs font-medium ${getSortieStatutInfo(sortie.statut).color}`">
                        {{ getSortieStatutInfo(sortie.statut).label }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ sortie.created_by }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <ModalLink
                        :href="route('bon-sorties.show', sortie.id)"
                      class="text-blue-600 hover:text-blue-900 p-1"
                      title="Voir les détails"
                    >
                      <EyeIcon class="h-5 w-5" />
                    </ModalLink>
                  
                    <a
                      :href="route('bon-sorties.download-pdf', sortie.id)"
                      target="_blank"
                      class="p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-100 rounded-xl transition-all duration-200 group/tooltip relative"
                      title="Télécharger le Bon de Sortie"
                    >
                      <DocumentArrowDownIcon class="h-5 w-5" />
                      <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                          Télécharger le Bon de Sortie
                      </div>
                    </a>
                    
                    <!-- <button
                      @click="openDelivredModal(sortie.id)"
                      class="text-green-600 hover:text-green-900 p-1"
                      title="Livrer le bon"
                      v-if="sortie.statut === 'attente_livraison'"
                    >
                      <CheckBadgeIcon class="h-5 w-5" />
                    </button> -->

                    <Link
                      :href="route('bon-sorties.showApprove', sortie.id)"
                      class="text-orange-600 hover:text-orange-900 p-1"
                      title="Approuver le bon"
                      v-if="sortie.statut === 'attente_validation'"
                    >
                      <QuestionMarkCircleIcon class="h-5 w-5" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="sorties.data.length === 0" class="text-center py-12">
          <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun bon de sortie trouvé</h3>
          <p class="mt-1 text-sm text-gray-500">Créez votre premier bon de sortie.</p>
        </div>

        <!-- Pagination -->
        <div v-if="sorties.links && sorties.links.length > 1" class="bg-white px-6 py-3 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Affichage de {{ sorties.from }} à {{ sorties.to }} sur {{ sorties.total }} résultats
            </div>
            <div class="flex space-x-2">
              <template v-for="link in sorties.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[ link.active ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200', 'px-3 py-1 rounded-lg text-sm font-medium' ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmationModal
      type="info"
      :show="showDelivredModal"
      title="Livrer le bon de sortie"
      message="Êtes-vous sûr de vouloir livrer ce bon de sortie ?"
      :onConfirm="delivredSortie"
      @close="showDelivredModal = false"
    />
  </AuthenticatedLayout>
</template>
