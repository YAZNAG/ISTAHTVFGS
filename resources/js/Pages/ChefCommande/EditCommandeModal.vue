<script setup>
import { ref, computed } from 'vue'
import { Modal } from '@inertiaui/modal-vue'
import { useForm } from '@inertiajs/vue3'
import { XMarkIcon, MagnifyingGlassIcon, PlusCircleIcon, CubeIcon } from '@heroicons/vue/24/outline'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  chefCommande: Object,
  articles: Array,
  categories: Array,
  users: Array
})

const search = ref('')
const dropdownOpen = ref(false)
const editCommandeModal = ref(null)

const form = useForm({
  user_id: props.chefCommande.user_id,
  categorie_id: props.chefCommande.categorie_id,
  articles: props.chefCommande.items.map(item => ({
    article_id: item.article.id,
    designation: item.article.designation,
    quantite_commandee: item.quantite,
    unite_mesure: item.article.unite_mesure
  })),
  note: props.chefCommande.note || ''
})

const articles = computed(() => {
  return props.articles.filter(a => Number(a.categorie_id) === Number(form.categorie_id))
})

const filteredArticles = computed(() => {
  return articles.value
    .filter(a => !form.articles.find(fa => fa.article_id === a.id))
    .filter(a => !search.value || a.designation.toLowerCase().includes(search.value.toLowerCase()))
})

function selectArticle(article) {
  form.articles.push({
    article_id: article.id,
    designation: article.designation,
    quantite_commandee: 1,
    unite_mesure: article.unite_mesure
  })
  search.value = ''
  dropdownOpen.value = false
}

function removeArticle(index) {
  form.articles.splice(index, 1)
}

function incrementQty(item) {
  item.quantite_commandee = Number(item.quantite_commandee || 0) + 1
}

function decrementQty(item) {
  if (Number(item.quantite_commandee) > 1) {
    item.quantite_commandee = Number(item.quantite_commandee) - 1
  }
}

function submit() {
  form.put(route('chef-commandes.update', props.chefCommande.id), {
    preserveScroll: true,
    onSuccess: () => {
      editCommandeModal.value.close()
    },
  })
}

function closeIdle() {
  setTimeout(() => dropdownOpen.value = false, 300)
}

function onCategorieChange() {
  form.articles = [];
}
</script>

<template>
  <Modal ref="editCommandeModal">
    <!-- ═══ En-tête ═══ -->
    <div class="mb-5 border-b border-slate-100 pb-4">
      <div class="flex items-center gap-2">
        <h2 class="text-lg font-bold text-istaht-navy">Modifier le bon</h2>
        <span class="rounded-full bg-blue-50 px-2.5 py-0.5 font-mono text-sm font-bold text-istaht-blue ring-1 ring-blue-100">
          {{ chefCommande.numero }}
        </span>
      </div>
      <p class="mt-1 text-sm text-slate-500">
        Créé le {{ chefCommande.created_at }} — modifiez la catégorie, les articles ou la note.
      </p>
    </div>

    <form class="space-y-4" @submit.prevent>
      <!-- Chef -->
      <div v-if="$page.props.auth.user.role == 'ADMIN'">
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Chef demandeur</label>
        <select v-model="form.user_id" class="ui-input w-full">
          <option value="">Choisissez un chef</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
        <InputError :message="form.errors.user_id" class="mt-1" />
      </div>

      <!-- Catégorie -->
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Catégorie *</label>
        <select v-model="form.categorie_id" class="ui-input w-full" @change="onCategorieChange">
          <option value="">Sélectionner une catégorie</option>
          <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
            {{ categorie.nom }}
          </option>
        </select>
        <InputError :message="form.errors.categorie_id" class="mt-1" />
      </div>

      <!-- Articles -->
      <div>
        <div class="mb-1.5 flex items-center justify-between">
          <label class="block text-xs font-bold uppercase text-slate-500">Articles commandés *</label>
          <span
            v-if="form.articles.length"
            class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-istaht-blue ring-1 ring-blue-100"
          >
            <CubeIcon class="h-3.5 w-3.5" />
            {{ form.articles.length }} article(s)
          </span>
        </div>

        <p
          v-if="$page.props.errors.articlesError"
          v-html="$page.props.errors.articlesError"
          class="mb-2 rounded-md border border-red-100 bg-red-50 px-3 py-2 text-sm text-istaht-red"
        />

        <div class="relative mb-2">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Rechercher un article à ajouter..."
              @focus="dropdownOpen = true"
              @blur="closeIdle"
              class="ui-input w-full pl-9"
            />
            <MagnifyingGlassIcon class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          </div>

          <div v-if="form.errors.articles" class="mt-1 text-sm font-semibold text-istaht-red">
            {{ form.errors.articles }}
          </div>

          <!-- Dropdown résultats -->
          <ul
            v-if="dropdownOpen && filteredArticles.length"
            class="absolute z-20 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-panel"
          >
            <li
              v-for="article in filteredArticles"
              :key="article.id"
              @click="selectArticle(article)"
              class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm transition hover:bg-blue-50"
            >
              <span class="font-semibold text-slate-700">{{ article.designation }}</span>
              <PlusCircleIcon class="h-4 w-4 text-istaht-blue" />
            </li>
          </ul>
        </div>

        <!-- Tableau articles sélectionnés -->
        <div class="overflow-hidden rounded-lg border border-slate-200">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 text-left">
                <th class="px-3 py-2 text-xs font-bold uppercase text-slate-500">Article</th>
                <th class="w-40 px-3 py-2 text-center text-xs font-bold uppercase text-slate-500">Quantité</th>
                <th class="w-12 px-3 py-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(item, index) in form.articles" :key="item.article_id" class="hover:bg-slate-50">
                <td class="px-3 py-2 font-semibold text-slate-700">{{ item.designation }}</td>
                <td class="px-3 py-2">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      type="button"
                      class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 text-slate-600 transition hover:bg-slate-100"
                      @click="decrementQty(item)"
                    >−</button>
                    <input
                      type="number"
                      min="1"
                      v-model.number="item.quantite_commandee"
                      class="ui-input w-16 px-1 py-1 text-center"
                    />
                    <button
                      type="button"
                      class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 text-slate-600 transition hover:bg-slate-100"
                      @click="incrementQty(item)"
                    >+</button>
                    <span class="ml-1 text-xs text-slate-500">{{ item.unite_mesure }}</span>
                  </div>
                </td>
                <td class="px-3 py-2 text-center">
                  <button
                    type="button"
                    @click="removeArticle(index)"
                    class="rounded-md p-1 text-istaht-red transition hover:bg-red-50"
                    title="Retirer l'article"
                  >
                    <XMarkIcon class="h-4 w-4" />
                  </button>
                </td>
              </tr>
              <tr v-if="form.articles.length === 0">
                <td colspan="3" class="px-3 py-6 text-center text-sm text-slate-400">
                  Aucun article ajouté — utilisez la recherche ci-dessus.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Note -->
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">
          Note <span class="font-normal normal-case text-slate-400">(optionnel)</span>
        </label>
        <textarea
          v-model="form.note"
          class="ui-input w-full"
          rows="2"
          placeholder="Précisions éventuelles pour la validation..."
        ></textarea>
      </div>
    </form>

    <!-- ═══ Pied ═══ -->
    <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
      <button
        type="button"
        @click="editCommandeModal.close()"
        class="ui-button ui-button-ghost"
      >
        Annuler
      </button>
      <button
        type="button"
        @click="submit"
        :disabled="form.processing"
        class="ui-button ui-button-primary disabled:opacity-50"
      >
        {{ form.processing ? 'Mise à jour...' : 'Mettre à jour' }}
      </button>
    </div>
  </Modal>
</template>
