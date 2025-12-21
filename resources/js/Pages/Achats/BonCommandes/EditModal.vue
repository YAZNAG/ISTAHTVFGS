<template>
  <Modal ref="editMarcheModal" max-width="4xl">
    <!-- Header -->
    <div class="mb-2">
      <h2 class="text-lg font-semibold text-gray-900">Modifier le Marché</h2>
      <p class="text-sm text-gray-500 mt-1">
        Modifiez les informations du marché sélectionné.
      </p>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitMarcheForm" class="p-6 space-y-6" enctype="multipart/form-data">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Colonne gauche -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Référence *</label>
            <input
              v-model="marcheForm.reference"
              type="text"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
              placeholder="MR-2024-001"
            />
            <InputError :message="marcheForm.errors.reference" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Objet *</label>
            <input
              v-model="marcheForm.objet"
              type="text"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
              placeholder="Objet de la commande"
            />
            <InputError :message="marcheForm.errors.objet" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              v-model="marcheForm.description"
              rows="3"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
              placeholder="Description détaillée de la commande..."
            ></textarea>
            <InputError :message="marcheForm.errors.description" />
          </div>
        </div>

        <!-- Colonne droite -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Date début</label>
            <input
              v-model="marcheForm.date_debut"
              type="date"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            />
            <InputError :message="marcheForm.errors.date_debut" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Date fin</label>
            <input
              v-model="marcheForm.date_fin"
              type="date"
              required
              :min="marcheForm.date_debut"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            />
            <InputError :message="marcheForm.errors.date_fin" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Date mise en ligne *</label>
            <input
              v-model="marcheForm.date_mise_ligne"
              type="date"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            />
            <InputError :message="marcheForm.errors.date_mise_ligne" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Date limite réception devis *</label>
            <input
              v-model="marcheForm.date_limite_reception"
              type="date"
              required
              :min="marcheForm.date_mise_ligne"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            />
            <InputError :message="marcheForm.errors.date_limite_reception" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Catégorie *</label>
            <select
              v-model="marcheForm.categorie_id"
              required
              @change="onCategorieChange"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            >
              <option value="">Sélectionnez...</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.nom }}
              </option>
            </select>
            <InputError :message="marcheForm.errors.categorie_id" />
          </div>
        </div>
      </div>

      <!-- Articles -->
      <div class="border-t pt-6">
        <div class="flex justify-between items-center mb-4">
          <h4 class="text-lg font-medium">Articles commandés</h4>
          <div class="text-sm text-gray-500">Les prix seront saisis lors de la mise à jour du statut</div>
          <button
            type="button"
            @click="addRow"
            :disabled="!marcheForm.categorie_id"
            class="bg-green-600 text-white px-3 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            + Article manuel
          </button>
        </div>

        <div
          v-for="(row, index) in marcheForm.articles"
          :key="index"
          class="relative mb-4 border p-4 rounded-lg bg-gray-50 flex gap-4"
        >
          <!-- Article Search -->
          <div class="relative">
            <label class="block text-sm font-medium mb-1">Article</label>
            <input
              v-model="row.search"
              type="text"
              placeholder="Rechercher un article..."
              @focus="row.dropdownOpen = true"
              @blur="closeIdle(index)"
              class="w-full border-gray-300 rounded-lg p-2"
            />
            <ul
              v-if="row.dropdownOpen && filteredArticles(row.search).length"
              class="absolute w-full z-10 bg-white border border-gray-300 rounded-md mt-1 shadow-lg max-h-[200px] overflow-y-auto"
            >
              <li
                v-for="article in filteredArticles(row.search)"
                :key="article.id"
                @mousedown.prevent="onArticleSelect(index, article)"
                class="px-3 py-2 hover:bg-indigo-200 cursor-pointer"
              >
                {{ article.designation }}
              </li>
            </ul>
          </div>

          <!-- Quantité -->
          <div class="w-32">
            <label class="block text-sm font-medium mb-1">
              Qté max {{ row.unite_mesure ? `(${row.unite_mesure})` : '' }}
            </label>
            <input
              v-model.number="row.quantite_commandee"
              type="number"
              min="1"
              class="w-full border-gray-300 rounded-lg p-2"
            />
          </div>

          <!-- TVA -->
          <div class="w-24">
            <label class="block text-sm font-medium text-gray-700">TVA</label>
            <select
              v-model="row.taux_tva"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
            >
              <option v-for="taux in tauxTVA" :key="taux" :value="taux">
                {{ getTvaLabel(taux) }}
              </option>
            </select>
          </div>

          <button
            type="button"
            class="text-white bg-red-500 text-sm px-3 py-2 rounded self-end ml-auto"
            @click="removeRow(index)"
          >
            Supprimer
          </button>
        </div>

        <div v-if="marcheForm.articles.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
          <p class="text-gray-500 mt-2">Aucun article sélectionné</p>
          <p class="text-gray-400 text-sm mt-1">Ajoutez des articles pour compléter le marché</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3 pt-6 border-t">
        <button
          type="button"
          @click="editMarcheModal.close()"
          class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Annuler
        </button>
        <button
          type="submit"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          :disabled="marcheForm.processing || marcheForm.articles.length === 0"
        >
          <span v-if="marcheForm.processing">Enregistrement...</span>
          <span v-else>Enregistrer les modifications</span>
        </button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
/* ------------------------------------------
   Imports
------------------------------------------- */
import { ref, computed, onMounted } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

/* ------------------------------------------
   Props & Emits
------------------------------------------- */
const props = defineProps({
  marche: { type: Object, required: true },               // <-- record to edit
  tauxTVA: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  articles: { type: Array, default: () => [] },
})


/* ------------------------------------------
   Modal ref
------------------------------------------- */
const editMarcheModal = ref(null)

/* ------------------------------------------
   Form (inertia)
------------------------------------------- */
const marcheForm = useForm({
    reference: props.marche.reference,
    objet: props.marche.objet,
    description: props.marche.description,
    date_debut: props.marche.date_debut,
    date_fin: props.marche.date_fin,
    date_mise_ligne: props.marche.date_mise_ligne,
    date_limite_reception: props.marche.date_limite_reception,
    categorie_id: props.marche.categorie_id,
    articles: props.marche.articles.map(a => ({
      article_id: a.id,
      designation: a.designation,
      unite_mesure: a.unite_mesure,
      quantite_commandee: a.quantite_commandee,
      taux_tva: Number(a.taux_tva),
      search: a.designation,
      dropdownOpen: false,
    })),
})


/* ------------------------------------------
   Same helpers as create component
------------------------------------------- */
function addRow() {
  marcheForm.articles.push({
    article_id: null,
    designation: '',
    unite_mesure: '',
    quantite_commandee: 1,
    taux_tva: 20,
    search: '',
    dropdownOpen: false,
  })
}

function removeRow(idx) {
  marcheForm.articles.splice(idx, 1)
}

function onCategorieChange() {
  marcheForm.articles = []
}

function filteredArticles(search) {
  const s = search.toLowerCase()
  const picked = new Set(marcheForm.articles.map(r => r.article_id).filter(Boolean))
  return props.articles.filter(
    a =>
      a.designation.toLowerCase().startsWith(s) &&
      a.marche_category_id == marcheForm.categorie_id &&
      !picked.has(a.id)
  )
}

function onArticleSelect(index, article) {
  const row = marcheForm.articles[index]
  row.article_id = article.id
  row.designation = article.designation
  row.unite_mesure = article.unite_mesure
  row.search = article.designation
  row.dropdownOpen = false
}

function closeIdle(index) {
  setTimeout(() => (marcheForm.articles[index].dropdownOpen = false), 150)
}

function getTvaLabel(taux) {
  return taux === 0 ? 'Exonéré' : `${taux} %`
}

/* ------------------------------------------
   Submit
------------------------------------------- */
function submitMarcheForm() {
  marcheForm
    .transform(data => ({
      ...data,
      articles: data.articles.map(a => ({
        article_id: a.article_id,
        quantite_commandee: a.quantite_commandee,
        taux_tva: a.taux_tva,
      })),
    }))
    .put(route('bon-commandes.modify', props.marche.id), {
      preserveScroll: true,
      onSuccess: () => {
        editMarcheModal.value.close()
      },
    })
}
</script>

<style scoped>
/* Tailwind already handles styling */
</style>