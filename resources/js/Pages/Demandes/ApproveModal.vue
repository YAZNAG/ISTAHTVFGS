<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Modal } from '@inertiaui/modal-vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    UserIcon,
    PencilIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';
import { getDemandeStatutInfo } from '@/Utils/labels.js';

const props = defineProps({
    demande: Object // passed from controller with relations: articles
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

// Compute a user-friendly status label
const statusLabel = computed(() => {
    const status = props.demande.statut;
    return {
        en_attente: 'En attente',
        approuvee: 'Approuvée',
        rejetee: 'Rejetée',
        annulee: 'Annulée'
    }[status] || status;
});

// Compute badge colors
const statusClasses = computed(() => {
    const status = props.demande.statut;
    return {
        en_attente: 'bg-yellow-100 text-yellow-700',
        approuvee: 'bg-green-100 text-green-700',
        rejetee: 'bg-red-100 text-red-700',
        annulee: 'bg-gray-100 text-gray-600'
    }[status] || 'bg-gray-100 text-gray-600';
});

const approveForm = useForm({
    commentaire_validation: '',
});

const approve = () => {
    approveForm.put(route('demandes.approve', props.demande), {
        preserveScroll: true,
        onSuccess: () => {
            approveForm.reset();
            approveModalRef.value.close();
        },
    });
}

const reject = () => {
    approveForm.put(route('demandes.reject', props.demande), {
        preserveScroll: true,
        onSuccess: () => {
            approveForm.reset();
            approveModalRef.value.close();
        },
    });
}

const statusInfo = ref(getDemandeStatutInfo(props.demande.statut));

</script>

<template>
<AuthenticatedLayout>
    <Head title="Détails de la demande" />

<Modal ref="approveModalRef">
       <!-- Header -->
    <div class="mb-2">
      <h2 class="text-lg font-semibold text-gray-900">Approuver la demande</h2>
      <p class="text-sm text-gray-500 mt-1">
        Confirmez que vous souhaitez approuver cette demande. Vous pouvez ajouter un commentaire optionnel.
      </p>
    </div>

    <!-- Motif & Status -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold mb-2">Motif</h2>
                    <p class="text-gray-700">{{ demande.motif || '—————' }}</p>
                </div>
                <div>
                    <span
                        :class="['px-3 py-1 rounded-full text-sm font-medium', statusInfo.color]"
                    >
                        {{ statusInfo.label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Validation Information -->
        <div
            v-if="demande.statut === 'approuvee' || demande.statut === 'rejetee'"
            class="bg-white p-6 rounded-lg shadow-sm border"
        >
            <h2 class="text-lg font-semibold mb-4">Informations de validation</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 text-sm">Validé par :</p>
                    <p class="text-gray-800 font-medium">{{ demande.validateur || '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Date de validation :</p>
                    <p class="text-gray-800 font-medium">{{ demande.date_validation ? formatDate(demande.date_validation) : '—' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-gray-500 text-sm">Commentaires :</p>
                    <p class="text-gray-800 font-medium">{{ demande.commentaire_validation || '—' }}</p>
                </div>
            </div>
        </div>

        <template v-if="$page.props.errors.articlesError">
            <p  v-for="error in $page.props.errors.articlesError" :key="error" class="text-sm text-red-600 mb-3">
                {{ error }}
            </p>
        </template>
        
        <!-- Articles Table -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <h2 class="text-lg font-semibold mb-4">Articles demandés</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Article</th>
                            <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Quantité demandée</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in demande.articles" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ item.designation }}</td>
                            <td class="px-6 py-4 text-center">{{ item.quantite_demandee }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form class="mt-8">
      <!-- Comment -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Commentaire (optionnel)
          </label>
          <textarea
            v-model="approveForm.commentaire_validation"
            class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            rows="2"
            placeholder="Ex: Besoin de fournitures de bureau"
          ></textarea>
          <p class="text-red-600 font-sm" v-if="approveForm.errors.commentaire_validation">{{ approveForm.errors.commentaire_validation }}</p>
        </div>


      <!-- Footer -->
    <div>
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
          Rejetter
        </button>
        <button
          type="button"
          @click="approve"
          :disabled="approveForm.processing"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
        >
          Approuver
        </button>
      </div>
    </div>

      
    </form>
</Modal>
</AuthenticatedLayout>
</template>
