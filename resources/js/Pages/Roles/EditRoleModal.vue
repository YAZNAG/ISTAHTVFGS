<script setup>
import { ref } from 'vue'
import { Modal } from '@inertiaui/modal-vue';
import { useForm } from '@inertiajs/vue3';
import { PencilSquareIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  permission_groups: Array,
  role: Object,
})

const editRoleModal = ref(null)

const form = useForm({
  name: props.role.name,
  permissions: props.role.permissions || [],
})

function submit() {
  form.put(route('roles.update', props.role.id), {
    onSuccess: () => { form.reset(); editRoleModal.value.close() },
  })
}

const groupIds = (group) => group.permissions.map(p => p.id)
const isAllSelected = (group) => { const ids = groupIds(group); return ids.length > 0 && ids.every(id => form.permissions.includes(id)) }
const isSomeSelected = (group) => { const ids = groupIds(group); return ids.some(id => form.permissions.includes(id)) && !isAllSelected(group) }

function toggleAllPermissions(group) {
  const ids = groupIds(group)
  if (ids.every(id => form.permissions.includes(id))) {
    form.permissions = form.permissions.filter(id => !ids.includes(id))
  } else {
    form.permissions = [...new Set([...form.permissions, ...ids])]
  }
}
</script>

<template>
  <Modal ref="editRoleModal" class="w-full max-w-2xl">
    <div class="mb-5 border-b border-slate-100 pb-4">
      <h2 class="flex items-center gap-2 text-lg font-bold text-istaht-navy">
        <PencilSquareIcon class="h-5 w-5" />
        Modifier le rôle
      </h2>
      <p class="mt-1 text-sm text-slate-500">Ajustez le nom et les permissions accordées à ce rôle.</p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nom du rôle *</label>
        <input v-model="form.name" type="text" class="ui-input w-full" :class="{ 'border-red-500': form.errors.name }" required />
        <div v-if="form.errors.name" class="mt-1 text-sm font-semibold text-istaht-red">{{ form.errors.name }}</div>
      </div>

      <div>
        <div class="mb-2 flex items-center justify-between">
          <label class="text-xs font-bold uppercase text-slate-500">Permissions</label>
          <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">{{ form.permissions.length }} sélectionnée(s)</span>
        </div>

        <div class="max-h-96 space-y-3 overflow-y-auto pr-1">
          <div v-for="group in permission_groups" :key="group.display_name" class="rounded-lg border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between border-b border-slate-100 pb-2">
              <span class="text-sm font-bold text-istaht-navy">{{ group.display_name }}</span>
              <label class="flex cursor-pointer select-none items-center gap-2 text-xs font-semibold text-slate-500 hover:text-istaht-blue">
                <input type="checkbox" :checked="isAllSelected(group)" :indeterminate="isSomeSelected(group)" @change="toggleAllPermissions(group)" class="h-4 w-4 rounded border-slate-300 text-istaht-blue" />
                Tout sélectionner
              </label>
            </div>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
              <label v-for="permission in group.permissions" :key="permission.id" class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 text-sm text-slate-700 transition hover:bg-slate-50">
                <input type="checkbox" v-model="form.permissions" :value="permission.id" class="h-4 w-4 rounded border-slate-300 text-istaht-blue" />
                {{ permission.display_name }}
              </label>
            </div>
          </div>
        </div>
        <div v-if="form.errors.permissions" class="mt-2 text-sm font-semibold text-istaht-red">{{ form.errors.permissions }}</div>
      </div>
    </form>

    <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row">
      <button type="button" @click="editRoleModal.close()" class="ui-button ui-button-ghost">Annuler</button>
      <button type="button" @click="submit" :disabled="form.processing" class="ui-button ui-button-primary disabled:opacity-50">
        {{ form.processing ? 'Enregistrement…' : 'Mettre à jour' }}
      </button>
    </div>
  </Modal>
</template>
