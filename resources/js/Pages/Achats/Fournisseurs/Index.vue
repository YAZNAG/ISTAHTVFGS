<template>
    <AuthenticatedLayout>
        <Head title="Gestion des Fournisseurs" />

        <div class="space-y-6">
            <!-- En-tête -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-4 px-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Fournisseurs</h1>
                    <p class="text-gray-600">Gestion des partenaires fournisseurs</p>
                </div>
                <div class="flex space-x-3">
                    <button
                        v-if="can('export_fournisseurs')"
                        @click="exportFournisseurs"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2 transition-colors"
                    >
                        <DocumentArrowDownIcon class="h-5 w-5" />
                        Exporter
                    </button>
                    <button
                        v-if="can('create_fournisseurs')"
                        @click="openFournisseurForm()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-colors"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Nouveau fournisseur
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <section class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-gray-500">Total Fournisseurs</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-gray-800">{{ stats?.total || 0 }}</div>
                        <UsersIcon class="w-6 h-6 text-gray-600" />
                    </div>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-gray-500">Fournisseurs Actifs</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-green-700">{{ stats?.actifs || 0 }}</div>
                        <CheckBadgeIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-gray-500">Fournisseurs Inactifs</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-red-700">{{ stats?.inactifs || 0 }}</div>
                        <XCircleIcon class="w-6 h-6 text-red-600" />
                    </div>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-gray-500">Marchés</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-indigo-700">{{ stats?.bons_commande || 0 }}</div>
                        <DocumentTextIcon class="w-6 h-6 text-indigo-600" />
                    </div>
                </div>
            </section>

            <!-- Filtres et Recherche -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 flex justify-between items-end">
                <div class="w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                    <div class="relative">
                        <input v-model="filters.search" type="text" placeholder="Rechercher par nom, raison sociale, contact..." 
                            class="w-full border border-gray-300 rounded-lg p-2 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 mr-2 transition-colors"
                    >
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- Formulaire de création/édition -->
            <div v-if="showFournisseurForm" class="bg-white rounded-lg shadow-lg border border-blue-200">
                <div class="flex items-center justify-between p-6 border-b border-blue-100 bg-blue-50 rounded-t-lg">
                    <h3 class="text-lg font-semibold text-blue-900">
                        {{ isEditing ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
                    </h3>
                    <button @click="closeAllForms" class="text-blue-400 hover:text-blue-600 transition-colors">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submitFournisseurForm" class="p-6 space-y-6" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Colonne gauche -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                                <input v-model="fournisseurForm.nom" type="text" required
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Nom du fournisseur">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Raison sociale</label>
                                <input v-model="fournisseurForm.raison_sociale" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Raison sociale">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxe Professionnelle</label>
                                <input v-model="fournisseurForm.tp" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Taxe Professionnelle">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Register de Commerce</label>
                                <input v-model="fournisseurForm.rc" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Register de Commerce">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Identifiant Fiscal</label>
                                <input v-model="fournisseurForm.if" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Identifiant Fiscal">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact</label>
                                <input v-model="fournisseurForm.contact" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Personne à contacter">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                                    <input v-model="fournisseurForm.telephone" type="text"
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="+212 XXX XXX XXX">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input v-model="fournisseurForm.email" type="email"
                                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="email@exemple.com">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Colonne droite -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                                <textarea v-model="fournisseurForm.adresse" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Adresse complète"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                                <input v-model="fournisseurForm.ville" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Ville">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ICE</label>
                                <input v-model="fournisseurForm.ice" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Identifiant Commun de l'Entreprise">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CNSS</label>
                                <input v-model="fournisseurForm.cnss" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="CNSS">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Compte Bancaire</label>
                                <input v-model="fournisseurForm.cb" type="text"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Compte Bancaire">
                            </div>

                            <!-- Logo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                                <input 
                                    type="file" 
                                    @change="onLogoChange" 
                                    accept="image/*"
                                    class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <p class="text-xs text-gray-500 mt-2">
                                    Formats acceptés: JPG, PNG, GIF (max 2MB)
                                </p>
                                
                                <!-- Aperçu du logo -->
                                <div v-if="fournisseurForm.logoPreview || selectedFournisseur?.logo_url" class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Aperçu du logo:</p>
                                    <div class="flex items-center space-x-4">
                                        <img 
                                            :src="fournisseurForm.logoPreview || selectedFournisseur?.logo_url" 
                                            alt="Logo preview" 
                                            class="h-16 w-16 object-cover rounded-lg border border-gray-200"
                                            v-if="fournisseurForm.logoPreview || selectedFournisseur?.logo_url"
                                        >
                                        <button 
                                            type="button" 
                                            @click="removeLogo"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                                            v-if="fournisseurForm.logo || selectedFournisseur?.logo_url"
                                        >
                                            Supprimer le logo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea v-model="fournisseurForm.notes" rows="4"
                            class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Notes supplémentaires..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeAllForms"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                            :disabled="fournisseurForm.processing">
                            <span v-if="fournisseurForm.processing">
                                {{ isEditing ? 'Modification...' : 'Création...' }}
                            </span>
                            <span v-else>
                                {{ isEditing ? 'Modifier le fournisseur' : 'Créer le fournisseur' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tableau des fournisseurs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Localisation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ICE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marchés</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="fournisseur in fournisseurs.data" :key="fournisseur.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img v-if="fournisseur.logo_url" :src="fournisseur.logo_url" 
                                                class="h-10 w-10 rounded-full object-cover border border-gray-200">
                                            <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <UserCircleIcon class="h-6 w-6 text-gray-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ fournisseur.nom_affichage }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ fournisseur.nom }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div v-if="fournisseur.contact" class="font-medium">{{ fournisseur.contact }}</div>
                                        <div v-if="fournisseur.telephone" class="text-blue-600">{{ fournisseur.telephone }}</div>
                                        <div v-if="fournisseur.email" class="text-blue-600 truncate max-w-xs">{{ fournisseur.email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div v-if="fournisseur.adresse" class="truncate max-w-xs">{{ fournisseur.adresse }}</div>
                                        <div v-if="fournisseur.ville" class="text-gray-500">{{ fournisseur.ville }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500">
                                        <div v-if="fournisseur.ice">{{ fournisseur.ice }}</div>
                                        <div v-else class="text-gray-400">-</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ fournisseur.bon_commandes_count || 0 }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatutBadgeClass(fournisseur.est_actif)" 
                                          class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ fournisseur.est_actif ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <Link
                                            v-if="can('show_fournisseurs')"
                                            :href="route('fournisseurs.show', fournisseur.id)"
                                            class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-xl transition-all duration-200 group/tooltip relative"
                                            title="Voir détails complets"
                                        >
                                            <EyeIcon class="h-5 w-5" />
                                        </Link>
                                        
                                        <button
                                            v-if="can('edit_fournisseurs')"
                                            @click="editFournisseur(fournisseur)"
                                            class="text-blue-600 hover:text-blue-900 p-1 rounded-lg hover:bg-blue-50 transition-colors"
                                            title="Modifier"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </button>
                                        <button
                                            v-if="can('edit_fournisseurs')"
                                            @click="openToggleModal(fournisseur.id)"
                                            :class="[
                                                'p-1 rounded-lg transition-colors',
                                                fournisseur.est_actif 
                                                    ? 'text-orange-600 hover:text-orange-900 hover:bg-orange-50' 
                                                    : 'text-green-600 hover:text-green-900 hover:bg-green-50'
                                            ]"
                                            :title="fournisseur.est_actif ? 'Désactiver' : 'Activer'"
                                        >
                                            <PowerIcon class="h-4 w-4" />
                                        </button>
                                        <button
                                            v-if="can('delete_fournisseurs')"
                                            @click="confirmDelete(fournisseur)"
                                            class="text-red-600 hover:text-red-900 p-1 rounded-lg hover:bg-red-50 transition-colors"
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
                <div v-if="fournisseurs.data.length === 0" class="text-center py-16">
                    <div class="text-gray-500">
                        <UsersIcon class="mx-auto h-20 w-20 text-gray-300" />
                        <h3 class="mt-4 text-xl font-medium text-gray-900">Aucun fournisseur trouvé</h3>
                        <p class="mt-2 text-gray-500">
                            {{ filters.search || filters.est_actif !== '' || filters.ville ? 'Aucun résultat pour vos critères de recherche.' : 'Commencez par créer votre premier fournisseur.' }}
                        </p>
                        <div class="mt-8" v-if="!filters.search && filters.est_actif === '' && !filters.ville">
                            <button
                                v-if="can('create_fournisseurs')"
                                @click="openFournisseurForm()"
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                            >
                                <PlusIcon class="h-5 w-5 mr-2" />
                                Nouveau fournisseur
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="fournisseurs.links && fournisseurs.links.length > 1" class="bg-white px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-700">
                            Affichage de {{ fournisseurs.from }} à {{ fournisseurs.to }} sur {{ fournisseurs.total }} résultats
                        </div>
                        <div class="flex space-x-1">
                            <template v-for="link in fournisseurs.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                        link.active 
                                            ? 'bg-blue-600 text-white border border-blue-600' 
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    :class="[
                                        'px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-400 border border-gray-300 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmation de suppression -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center gap-3 mb-4">
                    <ExclamationTriangleIcon class="h-6 w-6 text-red-500" />
                    <h3 class="text-lg font-semibold text-gray-900">Confirmer la suppression</h3>
                </div>
                <p class="text-gray-600 mb-6">
                    Êtes-vous sûr de vouloir supprimer le fournisseur 
                    <strong>"{{ selectedFournisseur?.nom_affichage }}"</strong> ? 
                    Cette action est irréversible.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="closeDeleteModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        @click="deleteFournisseur"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <transition name="fade">
            <div v-if="toast.show" class="fixed bottom-6 right-6 bg-white px-6 py-4 rounded-xl shadow-xl border border-green-200 text-green-800 font-medium max-w-sm">
                <div class="flex items-center gap-3">
                    <CheckCircleIcon class="h-5 w-5 text-green-500" />
                    {{ toast.message }}
                </div>
            </div>
        </transition>

        <ConfirmationModal
            type="danger"
            :show="showToggleModal"
            title="Modifier le statut"
            message="Êtes-vous sûr de vouloir modifier le statut ?"
            :onConfirm="toggleStatut"
            @close="showToggleModal = false"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import { 
    PlusIcon, 
    PencilIcon,
    TrashIcon,
    PowerIcon,
    XMarkIcon,
    DocumentTextIcon,
    DocumentArrowDownIcon,
    UsersIcon,
    UserCircleIcon,
    CheckBadgeIcon,
    XCircleIcon,
    MagnifyingGlassIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

// Props
const props = defineProps({
    fournisseurs: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({})
    },
});

// États
const showFournisseurForm = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedFournisseur = ref(null);

// Toast
const toast = ref({
    show: false,
    message: ''
});

// Filtres
const filters = ref({
    search: props.filters?.search || '',
});

// Formulaire fournisseur - seulement les champs qui existent
const fournisseurForm = useForm({
    nom: '',
    raison_sociale: '',
    contact: '',
    telephone: '',
    email: '',
    adresse: '',
    ville: '',
    ice: '',
    tp: '',
    rc: '',
    if: '',
    cb: '',
    cnss: '',
    notes: '',
    logo: null,
    logoPreview: null,
    _method: 'post'
});

// Watch pour les filtres
watch(filters, (value) => {
    router.get(route('fournisseurs.index'), value, {
        preserveState: true,
        replace: true,
    });
}, { deep: true });

// Méthodes utilitaires
const showToast = (message, duration = 3000) => {
    toast.value = { show: true, message };
    setTimeout(() => {
        toast.value.show = false;
    }, duration);
};

const getStatutBadgeClass = (estActif) => {
    return estActif 
        ? 'bg-green-100 text-green-800' 
        : 'bg-red-100 text-red-800';
};

// Méthodes principales
const openFournisseurForm = () => {
    showFournisseurForm.value = true;
    isEditing.value = false;
    selectedFournisseur.value = null;
    fournisseurForm.reset();
    fournisseurForm.logoPreview = null;
};

const editFournisseur = (fournisseur) => {
    selectedFournisseur.value = fournisseur;
    showFournisseurForm.value = true;
    isEditing.value = true;
    
    // Remplir le formulaire avec les données du fournisseur
    fournisseurForm.nom = fournisseur.nom;
    fournisseurForm.raison_sociale = fournisseur.raison_sociale || '';
    fournisseurForm.contact = fournisseur.contact || '';
    fournisseurForm.telephone = fournisseur.telephone || '';
    fournisseurForm.email = fournisseur.email || '';
    fournisseurForm.adresse = fournisseur.adresse || '';
    fournisseurForm.ville = fournisseur.ville || '';
    fournisseurForm.ice = fournisseur.ice || '';
    fournisseurForm.tp = fournisseur.tp || '';
    fournisseurForm.cnss = fournisseur.cnss || '';
    fournisseurForm.rc = fournisseur.rc || '';
    fournisseurForm.if = fournisseur.if || '';
    fournisseurForm.cb = fournisseur.cb || '';
    fournisseurForm.notes = fournisseur.notes || '';
};

const closeAllForms = () => {
    showFournisseurForm.value = false;
    showDeleteModal.value = false;
    selectedFournisseur.value = null;
    fournisseurForm.reset();
    fournisseurForm.logoPreview = null;
};

const resetFilters = () => {
    filters.value = {
        est_actif: '',
        ville: '',
        search: '',
    };
};

const onLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            showToast('Le fichier est trop volumineux (max 2MB)', 3000);
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            showToast('Veuillez sélectionner une image', 3000);
            return;
        }
        
        fournisseurForm.logo = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            fournisseurForm.logoPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    fournisseurForm.logo = null;
    fournisseurForm.logoPreview = null;
    
    if (isEditing.value && selectedFournisseur.value) {
        // Marquer le logo pour suppression
        fournisseurForm.logo = 'delete';
    }
};

const submitFournisseurForm = () => {
    const url = isEditing.value 
        ? route('fournisseurs.update', selectedFournisseur.value.id)
        : route('fournisseurs.store');

    if (isEditing.value){
        fournisseurForm._method = 'put';
    }
    
    fournisseurForm.post(url, {
        onSuccess: () => {
            closeAllForms();
            showToast(
                isEditing.value 
                    ? 'Fournisseur modifié avec succès' 
                    : 'Fournisseur créé avec succès', 
                3000
            );
        },
        onError: (errors) => {
            console.error('Erreurs de validation:', errors);
            showToast('Erreur lors de l\'opération', 3000);
        }
    });
};


const showToggleModal = ref(false)
const fourniseeurIdToToggle = ref(null)

function openToggleModal(id) {
  showToggleModal.value = true
  fourniseeurIdToToggle.value = id
}

const toggleStatut = () => {
    router.patch(route('fournisseurs.toggle-statut', fourniseeurIdToToggle.value));
};

const confirmDelete = (fournisseur) => {
    selectedFournisseur.value = fournisseur;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedFournisseur.value = null;
};

const deleteFournisseur = () => {
    if (!selectedFournisseur.value) return;

    router.delete(route('fournisseurs.destroy', selectedFournisseur.value.id), {
        onSuccess: () => {
            closeDeleteModal();
            showToast('Fournisseur supprimé avec succès', 3000);
        },
        onError: () => {
            showToast('Erreur lors de la suppression du fournisseur', 3000);
        }
    });
};

const exportFournisseurs = () => {
    window.open(route('fournisseurs.export', filters.value), '_blank');
};

// Initialisation
onMounted(() => {
    console.log('Fournisseurs component mounted');
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>