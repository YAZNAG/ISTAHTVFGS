<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { data } from 'autoprefixer'
import Dump from '@/Components/Dump.vue'

const props = defineProps({
    fiche: {
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
    <Modal ref="modalRef" max-width="5xl" class="overflow-y-scroll">
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex justify-between items-center pb-3">
                <h2 class="text-2xl font-bold">
                    Fiche Technique {{ fiche.type }}
                </h2>
            </div>

            <!-- Fiche Info -->
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-3">Informations générales</h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <p>
                        <span class="font-bold">Type :</span>
                        <span
                            class="ml-2 px-2 py-1 rounded text-sm font-semibold"
                            :class="fiche.type_label === 'pedagogique'
                            ? 'bg-blue-100 text-blue-800'
                            : 'bg-green-100 text-green-800'"
                        >
                            {{ fiche.type }}
                        </span>
                    </p>
                    <p><span class="font-bold">{{ fiche.type_label == 'pedagogique' ? 'Module: ' : 'Repas: ' }}</span> {{ fiche.repas }}</p>
                    <p><span class="font-bold">{{ fiche.type_label == 'pedagogique' ? 'Formatteur: ' : 'Chef de cuisine: ' }}</span> {{ fiche.responsable }}</p>
                    <p><span class="font-bold">Plat :</span> {{ fiche.plat }}</p>
                    <p><span class="font-bold">Effectif :</span> {{ fiche.effectif }}</p>
                    <p><span class="font-bold">Créé par :</span> {{ fiche.created_by }}</p>
                </div>
            </div>

            <!-- Steps -->
            <div class="space-y-6">
                <div class="grid grid-cols-3 gap-2">
                    <div v-for="(etape, index) in fiche.etapes" :key="etape.id" class="border rounded-lg p-5 bg-gray-50">
                        <h4 class="font-semibold text-base mb-3">Étape {{ ++index }} : {{ etape.title }}</h4>
                        <p>{{ etape.description }}</p>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold text-base mb-3">Ingrédients</h4>
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="px-3 py-2 border">Reference</th>
                                <th class="px-3 py-2 border">Code</th>
                                <th class="px-3 py-2 border">Unité</th>
                                <th class="px-3 py-2 border">Prix Unitaire</th>
                                <th class="px-3 py-2 border">Quantité</th>
                                <th class="px-3 py-2 border">TVA (%)</th>
                                <th class="px-3 py-2 border">Total TTC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in fiche.ingredients" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-3 py-2 border">{{ item.code }}</td>  
                                <td class="px-3 py-2 border">{{ item.designation }}</td>
                                <td class="px-3 py-2 border">{{ item.unite_mesure }}</td>
                                <td class="px-3 py-2 border">{{ item.prix_unitaire }}</td>
                                <td class="px-3 py-2 border">{{ item.quantite }}</td>
                                <td class="px-3 py-2 border">{{ item.taux_tva }}</td>
                                <td class="px-3 py-2 border">{{ item.total_ttc }}</td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            

            <!-- Footer -->
            <div class="flex justify-end border-t pt-4">
                <button @click="closeModal" class="px-4 py-2 rounded-lg border text-sm font-medium hover:bg-gray-100">
                    Fermer
                </button>
            </div>
        </div>
    </Modal>
</template>