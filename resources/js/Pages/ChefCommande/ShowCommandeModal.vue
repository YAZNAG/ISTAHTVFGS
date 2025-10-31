<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Modal } from '@inertiaui/modal-vue';
import { getBonCommandeStatutInfo } from '@/Utils/labels.js'; 
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { CubeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    chefCommande: Object // passed from controller with items relation
});

const showCommandeModal = ref(null)


// Format date in French
function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Example: if you have a helper to style statut
const statutInfo = getBonCommandeStatutInfo(props.chefCommande.statut);
</script>

<template>
<!-- <AuthenticatedLayout> -->
    <Head :title="`Bon de Commande #${chefCommande.numero}`" />

    <Modal ref="showCommandeModal">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold mb-1">Bon de Commande #{{ chefCommande.numero }}</h1>
                <p class="text-gray-500">Créé le {{ chefCommande.created_at }}</p>
            </div>

            <!-- Informations principales -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Numero</p>
                        <p class="text-gray-800 font-medium">{{ chefCommande.numero }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Nombre d'articles</p>
                        <p class="text-gray-800 font-medium">
                            <span class="bg-blue-100 border border-blue-400 text-blue-600 inline-flex gap-1 items-center px-2 py-1 rounded-full">
                                <CubeIcon class="h-4 w-4" />
                                {{ chefCommande.articles_count }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Statut</p>
                        <span
                            :class="['px-3 py-1 rounded-full text-sm font-medium', statutInfo.color]"
                        >
                            {{ statutInfo.label }}
                        </span>
                    </div>

                    <div v-if="chefCommande.note">
                        <p class="text-sm text-gray-500">Note</p>
                        <p class="text-gray-800 font-medium">{{ chefCommande.note }}</p>
                    </div>

                    <div v-if="chefCommande.validation_note">
                        <p class="text-sm text-gray-500">Note de validation</p>
                        <p class="text-gray-800 font-medium">{{ chefCommande.validation_note }}</p>
                    </div>

                    <div v-if="chefCommande.annule">
                        <p class="text-sm text-gray-500">Refusé le</p>
                        <p class="text-red-600 font-medium">{{ chefCommande.annule }}</p>
                    </div>
                </div>
            </div>

            <!-- Articles Table -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h2 class="text-lg font-semibold mb-4">Articles commandés</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Article</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">Unité</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">Quantité</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(item, index) in chefCommande.items"
                                :key="index"
                                class="hover:bg-gray-50 transition"
                            >
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    {{ item.article.designation }}
                                </td>
                                <td class="px-6 py-4 text-center text-gray-600">
                                    {{ item.article.unite_mesure }}
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-gray-800">
                                    {{ item.quantite }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 pt-4">
                <button
                    type="button"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    @click="showCommandeModal.close()"
                >
                    Fermer
                </button>
            </div>

        </div>
    </Modal>
<!-- </AuthenticatedLayout> -->
</template>
