<script setup>
import { ref, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, TruckIcon, DocumentArrowUpIcon } from '@heroicons/vue/24/outline';
import { onClickOutside } from '@vueuse/core';

const props = defineProps({
    bonLivraisons: Array,
})

const search = ref('')
const isOpen = ref(false)
const createReceptionModal = ref(null)
const dropdownRef = ref(null)

const form = useForm({
    bon_livraison_id: '',
    bon: null
});

const filteredBonLivraisons = computed(() =>
  props.bonLivraisons.filter(bon =>
    bon.numero.toLowerCase().includes(search.value.toLowerCase())
  )
)

const selectedBon = computed(() =>
  props.bonLivraisons.find(b => b.id === form.bon_livraison_id) || null
)

function selectBon(bon) {
  form.bon_livraison_id = bon.id
  search.value = bon.numero
  isOpen.value = false
}

function submit() {
    form.post(route('bon-receptions.store'), {
        onSuccess: () => {
            form.reset()
            createReceptionModal.value.close()
        }
    })
}

onClickOutside(dropdownRef, () => {
  isOpen.value = false
})
</script>

<template>
    <Modal ref="createReceptionModal" size="3xl">
        <!-- ═══ En-tête ═══ -->
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h2 class="text-lg font-bold text-istaht-navy">Nouveau bon de réception</h2>
            <p class="mt-1 text-sm text-slate-500">
                Sélectionnez le bon de livraison <strong>livré</strong> à réceptionner, puis joignez le document signé.
                Les articles seront automatiquement entrés en stock.
            </p>
        </div>

        <form class="space-y-4" @submit.prevent>
            <!-- Bon de livraison -->
            <div class="relative">
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Bon de livraison livré *</label>
                <div class="relative">
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Rechercher un bon de livraison livré..."
                        @focus="isOpen = true"
                        @input="isOpen = true"
                        class="ui-input w-full pl-9"
                    />
                    <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                </div>

                <!-- Dropdown -->
                <div
                    ref="dropdownRef"
                    v-if="isOpen"
                    class="absolute z-20 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-panel"
                >
                    <div
                        v-for="bon in filteredBonLivraisons"
                        :key="bon.id"
                        @click="selectBon(bon)"
                        class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm transition hover:bg-blue-50"
                    >
                        <span class="font-mono font-bold text-istaht-blue">{{ bon.numero }}</span>
                        <TruckIcon class="h-4 w-4 text-istaht-green" />
                    </div>
                    <p v-if="!filteredBonLivraisons.length" class="p-3 text-center text-sm text-slate-400">
                        Aucun bon de livraison livré disponible — tous sont déjà réceptionnés.
                    </p>
                </div>

                <p v-if="form.errors.bon_livraison_id" class="mt-1 text-sm font-semibold text-istaht-red">
                    {{ form.errors.bon_livraison_id }}
                </p>

                <!-- Sélection confirmée -->
                <div
                    v-if="selectedBon"
                    class="mt-2 flex items-center gap-2 rounded-lg border border-green-100 bg-green-50 px-3 py-2 text-sm"
                >
                    <TruckIcon class="h-4 w-4 text-istaht-green" />
                    <span class="text-slate-700">
                        Bon de livraison sélectionné :
                        <strong class="font-mono text-istaht-green">{{ selectedBon.numero }}</strong>
                    </span>
                </div>
            </div>

            <!-- Fichier -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Document du bon signé *</label>
                <label
                    class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500 transition hover:border-istaht-blue hover:bg-blue-50"
                >
                    <DocumentArrowUpIcon class="h-5 w-5" />
                    <span v-if="!form.bon">Cliquez pour joindre le bon de réception (PDF ou image)</span>
                    <span v-else class="font-semibold text-istaht-navy">{{ form.bon.name }}</span>
                    <input type="file" class="hidden" @change="form.bon = $event.target.files[0]" />
                </label>
                <p v-if="form.errors.bon" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.bon }}</p>
            </div>
        </form>

        <!-- ═══ Pied ═══ -->
        <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
            <button type="button" @click="createReceptionModal.close()" class="ui-button ui-button-ghost">
                Annuler
            </button>
            <button
                type="button"
                @click="submit"
                :disabled="form.processing || !form.bon_livraison_id || !form.bon"
                class="ui-button ui-button-primary disabled:opacity-50"
            >
                {{ form.processing ? 'Enregistrement...' : 'Enregistrer la réception' }}
            </button>
        </div>
    </Modal>
</template>
