<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import Dump from '@/Components/Dump.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  categorie: Object
});

const form = useForm({
  nom:         props.categorie.nom,
  code:        props.categorie.code,
  description: props.categorie.description,
  est_actif:   Boolean(props.categorie.est_actif),
});

const editCategorieModal = ref(null);

const submit = () => {
  form.put(route('categories.update', props.categorie.id), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => editCategorieModal.value.close(),
  });
};
</script>

<template>
  <Modal ref="editCategorieModal">
    <!-- Header -->
    <div class="mb-2">
      <h2 class="text-lg font-semibold text-gray-900">Modifier une catégorie</h2>
      <p class="text-sm text-gray-500 mt-1">
        Mettez à jour les informations de la catégorie.
      </p>
    </div>

    <form class="mt-8 space-y-4" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium text-gray-700">Nom *</label>
        <input v-model="form.nom" type="text" required
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        <InputError :message="form.errors.nom" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Code *</label>
        <input v-model="form.code" type="text" required
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        <InputError :message="form.errors.code" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea v-model="form.description" rows="3"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
      </div>

      <div class="flex items-center">
        <input v-model="form.est_actif" type="checkbox" id="categorie_est_actif_edit"
          class="h-4 w-4 text-blue-600 border-gray-300 rounded">
        <label for="categorie_est_actif_edit" class="ml-2 block text-sm text-gray-700">Catégorie active</label>
      </div>

      <!-- Footer -->
      <div class="flex justify-end space-x-3 pt-2">
        <button type="button" @click="editCategorieModal.close()"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
          Annuler
        </button>
        <button type="submit" :disabled="form.processing"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
          Enregistrer
        </button>
      </div>
    </form>
  </Modal>
</template>