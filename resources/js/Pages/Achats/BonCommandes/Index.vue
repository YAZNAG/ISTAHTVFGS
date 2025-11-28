<!-- resources/js/Pages/Achats/Marches/Index.vue -->
<template>
    <AuthenticatedLayout>

        <Head title="Gestion des Marchés" />

        <div class="space-y-6">
            <!-- En-tête -->
            <div class="flex items-center justify-between pt-4 px-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Les Marchés</h1>
                    <p class="text-gray-600">Gestion des marchés</p>
                </div>
                <div class="flex items-center gap-4">

                    <ModalLink as="button" href="#export-modal"
                        v-if="can('export_marches')"
                        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-400 flex items-center justify-center gap-2 ">
                        <DocumentArrowDownIcon class="h-5 w-5" />
                        Exporter
                    </ModalLink>
                    <CreateExportModal name="export-modal" />

                    <ModalLink :href="route('bon-commandes.create')"
                        v-if="can('create_marches')"
                        class="bg-blue-600 text-white  px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <PlusIcon class="h-5 w-5" />
                        Nouveau Marché
                    </ModalLink>

                </div>
            </div>

            <!-- Stats -->
            <section class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border">
                    <div class="text-gray-500">Marchés Créés</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-gray-800">{{ stats?.total || 0 }}</div>
                        <DocumentTextIcon class="w-6 h-6 text-gray-600" />
                    </div>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border">
                    <div class="text-gray-500">En Attente Livraison</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-yellow-700">{{ stats?.attente_livraison || 0 }}</div>
                        <ClockIcon class="w-6 h-6 text-yellow-600" />
                    </div>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border">
                    <div class="text-gray-500">Livrés</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-green-700">{{ stats?.livre_completement}}</div>
                        <CheckCircleIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border">
                    <div class="text-gray-500">Montant Total</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-indigo-700">{{ formatCurrency(stats?.montant_total || 0) }}
                        </div>
                        <BanknotesIcon class="w-6 h-6 text-indigo-600" />
                    </div>
                </div>
            </section>

            <!-- Filtres -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <select v-model="filters.statut" class="w-full border border-gray-300 rounded-lg p-2">
                            <option value="">Tous les statuts</option>
                            <option value="cree">Créé</option>
                            <option value="attente_livraison">En attente livraison</option>
                            <option value="livre_completement">Livré complètement</option>
                            <option value="livre_partiellement">Livré partiellement</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                        <select v-model="filters.categorie_id" class="w-full border border-gray-300 rounded-lg p-2">
                            <option value="">Toutes les catégories</option>
                            <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
                                {{ categorie.nom }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date limite</label>
                        <input v-model="filters.date_limite" type="date"
                            class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Référence</label>
                        <input v-model="filters.reference" type="text" placeholder="Rechercher par référence"
                            class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 mr-2">
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- Tableau des bons de commande -->
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date mise
                                    ligne</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date limite
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant TTC
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="marche in marches.data" :key="marche.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ marche.reference }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ marche.objet }}</div>
                                    <div v-if="marche.description" class="text-sm text-gray-500 truncate max-w-xs">
                                        {{ marche.description }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ marche.categorie?.nom || 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(marche.date_mise_ligne) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(marche.date_limite_reception) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ marche.fournisseur?.nom || 'Non attribué' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium" >
                                        ---
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getBonCommandeStatutInfo(marche.statut).color"
                                        class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ getBonCommandeStatutInfo(marche.statut).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <Link :href="route('bon-commandes.show', marche.id)"
                                            v-if="can('show_marches')"
                                            class="text-blue-600 hover:text-blue-900 p-1" title="Voir détails">
                                        <EyeIcon class="h-4 w-4" />
                                        </Link>
                                        <ModalLink :href="route('bon-commandes.edit', marche.id)"
                                            class="text-green-600 hover:text-green-900 p-1" title="Modifier statut"
                                            v-if="marche.statut === 'cree' && can('validate_marches')">
                                            <ArrowPathIcon class="h-4 w-4" />
                                        </ModalLink>
                                        <!-- Bouton PDF - N'apparaît que pour les statuts différents de "cree" et "annule" -->
                                        <a v-if="marche.statut !== 'cree' && marche.statut !== 'annule' && can('pdf_marches')"
                                            :href="route('bon-commandes.pdf', marche.id)" target="_blank"
                                            class="text-purple-600 hover:text-purple-900 p-1" title="Télécharger PDF">
                                            <DocumentTextIcon class="h-4 w-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Message vide -->
                <div v-if="marches.data.length === 0" class="text-center py-12">
                    <div class="text-gray-500">
                        <DocumentTextIcon class="mx-auto h-12 w-12" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun marché trouvé</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Commencez par créer votre premier marché.
                        </p>
                        <div class="mt-6">
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="marches.links && marches.links.length > 1"
                    class="bg-white px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Affichage de {{ marches.from }} à {{ marches.to }} sur {{ marches.total }} résultats
                        </div>
                        <div class="flex space-x-2">
                            <template v-for="link in marches.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" :class="[
                                    'px-3 py-1 rounded-lg text-sm font-medium',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                ]" v-html="link.label" />
                                <span v-else :class="[
                                    'px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import {
    PlusIcon,
    EyeIcon,
    DocumentTextIcon,
    XMarkIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    CheckCircleIcon,
    BanknotesIcon,
    DocumentArrowDownIcon
}
    from '@heroicons/vue/24/outline';
import { ModalLink } from '@inertiaui/modal-vue';

import InputError from '@/Components/InputError.vue';
import CreateExportModal from './CreateExportModal.vue';
import { getBonCommandeStatutInfo } from '@/Utils/labels';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

// Props avec valeurs par défaut pour éviter les erreurs
const props = defineProps({
    marches: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    articles: {
        type: Array,
        default: () => []
    },
    fournisseurs: {
        type: Array,
        default: () => []
    },
    tauxTVA: {
        type: Array,
        default: () => [0, 5, 7, 10, 14, 20]
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Array,
    }
});

// États pour les modales
const showMarcheForm = ref(false);
const showUpdateStatutForm = ref(false);
const showNewFournisseurForm = ref(false);
const selectedMarche = ref(null);
const confirmationAnnulation = ref(false);

// Filtres
const filters = ref({
    statut: props.filters?.statut || '',
    categorie_principale_id: props.filters?.categorie_principale_id || '',
    date_limite: props.filters?.date_limite || '',
    reference: props.filters?.reference || '',
});

// Formulaires
const marcheForm = useForm({
    reference: '',
    objet: '',
    description: '',
    categorie_id: '',
    date_debut: new Date().toISOString().split('T')[0],
    date_fin: null,
    date_mise_ligne: new Date().toISOString().split('T')[0],
    date_limite_reception: '',
    pieces_jointes: [],
    notes: '',
    articles: [],
});

const statutForm = useForm({
    statut: 'attente_livraison',
    fournisseur_id: '',
    articles: [],
    raison: '',
});

const newFournisseurForm = useForm({
    nom: '',
    code: '',
    contact: '',
    telephone: '',
    email: '',
    adresse: '',
    ville: '',
    pays: 'Maroc',
    ice: '',
    if: '',
    rc: '',
    patente: '',
    logo: null,
    logoPreview: null,
});

// Computed properties
const showFournisseurAndPrixSection = computed(() => {
    return ['attente_livraison', 'livre_completement', 'livre_partiellement'].includes(statutForm.statut);
});

// Dans la computed property isStatutFormValid
const isStatutFormValid = computed(() => {
    if (statutForm.statut === 'annule') {
        return !!statutForm.raison && statutForm.raison.length >= 20 && confirmationAnnulation.value;
    } else {
        // Seulement vérifier le fournisseur, pas les prix
        const hasFournisseur = !!statutForm.fournisseur_id;
        return hasFournisseur;
    }
});

// Dans le template, supprimez la section des prix pour l'annulation
// Gardez seulement la confirmation d'annulation

const isNewFournisseurValid = computed(() => {
    return newFournisseurForm.nom && newFournisseurForm.code;
});

const getSubmitButtonText = computed(() => {
    if (statutForm.statut === 'annule') {
        return 'Confirmer l\'annulation';
    } else {
        return 'Mettre à jour le statut';
    }
});

const totalHTStatut = computed(() => {
    return statutForm.articles.reduce((total, article) => {
        return total + (article.montant_ht || 0);
    }, 0);
});

const totalTVAStatut = computed(() => {
    return statutForm.articles.reduce((total, article) => {
        return total + (article.montant_tva || 0);
    }, 0);
});

const totalTTCStatut = computed(() => {
    return statutForm.articles.reduce((total, article) => {
        return total + (article.montant_ttc || 0);
    }, 0);
});

// Watch pour les filtres
watch(filters, (value) => {
    router.get(route('bon-commandes.index'), value, {
        preserveState: true,
        replace: true,
    });
}, { deep: true });

// Méthodes utilitaires
const getArticle = (articleId) => {
    return props.articles.find(art => art.id == articleId);
};

const getTvaLabel = (taux) => {
    const labels = {
        0: 'Exonéré 0%',
        5: '5%',
        7: '7%',
        10: '10%',
        14: '14%',
        20: '20%'
    };
    return labels[taux] || `${taux}%`;
};

const showToast = (message, duration = 3000) => {
    toast.value = { show: true, message };
    setTimeout(() => {
        toast.value.show = false;
    }, duration);
};

// Méthodes principales
const openMarcheForm = () => {
    showMarcheForm.value = true;
    marcheForm.reset();

    const today = new Date();
    marcheForm.date_mise_ligne = today.toISOString().split('T')[0];

    const dateLimite = new Date(today);
    dateLimite.setDate(dateLimite.getDate() + 15);
    marcheForm.date_limite_reception = dateLimite.toISOString().split('T')[0];

    marcheForm.articles = [];
};

// États pour les fournisseurs
const selectedFournisseur = ref(null);

// Méthodes pour les fournisseurs
const onFournisseurChange = () => {
    if (statutForm.fournisseur_id) {
        selectedFournisseur.value = props.fournisseurs.find(f => f.id == statutForm.fournisseur_id);
    } else {
        selectedFournisseur.value = null;
    }
};

// Mettez à jour la méthode openUpdateStatutForm
const openUpdateStatutForm = (marche) => {
    selectedMarche.value = marche;
    showUpdateStatutForm.value = true;
    showMarcheForm.value = false;
    confirmationAnnulation.value = false;
    selectedFournisseur.value = null;

    // Initialiser le formulaire de statut
    statutForm.reset();
    statutForm.statut = 'attente_livraison';
    statutForm.fournisseur_id = marche.fournisseur_id || '';
    statutForm.raison = '';

    // Initialiser les articles
    statutForm.articles = (marche.articles || []).map((articlePivot, index) => {
        return {
            id: articlePivot.id,
            article_id: articlePivot.article_id,
            reference: articlePivot.article?.reference || 'N/A',
            designation: articlePivot.article?.designation || 'Article non trouvé',
            unite_mesure: articlePivot.article?.unite_mesure || 'N/A',
            quantite_commandee: articlePivot.quantite_commandee,
            taux_tva: articlePivot.taux_tva,
            prix_unitaire_ht: articlePivot.prix_unitaire_ht || 0,
            montant_ht: articlePivot.montant_ht || 0,
            montant_tva: articlePivot.montant_tva || 0,
            montant_ttc: articlePivot.montant_ttc || 0,
        };
    });

    // Si un fournisseur est déjà attribué, charger ses informations
    if (marche.fournisseur_id) {
        selectedFournisseur.value = props.fournisseurs.find(f => f.id == marche.fournisseur_id);
    }

    // Scroll vers le formulaire
    setTimeout(() => {
        document.querySelector('.edit-form-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 100);
};

const closeAllForms = () => {
    showMarcheForm.value = false;
    showUpdateStatutForm.value = false;
    showNewFournisseurForm.value = false;
    selectedMarche.value = null;
    confirmationAnnulation.value = false;
    marcheForm.reset();
    statutForm.reset();
    newFournisseurForm.reset();
};

const resetFilters = () => {
    filters.value = {
        statut: '',
        categorie_id: '',
        date_limite: '',
        reference: '',
    };
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD',
        minimumFractionDigits: 2
    }).format(amount || 0);
};



</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.badge {
    @apply px-2 py-1 text-xs font-medium rounded-full;
}
</style>
<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>