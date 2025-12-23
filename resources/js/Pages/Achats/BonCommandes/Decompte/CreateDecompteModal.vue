<template>
  <Modal ref="createDecompteModal">
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
          <InputLabel for="date">Date</InputLabel>
          <input
            id="date"
            type="date"
            v-model="form.date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
          />
          <InputError :message="form.errors.date" />
        </div>

        <div class="flex items-center">
          <input
            id="is_final"
            type="checkbox"
            v-model="form.is_final"
            class="h-4 w-4 text-blue-600 border-gray-300 rounded"
          />
          <InputLabel for="is_final" class="ml-2 block text-sm text-gray-700">est finale</InputLabel>
          <InputError :message="form.errors.is_final" />
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

        <button
          @click="submit"
          target="_blank"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          :class="{ 'opacity-50 cursor-not-allowed': !form.date }"
        >
          Enregistrer
        </button>
        
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  marche_id: Object,
});

// Refs
const createDecompteModal = ref(null)

// Inertia form
const form = useForm({
  date: null,
  is_final: false,
})


// Close modal
function close() {
  createDecompteModal.value?.close()
}

function submit(event) {
    form.post(route('decompte.store', props.marche_id), {
      onSuccess: () => {
        createDecompteModal.value.close()
      }
    });
  }
</script>
