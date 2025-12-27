<script setup>
import { ClipboardDocumentListIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    decomptes: Array
});
</script>
<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex gap-2 items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <ClipboardDocumentListIcon class="w-5 h-5 text-blue-600" />
                Les Decomptes
            </h3>
            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 text-xs font-semibold rounded-full">
                {{ decomptes.length || 0 }}
            </span>
        </div>

        <div v-if="decomptes?.length" class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3 font-medium">ID</th>
                        <th class="px-6 py-3 font-medium">Date</th>
                        <th class="px-6 py-3 font-medium">Type</th>
                        <th class="px-6 py-3 font-medium">Travaux terminés </th>
                        <th class="px-6 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="decompte in decomptes" :key="decompte.id"
                        class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-medium text-gray-800">#{{ decompte.id }}</td>
                        <td class="px-6 py-3">{{ decompte.date }}</td>
                        <td class="px-6 py-3">{{ decompte.final ? 'Définitif' : 'Provisoire' }}</td>
                        <td class="px-6 py-3">{{ decompte.total_termine }}</td>

                        <td class="px-6 py-3">
                            <a
                                class="text-purple-600 hover:text-purple-900 p-1 cursor-pointer"
                                :href="route('decompte.download-pdf', decompte.id )"
                                title="Télécharger PDF"
                                target="_blank"
                            >
                            <DocumentTextIcon class="h-5 w-5" />
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-12 text-gray-500">
            <ClipboardDocumentListIcon class="w-10 h-10 mb-2 text-gray-300" />
            <p>Aucun Decompte trouvé pour cette marché.</p>
        </div>
    </div>
</template>
