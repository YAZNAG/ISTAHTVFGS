<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import { ArrowLeftIcon, CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  fiches: Array,
  menu: Object,
})

const form = useForm({
  date: props.menu.date,
  responsable: props.menu.responsable,
  effectif_petit_dejeuner: props.menu.effectif_petit_dejeuner,
  effectif_dejeuner: props.menu.effectif_dejeuner,
  effectif_diner: props.menu.effectif_diner,
  menus: {
    petit_dejeuner: { ...props.menu.menus.petit_dejeuner },
    dejeuner:       { ...props.menu.menus.dejeuner },
    diner:          { ...props.menu.menus.diner },
  },
})

const meals = [
  { key: 'petit_dejeuner', label: 'Petit-déjeuner', effectifKey: 'effectif_petit_dejeuner' },
  { key: 'dejeuner',       label: 'Déjeuner',       effectifKey: 'effectif_dejeuner' },
  { key: 'diner',          label: 'Dîner',          effectifKey: 'effectif_diner' },
]

const fichesBy = (repas) => props.fiches.filter(f => f.repas === repas)
const categories = computed(() => [
  { key: 'entree',       label: 'Entrée',       required: true,  fiches: fichesBy("hors d'oeuvres") },
  { key: 'plat',         label: 'Plat',         required: true,  fiches: fichesBy('plats') },
  { key: 'dessert',      label: 'Dessert',      required: true,  fiches: fichesBy('desserts') },
  { key: 'plat_special', label: 'Plat spécial', required: false, fiches: fichesBy('plats spéciaux') },
])

const activeMeal = ref('petit_dejeuner')

function mealComplete(mealKey) {
  const m = form.menus[mealKey]
  return m.entree && m.plat && m.dessert
}

const allSelectedIds = computed(() => {
  const ids = []
  Object.values(form.menus).forEach(meal => Object.values(meal).forEach(v => v && ids.push(v)))
  return ids
})

const duplicates = computed(() => {
  const seen = new Set(), dups = new Set()
  allSelectedIds.value.forEach(id => (seen.has(id) ? dups.add(id) : seen.add(id)))
  return [...dups]
})
const hasDuplicate = computed(() => duplicates.value.length > 0)
const isDuplicate = (id) => duplicates.value.includes(id)

const selectedFiches = computed(() => props.fiches.filter(f => allSelectedIds.value.includes(f.id)))

function submit() {
  form.put(route('menus.update', { menu: props.menu.id }))
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Modifier un menu collectivité" />

    <form @submit.prevent="submit" class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-istaht-navy">Modifier le menu collectivité</h2>
            <p class="mt-1 text-sm text-slate-500">
              Mettez à jour les informations et la composition de chaque service.
            </p>
          </div>
          <Link :href="route('menus.index')" class="ui-button ui-button-ghost">
            <ArrowLeftIcon class="mr-1.5 h-4 w-4" />
            Retour liste
          </Link>
        </div>
      </div>

      <!-- ═══ Informations générales ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">Informations générales</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Date du menu *</label>
            <input v-model="form.date" type="date" required class="ui-input w-full" />
            <InputError :message="form.errors.date" />
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Responsable *</label>
            <input v-model="form.responsable" type="text" required placeholder="Nom du chef / responsable" class="ui-input w-full" />
            <InputError :message="form.errors.responsable" />
          </div>
        </div>
      </div>

      <!-- ═══ Erreurs / doublons ═══ -->
      <div v-if="hasDuplicate" class="flex items-start gap-2 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-istaht-red">
        <ExclamationTriangleIcon class="mt-0.5 h-4 w-4 shrink-0" />
        <span>Une même fiche technique ne peut pas être sélectionnée plusieurs fois (id : <strong>{{ duplicates.join(', ') }}</strong>).</span>
      </div>
      <div v-if="form.hasErrors" class="rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-istaht-red">
        <p v-for="(msg, key) in form.errors" :key="key">{{ msg }}</p>
      </div>

      <!-- ═══ Composition par service (onglets) ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="flex gap-2 overflow-x-auto border-b border-slate-100 px-4 py-3">
          <button
            v-for="meal in meals"
            :key="meal.key"
            type="button"
            class="flex items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-bold transition"
            :class="activeMeal === meal.key ? 'bg-istaht-navy text-white' : 'text-slate-600 hover:bg-slate-100'"
            @click="activeMeal = meal.key"
          >
            {{ meal.label }}
            <CheckCircleIcon v-if="mealComplete(meal.key)" class="h-4 w-4" :class="activeMeal === meal.key ? 'text-white' : 'text-istaht-green'" />
          </button>
        </div>

        <div v-for="meal in meals" v-show="activeMeal === meal.key" :key="meal.key" class="p-5">
          <div class="mb-4 max-w-xs">
            <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Effectif — {{ meal.label }} *</label>
            <input v-model.number="form[meal.effectifKey]" type="number" min="1" required class="ui-input w-full" />
            <InputError :message="form.errors[meal.effectifKey]" />
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div v-for="cat in categories" :key="cat.key">
              <label class="mb-1 block text-xs font-bold uppercase text-slate-400">
                {{ cat.label }} <span v-if="cat.required" class="text-istaht-red">*</span>
              </label>
              <select
                v-model="form.menus[meal.key][cat.key]"
                class="ui-input w-full"
                :class="isDuplicate(form.menus[meal.key][cat.key]) ? 'border-red-500 ring-1 ring-red-500' : ''"
              >
                <option value="">Sélectionnez…</option>
                <option v-for="fiche in cat.fiches" :key="fiche.id" :value="fiche.id">
                  {{ fiche.plat }} — #{{ fiche.id }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ Résumé ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
        <div class="border-b border-slate-100 px-5 py-4">
          <h3 class="font-bold text-istaht-navy">Récapitulatif des plats sélectionnés</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">#</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Catégorie</th>
                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Plat</th>
                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Effectif fiche</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="fiche in selectedFiches" :key="fiche.id" class="hover:bg-slate-50">
                <td class="px-5 py-3 font-mono text-sm text-istaht-blue">#{{ fiche.id }}</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ fiche.repas }}</td>
                <td class="px-5 py-3 text-sm font-semibold text-slate-700">{{ fiche.plat }}</td>
                <td class="px-5 py-3 text-right text-sm text-slate-600">{{ fiche.effectif }}</td>
              </tr>
              <tr v-if="selectedFiches.length === 0">
                <td colspan="4" class="px-5 py-6 text-center text-sm text-slate-400">Aucun plat sélectionné.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ═══ Actions ═══ -->
      <div class="flex items-center justify-end gap-2">
        <Link :href="route('menus.index')" class="ui-button ui-button-ghost">Annuler</Link>
        <button type="submit" class="ui-button ui-button-primary disabled:opacity-50" :disabled="form.processing || hasDuplicate">
          {{ form.processing ? 'Mise à jour…' : 'Mettre à jour le menu' }}
        </button>
      </div>
    </form>
  </AuthenticatedLayout>
</template>
