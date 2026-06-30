<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm, router } from '@inertiajs/vue3'

/* ---------- refs ---------- */
const createInventaireModal = ref(null)

/* ---------- utils ---------- */
function currentIsoWeek() {
  const now = new Date()
  const target = new Date(now.valueOf())
  const dayNr = (now.getDay() + 6) % 7
  target.setDate(target.getDate() - dayNr + 3)
  const firstThursday = target.valueOf()
  target.setMonth(0, 1)
  if (target.getDay() !== 4) {
    target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7)
  }
  const week = 1 + Math.ceil((firstThursday - target.valueOf()) / 604800000)
  return `${now.getFullYear()}-W${String(week).padStart(2, '0')}`
}

/* ---------- form ---------- */
const form = useForm({
  semaine: currentIsoWeek(),
})

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
      <h2 class="text-lg font-semibold text-gray-900">Créer un inventaire hebdomadaire</h2>
      <p class="text-sm text-gray-500 mt-1">
        Un inventaire est créé chaque semaine et verrouillé une fois finalisé.
      </p>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="grid gap-4 mt-4">
      <!-- Semaine -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Semaine</label>
        <input
          v-model="form.semaine"
          type="week"
          placeholder="2026-W26"
          required
          class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
        />
        <div v-if="form.errors.semaine" class="text-red-600 text-xs mt-1">
          {{ form.errors.semaine }}
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
