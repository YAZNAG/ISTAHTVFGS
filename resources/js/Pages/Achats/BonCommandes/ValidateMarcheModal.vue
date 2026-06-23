<script setup>
import { computed, ref, watch } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  fournisseurs: { type: Array, default: () => [] },
  marche: { type: Object, required: true },
})

const validateMarcheModal = ref(null)
const activeStep = ref(0)
const confirmationAnnulation = ref(false)

const attributionSteps = [
  { label: 'Fournisseur', caption: 'Attributaire' },
  { label: 'Prix HT', caption: 'Par article' },
  { label: 'Validation', caption: 'Calculs finaux' },
]

const form = useForm({
  statut: props.marche.statut === 'annule' ? 'annule' : 'attente_livraison',
  fournisseur_id: props.marche.fournisseur_id || '',
  raison: '',
  articles: props.marche.articles?.map(article => ({
    ...article,
    prix_unitaire_ht: Number(article.prix_unitaire_ht || 0),
    montant_ht: Number(article.montant_ht || 0),
    montant_tva: Number(article.montant_tva || 0),
    montant_ttc: Number(article.montant_ttc || 0),
  })) || [],
})

const selectedFournisseur = computed(() => props.fournisseurs.find(f => Number(f.id) === Number(form.fournisseur_id)))

const totals = computed(() => form.articles.reduce((carry, article) => ({
  ht: carry.ht + Number(article.montant_ht || 0),
  tva: carry.tva + Number(article.montant_tva || 0),
  ttc: carry.ttc + Number(article.montant_ttc || 0),
}), { ht: 0, tva: 0, ttc: 0 }))

const canGoNext = computed(() => {
  if (form.statut === 'annule') return false
  if (activeStep.value === 0) return Boolean(form.fournisseur_id)
  if (activeStep.value === 1) return form.articles.length > 0 && form.articles.every(article => Number(article.prix_unitaire_ht || 0) > 0)
  return true
})

const canSubmit = computed(() => {
  if (form.statut === 'annule') {
    return form.raison.length >= 20 && confirmationAnnulation.value
  }

  return form.fournisseur_id && form.articles.every(article => Number(article.prix_unitaire_ht || 0) > 0)
})

watch(() => form.statut, () => {
  activeStep.value = 0
})

function calculateArticleMontants(index) {
  const article = form.articles[index]
  const prix = Number(article.prix_unitaire_ht || 0)
  const quantite = Number(article.quantite_commandee || 0)
  const taux = Number(article.taux_tva || 0)

  article.montant_ht = Number((prix * quantite).toFixed(2))
  article.montant_tva = Number((article.montant_ht * taux / 100).toFixed(2))
  article.montant_ttc = Number((article.montant_ht + article.montant_tva).toFixed(2))
}

function nextStep() {
  if (!canGoNext.value) return
  activeStep.value = Math.min(activeStep.value + 1, attributionSteps.length - 1)
}

function previousStep() {
  activeStep.value = Math.max(activeStep.value - 1, 0)
}

function submitUpdateStatut() {
  form
    .transform(data => ({
      ...data,
      articles: data.articles.map(article => ({
        id: article.id,
        prix_unitaire_ht: article.prix_unitaire_ht,
      })),
    }))
    .post(route('bon-commandes.statut', props.marche.id), {
      preserveScroll: true,
      onSuccess: () => validateMarcheModal.value.close(),
    })
}

function closeAllForms() {
  validateMarcheModal.value.close()
}

function formatCurrency(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    minimumFractionDigits: 2,
  }).format(Number(value || 0))
}
</script>

<template>
  <Modal ref="validateMarcheModal" max-width="5xl">
    <div class="mb-5">
      <p class="text-xs font-bold uppercase text-istaht-cyan">Attribution marche</p>
      <h2 class="mt-1 text-xl font-bold text-istaht-navy">{{ marche.reference }} - {{ marche.objet }}</h2>
      <p class="mt-1 text-sm text-slate-500">Choisissez le fournisseur attributaire, saisissez les prix HT, puis validez.</p>
    </div>

    <form @submit.prevent="submitUpdateStatut" class="space-y-6">
      <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <label class="block text-sm font-bold text-slate-700">Operation *</label>
        <select v-model="form.statut" required class="ui-input mt-1">
          <option value="attente_livraison">Attribuer le marche</option>
          <option value="annule">Annuler le marche</option>
        </select>
        <InputError :message="form.errors.statut" />
      </div>

      <template v-if="form.statut === 'attente_livraison'">
        <div class="grid gap-2 sm:grid-cols-3">
          <button
            v-for="(step, index) in attributionSteps"
            :key="step.label"
            type="button"
            class="rounded-lg border px-3 py-2 text-left transition"
            :class="activeStep === index ? 'border-istaht-blue bg-cyan-50 text-istaht-navy' : 'border-slate-200 bg-white text-slate-500'"
            @click="activeStep = index"
          >
            <span class="text-xs font-bold uppercase">Etape {{ index + 1 }}</span>
            <span class="mt-1 block text-sm font-bold">{{ step.label }}</span>
            <span class="mt-0.5 block text-xs">{{ step.caption }}</span>
          </button>
        </div>

        <section v-if="activeStep === 0" class="space-y-4 rounded-lg border border-cyan-100 bg-cyan-50 p-5">
          <div>
            <label class="block text-sm font-bold text-slate-700">Fournisseur attributaire *</label>
            <select v-model="form.fournisseur_id" required class="ui-input mt-1">
              <option value="">Selectionnez un fournisseur...</option>
              <option v-for="fournisseur in fournisseurs" :key="fournisseur.id" :value="fournisseur.id">
                {{ fournisseur.raison_sociale || fournisseur.nom }}
                <template v-if="fournisseur.ville"> - {{ fournisseur.ville }}</template>
              </option>
            </select>
            <InputError :message="form.errors.fournisseur_id" />
          </div>

          <div v-if="selectedFournisseur" class="rounded-lg border border-green-100 bg-white p-4">
            <p class="font-bold text-istaht-green">{{ selectedFournisseur.raison_sociale || selectedFournisseur.nom }}</p>
            <div class="mt-2 grid grid-cols-1 gap-2 text-sm text-slate-600 md:grid-cols-3">
              <span v-if="selectedFournisseur.telephone">Tel: {{ selectedFournisseur.telephone }}</span>
              <span v-if="selectedFournisseur.email">Email: {{ selectedFournisseur.email }}</span>
              <span v-if="selectedFournisseur.ice">ICE: {{ selectedFournisseur.ice }}</span>
            </div>
          </div>
        </section>

        <section v-if="activeStep === 1" class="space-y-3">
          <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm text-amber-700">
            Saisissez uniquement les prix unitaires HT. Les montants HT, TVA et TTC sont calcules automatiquement.
          </div>

          <div
            v-for="(article, index) in form.articles"
            :key="article.id"
            class="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 lg:grid-cols-[1fr_180px_1fr]"
          >
            <div>
              <p class="font-bold text-istaht-navy">{{ article.designation }}</p>
              <p class="mt-1 text-sm text-slate-500">
                {{ article.reference || '-' }} - {{ article.quantite_commandee }} {{ article.unite_mesure }} - TVA {{ article.taux_tva }}%
              </p>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase text-slate-400">Prix unitaire HT</label>
              <input
                v-model.number="article.prix_unitaire_ht"
                type="number"
                step="0.01"
                min="0"
                required
                class="ui-input mt-1"
                @input="calculateArticleMontants(index)"
              >
            </div>

            <div class="grid grid-cols-3 gap-2 text-sm">
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-400">HT</p>
                <p class="font-bold text-istaht-navy">{{ formatCurrency(article.montant_ht) }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-400">TVA</p>
                <p class="font-bold text-istaht-blue">{{ formatCurrency(article.montant_tva) }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-bold uppercase text-slate-400">TTC</p>
                <p class="font-bold text-istaht-green">{{ formatCurrency(article.montant_ttc) }}</p>
              </div>
            </div>
          </div>
        </section>

        <section v-if="activeStep === 2" class="space-y-4">
          <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
            <h3 class="text-base font-bold text-istaht-navy">Validation de l'attribution</h3>
            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Fournisseur</p>
                <p class="font-semibold text-slate-800">{{ selectedFournisseur?.raison_sociale || selectedFournisseur?.nom || '-' }}</p>
              </div>
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Articles valorises</p>
                <p class="font-semibold text-slate-800">{{ form.articles.length }}</p>
              </div>
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Total HT</p>
                <p class="font-bold text-istaht-navy">{{ formatCurrency(totals.ht) }}</p>
              </div>
              <div>
                <p class="text-xs font-bold uppercase text-slate-400">Total TTC</p>
                <p class="font-bold text-istaht-green">{{ formatCurrency(totals.ttc) }}</p>
              </div>
            </div>
          </div>
        </section>
      </template>

      <section v-if="form.statut === 'annule'" class="rounded-lg border border-red-200 bg-red-50 p-5">
        <h3 class="text-base font-bold text-istaht-red">Voulez-vous vraiment annuler ce marche ?</h3>
        <p class="mt-1 text-sm text-red-700">Le motif est obligatoire et sera conserve dans l'historique.</p>

        <div class="mt-4">
          <label class="block text-sm font-bold text-slate-700">Motif annulation *</label>
          <textarea
            v-model="form.raison"
            required
            rows="4"
            class="ui-input mt-1"
            placeholder="Expliquez le motif de l'annulation..."
          />
          <p class="mt-1 text-xs text-slate-500">Minimum 20 caracteres.</p>
          <InputError :message="form.errors.raison" />
        </div>

        <label class="mt-4 flex items-center gap-2 text-sm font-bold text-red-700">
          <input v-model="confirmationAnnulation" type="checkbox" class="rounded border-red-300 text-istaht-red">
          Je confirme l'annulation du marche
        </label>
      </section>

      <div class="flex justify-between gap-3 border-t border-slate-100 pt-5">
        <button type="button" class="ui-button ui-button-ghost" @click="closeAllForms">
          Fermer
        </button>

        <div class="flex gap-2">
          <button
            v-if="form.statut === 'attente_livraison' && activeStep > 0"
            type="button"
            class="ui-button ui-button-ghost"
            @click="previousStep"
          >
            Precedent
          </button>
          <button
            v-if="form.statut === 'attente_livraison' && activeStep < attributionSteps.length - 1"
            type="button"
            class="ui-button ui-button-primary"
            :disabled="!canGoNext"
            @click="nextStep"
          >
            Suivant
          </button>
          <button
            v-else
            type="submit"
            class="ui-button"
            :class="form.statut === 'annule' ? 'ui-button-danger' : 'ui-button-primary'"
            :disabled="form.processing || !canSubmit"
          >
            {{ form.processing ? 'Traitement...' : (form.statut === 'annule' ? 'Confirmer annulation' : 'Valider attribution') }}
          </button>
        </div>
      </div>
    </form>
  </Modal>
</template>
