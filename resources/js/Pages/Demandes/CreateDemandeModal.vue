<script setup>
import { ref, computed, watch } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import Dump from '@/Components/Dump.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  demandeurs: Array,
  fichesCollectives: Array,   // with articles
  fichesPedagogiques: Array,  // with articles
})

const createDemandeModal = ref(null)

const type = ref('')
const selectedFiche = ref(null)

const fiches = computed(() => {
  if (type.value === 'collective') return props.fichesCollectives
  if (type.value === 'pedagogique') return props.fichesPedagogiques
  return []
})

const form = useForm({
  demandeur: null,
  fiche_technique: null,
  motif: '',
  fiche_id: '',
})

const articles = ref([])
function handleFileUpload(event) {
  form.fiche_technique = event.target.files[0];
}

// when fiche changes, auto load its articles (read-only)
watch(selectedFiche, (ficheId) => {
  const fiche = fiches.value.find(f => f.id === ficheId)
  if (!fiche) {
    form.fiche_id = null
    articles.value = []
    return
  }
  form.fiche_id = fiche.id
  articles.value = fiche.articles.map(a => ({
    article_id: a.id,
    designation: a.designation,
    quantite:  a.quantite ?? 0,
    prix_unitaire: a.prix_unitaire ?? 0,
    unite_mesure: a.unite_mesure
  }))
})

function submit() {
  form.post(route('demandes.store'), {
    onSuccess: () => {
      form.reset()
      selectedFiche.value = null
      type.value = ''
      createDemandeModal.value.close()
    },
  })
}
</script>

<template>
  <Modal ref="createDemandeModal">
    <!-- Header -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold">Nouvelle Demande d’Articles</h2>
    </div>

    <!-- Body -->
    <form @submit.prevent="submit" class="space-y-4">

      <!-- Demandeur -->
      <div v-if="$page.props.auth.user.role === 'ADMIN'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Demandeur</label>
        <select v-model="form.demandeur"
            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option v-for="d in demandeurs" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>
        <p v-if="form.errors.demandeur" class="text-sm text-red-600 mt-1">{{ form.errors.demandeur }}</p>
      </div>

      <!-- Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type de Demande</label>
        <select
          v-model="type"
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option disabled value="">Sélectionnez un type</option>
          <option value="collective">Collective</option>
          <option value="pedagogique">Pédagogique</option>
        </select>
      </div>

      <!-- Fiche -->
      <div v-if="type">
        <label class="block text-sm font-medium text-gray-700 mb-1">Fiche <span class="text-xs text-gray-400">({{ type == 'collective' ? 'Module' : 'Repas' }})</span></label>
        <select
          v-model="selectedFiche"
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option disabled value="">Sélectionnez une fiche</option>
          <option v-for="fiche in fiches" :key="fiche.id" :value="fiche.id">
            {{ fiche.nom }}
          </option>
        </select>
        <InputError :message="form.errors.fiche_id" />

      </div>

      <!-- Fiche Technique -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Fiche Technique
        </label>
        <input
          type="file"
          required
          @change="handleFileUpload"
          class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <div v-if="form.errors.fiche_technique" class="text-red-600 text-sm mt-1">
          {{ form.errors.fiche_technique }}
        </div>
        <p v-if="form.fiche_technique" class="text-sm text-gray-500 mt-1">
          Fichier sélectionné : {{ form.fiche_technique.name }}
        </p>
      </div>

      <!-- Articles Table -->
      <div v-if="articles.length">
        <label class="block text-sm font-medium text-gray-700 mb-2">Articles de la fiche</label>
        <table class="w-full border border-gray-200 text-sm rounded-lg overflow-hidden">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="p-2 text-left">Article</th>
              <th class="p-2 text-center">Quantité</th>
              <!-- <th class="p-2 text-center">Prix</th> -->
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in articles" :key="i" class="border-t">
              <td class="p-2">{{ a.designation }}</td>
              <td class="p-2 text-center">{{ a.quantite }} ({{ a.unite_mesure }})</td>
              <!-- <td class="p-2 text-center">{{ a.prix_unitaire }} DH</td> -->
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else-if="selectedFiche">
        <p class="text-sm text-gray-500">Cette fiche ne contient aucun article.</p>
      </div>

      <!-- Motif -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Motif de la demande
        </label>
        <textarea
          v-model="form.motif"
          class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          rows="2"
          placeholder="Ex: Fournitures pédagogiques"
        ></textarea>
      </div>
    </form>

    <!-- Footer -->
    <div class="flex justify-end space-x-3 pt-2">
      <button
        type="button"
        @click="createDemandeModal.close()"
        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
      >
        Annuler
      </button>
      <button
        type="button"
        @click="submit"
        :disabled="form.processing"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
      >
        Enregistrer
      </button>
    </div>
  </Modal>
</template>
