<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  EyeIcon,
  PencilIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  CubeIcon,
  ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import { usePermission } from '@/Utils/permission';

const {can, canAny} = usePermission();

const props = defineProps({
  articles: Object,        // paginated list
  filters: Object,
});

/* ---------- reactive filter object ---------- */
const filters = ref({
  search: props.filters?.search || '',
});

/* ---------- reset / apply ---------- */
function resetFilters() {
  filters.value = { search: '' };
  router.get(route('articles.index'));
}
function applyFilters() {
  router.get(route('articles.index'), filters.value);
}

/* ---------- little helpers ---------- */
function getStatutColor(st) {
  switch (st) {
    case true:   return 'bg-green-100 text-green-700';
    case false: return 'bg-gray-100 text-gray-700';
    default:        return 'bg-yellow-100 text-yellow-700';
  }
}
function getStatutLabel(st) {
  switch (st) {
    case true:   return 'Actif';
    case false: return 'Inactif';
    default:        return 'En attente';
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Articles" />


    <div class="space-y-6">
      <!-- ====== HEADER ====== -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex-1">
            <h1 class="text-3xl font-bold mb-2">Articles</h1>
            <p class="text-indigo-100 text-lg opacity-90">Gérez et suivez tous vos articles</p>
          </div>
          <ModalLink
            v-if="can('create_articles')"
            :href="route('articles.create')"
            class="bg-white text-indigo-600 px-6 py-3 rounded-xl hover:bg-indigo-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl"
          >
            <PlusIcon class="h-5 w-5" />
            Nouvel article
          </ModalLink>
        </div>
      </div>

      <!-- ====== FILTERS ====== -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtrer les articles</h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="Référence ou désignation..."
                class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
              <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
            </div>
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
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all flex items-center gap-2"
          >
            <MagnifyingGlassIcon class="w-4 h-4" />
            Rechercher
          </button>
        </div>
      </div>

      <!-- ====== TABLE ====== -->
      <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Désignation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unité</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seuil min</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seuil max</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ article.reference }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ article.designation }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ article.categorie?.nom || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ article.unite_mesure }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ article.seuil_minimal }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ article.seuil_maximal }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getStatutColor(article.est_actif)
                    ]"
                  >
                    {{ getStatutLabel(article.est_actif) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2" v-if="canAny(['show_articles', 'edit_articles'])">
                    <Link
                      v-if="can('show_articles')"
                      :href="route('articles.show', article.id)"
                      class="text-blue-600 hover:text-blue-900 p-1"
                      title="Voir détails"
                    >
                      <EyeIcon class="h-5 w-5" />
                    </Link>
                    <ModalLink
                      v-if="can('edit_articles')"
                      :href="route('articles.edit', article.id)"
                      class="text-green-600 hover:text-green-900 p-1"
                      title="Modifier"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </ModalLink>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty -->
        <div v-if="articles.data.length === 0" class="text-center py-12">
          <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun article trouvé</h3>
          <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier article.</p>
          <div class="mt-6">
            <ModalLink
              :href="route('articles.create')"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              Nouvel article
            </ModalLink>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="articles.links && articles.links.length > 1" class="bg-white px-6 py-3 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Affichage de {{ articles.from }} à {{ articles.to }} sur {{ articles.total }} résultats
            </div>
            <div class="flex space-x-2">
              <template v-for="link in articles.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-1 rounded-lg text-sm font-medium',
                    link.active 
                      ? 'bg-indigo-600 text-white' 
                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  ]"
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
  </AuthenticatedLayout>
</template>