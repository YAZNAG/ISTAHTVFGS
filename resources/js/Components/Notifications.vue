<!-- resources/js/Components/NotificationDropdown.vue -->
<script setup>
import { ref, computed, watch, useTemplateRef } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { 
  BellIcon,
  InboxIcon,
  ShoppingCartIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  XMarkIcon,
  CheckCircleIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline'
import { InfiniteScroll, Link, usePage } from '@inertiajs/vue3'
import Dump from './Dump.vue'

const page = usePage();
// State
const isOpen = ref(false)

// Refs
const dropdownRef = ref(null)
const scrollContainer = useTemplateRef('scrollContainer', null)
// Click outside
onClickOutside(dropdownRef, () => {
  isOpen.value = false
})

// Computed
const unreadCount = computed(() => page.props.notifications_unread_count)
const notifications = computed(() => page.props.notifications)
const isLastLoad = computed(() => notifications.value.meta.last_page == notifications.value.meta.current_page)


// Toggle dropdown
const toggle = () => {
  isOpen.value = !isOpen.value
}

const getNotificationConfig = (type) => {
  switch (type) {
    case 'demande':
      return { icon: InboxIcon, colorClass: 'bg-blue-100 text-blue-600' }
    case 'marche':
      return { icon: ShoppingCartIcon, colorClass: 'bg-purple-100 text-purple-600' }
    case 'error':
      return { icon: XCircleIcon, colorClass: 'bg-red-100 text-red-600' }
    case 'warning':
      return { icon: ExclamationTriangleIcon, colorClass: 'bg-yellow-100 text-yellow-600' }
    case 'success':
      return { icon: CheckCircleIcon, colorClass: 'bg-green-100 text-green-600' }
    case 'budget':
      return { icon: BanknotesIcon, colorClass: 'bg-emerald-100 text-emerald-700' }
    default:
      return { icon: BellIcon, colorClass: 'bg-gray-100 text-gray-600' }
  }
}
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <button 
      @click="toggle"
      class="relative p-2 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
      <BellIcon class="w-5 h-5 text-gray-600" />
      <span 
        v-if="unreadCount > 0"
        class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"
      ></span>
    </button>
    
    <!-- Dropdown -->
    <div 
      v-show="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-30"
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <p class="text-sm font-semibold text-gray-900">Notifications</p>
        <Link
          :href="route('notifications.readAll')" 
          v-if="unreadCount > 0"
          class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors"
        >
          Tout marquer comme lu
        </Link>
      </div>
      
      <!-- List Container -->
      <div 
        class="max-h-96 overflow-y-auto"
        ref="scrollContainer"
      >
        <!-- Empty State -->
        <div 
          v-if="notifications.length === 0"
          class="px-4 py-8 text-center"
        >
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
            <BellIcon class="w-6 h-6 text-gray-400" />
          </div>
          <p class="text-sm font-medium text-gray-900">Aucune notification</p>
          <p class="text-xs text-gray-500 mt-1">Vous êtes à jour !</p>
        </div>

        <!-- Notifications -->
        <InfiniteScroll data="notifications" preserve-url>
        <div 
          v-for="notification in notifications.data" 
          :key="notification.id"
          class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
          :class="{ 'bg-blue-50': !notification.read }"
        >
        <Link :href="route('notifications.read', notification.id)">
          <div class="flex items-start space-x-3">
            <div 
              class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
              :class="notification.read ? 'bg-gray-300' : 'bg-blue-600'"
            ></div>
            
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-900 leading-snug" v-html="notification.message"></p>
              <p class="text-xs text-gray-500 mt-1">{{ notification.time }}</p>
            </div>
            
            <div 
              class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 bg-gray-100 text-gray-600"
            >
              <BellIcon
                class="w-3 h-3"
              />
            </div>
          </div>
        </Link>
        </div>

        <template #next="{ loading, hasMore }">
          <div v-if="loading" class="px-4 py-3 text-center">
            <div class="inline-flex items-center space-x-2 text-sm text-gray-500">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
              <span>Chargement...</span>
            </div>
          </div>


          <div 
            v-if="!hasMore "
            class="px-4 py-2 text-center text-xs text-gray-500 border-t border-gray-100"
          >
            Vous avez tout vu !
          </div>
        </template>
        
        
        </InfiniteScroll>

        
      </div>
    </div>
  </div>
</template>