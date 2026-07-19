<script setup>
import { ref, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import { MagnifyingGlassIcon, DocumentArrowDownIcon, CubeIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    articles: Array,
})

const search = ref('')
const createCardexModal = ref(null)
const dropdownOpen = ref(false)

const currentYear = new Date().getFullYear()
const years = Array.from({ length: 5 }, (_, i) => currentYear - i)

const form = useForm({
    article_id: null,
    year: currentYear,
})

const filteredArticles = computed(() =>
    props.articles.filter(a => !search.value || a.designation.toLowerCase().includes(search.value.toLowerCase()))
)

const selectedArticle = computed(() => props.articles.find(a => a.id === form.article_id) || null)

function selectArticle(article) {
    form.article_id = article.id
    search.value = article.designation
    dropdownOpen.value = false
}

function closeIdle() {
    setTimeout(() => (dropdownOpen.value = false), 300)
}
</script>

<template>
    <Modal ref="createCardexModal">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h2 class="flex items-center gap-2 text-lg font-bold text-istaht-navy">
                <DocumentArrowDownIcon class="h-5 w-5" />
                Exporter le Cardex
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Sélectionnez un article et une année pour générer sa fiche de stock annuelle (entrées / sorties / stock).
            </p>
        </div>

        <div class="space-y-4">
            <!-- Recherche article -->
            <div class="relative">
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Article *</label>
                <div class="relative">
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Rechercher un article…"
                        @focus="dropdownOpen = true"
                        @blur="closeIdle"
                        class="ui-input w-full pl-9"
                    />
                    <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                </div>

                <ul
                    v-if="dropdownOpen && filteredArticles.length"
                    class="absolute z-20 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-panel"
                >
                    <li
                        v-for="article in filteredArticles"
                        :key="article.id"
                        @click="selectArticle(article)"
                        class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm transition hover:bg-blue-50"
                    >
                        <span class="font-semibold text-slate-700">{{ article.designation }}</span>
                        <CubeIcon class="h-4 w-4 text-istaht-blue" />
                    </li>
                </ul>

                <div v-if="selectedArticle" class="mt-2 flex items-center gap-2 rounded-lg border border-green-100 bg-green-50 px-3 py-2 text-sm">
                    <CheckCircleIcon class="h-4 w-4 text-istaht-green" />
                    <span class="text-slate-700">Article : <strong class="text-istaht-navy">{{ selectedArticle.designation }}</strong></span>
                </div>
            </div>

            <!-- Année -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Année *</label>
                <select v-model="form.year" class="ui-input w-full">
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
            <button type="button" @click="createCardexModal.close()" class="ui-button ui-button-ghost">Annuler</button>
            <a
                v-if="form.article_id"
                :href="route('cardex.export', { article: form.article_id, year: form.year })"
                target="_blank"
                class="ui-button ui-button-primary"
            >
                <DocumentArrowDownIcon class="mr-1.5 h-4 w-4" />
                Exporter le PDF
            </a>
            <button v-else type="button" disabled class="ui-button ui-button-primary opacity-50">
                Sélectionnez un article
            </button>
        </div>
    </Modal>
</template>
