<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    EyeIcon,
    PlusIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    QuestionMarkCircleIcon,
    PencilIcon,
    TrashIcon,
    CheckIcon,
    DocumentTextIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import Dump from '@/Components/Dump.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { getDemandeStatutInfo } from '@/Utils/labels.js';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
    demandes: Object,
    filters: Object,
})

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

const filters = ref({
  search: '',
  status: '',
  start_date: '',
  end_date: '',
})

function resetFilters() {
  filters.value = { search: '', status: '', start_date: '', end_date: '' }
  router.get(route('demandes.index'))
}

function applyFilters() {
  router.get(route('demandes.index'), filters.value)
}

const showCancelModal = ref(false)
const demandeIdToCancel = ref(null)

function openCancelModal(id) {
  demandeIdToCancel.value = id
  showCancelModal.value = true
}

const showSubmitModal = ref(false)
const demandeIdToSubmit = ref(null)

function opensubmitModal(id) {
  demandeIdToSubmit.value = id
  showSubmitModal.value = true
}

function submitDemande() {
  return router.patch(route('demandes.submit', demandeIdToSubmit.value), {
    preserveScroll: true 
  });
}

function cancelDemande() {
  return router.delete(route('demandes.cancel', demandeIdToCancel.value), {
    preserveScroll: true 
  }, {
    onSuccess: () => {
      alert('Demande annulée avec succès !')
    },
    onError: (errors) => {
      alert('Impossible d’annuler la demande : ' + errors.message)
    }
  })
}

const getDemandeStatutColor = (statut) => getDemandeStatutInfo(statut).color;
const getDemandeStatutLabel = (statut) => getDemandeStatutInfo(statut).label;
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Les Demandes" />

        <div class="space-y-6">
            <!-- Header with actions -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Les Demandes</h1>
                        <p class="text-blue-100 text-lg opacity-90">Suivez et gérez toutes vos demandes</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <ModalLink
                            v-if="can('create_demandes')"
                            :href="route('demandes.create')"
                            class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl"
                        >
                            <PlusIcon class="h-5 w-5" />
                            Nouvelle Demande
                        </ModalLink>
                        <!-- <Link
                            class="bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-400 flex items-center justify-center gap-3 transition-all duration-200 font-semibold border border-blue-400"
                        >
                            <DocumentArrowDownIcon class="h-5 w-5" />
                            Exporter
                        </Link> -->
                    </div>
                </div>
            </div>

            <!-- Search Filter -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtrer les demandes</h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                        <div class="relative">
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="N° de demande ou objet..."
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select
                            v-model="filters.status"
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Tous</option>
                            <option value="en_attente_validation">En attente de validation</option>
                            <option value="validee">Validée</option>
                            <option value="rejetee">Rejetée</option>
                            <option value="annulee">Annulée</option>

                        </select>
                    </div>

                    <!-- Date début -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                        <input
                            v-model="filters.start_date"
                            type="date"
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Date fin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                        <input
                            v-model="filters.end_date"
                            type="date"
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <!-- Buttons -->
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validateur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Validation date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="demande in demandes.data" :key="demande.id" class="hover:bg-gray-50">
                                <!-- ID -->
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ demande.numero || demande.id }}
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ formatDate(demande.date_demande) }}
                                </td>

                                <!-- Statut -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        getDemandeStatutColor(demande.statut)
                                        ]"
                                    >
                                        {{ getDemandeStatutLabel(demande.statut) }}
                                    </span>
                                </td>

                                <!-- Articles -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ demande.articles_count }}
                                </td>

                                <!-- valideur -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class='px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full' >
                                        {{ demande.valide_par || 'Non validé' }}
                                    </span>
                                </td>

                                <!-- Validation date -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ demande.date_validation ? formatDate(demande.date_validation) : '------' }}
                                </td>
                                

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <ModalLink
                                            v-if="can('show_demandes')"
                                            :href="route('demandes.show', demande.id)"
                                            class="text-blue-600 hover:text-blue-900 p-1"
                                            title="Voir détails"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                        </ModalLink>

                                        <template v-if="demande.statut === 'cree'">
                                            <button
                                                
                                                @click="openCancelModal(demande.id)"
                                                class="text-red-600 hover:text-red-800"
                                                title="Annuler la demande"
                                            >
                                                <XMarkIcon class="h-5 w-5" />
                                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    Annuler
                                                </div>
                                            </button>

                                            <button
                                                @click="opensubmitModal(demande.id)"
                                                class="text-green-600 hover:text-green-800"
                                                title="Submitter la demande"
                                            >
                                                <CheckIcon class="h-5 w-5" />
                                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    Submitter
                                                </div>
                                            </button>
                                        </template>

                                        <!-- <ModalLink
                                            :href="route('demandes.edit', demande.id)"
                                            class="text-green-600 hover:text-green-900 p-1"
                                            title="Modifier"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </ModalLink> -->

                                        <!-- <a
                                            class="text-purple-600 hover:text-purple-900 p-1 cursor-pointer"
                                            :class="!demande.fiche_technique ? '!cursor-not-allowed opacity-50 pointer-events-none' : ''"
                                            :style="{ pointerEvents: demande.fiche_technique ? 'auto' : 'none' }"
                                            title="Télécharger PDF"
                                            target="_blank"
                                            :href="demande.fiche_technique"
                                        >
                                            <DocumentTextIcon class="h-5 w-5" />
                                        </a> -->

                                        <!-- Approve -->
                                        <Link
                                            v-if="can('validate_demandes') && demande.statut === 'en_attente_validation'"
                                            :href="route('demandes.show.approve', demande.id)"
                                            class="text-orange-600 hover:text-orange-900 p-1"
                                            title="Approuver la demande"
                                        >
                                            <QuestionMarkCircleIcon class="h-5 w-5" />
                                        </Link>

                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Message vide -->
                <div v-if="demandes.data.length === 0" class="text-center py-12">
                    <div class="text-gray-500">
                        <ClipboardDocumentListIcon class="mx-auto h-12 w-12" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune demande trouvée</h3>
                        <p class="mt-1 text-sm text-gray-500">Commencez par créer votre première demande.</p>
                        <div class="mt-6">
                            <ModalLink
                                :href="route('demandes.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                <PlusIcon class="h-4 w-4 mr-2" />
                                Nouvelle demande
                            </ModalLink>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="demandes.meta.links && demandes.meta.links.length > 1" class="bg-white px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Affichage de {{ demandes.from }} à {{ demandes.to }} sur {{ demandes.total }} résultats
                        </div>
                        <div class="flex space-x-2">
                            <template v-for="link in demandes.meta.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1 rounded-lg text-sm font-medium',
                                        link.active 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    :class="[
                                        'px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed'
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
            :show="showCancelModal"
            title="Annuler la demande"
            message="Êtes-vous sûr de vouloir annuler cette demande ?"
            :onConfirm="cancelDemande"
            @close="showCancelModal = false"
        />

        <ConfirmationModal
            :show="showSubmitModal"
            type="info"
            title="Submettre la demande"
            message="Êtes-vous sûr de vouloir submettre cette demande ?"
            :onConfirm="submitDemande"
            @close="showSubmitModal = false"
        />
    </AuthenticatedLayout>
</template>
