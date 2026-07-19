<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import { DocumentArrowDownIcon } from '@heroicons/vue/24/outline'
import InputError from '@/Components/InputError.vue'

const exportModalRef = ref(null)

const exportForm = useForm({
  start_date: '',
  end_date: '',
})

function close() {
  exportModalRef.value?.close()
}

function exportPdf() {
  window.open(route('menus.export', exportForm.data()), '_blank')
}
</script>

<template>
  <Modal ref="exportModalRef">
    <div class="mb-5 border-b border-slate-100 pb-4">
      <h2 class="text-lg font-bold text-istaht-navy">Exporter le planning des menus</h2>
      <p class="mt-1 text-sm text-slate-500">
        Sélectionnez une plage de dates pour générer le PDF des menus collectivité correspondants.
      </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Date de début *</label>
        <input type="date" v-model="exportForm.start_date" class="ui-input w-full" required />
        <InputError :message="exportForm.errors.start_date" />
      </div>
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Date de fin *</label>
        <input type="date" v-model="exportForm.end_date" class="ui-input w-full" required />
        <InputError :message="exportForm.errors.end_date" />
      </div>
    </div>

    <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
      <button type="button" @click="close" class="ui-button ui-button-ghost">Annuler</button>
      <button
        type="button"
        :disabled="!exportForm.start_date || !exportForm.end_date"
        class="ui-button ui-button-primary disabled:opacity-50"
        @click="exportPdf"
      >
        <DocumentArrowDownIcon class="mr-1.5 h-4 w-4" />
        Exporter le PDF
      </button>
    </div>
  </Modal>
</template>
