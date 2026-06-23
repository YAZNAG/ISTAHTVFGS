<script setup>
import { computed, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import { usePermission } from '@/Utils/permission'

const { can } = usePermission()

const props = defineProps({
  article: {
    type: Object,
    required: true,
  },
  mouvements: {
    type: Array,
    default: () => [],
  },
  marches: {
    type: Array,
    default: () => [],
  },
  receptions: {
    type: Array,
    default: () => [],
  },
  sorties: {
    type: Array,
    default: () => [],
  },
})

const pendingDelete = ref(false)
const isDeleting = ref(false)

const infoCards = computed(() => [
  { label: 'Stock actuel', value: `${formatNumber(props.article.quantite_stock, 2)} ${props.article.unite_mesure || ''}`, className: 'text-istaht-navy' },
  { label: 'Seuil minimal', value: formatNumber(props.article.seuil_minimal, 2), className: 'text-istaht-amber' },
  { label: 'Seuil maximal', value: formatNumber(props.article.seuil_maximal, 2), className: 'text-istaht-blue' },
  { label: 'Unite de mesure', value: props.article.unite_mesure || '-', className: 'text-slate-700' },
  { label: 'TVA', value: `${formatNumber(props.article.taux_tva, 2)}%`, className: 'text-istaht-green' },
  { label: 'Categorie', value: props.article.categorie?.nom || 'Non classe', className: 'text-istaht-blue' },
])

const deleteMessage = computed(() => {
  if (props.article.is_used) {
    return 'Cet article est deja utilise dans des marches ou mouvements de stock. Il ne peut pas etre supprime definitivement. Voulez-vous le desactiver ?'
  }

  return 'Cet article ne contient aucune relation. Confirmez-vous la suppression definitive ?'
})

function formatNumber(value, fraction = 0) {
  return Number(value || 0).toLocaleString('fr-FR', {
    minimumFractionDigits: fraction,
    maximumFractionDigits: fraction,
  })
}

function statusClasses(active) {
  return active
    ? 'bg-green-50 text-istaht-green ring-green-100'
    : 'bg-red-50 text-istaht-red ring-red-100'
}

function stockClasses(status) {
  if (status?.type === 'danger') return 'bg-red-50 text-istaht-red ring-red-100'
  if (status?.type === 'warning') return 'bg-amber-50 text-istaht-amber ring-amber-100'
  return 'bg-green-50 text-istaht-green ring-green-100'
}

function movementClasses(type) {
  return type === 'entree'
    ? 'bg-green-50 text-istaht-green ring-green-100'
    : 'bg-red-50 text-istaht-red ring-red-100'
}

function confirmDelete() {
  isDeleting.value = true
  router.delete(route('articles.destroy', props.article.id), {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false
      pendingDelete.value = false
    },
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="`Article - ${article.reference}`" />

    <section class="space-y-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <Link :href="route('articles.index')" class="ui-button ui-button-ghost">
          Retour liste
        </Link>

        <div class="flex flex-wrap gap-2">
          <ModalLink v-if="can('edit_articles')" :href="route('articles.edit', article.id)" class="ui-button ui-button-primary">
            Modifier article
          </ModalLink>
          <button v-if="can('edit_articles')" type="button" class="ui-button ui-button-danger" @click="pendingDelete = true">
            Supprimer article
          </button>
        </div>
      </div>

      <div
        class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft"
        :style="{ borderTop: `4px solid ${article.categorie?.couleur || '#155e9f'}` }"
      >
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
          <div>
            <p class="text-sm font-bold uppercase text-istaht-cyan">{{ article.reference }}</p>
            <h1 class="mt-1 text-2xl font-bold text-istaht-navy md:text-3xl">{{ article.designation }}</h1>
            <p class="mt-2 text-sm text-slate-500">
              {{ article.categorie?.code || '-' }} - {{ article.categorie?.nom || 'Non classe' }}
            </p>
          </div>

          <div class="flex flex-wrap gap-2">
            <span :class="['ui-badge ring-1', statusClasses(article.est_actif)]">
              {{ article.est_actif ? 'Actif' : 'Inactif' }}
            </span>
            <span :class="['ui-badge ring-1', stockClasses(article.stock_status)]">
              {{ article.stock_status.label }}
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
        <div
          v-for="card in infoCards"
          :key="card.label"
          class="rounded-lg border border-slate-100 bg-white p-4 shadow-soft"
        >
          <p class="text-xs font-bold uppercase text-slate-400">{{ card.label }}</p>
          <p class="mt-1 truncate text-lg font-bold" :class="card.className">{{ card.value }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
          <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-istaht-navy">Derniers mouvements de stock</h3>
          </div>
          <div v-if="mouvements.length" class="divide-y divide-slate-100">
            <div v-for="movement in mouvements" :key="movement.id" class="flex items-center justify-between gap-3 p-4">
              <div>
                <p class="font-semibold text-istaht-navy">{{ movement.motif || 'Mouvement stock' }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ movement.date || '-' }}</p>
              </div>
              <div class="text-right">
                <span :class="['ui-badge ring-1', movementClasses(movement.type)]">{{ movement.type || '-' }}</span>
                <p class="mt-1 text-sm font-bold text-slate-700">{{ formatNumber(movement.quantite, 2) }}</p>
              </div>
            </div>
          </div>
          <div v-else class="px-5 py-8 text-sm text-slate-500">Aucun mouvement trouve.</div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
          <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-istaht-navy">Derniers marches lies</h3>
          </div>
          <div v-if="marches.length" class="divide-y divide-slate-100">
            <div v-for="marche in marches" :key="marche.id" class="p-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="font-semibold text-istaht-navy">{{ marche.reference || 'Marche' }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ marche.fournisseur || '-' }}</p>
                </div>
                <span class="ui-badge bg-blue-50 text-istaht-blue ring-1 ring-blue-100">
                  TVA {{ formatNumber(marche.taux_tva, 2) }}%
                </span>
              </div>
              <p class="mt-2 text-sm text-slate-600">
                Prix HT: <span class="font-bold">{{ formatNumber(marche.prix_unitaire_ht, 2) }}</span>
                | Qte max: <span class="font-bold">{{ formatNumber(marche.quantite_maximale, 2) }}</span>
              </p>
            </div>
          </div>
          <div v-else class="px-5 py-8 text-sm text-slate-500">Aucun marche lie.</div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
          <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-istaht-navy">Dernieres receptions</h3>
          </div>
          <div v-if="receptions.length" class="divide-y divide-slate-100">
            <div v-for="reception in receptions" :key="reception.id" class="flex items-center justify-between gap-3 p-4">
              <div>
                <p class="font-semibold text-istaht-navy">{{ reception.numero || 'Reception' }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ reception.date || '-' }}</p>
              </div>
              <div class="text-right text-sm">
                <p class="font-bold text-istaht-green">{{ formatNumber(reception.quantite, 2) }}</p>
                <p class="text-slate-500">{{ formatNumber(reception.prix_unitaire, 2) }} DH</p>
              </div>
            </div>
          </div>
          <div v-else class="px-5 py-8 text-sm text-slate-500">Aucune reception trouvee.</div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
          <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-bold text-istaht-navy">Dernieres sorties</h3>
          </div>
          <div v-if="sorties.length" class="divide-y divide-slate-100">
            <div v-for="sortie in sorties" :key="sortie.id" class="flex items-center justify-between gap-3 p-4">
              <div>
                <p class="font-semibold text-istaht-navy">{{ sortie.motif || 'Sortie stock' }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ sortie.date || '-' }}</p>
              </div>
              <p class="text-sm font-bold text-istaht-red">{{ formatNumber(sortie.quantite, 2) }}</p>
            </div>
          </div>
          <div v-else class="px-5 py-8 text-sm text-slate-500">Aucune sortie trouvee.</div>
        </div>
      </div>
    </section>

    <div
      v-if="pendingDelete"
      class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-panel transition">
        <h3 class="text-lg font-bold text-istaht-navy">Confirmer la suppression</h3>
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ deleteMessage }}</p>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="ui-button ui-button-ghost" :disabled="isDeleting" @click="pendingDelete = false">
            Annuler
          </button>
          <button
            type="button"
            class="ui-button"
            :class="article.is_used ? 'bg-istaht-amber text-white hover:bg-orange-600' : 'ui-button-danger'"
            :disabled="isDeleting"
            @click="confirmDelete"
          >
            {{ isDeleting ? 'Traitement...' : article.is_used ? 'Desactiver article' : 'Supprimer definitivement' }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
