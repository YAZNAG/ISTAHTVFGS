<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  demande: Object, // The demande being edited
  demandeurs: Array,
  fichesCollectives: Array,
  fichesPedagogiques: Array,
})

const editDemandeModal = ref(null)

const type = ref('')
const selectedFiche = ref(null)
const articles = ref([])

// Compute fiches based on selected type
const fiches = computed(() => {
  if (type.value === 'collective') return props.fichesCollectives
  if (type.value === 'pedagogique') return props.fichesPedagogiques
  return []
})

const form = useForm({
  demandeur: null,
  fiche_id: '',
  fiche_technique: null,
  motif: '',
})

// Handle file upload
function handleFileUpload(event) {
  form.fiche_technique = event.target.files[0];
}

// Load fiche articles when selected
watch(selectedFiche, (ficheId) => {
  const fiche = fiches.value.find(f => f.id === ficheId)
  if (!fiche) {
    form.fiche_id = ''
    articles.value = []
    return
  }
  form.fiche_id = fiche.id
  articles.value = fiche.articles.map(a => ({
    article_id: a.id,
    designation: a.designation,
    quantite: a.quantite ?? 0,
    prix_unitaire: a.prix_unitaire ?? 0,
    unite_mesure: a.unite_mesure

  }))
})

// Preload demande data when modal opens
onMounted(() => {
  if (props.demande) {
    form.demandeur = props.demande.demandeur_id
    form.motif = props.demande.motif ?? ''
    form.fiche_id = props.demande.fiche_id

    // detect fiche type
    const ficheInCollectives = props.fichesCollectives.find(f => f.id === props.demande.fiche_id)
    const ficheInPedagogiques = props.fichesPedagogiques.find(f => f.id === props.demande.fiche_id)

    if (ficheInCollectives) {
      type.value = 'collective'
      selectedFiche.value = ficheInCollectives.id
    } else if (ficheInPedagogiques) {
      type.value = 'pedagogique'
      selectedFiche.value = ficheInPedagogiques.id
    }

    // if demande already has articles in backend (optional)
    if (props.demande.articles && props.demande.articles.length) {
      articles.value = props.demande.articles.map(a => ({
        article_id: a.id,
        designation: a.designation,
        quantite: a.quantite_demandee ?? 0,
        unite_mesure: a.unite_mesure
        // prix_unitaire: a.prix_unitaire ?? 0,
      }))
    }
  }
})

function submit() {
  form.put(route('demandes.update', props.demande.id), {
    onSuccess: () => {
      editDemandeModal.value.close()
      form.reset()
      type.value = ''
      selectedFiche.value = null
    },
  })
}
</script>

<template>
  <Modal ref="editDemandeModal">
    <!-- Header -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold">Modifier la Demande d’Articles</h2>
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
        <InputError :message="form.errors.demandeur" />
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
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Fiche
          <span class="text-xs text-gray-400">({{ type == 'collective' ? 'Module' : 'Repas' }})</span>
        </label>
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
          @change="handleFileUpload"
          class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <InputError :message="form.errors.fiche_technique" />
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
              <th class="p-2 text-center">Prix</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in articles" :key="i" class="border-t">
              <td class="p-2">{{ a.designation }}</td>
              <td class="p-2 text-center">{{ a.quantite }} {{ a.unite_mesure }}</td>
              <td class="p-2 text-center">{{ a.prix_unitaire }} DH</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else-if="selectedFiche">
        <p class="text-sm text-gray-500">Cette fiche ne contient aucun article.</p>
      </div>

      <!-- Motif -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Motif de la demande</label>
        <textarea
          v-model="form.motif"
          class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          rows="2"
        ></textarea>
      </div>
    </form>

    <!-- Footer -->
    <div class="flex justify-end space-x-3 pt-2">
      <button
        type="button"
        @click="editDemandeModal.close()"
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
        Mettre à jour
      </button>
    </div>
  </Modal>
</template>
