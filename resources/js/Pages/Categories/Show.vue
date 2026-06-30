<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
  categorie: {
    type: Object,
    required: true,
  },
  articles: {
    type: Object,
    required: true,
  },
})

function formatNumber(value, fraction = 0) {
  return Number(value || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: fraction,
    maximumFractionDigits: fraction,
  })
}

function statusClasses(statut) {
  if (statut?.type === 'danger') return 'bg-red-50 text-istaht-red ring-red-100'
  if (statut?.type === 'warning') return 'bg-amber-50 text-istaht-amber ring-amber-100'
  return 'bg-green-50 text-istaht-green ring-green-100'
}
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="categorie.nom" />

    <section class="space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <Link href="/categories" class="ui-button ui-button-ghost">
          Retour aux categories
        </Link>
        <ModalLink :href="route('categories.edit', categorie.id)" class="ui-button ui-button-primary">
          Modifier
        </ModalLink>
      </div>

      <div
        class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft"
        :style="{ borderTop: `4px solid ${categorie.couleur || '#155e9f'}` }"
      >
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
          <div class="flex items-start gap-4">
            <span
              class="block h-14 w-14 rounded-lg border border-slate-200"
              :style="{ backgroundColor: categorie.couleur || '#155e9f' }"
            ></span>
            <div>
              <p class="text-sm font-bold uppercase text-istaht-cyan">{{ categorie.code }}</p>
              <h2 class="mt-1 text-2xl font-bold text-istaht-navy md:text-3xl">{{ categorie.nom }}</h2>
              <p class="mt-2 text-sm text-slate-500">Categorie utilisee pour classer les articles.</p>
            </div>
          </div>

          <div class="flex flex-wrap gap-2">
            <span
              class="ui-badge ring-1"
              :class="categorie.est_actif ? 'bg-green-50 text-istaht-green ring-green-100' : 'bg-red-50 text-istaht-red ring-red-100'"
            >
              {{ categorie.est_actif ? 'Actif' : 'Inactif' }}
            </span>
            <span class="ui-badge bg-blue-50 text-istaht-blue ring-1 ring-blue-100">
              {{ formatNumber(categorie.articles_count) }} article(s)
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div class="rounded-lg border border-slate-100 bg-white p-4 shadow-soft">
          <p class="text-xs font-bold uppercase text-slate-400">Code</p>
          <p class="mt-1 text-xl font-bold text-istaht-navy">{{ categorie.code }}</p>
        </div>
        <div class="rounded-lg border border-slate-100 bg-white p-4 shadow-soft">
          <p class="text-xs font-bold uppercase text-slate-400">Nom</p>
          <p class="mt-1 truncate text-lg font-bold text-istaht-navy">{{ categorie.nom }}</p>
        </div>
        <div class="rounded-lg border border-slate-100 bg-white p-4 shadow-soft">
          <p class="text-xs font-bold uppercase text-slate-400">Couleur</p>
          <p class="mt-1 text-sm font-bold text-slate-700">{{ categorie.couleur }}</p>
        </div>
        <div class="rounded-lg border border-slate-100 bg-white p-4 shadow-soft">
          <p class="text-xs font-bold uppercase text-slate-400">Statut</p>
          <p class="mt-1 text-lg font-bold" :class="categorie.est_actif ? 'text-istaht-green' : 'text-istaht-red'">
            {{ categorie.est_actif ? 'Actif' : 'Inactif' }}
          </p>
        </div>
        <div class="rounded-lg border border-slate-100 bg-white p-4 shadow-soft">
          <p class="text-xs font-bold uppercase text-slate-400">Articles</p>
          <p class="mt-1 text-xl font-bold text-istaht-blue">{{ formatNumber(categorie.articles_count) }}</p>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="border-b border-slate-100 px-5 py-4">
          <h3 class="text-base font-bold text-istaht-navy">Articles de la categorie</h3>
          <p class="text-sm text-slate-500">Reference, designation, unite, stock et seuil minimal.</p>
        </div>

        <div class="overflow-x-auto">
          <table>
            <thead>
              <tr>
                <th>Reference</th>
                <th>Designation</th>
                <th>Unite</th>
                <th class="text-right">Stock actuel</th>
                <th class="text-right">Seuil minimal</th>
                <th class="text-right">Seuil maximal</th>
                <th>Statut</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="article in articles.data" :key="article.id">
                <td class="font-semibold text-istaht-blue">{{ article.reference }}</td>
                <td class="font-semibold text-slate-800">{{ article.designation }}</td>
                <td>{{ article.unite_mesure }}</td>
                <td class="text-right font-bold text-istaht-navy">{{ formatNumber(article.quantite_stock, 2) }}</td>
                <td class="text-right">{{ formatNumber(article.seuil_minimal, 2) }}</td>
                <td class="text-right">{{ formatNumber(article.seuil_maximal, 2) }}</td>
                <td>
                  <span :class="['ui-badge ring-1', statusClasses(article.statut)]">
                    {{ article.statut.label }}
                  </span>
                </td>
                <td>
                  <div class="flex justify-end gap-2">
                    <Link :href="route('articles.show', article.id)" class="ui-button ui-button-secondary px-3 py-1.5 text-xs">
                      Voir article
                    </Link>
                    <ModalLink :href="route('articles.edit', article.id)" class="ui-button ui-button-ghost px-3 py-1.5 text-xs">
                      Modifier article
                    </ModalLink>
                  </div>
                </td>
              </tr>
              <tr v-if="!articles.data.length">
                <td colspan="7" class="py-10 text-center text-sm text-slate-500">
                  Aucun article rattache a cette categorie.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
          <Pagination :links="articles.links" />
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
