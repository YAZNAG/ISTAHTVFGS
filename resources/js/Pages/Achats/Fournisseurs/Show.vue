<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UiBadge from '@/Components/UI/Badge.vue'

const props = defineProps({
  fournisseur: {
    type: Object,
    required: true,
  },
})

const activeTab = ref('marches')

const tabs = computed(() => [
  { key: 'marches', label: 'Marches attribues', count: props.fournisseur.marches?.length || 0 },
  { key: 'livraisons', label: 'Bons de livraison', count: props.fournisseur.livraisons?.length || 0 },
  { key: 'receptions', label: 'Receptions', count: props.fournisseur.receptions?.length || 0 },
  { key: 'decomptes', label: 'Decomptes', count: props.fournisseur.decomptes?.length || 0 },
  { key: 'documents', label: 'Documents', count: props.fournisseur.documents?.length || 0 },
])

const infoCards = computed(() => [
  { label: 'ICE', value: props.fournisseur.ice },
  { label: 'IF', value: props.fournisseur.if },
  { label: 'RC', value: props.fournisseur.rc },
  { label: 'TP', value: props.fournisseur.tp },
  { label: 'CNSS', value: props.fournisseur.cnss },
  { label: 'Compte bancaire', value: props.fournisseur.cb },
  { label: 'Telephone', value: props.fournisseur.telephone },
  { label: 'Email', value: props.fournisseur.email },
])

function statusTone(active) {
  return active ? 'success' : 'danger'
}

function marketStatus(status) {
  return {
    cree: { label: 'Cree', tone: 'info' },
    attente_livraison: { label: 'Attente livraison', tone: 'warning' },
    livre_partiellement: { label: 'Livre partiellement', tone: 'warning' },
    livre_completement: { label: 'Livre completement', tone: 'success' },
    annule: { label: 'Annule', tone: 'danger' },
  }[status] || { label: status || 'Inconnu', tone: 'neutral' }
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('fr-FR')
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
    <Head :title="`Fournisseur ${fournisseur.nom_affichage}`" />

    <section class="space-y-5">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="flex items-start gap-4">
            <img
              v-if="fournisseur.logo_url"
              :src="fournisseur.logo_url"
              alt="Logo fournisseur"
              class="h-16 w-16 rounded-lg border border-slate-200 object-cover"
            >
            <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-xl font-bold text-istaht-navy">
              {{ fournisseur.nom_affichage?.charAt(0) || 'F' }}
            </div>

            <div>
              <div class="flex flex-wrap items-center gap-2">
                <UiBadge :tone="statusTone(fournisseur.est_actif)">
                  {{ fournisseur.est_actif ? 'Actif' : 'Inactif' }}
                </UiBadge>
                <span class="text-sm font-semibold text-slate-500">{{ fournisseur.ville || 'Ville non renseignee' }}</span>
              </div>
              <h2 class="mt-2 text-2xl font-bold text-istaht-navy">{{ fournisseur.raison_sociale || fournisseur.nom }}</h2>
              <p class="mt-1 text-sm font-semibold text-slate-500">{{ fournisseur.nom }}</p>
              <p class="mt-2 text-sm text-slate-600">{{ fournisseur.contact || 'Contact principal non renseigne' }}</p>
            </div>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link :href="route('fournisseurs.index')" class="ui-button ui-button-ghost">
              Retour
            </Link>
            <a
              v-if="fournisseur.email"
              :href="`mailto:${fournisseur.email}`"
              class="ui-button ui-button-secondary"
            >
              Contacter
            </a>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Marches attribues</p>
            <p class="mt-1 text-xl font-bold text-istaht-blue">{{ fournisseur.marches_count || 0 }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Montant total attribue</p>
            <p class="mt-1 text-xl font-bold text-istaht-navy">{{ formatCurrency(fournisseur.montant_total_attribue) }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Adresse</p>
            <p class="mt-1 text-sm font-semibold text-slate-800">{{ fournisseur.adresse || '-' }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div
          v-for="item in infoCards"
          :key="item.label"
          class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-soft"
        >
          <p class="text-xs font-bold uppercase text-slate-400">{{ item.label }}</p>
          <p class="mt-1 break-words font-bold text-istaht-navy">{{ item.value || '-' }}</p>
        </div>
      </div>

      <div v-if="fournisseur.notes" class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <h3 class="font-bold text-istaht-navy">Notes</h3>
        <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-600">{{ fournisseur.notes }}</p>
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
          <div v-if="activeTab === 'marches'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Reference</th>
                  <th>Objet</th>
                  <th>Categorie</th>
                  <th>Periode</th>
                  <th class="text-right">Articles</th>
                  <th class="text-right">Montant TTC</th>
                  <th>Statut</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="marche in fournisseur.marches" :key="marche.id">
                  <td class="font-bold text-istaht-blue">{{ marche.reference }}</td>
                  <td class="font-semibold text-slate-800">{{ marche.objet }}</td>
                  <td>{{ marche.categorie || '-' }}</td>
                  <td>{{ formatDate(marche.date_debut) }} - {{ formatDate(marche.date_fin) }}</td>
                  <td class="text-right">{{ marche.articles_count }}</td>
                  <td class="text-right font-bold text-istaht-navy">{{ formatCurrency(marche.montant_ttc) }}</td>
                  <td><UiBadge :tone="marketStatus(marche.statut).tone">{{ marketStatus(marche.statut).label }}</UiBadge></td>
                  <td class="text-right">
                    <Link :href="route('bon-commandes.show', marche.id)" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                      Voir
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!fournisseur.marches?.length" class="py-8 text-center text-sm text-slate-500">Aucun marche attribue.</p>
          </div>

          <div v-if="activeTab === 'livraisons'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Numero BL</th>
                  <th>Date</th>
                  <th>Statut</th>
                  <th class="text-right">HT</th>
                  <th class="text-right">TVA</th>
                  <th class="text-right">TTC</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="livraison in fournisseur.livraisons" :key="livraison.id">
                  <td class="font-bold text-istaht-blue">{{ livraison.numero }}</td>
                  <td>{{ livraison.date }}</td>
                  <td><UiBadge tone="warning">{{ livraison.statut }}</UiBadge></td>
                  <td class="text-right">{{ formatCurrency(livraison.total_ht) }}</td>
                  <td class="text-right">{{ formatCurrency(livraison.total_tva) }}</td>
                  <td class="text-right font-bold text-istaht-navy">{{ formatCurrency(livraison.total_ttc) }}</td>
                  <td class="text-right">
                    <Link :href="route('bon-livraisons.show', livraison.id)" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                      Voir
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!fournisseur.livraisons?.length" class="py-8 text-center text-sm text-slate-500">Aucun bon de livraison rattache.</p>
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
                <tr v-for="reception in fournisseur.receptions" :key="`${reception.source}-${reception.id}`">
                  <td class="font-bold text-istaht-blue">{{ reception.numero }}</td>
                  <td>{{ reception.date }}</td>
                  <td><UiBadge tone="info">{{ reception.source }}</UiBadge></td>
                  <td>{{ reception.bon_livraison }}</td>
                  <td class="text-right font-bold text-istaht-navy">{{ formatCurrency(reception.total_ttc) }}</td>
                  <td class="text-right">
                    <Link
                      v-if="reception.source === 'receptions' && reception.id"
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
            <p v-if="!fournisseur.receptions?.length" class="py-8 text-center text-sm text-slate-500">Aucune reception rattachee.</p>
          </div>

          <div v-if="activeTab === 'decomptes'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Marche</th>
                  <th>Type</th>
                  <th class="text-right">Montant TTC</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="decompte in fournisseur.decomptes" :key="decompte.id">
                  <td>{{ formatDate(decompte.date) }}</td>
                  <td>
                    <p class="font-bold text-istaht-blue">{{ decompte.marche_reference || '-' }}</p>
                    <p class="text-xs text-slate-500">{{ decompte.marche_objet || '-' }}</p>
                  </td>
                  <td><UiBadge :tone="decompte.final ? 'success' : 'info'">{{ decompte.final ? 'Final' : 'Provisoire' }}</UiBadge></td>
                  <td class="text-right font-bold text-istaht-navy">{{ formatCurrency(decompte.total_ttc) }}</td>
                  <td class="text-right">
                    <a :href="route('decompte.download-pdf', decompte.id)" target="_blank" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                      PDF
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!fournisseur.decomptes?.length" class="py-8 text-center text-sm text-slate-500">Aucun decompte rattache.</p>
          </div>

          <div v-if="activeTab === 'documents'" class="overflow-x-auto">
            <table>
              <thead>
                <tr>
                  <th>Document</th>
                  <th>Type</th>
                  <th>Date</th>
                  <th class="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="document in fournisseur.documents" :key="document.id">
                  <td class="font-semibold text-slate-800">{{ document.nom }}</td>
                  <td><UiBadge tone="info">{{ document.type }}</UiBadge></td>
                  <td>{{ document.date }}</td>
                  <td class="text-right">
                    <a :href="document.url" target="_blank" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                      Ouvrir
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="!fournisseur.documents?.length" class="py-8 text-center text-sm text-slate-500">Aucun document rattache.</p>
          </div>
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
