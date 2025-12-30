<script setup>
import { ref, reactive, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Dump from '@/Components/Dump.vue';

const props = defineProps({
    articles: Array,
    types: Object,
    repas: Array,
    demandeurs: Array
})

const createFicheModal = ref(null)
const etapes = ref([]) // list of steps
const dropdownOpen = ref([]) // track dropdown for each etape

const form = useForm({
    type: props.types[0].value,
    nom: '',
    plat: '',
    responsable: '',
    effectif: 1,
    demandeur: '',
    etapes: [], // each step will have { title, articles: [{ article_id, designation, quantite }] }
})

// Search per etape
const search = ref({})

// Add new step
function addEtape() {
    const index = etapes.value.length
    etapes.value.push({
        title: '',
        articles: []
    })
    form.etapes.push({
        title: '',
        articles: []
    })
    search.value[index] = ''
    dropdownOpen.value[index] = false
}

// Remove step
function removeEtape(index) {
    etapes.value.splice(index, 1)
    form.etapes.splice(index, 1)
}

// Filter available articles per etape
function filteredArticles(index) {
    return props.articles
        .filter(a => !form.etapes[index].articles.find(fa => fa.article_id === a.id))
        .filter(a => !search.value[index] || a.designation.toLowerCase().includes(search.value[index].toLowerCase()))
}

// Select article for etape
function selectArticle(index, article) {
    const selected = {
        article_id: article.id,
        designation: article.designation,
        quantite: 1,
        unite_mesure: article.unite_mesure,
        prix_unitaire: article.prix_unitaire,
    }
    form.etapes[index].articles.push(selected)
    search.value[index] = ''
    dropdownOpen.value[index] = false
}

// Remove article from etape
function removeArticle(etapeIndex, articleIndex) {
    form.etapes[etapeIndex].articles.splice(articleIndex, 1)
}

// Submit form
function submit() {
    form.post(route('fiches-techniques.store'), {
        onSuccess: () => {
            form.reset()
            etapes.value = []
            createFicheModal.value.close()
        }
    })

    console.log(form.data());

}

// Close dropdown with small delay
function closeIdle(index) {
    setTimeout(() => dropdownOpen.value[index] = false, 200)
}


const articleErrors = computed(() => {
  return Object.entries(form.errors)
    .filter(([key]) => /^etapes\.\d+\.articles\.\d+\.article_id$/.test(key)) // only etapes.*.articles.*.article_id
    .map(([_, message]) => message) // get just the messages
})

const plats = ref('');
const updatePlats = () => {
    plats.value = props.repas.find(repa => repa.id === form.nom).plats;
}
</script>

<template>
    <Modal ref="createFicheModal" size="3xl" class="overflow-y-scroll">
        <div class="mb-4">
            <h2 class="text-lg font-semibold">Créer une fiche technique</h2>
        </div>

        <div>
            <form @submit.prevent="submit" class="space-y-4">

                <!-- Fiche Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select v-model="form.type"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option v-for="type in types" :key="type.value" :value="type.value">{{ type.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.type" class="text-sm text-red-600 mt-1">{{ form.errors.type }}</p>
                    </div>

                    <!-- Select Demandeur -->
                    <div v-if="$page.props.auth.role === 'manager'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Demandeur</label>
                        <select v-model="form.demandeur"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option disabled value="">Sélectionnez un demandeur</option>
                            <option v-for="demandeur in demandeurs" :key="demandeur.id" :value="demandeur.id">{{ demandeur.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.demandeur" class="text-sm text-red-600 mt-1">{{ form.errors.demandeur }}</p>
                    </div>

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ form.type === 'collectivite' ? 'Repas' : 'Nom de module' }}
                        </label>
                        <select v-model="form.nom" @change="updatePlats"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option disabled value="">Sélectionnez un repas</option>
                            <option v-for="repa in repas" :key="repa.id" :value="repa.id">{{ repa.nom }}
                            </option>
                        </select>
                        <p v-if="form.errors.nom" class="text-sm text-red-600 mt-1">{{ form.errors.nom }}</p>
                    </div>

                    <!-- Plat -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plat</label>
                         <select v-model="form.plat"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option disabled value="">Sélectionnez un plat</option>
                            <option v-for="plat in plats" :key="plat.id" :value="plat.id">{{ plat.nom }}
                            </option>
                        </select>
                        <p v-if="form.errors.plat" class="text-sm text-red-600 mt-1">{{ form.errors.plat }}</p>
                    </div>

                    <!-- Responsable -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ form.type === 'collectivite' ? 'Chef de cuisine' : 'Formateur' }}
                        </label>
                        <input v-model="form.responsable" type="text"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        <p v-if="form.errors.responsable" class="text-sm text-red-600 mt-1">{{ form.errors.responsable
                            }}</p>
                    </div>

                    <!-- Effectif -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Effectif</label>
                        <input v-model.number="form.effectif" type="number" min="1"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        <p v-if="form.errors.effectif" class="text-sm text-red-600 mt-1">{{ form.errors.effectif }}</p>
                    </div>
                </div>

                <!-- Etapes -->
                <div class="space-y-6">
                    <h3 class="text-md font-semibold">Etapes</h3>

                    <ul class="bg-red-300 text-red-900 border-red-500 border-1 rounded p-2 text-sm mt-2" v-if="articleErrors.length">
                        <li v-for="error in articleErrors" :key="error">
                            {{ error }}
                        </li>
                    </ul>

                    <div v-for="(etape, index) in etapes" :key="index" class="border p-4 rounded-lg relative">
                        <!-- Step Header -->
                        <div class="flex justify-between items-center mb-3">
                            <label class="font-medium">Titre de l’étape</label>
                            <button type="button" @click="removeEtape(index)" class="text-red-500 hover:text-red-700">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                        <input v-model="form.etapes[index].title" type="text"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500 mb-1" />
                        <p v-if="form.errors[`etapes.${index}.title`]" class="text-sm text-red-600 mb-3">
                            {{ form.errors[`etapes.${index}.title`] }}
                        </p>

                        <!-- Articles per step -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Articles</label>
                            <div class="relative mb-2">
                                <input type="text" v-model="search[index]" placeholder="Rechercher un article..."
                                    @focus="dropdownOpen[index] = true" @blur="closeIdle(index)"
                                    class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500" />

                                <!-- Dropdown -->
                                <ul v-if="dropdownOpen[index] && filteredArticles(index).length"
                                    class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 shadow-lg"
                                    style="max-height: 200px; overflow-y: auto;">
                                    <li v-for="article in filteredArticles(index)" :key="article.id"
                                        @click="selectArticle(index, article)"
                                        class="px-3 py-2 hover:bg-indigo-200 cursor-pointer">
                                        {{ article.designation }}
                                    </li>
                                </ul>
                            </div>

                            <!-- Selected Articles Table -->
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
                                    <tr v-for="(item, aIndex) in form.etapes[index].articles" :key="aIndex" class="border-t">
                                        <td class="p-2">{{ item.designation }}</td>
                                        <td class="p-2">{{ item.unite_mesure }}</td>
                                        <td class="p-2">{{ item.prix_unitaire }}</td>
                                        <td class="p-2 text-center">
                                            <input
                                            type="number"
                                            min="1"
                                            v-model.number="item.quantite"
                                            class="w-20 text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                            />
                                            <p v-if="form.errors[`etapes.${index}.articles.${aIndex}.quantite`]" class="text-sm text-red-600 mt-1">
                                            {{ form.errors[`etapes.${index}.articles.${aIndex}.quantite`] }}
                                            </p>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removeArticle(index, aIndex)" class="text-red-500 hover:text-red-700">✕</button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.etapes[index].articles.length === 0">
                                        <td colspan="5" class="text-center text-gray-400 p-3">Aucun article ajouté</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <button type="button" @click="addEtape"
                        class="flex items-center gap-2 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <PlusIcon class="w-4 h-4" />
                        Ajouter une étape
                    </button>
                </div>
            </form>
        </div>

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
