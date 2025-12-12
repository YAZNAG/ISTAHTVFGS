<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Modal } from '@inertiaui/modal-vue';
import { Link, useForm } from '@inertiajs/vue3';
import Dump from '@/Components/Dump.vue';
import InputError from '@/Components/InputError.vue';


const props = defineProps({
    categories: Array
});

const form = useForm({
    reference: '',
    designation: '',
    description: '',
    categorie_id: '',
    unite_mesure: '',
    taux_tva: 0,
    seuil_maximal: 0,
    seuil_minimal: 0,
    est_actif: true,
});

const createArticleModal = ref(null);

const submit = () => {
    form.post(route('articles.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            createArticleModal.value.close() // close the modal on success
        },
    })

}

</script>

<template>
    <Modal ref="createArticleModal">
        <!-- Header -->
        <div class="mb-2">
            <h2 class="text-lg font-semibold text-gray-900">Ajouter un article</h2>
            <p class="text-sm text-gray-500 mt-1">
                Remplissez le formulaire pour ajouter un nouvel article.
            </p>
        </div>

        <!-- Form -->
        <form class="mt-8">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Colonne gauche -->
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

                <!-- Colonne droite -->
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

                    </div>

                    <div class="flex items-center">
                        <input v-model="form.est_actif" type="checkbox" id="est_actif"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="est_actif" class="ml-2 block text-sm text-gray-700">Article actif</label>
                    </div>
                </div>
            </div>


            <!-- Footer -->
            <div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="createArticleModal.close()"
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
