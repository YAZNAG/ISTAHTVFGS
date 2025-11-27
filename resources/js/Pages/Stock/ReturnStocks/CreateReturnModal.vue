<script setup>
import { ref, computed, watch } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  users: Array,
  articles: Array,
})

const createReturnModal = ref(null)
const search = ref('')
const dropdownOpen = ref(false)

const form = useForm({
  date: new Date().toISOString().substr(0, 10),
  returner_id: '',
  motif: '',
  articles: [], // { article_id, designation, unite_mesure, quantite }
})

/* ---------- article picker helpers ---------- */
const filteredArticles = computed(() =>
  props.articles
    .filter(a => !form.articles.find(i => i.article_id === a.id))
    .filter(a => !search.value || a.designation.toLowerCase().includes(search.value.toLowerCase()))
)

function selectArticle(art) {
  form.articles.push({
    article_id: art.id,
    designation: art.designation,
    unite_mesure: art.unite_mesure,
    quantite: 1
  })
  search.value = ''
  dropdownOpen.value = false
}

function removeItem(idx) {
  form.articles.splice(idx, 1)
}

function closeIdle() { setTimeout(() => dropdownOpen.value = false, 200) }

/* ---------- submit ---------- */
function submit() {
  form
    .transform(data => ({
      ...data,
      articles: data.articles.map(i => ({ article_id: i.article_id, quantite: i.quantite }))
    }))
    .post(route('returns.store'), {
      onSuccess: () => {
        form.reset()
        createReturnModal.value.close()
      }
    })
}
</script>

<template>
  <Modal ref="createReturnModal">
    <!-- ======  Header  ====== -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold text-gray-800">Nouveau Retour d’Articles</h2>
      <p class="text-sm text-gray-500">Renseignez les informations du retour</p>
    </div>

    <!-- ======  Body  ====== -->
    <form @submit.prevent="submit" class="space-y-4">
      <!-- Date -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date du retour</label>
        <input v-model="form.date" type="date" required
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
        <InputError :message="form.errors.date" />
      </div>

      <!-- Returner -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Retourné par</label>
        <select v-model="form.returner_id" required
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
          <option disabled value="">Sélectionnez un utilisateur</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
        <InputError :message="form.errors.returner_id" />
      </div>

      <!-- Motif -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
        <textarea v-model="form.motif" required
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        <InputError :message="form.errors.motif" />
      </div>

      <!-- Articles picker (identical to restaurant) -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Articles à retourner</label>

        <div class="relative mb-2">
          <input type="text" v-model="search" placeholder="Rechercher un article..." @focus="dropdownOpen = true"
            @blur="closeIdle"
            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">

          <!-- dropdown -->
          <ul v-if="dropdownOpen && filteredArticles.length"
            class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-[200px] overflow-y-auto">
            <li v-for="article in filteredArticles" :key="article.id" @click="selectArticle(article)"
              class="px-3 py-2 hover:bg-indigo-200 cursor-pointer">
              {{ article.designation }}
            </li>
          </ul>
        </div>

        <!-- selected table -->
        <table class="w-full border border-gray-200 text-sm rounded-lg overflow-hidden">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="p-2 text-left">Article</th>
              <th class="p-2 text-center w-32">Quantité</th>
              <th class="p-2 w-14"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in form.articles" :key="item.article_id" class="border-t">
              <td class="p-2">{{ item.designation }}</td>
              <td class="p-2 text-center">
                <div class="flex items-center gap-2 justify-center">
                  <input v-model.number="item.quantite" type="number" min="1"
                    class="w-20 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                  <span class="text-xs text-slate-700">{{ item.unite_mesure }}</span>
                </div>
              </td>
              <td class="p-2 text-center">
                <button type="button" @click="removeItem(idx)"
                  class="text-red-700 hover:text-white bg-red-200 hover:bg-red-600 p-1 rounded">
                  <XMarkIcon class="h-4 w-4" />
                </button>
              </td>
            </tr>
            <tr v-if="!form.articles.length">
              <td colspan="3" class="text-center text-gray-400 p-3">Aucun article ajouté</td>
            </tr>
          </tbody>
        </table>
      </div>
    </form>

    <!-- ======  Footer  ====== -->
    <div class="flex justify-end space-x-3 pt-4">
      <button type="button" @click="createReturnModal.close()"
        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
        Annuler
      </button>
      <button type="button" @click="submit" :disabled="form.processing"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
        Enregistrer
      </button>
    </div>
  </Modal>
</template>