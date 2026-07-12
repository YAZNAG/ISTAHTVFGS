<template>
  <Modal>
    <!-- Header -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold text-gray-900">Créer un décompte</h2>
      <p class="text-sm text-gray-500 mt-1">
        Sélectionnez la période de livraison pour générer le décompte du marché.
      </p>
    </div>

    <!-- Form -->
    <div class="grid gap-4">

      <!-- Date début -->
      <div>
        <InputLabel for="date_debut">Date début <span class="text-gray-400 text-xs">(optionnelle)</span></InputLabel>
        <input
          id="date_debut"
          type="date"
          v-model="form.date_debut"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
        <InputError :message="form.errors.date_debut" />
      </div>

      <!-- Date fin -->
      <div>
        <InputLabel for="date">Date fin <span class="text-red-500">*</span></InputLabel>
        <input
          id="date"
          type="date"
          v-model="form.date"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          required
        />
        <InputError :message="form.errors.date" />
      </div>

      <!-- Catégorie (pré-sélectionnée depuis le marché) -->
      <div>
        <InputLabel for="categorie_id">Catégorie</InputLabel>
        <select
          id="categorie_id"
          v-model="form.categorie_id"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option :value="null">Toutes les catégories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
        </select>
        <InputError :message="form.errors.categorie_id" />
      </div>

      <!-- Final -->
      <div class="flex items-center gap-2">
        <input
          id="is_final"
          type="checkbox"
          v-model="form.is_final"
          class="h-4 w-4 text-blue-600 border-gray-300 rounded"
        />
        <InputLabel for="is_final" class="mb-0 cursor-pointer">Décompte définitif</InputLabel>
        <InputError :message="form.errors.is_final" />
      </div>
    </div>

    <!-- Résumé période -->
    <div v-if="form.date" class="mt-4 rounded-md bg-blue-50 border border-blue-100 px-4 py-2 text-sm text-blue-700">
      Période :
      <strong>{{ form.date_debut ? formatDate(form.date_debut) + ' → ' : 'Début du marché → ' }}</strong>
      <strong>{{ formatDate(form.date) }}</strong>
    </div>

    <!-- Footer -->
    <div class="mt-6 flex justify-end gap-2">
      <button
        type="button"
        @click="$modal.close()"
        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
      >
        Annuler
      </button>
      <button
        @click="submit"
        :disabled="!form.date || form.processing"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ form.processing ? 'Génération...' : 'Générer le décompte' }}
      </button>
    </div>
  </Modal>
</template>

<script setup>
import { Modal, useModal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  marche_id:            [Number, String],
  default_categorie_id: { type: [Number, String], default: null },
  categories:           { type: Array, default: () => [] },
})

const modal = useModal()

const form = useForm({
  date:         null,
  date_debut:   null,
  categorie_id: props.default_categorie_id,
  is_final:     false,
})

function formatDate(d) {
  if (!d) return ''
  const [y, m, j] = d.split('-')
  return `${j}/${m}/${y}`
}

function submit() {
  form.post(route('decompte.store', props.marche_id), {
    onSuccess: () => modal.close(),
  })
}
</script>
