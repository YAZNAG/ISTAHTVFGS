<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    EyeIcon,
    PlusIcon,
    ClipboardDocumentListIcon,
    MagnifyingGlassIcon,
    PencilIcon,
    TrashIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
    restaurants: Object, // paginated restaurants
    filters: Object,
});

const filters = ref({
    search: props.filters.search || '',
});

function resetFilters() {
    filters.value = { search: '' };
    router.get(route('restaurants.index'));
}

function applyFilters() {
    router.get(route('restaurants.index'), filters.value);
}

const showDeleteModal = ref(false);
const restaurantToDelete = ref(null);

function openDeleteModal(id) {
    restaurantToDelete.value = id;
    showDeleteModal.value = true;
}

function deleteRestaurant() {
    router.delete(route('restaurants.destroy', restaurantToDelete.value), {
        onFinish: () => {
            restaurantToDelete.value = null;
        }
    });

    restaurantToDelete.value = null;
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>

        <Head title="Les Restaurants" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Les Restaurants</h1>
                        <p class="text-blue-100 text-lg opacity-90">Liste et gestion des restaurants </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <ModalLink :href="route('restaurants.create')"
                            v-if="can('create_restaurants')"
                            class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl">
                            <PlusIcon class="h-5 w-5" />
                            Nouvelle Restaurant
                        </ModalLink>
                    </div>
                </div>
            </div>

        </div>
        <!-- Search Filter -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Rechercher</h3>

            <div class="flex justify-between">
                <div class="w-1/3">
                    <!-- Search -->
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom ou plat, ou autre...</label>
                    <div class="relative w-full">
                        <input v-model="filters.search" type="text" placeholder="Tapez le nom ..."
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                        <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                    </div>
                </div>

                <div class="mt-5 flex flex-col sm:flex-row justify-end gap-3">
                    <button @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all">
                        Réinitialiser
                    </button>

                    <button @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center gap-2">
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Rechercher
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chef de
                                Restaurant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Effectif</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créé le</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="restaurant in restaurants.data" :key="restaurant.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ restaurant.id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ restaurant.nom }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ restaurant.responsable }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ restaurant.effectif }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(restaurant.created_at) }}</td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <ModalLink :href="route('restaurants.show', restaurant.id)"
                                        v-if="can('show_restaurants')"
                                        class="text-blue-600 hover:text-blue-900 p-1" title="Voir détails">
                                        <EyeIcon class="h-5 w-5" />
                                    </ModalLink>

                                    <ModalLink :href="route('restaurants.edit', restaurant.id)"
                                        v-if="can('edit_restaurants')"
                                        class="text-blue-600 hover:text-blue-900 p-1" title="Modifier">
                                        <PencilIcon class="h-5 w-5" />
                                    </ModalLink>

                                    <!-- <a
                                        :href="route('restaurants.export', restaurant.id)"
                                        target="_blank"
                                        class="text-purple-600 hover:text-purple-900 p-1"
                                        title="Télécharger PDF"
                                    >
                                        <DocumentTextIcon class="h-4 w-4" />
                                    </a> -->

                                    <button @click="openDeleteModal(restaurant.id)"
                                        v-if="can('delete_restaurants')"
                                        class="text-red-600 hover:text-red-900 p-1" title="Supprimer">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty Message -->
            <div v-if="restaurants.data.length === 0" class="text-center py-12">
                <div class="text-gray-500">
                    <ClipboardDocumentListIcon class="mx-auto h-12 w-12" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune restaurant trouvée</h3>
                    <p class="mt-1 text-sm text-gray-500">Commencez par créer votre première restaurant.</p>
                    <div class="mt-6">
                        <ModalLink :href="route('restaurants.create')"
                            v-if="can('create_restaurants')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Nouvelle restaurant
                        </ModalLink>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="restaurants.links && restaurants.links.length > 1"
                class="bg-white px-6 py-3 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Affichage de {{ restaurants.from }} à {{ restaurants.to }} sur {{ restaurants.total }} résultats
                    </div>
                    <div class="flex space-x-2">
                        <template v-for="link in restaurants.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" :class="[
                                'px-3 py-1 rounded-lg text-sm font-medium',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]" v-html="link.label" />
                            <span v-else
                                class="px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="showDeleteModal" title="Supprimer la restaurant"
            message="Êtes-vous sûr de vouloir supprimer cette restaurant ?" :onConfirm="deleteRestaurant"
            @close="showDeleteModal = false" />
    </AuthenticatedLayout>
</template>
