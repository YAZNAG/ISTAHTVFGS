<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import { ref } from 'vue'
import { onClickOutside } from '@vueuse/core'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({
  roles: {
    type: Array,
    default: () => []
  }
})

// Track which dropdown is open
const openDropdown = ref(null)

// Store dropdown element references
const dropdownRefs = ref({})

const toggleDropdown = (roleId) => {
  openDropdown.value = openDropdown.value === roleId ? null : roleId
}

const closeDropdown = () => {
  openDropdown.value = null
}

// Setup click outside handler for each dropdown
const setupDropdownRef = (element, roleId) => {
  if (!element) return

  // Find the toggle button to ignore clicks on it
  const toggleButton = element.parentElement?.querySelector('button')

  onClickOutside(
    element,
    () => {
      if (openDropdown.value === roleId) {
        closeDropdown()
      }
    },
    {
      ignore: toggleButton ? [toggleButton] : []
    }
  )
}

const showDeleteModal = ref(false);
const roleToDelete = ref(false);
function openDeleteModal(id) {
  roleToDelete.value = id
  showDeleteModal.value = true
}


const deleteRole = (role) => {
  const roleId = roleToDelete.value;
  router.delete(route('roles.destroy', roleId), {
    preserveScroll: true,
  })

}
</script>

<template>
  <AuthenticatedLayout>

    <Head title="Gestion des Rôles et Permissions" />

    <div class="space-y-6 p-4">
      <!-- Page Header -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl p-6 text-white">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
          <div class="flex-1">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">Gestion des Rôles</h1>
            <p class="text-blue-100 text-lg opacity-90">
              Créez et configurez les rôles utilisateur
            </p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <ModalLink :href="route('roles.create')"
              class="bg-white text-blue-600 px-6 py-3 rounded-xl hover:bg-blue-50 flex items-center justify-center gap-3 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
              </svg>
              Ajouter un rôle
            </ModalLink>
          </div>
        </div>
      </div>

      <!-- Roles Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="role in roles" :key="role.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition p-6">
          <!-- Card Header -->
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ role.name }}</h3>
              <span
                class="inline-block mt-1 px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-md border border-blue-200">
                {{ role.permissions_count }} permissions
              </span>
            </div>

            <!-- Dropdown Actions -->
            <div class="relative">
              <button @click="toggleDropdown(role.id)"
                class="text-gray-400 hover:text-gray-600 p-1 hover:bg-gray-50 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div v-if="openDropdown === role.id" :ref="el => setupDropdownRef(el, role.id)"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10 overflow-hidden">
                <ModalLink :href="route('roles.edit', role.id)"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                  Modifier le rôle
                </ModalLink>
                <button @click="openDeleteModal(role.id)"
                  class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                  Supprimer le rôle
                </button>
              </div>
            </div>
          </div>

          <!-- Users Section -->
          <div class="mt-4 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm font-semibold text-gray-600">Utilisateurs</span>
              <span v-if="role.userStats.count > 0"
                class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full font-medium">
                +{{ role.userStats.count }} autres
              </span>
            </div>

            <div class="flex flex-wrap gap-1">
              <div v-for="user in role.userStats.users" :key="user.name" class="relative group">
                <!-- Avatar with Tooltip -->
                <div :title="user.name"
                  class="w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 cursor-pointer hover:bg-blue-700 transition-colors">
                  <span class="text-white text-xs font-medium">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <!-- Custom tooltip for better styling -->
                <div
                  class="absolute z-10 bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                  {{ user.name }}
                </div>
              </div>

              <!-- Empty State -->
              <p v-if="role.userStats.users.length === 0" class="text-sm text-gray-400 italic w-full text-center py-2">
                Aucun utilisateur assigné
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="roles.length === 0" class="text-center py-16">
        <div
          class="w-20 h-20 bg-gradient-to-br from-blue-50 to-purple-50 rounded-full mx-auto flex items-center justify-center">
          <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
        <h3 class="mt-4 text-xl font-semibold text-gray-800">Aucun rôle configuré</h3>
        <div class="mt-6">
          <ModalLink :href="route('roles.create')"
            class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:from-blue-700 hover:to-purple-800 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl inline-flex items-center gap-3">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
            Créer un rôle
          </ModalLink>
        </div>
      </div>
    </div>

    <ConfirmationModal
      :show="showDeleteModal"
      title="Êtes-vous sûr(e) de vouloir supprimer ce rôle ?"
      message="Cette action est irréversible. Le rôle sera supprimé et retiré de tous les utilisateurs concernés "
      :onConfirm="deleteRole"
      @close="showDeleteModal = false"
    />
  </AuthenticatedLayout>
</template>