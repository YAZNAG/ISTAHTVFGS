<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

// defineOptions({
//     layout: AuthenticatedLayout,
// });

const props = defineProps({
    article: Object,
});
</script>

<template>

    <Head :title="`Article - ${article.reference}`" />
    <AuthenticatedLayout>
        <div class="p-8 space-y-8">
            <!-- Header -->
            <div class="">
                <h1 class="text-3xl font-semibold text-gray-900 tracking-tight">
                    Détails de l’article
                </h1>
                <Link :href="route('articles.index')"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors mt-4">
                <ArrowLeftIcon class="h-5 w-5 mr-2" />
                Retour à la liste
                </Link>
            </div>

            <!-- Main Card -->
            <div class="">
                <!-- Title -->
                <div class="border-b border-gray-100 pb-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ article.designation }}
                    </h2>
                    <p class="text-gray-500 mt-1">
                        Référence : <span class="font-medium text-gray-800">{{ article.reference }}</span>
                    </p>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Unité de mesure</h3>
                        <p class="text-base text-gray-900 font-medium">{{ article.unite_mesure }}</p>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Catégorie</h3>
                        <p class="text-base text-gray-900 font-medium">{{ article.categorie.nom }}</p>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Seuil Maximal</h3>
                        <p class="text-base text-gray-900 font-medium">{{ article.seuil_maximal }}</p>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Statut</h3>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium"
                            :class="article.est_actif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                            <span class="h-2 w-2 rounded-full mr-2"
                                :class="article.est_actif ? 'bg-green-500' : 'bg-red-500'"></span>
                            {{ article.est_actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Créé le</h3>
                        <p class="text-base text-gray-900 font-medium">
                            {{ new Date(article.created_at).toLocaleDateString('fr-FR') }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Modifié le</h3>
                        <p class="text-base text-gray-900 font-medium">
                            {{ new Date(article.updated_at).toLocaleDateString('fr-FR') }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-10">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Description</h3>
                    <div class="p-4 bg-gray-50 border border-gray-100 rounded-xl text-gray-700 leading-relaxed"
                        v-if="article.description">
                        {{ article.description }}
                    </div>
                    <p v-else class="text-gray-400 italic">Aucune description disponible.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
