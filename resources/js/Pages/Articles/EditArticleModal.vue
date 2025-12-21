<script setup>
import { ref, watchEffect } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Modal } from '@inertiaui/modal-vue';
import { Link, useForm } from '@inertiajs/vue3';
import Dump from '@/Components/Dump.vue';
import InputError from '@/Components/InputError.vue';

/* ---------- props ---------- */
const props = defineProps({
  article: Object,          // article to edit
  categories: Array,
  marche_categories: Array,
});

/* ---------- form (pre-filled) ---------- */
const form = useForm({
  reference:     props.article.reference,
  designation:   props.article.designation,
  description:   props.article.description,
  categorie_id:  props.article.categorie_id,
  marche_category_id: props.article.marche_category_id,
  unite_mesure:  props.article.unite_mesure,
  taux_tva:      props.article.taux_tva,
  seuil_maximal: props.article.seuil_maximal,
  seuil_minimal: props.article.seuil_minimal,
  est_actif:     Boolean(props.article.est_actif),
});

/* ---------- modal ref ---------- */
const editArticleModal = ref(null);

/* ---------- submit ---------- */
const submit = () => {
  form.put(route('articles.update', props.article.id), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => editArticleModal.value.close(),
  });
};
</script>

<template>
  <Modal ref="editArticleModal">
    <!-- Header -->
    <div class="mb-2">
      <h2 class="text-lg font-semibold text-gray-900">Modifier un article</h2>
      <p class="text-sm text-gray-500 mt-1">
        Mettez à jour les informations de l’article.
      </p>
    </div>

    <!-- Form -->
    <form class="mt-8" @submit.prevent="submit">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left column -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Référence *</label>
            <input v-model="form.reference" type="text" required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            <InputError :message="form.errors.reference" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Désignation *</label>
            <input v-model="form.designation" type="text" required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            <InputError :message="form.errors.designation" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea v-model="form.description" rows="3"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
          </div>
        </div>

        <!-- Right column -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Catégorie *</label>
            <select v-model="form.categorie_id" required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
              <option value="">Sélectionnez...</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.nom }}
              </option>
            </select>
            <InputError :message="form.errors.categorie_id" />
          </div>

          <div>
                        <label class="block text-sm font-medium text-gray-700">Marché Catégorie *</label>
                        <select v-model="form.marche_category_id" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Sélectionnez...</option>
                            <option v-for="marche_cat in marche_categories" :key="marche_cat.id" :value="marche_cat.id">
                                {{ marche_cat.nom }}
                            </option>
                        </select>
                        <InputError :message="form.errors.marche_category_id" />
                    </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Unité *</label>
            <select v-model="form.unite_mesure" required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
              <option value="">Sélectionnez...</option>
              <option value="kg">kg</option>
              <option value="L">L</option>
              <option value="pièce">pièce</option>
              <option value="sachet">sachet</option>
              <option value="sac">sac</option>
              <option value="boite">boite</option>
              <option value="bidon">bidon</option>
              <option value="paquet">paquet</option>
              <option value="flacon">flacon</option>
              <option value="pot">pot</option>
              <option value="bouteille">bouteille</option>
            </select>
            <InputError :message="form.errors.unite_mesure" />
          </div>
<!-- 
          <div>
            <label class="block text-sm font-medium text-gray-700">Seuil minimal</label>
            <input v-model="form.seuil_minimal" type="number" min="0"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            <InputError :message="form.errors.seuil_minimal" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Seuil maximal</label>
            <input v-model="form.seuil_maximal" type="number" min="0"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            <InputError :message="form.errors.seuil_maximal" />
          </div> -->

          <div class="flex items-center">
            <input v-model="form.est_actif" type="checkbox" id="est_actif_edit"
              class="h-4 w-4 text-blue-600 border-gray-300 rounded">
            <label for="est_actif_edit" class="ml-2 block text-sm text-gray-700">Article actif</label>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end space-x-3 pt-6">
        <button type="button" @click="editArticleModal.close()"
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