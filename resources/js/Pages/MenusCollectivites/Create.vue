<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { Modal } from '@inertiaui/modal-vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { computed, ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  fiches: Array,
});


const form = useForm({
  date: '',
  responsable: '',
  effectif_petit_dejeuner: 1,
  effectif_dejeuner: 1,
  effectif_diner: 1,
  menus: {
    petit_dejeuner: { entree: '', plat: '', dessert: '', plat_special: '' },
    dejeuner:       { entree: '', plat: '', dessert: '', plat_special: '' },
    diner:          { entree: '', plat: '', dessert: '', plat_special: '' }
  }
})

const entreeFiches = computed(() => {
  return props.fiches.filter(fiche => fiche.repas == "hors d'oeuvres")
})

const platPrincipaleFiches = computed(() => {
  return props.fiches.filter(fiche => fiche.repas == "plats")
})

const dessertFiches = computed(() => {
  return props.fiches.filter(fiche => fiche.repas == "desserts")
})

const platSpecialFiches = computed(() => {
  return props.fiches.filter(fiche => fiche.repas == "plats spéciaux")
})

function submit() {
  form.post(route('menus.store'), {
    onSuccess: () => form.reset()
  })
}

const allSelectedIds = computed(() => {
  const ids = []
  Object.values(form.menus).forEach(meal =>
    Object.values(meal).forEach(v => v && ids.push(v))
  )
  return ids
})

const duplicates = computed(() => {
  const seen = new Set()
  const dups = new Set()
  allSelectedIds.value.forEach(id => {
    if (seen.has(id)) dups.add(id)
    seen.add(id)
  })
  return [...dups]          // array of duplicated IDs
})

const hasDuplicate = computed(() => duplicates.value.length > 0)

/* ------- helper to highlight a <select> that contains a duplicate ------- */
function isDuplicate(id) {
  return duplicates.value.includes(id)
}
</script>
<template>
  <AuthenticatedLayout>
    <Head title="Créer un menu collectivité" />
    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Créer un menu collectivité</h1>
      <p class="text-sm text-gray-600 mt-1">Remplissez le formulaire ci-dessous puis enregistrez.</p>
    </div>
    
    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-6">
    <div class="flex flex-col lg:flex-row gap-4">
      <div class="space-y-6">
        <!-- Section 1 : infos générales -->
        <div class="bg-white">
          <h2 class="text-lg font-semibold mb-4">Informations générales</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 shadow rounded-lg p-6">
            <!-- Date -->
            <div>
              <InputLabel for="date" value="Date du menu" />
              <input
                id="date"
                v-model="form.date"
                type="date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <InputError :message="form.errors.date" />
            </div>

            <!-- Responsable -->
            <div>
              <InputLabel for="responsable" value="Responsable" />
              <input
                id="responsable"
                v-model="form.responsable"
                type="text"
                required
                placeholder="Nom du chef / responsable"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <InputError :message="form.errors.responsable" />
            </div>

            <!-- Effectif petit-dejeuner -->
            <div>
              <InputLabel for="effectif_petit_dejeuner" value="Effectif petit-déjeuner" />
              <input
                id="effectif_petit_dejeuner"
                v-model.number="form.effectif_petit_dejeuner"
                type="number"
                min="1"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <InputError :message="form.errors.effectif_petit_dejeuner" />
            </div>

            <!-- Effectif dejeuner -->
            <div>
              <InputLabel for="effectif_dejeuner" value="Effectif déjeuner" />
              <input
                id="effectif_dejeuner"
                v-model.number="form.effectif_dejeuner"
                type="number"
                min="1"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <InputError :message="form.errors.effectif_dejeuner" />
            </div>

            <!-- Effectif diner -->
            <div>
              <InputLabel for="effectif_diner" value="Effectif dîner" />
              <input
                id="effectif_diner"
                v-model.number="form.effectif_diner"
                type="number"
                min="1"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <InputError :message="form.errors.effectif_diner" />
            </div>
          </div>
        </div>

        <!-- Section 2 : choix des plats par catégorie / service -->
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
      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <button
          type="button"
          variant="outline"
          @click="close"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Annuler
        </button>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" :disabled="form.processing || hasDuplicate">Enregistrer le menu</button>
      </div>
    </form>
  </AuthenticatedLayout>
</template>