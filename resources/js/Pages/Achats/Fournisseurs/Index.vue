<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import UiBadge from '@/Components/UI/Badge.vue'
import InputError from '@/Components/InputError.vue'
import { usePermission } from '@/Utils/permission'

const { can } = usePermission()

const props = defineProps({
  fournisseurs: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
})

const filters = ref({
  search: props.filters?.search || '',
  statut: props.filters?.statut || '',
})

const showForm = ref(false)
const selectedFournisseur = ref(null)
const pendingDelete = ref(null)
const pendingToggle = ref(null)
const isDeleting = ref(false)
const isToggling = ref(false)
const logoPreview = ref(null)
const logoInput = ref(null)

const fournisseurForm = useForm({
  nom: '',
  raison_sociale: '',
  contact: '',
  telephone: '',
  email: '',
  adresse: '',
  ville: '',
  ice: '',
  tp: '',
  rc: '',
  if: '',
  cb: '',
  cnss: '',
  notes: '',
  est_actif: true,
  logo: null,
})

const isEditing = computed(() => Boolean(selectedFournisseur.value))
const activeFilterCount = computed(() => Object.values(filters.value).filter(Boolean).length)

const exportParams = computed(() => Object.fromEntries(
  Object.entries(filters.value).filter(([, value]) => value !== '' && value !== null && value !== undefined)
))

const kpis = computed(() => [
  { label: 'Total fournisseurs', value: formatNumber(props.stats.total), tone: 'text-istaht-navy' },
  { label: 'Actifs', value: formatNumber(props.stats.actifs), tone: 'text-istaht-green' },
  { label: 'Inactifs', value: formatNumber(props.stats.inactifs), tone: 'text-istaht-red' },
  { label: 'Marches attribues', value: formatNumber(props.stats.marches), tone: 'text-istaht-blue' },
  { label: 'Montant attribue', value: formatCurrency(props.stats.montant_total_attribue), tone: 'text-istaht-navy' },
])

const deleteMessage = computed(() => {
  if (!pendingDelete.value) return ''

  if (pendingDelete.value.can_delete_physical) {
    return `Le fournisseur ${pendingDelete.value.nom_affichage} n'est lie a aucun marche ou document. Confirmez-vous la suppression definitive ?`
  }

  return 'Ce fournisseur est deja lie a des marches ou documents. Il ne peut pas etre supprime definitivement. Voulez-vous le desactiver ?'
})

const deleteLabel = computed(() => pendingDelete.value?.can_delete_physical ? 'Supprimer definitivement' : 'Desactiver')

let filterTimer = null
watch(filters, value => {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    router.get(route('fournisseurs.index'), value, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 280)
}, { deep: true })

function openCreateForm() {
  selectedFournisseur.value = null
  resetForm()
  showForm.value = true
}

function openEditForm(fournisseur) {
  selectedFournisseur.value = fournisseur
  fournisseurForm.defaults({
    nom: fournisseur.nom || '',
    raison_sociale: fournisseur.raison_sociale || '',
    contact: fournisseur.contact || '',
    telephone: fournisseur.telephone || '',
    email: fournisseur.email || '',
    adresse: fournisseur.adresse || '',
    ville: fournisseur.ville || '',
    ice: fournisseur.ice || '',
    tp: fournisseur.tp || '',
    rc: fournisseur.rc || '',
    if: fournisseur.if || '',
    cb: fournisseur.cb || '',
    cnss: fournisseur.cnss || '',
    notes: fournisseur.notes || '',
    est_actif: Boolean(fournisseur.est_actif),
    logo: null,
  })
  fournisseurForm.reset()
  fournisseurForm.clearErrors()
  logoPreview.value = fournisseur.logo_url || null
  showForm.value = true
}

function closeForm() {
  showForm.value = false
  selectedFournisseur.value = null
  resetForm()
}

function resetForm() {
  fournisseurForm.defaults({
    nom: '',
    raison_sociale: '',
    contact: '',
    telephone: '',
    email: '',
    adresse: '',
    ville: '',
    ice: '',
    tp: '',
    rc: '',
    if: '',
    cb: '',
    cnss: '',
    notes: '',
    est_actif: true,
    logo: null,
  })
  fournisseurForm.reset()
  fournisseurForm.clearErrors()
  logoPreview.value = null
  if (logoInput.value) logoInput.value.value = ''
}

function submitForm() {
  const url = isEditing.value
    ? route('fournisseurs.update', selectedFournisseur.value.id)
    : route('fournisseurs.store')

  fournisseurForm
    .transform(data => ({
      ...data,
      est_actif: data.est_actif ? 1 : 0,
      ...(isEditing.value ? { _method: 'put' } : {}),
    }))
    .post(url, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => closeForm(),
    })
}

function onLogoChange(event) {
  const file = event.target.files?.[0] || null
  fournisseurForm.logo = file

  if (!file) {
    logoPreview.value = selectedFournisseur.value?.logo_url || null
    return
  }

  logoPreview.value = URL.createObjectURL(file)
}

function resetFilters() {
  filters.value = {
    search: '',
    statut: '',
  }
}

function askDelete(fournisseur) {
  pendingDelete.value = fournisseur
}

function closeDelete() {
  if (isDeleting.value) return
  pendingDelete.value = null
}

function confirmDelete() {
  if (!pendingDelete.value) return

  isDeleting.value = true
  router.delete(route('fournisseurs.destroy', pendingDelete.value.id), {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false
      pendingDelete.value = null
    },
  })
}

function askToggle(fournisseur) {
  pendingToggle.value = fournisseur
}

function closeToggle() {
  if (isToggling.value) return
  pendingToggle.value = null
}

function confirmToggle() {
  if (!pendingToggle.value) return

  isToggling.value = true
  router.patch(route('fournisseurs.toggle-statut', pendingToggle.value.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      isToggling.value = false
      pendingToggle.value = null
    },
  })
}

function formatNumber(value) {
  return Number(value || 0).toLocaleString('fr-FR')
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    minimumFractionDigits: 2,
  }).format(Number(amount || 0))
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Fournisseurs" />

    <section class="space-y-5">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
          <div>
            <p class="text-xs font-bold uppercase text-istaht-cyan">Achats et partenaires</p>
            <h2 class="mt-1 text-2xl font-bold text-istaht-navy">Fournisseurs</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
              Referentiel des fournisseurs lies aux marches, bons de livraison, receptions et decomptes.
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <a
              v-if="can('export_fournisseurs')"
              :href="route('fournisseurs.export.pdf', exportParams)"
              target="_blank"
              class="ui-button ui-button-ghost"
            >
              Export PDF
            </a>
            <a
              v-if="can('export_fournisseurs')"
              :href="route('fournisseurs.export.excel', exportParams)"
              class="ui-button ui-button-secondary"
            >
              Export Excel
            </a>
            <button
              v-if="can('create_fournisseurs')"
              type="button"
              class="ui-button ui-button-primary"
              @click="openCreateForm"
            >
              Ajouter fournisseur
            </button>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-5">
          <div
            v-for="item in kpis"
            :key="item.label"
            class="rounded-lg border border-slate-100 bg-slate-50 px-4 py-3"
          >
            <p class="text-xs font-bold uppercase text-slate-400">{{ item.label }}</p>
            <p class="mt-1 text-xl font-bold" :class="item.tone">{{ item.value }}</p>
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-soft">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_180px_auto]">
          <input
            v-model="filters.search"
            type="search"
            class="ui-input"
            placeholder="Nom, raison sociale, ICE ou ville..."
          >

          <select v-model="filters.statut" class="ui-input">
            <option value="">Tous statuts</option>
            <option value="actifs">Actifs</option>
            <option value="inactifs">Inactifs</option>
          </select>

          <button type="button" class="ui-button ui-button-ghost whitespace-nowrap" @click="resetFilters">
            Reinitialiser
          </button>
        </div>

        <div v-if="activeFilterCount" class="mt-3 text-sm font-semibold text-slate-500">
          {{ activeFilterCount }} filtre(s) actif(s)
        </div>
      </div>

      <div v-if="showForm" class="rounded-lg border border-cyan-100 bg-white p-5 shadow-panel">
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p class="text-xs font-bold uppercase text-istaht-cyan">
              {{ isEditing ? 'Modification fournisseur' : 'Nouveau fournisseur' }}
            </p>
            <h3 class="mt-1 text-xl font-bold text-istaht-navy">
              {{ isEditing ? selectedFournisseur?.nom_affichage : 'Ajouter un fournisseur' }}
            </h3>
          </div>
          <button type="button" class="ui-button ui-button-ghost" @click="closeForm">
            Fermer
          </button>
        </div>

        <form class="space-y-6" enctype="multipart/form-data" @submit.prevent="submitForm">
          <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <section class="space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
              <h4 class="font-bold text-istaht-navy">Informations generales</h4>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label>Nom *</label>
                  <input v-model="fournisseurForm.nom" type="text" required class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.nom" />
                </div>
                <div>
                  <label>Raison sociale</label>
                  <input v-model="fournisseurForm.raison_sociale" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.raison_sociale" />
                </div>
                <div>
                  <label>Contact</label>
                  <input v-model="fournisseurForm.contact" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.contact" />
                </div>
                <div>
                  <label>Telephone</label>
                  <input v-model="fournisseurForm.telephone" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.telephone" />
                </div>
                <div>
                  <label>Email</label>
                  <input v-model="fournisseurForm.email" type="email" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.email" />
                </div>
                <div>
                  <label>Ville</label>
                  <input v-model="fournisseurForm.ville" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.ville" />
                </div>
              </div>

              <div>
                <label>Adresse</label>
                <textarea v-model="fournisseurForm.adresse" rows="3" class="ui-input mt-1 w-full" />
                <InputError :message="fournisseurForm.errors.adresse" />
              </div>
            </section>

            <section class="space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
              <h4 class="font-bold text-istaht-navy">Informations administratives</h4>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label>ICE</label>
                  <input v-model="fournisseurForm.ice" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.ice" />
                </div>
                <div>
                  <label>IF</label>
                  <input v-model="fournisseurForm.if" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.if" />
                </div>
                <div>
                  <label>RC</label>
                  <input v-model="fournisseurForm.rc" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.rc" />
                </div>
                <div>
                  <label>TP</label>
                  <input v-model="fournisseurForm.tp" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.tp" />
                </div>
                <div>
                  <label>CNSS</label>
                  <input v-model="fournisseurForm.cnss" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.cnss" />
                </div>
                <div>
                  <label>Compte bancaire</label>
                  <input v-model="fournisseurForm.cb" type="text" class="ui-input mt-1 w-full">
                  <InputError :message="fournisseurForm.errors.cb" />
                </div>
              </div>
            </section>
          </div>

          <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <section class="space-y-4 rounded-lg border border-slate-200 bg-white p-4">
              <h4 class="font-bold text-istaht-navy">Statut</h4>
              <label class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <input v-model="fournisseurForm.est_actif" type="checkbox" class="rounded border-slate-300 text-istaht-blue">
                <span class="font-semibold text-slate-700">{{ fournisseurForm.est_actif ? 'Actif' : 'Inactif' }}</span>
              </label>
            </section>

            <section class="space-y-4 rounded-lg border border-slate-200 bg-white p-4">
              <h4 class="font-bold text-istaht-navy">Logo ou document</h4>
              <input ref="logoInput" type="file" accept="image/*" class="ui-input w-full" @change="onLogoChange">
              <p class="text-xs text-slate-500">Facultatif. JPG, PNG, GIF ou WEBP, max 2 Mo.</p>
              <InputError :message="fournisseurForm.errors.logo" />

              <div v-if="logoPreview" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                <img :src="logoPreview" alt="Logo fournisseur" class="h-14 w-14 rounded-lg object-cover">
                <span class="text-sm font-semibold text-slate-700">Apercu du logo</span>
              </div>
            </section>
          </div>

          <section>
            <label>Notes</label>
            <textarea v-model="fournisseurForm.notes" rows="3" class="ui-input mt-1 w-full" />
            <InputError :message="fournisseurForm.errors.notes" />
          </section>

          <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
            <button type="button" class="ui-button ui-button-ghost" @click="closeForm">
              Annuler
            </button>
            <button type="submit" class="ui-button ui-button-primary" :disabled="fournisseurForm.processing">
              {{ fournisseurForm.processing ? 'Enregistrement...' : (isEditing ? 'Modifier fournisseur' : 'Creer fournisseur') }}
            </button>
          </div>
        </form>
      </div>

      <div v-if="fournisseurs.data.length" class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <article
          v-for="fournisseur in fournisseurs.data"
          :key="fournisseur.id"
          class="animate-fade-up rounded-lg border border-slate-200 bg-white p-5 shadow-soft transition hover:-translate-y-0.5 hover:shadow-panel"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-bold uppercase text-slate-400">{{ fournisseur.ville || 'Ville non renseignee' }}</p>
              <h3 class="mt-1 text-lg font-bold text-istaht-navy">{{ fournisseur.raison_sociale || fournisseur.nom }}</h3>
              <p class="mt-1 text-sm font-semibold text-slate-500">{{ fournisseur.nom }}</p>
            </div>
            <UiBadge :tone="fournisseur.est_actif ? 'success' : 'danger'">
              {{ fournisseur.est_actif ? 'Actif' : 'Inactif' }}
            </UiBadge>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-lg bg-slate-50 p-3">
              <p class="text-xs font-bold uppercase text-slate-400">Contact</p>
              <p class="mt-1 font-semibold text-slate-800">{{ fournisseur.contact || '-' }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
              <p class="text-xs font-bold uppercase text-slate-400">Telephone</p>
              <p class="mt-1 font-semibold text-slate-800">{{ fournisseur.telephone || '-' }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
              <p class="text-xs font-bold uppercase text-slate-400">ICE</p>
              <p class="mt-1 font-semibold text-slate-800">{{ fournisseur.ice || '-' }}</p>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
              <p class="text-xs font-bold uppercase text-slate-400">Marches</p>
              <p class="mt-1 font-bold text-istaht-blue">{{ fournisseur.marches_count }}</p>
            </div>
          </div>

          <div class="mt-4 rounded-lg border border-cyan-100 bg-cyan-50 p-3">
            <p class="text-xs font-bold uppercase text-istaht-blue">Montant total attribue</p>
            <p class="mt-1 text-lg font-bold text-istaht-navy">{{ formatCurrency(fournisseur.montant_total_attribue) }}</p>
          </div>

          <div class="mt-5 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
            <Link
              v-if="can('show_fournisseurs')"
              :href="route('fournisseurs.show', fournisseur.id)"
              class="ui-button ui-button-secondary px-3 py-1.5 text-xs"
            >
              Voir detail
            </Link>
            <button
              v-if="can('edit_fournisseurs')"
              type="button"
              class="ui-button ui-button-ghost px-3 py-1.5 text-xs"
              @click="openEditForm(fournisseur)"
            >
              Modifier
            </button>
            <button
              v-if="can('edit_fournisseurs')"
              type="button"
              class="ui-button px-3 py-1.5 text-xs"
              :class="fournisseur.est_actif ? 'bg-istaht-amber text-white hover:bg-orange-600' : 'ui-button-success'"
              @click="askToggle(fournisseur)"
            >
              {{ fournisseur.est_actif ? 'Desactiver' : 'Activer' }}
            </button>
            <button
              v-if="can('delete_fournisseurs')"
              type="button"
              class="ui-button ui-button-danger px-3 py-1.5 text-xs"
              @click="askDelete(fournisseur)"
            >
              Supprimer
            </button>
          </div>
        </article>
      </div>

      <div v-else class="rounded-lg border border-slate-200 bg-white px-5 py-12 text-center shadow-soft">
        <h3 class="text-base font-bold text-istaht-navy">Aucun fournisseur trouve</h3>
        <p class="mt-1 text-sm text-slate-500">Ajustez les filtres ou ajoutez un nouveau fournisseur.</p>
        <button
          v-if="can('create_fournisseurs')"
          type="button"
          class="ui-button ui-button-primary mt-5"
          @click="openCreateForm"
        >
          Ajouter fournisseur
        </button>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-soft">
        <Pagination
          :links="fournisseurs.links || []"
          :from="fournisseurs.from || 0"
          :to="fournisseurs.to || 0"
          :total="fournisseurs.total || 0"
        />
      </div>
    </section>

    <div
      v-if="pendingDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel">
        <h3 class="text-lg font-bold text-istaht-navy">Confirmer la suppression</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ deleteMessage }}</p>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="ui-button ui-button-ghost" :disabled="isDeleting" @click="closeDelete">
            Annuler
          </button>
          <button
            type="button"
            class="ui-button"
            :class="pendingDelete.can_delete_physical ? 'ui-button-danger' : 'bg-istaht-amber text-white hover:bg-orange-600'"
            :disabled="isDeleting"
            @click="confirmDelete"
          >
            {{ isDeleting ? 'Traitement...' : deleteLabel }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="pendingToggle"
      class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel">
        <h3 class="text-lg font-bold text-istaht-navy">Modifier le statut</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          Confirmez-vous l'action "{{ pendingToggle.est_actif ? 'Desactiver fournisseur' : 'Activer fournisseur' }}" pour {{ pendingToggle.nom_affichage }} ?
        </p>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="ui-button ui-button-ghost" :disabled="isToggling" @click="closeToggle">
            Annuler
          </button>
          <button
            type="button"
            class="ui-button"
            :class="pendingToggle.est_actif ? 'bg-istaht-amber text-white hover:bg-orange-600' : 'ui-button-success'"
            :disabled="isToggling"
            @click="confirmToggle"
          >
            {{ isToggling ? 'Traitement...' : (pendingToggle.est_actif ? 'Desactiver' : 'Activer') }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
