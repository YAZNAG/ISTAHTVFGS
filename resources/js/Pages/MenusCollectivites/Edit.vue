<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

/* ---------- props ---------- */
const props = defineProps({
  fiches: Array,
  menu:   Object,          // existing MenuCollectivite
})

/* ---------- form (pre-filled) ---------- */
const form = useForm({
  date: props.menu.date,
  responsable: props.menu.responsable,
  effectif: props.menu.effectif,
  menus: {
    petit_dejeuner: { ...props.menu.menus.petit_dejeuner },
    dejeuner:       { ...props.menu.menus.dejeuner },
    diner:          { ...props.menu.menus.diner },
  },
})

/* ---------- helpers ---------- */
const entreeFiches         = computed(() => props.fiches.filter(f => f.repas === "hors d'oeuvres"))
const platPrincipaleFiches = computed(() => props.fiches.filter(f => f.repas === "plats"))
const dessertFiches        = computed(() => props.fiches.filter(f => f.repas === "desserts"))
const platSpecialFiches    = computed(() => props.fiches.filter(f => f.repas === "plats spéciaux"))

const allSelectedIds = computed(() => {
  const ids = []
  Object.values(form.menus).forEach(meal =>
    Object.values(meal).forEach(v => v && ids.push(v))
  )
  return ids
})

const duplicates = computed(() => {
  const seen = new Set(), dups = new Set()
  allSelectedIds.value.forEach(id => (seen.has(id) ? dups.add(id) : seen.add(id)))
  return [...dups]
})
const hasDuplicate = computed(() => duplicates.value.length > 0)
const isDuplicate  = id => duplicates.value.includes(id)

/* ---------- submit ---------- */
function submit() {
  form.put(route('menus.update', { menu: props.menu.id }), {
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Modifier un menu collectivité" />

    <!-- header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Modifier un menu collectivité</h1>
      <p class="text-sm text-gray-600 mt-1">Modifiez les informations puis enregistrez.</p>
    </div>

    <!-- form -->
    <form @submit.prevent="submit" class="space-y-6">
      <div class="flex flex-col lg:flex-row gap-4">
        <div class="space-y-6 w-full">
          <!-- infos générales -->
          <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Informations générales</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- date -->
              <div>
                <InputLabel for="date" value="Date du menu" />
                <input id="date" v-model="form.date" type="date" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <InputError :message="form.errors.date" />
              </div>

              <!-- responsable -->
              <div>
                <InputLabel for="responsable" value="Responsable" />
                <input id="responsable" v-model="form.responsable" type="text" required placeholder="Nom du chef / responsable"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <InputError :message="form.errors.responsable" />
              </div>

              <!-- effectif -->
              <div>
                <InputLabel for="effectif" value="Effectif prévu" />
                <input id="effectif" v-model.number="form.effectif" type="number" min="1" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <InputError :message="form.errors.effectif" />
              </div>
            </div>
          </div>

          <!-- composition des menus -->
          <div class="bg-white">
          <h2 class="text-lg font-semibold mb-4">Composition des menus</h2>
          <div v-if="hasDuplicate" class="rounded-md bg-red-50 p-4 text-sm text-red-800 mb-2">
            ⚠️  Une même fiche technique ne peut pas être sélectionnée plusieurs fois 
            <span class="font-semibold">{{ duplicates.join(', ') }}</span>
          </div>

          <div v-if="form.hasErrors" class="rounded-md bg-red-50 p-4 text-sm text-red-800 mb-2">
            <p v-for="(msgs, key) in form.errors" :key="key" class="mb-2">⚠️  {{ msgs }}</p>
          </div>
          
          <!-- Petit-déjeuner -->
          <div class="mb-6 shadow rounded-lg p-6">
            <h3 class="text-base font-bold mb-2">Petit-déjeuner</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="entree-petit_dejeuner" value="Entrée" />
                <select 
                  id="entree-petit_dejeuner"
                  v-model="form.menus.petit_dejeuner.entree"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.petit_dejeuner.entree) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in entreeFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="plat-petit_dejeuner" value="Plat" />
                <select 
                  id="plat-petit_dejeuner"
                  v-model="form.menus.petit_dejeuner.plat"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.petit_dejeuner.plat) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in platPrincipaleFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="dessert-petit_dejeuner" value="Dessert" />
                <select 
                  id="dessert-petit_dejeuner"
                  v-model="form.menus.petit_dejeuner.dessert"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.petit_dejeuner.dessert) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in dessertFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="sp-petit_dejeuner" value="Plat spécial" />
                <select 
                  id="sp-petit_dejeuner"
                  v-model="form.menus.petit_dejeuner.plat_special"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.petit_dejeuner.plat_special) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in platSpecialFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Déjeuner -->
          <div class="mb-6 shadow rounded-lg p-6">
            <h3 class="text-base font-bold mb-2">Déjeuner</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="entree-dejeuner" value="Entrée" />
                <select 
                  id="entree-dejeuner"
                  v-model="form.menus.dejeuner.entree"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.dejeuner.entree) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in entreeFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="plat-dejeuner" value="Plat" />
                <select 
                  id="plat-dejeuner"
                  v-model="form.menus.dejeuner.plat"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.dejeuner.plat) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in platPrincipaleFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="dessert-dejeuner" value="Dessert" />
                <select 
                  id="dessert-dejeuner"
                  v-model="form.menus.dejeuner.dessert"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.dejeuner.dessert) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in dessertFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="sp-dejeuner" value="Plat spécial" />
                <select 
                  id="sp-dejeuner"
                  v-model="form.menus.dejeuner.plat_special"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.dejeuner.plat_special) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in platSpecialFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Dîner -->
          <div class="mb-6 shadow rounded-lg p-6">
            <h3 class="text-base font-bold mb-2">Dîner</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="entree-diner" value="Entrée" />
                <select
                  id="entree-diner"
                  v-model="form.menus.diner.entree"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.diner.entree) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in entreeFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="plat-diner" value="Plat" />
                <select 
                  id="plat-diner"
                  v-model="form.menus.diner.plat"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.diner.plat) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in platPrincipaleFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="dessert-diner" value="Dessert" />
                <select 
                  id="dessert-diner"
                  v-model="form.menus.diner.dessert"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.diner.dessert) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in dessertFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>

              <div>
                <InputLabel for="sp-diner" value="Plat spécial" />
                <select 
                  id="sp-diner"
                  v-model="form.menus.diner.plat_special"
                  :class="[
                    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                    isDuplicate(form.menus.diner.plat_special) ? 'border-red-500' : ''
                  ]">
                  <option value="">Sélectionnez...</option>
                  <option v-for="fiche in platSpecialFiches" :key="fiche.id" :value="fiche.id">{{ fiche.plat }} - {{ fiche.id }}</option>
                </select>
              </div>
            </div>
          </div>


        </div>
        </div>

        <!-- résumé -->
        <div>
        <h2 class="text-lg font-semibold mb-4">Resume</h2>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase">#</th>
            <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase">Repas</th>
            <th class="px-2 py-3 text-left font-medium text-gray-500 uppercase">Plat</th>
            <th class="px-2 py-3 text-center font-medium text-gray-500 uppercase">Effectif</th>
            <th class="px-2 py-3 text-center font-medium text-gray-500 uppercase">Crée le</th>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="fiche in fiches.filter(f => allSelectedIds.includes(f.id))" :key="fiche.id">
              <td class="px-2 py-4">{{ fiche.id }}</td>
              <td class="px-2 py-4">{{ fiche.repas }}</td>
              <td class="px-2 py-4">{{ fiche.plat }}</td>
              <td class="px-2 py-4 text-center">{{ fiche.effectif }}</td>
              <td class="px-2 py-4">{{ fiche.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
</div>
      <!-- actions -->
      <div class="flex items-center justify-end gap-3">
        <button :disabled="form.processing || hasDuplicate"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
          Mettre à jour le menu
        </button>
      </div>
    </form>
  </AuthenticatedLayout>
</template>