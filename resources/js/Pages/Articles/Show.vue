<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    DocumentArrowDownIcon,
    PhotoIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import PdfService from '@/Services/PdfService';

defineOptions({
    layout: AuthenticatedLayout
});

const props = defineProps({
    article: Object,
});

// États pour la confirmation de suppression
const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const isGeneratingPDF = ref(false);

const openDeleteConfirm = (article) => {
    itemToDelete.value = article;
    showDeleteConfirm.value = true;
};

const confirmDelete = () => {
    if (itemToDelete.value) {
        router.delete(route('articles.destroy', itemToDelete.value.id));
    }
    showDeleteConfirm.value = false;
    itemToDelete.value = null;
};

const downloadPDF = async () => {
    isGeneratingPDF.value = true;
    
    try {
        // URLs des images d'en-tête et pied de page
        const headerImageUrl = '/images/pdf-header.jpg'; // Chemin vers votre image d'en-tête
        const footerImageUrl = '/images/pdf-footer.jpg'; // Chemin vers votre image de pied de page
        
        await PdfService.generateArticlePDF(
            props.article, 
            headerImageUrl, 
            footerImageUrl
        );
    } catch (error) {
        console.error('Erreur génération PDF:', error);
        alert('Erreur lors de la génération du PDF');
    } finally {
        isGeneratingPDF.value = false;
    }
};

// Fonction pour ouvrir l'image en grand
const openImage = (imagePath) => {
    window.open(`/storage/${imagePath}`, '_blank');
};
</script>

<template>
    <Head :title="`Article - ${article.reference}`" />

    <div class="space-y-6">
        <!-- En-tête avec navigation -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-4">
                <Link 
                    :href="route('articles.index')"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5 mr-2" />
                    Retour à la liste
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Fiche Article</h1>
                    <p class="text-gray-600">Détails complets de l'article</p>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <button
                    @click="downloadPDF"
                    :disabled="isGeneratingPDF"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                    {{ isGeneratingPDF ? 'Génération...' : 'Télécharger PDF' }}
                </button>
                <!-- <Link
                    :href="route('articles.edit', article.id)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PencilIcon class="h-4 w-4 mr-2" />
                    Modifier
                </Link> -->
                <button
                    @click="openDeleteConfirm(article)"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    <TrashIcon class="h-4 w-4 mr-2" />
                    Supprimer
                </button>
            </div>
        </div>

        <!-- Contenu pour le PDF (caché à l'écran mais utilisé pour le PDF) -->
        <div id="pdf-content" class="hidden">
            <!-- Ce contenu sera utilisé pour générer le PDF -->
            <div class="pdf-content">
                <div class="pdf-header">
                    <h1>FICHE ARTICLE</h1>
                </div>
                
                <div class="pdf-body">
                    <div class="section">
                        <h2>Informations Générales</h2>
                        <table>
                            <tr>
                                <td><strong>Référence:</strong></td>
                                <td>{{ article.reference }}</td>
                                <td><strong>Unité:</strong></td>
                                <td>{{ article.unite_mesure }}</td>
                            </tr>
                            <tr>
                                <td><strong>Désignation:</strong></td>
                                <td colspan="3">{{ article.designation }}</td>
                            </tr>
                            <tr v-if="article.description">
                                <td><strong>Description:</strong></td>
                                <td colspan="3">{{ article.description }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="section">
                        <h2>Catégorisation</h2>
                        <table>
                            <tr>
                                <td><strong>Catégorie Principale:</strong></td>
                                <td>{{ article.categorie_principale.nom }}</td>
                            </tr>
                            <tr>
                                <td><strong>Catégorie:</strong></td>
                                <td>{{ article.categorie.nom }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nature de Prestation:</strong></td>
                                <td>{{ article.nature_prestation.nom }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="section">
                        <h2>Gestion des Stocks</h2>
                        <table>
                            <tr>
                                <td><strong>Stock Actuel:</strong></td>
                                <td>{{ article.stock_actuel }}</td>
                                <td><strong>Seuil Minimal:</strong></td>
                                <td>{{ article.seuil_minimal }}</td>
                                <td><strong>Seuil Maximal:</strong></td>
                                <td>{{ article.seuil_maximal }}</td>
                            </tr>
                        </table>
                    </div>

                    <div v-if="article.images && article.images.length > 0" class="section">
                        <h2>Images de l'article</h2>
                        <div class="images-grid">
                            <div v-for="(image, index) in article.images" :key="image.id" class="image-item">
                                <img :src="`/storage/${image.image_path}`" :alt="`Image ${index + 1}`" />
                                <p>Image {{ index + 1 }}{{ image.est_principale ? ' (Principale)' : '' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h2>Métadonnées</h2>
                        <table>
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td>{{ article.id }}</td>
                                <td><strong>Statut:</strong></td>
                                <td>{{ article.est_actif ? 'Actif' : 'Inactif' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Créé le:</strong></td>
                                <td>{{ new Date(article.created_at).toLocaleDateString('fr-FR') }}</td>
                                <td><strong>Modifié le:</strong></td>
                                <td>{{ new Date(article.updated_at).toLocaleDateString('fr-FR') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="pdf-footer">
                    <p>Document généré le {{ new Date().toLocaleDateString('fr-FR') }}</p>
                </div>
            </div>
        </div>

        <!-- Carte principale (affichage normal) -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <!-- En-tête de la carte -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ article.designation }}</h2>
                        <p class="text-blue-600 font-medium">{{ article.reference }}</p>
                    </div>
                    <div class="mt-2 sm:mt-0">
                        <span 
                            :class="[
                                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                article.est_actif 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            ]"
                        >
                            {{ article.est_actif ? '● Actif' : '● Inactif' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Informations principales sur 2 colonnes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Colonne gauche - Informations générales -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Informations Générales</h3>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Référence:</span>
                                    <span class="text-gray-900 font-semibold">{{ article.reference }}</span>
                                </div>
                                
                                <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Désignation:</span>
                                    <span class="text-gray-900 text-right">{{ article.designation }}</span>
                                </div>
                                
                                <div v-if="article.description" class="py-2 border-b border-gray-100">
                                    <div class="font-medium text-gray-700 mb-2">Description:</div>
                                    <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">{{ article.description }}</p>
                                </div>
                                
                                <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Unité de mesure:</span>
                                    <span class="text-gray-900 font-semibold">{{ article.unite_mesure }}</span>
                                </div>
                                
                                <div class="flex justify-between items-start py-2 border-b border-gray-100">
                                    <span class="font-medium text-gray-700">Taux TVA:</span>
                                    <span class="text-gray-900">{{ article.taux_tva }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Catégorisation -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Catégorisation</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <span class="font-medium text-blue-700">Catégorie Principale:</span>
                                    <span class="text-blue-900 font-semibold">{{ article.categorie_principale.nom }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <span class="font-medium text-green-700">Catégorie:</span>
                                    <span class="text-green-900 font-semibold">{{ article.categorie.nom }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                    <span class="font-medium text-purple-700">Nature de Prestation:</span>
                                    <span class="text-purple-900 font-semibold">{{ article.nature_prestation.nom }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne droite - Stocks et Images -->
                    <div class="space-y-6">
                        <!-- Gestion des stocks -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Gestion des Stocks</h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <div class="text-2xl font-bold text-gray-900">{{ article.stock_actuel }}</div>
                                        <div class="text-sm text-gray-600">Stock Actuel</div>
                                    </div>
                                    
                                    <div class="p-4 bg-yellow-50 rounded-lg">
                                        <div class="text-2xl font-bold text-yellow-700">{{ article.seuil_minimal }}</div>
                                        <div class="text-sm text-yellow-600">Seuil Minimal</div>
                                    </div>
                                    
                                    <div class="p-4 bg-blue-50 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-700">{{ article.seuil_maximal }}</div>
                                        <div class="text-sm text-blue-600">Seuil Maximal</div>
                                    </div>
                                </div>
                                
                                <!-- Barre de progression du stock -->
                                <div class="mt-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Niveau de stock</span>
                                        <span>{{ Math.round((article.stock_actuel / article.seuil_maximal) * 100) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div 
                                            :class="[
                                                'h-2 rounded-full',
                                                article.stock_actuel <= article.seuil_minimal 
                                                    ? 'bg-red-600' 
                                                    : article.stock_actuel <= article.seuil_maximal * 0.3 
                                                    ? 'bg-yellow-500' 
                                                    : 'bg-green-600'
                                            ]"
                                            :style="{ width: `${Math.min((article.stock_actuel / article.seuil_maximal) * 100, 100)}%` }"
                                        ></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>Rupture</span>
                                        <span>Optimal</span>
                                    </div>
                                </div>
                                
                                <!-- Alertes de stock -->
                                <div v-if="article.stock_actuel <= article.seuil_minimal" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <span class="text-red-400">⚠️</span>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-red-800">Stock critique</h4>
                                            <p class="text-sm text-red-700">Le stock actuel est en dessous du seuil minimal.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alerte stock élevé -->
                                <div v-else-if="article.stock_actuel > article.seuil_maximal" class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <span class="text-orange-400">📦</span>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-orange-800">Stock élevé</h4>
                                            <p class="text-sm text-orange-700">Le stock actuel dépasse le seuil maximal.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statut optimal -->
                                <div v-else class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <span class="text-green-400">✅</span>
                                        </div>
                                        <div class="ml-3">
                                            <h4 class="text-sm font-medium text-green-800">Stock optimal</h4>
                                            <p class="text-sm text-green-700">Le stock est dans les limites recommandées.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Galerie d'images -->
                        <div v-if="article.images && article.images.length > 0">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b">Galerie d'Images</h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <div 
                                    v-for="(image, index) in article.images" 
                                    :key="image.id"
                                    class="relative group cursor-pointer"
                                    @click="openImage(image.image_path)"
                                >
                                    <img 
                                        :src="`/storage/${image.image_path}`" 
                                        :alt="`Image ${index + 1} de ${article.designation}`"
                                        class="w-full h-24 object-cover rounded-lg border-2 border-gray-200 group-hover:border-blue-500 transition-colors"
                                    />
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                        <PhotoIcon class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                    </div>
                                    <div v-if="image.est_principale" class="absolute top-2 left-2">
                                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">Principale</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Cliquez sur une image pour l'agrandir</p>
                        </div>

                        <!-- Message si pas d'images -->
                        <div v-else class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                            <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm text-gray-500">Aucune image disponible</p>
                        </div>
                    </div>
                </div>

                <!-- Métadonnées -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Métadonnées</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">ID</div>
                            <div class="text-gray-600">{{ article.id }}</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">Créé le</div>
                            <div class="text-gray-600">{{ new Date(article.created_at).toLocaleDateString('fr-FR') }}</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">Modifié le</div>
                            <div class="text-gray-600">{{ new Date(article.updated_at).toLocaleDateString('fr-FR') }}</div>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="font-medium text-gray-900">Statut</div>
                            <div :class="article.est_actif ? 'text-green-600' : 'text-red-600'">
                                {{ article.est_actif ? 'Actif' : 'Inactif' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions Rapides</h3>
            <div class="flex flex-wrap gap-3">
                <Link
                    :href="route('articles.edit', article.id)"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PencilIcon class="h-4 w-4 mr-2" />
                    Modifier cet article
                </Link>
                <button
                    @click="downloadPDF"
                    :disabled="isGeneratingPDF"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
                    {{ isGeneratingPDF ? 'Génération...' : 'Exporter en PDF' }}
                </button>
                <button
                    @click="openDeleteConfirm(article)"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    <TrashIcon class="h-4 w-4 mr-2" />
                    Supprimer l'article
                </button>
                <Link
                    :href="route('articles.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <ArrowLeftIcon class="h-4 w-4 mr-2" />
                    Retour à la liste
                </Link>
            </div>
        </div>

        <!-- Statistiques supplémentaires -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Niveau de stock</p>
                        <p class="text-2xl font-bold" :class="
                            article.stock_actuel <= article.seuil_minimal ? 'text-red-600' :
                            article.stock_actuel > article.seuil_maximal ? 'text-orange-600' :
                            'text-green-600'
                        ">
                            {{ article.stock_actuel }} / {{ article.seuil_maximal }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg" :class="
                        article.stock_actuel <= article.seuil_minimal ? 'bg-red-100' :
                        article.stock_actuel > article.seuil_maximal ? 'bg-orange-100' :
                        'bg-green-100'
                    ">
                        <div class="w-6 h-6 rounded-full" :class="
                            article.stock_actuel <= article.seuil_minimal ? 'bg-red-600' :
                            article.stock_actuel > article.seuil_maximal ? 'bg-orange-600' :
                            'bg-green-600'
                        "></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Marge de sécurité</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ article.stock_actuel - article.seuil_minimal }}
                        </p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <div class="w-6 h-6 bg-blue-600 rounded-full"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Taux d'occupation</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ Math.round((article.stock_actuel / article.seuil_maximal) * 100) }}%
                        </p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <div class="w-6 h-6 bg-purple-600 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmation de Suppression -->
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
                        Êtes-vous sûr de vouloir supprimer cet article ?
                    </h4>
                    <p class="text-gray-600 mb-2">
                        <strong>{{ itemToDelete?.designation }}</strong>
                    </p>
                    <p class="text-gray-500 text-sm mb-4">
                        Référence: {{ itemToDelete?.reference }}
                    </p>
                    <p class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                        ⚠️ Cette action est irréversible. Toutes les données associées (images, historiques) seront définitivement supprimées.
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
</template>

<style scoped>
.hidden {
    display: none;
}

/* Styles spécifiques pour le contenu PDF */
.pdf-content {
    font-family: Arial, sans-serif;
    padding: 20px;
}

.pdf-header {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #333;
    padding-bottom: 10px;
}

.pdf-header h1 {
    font-size: 24px;
    color: #333;
    margin: 0;
}

.section {
    margin-bottom: 25px;
    page-break-inside: avoid;
}

.section h2 {
    font-size: 16px;
    color: #555;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
    margin-bottom: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 5px 10px;
    border: 1px solid #ddd;
    vertical-align: top;
}

.images-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 15px;
}

.image-item {
    text-align: center;
    page-break-inside: avoid;
}

.image-item img {
    max-width: 100%;
    height: auto;
    border: 1px solid #ddd;
}

.image-item p {
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.pdf-footer {
    margin-top: 30px;
    border-top: 1px solid #ddd;
    padding-top: 10px;
    text-align: center;
    font-size: 10px;
    color: #666;
}

/* Styles d'impression */
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        font-size: 12pt;
        color: #000;
        background: #fff;
        margin: 0;
        padding: 0;
    }
    
    .pdf-content {
        display: block !important;
    }
    
    .bg-gray-50, .bg-blue-50, .bg-green-50, .bg-purple-50, .bg-yellow-50 {
        background-color: transparent !important;
        border: 1px solid #000 !important;
    }
    
    button, .flex.flex-wrap.gap-2, .flex.flex-wrap.gap-3 {
        display: none !important;
    }
    
    .section {
        break-inside: avoid;
    }
    
    .images-grid {
        break-inside: avoid;
    }
}
</style>