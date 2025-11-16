<script setup>
import { ref, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  articles: Array,   
})

const search = ref('');
const createRestaurantModal = ref(null)
const dropdownOpen = ref(false)

const form = useForm({
  nom: '',
  effectif: 1,
  articles: [],
  motif: '',
})

/* ---------- article helpers ---------- */
// articles not yet selected
const filteredArticles = computed(() =>
  props.articles
    .filter(a => !form.articles.find(fa => fa.article_id === a.id))
    .filter(a => !search.value || a.designation.toLowerCase().includes(search.value.toLowerCase()))
)

function selectArticle(article) {
  form.articles.push({
    article_id: article.id,
    designation: article.designation,
    unite_mesure: article.unite_mesure,
    quantite: 1
  })
  search.value = ''
  dropdownOpen.value = false
}

function removeArticle(index) {
  form.articles.splice(index, 1)
}

/* ---------- submit ---------- */
function submit(type) {
  form.post(route('restaurants.store'), {
    onSuccess: () => {
      form.reset()
      dropdownOpen.value = false
      createRestaurantModal.value.close()
    },
  })
}

/* ---------- errors ---------- */
const articleErrors = computed(() =>
  Object.entries(form.errors)
    .filter(([key]) => key.startsWith('articles.'))
    .map(([_, message]) => message)
)

/* ---------- dropdown blur ---------- */
function closeIdle() {
  setTimeout(() => dropdownOpen.value = false, 300)
}
</script>

<template>
  <Modal ref="createRestaurantModal">
    <!-- Header -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold">Nouveau Restaurant</h2>
    </div>

    <!-- Body -->
    <div>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Nom -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom du restaurant</label>
          <input
            v-model="form.nom"
            type="text"
            class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            placeholder=""
          />
          <InputError :message="form.errors.nom" class="mt-2" />
        </div>

        <!-- Effectif -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Effectif</label>
          <input
            v-model.number="form.effectif"
            type="number"
            min="1"
            class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          />
          <InputError :message="form.errors.effectif" class="mt-2" />
        </div>

        <!-- Articles -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Articles nécessaires</label>

          <div class="relative mb-2">
            <input
              type="text"
              v-model="search"
              placeholder="Rechercher un article..."
              @focus="dropdownOpen = true"
              @blur="closeIdle"
              class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
            />

            <div v-if="form.errors.articles" class="text-red-600 text-sm mt-1">
              {{ form.errors.articles }}
            </div>

            <ul
              v-if="articleErrors.length"
              class="bg-red-300 text-red-900 border-red-500 border rounded p-2 text-sm mt-2"
            >
              <li v-for="error in articleErrors" :key="error">{{ error }}</li>
            </ul>

            <!-- Dropdown -->
            <ul
              v-if="dropdownOpen && filteredArticles.length"
              class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-[200px] overflow-y-auto"
            >
              <li
                v-for="article in filteredArticles"
                :key="article.id"
                @click="selectArticle(article)"
                class="px-3 py-2 hover:bg-indigo-200 cursor-pointer"
              >
                {{ article.designation }}
              </li>
            </ul>
          </div>

          <!-- Selected Articles Table -->
          <table class="w-full border border-gray-200 text-sm rounded-lg overflow-hidden">
            <thead class="bg-gray-50 text-gray-700">
              <tr>
                <th class="p-2 text-left">Article</th>
                <th class="p-2 text-center w-32">Quantité</th>
                <th class="p-2 w-14"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in form.articles" :key="item.article_id" class="border-t">
                <td class="p-2">{{ item.designation }}</td>
                <td class="p-2 text-center">
                  <div class="flex items-center gap-2">
                    <input
                      type="number"
                      min="1"
                      v-model.number="item.quantite"
                      class="w-24 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <span class="text-xs text-slate-800">{{ item.unite_mesure }}</span>
                  </div>
                </td>
                <td class="p-2 text-center">
                  <button
                    type="button"
                    @click="removeArticle(index)"
                    class="text-red-700 hover:text-red-700 bg-red-200 hover:bg-red-300 p-1 rounded"
                  >
                    <XMarkIcon class="h-4 w-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="form.articles.length === 0">
                <td colspan="3" class="text-center text-gray-400 p-3">Aucun article ajouté</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Note -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Note <span class="text-xs">(Optionnel)</span></label>
          <textarea
            v-model="form.motif"
            rows="2"
            class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          ></textarea>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div>
      <div class="flex justify-end space-x-3 pt-2">
        <button
          type="button"
          @click="createRestaurantModal.close()"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
        >
          Annuler
        </button>
        <button
          type="button"
          @click="submit()"
          :disabled="form.processing"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
        >
          Enregistrer
        </button>
      </div>
    </div>
  </Modal>
</template>