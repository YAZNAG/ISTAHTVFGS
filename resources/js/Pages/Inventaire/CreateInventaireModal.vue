<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm, router } from '@inertiajs/vue3'

/* ---------- refs ---------- */
const createInventaireModal = ref(null)


/* ---------- form ---------- */
const form = useForm({
  mois: new Date().toISOString().slice(0, 7), // yyyy-mm
})

/* ---------- utils ---------- */
function submit() {
  form.post(route('inventaires.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      createInventaireModal.value.close()
      form.reset()
    },
  })
}

</script>

<template>
  <Modal name="createInventaireModal" ref="createInventaireModal">
    <!-- Header -->
    <div class="mb-2">
      <h2 class="text-lg font-semibold text-gray-900">Créer un inventaire mensuel</h2>
      <p class="text-sm text-gray-500 mt-1">
        Un inventaire est créé mensuellement et verrouillé une fois finalisé.
      </p>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="grid gap-4 mt-4">
      <!-- Mois -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
        <input
          v-model="form.mois"
          type="month"
          placeholder="2025-01"
          required
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
        />
        <div v-if="form.errors.mois" class="text-red-600 text-xs mt-1">
          {{ form.errors.mois }}
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex justify-end gap-2">
        <button
          type="button"
          @click="createInventaireModal.close()"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Annuler
        </button>

        <button
          type="submit"
          :disabled="form.processing"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
        >
          Créer
        </button>
      </div>
    </form>
  </Modal>
</template>