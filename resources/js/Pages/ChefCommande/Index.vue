<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  EyeIcon,
  PlusIcon,
  ClipboardDocumentListIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  DocumentTextIcon,
  QuestionMarkCircleIcon,
  CubeIcon,
  CheckIcon,
  XMarkIcon,
  TrashIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ModalLink } from '@inertiaui/modal-vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { getChefCommandeStatutInfo } from '@/Utils/labels.js';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
  chefCommandes: Object,
  stats: Object,
  filters: Object,
});

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const filters = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
})

function resetFilters() {
  filters.value = { search: '', status: '', start_date: '', end_date: '' }
  router.get(route('chef-commandes.index'))
}

function applyFilters() {
  router.get(route('chef-commandes.index'), filters.value, { preserveState: true })
}

const showCancelModal = ref(false)
const chefCommandeIdToCancel = ref(null)

function openCancelModal(id) {
  chefCommandeIdToCancel.value = id
  showCancelModal.value = true
}

function cancelChefCommande() {
  return router.patch(route('chef-commandes.cancel', chefCommandeIdToCancel.value))
}

const showSubmitModal = ref(false)
const chefCommandeIdToSubmit = ref(null)

function opensubmitModal(id) {
  chefCommandeIdToSubmit.value = id
  showSubmitModal.value = true
}

function submitChefCommande() {
  return router.patch(route('chef-commandes.submit', chefCommandeIdToSubmit.value));
}

const showDeleteModal = ref(false)
const chefCommandeIdToDelete = ref(null)

function openDeleteModal(id) {
  chefCommandeIdToDelete.value = id
  showDeleteModal.value = true
}

function deleteChefCommande() {
  return router.delete(route('chef-commandes.destroy', chefCommandeIdToDelete.value))
}

const cancellableStatuts = ['cree', 'en_attente_validation', 'en_attente_livraison']
const deletableStatuts   = ['en_attente_livraison', 'annulee', 'rejet']

const getStatutColor = (statut) => getChefCommandeStatutInfo(statut).color;
const getStatutLabel = (statut) => getChefCommandeStatutInfo(statut).label;

const commandeStatus = [
  { value: 'cree',                  label: 'Créé' },
  { value: 'en_attente_validation', label: 'En attente de validation' },
  { value: 'en_attente_livraison',  label: 'En attente de livraison' },
  { value: 'livre_partiellement',   label: 'Livré partiellement' },
  { value: 'livre_completement',    label: 'Livré complètement' },
  { value: 'annulee',               label: 'Annulé' },
  { value: 'rejet',                 label: 'Rejeté' },
];

const statCards = [
  { key: 'total',              label: 'Total bons',            className: 'text-istaht-navy' },
  { key: 'attente_validation', label: 'Attente validation',    className: 'text-istaht-amber' },
  { key: 'attente_livraison',  label: 'Attente livraison',     className: 'text-istaht-blue' },
  { key: 'livres',             label: 'Livrés',                className: 'text-istaht-green' },
  { key: 'annulees',           label: 'Annulés / Rejetés',     className: 'text-istaht-red' },
];
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Bons de Commande" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-istaht-navy">Bons de commande</h2>
            <p class="mt-1 text-sm text-slate-500">
              Créez, suivez et validez les bons de commande internes par catégorie.
            </p>
          </div>
          <ModalLink
            v-if="can('create_chefCommandes')"
            :href="route('chef-commandes.create')"
            class="ui-button ui-button-primary"
          >
            <PlusIcon class="mr-1.5 h-4 w-4" />
            Nouveau bon
          </ModalLink>
        </div>
      </div>

      <!-- ═══ Statistiques ═══ -->
      <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div
          v-for="card in statCards"
          :key="card.key"
          class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-soft"
        >
          <p class="text-xs font-bold uppercase text-slate-400">{{ card.label }}</p>
          <p class="mt-1 text-2xl font-bold" :class="card.className">{{ stats?.[card.key] ?? 0 }}</p>
        </div>
      </div>

      <!-- ═══ Filtres ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
          <div class="md:col-span-2">
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Recherche</label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="N° de bon..."
                class="ui-input w-full pl-9"
                @keyup.enter="applyFilters"
              />
              <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
          </div>

          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Statut</label>
            <select v-model="filters.status" class="ui-input w-full">
              <option value="">Tous les statuts</option>
              <option v-for="status in commandeStatus" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date début</label>
            <input v-model="filters.start_date" type="date" class="ui-input w-full" />
          </div>

          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date fin</label>
            <input v-model="filters.end_date" type="date" class="ui-input w-full" />
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
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Numéro</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Date</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Demandeur</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Catégorie</th>
                <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Articles</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Statut</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              <tr v-for="commande in chefCommandes.data" :key="commande.id" class="transition hover:bg-slate-50">
                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm font-bold text-istaht-blue">
                  {{ commande.numero }}
                </td>

                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                  {{ formatDate(commande.created_at) }}
                </td>

                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-700">
                  {{ commande.demandeur || '—' }}
                </td>

                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">
                  {{ commande.categorie || '—' }}
                </td>

                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                  <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                    <CubeIcon class="h-3.5 w-3.5" />
                    {{ commande.articles_count }}
                  </span>
                </td>

                <td class="whitespace-nowrap px-5 py-3.5">
                  <span
                    :class="[
                      'rounded-full px-2.5 py-1 text-xs font-bold',
                      getStatutColor(commande.statut)
                    ]"
                  >
                    {{ getStatutLabel(commande.statut) }}
                  </span>
                </td>

                <td class="whitespace-nowrap px-5 py-3.5">
                  <div class="flex items-center justify-end gap-1">
                    <ModalLink
                      v-if="can('show_chefCommandes')"
                      :href="route('chef-commandes.show', commande.id)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-blue-50 hover:text-istaht-blue"
                      title="Voir détails"
                    >
                      <EyeIcon class="h-4.5 w-4.5" />
                    </ModalLink>

                    <ModalLink
                      v-if="can('validate_chefCommandes') && commande.statut === 'en_attente_validation'"
                      :href="route('chef-commandes.showApprove', commande.id)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-amber-50 hover:text-istaht-amber"
                      title="Approuver la commande"
                    >
                      <QuestionMarkCircleIcon class="h-4.5 w-4.5" />
                    </ModalLink>

                    <ModalLink
                      v-if="can('edit_chefCommandes') && (commande.statut === 'cree' || commande.statut === 'en_attente_validation')"
                      :href="route('chef-commandes.edit', commande.id)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-green-50 hover:text-istaht-green"
                      title="Modifier"
                    >
                      <PencilIcon class="h-4.5 w-4.5" />
                    </ModalLink>

                    <button
                      v-if="commande.statut === 'cree'"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-green-50 hover:text-istaht-green"
                      title="Soumettre pour validation"
                      @click="opensubmitModal(commande.id)"
                    >
                      <CheckIcon class="h-4.5 w-4.5" />
                    </button>

                    <button
                      v-if="cancellableStatuts.includes(commande.statut)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-red-50 hover:text-istaht-red"
                      title="Annuler la commande"
                      @click="openCancelModal(commande.id)"
                    >
                      <XMarkIcon class="h-4.5 w-4.5" />
                    </button>

                    <button
                      v-if="deletableStatuts.includes(commande.statut)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-red-50 hover:text-istaht-red"
                      title="Supprimer le bon de commande"
                      @click="openDeleteModal(commande.id)"
                    >
                      <TrashIcon class="h-4.5 w-4.5" />
                    </button>

                    <a
                      v-if="can('pdf_chefCommandes') && !['cree', 'rejet', 'annulee', 'en_attente_validation'].includes(commande.statut)"
                      class="rounded-md p-1.5 text-slate-500 transition hover:bg-purple-50 hover:text-purple-600"
                      :href="route('chef-commandes.download-pdf', commande.id)"
                      title="Télécharger PDF"
                      target="_blank"
                    >
                      <DocumentTextIcon class="h-4.5 w-4.5" />
                    </a>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Vide -->
        <div v-if="chefCommandes.data.length === 0" class="py-14 text-center">
          <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-slate-300" />
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun bon de commande trouvé</h3>
          <p class="mt-1 text-sm text-slate-500">Ajustez les filtres ou créez votre premier bon.</p>
          <div class="mt-5">
            <ModalLink
              v-if="can('create_chefCommandes')"
              :href="route('chef-commandes.create')"
              class="ui-button ui-button-primary"
            >
              <PlusIcon class="mr-1.5 h-4 w-4" />
              Nouveau bon
            </ModalLink>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="chefCommandes.meta && chefCommandes.meta.links && chefCommandes.meta.last_page > 1"
          class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-5 py-3 sm:flex-row"
        >
          <p class="text-sm text-slate-500">
            Affichage de <strong class="text-istaht-navy">{{ chefCommandes.meta.from }}</strong>
            à <strong class="text-istaht-navy">{{ chefCommandes.meta.to }}</strong>
            sur <strong class="text-istaht-navy">{{ chefCommandes.meta.total }}</strong> bons
          </p>
          <div class="flex flex-wrap gap-1">
            <template v-for="link in chefCommandes.meta.links" :key="link.label">
              <Link
                v-if="link.url"
                :href="link.url"
                :class="[
                  'rounded-md px-3 py-1.5 text-sm font-semibold transition',
                  link.active
                    ? 'bg-istaht-navy text-white'
                    : 'text-slate-600 hover:bg-slate-100'
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

    <ConfirmationModal
      :show="showCancelModal"
      title="Annuler le bon de commande"
      message="Êtes-vous sûr de vouloir annuler ce bon de commande ?"
      :onConfirm="cancelChefCommande"
      @close="showCancelModal = false"
    />

    <ConfirmationModal
      :show="showSubmitModal"
      type="info"
      title="Soumettre le bon de commande"
      message="Êtes-vous sûr de vouloir soumettre ce bon de commande pour validation ?"
      :onConfirm="submitChefCommande"
      @close="showSubmitModal = false"
    />

    <ConfirmationModal
      :show="showDeleteModal"
      title="Supprimer le bon de commande"
      message="Êtes-vous sûr de vouloir supprimer définitivement ce bon de commande ? Cette action est irréversible."
      :onConfirm="deleteChefCommande"
      @close="showDeleteModal = false"
    />
  </AuthenticatedLayout>
</template>
