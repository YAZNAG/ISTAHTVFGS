<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { Link, router, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  articles: Array,
  categories: Array,
  filters: Object,
});


// Filters
const filters = ref({
  search: props.filters.search || '',
  categorie: props.filters.categorie || '',
})

function resetFilters() {
  filters.value = { search: '', categorie: '' }
  router.get(route('articles-stocks.index'))
}

function applyFilters() {
  router.get(route('articles-stocks.index'), filters.value)
}
</script>

<template>
  <AuthenticatedLayout>

    <Head title="Gestion du Stock" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex-1">
            <h1 class="text-3xl font-bold mb-2">Gestion du Stock</h1>
            <p class="text-blue-100 text-lg opacity-90">Consultez et gérez les niveaux de stock</p>
          </div>
          <!-- 
           <ModalLink
              as="button"
              :href="route('article-stocks.export.create')"
              class="bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-400 flex items-center justify-center gap-3 transition-all duration-200 font-semibold border border-blue-400"
          >
              <DocumentArrowDownIcon class="h-5 w-5" />
              Exporter
          </ModalLink> -->
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtrer les articles</h3>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
            <div class="relative">
              <input v-model="filters.search" type="text" placeholder="Référence ou désignation..."
                class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
              <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Categorie</label>
            <select v-model="filters.categorie" class="w-full border border-gray-300 rounded-lg p-2">
              <option value="">Toutes les catégories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
            </select>
          </div>

          <div class="mt-5 flex flex-col sm:flex-row justify-end gap-3">
            <button @click="resetFilters"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all">
              Réinitialiser
            </button>

            <button @click="applyFilters"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center gap-2">
              <MagnifyingGlassIcon class="w-4 h-4" />
              Appliquer
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Réf. Article</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categorie</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Désignation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unité</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="article in articles.data" :key="article.id" class="hover:bg-gray-50">
                <!-- Article Reference -->
                <td class="px-6 py-4 text-sm font-mono text-gray-900">
                  {{ article.reference || 'N/A' }}
                </td>

                <!-- Article Categorie -->
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="max-w-xs truncate" :title="article.designation_article">
                    {{ article.categorie?.nom || 'N/A' }}
                  </div>
                </td>

                <!-- Article Designation -->
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="max-w-xs truncate" :title="article.designation_article">
                    {{ article.designation || 'N/A' }}
                  </div>
                </td>

                <!-- Unit -->
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ article.unite_mesure || '-' }}
                </td>

                <!-- Quantite in Stock -->
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                  {{ article.quantite_stock }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="articles.data.length === 0" class="text-center py-12">
          <div class="text-gray-500">
            <InboxIcon class="mx-auto h-12 w-12" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun article trouvé</h3>
            <p class="mt-1 text-sm text-gray-500">Aucun résultat ne correspond à vos filtres. Essayez d’élargir votre recherche ou de réinitialiser les filtres.</p>

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
                <Link v-if="link.url" :href="link.url" :class="[
                  'px-3 py-1 rounded-lg text-sm font-medium',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]" v-html="link.label" />
                <span v-else :class="[
                  'px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed'
                ]" v-html="link.label" />
              </template>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>