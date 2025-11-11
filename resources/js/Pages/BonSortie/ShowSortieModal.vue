<script setup>
import { getSortieStatutInfo } from '@/Utils/labels';
import {
  ClipboardDocumentListIcon,
  ClockIcon,
  CheckCircleIcon,
  XCircleIcon,
  UserIcon,
  CalendarDaysIcon,
  ArrowLeftIcon,
} from '@heroicons/vue/24/outline'
import { Modal } from '@inertiaui/modal-vue'
import { ref } from 'vue'

const props = defineProps({
  sortie: Object, 
})

const showSortieModalRef = ref(null);

// Label maps
const typeLabels = {
  vente: 'Vente',
  transfert: 'Transfert',
  perte: 'Perte',
  ajustement: 'Ajustement',
  demande: 'Demande',
}

const statutLabels = {
  cree: 'Créée',
  attente_validation: 'En attente de validation',
  valide: 'Validée',
  annule: 'Annulée',
}
</script>

<template>

<Modal ref="showSortieModalRef">
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
        <ClipboardDocumentListIcon class="w-7 h-7 text-blue-600" />
        Détails de la sortie
      </h1>
    
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 gap-4">
      <div class="bg-white p-5 rounded-xl border shadow-sm flex items-center gap-4">
        <div class="bg-blue-100 p-3 rounded-full">
          <ClipboardDocumentListIcon class="w-6 h-6 text-blue-600" />
        </div>
        <div>
          <p class="text-gray-500 text-sm">Type</p>
          <p class="text-lg font-semibold text-blue-600">{{ typeLabels[sortie.type] }}</p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-xl border shadow-sm flex items-center gap-4">
        <div class="bg-gray-100 p-3 rounded-full">
          <CalendarDaysIcon class="w-6 h-6 text-gray-600" />
        </div>
        <div>
          <p class="text-gray-500 text-sm">Date</p>
          <p class="text-lg font-semibold text-gray-800">{{ sortie.date }}</p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-xl border shadow-sm flex items-center gap-4">
        <div class="bg-green-100 p-3 rounded-full" v-if="sortie.statut === 'valide'">
          <CheckCircleIcon class="w-6 h-6 text-green-600" />
        </div>
        <div class="bg-yellow-100 p-3 rounded-full" v-else-if="sortie.statut === 'attente_validation'">
          <ClockIcon class="w-6 h-6 text-yellow-600" />
        </div>
        <div class="bg-red-100 p-3 rounded-full" v-else-if="sortie.statut === 'annule'">
          <XCircleIcon class="w-6 h-6 text-red-600" />
        </div>
        <div class="bg-gray-100 p-3 rounded-full" v-else>
          <ClipboardDocumentListIcon class="w-6 h-6 text-gray-600" />
        </div>
        <div>
          <p class="text-gray-500 text-sm">Statut</p>
          <p class="text-lg font-semibold px-3 rounded" :class="getSortieStatutInfo(sortie.statut).color">
            {{ getSortieStatutInfo(sortie.statut).label }}
          </p>
        </div>
      </div>

      <div class="bg-white p-5 rounded-xl border shadow-sm flex items-center gap-4">
        <div class="bg-purple-100 p-3 rounded-full">
          <UserIcon class="w-6 h-6 text-purple-600" />
        </div>
        <div>
          <p class="text-gray-500 text-sm">Créée par</p>
          <p class="text-lg font-semibold text-gray-800">{{ sortie.created_by || '—' }}</p>
        </div>
      </div>
    </div>

    <!-- Demande info (if exists) -->
    <div v-if="sortie.demandeur" class="bg-white rounded-xl shadow-sm border p-6 space-y-2">
      <h2 class="text-lg font-semibold text-gray-800">Informations de la demande</h2>
      <p><span class="font-medium text-gray-700">Demandeur:</span> {{ sortie.demandeur || '—' }}</p>
    </div>

    <!-- Articles list -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm border-collapse">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-4 py-2 text-left font-medium text-gray-600 border-r">Article</th>
            <th class="px-4 py-2 text-left font-medium text-gray-600 border-r">Quantité</th>
            <th class="px-4 py-2 text-left font-medium text-gray-600 border-r">Unité</th>
            <th class="px-4 py-2 text-left font-medium text-gray-600 border-r">Taux TVA</th>
            <th class="px-4 py-2 text-left font-medium text-gray-600">Prix unitaire</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(article, index) in sortie.articles"
            :key="index"
            class="border-b hover:bg-gray-50"
          >
            <td class="px-4 py-2">{{ article.designation }}</td>
            <td class="px-4 py-2">{{ article.quantite }}</td>
            <td class="px-4 py-2">{{ article.unite_mesure }}</td>
            <td class="px-4 py-2">{{ article.taux_tva }}</td>
            <td class="px-4 py-2">{{ article.prix_unitaire }} DH</td>
            
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</Modal>
</template>
