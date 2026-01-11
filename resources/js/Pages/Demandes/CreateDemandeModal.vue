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
  restaurants: Array,  // with articles
  types: Object,
})

const createDemandeModal = ref(null)

const selectedFiche = ref(null)

const fiches = computed(() => {
  if (form.demandable_type === 'collectivite') return props.fichesCollectives
  if (form.demandable_type === 'pedagogique') return props.fichesPedagogiques
  if (form.demandable_type === 'restaurant') return props.restaurants

  return []
})

const form = useForm({
  demandeur: '',
  fiche_technique: "",
  motif: '',
  demandable_id: '',
  demandable_type: '',
})

const articles = ref([])
function handleFileUpload(event) {
  form.fiche_technique = event.target.files[0];
}

// when fiche changes, auto load its articles (read-only)
watch(() => form.demandable_id, (ficheId) => {
  const fiche = fiches.value.find(f => f.id === ficheId)
  
  if (!fiche) {
    articles.value = []
    return
  }
  
  articles.value = fiche.articles.map(a => ({
    article_id: a.id,
    designation: a.designation,
    quantite:  a.quantite ?? 0,
    prix_unitaire: a.prix_unitaire ?? 0,
    unite_mesure: a.unite_mesure
  }))
});

watch(() => form.demandable_type, () => {
  form.demandable_id = null
  articles.value = []
})

function submit() {
  form.post(route('demandes.store'), {
    onSuccess: () => {
      form.reset()
      form.demandable_id = null,
      form.demandable_type = ''
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
      <div v-if="$page.props.auth.role === 'manager'">
        <label class="block text-sm font-medium text-gray-700 mb-1">Demandeur</label>
        <select v-model="form.demandeur"
            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option disabled value="">Sélectionnez un demandeur</option>
            <option v-for="d in demandeurs" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>
        <p v-if="form.errors.demandeur" class="text-sm text-red-600 mt-1">{{ form.errors.demandeur }}</p>
      </div>

      <!-- Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type de Demande</label>
        <select
          v-model="form.demandable_type"
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option disabled value="">Sélectionnez un type</option>
          <option :value="type.value" v-for="type in types" :key="type.value">{{ type.label }}</option>
        </select>
      </div>

      <!-- Fiche -->
      <div v-if="form.demandable_type">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ form.demandable_type === 'restaurant' ? 'Restaurant' : 'Fiche' }}</label>
        <select
          v-model="form.demandable_id"
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option disabled value="">Sélectionnez une {{ form.demandable_type === 'restaurant' ? 'Restaurant' : 'Fiche' }}</option>
          <option v-for="fiche in fiches" :key="fiche.id" :value="fiche.id">
            {{ fiche.nom }} - {{ fiche.id }}
          </option>
        </select>
        <InputError :message="form.errors.demandable_id" />
      </div>

      <!-- Fiche Technique -->
      <div v-if="form.demandable_type !== 'restaurant'">
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
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in articles" :key="i" class="border-t">
              <td class="p-2">{{ a.designation }}</td>
              <td class="p-2 text-center">{{ a.quantite }} ({{ a.unite_mesure }})</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else-if="selectedFiche">
        <p class="text-sm text-gray-500">Cette fiche ne contient aucun article.</p>
      </div>

      <!-- Motif / Restaurant motif selector -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Motif de la demande
        </label>

        <!-- Restaurant mode -->
        <div v-if="form.demandable_type === 'restaurant'">
          <select
            v-model="form.motif"
            class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option disabled value="">Sélectionnez un motif</option>
            <option value="petit-déjeuner">Petit-déjeuner</option>
            <option value="déjeuner">Déjeuner</option>
            <option value="dîner">Dîner</option>
            <option value="goûter">Goûter</option>
            <option value="autre">Autre</option>
          </select>

          <!-- Free text only when "autre" is chosen -->
          <textarea
            v-if="form.motif === 'autre'"
            v-model="form.motifAutre"
            class="w-full mt-2 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            rows="2"
            placeholder="Précisez le motif"
          ></textarea>
        </div>

        <!-- Default mode -->
        <textarea
          v-else
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