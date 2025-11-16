<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'

const props = defineProps({
  restaurant: {          // ← renamed prop
    type: Object,
    required: true
  }
})

const modalRef = ref(null)

function closeModal() {
  modalRef.value?.close()
}
</script>

<template>
  <Modal ref="modalRef" max-width="3xl" class="overflow-y-scroll">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center pb-3">
        <h2 class="text-2xl font-bold">Détail du restaurant</h2>
      </div>

      <!-- Restaurant Info -->
      <div class="bg-white border rounded-lg p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-3">Informations générales</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <p><span class="font-bold">Nom :</span> {{ restaurant.nom }}</p>
          <p><span class="font-bold">Effectif :</span> {{ restaurant.effectif }}</p>
          <p><span class="font-bold">Créé le :</span> {{ restaurant.created_at }}</p>
        </div>
      </div>

      <!-- Articles -->
        <table class="min-w-full text-sm border">
          <thead class="bg-gray-100 text-left">
            <tr>
              <th class="px-3 py-2 border">Référence</th>
              <th class="px-3 py-2 border">Désignation</th>
              <th class="px-3 py-2 border">Unité</th>
              <th class="px-3 py-2 border">Prix Unitaire</th>
              <th class="px-3 py-2 border">Quantité</th>
              <th class="px-3 py-2 border">TVA (%)</th>
              <th class="px-3 py-2 border">Total TTC</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(ing, idx) in restaurant.ingredients"
              :key="idx"
              class="hover:bg-gray-50"
            >
              <td class="px-3 py-2 border">{{ ing.code }}</td>
              <td class="px-3 py-2 border">{{ ing.designation }}</td>
              <td class="px-3 py-2 border">{{ ing.unite_mesure }}</td>
              <td class="px-3 py-2 border">{{ ing.prix_unitaire }}</td>
              <td class="px-3 py-2 border">{{ ing.quantite }}</td>
              <td class="px-3 py-2 border">{{ ing.taux_tva }}</td>
              <td class="px-3 py-2 border">{{ ing.total_ttc }} DH</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="flex justify-end border-t pt-4">
        <button
          @click="closeModal"
          class="px-4 py-2 rounded-lg border text-sm font-medium hover:bg-gray-100"
        >
          Fermer
        </button>
      </div>
  </Modal>
</template>