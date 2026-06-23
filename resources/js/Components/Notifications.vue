<script setup>
import { computed, ref, useTemplateRef } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { BellIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { InfiniteScroll, Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const isOpen = ref(false)
const dropdownRef = ref(null)
const scrollContainer = useTemplateRef('scrollContainer', null)

onClickOutside(dropdownRef, () => {
  isOpen.value = false
})

const unreadCount = computed(() => Number(page.props.notifications_unread_count || 0))
const notifications = computed(() => page.props.notifications || { data: [], meta: {} })
const notificationRows = computed(() => notifications.value?.data || [])

const toggle = () => {
  isOpen.value = !isOpen.value
}
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <button
      @click="toggle"
      class="ui-icon-button relative"
      aria-label="Notifications"
    >
      <BellIcon class="h-5 w-5" />
      <span
        v-if="unreadCount > 0"
        class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-istaht-red ring-2 ring-white animate-soft-pulse"
      ></span>
    </button>

    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-show="isOpen"
        class="absolute right-0 z-30 mt-3 w-80 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-panel sm:w-96"
      >
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
          <div>
            <p class="text-sm font-bold text-istaht-navy">Notifications</p>
            <p class="text-xs text-slate-500">{{ unreadCount }} non lue(s)</p>
          </div>
          <Link
            v-if="unreadCount > 0"
            :href="route('notifications.readAll')"
            class="text-xs font-bold text-istaht-cyan transition hover:text-istaht-blue"
          >
            Tout marquer comme lu
          </Link>
        </div>

        <div ref="scrollContainer" class="max-h-96 overflow-y-auto">
          <div v-if="notificationRows.length === 0" class="ui-empty-state py-10">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-50 text-istaht-cyan">
              <CheckIcon class="h-6 w-6" />
            </div>
            <p class="text-sm font-bold text-istaht-navy">Aucune notification</p>
            <p class="mt-1 text-xs text-slate-500">Vous êtes à jour.</p>
          </div>

          <InfiniteScroll v-else data="notifications" preserve-url>
            <div
              v-for="notification in notificationRows"
              :key="notification.id"
              class="border-b border-slate-100 transition last:border-b-0 hover:bg-cyan-50/60"
              :class="{ 'bg-cyan-50/70': !notification.read }"
            >
              <Link :href="route('notifications.read', notification.id)" class="block px-4 py-3">
                <div class="flex items-start gap-3">
                  <span
                    class="mt-2 h-2 w-2 shrink-0 rounded-full"
                    :class="notification.read ? 'bg-slate-300' : 'bg-istaht-cyan'"
                  ></span>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm leading-5 text-slate-700" v-html="notification.message"></p>
                    <p class="mt-1 text-xs font-medium text-slate-400">{{ notification.time }}</p>
                  </div>
                </div>
              </Link>
            </div>

            <template #next="{ loading, hasMore }">
              <div v-if="loading" class="px-4 py-3 text-center">
                <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-500">
                  <span class="h-4 w-4 animate-spin rounded-full border-2 border-cyan-200 border-t-istaht-cyan"></span>
                  Chargement...
                </span>
              </div>

              <div v-if="!hasMore" class="border-t border-slate-100 px-4 py-2 text-center text-xs text-slate-400">
                Fin des notifications
              </div>
            </template>
          </InfiniteScroll>
        </div>
      </div>
    </Transition>
  </div>
</template>
