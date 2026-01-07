<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';

const props = defineProps({
    fiche: Object,        // fiche to edit
    articles: Array,
    types: Object,
    demandeurs: Array,
    repas: Array,
})

const editFicheModal = ref(null)
const etapes = ref([])

const form = useForm({
    type: props.fiche.type,
    nom: props.fiche.nom,
    plat: props.fiche.plat,
    repas_id: props.fiche.repas_id,
    plat_id: props.fiche.plat_id,
    responsable: props.fiche.responsable,
    effectif: props.fiche.effectif,
    demandeur: props.fiche.demandeur_id || null,
    etapes: props.fiche.etapes.map(e => ({
        title: e.title,
        description: e.description,
    })),
    articles: props.fiche.articles.map(a => ({
        article_id: a.article_id,
        designation: a.designation,
        quantite: a.quantite
    }))
});

/* ---------- ETAPES ---------- */
function addEtape() {
    form.etapes.push({ title: '', description: '' })
}
function removeEtape(index) {
    form.etapes.splice(index, 1)
}

/* ---------- ARTICLES ---------- */
const searchText = ref('')          // single search box for articles
const dropdownOpen = ref(false)

function filteredArticles() {
    const selectedIds = form.articles.map(a => a.article_id)
    return props.articles
        .filter(a => !selectedIds.includes(a.id))
        .filter(a => !searchText.value || a.designation.toLowerCase().includes(searchText.value.toLowerCase()))
}

function selectArticle(article) {

    form.articles.push({
        article_id: article.id,
        designation: article.designation,
        quantite: 1,
        unite_mesure: article.unite_mesure,
        prix_unitaire: article.prix_unitaire,
    })
    searchText.value = ''
    dropdownOpen.value = false
}

function closeIdle(index) {
    setTimeout(() => dropdownOpen.value = false, 200)
}

function removeArticle(index) {
    form.articles.splice(index, 1)
}


function submit() {
    form.put(route('fiches-techniques.update', props.fiche.id), {
        onSuccess: () => {
            editFicheModal.value.close()
        }
    })
}

const plats = ref([])
const updatePlats = () => {
    const repas = props.repas.find(r => r.id === form.repas_id)
    plats.value = repas ? repas.plats : []
}

const articleErrors = computed(() =>
    Object.entries(form.errors)
        .filter(([k]) => /^articles\.\d+\.article_id$/.test(k))
        .map(([, m]) => m)
)

onMounted(() => {
    updatePlats()
})
</script>

<template>
    <Modal ref="editFicheModal" size="3xl" class="overflow-y-scroll">
        <div class="mb-4">
            <h2 class="text-lg font-semibold">Modifier la fiche technique</h2>
        </div>
        
         <form @submit.prevent="submit" class="space-y-6">
            <!-- ===== FICHE DETAILS ===== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select v-model="form.type"
                        class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                    <p v-if="form.errors.type" class="text-sm text-red-600 mt-1">{{ form.errors.type }}</p>
                </div>

                <!-- Demandeur (manager only) -->
                <div v-if="$page.props.auth.role === 'manager'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Demandeur</label>
                    <select v-model="form.demandeur"
                        class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option disabled value="">Sélectionnez un demandeur</option>
                        <option v-for="d in demandeurs" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                    <p v-if="form.errors.demandeur" class="text-sm text-red-600 mt-1">{{ form.errors.demandeur }}</p>
                </div>

                <!-- Repas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ form.type === 'collectivite' ?
                        'Repas'
                        : 'Nom de module' }}</label>
                    <select v-model="form.repas_id" @change="updatePlats"
                        class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option disabled value="">Sélectionnez un repas</option>
                        <option v-for="r in repas" :key="r.id" :value="r.id">{{ r.nom }}</option>
                    </select>
                    <p v-if="form.errors.repas_id" class="text-sm text-red-600 mt-1">{{ form.errors.repas_id }}</p>
                </div>

                <!-- Plat -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plat</label>
                    <select v-model="form.plat_id"
                        class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option disabled value="">Sélectionnez un plat</option>
                        <option v-for="p in plats" :key="p.id" :value="p.id">{{ p.nom }}</option>
                    </select>
                    <p v-if="form.errors.plat_id" class="text-sm text-red-600 mt-1">{{ form.errors.plat_id }}</p>
                </div>

                <!-- Responsable -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ form.type === 'collectivite' ? 'Chef decuisine' : 'Formateur' }}</label>
                    <input v-model="form.responsable" type="text"
                        class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    <p v-if="form.errors.responsable" class="text-sm text-red-600 mt-1">{{ form.errors.responsable }}
                    </p>
                </div>

                <!-- Effectif -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Effectif</label>
                    <input v-model.number="form.effectif" type="number" min="1"
                        class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    <p v-if="form.errors.effectif" class="text-sm text-red-600 mt-1">{{ form.errors.effectif }}</p>
                </div>
            </div>

            <!-- ===== ETAPES ===== -->
            <div class="space-y-4">
                <h3 class="text-md font-semibold">Étapes</h3>
                <div v-for="(etape, idx) in form.etapes" :key="idx" class="border p-3 rounded-lg flex gap-3">
                    <div class="flex flex-col flex-1 gap-2">

                        <input v-model="etape.title" type="text" placeholder="Titre de l’étape"
                            class=" border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <textarea v-model="etape.description" type="text" placeholder="Description de l’étape"
                            class="flex-1 border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        <p v-if="form.errors[`etapes.${idx}.title`]" class="text-sm text-red-600">{{
                            form.errors[`etapes.${idx}.title`] }}</p>
                    </div>
                    <button type="button" @click="removeEtape(idx)" class="text-red-500 hover:text-red-700">
                        <TrashIcon class="w-5 h-5" />
                    </button>
                </div>
                <button type="button" @click="addEtape"
                    class="flex items-center gap-2 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <PlusIcon class="w-4 h-4" />Ajouter une étape
                </button>
            </div>

            <!-- ===== ARTICLES ===== -->
            <div class="space-y-4">
                <h3 class="text-md font-semibold">Articles</h3>

                <!-- error block -->
                <ul v-if="articleErrors.length"
                    class="bg-red-300 text-red-900 border border-red-500 rounded p-2 text-sm">
                    <li v-for="e in articleErrors" :key="e">{{ e }}</li>
                </ul>

                <!-- search -->
                <div class="relative mb-2">
                    <input type="text" v-model="searchText" placeholder="Rechercher un article..."
                        @focus="dropdownOpen = true" @blur="closeIdle(index)"
                        class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />

                    <!-- Dropdown -->
                    <ul v-if="dropdownOpen && filteredArticles(index).length"
                        class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg"
                        style="max-height: 200px; overflow-y: auto;">
                        <li v-for="article in filteredArticles(index)" :key="article.id"
                            @click="selectArticle(article)" class="px-3 py-2 hover:bg-indigo-200 cursor-pointer">
                            {{ article.designation }}
                        </li>
                    </ul>
                </div>
                <!-- table -->
                <table class="w-full border border-gray-200 text-sm rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="p-2 text-left">Article</th>
                            <th class="p-2 text-left">Unité</th>
                            <th class="p-2 text-left">Prix</th>
                            <th class="p-2 text-center w-32">Quantité</th>
                            <th class="p-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, i) in form.articles" :key="i" class="border-t">
                            <td class="p-2">{{ item.designation }}</td>
                            <td class="p-2">{{ item.unite_mesure }}</td>
                            <td class="p-2">{{ item.prix_unitaire }}</td>
                            <td class="p-2 text-center">
                                <input type="number" min="1" v-model.number="item.quantite"
                                    class="w-20 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" />
                                <p v-if="form.errors[`articles.${i}.quantite`]" class="text-sm text-red-600 mt-1">{{
                                    form.errors[`articles.${i}.quantite`] }}</p>
                            </td>
                            <td class="p-2 text-center"><button type="button" @click="removeArticle(i)"
                                    class="text-red-500 hover:text-red-700">✕</button></td>
                        </tr>
                        <tr v-if="!form.articles.length">
                            <td colspan="5" class="text-center text-gray-400 p-3">Aucun article ajouté</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
        <!-- Footer -->
        <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="$emit('close')"
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
