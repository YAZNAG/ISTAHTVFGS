<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    PencilIcon,
    TrashIcon,
    PlusIcon,
    ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import { ModalLink } from '@inertiaui/modal-vue';
import { Head } from '@inertiajs/vue3';
import CreateCategorieModal from './CreateCategorieModal.vue';

defineProps({
    categories: Array,   // plain array, no pagination
});

function openDeleteConfirm(categorie) {
    if (confirm(`Supprimer la catégorie « ${categorie.nom} » ?`)) {
        router.delete(route('categories.destroy', categorie.id));
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Catégories" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold mb-2">Catégories</h1>
                        <p class="text-indigo-100 text-lg opacity-90">Gérez et suivez toutes vos catégories</p>
                    </div>
                    <ModalLink href="#create-categorie-modal"
                        class="bg-white text-indigo-600 px-6 py-3 rounded-xl hover:bg-indigo-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl">
                        <PlusIcon class="h-5 w-5" />
                        Nouvelle catégorie
                    </ModalLink>
                </div>
            </div>

            <!-- Cards grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="categorie in categories" :key="categorie.id"
                     class="border rounded-lg p-4 bg-white hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 capitalize">{{ categorie.nom }}</h4>
                            <p class="text-sm text-gray-500">{{ categorie.code }}</p>
                            <p class="text-xs text-gray-400">
                            </p>
                            <p v-if="categorie.description" class="text-sm text-gray-600 mt-2">
                                {{ categorie.description }}
                            </p>
                        </div>

                        <div class="flex space-x-1 ml-4">
                            <ModalLink :href="route('categories.edit', categorie.id)"
                                       class="text-blue-600 hover:text-blue-900 p-1"
                                       title="Modifier">
                                <PencilIcon class="h-4 w-4" />
                            </ModalLink>
                            <!-- <button @click="openDeleteConfirm(categorie)"
                                    class="text-red-600 hover:text-red-900 p-1"
                                    title="Supprimer">
                                <TrashIcon class="h-4 w-4" />
                            </button> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="categories.length === 0" class="text-center py-12">
                <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune catégorie trouvée</h3>
                <p class="mt-1 text-sm text-gray-500">Commencez par créer votre première catégorie.</p>
                <div class="mt-6">
                    <ModalLink :href="route('categories.create')"
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Nouvelle catégorie
                    </ModalLink>
                </div>
            </div>
        </div>

        <CreateCategorieModal />
    </AuthenticatedLayout>
</template>