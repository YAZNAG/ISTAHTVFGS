<template>
    <AuthenticatedLayout>
        <Head title="Créer un Bon de Réception" />
        
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- En-tête -->
            <div class="px-4 sm:px-0 mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Créer un Bon de Réception</h1>
                        <p class="text-gray-600 mt-2 text-lg">Remplissez les informations pour enregistrer une nouvelle réception</p>
                    </div>
                    <Link 
                        :href="route('bon-receptions.index')"
                        class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center gap-2 shadow-md"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                        Retour à la liste
                    </Link>
                </div>
            </div>

            <!-- Alertes -->
            <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <CheckCircleIcon class="h-6 w-6 text-green-500 mr-3" />
                    <div>
                        <h3 class="text-green-800 font-semibold">Succès</h3>
                        <p class="text-green-700 mt-1">{{ $page.props.flash.success }}</p>
                    </div>
                </div>
            </div>

            <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <ExclamationTriangleIcon class="h-6 w-6 text-red-500 mr-3" />
                    <div>
                        <h3 class="text-red-800 font-semibold">Erreur</h3>
                        <p class="text-red-700 mt-1">{{ $page.props.flash.error }}</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="space-y-8">
                <!-- Étape 1: Sélection de la commande -->
                <div class="bg-white shadow-xl rounded-2xl p-8 border-l-8 border-blue-600 transform transition-all duration-300 hover:shadow-2xl">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            1
                        </div>
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold text-gray-900">Sélection de la Commande</h2>
                            <p class="text-gray-600 mt-1">Choisissez la commande à réceptionner</p>
                        </div>
                    </div>
                    
                    <div class="max-w-2xl">
                        <label class="block text-lg font-medium text-gray-800 mb-3">
                            Commande *
                        </label>
                        <select 
                            v-model="form.bon_commande_id" 
                            @change="loadCommandeDetails"
                            class="w-full px-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm"
                            :class="{ 'border-red-400': form.errors.bon_commande_id }"
                            required
                        >
                            <option value="" class="text-gray-400">Sélectionnez une commande...</option>
                            <option 
                                v-for="commande in bonCommandes" 
                                :value="commande.id"
                                :key="commande.id"
                                :disabled="commande.reste_a_recevoir <= 0"
                                class="py-2"
                            >
                                {{ commande.reference }} - {{ commande.fournisseur?.raison_sociale || 'Non spécifié' }} 
                                ({{ commande.pourcentage_recu }}% reçu - Reste: {{ commande.reste_a_recevoir }})
                            </option>
                        </select>
                        <p v-if="form.errors.bon_commande_id" class="mt-3 text-red-600 font-medium flex items-center gap-2">
                            <ExclamationCircleIcon class="h-5 w-5" />
                            {{ form.errors.bon_commande_id }}
                        </p>
                    </div>
                </div>

                <!-- Informations de la commande sélectionnée -->
                <div v-if="selectedCommande" class="bg-gradient-to-r from-blue-50 to-indigo-50 shadow-lg rounded-2xl p-8 border-2 border-blue-200 transform transition-all duration-300">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                        <InformationCircleIcon class="h-8 w-8 text-blue-600" />
                        Informations de la Commande Sélectionnée
                    </h2>
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 text-base">
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
                            <div class="text-blue-600 font-semibold">Référence</div>
                            <div class="text-gray-900 font-bold text-lg mt-1">{{ selectedCommande.reference }}</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
                            <div class="text-blue-600 font-semibold">Fournisseur</div>
                            <div class="text-gray-900 font-bold text-lg mt-1">{{ selectedCommande.fournisseur?.raison_sociale || 'Non spécifié' }}</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
                            <div class="text-blue-600 font-semibold">Date commande</div>
                            <div class="text-gray-900 font-bold text-lg mt-1">{{ formatDate(selectedCommande.date_mise_ligne) }}</div>
                        </div>
                        <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100">
                            <div class="text-blue-600 font-semibold">Statut</div>
                            <div class="text-gray-900 font-bold text-lg mt-1 capitalize">{{ selectedCommande.statut }}</div>
                        </div>
                    </div>
                    
                    <!-- Barre de progression -->
                    <div class="mt-6 bg-white p-6 rounded-xl shadow-sm border border-blue-100">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-semibold text-gray-900">Progression de la livraison</span>
                            <span class="text-2xl font-bold text-blue-600">{{ Math.min(selectedCommande.pourcentage_recu, 100) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
                            <div 
                                class="bg-gradient-to-r from-blue-500 to-blue-600 h-4 rounded-full transition-all duration-1000 ease-out shadow-md" 
                                :style="{ width: Math.min(selectedCommande.pourcentage_recu, 100) + '%' }"
                            ></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mt-2">
                            <span>Commandé: {{ selectedCommande.quantite_totale_commandee }}</span>
                            <span>Reçu: {{ selectedCommande.quantite_totale_recue }}</span>
                            <span class="font-bold text-orange-600">Reste: {{ selectedCommande.reste_a_recevoir }}</span>
                        </div>
                    </div>
                </div>

                <!-- Étape 2: Articles à réceptionner -->
                <div v-if="selectedCommande" class="bg-white shadow-xl rounded-2xl overflow-hidden border-l-8 border-green-600 transform transition-all duration-300 hover:shadow-2xl">
                    <div class="px-8 py-6 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                2
                            </div>
                            <div class="ml-4">
                                <h2 class="text-2xl font-bold text-gray-900">Articles à Réceptionner</h2>
                                <p class="text-gray-600 mt-1">Saisissez les quantités reçues pour chaque article</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-8 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider bg-gradient-to-r from-gray-50 to-gray-100">Article</th>
                                    <th class="px-4 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider bg-gradient-to-r from-gray-50 to-gray-100">Quantité Commandée</th>
                                    <th class="px-4 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider bg-gradient-to-r from-gray-50 to-gray-100">Déjà Reçue</th>
                                    <th class="px-4 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider bg-gradient-to-r from-gray-50 to-gray-100">Reste à Recevoir</th>
                                    <th class="px-4 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider bg-gradient-to-r from-gray-50 to-gray-100">Quantité Reçue *</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr 
                                    v-for="(ligne, index) in selectedCommande.articles" 
                                    :key="ligne.id" 
                                    class="hover:bg-blue-50 transition-all duration-200 transform hover:scale-[1.01]"
                                >
                                    <td class="px-8 py-6 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <CubeIcon class="h-6 w-6 text-blue-600" />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-lg font-semibold text-gray-900">{{ ligne.article?.designation }}</div>
                                                <div class="text-sm text-gray-500 mt-1">Ref: {{ ligne.article?.reference }}</div>
                                                <div class="text-xs text-gray-400 mt-1">Unité: {{ ligne.article?.unite_mesure }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-6 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 shadow-sm">
                                            {{ ligne.quantite_commandee }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-6 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800 shadow-sm">
                                            {{ ligne.quantite_deja_recue || 0 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-6 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-800 shadow-sm">
                                            {{ ligne.reste_a_recevoir }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-6 whitespace-nowrap">
                                        <div class="flex items-center justify-center space-x-3">
                                            <input
                                                type="number"
                                                v-model="form.lignes_reception[index].quantite_receptionnee"
                                                :max="ligne.reste_a_recevoir"
                                                min="0"
                                                step="0.01"
                                                class="w-28 px-4 py-3 border-2 border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-4 focus:ring-green-500 focus:border-green-500 text-center font-semibold text-lg transition-all duration-200"
                                                @change="validateQuantite(ligne, index)"
                                                placeholder="0"
                                                :class="{
                                                    'border-green-500 bg-green-50': form.lignes_reception[index].quantite_receptionnee > 0,
                                                    'border-red-400': form.errors[`lignes_reception.${index}.quantite_receptionnee`]
                                                }"
                                            >
                                            <button
                                                type="button"
                                                @click="prefillQuantite(ligne, index)"
                                                class="px-4 py-3 text-sm bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 font-semibold"
                                                title="Remplir avec le reste à recevoir"
                                            >
                                                MAX
                                            </button>
                                        </div>
                                        <div 
                                            v-if="form.errors[`lignes_reception.${index}.quantite_receptionnee`]" 
                                            class="text-red-600 font-medium text-sm mt-2 flex items-center justify-center gap-2"
                                        >
                                            <ExclamationCircleIcon class="h-4 w-4" />
                                            {{ form.errors[`lignes_reception.${index}.quantite_receptionnee`] }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Actions rapides -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-wrap gap-4">
                            <button
                                type="button"
                                @click="prefillAllWithMax"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 font-semibold flex items-center gap-2"
                            >
                                <CheckIcon class="h-5 w-5" />
                                Remplir tous les restes
                            </button>
                            <button
                                type="button"
                                @click="resetAllQuantities"
                                class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 font-semibold flex items-center gap-2"
                            >
                                <ArrowPathIcon class="h-5 w-5" />
                                Réinitialiser toutes les quantités
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Étape 3: Informations de réception -->
                <div class="bg-white shadow-xl rounded-2xl p-8 border-l-8 border-purple-600 transform transition-all duration-300 hover:shadow-2xl">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            3
                        </div>
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold text-gray-900">Informations de Réception</h2>
                            <p class="text-gray-600 mt-1">Détails de la réception physique</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl">
                        <div class="space-y-2">
                            <label class="block text-lg font-semibold text-gray-800">
                                Date de Réception *
                            </label>
                            <input
                                type="date"
                                v-model="form.date_reception"
                                class="w-full px-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 shadow-sm"
                                :class="{ 'border-red-400': form.errors.date_reception }"
                                required
                            >
                            <p v-if="form.errors.date_reception" class="text-red-600 font-medium flex items-center gap-2 mt-2">
                                <ExclamationCircleIcon class="h-5 w-5" />
                                {{ form.errors.date_reception }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-lg font-semibold text-gray-800">
                                Responsable Réception *
                            </label>
                            <select
                                v-model="form.responsable_reception_id"
                                class="w-full px-4 py-4 text-lg border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 shadow-sm"
                                :class="{ 'border-red-400': form.errors.responsable_reception_id }"
                                required
                            >
                                <option value="" class="text-gray-400">Sélectionnez un responsable...</option>
                                <option 
                                    v-for="magasinier in magasiniers" 
                                    :key="magasinier.id" 
                                    :value="magasinier.id"
                                    class="py-2"
                                >
                                    {{ magasinier.name }} ({{ magasinier.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.responsable_reception_id" class="text-red-600 font-medium flex items-center gap-2 mt-2">
                                <ExclamationCircleIcon class="h-5 w-5" />
                                {{ form.errors.responsable_reception_id }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Étape 4: Documents -->
                <div class="bg-white shadow-xl rounded-2xl p-8 border-l-8 border-yellow-500 transform transition-all duration-300 hover:shadow-2xl">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            4
                        </div>
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold text-gray-900">Documents (Optionnel)</h2>
                            <p class="text-gray-600 mt-1">Téléchargez les documents associés</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl">
                        <div class="space-y-4">
                            <label class="block text-lg font-semibold text-gray-800">
                                Bon de Livraison
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:border-yellow-400 transition-all duration-200 bg-gray-50 hover:bg-yellow-50">
                                <input
                                    type="file"
                                    @change="form.fichier_bonlivraison = $event.target.files[0]"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="hidden"
                                    id="bonLivraison"
                                >
                                <label for="bonLivraison" class="cursor-pointer">
                                    <DocumentIcon class="h-12 w-12 text-yellow-500 mx-auto mb-3" />
                                    <div class="text-gray-600 font-medium">Cliquez pour télécharger</div>
                                    <div class="text-gray-500 text-sm mt-1">PDF, JPG, JPEG, PNG (max 5MB)</div>
                                </label>
                            </div>
                            <p v-if="form.errors.fichier_bonlivraison" class="text-red-600 font-medium flex items-center gap-2">
                                <ExclamationCircleIcon class="h-5 w-5" />
                                {{ form.errors.fichier_bonlivraison }}
                            </p>
                        </div>
                        <div class="space-y-4">
                            <label class="block text-lg font-semibold text-gray-800">
                                Facture
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:border-yellow-400 transition-all duration-200 bg-gray-50 hover:bg-yellow-50">
                                <input
                                    type="file"
                                    @change="form.fichier_facture = $event.target.files[0]"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="hidden"
                                    id="facture"
                                >
                                <label for="facture" class="cursor-pointer">
                                    <ReceiptRefundIcon class="h-12 w-12 text-yellow-500 mx-auto mb-3" />
                                    <div class="text-gray-600 font-medium">Cliquez pour télécharger</div>
                                    <div class="text-gray-500 text-sm mt-1">PDF, JPG, JPEG, PNG (max 5MB)</div>
                                </label>
                            </div>
                            <p v-if="form.errors.fichier_facture" class="text-red-600 font-medium flex items-center gap-2">
                                <ExclamationCircleIcon class="h-5 w-5" />
                                {{ form.errors.fichier_facture }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Étape 5: Notes -->
                <div class="bg-white shadow-xl rounded-2xl p-8 border-l-8 border-indigo-600 transform transition-all duration-300 hover:shadow-2xl">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                            5
                        </div>
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold text-gray-900">Notes (Optionnel)</h2>
                            <p class="text-gray-600 mt-1">Informations complémentaires</p>
                        </div>
                    </div>
                    
                    <div class="max-w-4xl">
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            class="w-full px-4 py-4 text-lg border-2 border-gray-300 rounded-2xl focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 shadow-sm resize-none"
                            placeholder="Notes supplémentaires sur cette réception (état des marchandises, observations, etc.)..."
                        ></textarea>
                        <p v-if="form.errors.notes" class="text-red-600 font-medium flex items-center gap-2 mt-2">
                            <ExclamationCircleIcon class="h-5 w-5" />
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </div>

                <!-- Résumé et validation -->
                <div v-if="hasQuantitesSaisies" class="bg-gradient-to-r from-green-50 to-emerald-50 shadow-2xl rounded-2xl p-8 border-2 border-green-200 transform transition-all duration-500">
                    <h2 class="text-3xl font-bold text-green-900 mb-8 text-center">Résumé de la Réception</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center p-6 bg-white rounded-2xl border-2 border-green-200 shadow-lg transform hover:scale-105 transition-all duration-300">
                            <div class="text-4xl font-bold text-green-600">{{ nombreArticlesAReceptionner }}</div>
                            <div class="text-green-800 font-semibold text-lg mt-2">Articles</div>
                        </div>
                        <div class="text-center p-6 bg-white rounded-2xl border-2 border-green-200 shadow-lg transform hover:scale-105 transition-all duration-300">
                            <div class="text-4xl font-bold text-green-600">{{ quantiteTotaleReceptionnee }}</div>
                            <div class="text-green-800 font-semibold text-lg mt-2">Quantité totale</div>
                        </div>
                        <div class="text-center p-6 bg-white rounded-2xl border-2 border-green-200 shadow-lg transform hover:scale-105 transition-all duration-300">
                            <div class="text-4xl font-bold" :class="getTypeReceptionCalcule === 'Complet' ? 'text-green-600' : 'text-orange-600'">
                                {{ getTypeReceptionCalcule }}
                            </div>
                            <div class="text-gray-700 font-semibold text-lg mt-2">Type de réception</div>
                        </div>
                        <div class="text-center p-6 bg-white rounded-2xl border-2 border-green-200 shadow-lg transform hover:scale-105 transition-all duration-300">
                            <div class="text-4xl font-bold text-blue-600">{{ getNouveauPourcentage }}%</div>
                            <div class="text-blue-800 font-semibold text-lg mt-2">Progression totale</div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col lg:flex-row justify-between items-center gap-6 pt-8 border-t-2 border-gray-200">
                    <Link 
                        :href="route('bon-receptions.index')"
                        class="w-full lg:w-auto px-8 py-4 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-2xl hover:from-gray-600 hover:to-gray-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-bold text-lg flex items-center justify-center gap-3"
                    >
                        <XMarkIcon class="h-6 w-6" />
                        Annuler
                    </Link>
                    
                    <div class="flex flex-col lg:flex-row gap-4 w-full lg:w-auto">
                        <button
                            type="button"
                            @click="resetForm"
                            class="px-8 py-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-2xl hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-bold text-lg flex items-center justify-center gap-3"
                        >
                            <ArrowPathIcon class="h-6 w-6" />
                            Réinitialiser
                        </button>
                        <button 
                            type="submit"
                            :disabled="!canSubmit || form.processing"
                            class="px-8 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-2xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-bold text-lg flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        >
                            <svg v-if="form.processing" class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <CheckIcon v-else class="h-6 w-6" />
                            {{ form.processing ? 'Création en cours...' : 'Créer le Bon de Réception' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { 
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ArrowLeftIcon,
    InformationCircleIcon,
    CubeIcon,
    ExclamationCircleIcon,
    CheckIcon,
    ArrowPathIcon,
    DocumentIcon,
    ReceiptRefundIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    bonCommandes: Array,
    selectedBonCommande: Object,
    magasiniers: Array,
});

const selectedCommande = ref(props.selectedBonCommande);

const form = useForm({
    bon_commande_id: props.selectedBonCommande?.id || '',
    date_reception: new Date().toISOString().split('T')[0],
    responsable_reception_id: '',
    fichier_bonlivraison: null,
    fichier_facture: null,
    lignes_reception: [],
    notes: '',
});

// Computed properties
const hasQuantitesSaisies = computed(() => {
    return form.lignes_reception.some(ligne => 
        parseFloat(ligne.quantite_receptionnee) > 0
    );
});

const canSubmit = computed(() => {
    return form.bon_commande_id && 
           form.date_reception && 
           form.responsable_reception_id &&
           hasQuantitesSaisies.value;
});

const nombreArticlesAReceptionner = computed(() => {
    return form.lignes_reception.filter(ligne => 
        parseFloat(ligne.quantite_receptionnee) > 0
    ).length;
});

const quantiteTotaleReceptionnee = computed(() => {
    return form.lignes_reception.reduce((total, ligne) => 
        total + parseFloat(ligne.quantite_receptionnee || 0), 0
    );
});

const getTypeReceptionCalcule = computed(() => {
    if (!selectedCommande.value) return 'Partiel';
    
    const tousComplets = selectedCommande.value.articles.every(ligne => {
        const quantiteRecue = parseFloat(
            form.lignes_reception.find(l => l.article_id === ligne.article_id)?.quantite_receptionnee || 0
        );
        const quantiteTotaleRecue = (ligne.quantite_deja_recue || 0) + quantiteRecue;
        return quantiteTotaleRecue >= ligne.quantite_commandee;
    });
    
    return tousComplets ? 'Complet' : 'Partiel';
});

const getNouveauPourcentage = computed(() => {
    if (!selectedCommande.value) return 0;
    
    const quantiteTotaleCommandee = selectedCommande.value.quantite_totale_commandee;
    const quantiteTotaleRecueAvant = selectedCommande.value.quantite_totale_recue;
    const quantiteCetteReception = quantiteTotaleReceptionnee.value;
    
    const nouveauTotalRecu = quantiteTotaleRecueAvant + quantiteCetteReception;
    
    return quantiteTotaleCommandee > 0 ? 
        Math.min(Math.round((nouveauTotalRecu / quantiteTotaleCommandee) * 100), 100) : 0;
});

// Méthodes
const loadCommandeDetails = () => {
    if (!form.bon_commande_id) {
        selectedCommande.value = null;
        form.lignes_reception = [];
        return;
    }

    const commande = props.bonCommandes.find(c => c.id == form.bon_commande_id);
    if (commande) {
        selectedCommande.value = { ...commande };
        
        // Initialiser les lignes de réception
        form.lignes_reception = selectedCommande.value.articles.map(ligne => ({
            article_id: ligne.article_id,
            quantite_receptionnee: 0,
        }));
    }
};

const validateQuantite = (ligne, index) => {
    const quantiteReceptionnee = parseFloat(form.lignes_reception[index].quantite_receptionnee) || 0;
    const resteARecevoir = ligne.reste_a_recevoir || 0;
    
    if (quantiteReceptionnee > resteARecevoir) {
        form.lignes_reception[index].quantite_receptionnee = resteARecevoir;
    }
    
    if (quantiteReceptionnee < 0) {
        form.lignes_reception[index].quantite_receptionnee = 0;
    }
};

const prefillQuantite = (ligne, index) => {
    form.lignes_reception[index].quantite_receptionnee = ligne.reste_a_recevoir;
};

const prefillAllWithMax = () => {
    form.lignes_reception.forEach((ligne, index) => {
        const ligneCommande = selectedCommande.value.articles[index];
        if (ligneCommande && ligneCommande.reste_a_recevoir > 0) {
            ligne.quantite_receptionnee = ligneCommande.reste_a_recevoir;
        }
    });
};

const resetAllQuantities = () => {
    form.lignes_reception.forEach(ligne => {
        ligne.quantite_receptionnee = 0;
    });
};

const resetForm = () => {
    form.reset();
    selectedCommande.value = null;
};

const submitForm = () => {
    // Filtrer seulement les lignes avec des quantités > 0
    const lignesAEnvoyer = form.lignes_reception
        .filter(ligne => parseFloat(ligne.quantite_receptionnee) > 0)
        .map(ligne => ({
            article_id: ligne.article_id,
            quantite_receptionnee: parseFloat(ligne.quantite_receptionnee)
        }));

    form.transform((data) => ({
        ...data,
        lignes_reception: lignesAEnvoyer
    })).post(route('bon-receptions.store'), {
        onSuccess: () => {
            // Redirection gérée par le contrôleur
        },
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

// Initialiser si une commande est présélectionnée
onMounted(() => {
    if (props.selectedBonCommande) {
        loadCommandeDetails();
    }
});
</script>

<style scoped>
/* Animations personnalisées */
.hover-lift:hover {
    transform: translateY(-2px);
}

/* Style pour les inputs avec valeur */
input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Transition douce pour tous les éléments */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
