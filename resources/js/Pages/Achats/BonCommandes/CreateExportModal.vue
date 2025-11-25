<template>
  <Modal name="export-modal">
    <!-- Header -->
    <div class="mb-2">
      <h2 class="text-lg font-semibold text-gray-900">Exporter les marchés</h2>
      <p class="text-sm text-gray-500 mt-1">
        Sélectionnez une plage de dates pour exporter les marchés correspondants.
      </p>
    </div>

    <!-- Form -->
    <div>
      <div class="grid gap-4 mt-4">
        <!-- Start date -->
        <div>
          <InputLabel for="start_date">Date de début</InputLabel>
          <input
            id="start_date"
            type="date"
            v-model="exportForm.start_date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
          />
          <InputError :message="exportForm.errors.start_date" />
        </div>

        <!-- End date -->
        <div>
          <InputLabel for="end_date">Date de fin</InputLabel>
          <input
            id="end_date"
            type="date"
            v-model="exportForm.end_date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          />
          <InputError :message="exportForm.errors.end_date" />
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="mt-6 flex justify-end gap-2">
        <button
          type="button"
          variant="outline"
          @click="close"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Annuler
        </button>

        <a
          :href="route('bon-commandes.export', exportForm.data())"
          @click="handleExport"
          target="_blank"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          :class="{ 'opacity-50 cursor-not-allowed': !exportForm.start_date }"
        >
          Exporter
        </a>
        
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

// Refs
const exportModalRef = ref(null)

// Inertia form
const exportForm = useForm({
  start_date: null,
  end_date: null,
})


// Close modal
function close() {
  exportModalRef.value?.close()
}

function handleExport(event) {
    if (!exportForm.start_date) {
      event.preventDefault();
    }
  }
</script>
