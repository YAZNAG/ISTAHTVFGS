<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    EyeIcon, PlusIcon, ClipboardDocumentListIcon, MagnifyingGlassIcon,
    PencilIcon, TrashIcon, DocumentTextIcon, DocumentDuplicateIcon, ArrowPathIcon, RectangleStackIcon,
} from '@heroicons/vue/24/outline';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
    fiches: Object,
    filters: Object,
});

const filters = ref({ search: props.filters.search || '' });

function resetFilters() {
    filters.value = { search: '' };
    router.get(route('fiches-techniques.collectivite'));
}
function applyFilters() {
    router.get(route('fiches-techniques.collectivite'), filters.value, { preserveState: true, replace: true });
}

const showDeleteModal = ref(false);
const ficheToDelete = ref(null);
function openDeleteModal(id) { ficheToDelete.value = id; showDeleteModal.value = true; }
function deleteFiche() {
    router.delete(route('fiches-techniques.destroy', ficheToDelete.value), {
        onSuccess: () => (showDeleteModal.value = false),
    });
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Fiches Techniques Collectivité" />

        <section class="space-y-5">

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
                            <RectangleStackIcon class="h-6 w-6" />
                            Fiches techniques collectivité
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Recettes de restauration collective — elles composent les menus collectivité.
                        </p>
                    </div>
                    <ModalLink v-if="can('create_ficheTechniques')" :href="route('fiches-techniques.create')" class="ui-button ui-button-primary">
                        <PlusIcon class="mr-1.5 h-4 w-4" /> Nouvelle fiche
                    </ModalLink>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex flex-col gap-4 md:flex-row md:items-end">
                    <div class="flex-1">
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
                        <div class="relative">
                            <input v-model="filters.search" type="text" placeholder="Plat, catégorie ou responsable…" class="ui-input w-full pl-9" @keyup.enter="applyFilters" />
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="ui-button ui-button-ghost" @click="resetFilters"><ArrowPathIcon class="mr-1.5 h-4 w-4" /> Réinitialiser</button>
                        <button type="button" class="ui-button ui-button-primary" @click="applyFilters"><MagnifyingGlassIcon class="mr-1.5 h-4 w-4" /> Rechercher</button>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ClipboardDocumentListIcon class="h-5 w-5 text-istaht-blue" />
                        <h3 class="font-bold text-istaht-navy">Liste des fiches</h3>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">{{ fiches?.meta?.total ?? 0 }} fiche(s)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">#</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Catégorie</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Plat</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Responsable</th>
                                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Effectif</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Créé le</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="fiche in fiches.data" :key="fiche.id" class="transition hover:bg-slate-50">
                                <td class="px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">#{{ fiche.id }}</td>
                                <td class="px-5 py-3.5 text-sm text-slate-600">{{ fiche.repas }}</td>
                                <td class="px-5 py-3.5 text-sm font-semibold text-slate-700">{{ fiche.plat }}</td>
                                <td class="px-5 py-3.5 text-sm text-slate-600">{{ fiche.responsable }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">{{ fiche.effectif }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-500">{{ formatDate(fiche.created_at) }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <ModalLink v-if="can('show_ficheTechniques')" :href="route('fiches-techniques.show', fiche.id)"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue" title="Voir détails">
                                            <EyeIcon class="h-5 w-5" />
                                        </ModalLink>
                                        <ModalLink v-if="can('create_ficheTechniques')" :href="route('fiches-techniques.duplicate', fiche.id)"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-amber-50 hover:text-istaht-amber" title="Dupliquer">
                                            <DocumentDuplicateIcon class="h-5 w-5" />
                                        </ModalLink>
                                        <ModalLink v-if="can('edit_ficheTechniques')" :href="route('fiches-techniques.edit', fiche.id)"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue" title="Modifier">
                                            <PencilIcon class="h-5 w-5" />
                                        </ModalLink>
                                        <a v-if="can('pdf_ficheTechniques')" :href="route('fiches-techniques.export', fiche.id)" target="_blank"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-purple-50 hover:text-purple-600" title="Télécharger PDF">
                                            <DocumentTextIcon class="h-5 w-5" />
                                        </a>
                                        <button v-if="can('delete_ficheTechniques')" class="rounded-md p-1.5 text-slate-500 transition hover:bg-red-50 hover:text-istaht-red" title="Supprimer" @click="openDeleteModal(fiche.id)">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="fiches.data.length === 0" class="py-14 text-center">
                    <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-slate-300" />
                    <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucune fiche trouvée</h3>
                    <p class="mt-1 text-sm text-slate-500">Créez votre première fiche technique collectivité.</p>
                    <div class="mt-5">
                        <ModalLink v-if="can('create_ficheTechniques')" :href="route('fiches-techniques.create')" class="ui-button ui-button-primary">
                            <PlusIcon class="mr-1.5 h-4 w-4" /> Nouvelle fiche
                        </ModalLink>
                    </div>
                </div>

                <div v-if="fiches?.meta?.links && fiches.meta.last_page > 1" class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row">
                    <p class="text-sm text-slate-500">Affichage de <strong class="text-istaht-navy">{{ fiches.meta.from }}</strong> à <strong class="text-istaht-navy">{{ fiches.meta.to }}</strong> sur <strong class="text-istaht-navy">{{ fiches.meta.total }}</strong> fiches</p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in fiches.meta.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" :class="['rounded-md px-3 py-1.5 text-sm font-semibold transition', link.active ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100']" v-html="link.label" />
                            <span v-else class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm font-semibold text-slate-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <ConfirmationModal :show="showDeleteModal" title="Supprimer la fiche"
            message="Êtes-vous sûr de vouloir supprimer cette fiche ?" :onConfirm="deleteFiche" @close="showDeleteModal = false" />
    </AuthenticatedLayout>
</template>
