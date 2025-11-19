<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  permission_groups: Array,
})

const createRoleModal = ref(null)

const form = useForm({
  name: '',
  permissions: [],
})

function submit() {
  form.post(route('roles.store'), {
    onSuccess: () => {
      form.reset()
      createRoleModal.value.close()
    },
  })
}

// Helper function to get permission IDs for a group
function getGroupPermissionIds(group) {
  return group.permissions.map(p => p.id)
}

// Check if all permissions in a group are selected
function isAllSelected(group) {
  const ids = getGroupPermissionIds(group)
  return ids.length > 0 && ids.every(id => form.permissions.includes(id))
}

// Check if some (but not all) permissions in a group are selected
function isSomeSelected(group) {
  const ids = getGroupPermissionIds(group)
  const hasSelected = ids.some(id => form.permissions.includes(id))
  return hasSelected && !isAllSelected(group)
}

// Toggle all permissions for a group
function toggleAllPermissions(group) {
  const groupIds = getGroupPermissionIds(group)
  const currentlySelected = form.permissions.filter(id => groupIds.includes(id))
  
  if (currentlySelected.length === groupIds.length) {
    // All are selected, so remove them all
    form.permissions = form.permissions.filter(id => !groupIds.includes(id))
  } else {
    // Not all selected, so add all (avoiding duplicates)
    const newPermissions = [...form.permissions]
    groupIds.forEach(id => {
      if (!newPermissions.includes(id)) {
        newPermissions.push(id)
      }
    })
    form.permissions = newPermissions
  }
}
</script>

<template>
  <Modal ref="createRoleModal">
    <!-- Header -->
    <div class="mb-4">
      <h2 class="text-lg font-semibold">Nouveau Rôle</h2>
    </div>

    <!-- Body -->
    <form @submit.prevent="submit" class="space-y-4">
      <!-- Role Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
          Nom du rôle
        </label>
        <input 
          id="name" 
          v-model="form.name" 
          type="text" 
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500  sm:text-sm"
          :class="{ 'border-red-500': form.errors.name }"
          placeholder="Entrez le nom du rôle"
          required
        >
        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
          {{ form.errors.name }}
        </div>
      </div>

      <!-- Permissions Cards -->
      <div>
        <h3 class="block text-sm font-medium text-gray-700 mb-3">
          Permissions
        </h3>

        <div class="space-y-4 max-h-96 overflow-y-auto">
          <div 
            v-for="group in permission_groups" 
            :key="group.display_name"
            class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"
          >
            <!-- Group Title with Select All -->
            <h4 class="font-semibold text-gray-900 mb-3 flex items-center justify-between">
              <span>
                <span class="text-blue-600 mr-2">▸</span>
                {{ group.display_name }}
              </span>
              <label class="flex items-center text-sm font-normal cursor-pointer select-none">
                <input 
                  type="checkbox" 
                  :checked="isAllSelected(group)"
                  :indeterminate="isSomeSelected(group)"
                  @change="toggleAllPermissions(group)"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
                >
                <span class="text-gray-600 hover:text-gray-900">Tout sélectionner</span>
              </label>
            </h4>
            
            <!-- Permissions Checkboxes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6">
              <div 
                v-for="permission in group.permissions" 
                :key="permission.id"
                class="flex items-center"
              >
                <input 
                  type="checkbox" 
                  v-model="form.permissions" 
                  :value="permission.id"
                  :id="'permission-' + permission.id"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600"
                >
                <label 
                  :for="'permission-' + permission.id"
                  class="ml-2 text-sm text-gray-700 hover:text-gray-900 cursor-pointer"
                >
                  {{ permission.display_name }}
                </label>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="form.errors.permissions" class="mt-2 text-sm text-red-600">
          {{ form.errors.permissions }}
        </div>
      </div>
    </form>

    <!-- Footer -->
    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
      <button
        type="button"
        @click="createRoleModal.close()"
        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
      >
        Annuler
      </button>
      <button
        type="button"
        @click="submit"
        :disabled="form.processing"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        Enregistrer
      </button>
    </div>
  </Modal>
</template>