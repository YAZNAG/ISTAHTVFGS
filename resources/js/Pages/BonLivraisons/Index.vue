<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import {
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    TruckIcon,
    ArrowPathIcon,
    QueueListIcon,
    ClockIcon,
    CubeIcon,
    DocumentCheckIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';
import { getBonLivraisonInfo } from '@/Utils/labels';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const getStatutColor = (statut) => getBonLivraisonInfo(statut).color;
const getStatutLabel = (statut) => getBonLivraisonInfo(statut).label;

const props = defineProps({
    bonLivraisons: { type: Object },
    pendingLivraisons: { type: Array },
    filters: { type: Object, default: () => ({}) },
    magasiniers: { type: Array, default: () => ([]) }
});

const filters = ref({
    responsable_id: props.filters?.responsable_id || '',
    search: props.filters?.search || '',
});

const applyFilters = () => {
    router.get(route('bon-livraisons.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.value = { responsable_id: '', search: '' };
    router.get(route('bon-livraisons.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Bons de Livraison" />

        <section class="space-y-5">

            <!-- ═══ En-tête ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-istaht-navy">Bons de livraison</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Finalisez les livraisons en attente et consultez l'historique des bons livrés.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-2 text-center">
                            <p class="text-xs font-bold uppercase text-slate-400">En attente</p>
                            <p class="text-xl font-bold text-istaht-amber">{{ pendingLivraisons?.length || 0 }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-2 text-center">
                            <p class="text-xs font-bold uppercase text-slate-400">Livrés</p>
                            <p class="text-xl font-bold text-istaht-green">{{ bonLivraisons?.meta?.total ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Filtres ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
                        <div class="relative">
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="N° de bon ou fournisseur..."
                                class="ui-input w-full pl-9"
                                @keyup.enter="applyFilters"
                            />
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Magasinier</label>
                        <select v-model="filters.responsable_id" class="ui-input w-full">
                            <option value="">Tous les magasiniers</option>
                            <option v-for="magasinier in magasiniers" :key="magasinier.id" :value="magasinier.id">
                                {{ magasinier.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end justify-end gap-2">
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
            </div>

            <!-- ═══ Bons en attente ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ClockIcon class="h-5 w-5 text-istaht-amber" />
                        <h3 class="font-bold text-istaht-navy">Bons de livraison en attente</h3>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-istaht-amber ring-1 ring-amber-100">
                        {{ pendingLivraisons?.length || 0 }} bon(s)
                    </span>
                </div>

                <div v-if="pendingLivraisons?.length" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Numéro</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Fournisseur</th>
                                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Articles</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Statut</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="bon in pendingLivraisons" :key="bon.id" class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">
                                    {{ bon.numero }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-700">
                                    {{ bon.fournisseur }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                                        <CubeIcon class="h-3.5 w-3.5" />
                                        {{ bon.items_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span :class="['rounded-full px-2.5 py-1 text-xs font-bold', getStatutColor(bon.statut)]">
                                        {{ getStatutLabel(bon.statut) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                    <ModalLink
                                        v-if="can('validate_bonLivraisons')"
                                        :href="route('bon-livraisons.edit', bon.id)"
                                        class="ui-button ui-button-primary px-3 py-1.5 text-xs"
                                    >
                                        <TruckIcon class="mr-1 h-4 w-4" />
                                        Livrer
                                    </ModalLink>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="py-10 text-center">
                    <DocumentCheckIcon class="mx-auto h-12 w-12 text-istaht-green" />
                    <h3 class="mt-3 text-sm font-bold text-istaht-navy">Toutes les livraisons sont effectuées</h3>
                    <p class="mt-1 text-sm text-slate-500">Aucun bon en attente de livraison.</p>
                </div>
            </div>

            <!-- ═══ Historique ═══ -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <QueueListIcon class="h-5 w-5 text-istaht-blue" />
                        <h3 class="font-bold text-istaht-navy">Historique des bons de livraison</h3>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                        {{ bonLivraisons?.meta?.total ?? 0 }} résultat(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Numéro</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Fournisseur</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date livraison</th>
                                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Articles</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Statut</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="bon in bonLivraisons.data" :key="bon.id" class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">
                                    {{ bon.numero }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-700">
                                    {{ bon.fournisseur }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                                    {{ formatDate(bon.date_livraison) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                                        <CubeIcon class="h-3.5 w-3.5" />
                                        {{ bon.items_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span :class="['rounded-full px-2.5 py-1 text-xs font-bold', getStatutColor(bon.statut)]">
                                        {{ getStatutLabel(bon.statut) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            v-if="can('show_bonLivraisons')"
                                            :href="route('bon-livraisons.show', bon.id)"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue"
                                            title="Voir détails"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                        </Link>
                                        <a
                                            v-if="can('pdf_bonLivraisons')"
                                            :href="route('bon-livraisons.pdf', bon.id)"
                                            class="rounded-md p-1.5 text-slate-500 transition hover:bg-purple-50 hover:text-purple-600"
                                            title="Télécharger PDF"
                                            target="_blank"
                                        >
                                            <DocumentArrowDownIcon class="h-5 w-5" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Vide -->
                <div v-if="!bonLivraisons?.data || bonLivraisons.data.length === 0" class="py-14 text-center">
                    <TruckIcon class="mx-auto h-12 w-12 text-slate-300" />
                    <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun bon de livraison livré</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ filters.search ? 'Aucun résultat ne correspond à vos critères.' : "L'historique des livraisons finalisées apparaîtra ici." }}
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="bonLivraisons?.meta?.links && bonLivraisons.meta.last_page > 1"
                    class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row"
                >
                    <p class="text-sm text-slate-500">
                        Affichage de <strong class="text-istaht-navy">{{ bonLivraisons.meta.from }}</strong>
                        à <strong class="text-istaht-navy">{{ bonLivraisons.meta.to }}</strong>
                        sur <strong class="text-istaht-navy">{{ bonLivraisons.meta.total }}</strong> bons
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in bonLivraisons.meta.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'rounded-md px-3 py-1.5 text-sm font-semibold transition',
                                    link.active ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm font-semibold text-slate-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
