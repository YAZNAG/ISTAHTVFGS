<script setup>
import { ref } from 'vue';
import { Modal } from '@inertiaui/modal-vue';
import { Link, useForm } from '@inertiajs/vue3';
import Dump from '@/Components/Dump.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    nom: '',
    code: '',
    description: '',
    est_actif: true,
});

const createCategorieModal = ref(null);

const submit = () => {
    form.post(route('categories.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            createCategorieModal.value.close() // close the modal on success
        },
    })

}

</script>

<template>
    <Modal ref="createCategorieModal" name="create-categorie-modal">
        <!-- Header -->
        <div class="mb-2">
            <h2 class="text-lg font-semibold text-gray-900">Ajouter un categorie</h2>
            <p class="text-sm text-gray-500 mt-1">
                Remplissez le formulaire pour ajouter un categorie.
            </p>
        </div>

        <Dump :data="form.data()" />


        <form class="mt-8 space-y-4">

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
                <input v-model="form.est_actif" type="checkbox" id="categorie_est_actif"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="categorie_est_actif" class="ml-2 block text-sm text-gray-700">Catégorie active</label>
            </div>



            <!-- Footer -->
            <div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="createCategorieModal.close()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Annuler
                    </button>
                    <button type="button" @click="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        Enregistrer
                    </button>
                </div>
            </div>

        </form>
    </Modal>
</template>
