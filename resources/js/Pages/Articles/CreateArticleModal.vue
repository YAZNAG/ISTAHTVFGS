<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  categories: { type: Array, default: () => [] },
  unitOptions: { type: Array, default: () => ['kg', 'L', 'piece'] },
})

const createArticleModal = ref(null)

const form = useForm({
  reference: '',
  designation: '',
  categorie_id: '',
  unite_mesure: '',
  taux_tva: 0,
  stock_initial: 0,
  seuil_minimal: 0,
  seuil_maximal: 0,
  est_actif: true,
})

const submit = () => {
  form.post(route('articles.store'), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      form.taux_tva = 0
      form.stock_initial = 0
      form.seuil_minimal = 0
      form.seuil_maximal = 0
      form.est_actif = true
      createArticleModal.value.close()
    },
  })
}
</script>

<template>
  <Modal ref="createArticleModal">
    <div>
      <h2 class="text-lg font-bold text-istaht-navy">Ajouter un article</h2>
      <p class="mt-1 text-sm text-slate-500">Renseignez les informations necessaires au referentiel et au suivi stock.</p>
    </div>

    <form class="mt-6 space-y-4" @submit.prevent="submit">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="ui-label">Reference *</label>
          <input v-model="form.reference" type="text" required maxlength="50" class="ui-input mt-1.5" />
          <InputError :message="form.errors.reference" />
        </div>

        <div>
          <label class="ui-label">Designation *</label>
          <input v-model="form.designation" type="text" required maxlength="255" class="ui-input mt-1.5" />
          <InputError :message="form.errors.designation" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="ui-label">Categorie *</label>
          <select v-model="form.categorie_id" required class="ui-input mt-1.5">
            <option value="">Selectionner...</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.code || '-' }} - {{ cat.nom }}
            </option>
          </select>
          <InputError :message="form.errors.categorie_id" />
        </div>

        <div>
          <label class="ui-label">Unite de mesure *</label>
          <select v-model="form.unite_mesure" required class="ui-input mt-1.5">
            <option value="">Selectionner...</option>
            <option v-for="unit in props.unitOptions" :key="unit" :value="unit">{{ unit }}</option>
          </select>
          <InputError :message="form.errors.unite_mesure" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div>
          <label class="ui-label">TVA %</label>
          <input v-model.number="form.taux_tva" type="number" min="0" max="100" step="0.01" class="ui-input mt-1.5" />
          <InputError :message="form.errors.taux_tva" />
        </div>

        <div>
          <label class="ui-label">Stock initial</label>
          <input v-model.number="form.stock_initial" type="number" min="0" step="0.01" class="ui-input mt-1.5" />
          <InputError :message="form.errors.stock_initial" />
        </div>

        <div>
          <label class="ui-label">Seuil minimal</label>
          <input v-model.number="form.seuil_minimal" type="number" min="0" step="0.01" class="ui-input mt-1.5" />
          <InputError :message="form.errors.seuil_minimal" />
        </div>

        <div>
          <label class="ui-label">Seuil maximal</label>
          <input v-model.number="form.seuil_maximal" type="number" min="0" step="0.01" class="ui-input mt-1.5" />
          <InputError :message="form.errors.seuil_maximal" />
        </div>
      </div>

      <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
        <input v-model="form.est_actif" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-istaht-blue focus:ring-istaht-cyan" />
        <span>
          <span class="block text-sm font-bold text-istaht-navy">Article actif</span>
          <span class="block text-xs text-slate-500">Actif par defaut dans les listes et workflows</span>
        </span>
      </label>

      <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
        <button type="button" class="ui-button ui-button-ghost" @click="createArticleModal.close()">Annuler</button>
        <button type="submit" class="ui-button ui-button-primary" :disabled="form.processing">
          {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>
    </form>
  </Modal>
</template>
