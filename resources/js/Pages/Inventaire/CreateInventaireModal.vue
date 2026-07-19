<script setup>
import { ref, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import { CalendarDaysIcon, ClipboardDocumentCheckIcon, TagIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  categories: { type: Array, default: () => [] },
})

const createInventaireModal = ref(null)

function currentIsoWeek() {
  const now = new Date()
  const target = new Date(now.valueOf())
  const dayNr = (now.getDay() + 6) % 7
  target.setDate(target.getDate() - dayNr + 3)
  const firstThursday = target.valueOf()
  target.setMonth(0, 1)
  if (target.getDay() !== 4) {
    target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7)
  }
  const week = 1 + Math.ceil((firstThursday - target.valueOf()) / 604800000)
  return `${now.getFullYear()}-W${String(week).padStart(2, '0')}`
}

const form = useForm({ semaine: currentIsoWeek(), categorie_id: '' })

// Aperçu lisible : lundi → dimanche de la semaine ISO sélectionnée
const weekRange = computed(() => {
  const m = /^(\d{4})-W(\d{2})$/.exec(form.semaine || '')
  if (!m) return null
  const year = +m[1], week = +m[2]
  const simple = new Date(year, 0, 1 + (week - 1) * 7)
  const dow = simple.getDay()
  const monday = new Date(simple)
  if (dow <= 4) monday.setDate(simple.getDate() - simple.getDay() + 1)
  else monday.setDate(simple.getDate() + 8 - simple.getDay())
  const sunday = new Date(monday); sunday.setDate(monday.getDate() + 6)
  const fmt = (d) => d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
  return `${fmt(monday)} → ${fmt(sunday)}`
})

const isCurrentWeek = computed(() => form.semaine === currentIsoWeek())

function useThisWeek() {
  form.semaine = currentIsoWeek()
}

function submit() {
  form.post(route('inventaires.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      createInventaireModal.value.close()
      form.reset()
    },
  })
}
</script>

<template>
  <Modal name="createInventaireModal" ref="createInventaireModal">
    <div class="mb-5 border-b border-slate-100 pb-4">
      <h2 class="flex items-center gap-2 text-lg font-bold text-istaht-navy">
        <ClipboardDocumentCheckIcon class="h-5 w-5" />
        Nouvel inventaire hebdomadaire
      </h2>
      <p class="mt-1 text-sm text-slate-500">
        L'inventaire se génère pour une semaine et liste automatiquement les articles ayant un stock théorique positif.
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Catégorie -->
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Catégorie</label>
        <div class="relative">
          <select v-model="form.categorie_id" class="ui-input w-full pl-9">
            <option value="">Toutes les catégories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.nom }}</option>
          </select>
          <TagIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>
        <div v-if="form.errors.categorie_id" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.categorie_id }}</div>
      </div>

      <!-- Semaine -->
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Semaine à inventorier *</label>
        <div class="relative">
          <input v-model="form.semaine" type="week" required class="ui-input w-full pl-9" />
          <CalendarDaysIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>
        <div v-if="form.errors.semaine" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.semaine }}</div>

        <!-- Aperçu de la période -->
        <div v-if="weekRange" class="mt-2 flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
          <span class="text-sm text-istaht-navy">
            Période : <strong>{{ weekRange }}</strong>
          </span>
          <button
            v-if="!isCurrentWeek"
            type="button"
            class="text-xs font-bold text-istaht-blue hover:underline"
            @click="useThisWeek"
          >
            Semaine en cours
          </button>
          <span v-else class="rounded-full bg-istaht-blue px-2 py-0.5 text-xs font-bold text-white">Semaine en cours</span>
        </div>
      </div>

      <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
        <button type="button" @click="createInventaireModal.close()" class="ui-button ui-button-ghost">Annuler</button>
        <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary disabled:opacity-50">
          {{ form.processing ? 'Création…' : 'Créer l\'inventaire' }}
        </button>
      </div>
    </form>
  </Modal>
</template>
