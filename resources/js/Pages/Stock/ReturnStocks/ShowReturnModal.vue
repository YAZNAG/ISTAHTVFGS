<script setup>
import { Modal } from '@inertiaui/modal-vue';
import { CubeIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    returnStock: Object,
});

const showReturnModal = ref(null)
</script>

<template>
    <Modal ref="showReturnModal">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold mb-1">Retour #{{ returnStock.numero }}</h1>
                <p class="text-gray-500">Date : {{ returnStock.date }}</p>
            </div>

            <!-- Core infos -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Retourné par</p>
                        <p class="text-gray-800 font-medium">{{ returnStock.returner_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Reçu par</p>
                        <p class="text-gray-800 font-medium">{{ returnStock.receiver_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Motif</p>
                        <p class="text-gray-800 font-medium">{{ returnStock.motif }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nombre d'articles</p>
                        <p class="text-gray-800 font-medium">
                            <span
                                class="bg-blue-100 border border-blue-400 text-blue-600 inline-flex gap-1 items-center px-2 py-1 rounded-full">
                                <CubeIcon class="h-4 w-4" />
                                {{ returnStock.articles_count }}
                            </span>
                        </p>
                    </div>
                    <div v-if="returnStock.created_at">
                        <p class="text-sm text-gray-500">Créé le</p>
                        <p class="text-gray-800 font-medium">{{ returnStock.created_at }}</p>
                    </div>
                </div>
            </div>

            <!-- Articles Table -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h2 class="text-lg font-semibold mb-4">Articles retournés</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Article</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">
                                    Unité</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">
                                    Quantité</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in returnStock.items" :key="index"
                                class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    {{ item.designation }}
                                </td>
                                <td class="px-6 py-4 text-center text-gray-600">
                                    {{ item.unite_mesure }}
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
                <button type="button"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    @click="showReturnModal.close()">
                    Fermer
                </button>
            </div>
        </div>
    </Modal>
</template>