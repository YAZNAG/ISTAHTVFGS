<script setup>
import { ref, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  chefCommande: Object, // existing chefCommande with items + articles
  articles: Array,
  categories: Array,
  users: Array
})

const search = ref('')
const dropdownOpen = ref(false)
const editCommandeModal = ref(null)

// Initialize form with existing data
const form = useForm({
  user_id: props.chefCommande.user_id,
  categorie_id: props.chefCommande.categorie_id,
  articles: props.chefCommande.items.map(item => ({
    article_id: item.article.id,
    designation: item.article.designation,
    quantite_commandee: item.quantite,
    unite_mesure: item.article.unite_mesure
  })),
  note: props.chefCommande.note || ''
})

const articles = computed(() => {
  return props.articles.filter(a => a.marche_category_id === form.categorie_id)
})


// Filter available articles
const filteredArticles = computed(() => {
  return articles.value
    .filter(a => !form.articles.find(fa => fa.article_id === a.id))
    .filter(a => !search.value || a.designation.toLowerCase().includes(search.value.toLowerCase()))
})

// Select a new article to add
function selectArticle(article) {
  form.articles.push({
    article_id: article.id,
    designation: article.designation,
    quantite_commandee: 1,
    unite_mesure: article.unite_mesure
  })
  search.value = ''
  dropdownOpen.value = false
}

function removeArticle(index) {
  form.articles.splice(index, 1)
}

// Submit the update
function submit(type) {
  form.put(route('chef-commandes.update', props.chefCommande.id), {
    preserveScroll: true,
    onSuccess: () => {
      editCommandeModal.value.close()
    },
  })
}

function closeIdle() {
  setTimeout(() => dropdownOpen.value = false, 300)
}

function onCategorieChange(event)
{
  form.articles = [];
}
</script>

<template>
  <Modal ref="editCommandeModal">
    <!-- Header -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold">Modifier Commande #{{ chefCommande.numero }}</h2>
      <p class="text-gray-500 text-sm">
        Créée le {{ chefCommande.created_at }}
      </p>
    </div>

    <!-- Body -->
    <div>
      <form @submit.prevent="submit" class="space-y-4">
        <div v-if="$page.props.auth.user.role == 'ADMIN'">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Chef
          </label>
          <select
            v-model="form.user_id"
            class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Choisissez un chef </option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
          <InputError :message="form.errors.user_id" class="mt-2" />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Categorie
          </label>
          <select v-model="form.categorie_id" @change="onCategorieChange" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Selectionner une categorie</option>
            <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
              {{ categorie.nom }}
            </option>
          </select>
          <InputError :message="form.errors.categorie_id" />
        </div>
        
        <!-- Articles -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Articles Commandés
          </label>

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


            <!-- Dropdown -->
            <ul
              v-if="dropdownOpen && filteredArticles.length"
              class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg"
              style="max-height: 200px; overflow-y: auto;"
            >
              <li
                v-for="article in filteredArticles"
                :key="article.id"
                @click="selectArticle(article)"
                class="px-3 py-2 hover:bg-indigo-100 cursor-pointer"
              >
                {{ article.designation }}
              </li>
            </ul>
          </div>

          <!-- Selected Articles -->
          <table class="w-full border border-gray-200 text-sm rounded-lg overflow-hidden">
            <thead class="bg-gray-50 text-gray-700">
              <tr>
                <th class="p-2 text-left">Article</th>
                <th class="p-2 text-center w-32">Quantité</th>
                <th class="p-2 w-10"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in form.articles" :key="index" class="border-t">
                <td class="p-2">{{ item.designation }}</td>
                <td class="p-2 text-center">
                  <div class="flex items-center gap-2">

                    <input
                    type="number"
                    min="1"
                    v-model.number="item.quantite_commandee"
                    class="w-24 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <span class="text-xs text-slate-800">{{ item.unite_mesure }}</span>
                  </div>
                </td>
                <td class="p-2 text-center">
                  <button
                    type="button"
                    @click="removeArticle(index)"
                    class="text-red-500 hover:text-red-700"
                  >
                    ✕
                  </button>
                </td>
              </tr>

              <tr v-if="form.articles.length === 0">
                <td colspan="3" class="text-center text-gray-400 p-3">
                  Aucun article ajouté
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Note -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Note <span class="text-xs">(Optionnel)</span>
          </label>
          <textarea
            v-model="form.note"
            class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            rows="2"
          ></textarea>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="flex justify-end space-x-3 pt-3">
      <button
        type="button"
        @click="editCommandeModal.close()"
        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
      >
        Annuler
      </button>
      <button
        type="button"
        @click="submit"
        :disabled="form.processing"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        Mettre à jour
      </button>
    </div>
  </Modal>
</template>
