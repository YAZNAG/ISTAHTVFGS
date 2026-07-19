<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeftIcon, DocumentArrowDownIcon, ClipboardDocumentCheckIcon, CubeIcon, TruckIcon } from '@heroicons/vue/24/outline';
import { usePermission } from '@/Utils/permission';

const { can } = usePermission();

const props = defineProps({
  bonReception: {
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
    <Head :title="`Bon de Réception - ${bonReception.numero}`" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-mono text-sm font-bold text-istaht-blue">{{ bonReception.numero }}</p>
              <Link
                v-if="bonReception.bon_livraison_id"
                :href="route('bon-livraisons.show', bonReception.bon_livraison_id)"
                class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 font-mono text-xs font-bold text-istaht-green ring-1 ring-green-100 transition hover:bg-green-100"
                title="Voir le bon de livraison lié"
              >
                <TruckIcon class="h-3.5 w-3.5" />
                {{ bonReception.bon_livraison_numero }}
              </Link>
            </div>
            <h2 class="mt-2 flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <ClipboardDocumentCheckIcon class="h-6 w-6" />
              Bon de réception
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              Créé le {{ formatDate(bonReception.created_at) }} — lié au bon de livraison
              <strong class="text-istaht-green">{{ bonReception.bon_livraison_numero }}</strong>
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link :href="route('bon-receptions.index')" class="ui-button ui-button-ghost">
              <ArrowLeftIcon class="mr-1.5 h-4 w-4" />
              Retour liste
            </Link>
            <a
              v-if="can('pdf_bonReceptions')"
              :href="route('bon-receptions.pdf', bonReception.id)"
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
            <p class="mt-1 font-bold text-istaht-navy">{{ bonReception.fournisseur?.nom || 'Non spécifié' }}</p>
            <p class="mt-0.5 text-sm text-slate-500">{{ bonReception.fournisseur?.contact || '—' }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Date de livraison</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ bonReception.date_livraison || '—' }}</p>
          </div>
          <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase text-slate-400">Réceptionné par</p>
            <p class="mt-1 font-bold text-istaht-navy">{{ bonReception.receptionne_par || 'Non spécifié' }}</p>
          </div>
        </div>
      </div>

      <!-- ═══ Articles réceptionnés ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
          <div class="flex items-center gap-2">
            <CubeIcon class="h-5 w-5 text-istaht-blue" />
            <h3 class="font-bold text-istaht-navy">Articles réceptionnés</h3>
          </div>
          <span
            v-if="bonReception.items?.length"
            class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-istaht-blue ring-1 ring-blue-100"
          >
            {{ bonReception.items.length }} article(s)
          </span>
        </div>

        <div v-if="bonReception.items && bonReception.items.length > 0" class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">N°</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Désignation</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Unité</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Quantité reçue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(ligne, index) in bonReception.items" :key="index" class="transition hover:bg-slate-50">
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
          <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun article réceptionné</h3>
        </div>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
