<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermission } from '@/Utils/permission';
import { DocumentArrowDownIcon, DocumentTextIcon, FunnelIcon, PencilIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
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
  router.get(route('menus.index'), filters.value);
}

function deleteMenu() {
  router.delete(route('menus.destroy', menuToDelete.value), {
    onFinish: () => (menuToDelete.value = null),
  });
  menuToDelete.value = null;
}

const showDeleteModal = ref(false);
const menuToDelete = ref(null);
function openDeleteModal(id) {
  menuToDelete.value = id;
  showDeleteModal.value = true;
}
</script>

<template>
  <AuthenticatedLayout>

    <Head title="Menu Collectivité" />

    <!-- 5.  Header text -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex-1">
          <h1 class="text-3xl md:text-4xl font-bold mb-2">Menu Collectivité</h1>
          <p class="text-blue-100 text-lg opacity-90">
            Liste et gestion des menus de type Collectivité
          </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
          <!-- 6.  Creation link -->
          <Link v-if="can('create_menus')" :href="route('menus.create')"
            class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl">
            <PlusIcon class="h-5 w-5" />
            Nouveau Menu
          </Link>

          <ModalLink
              v-if="can('export_menus')"
              as="button"
              :href="route('menus.createExport')"
              class="bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-400 flex items-center justify-center gap-3 transition-all duration-200 font-semibold border border-blue-400"
          >
              <DocumentArrowDownIcon class="h-5 w-5" />
              Exporter
          </ModalLink>
        </div>
      </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border ">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
          <input v-model="filters.search" type="text" placeholder="Rechercher "
            class="w-full border border-gray-300 rounded-lg p-2">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
          <input v-model="filters.date" type="date" class="w-full border border-gray-300 rounded-lg p-2">
        </div>
      </div>
      <div class="flex justify-end mt-4">
        <button @click="resetFilters"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 mr-2">
          Réinitialiser
        </button>
        <button @click="applyFilters"
          class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2 transition-all duration-200">
          <FunnelIcon class="h-4 w-4" />
          Appliquer
        </button>
      </div>
    </div>


    <!-- 8.  Table – adapt headers and cells to the new columns -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden mt-4">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Responsable</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effectif</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créé le</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="menu in menus.data" :key="menu.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ menu.id }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ new Date(menu.date).toLocaleDateString('fr-FR') }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ menu.responsable }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ menu.effectif }}</td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ new Date(menu.created_at).toLocaleDateString('fr-FR') }}
              </td>
              <td class="px-6 py-4 text-sm font-medium">
                <div class="flex space-x-2">
                  <!-- 9.  Action buttons – update routes & permissions -->
                  <!-- <ModalLink 
                             :href="route('menus.show', menu.id)"
                             class="text-blue-600 hover:text-blue-900 p-1"
                             title="Voir détails">
                    <EyeIcon class="h-5 w-5" />
                  </ModalLink>
-->
                  <Link :href="route('menus.edit', menu.id)" class="text-blue-600 hover:text-blue-900 p-1"
                    v-if="can('edit_menus')"
                    title="Modifier">
                    <PencilIcon class="h-5 w-5" />
                  </Link>

                  <a
                     :href="route('menus.download', menu.id)"
                     v-if="can('pdf_menus')"
                     target="_blank"
                     class="text-purple-600 hover:text-purple-900 p-1"
                     title="Télécharger PDF">
                    <DocumentTextIcon class="h-5 w-5" />
                  </a>

                  <!-- <button @click="openDeleteModal(menu.id)" class="text-red-600 hover:text-red-900 p-1"
                    title="Supprimer">
                    <TrashIcon class="h-5 w-5" />
                  </button> -->
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 10.  Empty state -->
      <div v-if="menus.data.length === 0" class="text-center py-12">
        <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-500" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun menu trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier menu collectivité.</p>
        <div class="mt-6">
          <Link :href="route('menus.create')"
            v-if="can('create_menus')"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <PlusIcon class="h-4 w-4 mr-2" />
            Nouveau menu
          </Link>
        </div>
      </div>

      <!-- 11.  Pagination – only the prop name changed -->
      <div v-if="menus.links && menus.links.length > 1" class="bg-white px-6 py-3 border-t border-gray-200">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-700">
            Affichage de {{ menus.from }} à {{ menus.to }} sur {{ menus.total }} résultats
          </div>
          <div class="flex space-x-2">
            <template v-for="link in menus.links" :key="link.label">
              <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 rounded-lg text-sm font-medium',
                link.active ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                v-html="link.label" />
              <span v-else class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                v-html="link.label" />
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- 12.  Confirmation modal – only the local state name changed -->
    <ConfirmationModal :show="showDeleteModal" title="Supprimer le menu"
      message="Êtes-vous sûr de vouloir supprimer ce menu ?" :onConfirm="deleteMenu" @close="showDeleteModal = false" />
  </AuthenticatedLayout>
</template>