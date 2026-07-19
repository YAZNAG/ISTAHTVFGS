<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
  MagnifyingGlassIcon, CheckCircleIcon, ArrowLeftIcon, ArrowRightIcon,
  LockClosedIcon, ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/outline';
import InputError from '@/Components/InputError.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({ inventaire: Object });

// Copie LOCALE reactive des lignes : les props Vue 3 sont en lecture seule,
// muter props.inventaire.lignes[i] echoue silencieusement (progression jamais a 100%,
// bouton Finaliser bloque). On mute cette copie a la place.
const lignes = ref((props.inventaire.lignes ?? []).map(l => ({ ...l })));

const form = useForm({ ligne_id: null, stock_reel: null, observations: '' });

const search = ref('');
const currentIndex = ref(0);
const current = computed(() => lignes.value[currentIndex.value]);

watch(current, l => {
  if (!l) return;
  form.ligne_id = l.id;
  form.stock_reel = l.stock_reel;
  form.observations = l.observations ?? '';
}, { immediate: true });

const filtered = computed(() => {
  const q = search.value.toLowerCase();
  return lignes.value.filter(l =>
    (l.code_article ?? '').toLowerCase().includes(q) || (l.designation ?? '').toLowerCase().includes(q));
});

const ecartPreview = computed(() => {
  if (form.stock_reel === null || form.stock_reel === '' || !current.value) return null;
  return Number(form.stock_reel) - Number(current.value.stock_theorique);
});

function selectLine(l) {
  const idx = lignes.value.findIndex(x => x.id === l.id);
  if (idx !== -1) currentIndex.value = idx;
}

function saveAndNext() {
  form.patch(route('inventaires.ligne.update', form.ligne_id), {
    preserveScroll: true,
    onSuccess: () => {
      const idx = lignes.value.findIndex(l => l.id === form.ligne_id);
      if (idx !== -1) {
        lignes.value[idx].stock_reel = form.stock_reel;
        lignes.value[idx].observations = form.observations;
        lignes.value[idx].ecart = Number(form.stock_reel) - Number(lignes.value[idx].stock_theorique);
      }
      currentIndex.value = (currentIndex.value + 1) % lignes.value.length;
    },
  });
}

function prev() { currentIndex.value = (currentIndex.value - 1 + lignes.value.length) % lignes.value.length; }
function skip() { currentIndex.value = (currentIndex.value + 1) % lignes.value.length; }

const total = computed(() => lignes.value.length);
const filled = computed(() => lignes.value.filter(l => l.stock_reel !== null && l.stock_reel !== '').length);
const percent = computed(() => total.value ? Math.round(filled.value / total.value * 100) : 0);

const showConfirmModal = ref(false);
function finalize() { router.patch(route('inventaires.finalize', props.inventaire.id)); }
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="'Inventaire ' + inventaire.semaine" />

    <section class="space-y-5">

      <!-- ═══ En-tête + progression ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <div class="flex items-center gap-2">
              <p class="font-mono text-sm font-bold text-istaht-blue">{{ inventaire.semaine }}</p>
              <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-istaht-amber ring-1 ring-amber-100">Brouillon</span>
            </div>
            <h2 class="mt-2 flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <ClipboardDocumentCheckIcon class="h-6 w-6" />
              Saisie de l'inventaire
            </h2>
          </div>
          <Link :href="route('inventaires.index')" class="ui-button ui-button-ghost">
            <ArrowLeftIcon class="mr-1.5 h-4 w-4" /> Retour liste
          </Link>
        </div>

        <div class="mt-4">
          <div class="mb-1 flex items-center justify-between text-sm">
            <span class="font-semibold text-slate-600">Progression</span>
            <span class="font-bold text-istaht-navy">{{ filled }} / {{ total }} · {{ percent }} %</span>
          </div>
          <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full transition-all" :class="percent === 100 ? 'bg-istaht-green' : 'bg-istaht-blue'" :style="{ width: percent + '%' }" />
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        <!-- ═══ Carte de saisie ═══ -->
        <div class="lg:col-span-2">
          <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="font-bold text-istaht-navy">Article {{ currentIndex + 1 }} / {{ total }}</h3>
              <div class="flex gap-1">
                <button type="button" class="rounded-md border border-slate-200 p-1.5 text-slate-500 hover:bg-slate-50" title="Précédent" @click="prev"><ArrowLeftIcon class="h-4 w-4" /></button>
                <button type="button" class="rounded-md border border-slate-200 p-1.5 text-slate-500 hover:bg-slate-50" title="Suivant" @click="skip"><ArrowRightIcon class="h-4 w-4" /></button>
              </div>
            </div>

            <div v-if="current" class="space-y-4">
              <div class="rounded-lg border border-slate-100 bg-slate-50 p-4">
                <p class="font-mono text-sm font-bold text-istaht-blue">{{ current.code_article }}</p>
                <p class="mt-0.5 text-lg font-bold text-istaht-navy">{{ current.designation }}</p>
                <p class="text-sm text-slate-500">Unité : {{ current.unite_mesure }}</p>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                  <p class="text-xs font-bold uppercase text-slate-400">Stock théorique</p>
                  <p class="mt-1 text-xl font-bold text-istaht-navy">{{ current.stock_theorique }}</p>
                </div>
                <div>
                  <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Stock réel *</label>
                  <input v-model.number="form.stock_reel" type="number" step="0.01" class="ui-input w-full" @keyup.enter="saveAndNext" />
                  <InputError :message="form.errors.stock_reel" />
                </div>
                <div class="rounded-lg border p-3" :class="ecartPreview === null ? 'border-slate-100 bg-slate-50' : (ecartPreview > 0 ? 'border-green-100 bg-green-50' : (ecartPreview < 0 ? 'border-red-100 bg-red-50' : 'border-slate-100 bg-slate-50'))">
                  <p class="text-xs font-bold uppercase text-slate-400">Écart</p>
                  <p class="mt-1 text-xl font-bold" :class="ecartPreview === null ? 'text-slate-400' : (ecartPreview > 0 ? 'text-istaht-green' : (ecartPreview < 0 ? 'text-istaht-red' : 'text-slate-500'))">
                    {{ ecartPreview === null ? '—' : (ecartPreview > 0 ? '+' : '') + ecartPreview.toFixed(2) }}
                  </p>
                </div>
              </div>

              <div>
                <label class="mb-1 block text-xs font-bold uppercase text-slate-400">Observations</label>
                <input v-model="form.observations" type="text" placeholder="Facultatif…" class="ui-input w-full" />
              </div>

              <div class="flex justify-end gap-2 pt-1">
                <button type="button" class="ui-button ui-button-ghost" @click="skip">Passer</button>
                <button type="button" class="ui-button ui-button-primary disabled:opacity-50" :disabled="form.processing || form.stock_reel === null || form.stock_reel === ''" @click="saveAndNext">
                  <CheckCircleIcon class="mr-1.5 h-4 w-4" /> Enregistrer &amp; suivant
                </button>
              </div>
            </div>
          </div>

          <div class="mt-4 flex flex-col items-end gap-2">
            <p v-if="filled < total" class="text-xs text-slate-500">
              {{ total - filled }} article(s) non saisi(s) — ils seront comptés à <strong class="text-istaht-navy">0</strong> à la clôture.
            </p>
            <button
              type="button"
              class="ui-button ui-button-primary"
              @click="showConfirmModal = true"
            >
              <LockClosedIcon class="mr-1.5 h-4 w-4" />
              Clôturer l'inventaire
            </button>
          </div>
        </div>

        <!-- ═══ Liste des articles ═══ -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-soft">
          <div class="border-b border-slate-100 p-3">
            <div class="relative">
              <input v-model="search" type="text" placeholder="Rechercher…" class="ui-input w-full pl-9" />
              <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
          </div>
          <ul class="max-h-[26rem] divide-y divide-slate-100 overflow-y-auto">
            <li
              v-for="l in filtered"
              :key="l.id"
              class="flex cursor-pointer items-center justify-between px-4 py-2.5 text-sm transition hover:bg-slate-50"
              :class="current && l.id === current.id ? 'bg-blue-50' : ''"
              @click="selectLine(l)"
            >
              <div class="min-w-0">
                <p class="font-mono text-xs font-bold text-istaht-blue">{{ l.code_article }}</p>
                <p class="truncate text-slate-700">{{ l.designation }}</p>
              </div>
              <CheckCircleIcon v-if="l.stock_reel !== null" class="ml-2 h-5 w-5 shrink-0 text-istaht-green" />
              <span v-else class="ml-2 h-2 w-2 shrink-0 rounded-full bg-slate-300" />
            </li>
          </ul>
        </div>
      </div>
    </section>

    <ConfirmationModal
      :show="showConfirmModal"
      type="warning"
      title="Clôturer l'inventaire"
      :message="`Les articles non saisis (${total - filled}) seront comptés à 0. Une fois clôturé, l'inventaire est verrouillé et ne peut plus être modifié. Confirmer ?`"
      :onConfirm="finalize"
      @close="showConfirmModal = false"
    />
  </AuthenticatedLayout>
</template>
