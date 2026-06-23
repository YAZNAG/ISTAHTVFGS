<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  categorie: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  code: props.categorie.code,
  nom: props.categorie.nom,
  couleur: props.categorie.couleur || '#155e9f',
  est_actif: Boolean(props.categorie.est_actif),
})

const editCategorieModal = ref(null)

const submit = () => {
  form.put(route('categories.update', props.categorie.id), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => editCategorieModal.value.close(),
  })
}
</script>

<template>
  <Modal ref="editCategorieModal">
    <div>
      <h2 class="text-lg font-bold text-istaht-navy">Modifier une categorie</h2>
      <p class="mt-1 text-sm text-slate-500">Le formulaire reste limite au code, nom, couleur et statut.</p>
    </div>

    <form class="mt-6 space-y-4" @submit.prevent="submit">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="ui-label">Code categorie *</label>
          <input v-model="form.code" type="text" required maxlength="50" class="ui-input mt-1.5" />
          <InputError :message="form.errors.code" />
        </div>

        <div>
          <label class="ui-label">Nom categorie *</label>
          <input v-model="form.nom" type="text" required maxlength="100" class="ui-input mt-1.5" />
          <InputError :message="form.errors.nom" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="ui-label">Couleur *</label>
          <input
            v-model="form.couleur"
            type="color"
            required
            class="mt-1.5 h-11 w-full rounded-lg border border-slate-200 bg-white p-1"
          />
          <InputError :message="form.errors.couleur" />
        </div>

        <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
          <input v-model="form.est_actif" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-istaht-blue focus:ring-istaht-cyan" />
          <span>
            <span class="block text-sm font-bold text-istaht-navy">Categorie active</span>
            <span class="block text-xs text-slate-500">Desactiver si elle ne doit plus etre utilisee</span>
          </span>
        </label>
      </div>

      <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
        <button type="button" class="ui-button ui-button-ghost" @click="editCategorieModal.close()">Annuler</button>
        <button type="submit" class="ui-button ui-button-primary" :disabled="form.processing">
          {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>
    </form>
  </Modal>
</template>
