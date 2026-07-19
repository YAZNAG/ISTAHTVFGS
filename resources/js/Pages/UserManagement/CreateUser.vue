<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { EyeIcon, EyeSlashIcon, UserPlusIcon } from '@heroicons/vue/24/outline'
import { Modal } from '@inertiaui/modal-vue';

const props = defineProps({ roles: Array });

const createUserModal = ref(null);
const showPassword = ref(false);

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: '',
  status: true,
})

const submit = () => {
  form.post(route('users.store'), {
    onSuccess: () => createUserModal.value.close(),
  })
}
</script>

<template>
  <Modal ref="createUserModal" class="w-full max-w-lg">
    <div class="mb-5 border-b border-slate-100 pb-4">
      <h2 class="flex items-center gap-2 text-lg font-bold text-istaht-navy">
        <UserPlusIcon class="h-5 w-5" />
        Créer un utilisateur
      </h2>
      <p class="mt-1 text-sm text-slate-500">Renseignez les informations du compte et son rôle.</p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nom *</label>
        <input v-model="form.name" type="text" class="ui-input w-full" required />
        <div v-if="form.errors.name" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.name }}</div>
      </div>

      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Email *</label>
        <input v-model="form.email" type="email" class="ui-input w-full" required />
        <div v-if="form.errors.email" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.email }}</div>
      </div>

      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Mot de passe *</label>
        <div class="relative">
          <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="ui-input w-full pr-10" required />
          <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
            <component :is="showPassword ? EyeSlashIcon : EyeIcon" class="h-5 w-5" />
          </button>
        </div>
        <div v-if="form.errors.password" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.password }}</div>
      </div>

      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Rôle *</label>
        <select v-model="form.role" class="ui-input w-full">
          <option value="" disabled>— Choisir un rôle —</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
        </select>
        <div v-if="form.errors.role" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.role }}</div>
      </div>

      <label class="flex items-center gap-2">
        <input id="status" type="checkbox" v-model="form.status" class="h-4 w-4 rounded border-slate-300 text-istaht-blue" />
        <span class="text-sm font-semibold text-slate-700">Activer le compte</span>
      </label>
    </form>

    <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
      <button type="button" @click="createUserModal.close()" class="ui-button ui-button-ghost">Annuler</button>
      <button type="button" @click="submit" :disabled="form.processing" class="ui-button ui-button-primary disabled:opacity-50">
        {{ form.processing ? 'Création…' : 'Créer l\'utilisateur' }}
      </button>
    </div>
  </Modal>
</template>
