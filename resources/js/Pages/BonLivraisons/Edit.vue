<script setup>
import { ref, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import Dump from '@/Components/Dump.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  bonLivraison: Object, // existing bon livraison with items + articles
  articles: Array,
  users: Array
})

const search = ref('')
const dropdownOpen = ref(false)
const editBonLivraisonModal = ref(null)

// Initialize form with existing data
const form = useForm({
  date_livraison: props.bonLivraison.date_livraison || new Date().toISOString().split('T')[0],
  user_id: '',
  items: props.bonLivraison.items.map(item => ({
    id: item.id,
    article_id: item.article_id,
    designation: item.designation,
    quantite: item.quantite,
    prix_unitaire: item.prix_unitaire,
    taux_tva: item.taux_tva,
  })),
  notes: props.bonLivraison.notes || '',
})

// Filter available articles to add
const filteredArticles = computed(() => {
  return props.articles
    .filter(a => !form.items.find(fi => fi.article_id === a.id))
    .filter(a => !search.value || a.designation.toLowerCase().includes(search.value.toLowerCase()))
})

// Add new article
function selectArticle(article) {
  form.items.push({
    article_id: article.id,
    designation: article.designation,
    quantite: 1,
    prix_unitaire: article.prix_unitaire || 0,
    taux_tva: article.taux_tva || 0,
  })
  search.value = ''
  dropdownOpen.value = false
}

function removeItem(index) {
  form.items.splice(index, 1)
}

function closeIdle() {
  setTimeout(() => dropdownOpen.value = false, 300)
}

// Totals
const total_ht = computed(() =>
  form.items.reduce((sum, i) => sum + i.prix_unitaire * i.quantite, 0).toFixed(2)
)

const total_tva = computed(() =>
  form.items.reduce((sum, i) => sum + (i.prix_unitaire * i.quantite * (i.taux_tva / 100)), 0).toFixed(2)
)

const total_ttc = computed(() =>
  form.items.reduce((sum, i) => sum + i.prix_unitaire * i.quantite * (1 + i.taux_tva / 100), 0).toFixed(2)
)

// Submit form
function submit() {
  form.put(route('bon-livraisons.update', props.bonLivraison.id), {
    preserveScroll: true,
    onSuccess: () => editBonLivraisonModal.value.close(),
  })
}
</script>

<template>
  <Modal ref="editBonLivraisonModal">
    <!-- Header -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold">
        Modifier Bon de Livraison #{{ bonLivraison.numero }}
      </h2>
      <!-- <p class="text-gray-500 text-sm">
        Livraison prévue le {{ bonLivraison.date_livraison }}
      </p> -->
    </div>

    <!-- Body -->
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Date Livraison</label>
        <input v-model="form.date_livraison" type="date" required
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        <InputError :message="form.errors.date_livraison" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Responsable
        </label>
        <select
          v-model="form.user_id"
          class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option value="">Choisissez un responsable </option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
        <InputError :message="form.errors.user_id" class="mt-2" />
      </div>

      <!-- Article Selector -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Articles Livrés</label>

        <div class="relative mb-2">
          <input type="text" v-model="search" placeholder="Rechercher un article..." @focus="dropdownOpen = true"
            @blur="closeIdle"
            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />
            <InputError :message="form.errors.items" />

          <!-- Dropdown -->
          <ul v-if="dropdownOpen && filteredArticles.length"
            class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg"
            style="max-height: 200px; overflow-y: auto;">
            <li v-for="article in filteredArticles" :key="article.id" @click="selectArticle(article)"
              class="px-3 py-2 hover:bg-indigo-100 cursor-pointer">
              {{ article.designation }}
            </li>
          </ul>
        </div>

        <!-- Items Table -->
        <table class="w-full border border-gray-200 text-sm rounded-lg overflow-hidden">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="p-2 text-left">Article</th>
              <th class="p-2 text-center w-24">Quantité</th>
              <th class="p-2 text-center w-28">Prix Unitaire</th>
              <th class="p-2 text-center w-20">TVA (%)</th>
              <th class="p-2 text-right w-32">Total TTC</th>
              <th class="p-2 w-8"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in form.items" :key="index" class="border-t hover:bg-gray-50">
              <td class="p-2">{{ item.designation }}</td>
              <td class="p-2 text-center">
                <input type="number" min="1" v-model.number="item.quantite"
                  class="w-20 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" />
              </td>
              <td class="p-2 text-center">
                <div class="w-24 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                  {{ item.prix_unitaire }}
                </div>
              </td>
              <td class="p-2 text-center">
                <div class="w-24 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                  {{ item.taux_tva }}
                </div>
              </td>
              <td class="p-2 text-right font-medium">
                {{ (item.quantite * item.prix_unitaire * (1 + item.taux_tva / 100)).toFixed(2) }}
              </td>
              <td class="p-2 text-center">
                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                  ✕
                </button>
              </td>
            </tr>

            <tr v-if="form.items.length === 0">
              <td colspan="6" class="text-center text-gray-400 p-3">
                Aucun article ajouté
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Totals -->
      <div class="flex justify-end space-x-6 text-sm text-gray-700 pt-2">
        <div>Total HT: <span class="font-semibold">{{ total_ht }} DH</span></div>
        <div>TVA: <span class="font-semibold">{{ total_tva }} DH</span></div>
        <div>Total TTC: <span class="font-semibold">{{ total_ttc }} DH</span></div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Notes <span class="text-xs text-gray-400">(Optionnel)</span>
        </label>
        <textarea v-model="form.notes"
          class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" rows="2"></textarea>
      </div>
    </form>

    <!-- Footer -->
    <div class="flex justify-end space-x-3 pt-4">
      <button type="button" @click="editBonLivraisonModal.close()"
        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
        Annuler
      </button>
      <button type="button" @click="submit" :disabled="form.processing"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
        Mettre à jour
      </button>
    </div>
  </Modal>
</template>
