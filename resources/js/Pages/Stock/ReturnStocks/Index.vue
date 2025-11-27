<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    EyeIcon,
    PlusIcon,
    ClipboardDocumentListIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    TrashIcon,
    CubeIcon,
} from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { usePermission } from '@/Utils/permission';
import { Head } from '@inertiajs/vue3';

const { can } = usePermission();

const props = defineProps({
    returns: Object,        // paginated returns from server
    filters: Object,        // old filters
});

/* ---------- helpers ---------- */
function formatDate(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

/* ---------- filters ---------- */
const filters = ref({
    search: props.filters.search || '',
});

function applyFilters() {
    router.get(route('returns.index'), filters.value, { preserveState: true });
}
function resetFilters() {
    filters.value = { search: '' };
    router.get(route('returns.index'));
}

/* ---------- delete ---------- */
const showDelete = ref(false);
const deleteId = ref(null);
function openDeleteModal(id) { deleteId.value = id; showDelete.value = true; }
function deleteReturn() {
    router.delete(route('returns.destroy', deleteId.value), {
        preserveScroll: true,
        onSuccess: () => (showDelete.value = false),
    });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Retours" />

        <div class="space-y-6">
            <!-- ======  Header  ====== -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Les Retours</h1>
                        <p class="text-indigo-100 text-lg opacity-90">Gérez les retours d'articles non utilisés</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <ModalLink :href="route('returns.create')" class="bg-white text-indigo-600 px-6 py-3 rounded-xl hover:bg-indigo-50
                                   flex items-center justify-center gap-3 transition-all font-semibold shadow-lg">
                            <PlusIcon class="h-5 w-5" />
                            Nouveau Retour
                        </ModalLink>
                    </div>
                </div>
            </div>

            <!-- ======  Filtres  ====== -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Filtrer les retours</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- search -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                        <div class="relative">
                            <input v-model="filters.search" type="text" placeholder="N° retour ou motif…" class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2
                                       focus:ring-indigo-500 focus:border-indigo-500" />
                            <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-col sm:flex-row justify-end gap-3">
                    <button @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Réinitialiser
                    </button>
                    <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700
                                   flex items-center gap-2">
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Rechercher
                    </button>
                </div>
            </div>

            <!-- ======  Table  ====== -->
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Retourné par
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reçu par
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motif</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Articles
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="ret in returns.data" :key="ret.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ ret.numero }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                    {{ formatDate(ret.date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ ret.returner_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ ret.receiver_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ ret.motif }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <span
                                        class="bg-blue-100 border border-blue-400 text-blue-600 inline-flex gap-1 items-center px-2 py-1 rounded-full">
                                        <CubeIcon class="h-4 w-4" />
                                        {{ ret.articles_count }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <ModalLink :href="route('returns.show', ret.id)"
                                            class="text-indigo-600 hover:text-indigo-900" title="Voir détails">
                                            <EyeIcon class="h-5 w-5" />
                                        </ModalLink>

                                        <button @click="openDeleteModal(ret.id)" class="text-red-600 hover:text-red-900"
                                            title="Supprimer">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div v-if="returns.data.length === 0" class="text-center py-12">
                    <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun retour trouvé</h3>
                    <p class="mt-1 text-sm text-gray-500">Commencez par enregistrer un nouveau retour.</p>
                    <div class="mt-6">
                        <ModalLink :href="route('returns.create')"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Nouveau Retour
                        </ModalLink>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="returns.meta?.links?.length > 1" class="bg-white px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Affichage de {{ returns.from }} à {{ returns.to }} sur {{ returns.total }} résultats
                        </div>
                        <div class="flex space-x-2">
                            <template v-for="lnk in returns.meta.links" :key="lnk.label">
                                <Link v-if="lnk.url" :href="lnk.url" v-html="lnk.label"
                                    class="px-3 py-1 rounded-lg text-sm font-medium"
                                    :class="lnk.active ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" />
                                <span v-else
                                    class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                                    v-html="lnk.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======  Confirmation suppression  ====== -->
        <ConfirmationModal :show="showDelete" title="Supprimer le retour"
            message="Êtes-vous sûr de vouloir supprimer ce retour ? Cette action est irréversible."
            :onConfirm="deleteReturn" @close="showDelete = false" />
    </AuthenticatedLayout>
</template>