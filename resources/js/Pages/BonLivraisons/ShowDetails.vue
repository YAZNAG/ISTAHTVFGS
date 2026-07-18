<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, DocumentArrowDownIcon, TruckIcon, CubeIcon } from '@heroicons/vue/24/outline';
import { getBonLivraisonInfo } from '@/Utils/labels';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
  bonLivraison: {
    type: Object,
    required: true,
  },
});

const formatDate = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('fr-FR');
};

const formatNumber = (number) => {
  if (!number || isNaN(number)) return '0';
  return new Intl.NumberFormat('fr-FR').format(number);
};
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="`Bon de Livraison - ${bonLivraison.numero}`" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-mono text-sm font-bold text-istaht-blue">{{ bonLivraison.numero }}</p>
              <span
                class="rounded-full px-2.5 py-1 text-xs font-bold"
                :class="getBonLivraisonInfo(bonLivraison.statut).color"
              >
                {{ getBonLivraisonInfo(bonLivraison.statut).label }}
              </span>
            </div>
            <h2 class="mt-2 flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <TruckIcon class="h-6 w-6" />
              Bon de livraison
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Créé le {{ formatDate(bonLivraison.created_at) }}
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link :href="route('bon-livraisons.index')" class="ui-button ui-button-ghost">
              <ArrowLeftIcon class="mr-1.5 h-4 w-4" />
              Retour liste
            </Link>
            <a
              v-if="can('pdf_bonLivraisons') && bonLivraison.statut === 'livree'"
              :href="route('bon-livraisons.pdf', bonLivraison.id)"
              target="_blank"
              class="ui-button ui-button-secondary"
            >
              <DocumentArrowDownIcon class="mr-1.5 h-4 w-4" />
              Télécharger PDF
            </a>
          </div>
        </div>

        <!-- Infos -->
        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Fournisseur</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ bonLivraison.fournisseur?.nom || 'Non spécifié' }}</p>
            <p class="mt-0.5 text-sm text-slate-500">{{ bonLivraison.fournisseur?.contact || '—' }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Date de livraison</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ bonLivraison.date_livraison || '—' }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Réceptionné par</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ bonLivraison.receptionne_par || 'Non spécifié' }}</p>
          </div>
        </div>

        <!-- Annulé -->
        <div v-if="bonLivraison.statut === 'annule'" class="mt-5 rounded-lg border border-red-100 bg-red-50 p-4">
          <p class="font-bold text-istaht-red">Bon de livraison annulé le {{ formatDate(bonLivraison.annule_at) }}</p>
          <p class="mt-1 text-sm text-red-700">{{ bonLivraison.reason_annulation || 'Aucune raison fournie.' }}</p>
        </div>
      </div>

      <!-- ═══ Articles livrés ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <CubeIcon class="h-5 w-5 text-istaht-blue" />
            <h3 class="font-bold text-istaht-navy">Articles livrés</h3>
          </div>
          <span
            v-if="bonLivraison.items?.length"
            class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100"
          >
            {{ bonLivraison.items.length }} article(s)
          </span>
        </div>

        <div v-if="bonLivraison.items && bonLivraison.items.length > 0" class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">N°</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Désignation</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Unité</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Quantité livrée</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(ligne, index) in bonLivraison.items" :key="index" class="transition hover:bg-slate-50">
                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-500">{{ index + 1 }}</td>
                <td class="px-5 py-3.5 text-sm font-semibold text-slate-700">{{ ligne.designation || 'Non spécifié' }}</td>
                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-600">{{ ligne.unite_mesure || 'Unité' }}</td>
                <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm font-bold text-istaht-navy">
                  {{ formatNumber(ligne.quantite) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="py-10 text-center">
          <CubeIcon class="mx-auto h-12 w-12 text-slate-300" />
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun article livré</h3>
          <p class="mt-1 text-sm text-slate-500">Ce bon de livraison ne contient aucun article ou a été annulé.</p>
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
