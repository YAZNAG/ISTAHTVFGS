<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import { DocumentArrowDownIcon } from '@heroicons/vue/24/outline'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  categories: { type: Array, default: () => [] },
})

const exportModalRef = ref(null)

const exportForm = useForm({
  start_date: '',
  end_date: '',
  categorie_id: '',
})

function close() {
  exportModalRef.value?.close()
}

function exportPdf() {
  window.open(route('entree-stocks.export', exportForm.data()), '_blank')
}
</script>

<template>
  <Modal ref="exportModalRef">
    <!-- ═══ En-tête ═══ -->
    <div class="mb-5 border-b border-slate-100 pb-4">
      <h2 class="text-lg font-bold text-istaht-navy">Exporter la fiche des entrées</h2>
      <p class="mt-1 text-sm text-slate-500">
        Sélectionnez la période et, si besoin, une catégorie pour filtrer les entrées en stock.
      </p>
    </div>

    <div class="grid gap-4">
      <!-- Dates -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Date de début *</label>
          <input
            id="start_date"
            type="date"
            v-model="exportForm.start_date"
            class="ui-input w-full"
            required
          />
          <InputError :message="exportForm.errors.start_date" />
        </div>

        <div>
          <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Date de fin *</label>
          <input
            id="end_date"
            type="date"
            v-model="exportForm.end_date"
            class="ui-input w-full"
          />
          <InputError :message="exportForm.errors.end_date" />
        </div>
      </div>

      <!-- Catégorie -->
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Catégorie</label>
        <select v-model="exportForm.categorie_id" class="ui-input w-full">
          <option value="">Toutes les catégories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
        </select>
        <InputError :message="exportForm.errors.categorie_id" />
      </div>
    </div>

    <!-- ═══ Pied ═══ -->
    <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
      <button type="button" @click="close" class="ui-button ui-button-ghost">
        Annuler
      </button>
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
