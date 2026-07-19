<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import { DocumentChartBarIcon, ChartBarSquareIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'

const reports = [
  {
    name: 'Cardex',
    description: 'Fiche de stock annuelle d\'un article : entrées, sorties et stock jour par jour, sur 12 mois.',
    href: route('cardex.create'),
    icon: DocumentChartBarIcon,
    hasModal: true,
  },
]
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Les Rapports" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
          <ChartBarSquareIcon class="h-6 w-6" />
          Rapports
        </h2>
        <p class="mt-1 text-sm text-slate-500">
          Générez et téléchargez les rapports détaillés de votre activité de stock.
        </p>
      </div>

      <!-- ═══ Cartes de rapports ═══ -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <component
          :is="report.hasModal ? ModalLink : Link"
          v-for="report in reports"
          :key="report.href"
          :href="report.href"
          class="group flex flex-col rounded-lg border border-slate-200 bg-white p-5 shadow-soft transition hover:border-istaht-blue hover:shadow-panel"
        >
          <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-istaht-blue ring-1 ring-blue-100">
              <component :is="report.icon" class="h-6 w-6" />
            </div>
            <div class="min-w-0 flex-1">
              <h3 class="text-lg font-bold text-istaht-navy">{{ report.name }}</h3>
              <p class="mt-1 text-sm leading-6 text-slate-500">{{ report.description }}</p>
            </div>
          </div>
          <div class="mt-4 flex items-center gap-1 text-sm font-bold text-istaht-blue">
            Générer le rapport
            <ArrowRightIcon class="h-4 w-4 transition group-hover:translate-x-1" />
          </div>
        </component>
      </div>
    </section>
  </AuthenticatedLayout>
</template>
