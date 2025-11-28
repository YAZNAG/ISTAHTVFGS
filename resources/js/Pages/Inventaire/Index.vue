<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { MagnifyingGlassIcon, InboxIcon, PencilIcon, UnderlineIcon, LockOpenIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import { Link, router, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import CreateInventaireModal from './CreateInventaireModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
  inventaires: Object,        // paginated collection
  filters: Object,
});

const showUnlockModal = ref(false)
const inventaireIdToUnlock = ref(null)

function openUnlockModal(id) {
  inventaireIdToUnlock.value = id;
  showUnlockModal.value = true
}
/* ---------- filters ---------- */
const filters = ref({
  mois: props.filters.mois ?? '',
  statut: props.filters.statut ?? '',   // draft | finalized | ''
});

function applyFilters() {
  router.get(route('inventaires.index'), filters.value, { preserveState: true });
}
function resetFilters() {
  filters.value = { mois: '', statut: '' };
  router.get(route('inventaires.index'));
}

const unlock  = () => {
  router.patch(route('inventaires.unlock', inventaireIdToUnlock.value), {
    preserveScroll: true,
    onSuccess: () => {
      showUnlockModal.value = false
      inventaireIdToUnlock.value = null
    }
  })
}

/* ---------- helpers ---------- */
const statutBadge = (s) =>
  s === 'finalized'
    ? 'bg-green-100 text-green-800'
    : 'bg-amber-100 text-amber-800';
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Inventaires mensuels" />

    <div class="space-y-6">
      <!-- ====== HEADER ====== -->
      <div
        class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg"
      >
        <div
          class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6"
        >
          <div class="flex-1">
            <h1 class="text-3xl font-bold mb-2">Inventaires mensuels</h1>
            <p class="text-blue-100 text-lg opacity-90">
              Créez, continuez ou téléchargez vos inventaires
            </p>
          </div>

          <ModalLink
            href="#createInventaireModal"
            v-if="can('create_inventaire')"
            as="button"
            class="bg-white text-blue-700 px-6 py-3 rounded-xl hover:bg-blue-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow"
          >
            <span class="text-xl">＋</span>
            Nouvel inventaire
          </ModalLink>
        </div>
      </div>

      <!-- ====== FILTERS ====== -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtrer les inventaires</h3>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
          <!-- Mois -->
          <div class="">
            <label class="block text-sm font-medium text-gray-700 mb-2">Mois</label>
            <input
              v-model="filters.mois"
              type="month"
              placeholder="2025-12"
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>

          <!-- Statut -->
          <div class="">
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select
              v-model="filters.statut"
              class="w-full border border-gray-300 rounded-lg p-2"
            >
              <option value="">Tous</option>
              <option value="draft">Brouillon</option>
              <option value="finalized">Finalisé</option>
            </select>
        </div>

          <!-- Actions -->
          <div class="md:col-span-2 flex items-end gap-3">
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
              Appliquer
            </button>
          </div>
        </div>
      </div>

      <!-- ====== TABLE ====== -->
      <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Mois
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Statut
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Articles
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Créé le
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Progression
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Actions
                </th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="inv in inventaires.data"
                :key="inv.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                  {{ inv.mois }}
                </td>

                <td class="px-6 py-4">
                  <span
                    :class="statutBadge(inv.statut)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ inv.statut === 'finalized' ? 'Finalisé' : 'Brouillon' }}
                  </span>
                </td>

                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ inv.articles_count }}
                </td>

                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ inv.created_at }}
                </td>

                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ inv.progress ?? '—' }}
                </td>

                <td class="px-6 py-4 text-sm font-medium flex items-center gap-3">
                  <Link
                    v-if="inv.statut === 'draft' && can('fill_stock_reel')"
                    :href="route('inventaires.edit', inv.id)"
                    class="text-green-600 hover:text-green-900 p-1"
                    title="Voir / continuer"
                  >
                    <PencilIcon class="h-5 w-5" />
                  </Link>

                  <a
                    v-if="inv.statut === 'finalized' && can('pdf_inventaire')"
                    :href="route('inventaires.pdf', inv.id)"
                    class="text-purple-600 hover:text-purple-900"
                    title="Télécharger le PDF"
                    target="_blank"
                  >
                    <DocumentTextIcon class="h-5 w-5" />
                  </a>

                  <button
                    v-if="inv.statut === 'finalized' && can('unlock_inventaire')"
                    @click="openUnlockModal(inv.id)"
                    class="text-amber-600 hover:text-amber-900"
                    title="Déverrouiller"
                  >
                    <LockOpenIcon class="h-5 w-5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="inventaires.data.length === 0" class="text-center py-12">
          <InboxIcon class="mx-auto h-12 w-12 text-gray-300" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun inventaire</h3>
          <p class="mt-1 text-sm text-gray-500">
            Créez le premier inventaire mensuel.
          </p>
          <div class="mt-4">
            <ModalLink
              href="#createInventaireModal"
              v-if="can('create_inventaire')"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Nouvel inventaire
            </ModalLink>
          </div>
        </div>

        <!-- PAGINATION (same markup you already use) -->
        <div
          v-if="inventaires.links.length > 1"
          class="bg-white px-6 py-3 border-t border-gray-200"
        >
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Affichage de {{ inventaires.from }} à {{ inventaires.to }} sur
              {{ inventaires.total }} résultats
            </div>
            <div class="flex space-x-2">
              <template v-for="link in inventaires.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-1 rounded-lg text-sm font-medium',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed',
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <CreateInventaireModal />
    <ConfirmationModal
      :show="showUnlockModal"
      type="warning"
      title="Déverrouiller l’inventaire"
      message="Êtes-vous sûr de vouloir déverrouiller cet inventaire ?"
      :onConfirm="unlock"
      @close="showUnlockModal = false"
    />
  </AuthenticatedLayout>
</template>