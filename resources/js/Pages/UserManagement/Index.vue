<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    EyeIcon, PencilIcon, PlusIcon, CheckCircleIcon, XCircleIcon,
    UserIcon, MagnifyingGlassIcon, ArrowPathIcon, UsersIcon,
} from '@heroicons/vue/24/outline'
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({ users: Object, filters: Object })

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' })
}

function initials(name) {
    return (name || '?').trim().charAt(0).toUpperCase()
}

const filters = ref({ search: props.filters.search || '' })

function resetFilters() {
    filters.value.search = ''
    router.get(route('users.index'))
}
function applyFilters() {
    router.get(route('users.index'), filters.value, { preserveState: true, replace: true })
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Gestion des utilisateurs" />

        <section class="space-y-5">

            <!-- ═══ En-tête ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
                            <UsersIcon class="h-6 w-6" />
                            Utilisateurs
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Gérez les comptes, leurs rôles et leur statut d'accès.</p>
                    </div>
                    <ModalLink v-if="can('create_utilisateurs')" :href="route('users.create')" class="ui-button ui-button-primary">
                        <PlusIcon class="mr-1.5 h-4 w-4" /> Ajouter un utilisateur
                    </ModalLink>
                </div>
            </div>

            <!-- ═══ Filtres ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex flex-col gap-4 md:flex-row md:items-end">
                    <div class="flex-1">
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
                        <div class="relative">
                            <input v-model="filters.search" type="text" placeholder="Nom ou email…" class="ui-input w-full pl-9" @keyup.enter="applyFilters" />
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="ui-button ui-button-ghost" @click="resetFilters"><ArrowPathIcon class="mr-1.5 h-4 w-4" /> Réinitialiser</button>
                        <button type="button" class="ui-button ui-button-primary" @click="applyFilters"><MagnifyingGlassIcon class="mr-1.5 h-4 w-4" /> Rechercher</button>
                    </div>
                </div>
            </div>

            <!-- ═══ Tableau ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <UsersIcon class="h-5 w-5 text-istaht-blue" />
                        <h3 class="font-bold text-istaht-navy">Liste des utilisateurs</h3>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">{{ users?.meta?.total ?? 0 }} utilisateur(s)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Utilisateur</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Email</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Rôle</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Statut</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Créé le</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users.data" :key="user.id" class="transition hover:bg-slate-50">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-istaht-navy text-sm font-bold text-white">
                                            {{ initials(user.name) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ user.name }}</p>
                                            <p class="text-xs text-slate-400">#{{ user.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">{{ user.email }}</td>
                                <td class="px-5 py-3.5">
                                    <span v-for="role in user.roles" :key="role.id" class="mr-1 inline-block rounded-full bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-700 ring-1 ring-purple-100">
                                        {{ role.name }}
                                    </span>
                                    <span v-if="!user.roles || user.roles.length === 0" class="text-xs text-slate-300">—</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="user.status ? 'bg-green-50 text-istaht-green ring-1 ring-green-100' : 'bg-red-50 text-istaht-red ring-1 ring-red-100'">
                                        {{ user.status ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-500">{{ formatDate(user.created_at) }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <ModalLink v-if="can('show_utilisateurs')" :href="route('users.show', user.id)" class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue" title="Voir détails">
                                            <EyeIcon class="h-5 w-5" />
                                        </ModalLink>
                                        <ModalLink v-if="can('edit_utilisateurs')" :href="route('users.edit', user.id)" class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue" title="Modifier">
                                            <PencilIcon class="h-5 w-5" />
                                        </ModalLink>
                                        <Link
                                            v-if="can('edit_utilisateurs')"
                                            as="button"
                                            method="patch"
                                            :href="route('users.toggle-status', user.id)"
                                            preserve-scroll
                                            class="rounded-md p-1.5 transition"
                                            :class="user.status ? 'text-slate-500 hover:bg-red-50 hover:text-istaht-red' : 'text-slate-500 hover:bg-green-50 hover:text-istaht-green'"
                                            :title="user.status ? 'Désactiver' : 'Activer'"
                                        >
                                            <component :is="user.status ? XCircleIcon : CheckCircleIcon" class="h-5 w-5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="users.data.length === 0" class="py-14 text-center">
                    <UserIcon class="mx-auto h-12 w-12 text-slate-300" />
                    <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun utilisateur trouvé</h3>
                    <p class="mt-1 text-sm text-slate-500">Ajustez la recherche ou créez un nouvel utilisateur.</p>
                    <div class="mt-5">
                        <ModalLink v-if="can('create_utilisateurs')" :href="route('users.create')" class="ui-button ui-button-primary">
                            <PlusIcon class="mr-1.5 h-4 w-4" /> Ajouter un utilisateur
                        </ModalLink>
                    </div>
                </div>

                <div v-if="users?.meta?.links && users.meta.last_page > 1" class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row">
                    <p class="text-sm text-slate-500">Affichage de <strong class="text-istaht-navy">{{ users.meta.from }}</strong> à <strong class="text-istaht-navy">{{ users.meta.to }}</strong> sur <strong class="text-istaht-navy">{{ users.meta.total }}</strong> utilisateurs</p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in users.meta.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" :class="['rounded-md px-3 py-1.5 text-sm font-semibold transition', link.active ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100']" v-html="link.label" />
                            <span v-else class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm font-semibold text-slate-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
