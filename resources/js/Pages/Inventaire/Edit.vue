<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { MagnifyingGlassIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';
import InputError from '@/Components/InputError.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
  inventaire: Object,          // header + ALL lines
});

/* ---------- form (only ONE line at a time) ---------- */
const form = useForm({
  ligne_id       : null,
  stock_reel     : null,
  observations   : '',
});

/* ---------- search ---------- */
const search      = ref('');
const dropdown    = ref(false);
const dropdownEl  = ref(null);              // root wrapper

onClickOutside(dropdownEl, () => (dropdown.value = false));

const filtered = computed(() =>
  props.inventaire.lignes.filter(l =>
    l.code_article.toLowerCase().includes(search.value.toLowerCase()) ||
    l.designation.toLowerCase().includes(search.value.toLowerCase())
  )
);

/* ---------- current line ---------- */
const currentIndex = ref(0);
const current = computed(() => props.inventaire.lignes[currentIndex.value]);

/* ---------- preload current into form ---------- */
watch(current, l => {
  form.ligne_id     = l.id;
  form.stock_reel   = l.stock_reel;
  form.observations = l.observations ?? '';
  search.value      = `${l.code_article} - ${l.designation}`;
}, { immediate: true });

/* ---------- save & advance ---------- */
function saveAndNext() {
  form.patch(route('inventaires.ligne.update', form.ligne_id), {
    preserveScroll: true,
    onSuccess: () => {
      /* mark local array (for progress) */
      const idx = props.inventaire.lignes.findIndex(l => l.id === form.ligne_id);
      props.inventaire.lignes[idx].stock_reel   = form.stock_reel;
      props.inventaire.lignes[idx].observations = form.observations;
      props.inventaire.lignes[idx].ecart        = form.stock_reel - props.inventaire.lignes[idx].stock_theorique;

      /* next article (loop back to 0 when finished) */
      currentIndex.value = (currentIndex.value + 1) % props.inventaire.lignes.length;
    },
  });
}

function skip() {
  currentIndex.value = (currentIndex.value + 1) % props.inventaire.lignes.length;
}

/* ---------- progress ---------- */
const total   = computed(() => props.inventaire.lignes.length);
const filled  = computed(() => props.inventaire.lignes.filter(l => l.stock_reel !== null).length);
const percent = computed(() => (filled.value / total.value * 100).toFixed(0));

/* ---------- finalize ---------- */
function finalize() {
  if (filled.value < total.value) {
    alert('Tous les stocks réels doivent être renseignés.');
    return;
  }

  router.patch(route('inventaires.finalize', props.inventaire.id));
}

function toggleDropdown() {
  search.value = '';
  dropdown.value = !dropdown.value;
}

const showConfirmModal = ref(false)

function openConfirmModal(id) {
  showConfirmModal.value = true
}

</script>

<template>
  <AuthenticatedLayout>
    <Head :title="'Inventaire ' + inventaire.mois" />

    <div class="space-y-6">
      <!-- ====== HEADER ====== -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex-1">
            <h1 class="text-3xl font-bold mb-2">Inventaire du mois</h1>
            <p class="text-blue-100 text-lg opacity-90">{{ inventaire.mois }}</p>
          </div>
        </div>
      </div>

      <!-- Progress badge -->
          <div class="w-full">
            <div class="flex items-center justify-between text-sm text-slate-800 mb-1">
              <span>Progression</span>
              <span class="font-semibold">{{ filled }} / {{ total }}</span>
            </div>
            <div class="w-full bg-blue-100 rounded-full h-2.5 shadow-inner">
              <div class="bg-blue-500 h-2.5 rounded-full transition-all duration-300" :style="{ width: percent + '%' }" />
            </div>
            <div class="text-right text-xs text-slate-800 mt-1">{{ percent }} %</div>
          </div>

      <!-- ====== ARTICLE SELECTOR CARD ====== -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Sélectionner un article</h2>

        <!-- Searchable dropdown -->
        <div ref="dropdownEl" class="relative max-w-xl">
          <div class="relative">
            <input
              v-model="search"
              @focus="dropdown = true"
              type="text"
              placeholder="Rechercher code ou désignation..."
              class="w-full border border-gray-300 rounded-lg pl-10 pr-10 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <MagnifyingGlassIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
            <ChevronDownIcon
              @click="toggleDropdown"
              class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 cursor-pointer"
            />
          </div>

          <!-- Dropdown list -->
          <ul
            v-if="dropdown && filtered.length"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
          >
            <li
              v-for="l in filtered"
              :key="l.id"
              @click="currentIndex = props.inventaire.lignes.indexOf(l); dropdown = false"
              class="px-4 py-2 hover:bg-blue-50 cursor-pointer flex justify-between"
            >
              <span>{{ l.code_article }}</span>
              <span class="text-gray-500 text-sm truncate ml-2">{{ l.designation }}</span>
            </li>
          </ul>
        </div>

        <!-- Current article card -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
          <div>
            <label class="block text-sm font-medium text-gray-700">Article</label>
            <div class="mt-1 text-lg font-semibold text-gray-900">{{ current.code_article }} - {{ current.designation }}</div>
            <div class="text-sm text-gray-500">Unité : {{ current.unite_mesure }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Stock théorique</label>
            <div class="mt-1 text-lg font-semibold text-blue-600">{{ current.stock_theorique }}</div>
          </div>

          <!-- Stock réel -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Stock réel <span class="text-red-500">*</span></label>
            <input
              v-model.number="form.stock_reel"
              type="number"
              step="0.01"
              class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <InputError class="mt-2" :message="form.errors.stock_reel" />
          </div>

          <!-- Observations -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Observations</label>
            <input
              v-model="form.observations"
              type="text"
              placeholder=""
              class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="skip"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Passer
          </button>
          <button
            @click="saveAndNext"
            :disabled="form.processing || form.stock_reel === null"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            Sauvegarder & suivant
          </button>
        </div>
      </div>

      <!-- ====== BOTTOM ACTIONS ====== -->
      <div class="flex justify-end gap-3">
        <Link :href="route('inventaires.index')" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
          Retour
        </Link>

        <button
          @click="openConfirmModal"
          :disabled="percent < 100"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          :class="{ 'opacity-50 cursor-not-allowed': percent < 100 }"
        >
          Finaliser
        </button>
      </div>
    </div>

    <ConfirmationModal
        :show="showConfirmModal"
        type="warning"
        title="Finaliser l’inventaire"
        message="Une fois finalisé, vous ne pourrez plus modifier les données. Êtes-vous sûr de vouloir finaliser cet inventaire ?"
        :onConfirm="finalize"
        @close="showConfirmModal = false"
    />
  </AuthenticatedLayout>
</template>