<script setup>
import Dump from '@/Components/Dump.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    EyeIcon,
    PencilIcon,
    PlusIcon,
    TrashIcon,
    CheckCircleIcon,
    XCircleIcon,
    UserIcon,
} from '@heroicons/vue/24/outline'
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import { usePermission } from '@/Utils/permission';
// import Modal from '@/Components/Modal.vue';

const { can } = usePermission();

const props = defineProps({ users: Object, filters: Object })

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

// Actions
function viewUser(user) {
    alert(`Voir profil de ${user.name}`)
}

function editUser(user) {
    alert(`Modifier ${user.name}`)
}

function toggleStatus(user) {
    user.status = !user.status
}

function deleteUser(user) {
    if (confirm(`Supprimer ${user.name} ?`)) {
        users.value = users.value.filter((u) => u.id !== user.id)
    }
}

function createUser() {
    alert('Créer un nouvel utilisateur')
}

const filters = ref({ search: props.filters.search || '' })

function resetFilters() {
    filters.value.search = ''
    router.get(route('users.index')) // reset to full list
}

function applyFilters() {
    if (filters.value.search.trim() === '') {
        router.get(route('users.index'))
    } else {
        router.get(route('users.index'), { search: filters.value.search })
    }
}

</script>
<template>
    <AuthenticatedLayout>
        <Head title="Gestion des utilisateurs" />

        <div class="space-y-6">
            <!-- En-tête avec statistiques -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Utilisateurs</h1>
                        <p class="text-blue-100 text-lg opacity-90">Gestion complète des utilisateurs</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <ModalLink
                            v-if="can('create_utilisateurs')"
                            :href="route('users.create')"
                            class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl"
                        >
                            <PlusIcon class="h-5 w-5" />
                            Ajouter un utilisateur
                        </ModalLink>
                        <!-- <Link
                            class="bg-blue-500 text-white px-6 py-3 rounded-xl hover:bg-blue-400 flex items-center justify-center gap-3 transition-all duration-200 font-semibold border border-blue-400"
                        >
                            <DocumentArrowDownIcon class="h-5 w-5" />
                            Exporter
                        </Link> -->
                    </div>
                </div>
            </div>
        </div>


        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher un utilisateur</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Nom ou email..."
                        class="w-full border border-gray-300 rounded-lg p-2"
                    />
                </div>
                <div class="flex space-x-2 justify-end">
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Réinitialiser
                    </button>
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Rechercher
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table -->

        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créé le</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                        <!-- ID -->
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ user.id }}
                        </td>

                        <!-- Nom -->
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ user.name }}</div>
                        </td>

                        <!-- Email -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-700">{{ user.email }}</div>
                        </td>

                        <!-- Rôle -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                v-for="role in user.roles"
                                :key="role.id"  
                                class='px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800'
                            >
                                {{ role.name }}
                            </span>
                        </td>

                        <!-- Statut -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    user.status
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                ]"
                            >
                                {{ user.status ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>

                        <!-- Créé le -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(user.created_at) }}
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <!-- Voir -->
                                <ModalLink
                                    v-if="can('show_utilisateurs')"
                                    :href="route('users.show', user.id)"
                                    class="text-blue-600 hover:text-blue-900 p-1"
                                    title="Voir détails"
                                >
                                    <EyeIcon class="h-4 w-4" />
                                </ModalLink>

                                <!-- Modifier -->
                                <ModalLink
                                    v-if="can('edit_utilisateurs')"
                                    :href="route('users.edit', user.id)"
                                    class="text-orange-600 hover:text-orange-900 p-1"
                                    title="Modifier"
                                >
                                    <PencilIcon class="h-4 w-4" />
                                </ModalLink>

                                <!-- Activer / Désactiver -->
                                <Link
                                    v-if="can('edit_utilisateurs')"
                                    as="button"
                                    method="patch"
                                    :href="route('users.toggle-status', user.id)"
                                    :class="[
                                        'p-1',
                                        user.status
                                            ? 'text-gray-500 hover:text-gray-700'
                                            : 'text-green-600 hover:text-green-900'
                                    ]"
                                    :title="user.status ? 'Désactiver' : 'Activer'"
                                >
                                    <component
                                        :is="user.status ? XCircleIcon : CheckCircleIcon"
                                        class="h-4 w-4"
                                    />
                                </Link>

                                <!-- Supprimer -->
                                <!-- <button
                                    @click="deleteUser(user)"
                                    class="text-red-600 hover:text-red-900 p-1"
                                    title="Supprimer"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button> -->
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Message vide -->
        <div v-if="users.data.length === 0" class="text-center py-12">
            <div class="text-gray-500">
                <UserIcon class="mx-auto h-12 w-12" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun utilisateur trouvé</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Commencez par créer votre premier utilisateur.
                </p>
                <div class="mt-6">
                    <button 
                        @click="createUser"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Nouvel utilisateur
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="users.links && users.links.length > 1" class="bg-white px-6 py-3 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    Affichage de {{ users.from }} à {{ users.to }} sur {{ users.total }} résultats
                </div>
                <div class="flex space-x-2">
                    <template v-for="link in users.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-3 py-1 rounded-lg text-sm font-medium',
                                link.active 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            :class="[
                                'px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>

    </AuthenticatedLayout>
</template>
