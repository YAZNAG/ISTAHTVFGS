<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ModalLink } from '@inertiaui/modal-vue'
import { ref } from 'vue'
import { onClickOutside } from '@vueuse/core'
import {
  PlusIcon, EllipsisVerticalIcon, ShieldCheckIcon, PencilIcon, TrashIcon, KeyIcon, UsersIcon,
} from '@heroicons/vue/24/outline'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import { usePermission } from '@/Utils/permission'

const { can, canAny } = usePermission();

const props = defineProps({
  roles: { type: Array, default: () => [] }
})

const openDropdown = ref(null)
const toggleDropdown = (roleId) => { openDropdown.value = openDropdown.value === roleId ? null : roleId }
const closeDropdown = () => { openDropdown.value = null }

const setupDropdownRef = (element, roleId) => {
  if (!element) return
  const toggleButton = element.parentElement?.querySelector('button')
  onClickOutside(element, () => { if (openDropdown.value === roleId) closeDropdown() }, { ignore: toggleButton ? [toggleButton] : [] })
}

const showDeleteModal = ref(false);
const roleToDelete = ref(null);
function openDeleteModal(id) { roleToDelete.value = id; showDeleteModal.value = true; closeDropdown() }
function deleteRole() {
  router.delete(route('roles.destroy', roleToDelete.value), {
    preserveScroll: true,
    onSuccess: () => (showDeleteModal.value = false),
  })
}

function initials(name) { return (name || '?').trim().charAt(0).toUpperCase() }
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Rôles et permissions" />

    <section class="space-y-5">

      <!-- ═══ En-tête ═══ -->
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-istaht-navy">
              <ShieldCheckIcon class="h-6 w-6" />
              Rôles &amp; permissions
            </h2>
            <p class="mt-1 text-sm text-slate-500">Définissez les rôles et les droits d'accès de chaque profil utilisateur.</p>
          </div>
          <ModalLink v-if="can('create_roles')" :href="route('roles.create')" class="ui-button ui-button-primary">
            <PlusIcon class="mr-1.5 h-4 w-4" /> Ajouter un rôle
          </ModalLink>
        </div>
      </div>

      <!-- ═══ Grille des rôles ═══ -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="role in roles" :key="role.id" class="flex flex-col rounded-lg border border-slate-200 bg-white p-5 shadow-soft">
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-purple-50 text-purple-700 ring-1 ring-purple-100">
                <ShieldCheckIcon class="h-6 w-6" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-istaht-navy">{{ role.name }}</h3>
                <span class="mt-0.5 inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-istaht-blue ring-1 ring-blue-100">
                  <KeyIcon class="h-3 w-3" /> {{ role.permissions_count }} permission(s)
                </span>
              </div>
            </div>

            <div v-if="canAny(['edit_roles', 'delete_roles'])" class="relative">
              <button @click="toggleDropdown(role.id)" class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <EllipsisVerticalIcon class="h-5 w-5" />
              </button>
              <div v-if="openDropdown === role.id" :ref="el => setupDropdownRef(el, role.id)"
                class="absolute right-0 z-20 mt-1 w-44 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-panel">
                <ModalLink v-if="can('edit_roles')" :href="route('roles.edit', role.id)" @click="closeDropdown"
                  class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-blue-50 hover:text-istaht-blue">
                  <PencilIcon class="h-4 w-4" /> Modifier le rôle
                </ModalLink>
                <button v-if="can('delete_roles')" @click="openDeleteModal(role.id)"
                  class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm font-medium text-istaht-red transition hover:bg-red-50">
                  <TrashIcon class="h-4 w-4" /> Supprimer le rôle
                </button>
              </div>
            </div>
          </div>

          <!-- Utilisateurs -->
          <div class="mt-4 border-t border-slate-100 pt-4">
            <div class="mb-3 flex items-center justify-between">
              <span class="flex items-center gap-1.5 text-sm font-bold text-slate-600">
                <UsersIcon class="h-4 w-4" /> Utilisateurs
              </span>
              <span v-if="role.userStats.count > 0" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">
                +{{ role.userStats.count }} autres
              </span>
            </div>
            <div class="flex flex-wrap gap-1.5">
              <div v-for="user in role.userStats.users" :key="user.name" :title="user.name"
                class="flex h-7 w-7 items-center justify-center rounded-full bg-istaht-blue text-xs font-bold text-white ring-2 ring-white">
                {{ initials(user.name) }}
              </div>
              <p v-if="role.userStats.users.length === 0" class="w-full py-1 text-center text-sm italic text-slate-400">
                Aucun utilisateur assigné
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Vide -->
      <div v-if="roles.length === 0" class="rounded-lg border border-slate-200 bg-white py-16 text-center shadow-soft">
        <ShieldCheckIcon class="mx-auto h-12 w-12 text-slate-300" />
        <h3 class="mt-3 text-sm font-bold text-istaht-navy">Aucun rôle configuré</h3>
        <p class="mt-1 text-sm text-slate-500">Créez un premier rôle et attribuez-lui des permissions.</p>
        <div class="mt-5">
          <ModalLink v-if="can('create_roles')" :href="route('roles.create')" class="ui-button ui-button-primary">
            <PlusIcon class="mr-1.5 h-4 w-4" /> Créer un rôle
          </ModalLink>
        </div>
      </div>
    </section>

    <ConfirmationModal
      :show="showDeleteModal"
      title="Supprimer ce rôle ?"
      message="Cette action est irréversible. Le rôle sera supprimé et retiré de tous les utilisateurs concernés."
      :onConfirm="deleteRole"
      @close="showDeleteModal = false"
    />
  </AuthenticatedLayout>
</template>
