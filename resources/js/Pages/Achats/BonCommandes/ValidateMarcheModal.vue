<script setup>
/* ---------------------------------------------------------
   Imports
---------------------------------------------------------- */
import { ref, computed, reactive } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'
import Dump from '@/Components/Dump.vue'

/* ---------------------------------------------------------
   Props & Emits
---------------------------------------------------------- */
const props = defineProps({
  fournisseurs: { type: Array, default: () => [] },           // liste fournisseurs
  marche: { type: Object, required: true }                    // le marché en cours
})

console.log(props.marche);


/* ---------------------------------------------------------
   Refs
---------------------------------------------------------- */
const validateMarcheModal = ref(null)                          // accès au Modal
const confirmationAnnulation = ref(false)                      // case à cocher "annuler"

/* ---------------------------------------------------------
   Form Inertia
---------------------------------------------------------- */
const form = useForm({
  statut: props.marche.statut,
  fournisseur_id: props.marche.fournisseur_id || '',
  raison: '',
  articles: props.marche.articles?.map(a => reactive({
    ...a,
    prix_unitaire_ht: a.prix_unitaire_ht || 0,
    montant_ht: a.montant_ht || 0,
    montant_tva: a.montant_tva || 0,
    montant_ttc: a.montant_ttc || 0
  })) || []
})

/* ---------------------------------------------------------
   Computed
---------------------------------------------------------- */
/* montre la section fournisseur + prix uniquement si on passe en attente_livraison */
const showFournisseurAndPrixSection = computed(
  () => form.statut === 'attente_livraison'
)

/* fournisseur sélectionné pour l’affichage des détails */
const selectedFournisseur = computed(
  () => props.fournisseurs.find(f => f.id === form.fournisseur_id)
)

/* bouton grisé si formulaire invalide */
const isformValid = computed(() => {
  if (form.statut === 'annule') {
    return form.raison.length >= 20 && confirmationAnnulation.value
  }
  if (form.statut === 'attente_livraison') {
    return form.fournisseur_id && form.articles.every(a => +a.prix_unitaire_ht > 0)
  }
  return true
})

/* texte du bouton principal */
const getSubmitButtonText = computed(() =>
  form.statut === 'annule' ? 'Confirmer l\'annulation' : 'Enregistrer'
)

/* ---------------------------------------------------------
   Methods
---------------------------------------------------------- */
/* calculs automatiques pour une ligne article */
function calculateArticleMontants(index) {
  const art = form.articles[index]
  const pu = parseFloat(art.prix_unitaire_ht) || 0
  const qte = parseFloat(art.quantite_commandee) || 0
  const taux = parseFloat(art.taux_tva) || 0

  art.montant_ht = +(pu * qte).toFixed(2)
  art.montant_tva = +(art.montant_ht * taux / 100).toFixed(2)
  art.montant_ttc = +(art.montant_ht + art.montant_tva).toFixed(2)
}

/* envoi */
function submitUpdateStatut() {
    console.log(form.data());
    
  form
    .transform(d => ({
      ...d,
      articles: d.articles.map(a => ({
        id: a.id,
        prix_unitaire_ht: a.prix_unitaire_ht,
      }))
    }))
    .post(route('bon-commandes.statut', props.marche.id), {
      preserveScroll: true,
      onSuccess: () => {
        validateMarcheModal.value.close()
        form.reset()
      }
    })
}

/* fermeture sans sauver */
function closeAllForms() {
  validateMarcheModal.value.close()
  form.reset()
}


/* helper format monnaie */
function formatCurrency(val) {
  return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH'
}
</script>

<template>
    <Modal ref="validateMarcheModal" max-width="4xl">
        <!-- Header -->
        <div class="mb-2">
            <h2 class="text-lg font-semibold text-gray-900">Validation du marché</h2>
            <p class="text-sm text-gray-500 mt-1">
                Finalisez le marché en choisissant un statut, un fournisseur et les prix unitaires.
            </p>
        </div>

        <form @submit.prevent="submitUpdateStatut" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nouveau statut *</label>
                        <select v-model="form.statut" required
                            class="mt-1 block w-full border border-slate-300 rounded-md shadow-sm p-2">
                            <option value="attente_livraison">En attente livraison</option>

                            <option value="annule">Annuler</option>
                        </select>
                    </div>

                    <!-- Fournisseur et Prix (visible seulement pour les statuts de livraison) -->
                    <!-- Dans la section Articles du formulaire d'édition -->
                    <!-- Section Fournisseur simplifiée -->
                    <div v-if="showFournisseurAndPrixSection" class="space-y-6 border-t border-slate-200 pt-6">

                        <!-- Sélection du fournisseur -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-blue-900">Sélection du fournisseur</h4>
                                <span
                                    class="text-sm text-blue-600 bg-blue-100 px-3 py-1 rounded-full">Obligatoire</span>
                            </div>

                            <div class="space-y-4">
                                <!-- Sélection fournisseur existant -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-3">
                                        Choisir un fournisseur *
                                    </label>
                                    <select v-model="form.fournisseur_id" required
                                        class="w-full border border-slate-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        @change="onFournisseurChange">
                                        <option value="">Sélectionnez un fournisseur...</option>
                                        <option v-for="fournisseur in fournisseurs" :key="fournisseur.id"
                                            :value="fournisseur.id" class="py-2">
                                            {{ fournisseur.raison_sociale || fournisseur.nom }}
                                            <template v-if="fournisseur.contact"> - {{ fournisseur.contact }}</template>
                                            <template v-if="fournisseur.ville"> ({{ fournisseur.ville }})</template>
                                        </option>
                                    </select>

                                    <p class="text-xs text-slate-500 mt-2">
                                        Sélectionnez le fournisseur qui livrera cette commande
                                    </p>
                                </div>

                                <!-- Informations du fournisseur sélectionné -->
                                <div v-if="selectedFournisseur"
                                    class="p-4 bg-white border border-green-200 rounded-lg animate-fade-in">
                                    <h5 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                                        <CheckCircleIcon class="h-5 w-5 text-green-500" />
                                        Fournisseur sélectionné
                                    </h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-slate-700">
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="font-medium">Nom :</span>
                                                <span class="text-green-600">{{ selectedFournisseur.raison_sociale ||
                                                    selectedFournisseur.nom }}</span>
                                            </div>
                                            <div v-if="selectedFournisseur.contact" class="flex justify-between">
                                                <span class="font-medium">Contact :</span>
                                                <span>{{ selectedFournisseur.contact }}</span>
                                            </div>
                                            <div v-if="selectedFournisseur.telephone" class="flex justify-between">
                                                <span class="font-medium">Téléphone :</span>
                                                <span class="text-blue-600">{{ selectedFournisseur.telephone }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-if="selectedFournisseur.email" class="flex justify-between">
                                                <span class="font-medium">Email :</span>
                                                <span class="text-blue-600">{{ selectedFournisseur.email }}</span>
                                            </div>
                                            <div v-if="selectedFournisseur.ville" class="flex justify-between">
                                                <span class="font-medium">Ville :</span>
                                                <span>{{ selectedFournisseur.ville }}</span>
                                            </div>
                                            <div v-if="selectedFournisseur.ice" class="flex justify-between">
                                                <span class="font-medium">ICE :</span>
                                                <span class="font-mono">{{ selectedFournisseur.ice }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message si aucun fournisseur sélectionné -->
                                <div v-else-if="form.fournisseur_id === ''"
                                    class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-500" />
                                        <p class="text-sm text-yellow-700">
                                            Veuillez sélectionner un fournisseur pour continuer
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Message d'erreur de validation -->
                            <div v-if="form.errors.fournisseur_id"
                                class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <ExclamationTriangleIcon class="h-4 w-4 text-red-500" />
                                    <p class="text-sm text-red-700">{{ form.errors.fournisseur_id }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section Articles -->
                        <div class="border border-slate-200 rounded-xl p-6 bg-white">
                            <h4 class="text-xl font-semibold text-slate-900 mb-6">Prix unitaires HT des articles</h4>

                            <div v-if="form.articles.length === 0" class="text-center py-8 bg-slate-50 rounded-lg">
                                <DocumentTextIcon class="mx-auto h-12 w-12 text-slate-400" />
                                <p class="text-slate-500 mt-2">Aucun article dans ce marché</p>
                            </div>

                            <!-- Vos articles ici... -->
                            <div v-for="(articleForm, index) in form.articles" :key="articleForm.id"
                                class="border border-slate-200 rounded-lg p-6 mb-6 bg-slate-50">

                                <!-- En-tête de l'article -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 pb-6 border-b border-slate-200">
                                    <div>
                                        <h4 class="font-semibold text-slate-900 text-lg">{{ articleForm.designation }}
                                        </h4>
                                        <div class="text-sm text-slate-600 mt-3 space-y-1">
                                            <div><strong class="text-slate-700">Référence:</strong> {{
                                                articleForm.reference }}</div>
                                            <div><strong class="text-slate-700">Quantité commandée:</strong> {{
                                                articleForm.quantite_commandee }}
                                                {{ articleForm.unite_mesure }}</div>
                                        </div>
                                    </div>
                                    <div class="text-sm text-slate-600 space-y-1">
                                        <div><strong class="text-gray-700">TVA appliquée:</strong> {{
                                            articleForm.taux_tva }}%</div>
                                    </div>
                                </div>

                                <!-- Prix et calculs -->
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Prix unitaire HT (DH) *
                                        </label>
                                        <input v-model="articleForm.prix_unitaire_ht" type="number" step="0.01" min="0"
                                            required @input="calculateArticleMontants(index)"
                                            class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="0.00">
                                    </div>

                                    <div class="text-sm bg-green-50 p-4 rounded-lg border border-green-200">
                                        <div class="font-semibold text-gray-700 mb-2">Détails TVA</div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span>Taux:</span>
                                                <span class="font-bold text-green-600">{{ articleForm.taux_tva
                                                    }}%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-sm bg-blue-50 p-4 rounded-lg border border-blue-200">
                                        <div class="font-semibold text-gray-700 mb-2">Calculs automatiques</div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span>Montant HT:</span>
                                                <strong class="text-blue-600">{{ formatCurrency(articleForm.montant_ht
                                                    || 0) }}</strong>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Montant TVA:</span>
                                                <strong class="text-blue-600">{{ formatCurrency(articleForm.montant_tva
                                                    || 0) }}</strong>
                                            </div>
                                            <div class="flex justify-between border-t border-blue-200 pt-2 mt-2">
                                                <span class="font-semibold">Montant TTC:</span>
                                                <strong class="text-blue-700">{{ formatCurrency(articleForm.montant_ttc
                                                    || 0) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Confirmation d'annulation améliorée -->
                    <div v-if="form.statut === 'annule'" class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <ExclamationTriangleIcon class="h-6 w-6 text-red-400" />
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-red-800">
                                    Confirmation d'annulation
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p class="font-semibold">Attention : Cette action est irréversible !</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>Le marché sera marqué comme annulé</li>
                                        <li>Le fournisseur attribué sera retiré</li>
                                        <li>Les prix saisis seront réinitialisés</li>
                                        <li>Cette action ne peut pas être annulée</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Raison de l'annulation *</label>
                            <textarea v-model="form.raison" required rows="4"
                                class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm p-3"
                                placeholder="Veuillez expliquer en détail la raison de l'annulation de ce marché..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimum 20 caractères</p>
                        </div>

                        <div class="mt-4 flex items-center">
                            <input v-model="confirmationAnnulation" type="checkbox"
                                class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <label class="ml-2 block text-sm text-red-700 font-medium">
                                Je confirme que je souhaite annuler ce marché
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <button type="button" @click="closeAllForms"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            :disabled="form.processing || !isformValid">
                            <span v-if="form.processing">Mise à jour...</span>
                            <span v-else>{{ getSubmitButtonText }}</span>
                        </button>
                    </div>
                </form>

    </Modal>
</template>
