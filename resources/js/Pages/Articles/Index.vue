<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    PlusIcon, 
    MagnifyingGlassIcon,
    PencilIcon,
    EyeIcon,
    TrashIcon,
    FunnelIcon,
    ArrowPathIcon,
    XMarkIcon,
    ListBulletIcon,
    PhotoIcon,
    DocumentDuplicateIcon
} from '@heroicons/vue/24/outline';

defineOptions({
    layout: AuthenticatedLayout
});

const props = defineProps({
    articles: Object,
    categoriesPrincipales: Array,
    naturesPrestation: Array,
    categories: Array,
});

// États pour la recherche et filtres
const search = ref('');
const selectedCategoriePrincipale = ref('');
const selectedNature = ref('');
const selectedCategorie = ref('');
const showFilters = ref(false);

// États pour les modales et formulaires
const showArticleForm = ref(false);
const showCategorieForm = ref(false);
const showCategoriePrincipaleForm = ref(false);
const showNatureForm = ref(false);
const showCategoriesList = ref(false);
const editingArticle = ref(null);
const editingCategorie = ref(null);
const editingCategoriePrincipale = ref(null);
const editingNature = ref(null);
const formMode = ref('create');

// Référence pour l'input file
const fileInput = ref(null);

// Gestion des images
const imagePreviews = ref([]);

// Formulaires avec useForm pour Inertia
const articleForm = useForm({
    reference: '',
    designation: '',
    description: '',
    categorie_id: '',
    categorie_principale_id: '',
    nature_prestation_id: '',
    unite_mesure: '',
    taux_tva: 0,
    seuil_minimal: 0,
    seuil_maximal: 0,
    est_actif: true,
    images: [],
});

const categorieForm = useForm({
    nom: '',
    code: '',
    description: '',
    categorie_principale_id: '',
    nature_prestation_id: '',
    est_actif: true,
});

const categoriePrincipaleForm = useForm({
    nom: '',
    code: '',
    description: '',
    est_actif: true,
});

const natureForm = useForm({
    nom: '',
    code: '',
    description: '',
    categorie_principale_id: '',
    est_actif: true,
});

// Watch pour mettre à jour les catégories quand la catégorie principale change
watch(selectedCategoriePrincipale, (newValue) => {
    selectedCategorie.value = '';
});

watch(() => articleForm.categorie_principale_id, (newValue) => {
    articleForm.categorie_id = '';
});

// Articles filtrés
const filteredArticles = computed(() => {
    let filtered = props.articles.data;

    if (search.value) {
        const query = search.value.toLowerCase();
        filtered = filtered.filter(article => 
            article.reference.toLowerCase().includes(query) ||
            article.designation.toLowerCase().includes(query) ||
            article.description?.toLowerCase().includes(query)
        );
    }

    if (selectedCategoriePrincipale.value) {
        filtered = filtered.filter(article => 
            article.categorie_principale_id == selectedCategoriePrincipale.value
        );
    }

    if (selectedCategorie.value) {
        filtered = filtered.filter(article => 
            article.categorie_id == selectedCategorie.value
        );
    }

    if (selectedNature.value) {
        filtered = filtered.filter(article => 
            article.nature_prestation_id == selectedNature.value
        );
    }

    return filtered;
});

// Catégories filtrées par catégorie principale
const categoriesFiltrees = computed(() => {
    if (!selectedCategoriePrincipale.value) {
        return props.categories;
    }
    return props.categories.filter(cat => 
        cat.categorie_principale_id == selectedCategoriePrincipale.value
    );
});

// Natures filtrées par catégorie principale
const naturesFiltrees = computed(() => {
    if (!selectedCategoriePrincipale.value) {
        return props.naturesPrestation;
    }
    return props.naturesPrestation.filter(nature => 
        nature.categorie_principale_id == selectedCategoriePrincipale.value
    );
});

// Réinitialiser les filtres
const resetFilters = () => {
    search.value = '';
    selectedCategoriePrincipale.value = '';
    selectedCategorie.value = '';
    selectedNature.value = '';
};

// Gestion des images
const onImagesSelected = (event) => {
    const files = Array.from(event.target.files || []);
    
    // Vérifier le nombre maximum d'images
    if (files.length + articleForm.images.length > 5) {
        alert('Maximum 5 images autorisées');
        return;
    }

    // Valider et ajouter chaque fichier
    files.forEach(file => {
        if (file.size > 4 * 1024 * 1024) {
            alert(`Le fichier ${file.name} dépasse la taille maximale de 4MB`);
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            alert(`Le fichier ${file.name} n'est pas une image valide`);
            return;
        }

        articleForm.images.push(file);
        const url = URL.createObjectURL(file);
        imagePreviews.value.push(url);
    });

    // Réinitialiser l'input file
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const removeImage = (index) => {
    // Libérer l'URL de l'aperçu
    URL.revokeObjectURL(imagePreviews.value[index]);
    imagePreviews.value.splice(index, 1);
    articleForm.images.splice(index, 1);
};

// Ouvrir le formulaire d'ajout d'article
const openArticleForm = (article = null) => {
    if (article) {
        formMode.value = 'edit';
        editingArticle.value = article;
        Object.keys(articleForm).forEach(key => {
            if (key in article && key !== 'images') {
                articleForm[key] = article[key];
            }
        });
        // Réinitialiser les images pour l'édition
        articleForm.images = [];
        imagePreviews.value = [];
    } else {
        formMode.value = 'create';
        editingArticle.value = null;
        articleForm.reset();
        articleForm.unite_mesure = '';
        articleForm.taux_tva = 0;
        articleForm.seuil_minimal = 0;
        articleForm.seuil_maximal = 0;
        articleForm.est_actif = true;
        articleForm.images = [];
        imagePreviews.value = [];
    }
    showArticleForm.value = true;
};

// Cloner un article
const cloneArticle = (article) => {
    formMode.value = 'create';
    editingArticle.value = null;
    
    // Réinitialiser le formulaire
    articleForm.reset();
    articleForm.images = [];
    imagePreviews.value = [];
    
    // Copier les données de l'article source (sauf la référence)
    Object.keys(articleForm).forEach(key => {
        if (key in article && key !== 'reference' && key !== 'id' && key !== 'images') {
            articleForm[key] = article[key];
        }
    });
    
    // Référence vide pour le clonage
    articleForm.reference = '';
    
    showArticleForm.value = true;
};

// Ouvrir le formulaire d'ajout de catégorie
const openCategorieForm = (categorie = null) => {
    if (categorie) {
        formMode.value = 'edit';
        editingCategorie.value = categorie;
        Object.keys(categorieForm).forEach(key => {
            if (key in categorie) {
                categorieForm[key] = categorie[key];
            }
        });
    } else {
        formMode.value = 'create';
        editingCategorie.value = null;
        categorieForm.reset();
        categorieForm.categorie_principale_id = selectedCategoriePrincipale.value || '';
        categorieForm.est_actif = true;
    }
    showCategorieForm.value = true;
};

// Ouvrir le formulaire d'ajout de catégorie principale
const openCategoriePrincipaleForm = (categoriePrincipale = null) => {
    if (categoriePrincipale) {
        formMode.value = 'edit';
        editingCategoriePrincipale.value = categoriePrincipale;
        Object.keys(categoriePrincipaleForm).forEach(key => {
            if (key in categoriePrincipale) {
                categoriePrincipaleForm[key] = categoriePrincipale[key];
            }
        });
    } else {
        formMode.value = 'create';
        editingCategoriePrincipale.value = null;
        categoriePrincipaleForm.reset();
        categoriePrincipaleForm.est_actif = true;
    }
    showCategoriePrincipaleForm.value = true;
};

// Ouvrir le formulaire d'ajout de nature
const openNatureForm = (nature = null) => {
    if (nature) {
        formMode.value = 'edit';
        editingNature.value = nature;
        Object.keys(natureForm).forEach(key => {
            if (key in nature) {
                natureForm[key] = nature[key];
            }
        });
    } else {
        formMode.value = 'create';
        editingNature.value = null;
        natureForm.reset();
        natureForm.categorie_principale_id = selectedCategoriePrincipale.value || '';
        natureForm.est_actif = true;
    }
    showNatureForm.value = true;
};

// Fermer tous les formulaires
const closeAllForms = () => {
    showArticleForm.value = false;
    showCategorieForm.value = false;
    showCategoriePrincipaleForm.value = false;
    showNatureForm.value = false;
    showCategoriesList.value = false;
    editingArticle.value = null;
    editingCategorie.value = null;
    editingCategoriePrincipale.value = null;
    editingNature.value = null;
    
    // Nettoyer les URLs des aperçus d'images
    imagePreviews.value.forEach(url => URL.revokeObjectURL(url));
    imagePreviews.value = [];
    
    articleForm.reset();
    categorieForm.reset();
    categoriePrincipaleForm.reset();
    natureForm.reset();
};

// Soumettre le formulaire article avec FormData pour les images
const submitArticleForm = () => {
    // Créer FormData pour gérer les fichiers
    const formData = new FormData();
    
    // Ajouter tous les champs du formulaire
    Object.keys(articleForm).forEach(key => {
        if (key !== 'images' && key !== 'processing' && key !== 'errors' && key !== 'hasErrors') {
            formData.append(key, articleForm[key]);
        }
    });
    
    // Ajouter les images
    articleForm.images.forEach((image, index) => {
        formData.append('images[]', image);
    });

    if (formMode.value === 'create') {
        articleForm.post(route('articles.store'), {
            data: formData,
            forceFormData: true,
            onSuccess: () => {
                closeAllForms();
            },
        });
    } else {
        articleForm.post(route('articles.update', editingArticle.value.id), {
            data: formData,
            forceFormData: true,
            onSuccess: () => {
                closeAllForms();
            },
        });
    }
};

// Soumettre le formulaire catégorie
const submitCategorieForm = () => {
    if (formMode.value === 'create') {
        categorieForm.post(route('categories.store'), {
            onSuccess: () => closeAllForms(),
        });
    } else {
        categorieForm.put(route('categories.update', editingCategorie.value.id), {
            onSuccess: () => closeAllForms(),
        });
    }
};

// Soumettre le formulaire catégorie principale
const submitCategoriePrincipaleForm = () => {
    if (formMode.value === 'create') {
        categoriePrincipaleForm.post(route('categorie-principales.store'), {
            onSuccess: () => closeAllForms(),
        });
    } else {
        categoriePrincipaleForm.put(route('categorie-principales.update', editingCategoriePrincipale.value.id), {
            onSuccess: () => closeAllForms(),
        });
    }
};

// Soumettre le formulaire nature
const submitNatureForm = () => {
    if (formMode.value === 'create') {
        natureForm.post(route('nature-prestations.store'), {
            onSuccess: () => closeAllForms(),
        });
    } else {
        natureForm.put(route('nature-prestations.update', editingNature.value.id), {
            onSuccess: () => closeAllForms(),
        });
    }
};

// Supprimer un article
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const deleteType = ref(''); // 'article', 'categorie', etc.

// Méthodes pour gérer la suppression
const openDeleteConfirm = (item, type) => {
    itemToDelete.value = item;
    deleteType.value = type;
    showDeleteConfirm.value = true;
};

const confirmDelete = () => {
    if (itemToDelete.value && deleteType.value) {
        switch (deleteType.value) {
            case 'article':
                router.delete(route('articles.destroy', itemToDelete.value.id));
                break;
            case 'categorie':
                router.delete(route('categories.destroy', itemToDelete.value.id));
                break;
            case 'categorie_principale':
                router.delete(route('categorie-principales.destroy', itemToDelete.value.id));
                break;
            case 'nature':
                router.delete(route('nature-prestations.destroy', itemToDelete.value.id));
                break;
        }
    }
    showDeleteConfirm.value = false;
    itemToDelete.value = null;
    deleteType.value = '';
};

// Mettez à jour les méthodes de suppression existantes :
const deleteArticle = (article) => {
    openDeleteConfirm(article, 'article');
};

const deleteCategorie = (categorie) => {
    openDeleteConfirm(categorie, 'categorie');
};

const deleteCategoriePrincipale = (categoriePrincipale) => {
    openDeleteConfirm(categoriePrincipale, 'categorie_principale');
};

const deleteNature = (nature) => {
    openDeleteConfirm(nature, 'nature');
};

// Basculer l'affichage de la liste des catégories
const toggleCategoriesList = () => {
    showCategoriesList.value = !showCategoriesList.value;
    if (showCategoriesList.value) {
        showArticleForm.value = false;
        showCategorieForm.value = false;
        showCategoriePrincipaleForm.value = false;
        showNatureForm.value = false;
    }
};
</script>

<template>
    <Head title="Référentiel Articles" />

    <div class="space-y-6">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-4 px-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Référentiel Articles</h1>
                <p class="text-gray-600">Gestion du catalogue des articles en stock</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    @click="toggleCategoriesList"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                    :class="{ 'bg-blue-50 border-blue-300': showCategoriesList }"
                >
                    <ListBulletIcon class="h-5 w-5 mr-2" />
                    {{ showCategoriesList ? 'Masquer catégories' : 'Lister catégories' }}
                </button>
               
                <!-- <button
                    @click="openCategoriePrincipaleForm()"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                >
                    <PlusIcon class="h-5 w-5 mr-2" />
                    Catégorie Principale
                </button>
                <button
                    @click="openNatureForm()"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
                >
                    <PlusIcon class="h-5 w-5 mr-2" />
                    Nature
                </button> -->
                <button
                    @click="openCategorieForm()"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                >
                    <PlusIcon class="h-5 w-5 mr-2" />
                    Catégorie
                </button>
                <button
                    @click="openArticleForm()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="h-5 w-5 mr-2" />
                    Article
                </button>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total articles</p>
                        <p class="text-2xl font-bold">{{ articles.total }}</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <div class="w-6 h-6 bg-blue-600 rounded-full"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">En rupture</p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ articles.data.filter(a => a.stock_actuel <= a.seuil_minimal).length }}
                        </p>
                    </div>
                    <div class="p-2 bg-red-100 rounded-lg">
                        <div class="w-6 h-6 bg-red-600 rounded-full"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Catégories</p>
                        <p class="text-2xl font-bold text-green-600">{{ categories.length }}</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-lg">
                        <div class="w-6 h-6 bg-green-600 rounded-full"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Natures</p>
                        <p class="text-2xl font-bold text-purple-600">{{ naturesPrestation.length }}</p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <div class="w-6 h-6 bg-purple-600 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Barre de recherche -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher par référence, désignation..."
                        class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <!-- Bouton filtres -->
                <button
                    @click="showFilters = !showFilters"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <FunnelIcon class="h-5 w-5 mr-2" />
                    Filtres
                    <span v-if="selectedCategoriePrincipale || selectedCategorie || selectedNature" 
                          class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                        {{ (selectedCategoriePrincipale ? 1 : 0) + (selectedCategorie ? 1 : 0) + (selectedNature ? 1 : 0) }}
                    </span>
                </button>

                <!-- Réinitialiser -->
                <button
                    @click="resetFilters"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <ArrowPathIcon class="h-5 w-5 mr-2" />
                    Réinitialiser
                </button>
            </div>

            <!-- Filtres avancés -->
            <div v-if="showFilters" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie principale</label>
                    <select
                        v-model="selectedCategoriePrincipale"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Toutes</option>
                        <option v-for="categorie in categoriesPrincipales" :key="categorie.id" :value="categorie.id">
                            {{ categorie.nom }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                    <select
                        v-model="selectedCategorie"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Toutes</option>
                        <option v-for="categorie in categoriesFiltrees" :key="categorie.id" :value="categorie.id">
                            {{ categorie.nom }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nature de prestation</label>
                    <select
                        v-model="selectedNature"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Toutes</option>
                        <option v-for="nature in naturesFiltrees" :key="nature.id" :value="nature.id">
                            {{ nature.nom }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Modal Article -->
        <div v-if="showArticleForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ formMode === 'create' ? 'Nouvel article' : 'Modifier l\'article' }}
                    </h3>
                    <button @click="closeAllForms" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submitArticleForm" class="p-6 space-y-6" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Référence *</label>
                                <input v-model="articleForm.reference" type="text" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Désignation *</label>
                                <input v-model="articleForm.designation" type="text" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="articleForm.description" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                            </div>

                            <!-- Upload d'images -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Images (JPG/PNG/WEBP, max 4 Mo)</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    @change="onImagesSelected"
                                    ref="fileInput"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                />

                                <!-- Preview -->
                                <div v-if="imagePreviews.length" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div v-for="(src, idx) in imagePreviews" :key="idx" class="relative">
                                        <img :src="src" class="w-full h-28 object-cover rounded border" />
                                        <button type="button"
                                                @click="removeImage(idx)"
                                                class="absolute top-1 right-1 bg-white/80 hover:bg-white border text-xs px-2 rounded">
                                            Suppr.
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Colonne droite -->
                        <div class="space-y-4">
                            <!-- <div>
                                <label class="block text-sm font-medium text-gray-700">Catégorie principale *</label>
                                <select v-model="articleForm.categorie_principale_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Sélectionnez...</option>
                                    <option v-for="catPrinc in categoriesPrincipales" :key="catPrinc.id" :value="catPrinc.id">
                                        {{ catPrinc.nom }}
                                    </option>
                                </select>
                            </div> -->
                            <!-- <div>
                                <label class="block text-sm font-medium text-gray-700">Nature de prestation *</label>
                                <select v-model="articleForm.nature_prestation_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Sélectionnez...</option>
                                    <option v-for="nature in naturesFiltrees" :key="nature.id" :value="nature.id">
                                        {{ nature.nom }}
                                    </option>
                                </select>
                            </div> -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Catégorie *</label>
                                <select v-model="articleForm.categorie_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Sélectionnez...</option>
                                    <option v-for="cat in categoriesFiltrees" :key="cat.id" :value="cat.id">
                                        {{ cat.nom }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Unité *</label>
                                <select v-model="articleForm.unite_mesure" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Sélectionnez...</option>
                                    <option value="kg">kg</option>
                                    <option value="L">L</option>
                                    <option value="pièce">pièce</option>
                                    <option value="sachet">sachet</option>
                                    <option value="sac">sac</option>
                                    <option value="boite">boite</option>
                                    <option value="bidon">bidon</option>
                                    <option value="paquet">paquet</option>
                                    <option value="flacon">flacon</option>
                                    <option value="pot">pot</option>
                                    <option value="bouteille">bouteille</option>
                                </select>
                            </div>
                            
                            <!-- <div>
                                <label class="block text-sm font-medium text-gray-700">Seuil minimal</label>
                                <input v-model="articleForm.seuil_minimal" type="number" min="0"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div> -->
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Seuil maximal</label>
                                <input v-model="articleForm.seuil_maximal" type="number" min="0"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            </div>

                            <div class="flex items-center">
                                <input v-model="articleForm.est_actif" type="checkbox" id="est_actif"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <label for="est_actif" class="ml-2 block text-sm text-gray-700">Article actif</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <button type="button" @click="closeAllForms"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            :disabled="articleForm.processing">
                            {{ formMode === 'create' ? 'Créer' : 'Modifier' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Catégorie -->
        <div v-if="showCategorieForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ formMode === 'create' ? 'Nouvelle catégorie' : 'Modifier la catégorie' }}
                    </h3>
                    <button @click="closeAllForms" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submitCategorieForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input v-model="categorieForm.nom" type="text" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code *</label>
                        <input v-model="categorieForm.code" type="text" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="categorieForm.description" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catégorie principale *</label>
                        <select v-model="categorieForm.categorie_principale_id" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Sélectionnez...</option>
                            <option v-for="catPrinc in categoriesPrincipales" :key="catPrinc.id" :value="catPrinc.id">
                                {{ catPrinc.nom }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nature de prestation *</label>
                        <select v-model="categorieForm.nature_prestation_id" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Sélectionnez...</option>
                            <option v-for="nature in naturesPrestation" :key="nature.id" :value="nature.id">
                                {{ nature.nom }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input v-model="categorieForm.est_actif" type="checkbox" id="categorie_est_actif"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="categorie_est_actif" class="ml-2 block text-sm text-gray-700">Catégorie active</label>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <button type="button" @click="closeAllForms"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                            :disabled="categorieForm.processing">
                            {{ formMode === 'create' ? 'Créer' : 'Modifier' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Catégorie Principale -->
        <div v-if="showCategoriePrincipaleForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ formMode === 'create' ? 'Nouvelle catégorie principale' : 'Modifier la catégorie principale' }}
                    </h3>
                    <button @click="closeAllForms" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submitCategoriePrincipaleForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input v-model="categoriePrincipaleForm.nom" type="text" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code *</label>
                        <input v-model="categoriePrincipaleForm.code" type="text" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="categoriePrincipaleForm.description" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input v-model="categoriePrincipaleForm.est_actif" type="checkbox" id="catPrinc_est_actif"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="catPrinc_est_actif" class="ml-2 block text-sm text-gray-700">Catégorie principale active</label>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <button type="button" @click="closeAllForms"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50"
                            :disabled="categoriePrincipaleForm.processing">
                            {{ formMode === 'create' ? 'Créer' : 'Modifier' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Nature -->
        <div v-if="showNatureForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">
                        {{ formMode === 'create' ? 'Nouvelle nature' : 'Modifier la nature' }}
                    </h3>
                    <button @click="closeAllForms" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submitNatureForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input v-model="natureForm.nom" type="text" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code *</label>
                        <input v-model="natureForm.code" type="text" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="natureForm.description" rows="3"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catégorie principale *</label>
                        <select v-model="natureForm.categorie_principale_id" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                            <option value="">Sélectionnez...</option>
                            <option v-for="catPrinc in categoriesPrincipales" :key="catPrinc.id" :value="catPrinc.id">
                                {{ catPrinc.nom }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input v-model="natureForm.est_actif" type="checkbox" id="nature_est_actif"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="nature_est_actif" class="ml-2 block text-sm text-gray-700">Nature active</label>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <button type="button" @click="closeAllForms"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 disabled:opacity-50"
                            :disabled="natureForm.processing">
                            {{ formMode === 'create' ? 'Créer' : 'Modifier' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des catégories -->
        <div v-if="showCategoriesList" class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Liste des Catégories </h2>
                <p class="text-sm text-gray-600">Gestion hiérarchique des catégories </p>
            </div>

            <!-- Catégories Principales
            <div class="p-6 border-b">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-md font-medium text-gray-900">Catégories Principales</h3>
                    <button
                        @click="openCategoriePrincipaleForm()"
                        class="inline-flex items-center px-3 py-1 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm"
                    >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Ajouter
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="catPrinc in categoriesPrincipales" :key="catPrinc.id" 
                         class="border rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ catPrinc.nom }}</h4>
                                <p class="text-sm text-gray-500">{{ catPrinc.code }}</p>
                                <p v-if="catPrinc.description" class="text-sm text-gray-600 mt-1">{{ catPrinc.description }}</p>
                            </div>
                            <div class="flex space-x-1">
                                <button
                                    @click="openCategoriePrincipaleForm(catPrinc)"
                                    class="text-blue-600 hover:text-blue-900 p-1"
                                    title="Modifier"
                                >
                                    <PencilIcon class="h-4 w-4" />
                                </button>
                                <button
                                    @click="openDeleteConfirm(catPrinc, 'categorie_principale')"
                                    class="text-red-600 hover:text-red-900 p-1"
                                    title="Supprimer"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center">
                            <span :class="[
                                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                catPrinc.est_actif 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            ]">
                                {{ catPrinc.est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            -->

            <!-- Catégories -->
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-md font-medium text-gray-900">Catégories</h3>
                    <button
                        @click="openCategorieForm()"
                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                    >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Ajouter
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="categorie in categories" :key="categorie.id" 
                         class="border rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ categorie.nom }}</h4>
                                <p class="text-sm text-gray-500">{{ categorie.code }}</p>
                                <p class="text-xs text-gray-400">
                                    Catégorie: {{ categorie.categorie_principale?.nom }} | 
                                    Nature: {{ categorie.nature_prestation?.nom }}
                                </p>
                                <p v-if="categorie.description" class="text-sm text-gray-600 mt-1">{{ categorie.description }}</p>
                            </div>
                            <div class="flex space-x-1">
                                <button
                                    @click="openCategorieForm(categorie)"
                                    class="text-blue-600 hover:text-blue-900 p-1"
                                    title="Modifier"
                                >
                                    <PencilIcon class="h-4 w-4" />
                                </button>
                                <button
                                    @click="openDeleteConfirm(categorie, 'categorie')"
                                    class="text-red-600 hover:text-red-900 p-1"
                                    title="Supprimer"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center">
                            <span :class="[
                                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                categorie.est_actif 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            ]">
                                {{ categorie.est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des articles avec bouton cloner -->
        <div v-if="!showCategoriesList" class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Référence
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Désignation
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Catégorie
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prix Unitaire
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unité
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Seuil maximale
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="article in filteredArticles" :key="article.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ article.reference }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ article.designation }}</div>
                                <div v-if="article.description" class="text-sm text-gray-500 truncate max-w-xs">
                                    {{ article.description }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ article.categorie.nom }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ article.last_entry_stock?.prix_unitaire || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ article.unite_mesure }}</div>
                                <div class="text-xs text-gray-500">TVA: {{ article.taux_tva }}%</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                   {{ article.seuil_maximal }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="article.est_actif" 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Actif
                                </span>
                                <span v-else 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button
                                        @click="cloneArticle(article)"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded"
                                        title="Cloner l'article"
                                    >
                                        <DocumentDuplicateIcon class="h-4 w-4" />
                                    </button>
                                    <Link
                                        :href="route('articles.show', article.id)"
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded"
                                        title="Voir détails"
                                    >
                                        <EyeIcon class="h-4 w-4" />
                                    </Link>
                                    <button
                                        @click="openArticleForm(article)"
                                        class="text-green-600 hover:text-green-900 p-1 rounded"
                                        title="Modifier"
                                    >
                                        <PencilIcon class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="openDeleteConfirm(article, 'article')"
                                        class="text-red-600 hover:text-red-900 p-1 rounded"
                                        title="Supprimer"
                                    >
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Message vide -->
            <div v-if="filteredArticles.length === 0" class="text-center py-12">
                <div class="text-gray-500">
                    <MagnifyingGlassIcon class="mx-auto h-12 w-12" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun article trouvé</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ search || selectedCategoriePrincipale || selectedCategorie || selectedNature ? 
                           'Essayez de modifier vos critères de recherche.' : 
                           'Commencez par créer votre premier article.' }}
                    </p>
                    <div v-if="!search && !selectedCategoriePrincipale && !selectedCategorie && !selectedNature" class="mt-6">
                        <button 
                            @click="openArticleForm()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            <PlusIcon class="h-4 w-4 mr-2" />
                            Nouvel article
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal de Confirmation de Suppression Global -->
            <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="flex items-center justify-between p-6 border-b">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <TrashIcon class="h-6 w-6 text-red-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Confirmation de suppression</h3>
                                <p class="text-sm text-gray-500">Action irréversible</p>
                            </div>
                        </div>
                        <button @click="showDeleteConfirm = false" class="text-gray-400 hover:text-gray-600">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-center justify-center mb-4">
                            <div class="p-3 bg-red-50 rounded-full">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <TrashIcon class="h-6 w-6 text-red-600" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <h4 class="text-lg font-medium text-gray-900 mb-2">
                                Êtes-vous sûr de vouloir supprimer ?
                            </h4>
                            <p class="text-gray-600 mb-2">
                                <strong>{{ 
                                    deleteType === 'article' ? itemToDelete?.designation :
                                    deleteType === 'categorie' ? itemToDelete?.nom :
                                    deleteType === 'categorie_principale' ? itemToDelete?.nom :
                                    deleteType === 'nature' ? itemToDelete?.nom : ''
                                }}</strong>
                            </p>
                            <p class="text-gray-500 text-sm mb-4">
                                {{ 
                                    deleteType === 'article' ? `Référence: ${itemToDelete?.reference}` :
                                    deleteType === 'categorie' ? `Code: ${itemToDelete?.code}` :
                                    deleteType === 'categorie_principale' ? `Code: ${itemToDelete?.code}` :
                                    deleteType === 'nature' ? `Code: ${itemToDelete?.code}` : ''
                                }}
                            </p>
                            <p class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                                ⚠️ Cette action est irréversible. Toutes les données associées seront perdues.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50 rounded-b-lg">
                        <button 
                            @click="showDeleteConfirm = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                        >
                            Annuler
                        </button>
                        <button 
                            @click="confirmDelete"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2"
                        >
                            <TrashIcon class="h-4 w-4" />
                            <span>Supprimer définitivement</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="articles?.data?.length > 0" class="bg-white px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Affichage de {{ articles.from }} à {{ articles.to }} sur {{ articles.total }} résultats
                    </div>
                    <div class="flex space-x-2">
                        <template v-for="link in articles.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 rounded-lg text-sm font-medium',
                                    link.active 
                                        ? 'bg-blue-600 text-white' 
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="[
                                    'px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>