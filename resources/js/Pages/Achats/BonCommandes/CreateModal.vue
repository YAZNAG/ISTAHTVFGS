<script setup>
import { computed, ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  tauxTVA: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  articles: { type: Array, default: () => [] },
})

const steps = [
  { label: 'Informations', caption: 'Reference, objet et dates' },
  { label: 'Categorie', caption: 'Famille alimentaire' },
  { label: 'Articles', caption: 'Chargement automatique' },
  { label: 'Quantites', caption: 'Min, max et TVA' },
  { label: 'Validation', caption: 'Enregistrer en cree' },
]

const activeStep = ref(0)
const createMarcheModal = ref(null)

const marcheForm = useForm({
  reference: '',
  objet: '',
  description: '',
  date_debut: '',
  date_fin: '',
  date_mise_ligne: '',
  date_limite_reception: '',
  notes: '',
  pieces_jointes: [],
  categorie_id: '',
  articles: [],
})

const selectedCategorie = computed(() => props.categories.find(cat => Number(cat.id) === Number(marcheForm.categorie_id)))

const totalQuantiteMax = computed(() => marcheForm.articles.reduce((total, row) => total + Number(row.quantite_maximale || 0), 0))

const canGoNext = computed(() => {
  if (activeStep.value === 0) {
    return marcheForm.reference && marcheForm.objet && marcheForm.date_debut && marcheForm.date_fin && marcheForm.date_mise_ligne && marcheForm.date_limite_reception
  }

  if (activeStep.value === 1) {
    return marcheForm.categorie_id && marcheForm.articles.length > 0
  }

  if (activeStep.value === 2) {
    return marcheForm.articles.length > 0
  }

  if (activeStep.value === 3) {
    return marcheForm.articles.every(row => Number(row.quantite_maximale || 0) > 0 && Number(row.quantite_minimale || 0) <= Number(row.quantite_maximale || 0))
  }

  return true
})

function buildRowsForCategory() {
  marcheForm.articles = props.articles
    .filter(article => Number(article.categorie_id) === Number(marcheForm.categorie_id))
    .map(article => ({
      article_id: article.id,
      reference: article.reference,
      designation: article.designation,
      unite_mesure: article.unite_mesure,
      quantite_minimale: 0,
      quantite_maximale: 1,
      taux_tva: props.tauxTVA.includes(20) ? 20 : props.tauxTVA[0] ?? 0,
    }))
}

function onCategorieChange() {
  buildRowsForCategory()
}

function onFilesChange(event) {
  marcheForm.pieces_jointes = Array.from(event.target.files || [])
}

function nextStep() {
  if (!canGoNext.value) return
  activeStep.value = Math.min(activeStep.value + 1, steps.length - 1)
}

function previousStep() {
  activeStep.value = Math.max(activeStep.value - 1, 0)
}

function getTvaLabel(taux) {
  return Number(taux) === 0 ? 'Exonere' : `${taux} %`
}

function submitMarcheForm() {
  marcheForm
    .transform(data => ({
      ...data,
      articles: data.articles.map(article => ({
        article_id: article.article_id,
        quantite_minimale: article.quantite_minimale,
        quantite_maximale: article.quantite_maximale,
        taux_tva: article.taux_tva,
      })),
    }))
    .post(route('bon-commandes.store'), {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => createMarcheModal.value.close(),
    })
}
</script>

<template>
  <Modal ref="createMarcheModal" max-width="5xl">
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <p class="text-xs font-bold uppercase text-istaht-cyan">Assistant marche</p>
        <h2 class="mt-1 text-xl font-bold text-istaht-navy">Creer un marche</h2>
        <p class="mt-1 text-sm text-slate-500">Categorie, articles automatiques, quantites min/max et TVA.</p>
      </div>
      <span class="ui-badge bg-cyan-50 text-istaht-blue ring-1 ring-cyan-100">Statut final: Cree</span>
    </div>

    <div class="mb-6 grid gap-2 sm:grid-cols-5">
      <button
        v-for="(step, index) in steps"
        :key="step.label"
        type="button"
        class="rounded-lg border px-3 py-2 text-left transition"
        :class="activeStep === index ? 'border-istaht-blue bg-cyan-50 text-istaht-navy' : 'border-slate-200 bg-slate-50 text-slate-500'"
        @click="activeStep = index"
      >
        <span class="text-xs font-bold uppercase">Etape {{ index + 1 }}</span>
        <span class="mt-1 block text-sm font-bold">{{ step.label }}</span>
        <span class="mt-0.5 block text-xs">{{ step.caption }}</span>
      </button>
    </div>

    <form @submit.prevent="submitMarcheForm" class="space-y-6" enctype="multipart/form-data">
      <section v-if="activeStep === 0" class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <label class="block text-sm font-bold text-slate-700">Reference marche *</label>
          <input v-model="marcheForm.reference" type="text" required class="ui-input mt-1" placeholder="MR-2026-001">
          <InputError :message="marcheForm.errors.reference" />
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700">Objet *</label>
          <input v-model="marcheForm.objet" type="text" required class="ui-input mt-1" placeholder="Approvisionnement alimentaire">
          <InputError :message="marcheForm.errors.objet" />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-bold text-slate-700">Description</label>
          <textarea v-model="marcheForm.description" rows="3" class="ui-input mt-1" />
          <InputError :message="marcheForm.errors.description" />
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700">Date debut *</label>
          <input v-model="marcheForm.date_debut" type="date" required class="ui-input mt-1">
          <InputError :message="marcheForm.errors.date_debut" />
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700">Date fin *</label>
          <input v-model="marcheForm.date_fin" type="date" required :min="marcheForm.date_debut" class="ui-input mt-1">
          <InputError :message="marcheForm.errors.date_fin" />
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700">Date mise en ligne *</label>
          <input v-model="marcheForm.date_mise_ligne" type="date" required class="ui-input mt-1">
          <InputError :message="marcheForm.errors.date_mise_ligne" />
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700">Date limite reception offres *</label>
          <input v-model="marcheForm.date_limite_reception" type="date" required :min="marcheForm.date_mise_ligne" class="ui-input mt-1">
          <InputError :message="marcheForm.errors.date_limite_reception" />
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700">Pieces jointes</label>
          <input type="file" multiple class="ui-input mt-1" @change="onFilesChange">
          <InputError :message="marcheForm.errors.pieces_jointes" />
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700">Notes</label>
          <textarea v-model="marcheForm.notes" rows="2" class="ui-input mt-1" />
        </div>
      </section>

      <section v-if="activeStep === 1" class="space-y-4">
        <div>
          <label class="block text-sm font-bold text-slate-700">Categorie *</label>
          <select v-model="marcheForm.categorie_id" required class="ui-input mt-1" @change="onCategorieChange">
            <option value="">Selectionnez une categorie...</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.code || '-' }} - {{ cat.nom }}
            </option>
          </select>
          <InputError :message="marcheForm.errors.categorie_id" />
        </div>

        <div v-if="selectedCategorie" class="rounded-lg border border-cyan-100 bg-cyan-50 p-4">
          <p class="text-sm font-bold text-istaht-navy">{{ selectedCategorie.nom }}</p>
          <p class="mt-1 text-sm text-slate-600">{{ marcheForm.articles.length }} article(s) actifs charges automatiquement.</p>
        </div>

        <div v-if="marcheForm.categorie_id && marcheForm.articles.length === 0" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
          Aucun article actif n'est rattache a cette categorie.
        </div>
      </section>

      <section v-if="activeStep === 2" class="space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-istaht-navy">Articles charges automatiquement</h3>
            <p class="text-sm text-slate-500">Aucune ressaisie article n'est necessaire.</p>
          </div>
          <span class="ui-badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ marcheForm.articles.length }} article(s)</span>
        </div>

        <div class="max-h-[420px] overflow-y-auto rounded-lg border border-slate-200">
          <div
            v-for="row in marcheForm.articles"
            :key="row.article_id"
            class="grid grid-cols-[120px_1fr_90px] gap-3 border-b border-slate-100 p-3 last:border-b-0"
          >
            <span class="font-mono text-sm font-bold text-istaht-blue">{{ row.reference || '-' }}</span>
            <span class="font-semibold text-slate-800">{{ row.designation }}</span>
            <span class="text-right text-sm text-slate-500">{{ row.unite_mesure || '-' }}</span>
          </div>
        </div>
      </section>

      <section v-if="activeStep === 3" class="space-y-3">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-base font-bold text-istaht-navy">Quantites et TVA</h3>
            <p class="text-sm text-slate-500">Saisissez uniquement les quantites minimale/maximale et la TVA.</p>
          </div>
          <span class="ui-badge bg-green-50 text-istaht-green ring-1 ring-green-100">Aucun prix a cette etape</span>
        </div>

        <div class="space-y-3">
          <div
            v-for="row in marcheForm.articles"
            :key="row.article_id"
            class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_150px_150px_120px]"
          >
            <div>
              <label class="block text-xs font-bold uppercase text-slate-400">Article</label>
              <div class="mt-1 rounded-md border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-800">
                {{ row.designation }}
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase text-slate-400">Quantite min</label>
              <input v-model.number="row.quantite_minimale" type="number" min="0" step="0.01" class="ui-input mt-1">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase text-slate-400">Quantite max</label>
              <input v-model.number="row.quantite_maximale" type="number" min="0.01" step="0.01" class="ui-input mt-1">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase text-slate-400">TVA</label>
              <select v-model.number="row.taux_tva" required class="ui-input mt-1">
                <option v-for="taux in tauxTVA" :key="taux" :value="taux">{{ getTvaLabel(taux) }}</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <section v-if="activeStep === 4" class="space-y-4">
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
          <h3 class="text-base font-bold text-istaht-navy">Validation du marche</h3>
          <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            <div>
              <p class="text-xs font-bold uppercase text-slate-400">Reference</p>
              <p class="font-semibold text-slate-800">{{ marcheForm.reference || '-' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase text-slate-400">Categorie</p>
              <p class="font-semibold text-slate-800">{{ selectedCategorie?.nom || '-' }}</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase text-slate-400">Articles</p>
              <p class="font-semibold text-slate-800">{{ marcheForm.articles.length }} article(s)</p>
            </div>
            <div>
              <p class="text-xs font-bold uppercase text-slate-400">Quantite max totale</p>
              <p class="font-semibold text-slate-800">{{ totalQuantiteMax.toLocaleString('fr-FR') }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-green-700">
          Le marche sera enregistre avec le statut <strong>Cree</strong>. L'attribution fournisseur et les prix HT se feront dans l'etape d'attribution.
        </div>
      </section>

      <div class="flex justify-between gap-3 border-t border-slate-100 pt-5">
        <button type="button" class="ui-button ui-button-ghost" @click="createMarcheModal.close()">
          Annuler
        </button>

        <div class="flex gap-2">
          <button v-if="activeStep > 0" type="button" class="ui-button ui-button-ghost" @click="previousStep">
            Precedent
          </button>
          <button v-if="activeStep < steps.length - 1" type="button" class="ui-button ui-button-primary" :disabled="!canGoNext" @click="nextStep">
            Suivant
          </button>
          <button
            v-else
            type="submit"
            class="ui-button ui-button-primary"
            :disabled="marcheForm.processing || marcheForm.articles.length === 0"
          >
            {{ marcheForm.processing ? 'Creation...' : 'Creer le marche' }}
          </button>
        </div>
      </div>
    </form>
  </Modal>
</template>
