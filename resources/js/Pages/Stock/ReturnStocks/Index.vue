<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    EyeIcon,
    PlusIcon,
    ClipboardDocumentListIcon,
    MagnifyingGlassIcon,
    TrashIcon,
    CubeIcon,
    ArrowPathIcon,
    ArrowUturnLeftIcon,
} from '@heroicons/vue/24/outline';
import { Link, router, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
    returns: Object,
    filters: Object,
    articles: { type: Array, default: () => [] },
});

function formatDate(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

const filters = ref({
    search: props.filters.search || '',
    article_id: props.filters.article_id || '',
    date_debut: props.filters.date_debut || '',
    date_fin: props.filters.date_fin || '',
});

function applyFilters() {
    router.get(route('returns.index'), filters.value, { preserveState: true, replace: true });
}

function resetFilters() {
    filters.value = { search: '', article_id: '', date_debut: '', date_fin: '' };
    router.get(route('returns.index'));
}

const showDelete = ref(false);
const deleteId = ref(null);

function openDeleteModal(id) {
    deleteId.value = id;
    showDelete.value = true;
}

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

        <section class="space-y-5">

            <!-- ═══ En-tête ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
                            <ArrowUturnLeftIcon class="h-6 w-6" />
                            Retours de stock
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Gérez les retours d'articles non utilisés — chaque retour ré-entre les quantités en stock.
                        </p>
                    </div>
                    <ModalLink
                        v-if="can('returns_stocks')"
                        :href="route('returns.create')"
                        class="ui-button ui-button-primary"
                    >
                        <PlusIcon class="mr-1.5 h-4 w-4" />
                        Nouveau retour
                    </ModalLink>
                </div>
            </div>

            <!-- ═══ Filtres ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
                        <div class="relative">
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="N° retour ou motif..."
                                class="ui-input w-full pl-9"
                                @keyup.enter="applyFilters"
                            />
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Article</label>
                        <select v-model="filters.article_id" class="ui-input w-full">
                            <option value="">Tous les articles</option>
                            <option v-for="article in articles" :key="article.id" :value="article.id">
                                {{ article.designation }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date début</label>
                        <input v-model="filters.date_debut" type="date" class="ui-input w-full" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date fin</label>
                        <input v-model="filters.date_fin" type="date" class="ui-input w-full" />
                    </div>
                </div>

                <div class="mt-4 flex flex-col justify-end gap-2 sm:flex-row">
                    <button type="button" class="ui-button ui-button-ghost" @click="resetFilters">
                        <ArrowPathIcon class="mr-1.5 h-4 w-4" />
                        Réinitialiser
                    </button>
                    <button type="button" class="ui-button ui-button-primary" @click="applyFilters">
                        <MagnifyingGlassIcon class="mr-1.5 h-4 w-4" />
                        Rechercher
                    </button>
                </div>
            </div>

            <!-- ═══ Tableau ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ClipboardDocumentListIcon class="h-5 w-5 text-istaht-blue" />
                        <h3 class="font-bold text-istaht-navy">Historique des retours</h3>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                        {{ returns?.meta?.total ?? 0 }} retour(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Numéro</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Retourné par</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Reçu par</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Motif</th>
                                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Articles</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="ret in returns.data" :key="ret.id" class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">
                                    {{ ret.numero }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                                    {{ formatDate(ret.date) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-700">
                                    {{ ret.returner_name || '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                                    {{ ret.receiver_name || '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-600">
                                    <div class="max-w-xs truncate" :title="ret.motif">{{ ret.motif || '—' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                                        <CubeIcon class="h-3.5 w-3.5" />
                                        {{ ret.articles_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <ModalLink
                                            :href="route('returns.show', ret.id)"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue"
                                            title="Voir détails"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                        </ModalLink>
                                        <button
                                            v-if="can('returns_stocks')"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-red-50 hover:text-istaht-red"
                                            title="Supprimer"
                                            @click="openDeleteModal(ret.id)"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Vide -->
                <div v-if="returns.data.length === 0" class="py-14 text-center">
                    <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-slate-300" />
                    <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun retour trouvé</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ filters.search || filters.article_id || filters.date_debut || filters.date_fin
                            ? 'Aucun résultat ne correspond à vos critères.'
                            : 'Commencez par enregistrer un nouveau retour.' }}
                    </p>
                    <div class="mt-5">
                        <ModalLink
                            v-if="can('returns_stocks')"
                            :href="route('returns.create')"
                            class="ui-button ui-button-primary"
                        >
                            <PlusIcon class="mr-1.5 h-4 w-4" />
                            Nouveau retour
                        </ModalLink>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="returns?.meta?.links && returns.meta.last_page > 1"
                    class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row"
                >
                    <p class="text-sm text-slate-500">
                        Affichage de <strong class="text-istaht-navy">{{ returns.meta.from }}</strong>
                        à <strong class="text-istaht-navy">{{ returns.meta.to }}</strong>
                        sur <strong class="text-istaht-navy">{{ returns.meta.total }}</strong> retours
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="lnk in returns.meta.links" :key="lnk.label">
                            <Link
                                v-if="lnk.url"
                                :href="lnk.url"
                                :class="[
                                    'rounded-md px-3 py-1.5 text-sm font-semibold transition',
                                    lnk.active ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100'
                                ]"
                                v-html="lnk.label"
                            />
                            <span
                                v-else
                                class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm font-semibold text-slate-300"
                                v-html="lnk.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <ConfirmationModal
            :show="showDelete"
            title="Supprimer le retour"
            message="Êtes-vous sûr de vouloir supprimer ce retour ? Les quantités ré-entrées en stock seront retirées. Cette action est irréversible."
            :onConfirm="deleteReturn"
            @close="showDelete = false"
        />
    </AuthenticatedLayout>
</template>
