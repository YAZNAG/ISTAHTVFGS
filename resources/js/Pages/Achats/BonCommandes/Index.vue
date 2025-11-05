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

                    <ModalLink
                        as="button"
                        href="#export-modal"
                        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-400 flex items-center justify-center gap-2 "
                    >
                        <DocumentArrowDownIcon class="h-5 w-5" />
                        Exporter
                    </ModalLink>
                    <CreateExportModal name="export-modal" />

                    <button
                        @click="openMarcheForm()"
                        class="bg-blue-600 text-white  px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center gap-2"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Nouveau Marché
                    </button>
                    
                </div>
            </div>

            <!-- Stats -->
            <section class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border">
                    <div class="text-gray-500">Marchés Créés</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-gray-800">{{ stats?.cree || 0 }}</div>
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
                        <div class="text-3xl font-bold text-green-700">{{ (stats?.livre_completement || 0) + (stats?.livre_partiellement || 0) }}</div>
                        <CheckCircleIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
                <div class="bg-white p-5 rounded-lg shadow-sm border">
                    <div class="text-gray-500">Montant Total</div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="text-3xl font-bold text-indigo-700">{{ formatCurrency(stats?.montant_total || 0) }}</div>
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
                        <input v-model="filters.date_limite" type="date" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Référence</label>
                        <input v-model="filters.reference" type="text" placeholder="Rechercher par référence" 
                            class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 mr-2"
                    >
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date mise ligne</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date limite</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
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
                                    <div class="text-sm text-gray-900">{{ formatDate(marche.date_limite_reception) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ marche.fournisseur?.nom || 'Non attribué' }}</div>
                                </td>
                              <td class="px-6 py-4 whitespace-nowrap">
    <div class="text-sm font-medium" :class="getMontantTtcClass(marche)">
        {{ getMontantTtcDisplay(marche) }}
    </div>
</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getBonCommandeStatutInfo(marche.statut).color" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ getBonCommandeStatutInfo(marche.statut).label }}
                                    </span>
                                </td>
                               <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    <div class="flex space-x-2">
        <Link
            :href="route('bon-commandes.show', marche.id)"
            class="text-blue-600 hover:text-blue-900 p-1"
            title="Voir détails"
        >
            <EyeIcon class="h-4 w-4" />
        </Link>
        <button
            @click="openUpdateStatutForm(marche)"
            class="text-green-600 hover:text-green-900 p-1"
            title="Modifier statut"
            v-if="marche.statut === 'cree'"
        >
            <ArrowPathIcon class="h-4 w-4" />
        </button>
        <!-- Bouton PDF - N'apparaît que pour les statuts différents de "cree" et "annule" -->
        <a
            v-if="marche.statut !== 'cree' && marche.statut !== 'annule'"
            :href="route('bon-commandes.pdf', marche.id)"
            target="_blank"
            class="text-purple-600 hover:text-purple-900 p-1"
            title="Télécharger PDF"
        >
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
                            <button 
                                @click="openMarcheForm()"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                <PlusIcon class="h-4 w-4 mr-2" />
                                Nouveau marché
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="marches.links && marches.links.length > 1" class="bg-white px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Affichage de {{ marches.from }} à {{ marches.to }} sur {{ marches.total }} résultats
                        </div>
                        <div class="flex space-x-2">
                            <template v-for="link in marches.links" :key="link.label">
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

        <!-- Modal Création Bon de Commande - SANS PRIX -->
        <div v-if="showMarcheForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-6 border-b">
                    <h3 class="text-lg font-semibold">Nouveau marché</h3>
                    <button @click="closeAllForms" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                
                <form @submit.prevent="submitMarcheForm" class="p-6 space-y-6" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Colonne gauche -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Référence *</label>
                                <input v-model="marcheForm.reference" type="text" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                    placeholder="MR-2024-001">
                                <InputError :message="marcheForm.errors.reference" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Objet *</label>
                                <input v-model="marcheForm.objet" type="text" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                    placeholder="Objet de la commande">
                                <InputError :message="marcheForm.errors.objet" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="marcheForm.description" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                    placeholder="Description détaillée de la commande..."></textarea>
                                <InputError :message="marcheForm.errors.description" />
                            </div>

                            <!-- Dans le template, modifiez le gestionnaire de fichiers -->
<div>
    <label class="block text-sm font-medium text-gray-700">Pièces jointes</label>
    <input 
        type="file" 
        multiple 
        @change="handleFileUpload" 
        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
    >
    <p class="text-xs text-gray-500 mt-1">
        Formats acceptés: PDF, Word, Excel, JPG, PNG (max 10MB par fichier)
    </p>
    <div v-if="marcheForm.pieces_jointes.length > 0" class="mt-2">
        <p class="text-sm text-gray-600">Fichiers sélectionnés:</p>
        <ul class="text-xs text-gray-500">
            <li v-for="(file, index) in marcheForm.pieces_jointes" :key="index" class="flex justify-between items-center">
                {{ file.name }} ({{ formatFileSize(file.size) }})
                <button 
                    type="button" 
                    @click="removeFile(index)"
                    class="text-red-600 hover:text-red-800 ml-2"
                >
                    ×
                </button>
            </li>
        </ul>
    </div>
</div>
                        </div>
                        
                        <!-- Colonne droite -->
                        <div class="space-y-4">
                            <!-- <div>
                                <label class="block text-sm font-medium text-gray-700">Catégorie principale *</label>
                                <select v-model="marcheForm.categorie_principale_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Sélectionnez...</option>
                                    <option v-for="catPrinc in categoriesPrincipales" :key="catPrinc.id" :value="catPrinc.id">
                                        {{ catPrinc.nom }}
                                    </option>
                                </select>
                                <InputError :message="marcheForm.errors.categorie_principale_id" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nature de prestation *</label>
                                <select v-model="marcheForm.nature_prestation_id" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Sélectionnez...</option>
                                    <option v-for="nature in naturesPrestation" :key="nature.id" :value="nature.id">
                                        {{ nature.nom }}
                                    </option>
                                </select>
                                <InputError :message="marcheForm.errors.nature_prestation_id" />
                            </div> -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date debut</label>
                                <input v-model="marcheForm.date_debut" type="date" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <InputError :message="marcheForm.errors.date_debut" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date fin</label>
                                <input v-model="marcheForm.date_fin" type="date" required
                                    :min="marcheForm.date_debut"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <InputError :message="marcheForm.errors.date_fin" />
                            </div>
                            

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date mise en ligne *</label>
                                <input v-model="marcheForm.date_mise_ligne" type="date" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <InputError :message="marcheForm.errors.date_mise_ligne" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date limite réception devis *</label>
                                <input v-model="marcheForm.date_limite_reception" type="date" required
                                    :min="marcheForm.date_mise_ligne"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <InputError :message="marcheForm.errors.date_limite_reception" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Catégorie *</label>
                                <select v-model="marcheForm.categorie_id" required @change="onCategorieChange"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Sélectionnez...</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.nom }}
                                    </option>
                                </select>
                                <InputError :message="marcheForm.errors.categorie_id" />
                            </div>
                        </div>
                    </div>

                    <!-- Articles - SANS CHAMP PRIX -->
                    <div class="border-t pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-medium">Articles commandés</h4>
                            <div class="text-sm text-gray-500">
                                Les prix seront saisis lors de la mise à jour du statut
                            </div>
                            <div class="flex space-x-2">
                                <!-- <select @change="onCategorieChange" class="border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="">Ajouter par catégorie...</option>
                                    <option v-for="(articles, categorieId) in articlesGroupes" :key="categorieId" 
                                            :value="categorieId">
                                        {{ getCategorieName(categorieId) }} ({{ articles?.length || 0 }} articles)
                                    </option>
                                </select> -->
                                <button type="button" @click="addRow" :disabled="!marcheForm.categorie_id" class="bg-green-600 text-white px-3 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    + Article manuel
                                </button>
                            </div>
                        </div>

                        <div
                        v-for="(row, index) in marcheForm.articles"
                        :key="index"
                        class="relative mb-4 border p-4 rounded-lg bg-gray-50 flex gap-2"
                        >
                        <!-- Article Search Input -->
                         <div class="relative">
                            <label class="block text-sm font-medium mb-1">Article</label>
                             <input
                            type="text"
                            v-model="row.search"
                            placeholder="Rechercher un article..."
                            @focus="row.dropdownOpen = true"
                            @blur="closeIdle(index)"
                            class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />

                       
                            <!-- Dropdown -->
                            <ul
                                v-if="row.dropdownOpen && filteredArticles(row.search).length"
                                class="absolute w-full z-10 bg-white border border-gray-300 rounded-md mt-1 shadow-lg"
                                style="max-height: 200px; overflow-y: auto;"
                            >
                                <li
                                v-for="article in filteredArticles(row.search)"
                                :key="article.id"
                                @mousedown.prevent="onArticleSelect(index, article)"
                                class="px-3 py-2 hover:bg-indigo-200 cursor-pointer"
                                >
                                {{ article.designation }}
                                </li>
                            </ul>
                        </div>
                        <!-- Quantité -->
                        <div class="">
                            <label class="block text-sm font-medium mb-1">Quantité {{ row.unite_mesure ?`(${row.unite_mesure})`: '' }}</label>
                            <input
                            type="number"
                            v-model.number="row.quantite_commandee"
                            min="1"
                            class="w-full border-gray-300 rounded-lg p-2"
                            />
                        </div>

                        <!-- Taux TVA -->
                        <div class="min-w-[160px]">
                            <label class="block text-sm font-medium text-gray-700">TVA</label>
                            <select v-model="row.taux_tva" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <option v-for="taux in tauxTVA" :key="taux" :value="taux">
                                    {{ getTvaLabel(taux) }}
                                </option>
                            </select>
                        </div>

                        <button
                            type="button"
                            class="text-white bg-red-500 text-sm px-3 py-2 rounded self-center ms-auto"
                            @click="removeRow(index)"
                        >
                            Supprimer
                        </button>
                        </div>

                        <div v-if="marcheForm.articles.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                            <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="text-gray-500 mt-2">Aucun article sélectionné</p>
                            <p class="text-gray-400 text-sm mt-1">Ajoutez des articles pour créer le marché</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <button type="button" @click="closeAllForms"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            :disabled="marcheForm.processing || marcheForm.articles.length === 0">
                            <span v-if="marcheForm.processing">Création en cours...</span>
                            <span v-else>Créer le marché</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

      <!-- Modal Mise à jour Statut avec Fournisseur et Prix -->
<div v-if="showUpdateStatutForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-lg font-semibold">
                Mettre à jour le statut - {{ selectedMarche?.reference }}
            </h3>
            <button @click="closeAllForms" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="h-6 w-6" />
            </button>
        </div>
        
        <form @submit.prevent="submitUpdateStatut" class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nouveau statut *</label>
                <select v-model="statutForm.statut" required @change="onStatutChange"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="attente_livraison">En attente livraison</option>
    
                    <option value="annule">Annuler</option>
                </select>
            </div>

            <!-- Fournisseur et Prix (visible seulement pour les statuts de livraison) -->
         <!-- Dans la section Articles du formulaire d'édition -->
<!-- Section Fournisseur simplifiée -->
<div v-if="showFournisseurAndPrixSection" class="space-y-6 border-t border-gray-200 pt-6">
    
    <!-- Sélection du fournisseur -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-semibold text-blue-900">Sélection du fournisseur</h4>
            <span class="text-sm text-blue-600 bg-blue-100 px-3 py-1 rounded-full">Obligatoire</span>
        </div>
        
        <div class="space-y-4">
            <!-- Sélection fournisseur existant -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Choisir un fournisseur *
                </label>
                <select 
                    v-model="statutForm.fournisseur_id" 
                    required
                    class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    @change="onFournisseurChange"
                >
                    <option value="">Sélectionnez un fournisseur...</option>
                    <option 
                        v-for="fournisseur in fournisseurs" 
                        :key="fournisseur.id" 
                        :value="fournisseur.id"
                        class="py-2"
                    >
                        {{ fournisseur.raison_sociale || fournisseur.nom }}
                        <template v-if="fournisseur.contact"> - {{ fournisseur.contact }}</template>
                        <template v-if="fournisseur.ville"> ({{ fournisseur.ville }})</template>
                    </option>
                </select>
                
                <p class="text-xs text-gray-500 mt-2">
                    Sélectionnez le fournisseur qui livrera cette commande
                </p>
            </div>

            <!-- Informations du fournisseur sélectionné -->
            <div v-if="selectedFournisseur" class="p-4 bg-white border border-green-200 rounded-lg animate-fade-in">
                <h5 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                    <CheckCircleIcon class="h-5 w-5 text-green-500" />
                    Fournisseur sélectionné
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium">Nom :</span>
                            <span class="text-green-600">{{ selectedFournisseur.raison_sociale || selectedFournisseur.nom }}</span>
                        </div>
                        <div v-if="selectedFournisseur.contact" class="flex justify-between">
                            <span class="font-medium">Contact :</span>
                            <span>{{ selectedFournisseur.contact }}</span>
                        </div>
                        <div v-if="selectedFournisseur.telephone" class="flex justify-between">
                            <span class="font-medium">Téléphone :</span>
                            <span class="text-blue-600">{{ selectedFournisseur.telephone }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div v-if="selectedFournisseur.email" class="flex justify-between">
                            <span class="font-medium">Email :</span>
                            <span class="text-blue-600">{{ selectedFournisseur.email }}</span>
                        </div>
                        <div v-if="selectedFournisseur.ville" class="flex justify-between">
                            <span class="font-medium">Ville :</span>
                            <span>{{ selectedFournisseur.ville }}</span>
                        </div>
                        <div v-if="selectedFournisseur.ice" class="flex justify-between">
                            <span class="font-medium">ICE :</span>
                            <span class="font-mono">{{ selectedFournisseur.ice }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message si aucun fournisseur sélectionné -->
            <div v-else-if="statutForm.fournisseur_id === ''" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center gap-3">
                    <ExclamationTriangleIcon class="h-5 w-5 text-yellow-500" />
                    <p class="text-sm text-yellow-700">
                        Veuillez sélectionner un fournisseur pour continuer
                    </p>
                </div>
            </div>
        </div>

        <!-- Message d'erreur de validation -->
        <div v-if="statutForm.errors.fournisseur_id" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2">
                <ExclamationTriangleIcon class="h-4 w-4 text-red-500" />
                <p class="text-sm text-red-700">{{ statutForm.errors.fournisseur_id }}</p>
            </div>
        </div>
    </div>

    <!-- Section Articles -->
    <div class="border border-gray-200 rounded-xl p-6 bg-white">
        <h4 class="text-xl font-semibold text-gray-900 mb-6">Prix unitaires HT des articles</h4>
        
        <div v-if="statutForm.articles.length === 0" class="text-center py-8 bg-gray-50 rounded-lg">
            <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
            <p class="text-gray-500 mt-2">Aucun article dans ce marché</p>
        </div>
        
        <!-- Vos articles ici... -->
        <div v-for="(articleForm, index) in statutForm.articles" :key="articleForm.id" 
             class="border border-gray-200 rounded-lg p-6 mb-6 bg-gray-50">
            
            <!-- En-tête de l'article -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 pb-6 border-b border-gray-200">
                <div>
                    <h4 class="font-semibold text-gray-900 text-lg">{{ articleForm.designation }}</h4>
                    <div class="text-sm text-gray-600 mt-3 space-y-1">
                        <div><strong class="text-gray-700">Référence:</strong> {{ articleForm.reference }}</div>
                        <div><strong class="text-gray-700">Quantité commandée:</strong> {{ articleForm.quantite_commandee }} {{ articleForm.unite_mesure }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                    <div><strong class="text-gray-700">TVA appliquée:</strong> {{ articleForm.taux_tva }}%</div>
                </div>
            </div>

            <!-- Prix et calculs -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Prix unitaire HT (DH) *
                    </label>
                    <input v-model="articleForm.prix_unitaire_ht" 
                           type="number" step="0.01" min="0" required
                           @input="calculateArticleMontants(index)"
                           class="w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0.00">
                </div>
                
                <div class="text-sm bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="font-semibold text-gray-700 mb-2">Détails TVA</div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Taux:</span>
                            <span class="font-bold text-green-600">{{ articleForm.taux_tva }}%</span>
                        </div>
                    </div>
                </div>
                
                <div class="text-sm bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="font-semibold text-gray-700 mb-2">Calculs automatiques</div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Montant HT:</span>
                            <strong class="text-blue-600">{{ formatCurrency(articleForm.montant_ht || 0) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Montant TVA:</span>
                            <strong class="text-blue-600">{{ formatCurrency(articleForm.montant_tva || 0) }}</strong>
                        </div>
                        <div class="flex justify-between border-t border-blue-200 pt-2 mt-2">
                            <span class="font-semibold">Montant TTC:</span>
                            <strong class="text-blue-700">{{ formatCurrency(articleForm.montant_ttc || 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            <!-- Confirmation d'annulation améliorée -->
            <div v-if="statutForm.statut === 'annule'" class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-6 w-6 text-red-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-red-800">
                            Confirmation d'annulation
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p class="font-semibold">Attention : Cette action est irréversible !</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Le marché sera marqué comme annulé</li>
                                <li>Le fournisseur attribué sera retiré</li>
                                <li>Les prix saisis seront réinitialisés</li>
                                <li>Cette action ne peut pas être annulée</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700">Raison de l'annulation *</label>
                    <textarea v-model="statutForm.raison" required rows="4"
                        class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm p-3"
                        placeholder="Veuillez expliquer en détail la raison de l'annulation de ce marché..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimum 20 caractères</p>
                </div>

                <div class="mt-4 flex items-center">
                    <input v-model="confirmationAnnulation" type="checkbox" 
                        class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <label class="ml-2 block text-sm text-red-700 font-medium">
                        Je confirme que je souhaite annuler ce marché
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <button type="button" @click="closeAllForms"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    :disabled="statutForm.processing || !isStatutFormValid">
                    <span v-if="statutForm.processing">Mise à jour...</span>
                    <span v-else>{{ getSubmitButtonText }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
        <!-- Toast -->
        <transition name="fade">
            <div v-if="toast.show" class="fixed bottom-6 right-6 bg-white px-4 py-3 rounded-lg shadow-lg border border-green-200 text-green-800">
                {{ toast.message }}
            </div>
        </transition>
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
import Dump from '@/Components/Dump.vue';
import { getBonCommandeStatutInfo } from '@/Utils/labels';

// Props avec valeurs par défaut pour éviter les erreurs
const props = defineProps({
    marches: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    categoriesPrincipales: {
        type: Array,
        default: () => []
    },
    naturesPrestation: {
        type: Array,
        default: () => []
    },
    articles: {
        type: Array,
        default: () => []
    },
    articlesGroupes: {
        type: Object,
        default: () => ({})
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

// Toast
const toast = ref({
    show: false,
    message: ''
});

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

const getCategorieName = (categorieId) => {
    const categorie = props.categoriesPrincipales.find(cat => cat.id == categorieId);
    return categorie ? categorie.nom : 'Catégorie inconnue';
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


const filteredArticles = (search) => {
  if (!search) return []

  return props.articles
    .filter(a => a.categorie_id == marcheForm.categorie_id) 
    .filter((a) =>
        a.designation.toLowerCase().includes(search.toLowerCase())
    )
}

// Select article
function onArticleSelect(index, article) {
  const row = marcheForm.articles[index]
  row.article_id = article.id
  row.search = article.designation
  row.dropdownOpen = false
  row.unite_mesure = article.unite_mesure
}



function addRow() {
  marcheForm.articles.push({
    article_id: null,
    search: '',
    quantite_commandee: 1,
    taux_tva: 0,
    dropdownOpen: false,
  })
}

function removeRow(index) {
  marcheForm.articles.splice(index, 1)
}

function closeIdle(index) {
  setTimeout(() => (marcheForm.articles[index].dropdownOpen = false), 150)
}

const removeArticle = (index) => {
    marcheForm.articles.splice(index, 1);
};


const onArticleChange = (index) => {
    const selectedArticle = getArticle(marcheForm.articles[index].article_id);
    if (selectedArticle && selectedArticle.taux_tva) {
        marcheForm.articles[index].taux_tva = selectedArticle.taux_tva;
    }

    formRows.value[index].search = selectedArticle.designation
    formRows.value[index].dropdownOpen = false
};

const onStatutChange = () => {
    if (statutForm.statut === 'annule') {
        statutForm.fournisseur_id = '';
        statutForm.articles = statutForm.articles.map(article => ({
            ...article,
            prix_unitaire_ht: 0
        }));
        confirmationAnnulation.value = false;
    }
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
        
        newFournisseurForm.logo = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            newFournisseurForm.logoPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
const calculateArticleMontants = (index) => {
    const articleForm = statutForm.articles[index];
    
    console.log(`Calcul pour l'article ${index}:`, {
        prixUnitaireHT: articleForm.prix_unitaire_ht,
        quantite: articleForm.quantite_commandee,
        tauxTVA: articleForm.taux_tva
    });
    
    if (articleForm.prix_unitaire_ht && articleForm.quantite_commandee) {
        const tauxTVA = articleForm.taux_tva || 20;
        const quantite = articleForm.quantite_commandee || 1;
        const prixUnitaireHT = parseFloat(articleForm.prix_unitaire_ht) || 0;
        
        articleForm.montant_ht = prixUnitaireHT * quantite;
        articleForm.montant_tva = articleForm.montant_ht * (tauxTVA / 100);
        articleForm.montant_ttc = articleForm.montant_ht + articleForm.montant_tva;
        
        console.log(`Résultats calcul ${index}:`, {
            montantHT: articleForm.montant_ht,
            montantTVA: articleForm.montant_tva,
            montantTTC: articleForm.montant_ttc
        });
    } else {
        articleForm.montant_ht = 0;
        articleForm.montant_tva = 0;
        articleForm.montant_ttc = 0;
    }
    
    // Forcer la mise à jour du reactive
    statutForm.articles[index] = { ...articleForm };
};
const submitMarcheForm = () => {
    const payload = marcheForm.articles.map(({ article_id, quantite_commandee, taux_tva }) => ({
        article_id,
        quantite_commandee,
        taux_tva,
    }));

    marcheForm.transform((data) => ({...data, articles: payload})).post(route('bon-commandes.store'), {
        onSuccess: () => {
            closeAllForms();
            showToast('Marché créé avec succès', 3000);
        },
        onError: (errors) => {
            console.error('Erreurs de validation:', errors);
            showToast('Erreur lors de la création du marché', 3000);
        }
    });
};

const submitUpdateStatut = () => {
    if (!selectedMarche.value) {
        console.error('Aucun marché sélectionné');
        return;
    }
    
    console.log('Début submitUpdateStatut', {
        marcheId: selectedMarche.value.id,
        statut: statutForm.statut,
        raison: statutForm.raison,
        confirmationAnnulation: confirmationAnnulation.value
    });

    if (statutForm.statut === 'annule') {
        if (!confirmationAnnulation.value) {
            showToast('Veuillez confirmer l\'annulation', 3000);
            return;
        }
        
        if (!statutForm.raison || statutForm.raison.length < 20) {
            showToast('La raison doit contenir au moins 20 caractères', 3000);
            return;
        }
    }

    statutForm.post(route('bon-commandes.statut', selectedMarche.value.id), {
        onSuccess: (response) => {
            console.log('Succès de la requête', response);
            closeAllForms();
            // Message simplifié sans mention du PDF
            showToast('Statut mis à jour avec succès', 3000);
        },
        onError: (errors) => {
            console.error('Erreurs de validation:', errors);
            let errorMessage = 'Erreur lors de la mise à jour du statut';
            if (errors.statut) {
                errorMessage = errors.statut;
            } else if (errors.raison) {
                errorMessage = errors.raison;
            } else if (errors.error) {
                errorMessage = errors.error;
            }
            showToast(errorMessage, 5000);
        }
    });
};

// Méthode pour afficher le montant TTC
const getMontantTtcDisplay = (marche) => {
    const montant = marche.total_ttc || 0;
    
    // Si le statut est "créé" ou "annulé", les prix ne sont pas encore saisis
    if (marche.statut === 'cree' || marche.statut === 'annule') {
        return 'Non défini';
    }
    
    // Si le montant est 0 mais que le statut devrait avoir des prix
    if (montant === 0 && ['attente_livraison', 'livre_completement', 'livre_partiellement'].includes(marche.statut)) {
        return 'En attente';
    }
    
    // Sinon afficher le montant formaté
    return formatCurrency(montant);
};

// Méthode pour les classes CSS du montant TTC
const getMontantTtcClass = (marche) => {
    const montant = marche.total_ttc || 0;
    
    if (marche.statut === 'cree' || marche.statut === 'annule') {
        return 'text-gray-400';
    }
    
    if (montant === 0 && ['attente_livraison', 'livre_completement', 'livre_partiellement'].includes(marche.statut)) {
        return 'text-yellow-600';
    }
    
    return 'text-green-600 font-semibold';
};
const createFournisseur = async () => {
    const formData = new FormData();
    
    Object.keys(newFournisseurForm).forEach(key => {
        if (key !== 'logoPreview' && newFournisseurForm[key] !== null) {
            formData.append(key, newFournisseurForm[key]);
        }
    });
    
    try {
        const response = await axios.post(route('bon-commandes.fournisseurs.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        router.reload({ only: ['fournisseurs'] });
        
        statutForm.fournisseur_id = response.data.id;
        showNewFournisseurForm.value = false;
        newFournisseurForm.reset();
        newFournisseurForm.logoPreview = null;
        
        showToast('Fournisseur créé avec succès', 3000);
    } catch (error) {
        console.error('Erreur création fournisseur:', error);
        showToast('Erreur lors de la création du fournisseur', 3000);
    }
};

const viewOrDownloadPdf = (marche) => {
    if (marche.statut === 'attente_livraison') {
        // Voir le PDF dans un nouvel onglet
        window.open(route('bon-commandes.pdf', marche.id), '_blank');
    } else {
        // Télécharger le PDF
        const link = document.createElement('a');
        link.href = route('bon-commandes.pdf', marche.id);
        link.download = `bon-commande-${marche.reference}.pdf`;
        link.click();
    }
};

// AJOUTEZ ICI LA NOUVELLE MÉTHODE
const downloadPdf = (marche) => {
    // Utilisez la route correcte
    const url = route('bon-commandes.pdf', { marche: marche.id });
    window.open(url, '_blank');
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

// Méthodes pour gérer les fichiers
const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    const validFiles = files.filter(file => {
        // Validation de la taille (max 10MB par fichier)
        if (file.size > 10 * 1024 * 1024) {
            showToast(`Le fichier ${file.name} est trop volumineux (max 10MB)`, 5000);
            return false;
        }
        
        // Validation du type
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/jpg',
            'image/png'
        ];
        
        if (!allowedTypes.includes(file.type)) {
            showToast(`Type de fichier non supporté: ${file.name}`, 5000);
            return false;
        }
        
        return true;
    });
    
    marcheForm.pieces_jointes = [...marcheForm.pieces_jointes, ...validFiles];
    event.target.value = ''; // Reset l'input
};

const removeFile = (index) => {
    marcheForm.pieces_jointes.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
// Initialisation
onMounted(() => {
    console.log('Component mounted');
});


const formRows = ref([]);

function onCategorieChange(event) {
    console.log(event.target.value);
    
    marcheForm.articles = [];
    formRows.value = [];
}

</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
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