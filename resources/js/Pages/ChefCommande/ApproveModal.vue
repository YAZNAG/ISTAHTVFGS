<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Modal } from '@inertiaui/modal-vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    UserIcon,
    PencilIcon,
    XCircleIcon,
    CubeIcon
} from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';
import { getChefCommandeStatutInfo } from '@/Utils/labels.js';

const props = defineProps({
    chefCommande: Object // passed from controller with relations: articles
});

const approveModalRef = ref(null);


const approveForm = useForm({
    validation_note: '',
});

const approve = () => {
    approveForm.put(route('chef-commandes.approve', props.chefCommande), {
        preserveScroll: true,
        onSuccess: () => {
            approveForm.reset();
            approveModalRef.value.close();
        },
    });
}

const reject = () => {
    approveForm.put(route('chef-commandes.reject', props.chefCommande), {
        preserveScroll: true,
        onSuccess: () => {
            approveForm.reset();
            approveModalRef.value.close();
        },
    });
}

const statutInfo = getChefCommandeStatutInfo(props.chefCommande.statut);

</script>

<template>

    <Modal ref="approveModalRef">
        <!-- Header -->
        <div class="mb-2">
            <h2 class="text-lg font-semibold text-gray-900">Approuver le Bon de Commande</h2>
            <p class="text-sm text-gray-500 mt-1">
                Confirmez que vous souhaitez approuver cette bon de commande. Vous pouvez ajouter un commentaire
                optionnel.
            </p>
        </div>

        <!-- Motif & Status -->
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold mb-1">Bon de Commande #{{ chefCommande.numero }}</h1>
                <p class="text-gray-500">Créé le {{ chefCommande.created_at }}</p>
            </div>

            <!-- Informations principales -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex gap-4 justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Numero</p>
                        <p class="text-gray-800 font-medium">{{ chefCommande.numero }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Nombre d'articles</p>
                        <p class="text-gray-800 font-medium">
                            <span
                                class="bg-blue-100 border border-blue-400 text-blue-600 inline-flex gap-1 items-center px-2 py-1 rounded-full">
                                <CubeIcon class="h-4 w-4" />
                                {{ chefCommande.articles_count }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Statut</p>
                        <span :class="['px-3 py-1 rounded-full text-sm font-medium', statutInfo.color]">
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

                    <div v-if="chefCommande.statut === 'rejet'">
                        <p class="text-sm text-gray-500">Refusé le</p>
                        <p class="text-red-600 font-medium">{{ chefCommande.validateion_date }}</p>
                    </div>

                    <div v-if="chefCommande.statut === 'en_attente_livraison'">
                        <p class="text-sm text-gray-500">Validé le</p>
                        <p class="text-green-600 font-medium">{{ chefCommande.validateion_date }}</p>
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
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                    Article</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">
                                    Unité</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">
                                    Quantité</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in chefCommande.items" :key="index"
                                class="hover:bg-gray-50 transition">
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

            <!-- Form -->
            <form class="mt-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Commentaire <span class="text-xs">(Optionnel)</span>
                    </label>
                    <textarea v-model="approveForm.validation_note"
                        class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" rows="2"
                        placeholder=""></textarea>
                    <p class="text-red-600 font-sm" v-if="approveForm.errors.validation_note">{{
                        approveForm.errors.validation_note }}</p>
                </div>


                <!-- Footer -->
                <div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" @click="approveModalRef.close()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            Annuler
                        </button>
                        <button type="button" @click="reject" :disabled="approveForm.processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                            Rejetter
                        </button>
                        <button type="button" @click="approve" :disabled="approveForm.processing"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                            Approuver
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>
