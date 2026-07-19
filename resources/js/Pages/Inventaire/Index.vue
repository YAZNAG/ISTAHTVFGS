<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  MagnifyingGlassIcon, InboxIcon, PencilIcon, LockOpenIcon, DocumentTextIcon,
  ArrowPathIcon, ClipboardDocumentCheckIcon, PlusIcon, BoltIcon,
} from '@heroicons/vue/24/outline';
import { Link, router, Head } from '@inertiajs/vue3';
import { ModalLink } from '@inertiaui/modal-vue';
import { ref } from 'vue';
import CreateInventaireModal from './CreateInventaireModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
  inventaires: Object,
  filters: Object,
});

const filters = ref({
  semaine: props.filters.semaine ?? '',
  statut: props.filters.statut ?? '',
});

function applyFilters() {
  router.get(route('inventaires.index'), filters.value, { preserveState: true, replace: true });
}
function resetFilters() {
  filters.value = { semaine: '', statut: '' };
  router.get(route('inventaires.index'));
}

const showUnlockModal = ref(false);
const inventaireIdToUnlock = ref(null);
function openUnlockModal(id) { inventaireIdToUnlock.value = id; showUnlockModal.value = true; }
function unlock() {
  router.patch(route('inventaires.unlock', inventaireIdToUnlock.value), {
    preserveScroll: true,
    onSuccess: () => { showUnlockModal.value = false; inventaireIdToUnlock.value = null; },
  });
}

function progressPercent(p) {
  if (!p || !p.includes('/')) return 0;
  const [done, total] = p.split('/').map(Number);
  return total > 0 ? Math.round((done / total) * 100) : 0;
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Inventaires hebdomadaires" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <ClipboardDocumentCheckIcon class="h-6 w-6" />
              Inventaires hebdomadaires
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Un inventaire par semaine — seuls les articles à stock théorique positif sont listés automatiquement.
            </p>
          </div>
          <ModalLink
            v-if="can('create_inventaire')"
            href="#createInventaireModal"
            as="button"
            class="ui-button ui-button-primary"
          >
            <PlusIcon class="mr-1.5 h-4 w-4" />
            Nouvel inventaire
          </ModalLink>
        </div>
      </div>

      <!-- ═══ Filtres ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Semaine</label>
            <input v-model="filters.semaine" type="week" class="ui-input w-full" />
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Statut</label>
            <select v-model="filters.statut" class="ui-input w-full">
              <option value="">Tous</option>
              <option value="draft">Brouillon</option>
              <option value="finalized">Finalisé</option>
            </select>
          </div>
          <div class="flex items-end gap-2">
            <button type="button" class="ui-button ui-button-ghost" @click="resetFilters">
              <ArrowPathIcon class="mr-1.5 h-4 w-4" /> Réinitialiser
            </button>
            <button type="button" class="ui-button ui-button-primary" @click="applyFilters">
              <MagnifyingGlassIcon class="mr-1.5 h-4 w-4" /> Filtrer
            </button>
          </div>
        </div>
      </div>

      <!-- ═══ Tableau ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <ClipboardDocumentCheckIcon class="h-5 w-5 text-istaht-blue" />
            <h3 class="font-bold text-istaht-navy">Liste des inventaires</h3>
          </div>
          <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
            {{ inventaires?.meta?.total ?? 0 }} inventaire(s)
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Semaine</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Statut</th>
                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Articles</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Progression</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Créé le</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="inv in inventaires.data" :key="inv.id" class="transition hover:bg-slate-50">
                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">{{ inv.semaine }}</td>
                <td class="px-5 py-3.5">
                  <span
                    class="rounded-full px-2.5 py-1 text-xs font-bold"
                    :class="inv.statut === 'finalized' ? 'bg-green-50 text-istaht-green ring-1 ring-green-100' : 'bg-amber-50 text-istaht-amber ring-1 ring-amber-100'"
                  >
                    {{ inv.statut === 'finalized' ? 'Finalisé' : 'Brouillon' }}
                  </span>
                </td>
                <td class="px-5 py-3.5 text-center text-sm text-slate-600">{{ inv.articles_count }}</td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                      <div class="h-full rounded-full bg-istaht-green transition-all" :style="{ width: progressPercent(inv.progress) + '%' }" />
                    </div>
                    <span class="text-xs font-semibold text-slate-500">{{ inv.progress ?? '—' }}</span>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-sm text-slate-500">{{ formatDate(inv.created_at) }}</td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center justify-end gap-1">
                    <Link
                      v-if="inv.statut === 'draft' && can('fill_stock_reel')"
                      :href="route('inventaires.edit', inv.id)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-green-50 hover:text-istaht-green"
                      title="Saisir / continuer les stocks réels"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </Link>
                    <a
                      v-if="inv.statut === 'finalized' && can('pdf_inventaire')"
                      :href="route('inventaires.pdf', inv.id)"
                      target="_blank"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-purple-50 hover:text-purple-600"
                      title="Télécharger le PDF"
                    >
                      <DocumentTextIcon class="h-5 w-5" />
                    </a>
                    <button
                      v-if="inv.statut === 'finalized' && can('unlock_inventaire')"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-amber-50 hover:text-istaht-amber"
                      title="Déverrouiller"
                      @click="openUnlockModal(inv.id)"
                    >
                      <LockOpenIcon class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="inventaires.data.length === 0" class="py-14 text-center">
          <InboxIcon class="mx-auto h-12 w-12 text-slate-300" />
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun inventaire</h3>
          <p class="mt-1 text-sm text-slate-500">Créez le premier inventaire hebdomadaire en un clic.</p>
          <div class="mt-5">
            <ModalLink v-if="can('create_inventaire')" href="#createInventaireModal" as="button" class="ui-button ui-button-primary">
              <PlusIcon class="mr-1.5 h-4 w-4" /> Nouvel inventaire
            </ModalLink>
          </div>
        </div>

        <div v-if="inventaires?.meta?.links && inventaires.meta.last_page > 1" class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row">
          <p class="text-sm text-slate-500">
            Affichage de <strong class="text-istaht-navy">{{ inventaires.meta.from }}</strong>
            à <strong class="text-istaht-navy">{{ inventaires.meta.to }}</strong>
            sur <strong class="text-istaht-navy">{{ inventaires.meta.total }}</strong> inventaires
          </p>
          <div class="flex flex-wrap gap-1">
            <template v-for="link in inventaires.meta.links" :key="link.label">
              <Link v-if="link.url" :href="link.url" :class="['rounded-md px-3 py-1.5 text-sm font-semibold transition', link.active ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100']" v-html="link.label" />
              <span v-else class="cursor-not-allowed rounded-md px-3 py-1.5 text-sm font-semibold text-slate-300" v-html="link.label" />
            </template>
          </div>
        </div>
      </div>
    </section>

    <CreateInventaireModal />
    <ConfirmationModal
      :show="showUnlockModal"
      type="warning"
      title="Déverrouiller l'inventaire"
      message="Êtes-vous sûr de vouloir déverrouiller cet inventaire ? Il repassera en brouillon et pourra être modifié."
      :onConfirm="unlock"
      @close="showUnlockModal = false"
    />
  </AuthenticatedLayout>
</template>
