<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { getDemandeStatutInfo, getDemandeTypeInfo, getSortieTypeInfo, getSortieStatutInfo } from '@/Utils/labels.js'; // import the reusable functions
import Dump from '@/Components/Dump.vue';

const props = defineProps({
    sortie: Object // includes articles, demande, created_by
});

const approveModalRef = ref(null);

// Format date in French
function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Form for approval/rejection
const approveForm = useForm({
    commentaire_validation: '',
});

const approve = () => {
    approveForm.put(route('sortie-stocks.approve', props.sortie), {
        preserveScroll: true,
        onSuccess: () => {
            approveForm.reset();
            approveModalRef.value.close();
        },
    });
}

const reject = () => {
    approveForm.put(route('sortie-stocks.reject', props.sortie), {
        preserveScroll: true,
        onSuccess: () => {
            approveForm.reset();
            approveModalRef.value.close();
        },
    });
}

// Computed labels for sortie & demande
const sortieStatut = getSortieStatutInfo(props.sortie.statut);
const sortieType = getSortieTypeInfo(props.sortie.type);

const demandeStatut = props.sortie.demande ? getDemandeStatutInfo(props.sortie.demande.statut) : null;
const demandeType = props.sortie.demande ? getDemandeTypeInfo(props.sortie.demande.type) : null;

</script>

<template>
<AuthenticatedLayout>
    <Head title="Détails de la sortie" />

    <Modal ref="approveModalRef">
        <div class="mb-2">
            <h2 class="text-lg font-semibold text-gray-900">Détails de la sortie</h2>
            <p class="text-sm text-gray-500 mt-1">Vérifiez les informations et validez ou rejetez si nécessaire.</p>
        </div>

        <!-- Sortie Info -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold mb-2">Motif</h2>
                        <p class="text-gray-700">{{ sortie.motif || '—————' }}</p>
                    </div>
                    <div>
                        <span :class="['px-3 py-1 rounded-full text-sm font-medium', sortieStatut.color]">
                            {{ sortieStatut.label }}
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-gray-500 text-sm">Type:</p>
                    <span :class="['px-2 py-1 rounded text-sm font-medium', sortieType.color]">
                        {{ sortieType.label }}
                    </span>
                </div>

                <div class="mt-2">
                    <p class="text-gray-500 text-sm">Date de sortie:</p>
                    <p class="text-gray-800 font-medium">{{ sortie.date }}</p>
                </div>

                <div class="mt-2">
                    <p class="text-gray-500 text-sm">Créée par:</p>
                    <p class="text-gray-800 font-medium">{{ sortie.created_by || '—' }}</p>
                </div>

                <!-- Related demande info -->
                <div v-if="sortie.demande" class="mt-4 p-4 bg-gray-50 rounded-lg border">
                    <h3 class="text-gray-700 font-medium mb-1">Demande liée</h3>
                    <p>
                        <span class="font-medium">Demandeur:</span> {{ sortie.demande.demandeur || '—' }}
                    </p>
                    <p class="mt-1">
                        <span class="font-medium">Statut:</span>
                        <span :class="['px-2 py-1 rounded text-sm font-medium', demandeStatut.color]">
                            {{ demandeStatut.label }}
                        </span>
                    </p>
                    <p class="mt-1">
                        <span class="font-medium">Type:</span>
                        <span :class="['px-2 py-1 rounded text-sm font-medium', demandeType.color]">
                            {{ demandeType.label }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Articles Table -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h2 class="text-lg font-semibold mb-4">Articles</h2>

                <template v-if="$page.props.errors.lignesSortie">
                    <p  v-for="error in $page.props.errors.lignesSortie" :key="error" class="text-sm text-red-600 mb-3">
                        {{ error }}
                    </p>
                </template>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Article</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Quantité</th>
                                <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in sortie.articles" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">{{ item.designation }}</td>
                                <td class="px-6 py-4 text-center">{{ item.quantite }}</td>
                                <td class="px-6 py-4 text-center">{{ item.quantite_stock }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Approval / Rejection -->
        <form class="mt-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire (optionnel)</label>
                <textarea
                    v-model="approveForm.commentaire_validation"
                    class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    rows="2"
                    placeholder="Ex: Vérification du stock"
                ></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <button
                    type="button"
                    @click="approveModalRef.close()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                    Annuler
                </button>
                <button
                    type="button"
                    @click="reject"
                    :disabled="approveForm.processing"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                >
                    Rejeter
                </button>
                <button
                    type="button"
                    @click="approve"
                    :disabled="approveForm.processing"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                    Valider
                </button>
            </div>
        </form>
    </Modal>
</AuthenticatedLayout>
</template>
