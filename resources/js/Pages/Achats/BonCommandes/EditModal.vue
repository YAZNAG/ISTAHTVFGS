<script setup>
import { computed, ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  marche: { type: Object, required: true },
  tauxTVA: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  articles: { type: Array, default: () => [] },
})

const steps = [
  { label: 'Informations', caption: 'Reference, objet et dates' },
  { label: 'Categorie', caption: 'Recharge articles' },
  { label: 'Articles', caption: 'Liste automatique' },
  { label: 'Quantites', caption: 'Min, max et TVA' },
  { label: 'Validation', caption: 'Enregistrer' },
]

const activeStep = ref(0)
const editMarcheModal = ref(null)

const marcheForm = useForm({
  reference: props.marche.reference,
  objet: props.marche.objet,
  description: props.marche.description,
  date_debut: props.marche.date_debut,
  date_fin: props.marche.date_fin,
  date_mise_ligne: props.marche.date_mise_ligne,
  date_limite_reception: props.marche.date_limite_reception,
  categorie_id: props.marche.categorie_id,
  articles: props.marche.articles.map(article => ({
    article_id: article.id,
    reference: article.reference,
    designation: article.designation,
    unite_mesure: article.unite_mesure,
    quantite_minimale: Number(article.quantite_minimale ?? 0),
    quantite_maximale: Number(article.quantite_maximale ?? article.quantite_commandee ?? 1),
    taux_tva: Number(article.taux_tva ?? 0),
  })),
})

const selectedCategorie = computed(() => props.categories.find(cat => Number(cat.id) === Number(marcheForm.categorie_id)))

const totalQuantiteMax = computed(() => marcheForm.articles.reduce((total, row) => total + Number(row.quantite_maximale || 0), 0))

const canGoNext = computed(() => {
  if (activeStep.value === 0) {
    return marcheForm.reference && marcheForm.objet && marcheForm.date_debut && marcheForm.date_fin && marcheForm.date_mise_ligne && marcheForm.date_limite_reception
  }

  if (activeStep.value === 1 || activeStep.value === 2) {
    return marcheForm.categorie_id && marcheForm.articles.length > 0
  }

  if (activeStep.value === 3) {
    return marcheForm.articles.every(row => Number(row.quantite_maximale || 0) > 0 && Number(row.quantite_minimale || 0) <= Number(row.quantite_maximale || 0))
  }

  return true
})

function rowsForCategory() {
  return props.articles
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
  marcheForm.articles = rowsForCategory()
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
    .put(route('bon-commandes.updateModify', props.marche.id), {
      preserveScroll: true,
      onSuccess: () => editMarcheModal.value.close(),
    })
}
</script>

<template>
  <Modal ref="editMarcheModal" max-width="5xl">
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <p class="text-xs font-bold uppercase text-istaht-cyan">Modification marche</p>
        <h2 class="mt-1 text-xl font-bold text-istaht-navy">{{ marche.reference }}</h2>
        <p class="mt-1 text-sm text-slate-500">Le marche doit rester au statut Cree pour etre modifie.</p>
      </div>
      <span class="ui-badge bg-blue-50 text-istaht-blue ring-1 ring-blue-100">Statut: Cree</span>
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

    <form @submit.prevent="submitMarcheForm" class="space-y-6">
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
          <textarea v-model="marcheForm.description" rows="3" class="ui-input mt-1 w-full" />
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
          <p class="mt-1 text-sm text-slate-600">{{ marcheForm.articles.length }} article(s) actifs rattaches au marche.</p>
        </div>
      </section>

      <section v-if="activeStep === 2" class="space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-istaht-navy">Articles du marche</h3>
            <p class="text-sm text-slate-500">Changer de categorie remplace cette liste par les articles actifs de la nouvelle categorie.</p>
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

        <div v-if="marcheForm.categorie_id && marcheForm.articles.length === 0" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
          Aucun article actif n'est rattache a cette categorie.
        </div>
      </section>

      <section v-if="activeStep === 3" class="space-y-3">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-base font-bold text-istaht-navy">Quantites et TVA</h3>
            <p class="text-sm text-slate-500">Les prix restent geres dans l'attribution fournisseur.</p>
          </div>
          <span class="ui-badge bg-green-50 text-istaht-green ring-1 ring-green-100">Pas de prix ici</span>
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
          <h3 class="text-base font-bold text-istaht-navy">Validation de la modification</h3>
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
      </section>

      <div class="flex justify-between gap-3 border-t border-slate-100 pt-5">
        <button type="button" class="ui-button ui-button-ghost" @click="editMarcheModal.close()">
          Fermer
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
            {{ marcheForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </div>
    </form>
  </Modal>
</template>
