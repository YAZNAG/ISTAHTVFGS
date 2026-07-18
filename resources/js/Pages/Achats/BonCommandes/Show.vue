<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UiBadge from '@/Components/UI/Badge.vue'
import { usePermission } from '@/Utils/permission'

const props = defineProps({
  marche: {
    type: Object,
    required: true,
  },
  decomptes: {
    type: Array,
    default: () => [],
  },
})

const { can } = usePermission()
const activeTab = ref('articles')
const pendingCancel = ref(false)
const isCancelling = ref(false)

const cancelForm = useForm({
  raison: '',
})

const statusMap = {
  cree: { label: 'Cree', tone: 'info' },
  attente_livraison: { label: 'Attente livraison', tone: 'warning' },
  livre_partiellement: { label: 'Livre partiellement', tone: 'warning' },
  livre_completement: { label: 'Livre completement', tone: 'success' },
  annule: { label: 'Annule', tone: 'danger' },
}

const tabs = computed(() => [
  { key: 'articles', label: 'Articles', count: props.marche.articles?.length || 0 },
  { key: 'bons', label: 'Bons de commande', count: props.marche.bons_commandes?.length || 0 },
  { key: 'livraisons', label: 'Livraisons', count: props.marche.livraisons?.length || 0 },
  { key: 'receptions', label: 'Receptions', count: props.marche.receptions?.length || 0 },
  { key: 'decomptes', label: 'Decomptes', count: props.decomptes?.length || 0 },
])

const kpis = computed(() => [
  { label: 'Montant HT', value: formatCurrency(props.marche.total_ht), className: 'text-istaht-navy' },
  { label: 'TVA', value: formatCurrency(props.marche.total_tva), className: 'text-istaht-blue' },
  { label: 'TTC', value: formatCurrency(props.marche.total_ttc), className: 'text-istaht-navy' },
  { label: 'Quantite commandee', value: formatNumber(props.marche.quantite_commandee, 2), className: 'text-slate-700' },
  { label: 'Quantite livree', value: formatNumber(props.marche.quantite_livree, 2), className: 'text-istaht-green' },
  { label: 'Quantite restante', value: formatNumber(props.marche.quantite_restante, 2), className: 'text-istaht-amber' },
])

function statusInfo(status) {
  return statusMap[status] || { label: 'Inconnu', tone: 'neutral' }
}

function alertTone(type) {
  if (type === 'danger') return 'danger'
  if (type === 'warning') return 'warning'
  return 'info'
}

function progressClass(percent) {
  if (Number(percent || 0) >= 90) return 'bg-istaht-red'
  if (Number(percent || 0) >= 80) return 'bg-istaht-amber'
  return 'bg-istaht-green'
}

function canCancelMarket() {
  return ['cree', 'attente_livraison'].includes(props.marche.statut)
}

function openCancel() {
  pendingCancel.value = true
  cancelForm.reset()
  cancelForm.clearErrors()
}

function closeCancel() {
  if (isCancelling.value) return
  pendingCancel.value = false
  cancelForm.reset()
  cancelForm.clearErrors()
}

function confirmCancel() {
  isCancelling.value = true
  cancelForm.post(route('bon-commandes.annuler', props.marche.id), {
    preserveScroll: true,
    onSuccess: () => {
      pendingCancel.value = false
      cancelForm.reset()
    },
    onFinish: () => {
      isCancelling.value = false
    },
  })
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
}

function formatNumber(value, fraction = 0) {
  return Number(value || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: fraction,
    maximumFractionDigits: fraction,
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    minimumFractionDigits: 2,
  }).format(Number(amount || 0))
}

function deleteDecompte(decompte) {
  if (!confirm(`Supprimer le decompte ${decompte.numero} (${formatDate(decompte.date_debut) || 'Debut'} → ${formatDate(decompte.date)}) ?`)) return
  router.delete(route('decompte.destroy', decompte.id), { preserveScroll: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="`Marche ${marche.reference}`" />

    <section class="space-y-5">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-mono text-sm font-bold text-istaht-blue">{{ marche.reference }}</p>
              <UiBadge :tone="statusInfo(marche.statut).tone">{{ statusInfo(marche.statut).label }}</UiBadge>
            </div>
            <h2 class="mt-2 text-2xl font-bold text-istaht-navy">{{ marche.objet }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
              {{ marche.description || 'Dossier marche avec articles, fournisseur, livraisons, receptions et decomptes.' }}
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link :href="route('bon-commandes.index')" class="ui-button ui-button-ghost">
              Retour liste
            </Link>
            <ModalLink
              v-if="marche.statut === 'cree' && can('validate_marches')"
              :href="route('bon-commandes.edit', marche.id)"
              class="ui-button ui-button-primary"
            >
              Attribuer le marche
            </ModalLink>
            <ModalLink
              v-if="marche.statut === 'cree' && can('edit_marches')"
              :href="route('bon-commandes.modify', marche.id)"
              class="ui-button ui-button-secondary"
            >
              Modifier
            </ModalLink>
            <a
              v-if="marche.statut !== 'cree' && marche.statut !== 'annule' && can('pdf_marches')"
              :href="route('bon-commandes.pdf', marche.id)"
              target="_blank"
              class="ui-button ui-button-secondary"
            >
              PDF marche
            </a>
            <ModalLink
              v-if="marche.statut !== 'cree' && marche.statut !== 'annule'"
              :href="route('decompte.create', marche.id)"
              class="ui-button ui-button-primary"
            >
              Creer decompte
            </ModalLink>
            <button
              v-if="canCancelMarket() && can('validate_marches')"
              type="button"
              class="ui-button bg-istaht-amber text-white hover:bg-orange-600"
              @click="openCancel"
            >
              Annuler
            </button>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Categorie</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ marche.categorie?.nom || 'Non renseignee' }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Fournisseur attributaire</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ marche.fournisseur?.nom_affichage || 'Non attribue' }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Periode</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ formatDate(marche.date_debut) }} - {{ formatDate(marche.date_fin) }}</p>
          </div>
        </div>

        <div v-if="marche.statut === 'annule'" class="mt-5 rounded-lg border border-red-100 bg-red-50 p-4">
          <p class="font-bold text-istaht-red">Marche annule le {{ formatDate(marche.annule_at) }}</p>
          <p class="mt-1 text-sm text-red-700">{{ marche.reason_annulation || 'Motif non renseigne.' }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
        <div
          v-for="item in kpis"
          :key="item.label"
          class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-soft"
        >
          <p class="text-xs font-bold uppercase text-slate-400">{{ item.label }}</p>
          <p class="mt-1 text-lg font-bold" :class="item.className">{{ item.value }}</p>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="flex-1">
            <div class="mb-2 flex items-center justify-between text-sm">
              <span class="font-bold text-istaht-navy">Consommation du marche</span>
              <span class="font-bold text-istaht-navy">{{ formatNumber(marche.consumption_percent, 1) }}%</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="progressClass(marche.consumption_percent)"
                :style="{ width: `${Math.min(Number(marche.consumption_percent || 0), 100)}%` }"
              />
            </div>
            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500">
              <span>Montant marche: {{ formatCurrency(marche.total_ttc) }}</span>
              <span>Consomme: {{ formatCurrency(marche.consumed_amount) }}</span>
              <span>Reste: {{ formatCurrency(marche.remaining_amount) }}</span>
            </div>
          </div>

          <div class="flex flex-wrap gap-2">
            <UiBadge v-for="alert in marche.alerts" :key="alert.message" :tone="alertTone(alert.type)">
              {{ alert.message }}
            </UiBadge>
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex gap-2 overflow-x-auto border-b border-slate-100 px-4 py-3">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-bold transition"
            :class="activeTab === tab.key ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100'"
            @click="activeTab = tab.key"
          >
            {{ tab.label }}
            <span class="ml-1 rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ tab.count }}</span>
          </button>
        </div>

        <div class="p-4">
          <div v-if="activeTab === 'articles'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Reference</th>
                  <th>Designation</th>
                  <th>Unite</th>
                  <th class="text-right">Qte engagee</th>
                  <th class="text-right">Qte consommee</th>
                  <th class="text-right">Qte restante</th>
                  <th class="text-right">Prix HT</th>
                  <th class="text-right">TVA</th>
                  <th class="text-right">TTC</th>
                  <th>Alerte</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="article in marche.articles" :key="article.id" class="hover:bg-slate-50">
                  <td class="font-bold text-istaht-blue">{{ article.reference || '-' }}</td>
                  <td class="font-semibold text-slate-800">{{ article.designation }}</td>
                  <td>{{ article.unite_mesure || '-' }}</td>
                  <td class="text-right">{{ formatNumber(article.quantite_engagee, 2) }}</td>
                  <td class="text-right">{{ formatNumber(article.quantite_consommee, 2) }}</td>
                  <td class="text-right font-bold" :class="article.alerte_quantite ? 'text-istaht-red' : 'text-istaht-navy'">
                    {{ formatNumber(article.quantite_restante, 2) }}
                  </td>
                  <td class="text-right">{{ formatCurrency(article.prix_unitaire_ht) }}</td>
                  <td class="text-right">{{ formatNumber(article.taux_tva, 2) }}%</td>
                  <td class="text-right font-bold text-istaht-navy">{{ formatCurrency(article.montant_ttc) }}</td>
                  <td>
                    <span v-if="article.alerte_quantite === 'epuise'" class="ui-badge bg-red-50 text-istaht-red ring-1 ring-red-100">Epuise</span>
                    <span v-else-if="article.alerte_quantite === 'faible'" class="ui-badge bg-amber-50 text-istaht-amber ring-1 ring-amber-100">Faible</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="activeTab === 'bons'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Numero</th>
                  <th>Date</th>
                  <th>Demandeur</th>
                  <th>Statut</th>
                  <th class="text-right">Articles</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="bon in marche.bons_commandes" :key="bon.id" class="hover:bg-slate-50">
                  <td class="font-bold text-istaht-blue">{{ bon.numero }}</td>
                  <td>{{ bon.date }}</td>
                  <td>{{ bon.demandeur || '-' }}</td>
                  <td><UiBadge tone="info">{{ bon.statut }}</UiBadge></td>
                  <td class="text-right">{{ bon.articles_count }}</td>
                  <td class="text-right">
                    <Link :href="route('chef-commandes.show', bon.id)" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                      Voir
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!marche.bons_commandes?.length" class="py-8 text-center text-sm text-slate-500">Aucun bon interne rattache.</p>
          </div>

          <div v-if="activeTab === 'livraisons'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Numero BL</th>
                  <th>Date</th>
                  <th>Statut</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="livraison in marche.livraisons" :key="livraison.id" class="hover:bg-slate-50">
                  <td class="font-bold text-istaht-blue">{{ livraison.numero }}</td>
                  <td>{{ livraison.date }}</td>
                  <td><UiBadge tone="warning">{{ livraison.statut }}</UiBadge></td>
                  <td class="text-right">
                    <Link :href="route('bon-livraisons.show', livraison.id)" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                      Voir
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!marche.livraisons?.length" class="py-8 text-center text-sm text-slate-500">Aucune livraison rattachee.</p>
          </div>

          <div v-if="activeTab === 'receptions'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Numero</th>
                  <th>Date</th>
                  <th>Source</th>
                  <th>Bon livraison</th>
                  <th class="text-right">TTC</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reception in marche.receptions" :key="`${reception.source}-${reception.id}`" class="hover:bg-slate-50">
                  <td class="font-bold text-istaht-blue">{{ reception.numero }}</td>
                  <td>{{ reception.date }}</td>
                  <td><UiBadge tone="info">{{ reception.source }}</UiBadge></td>
                  <td>{{ reception.bon_livraison }}</td>
                  <td class="text-right font-bold text-istaht-navy">{{ formatCurrency(reception.total_ttc) }}</td>
                  <td class="text-right">
                    <Link
                      v-if="reception.source === 'receptions'"
                      :href="route('bon-receptions.show', reception.id)"
                      class="ui-button ui-button-secondary px-3 py-1.5 text-xs"
                    >
                      Voir
                    </Link>
                    <span v-else class="text-xs text-slate-400">Historique</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!marche.receptions?.length" class="py-8 text-center text-sm text-slate-500">Aucune reception rattachee.</p>
          </div>

          <div v-if="activeTab === 'decomptes'">
            <div class="mb-3 flex items-center justify-between">
              <p class="text-sm text-slate-500">{{ decomptes?.length || 0 }} decompte(s) genere(s) pour ce marche.</p>
              <ModalLink
                v-if="marche.statut !== 'cree' && marche.statut !== 'annule'"
                :href="route('decompte.create', marche.id)"
                class="ui-button ui-button-primary px-3 py-1.5 text-xs"
              >
                + Nouveau decompte
              </ModalLink>
            </div>
            <div v-if="decomptes?.length" class="overflow-x-auto">
              <table>
                <thead>
                  <tr>
                    <th>Reference</th>
                    <th>Periode</th>
                    <th>Type</th>
                    <th class="text-right">Articles</th>
                    <th class="text-right">Montant TTC</th>
                    <th class="text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="decompte in decomptes" :key="decompte.id" class="hover:bg-slate-50">
                    <td class="font-bold text-istaht-blue">{{ decompte.numero }}</td>
                    <td>
                      <span class="text-slate-700">
                        {{ decompte.date_debut ? formatDate(decompte.date_debut) : 'Debut marche' }}
                      </span>
                      <span class="mx-1 text-slate-400">→</span>
                      <span class="font-semibold text-slate-700">{{ formatDate(decompte.date) }}</span>
                    </td>
                    <td>
                      <UiBadge :tone="decompte.final ? 'success' : 'warning'">
                        {{ decompte.final ? 'Definitif' : 'Provisoire' }}
                      </UiBadge>
                    </td>
                    <td class="text-right text-slate-600">{{ decompte.nb_articles }}</td>
                    <td class="text-right font-bold text-istaht-navy">{{ formatCurrency(decompte.total_ttc) }}</td>
                    <td class="text-right">
                      <a :href="route('decompte.download-pdf', decompte.id)" target="_blank" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                        PDF
                      </a>
                      <a :href="route('decompte.download-excel', decompte.id)" target="_blank" class="ui-button ui-button-secondary px-3 py-1.5 text-xs ml-1">
                        Excel
                      </a>
                      <button
                        type="button"
                        class="ui-button ui-button-danger px-3 py-1.5 text-xs ml-1"
                        @click="deleteDecompte(decompte)"
                      >
                        Supprimer
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p v-else class="py-8 text-center text-sm text-slate-500">Aucun decompte genere pour ce marche.</p>
          </div>
        </div>
      </div>

      <div
        v-if="pendingCancel"
        class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
      >
        <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel">
          <h3 class="text-lg font-bold text-istaht-navy">Annuler le marche</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600">
            Indiquez le motif d'annulation du marche {{ marche.reference }}. Le motif sera conserve dans l'historique.
          </p>

          <div class="mt-4">
            <label class="block text-sm font-bold text-slate-700">Motif annulation *</label>
            <textarea
              v-model="cancelForm.raison"
              rows="4"
              class="ui-input mt-1 w-full"
              placeholder="Minimum 20 caracteres..."
            />
            <p class="mt-1 text-xs text-slate-500">Minimum 20 caracteres.</p>
            <p v-if="cancelForm.errors.raison" class="mt-1 text-sm font-semibold text-istaht-red">
              {{ cancelForm.errors.raison }}
            </p>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="ui-button ui-button-ghost" :disabled="isCancelling" @click="closeCancel">
              Retour
            </button>
            <button
              type="button"
              class="ui-button ui-button-danger"
              :disabled="isCancelling || cancelForm.raison.length < 20"
              @click="confirmCancel"
            >
              {{ isCancelling ? 'Annulation...' : 'Confirmer annulation' }}
            </button>
          </div>
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
