<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue';
// import { Modal } from '@inertiaui/modal-vue';

const props = defineProps({
    user: Object
});

const showUserModal = ref(null)

function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}


</script>

<template>
    <Modal ref="showUserModal" class="max-w-md w-full z-50">
        <!-- Header -->
        <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Détails de l'utilisateur</h2>
        </div>

        <!-- Body -->
        <div class="space-y-4">
            <!-- User Info Card -->
            <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-lg">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ user.name }}</h3>
                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Role & Status -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center shadow-sm">
                    <span class="block text-xs text-gray-500">Rôle</span>
                    <span v-for="role in roles" :key="role.id" class= 'mt-1 inline-block px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800'>
                        {{ role.name }}
                    </span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center shadow-sm">
                    <span class="block text-xs text-gray-500">Statut</span>
                    <span :class="[
                        'mt-1 inline-block px-3 py-1 rounded-full text-sm font-medium',
                        user.status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                    ]">
                        {{ user.status ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
            </div>

            <!-- Email Verified & Created At -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center shadow-sm">
                    <span class="block text-xs text-gray-500">Email vérifié</span>
                    <span :class="[
                        'mt-1 inline-block px-3 py-1 rounded-full text-sm font-medium',
                        user.email_verified_at ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'
                    ]">
                        {{ user.email_verified_at ? 'Oui' : 'Non' }}
                    </span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center shadow-sm">
                    <span class="block text-xs text-gray-500">Créé le</span>
                    <span class="mt-1 inline-block text-sm text-gray-700">
                        {{ formatDate(user.created_at) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end mt-6">
            <button type="button" @click="showUserModal.close"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Fermer
            </button>
        </div>
    </Modal>
</template>