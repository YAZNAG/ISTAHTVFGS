<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { EyeIcon, EyeSlashIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { Modal } from '@inertiaui/modal-vue';

const props = defineProps({
  roles: Array
});

// Props from InertiaUI modal
const createUserModal = ref(null);
const showPassword = ref(false);
// Create user form
const form = useForm({
  name: '',
  email: '',
  password: '',
  role: '',
  status: true,
})

// Submit handler
const submit = () => {
  form.post(route('users.store'), {
    onSuccess: () => {
      createUserModal.value.close() // close the modal on success
    },
  })

  console.log(form.data());

}
</script>

<template>
  <Modal ref='createUserModal' class="max-w-lg w-full z-50">
    <div class="mb-4">
      <div class="flex justify-between items-start">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Créer un utilisateur</h2>
          <p class="mt-1 text-sm text-gray-500">
            Remplissez les informations ci-dessous pour ajouter un nouvel utilisateur au système.
          </p>
        </div>
      </div>
    </div>

    <div>
      <form @submit.prevent="submit" class="space-y-4">
        <!-- Nom -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Nom</label>
          <input v-model="form.name" type="text" class="w-full border-gray-300 rounded-lg p-2 mt-1" required />
          <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
            {{ form.errors.name }}
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" class="w-full border-gray-300 rounded-lg p-2 mt-1" required />
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <!-- Mot de passe -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
          <div class="relative">
            <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
              class="w-full border-gray-300 rounded-lg p-2 mt-1 pr-10" required />
            <!-- Toggle button -->
            <button type="button" @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
              <component :is="showPassword ? EyeSlashIcon : EyeIcon" class="h-5 w-5" />
            </button>
          </div>
          <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <!-- Rôle -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Rôle</label>
          <select v-model="form.role" class="w-full border-gray-300 rounded-lg p-2 mt-1">
            <option value="" disabled>-- Choisir un rôle --</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }}
            </option>
          </select>
          <div v-if="form.errors.role" class="text-red-600 text-sm mt-1">
            {{ form.errors.role }}
          </div>
        </div>

        <!-- Statut -->
        <div class="flex items-center">
          <input id="status" type="checkbox" v-model="form.status"
            class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
          <label for="status" class="ml-2 block text-sm text-gray-700">
            Activer le compte
          </label>
        </div>
      </form>
    </div>

    <div>
      <div class="flex justify-end space-x-3">
        <button type="button" @click="createUserModal.close"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
          Annuler
        </button>
        <button type="button" @click="submit" :disabled="form.processing"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
          {{ form.processing ? 'Création...' : 'Créer' }}
        </button>
      </div>
    </div>
  </Modal>
</template>
