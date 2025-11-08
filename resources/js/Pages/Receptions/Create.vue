<script setup>
import { ref, reactive, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';

const props = defineProps({
    bonLivraisons: Array,
})

const search = ref('')
const isOpen = ref(false)
const createReceptionModal = ref(null)

const form = useForm({
    bon_livraison_id: '',
    bon: null
});

const filteredBonLivraisons = computed(() =>
  props.bonLivraisons.filter(bon =>
    bon.numero.toLowerCase().includes(search.value.toLowerCase())
  )
)

function selectBon(bon) {
  form.bon_livraison_id = bon.id
  search.value = bon.numero
  isOpen.value = false
}


// Submit form
function submit() {
    form.post(route('bon-receptions.store'), {
        onSuccess: () => {
            form.reset()
            createReceptionModal.value.close()
        }
    })

    console.log(form.data());

}

</script>

<template>
    <Modal ref="createReceptionModal" size="3xl" class="overflow-y-scroll">
        <div class="mb-4">
            <h2 class="text-lg font-semibold">Créer un bon de réception</h2>
        </div>

        <div>
            <form @submit.prevent="submit" class="space-y-4">

                <div class="grid grid-cols-1 gap-4">
                    <!-- Bon de livraison select -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bon de livraison</label>

                        <!-- Input field -->
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Rechercher un bon de livraison..."
                            @focus="isOpen = true"
                            @input="isOpen = true"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />

                        <!-- Dropdown -->
                        <div
                            v-if="isOpen"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                        >
                            <div
                            v-for="bon in filteredBonLivraisons"
                            :key="bon.id"
                            @click="selectBon(bon)"
                            class="px-3 py-2 cursor-pointer hover:bg-indigo-50 text-sm"
                            >
                            <p class="font-medium text-gray-800">{{ bon.numero }}</p>
                            <p class="text-gray-500 text-xs">{{ bon.date_livraison }}</p>
                            </div>

                            <p v-if="!filteredBonLivraisons.length" class="p-3 text-gray-500 text-sm text-center">
                            Aucun résultat
                            </p>
                        </div>

                        <!-- Error message -->
                        <p v-if="form.errors.bon_livraison_id" class="text-sm text-red-600 mt-1">
                            {{ form.errors.bon_livraison_id }}
                        </p>
                        </div>

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bon
                        </label>
                        <input @change="form.bon = $event.target.files[0]" type="file"
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        <p v-if="form.errors.bon" class="text-sm text-red-600 mt-1">{{ form.errors.bon }}</p>
                    </div>

                </div>

            </form>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="createReceptionModal.close()"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                Annuler
            </button>
            <button type="button" @click="submit" :disabled="form.processing"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                Enregistrer
            </button>
        </div>
    </Modal>
</template>
