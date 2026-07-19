<script setup>
import { ref } from 'vue';
import { Modal } from '@inertiaui/modal-vue';
import { UserIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ user: Object });

const showUserModal = ref(null)

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}

function initials(name) { return (name || '?').trim().charAt(0).toUpperCase() }
</script>

<template>
    <Modal ref="showUserModal" class="w-full max-w-md">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h2 class="flex items-center gap-2 text-lg font-bold text-istaht-navy">
                <UserIcon class="h-5 w-5" />
                Détails de l'utilisateur
            </h2>
        </div>

        <div class="space-y-4">
            <!-- Carte identité -->
            <div class="flex items-center gap-4 rounded-lg border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-istaht-navy text-xl font-bold text-white">
                    {{ initials(user.name) }}
                </div>
                <div class="min-w-0">
                    <h3 class="truncate text-lg font-bold text-istaht-navy">{{ user.name }}</h3>
                    <p class="truncate text-sm text-slate-500">{{ user.email }}</p>
                </div>
            </div>

            <!-- Rôle & statut -->
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <span class="text-xs font-bold uppercase text-slate-400">Rôle</span>
                    <div class="mt-1">
                        <span v-for="role in user.roles" :key="role.id" class="inline-block rounded-full bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-700 ring-1 ring-purple-100">
                            {{ role.name }}
                        </span>
                        <span v-if="!user.roles || user.roles.length === 0" class="text-sm text-slate-300">—</span>
                    </div>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <span class="text-xs font-bold uppercase text-slate-400">Statut</span>
                    <div class="mt-1">
                        <span class="inline-block rounded-full px-2.5 py-1 text-xs font-bold" :class="user.status ? 'bg-green-50 text-istaht-green ring-1 ring-green-100' : 'bg-red-50 text-istaht-red ring-1 ring-red-100'">
                            {{ user.status ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Vérif email & date -->
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <span class="text-xs font-bold uppercase text-slate-400">Email vérifié</span>
                    <p class="mt-1 text-sm font-semibold" :class="user.email_verified_at ? 'text-istaht-green' : 'text-slate-400'">
                        {{ user.email_verified_at ? 'Oui' : 'Non' }}
                    </p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <span class="text-xs font-bold uppercase text-slate-400">Créé le</span>
                    <p class="mt-1 text-sm font-semibold text-slate-700">{{ formatDate(user.created_at) }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
            <button type="button" @click="showUserModal.close()" class="ui-button ui-button-primary">Fermer</button>
        </div>
    </Modal>
</template>
