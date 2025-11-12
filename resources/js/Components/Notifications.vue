<!-- resources/js/Components/NotificationDropdown.vue -->
<script setup>
import { ref, computed, watch } from 'vue'
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

// State
const isOpen = ref(false)
const notifications = ref([])
const loading = ref(false)
const page = ref(1)
const hasMore = ref(true)

// Refs
const dropdownRef = ref(null)
const listRef = ref(null)

// Click outside
onClickOutside(dropdownRef, () => {
  isOpen.value = false
})

// Computed
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

// Load notifications
const loadNotifications = async (loadMore = false) => {
  if (!hasMore.value && loadMore) return
  
  loading.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 800))
    
    // Generate mock data
    const newNotifications = Array(10).fill(null).map((_, i) => ({
      id: Date.now() + i,
      message: `Nouvelle notification #${page.value * 10 + i + 1}`,
      time: `Il y a ${Math.floor(Math.random() * 60) + 1} minutes`,
      type: ['demande', 'marche', 'error', 'warning'][Math.floor(Math.random() * 4)],
      read: Math.random() > 0.3
    }))
    
    if (loadMore) {
      notifications.value.push(...newNotifications)
    } else {
      notifications.value = newNotifications
    }
    
    // Stop after 3 pages
    if (page.value >= 3) {
      hasMore.value = false
    }
    
    page.value++
  } catch (error) {
    console.error('Failed to load notifications:', error)
  } finally {
    loading.value = false
  }
}

// Toggle dropdown
const toggle = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value && notifications.value.length === 0) {
    loadNotifications()
  }
}

// Handle infinite scroll
const handleScroll = (event) => {
  const { scrollTop, scrollHeight, clientHeight } = event.target
  if (scrollHeight - scrollTop <= clientHeight * 1.2 && !loading.value && hasMore.value) {
    loadNotifications(true)
  }
}

// Mark all as read
const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
}

// Handle notification click
const handleClick = (notification) => {
  notification.read = true
  // Here you can emit an event or navigate
  // Example: window.location.href = `/demandes/${notification.id}`
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
        <button 
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors"
        >
          Tout marquer comme lu
        </button>
      </div>
      
      <!-- List Container -->
      <div 
        ref="listRef"
        class="max-h-96 overflow-y-auto"
        @scroll="handleScroll"
      >
        <!-- Empty State -->
        <div 
          v-if="notifications.length === 0 && !loading"
          class="px-4 py-8 text-center"
        >
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
            <BellIcon class="w-6 h-6 text-gray-400" />
          </div>
          <p class="text-sm font-medium text-gray-900">Aucune notification</p>
          <p class="text-xs text-gray-500 mt-1">Vous êtes à jour !</p>
        </div>
        
        <!-- Notifications -->
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
          :class="{ 'bg-blue-50': !notification.read }"
          @click="handleClick(notification)"
        >
          <div class="flex items-start space-x-3">
            <!-- Status Dot -->
            <div 
              class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
              :class="notification.read ? 'bg-gray-300' : 'bg-blue-600'"
            ></div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-900 leading-snug">{{ notification.message }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ notification.time }}</p>
            </div>
            
            <!-- Type Icon -->
            <div 
              v-if="notification.type"
              class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
              :class="getNotificationConfig(notification.type).colorClass"
            >
              <component 
                :is="getNotificationConfig(notification.type).icon" 
                class="w-3 h-3"
              />
            </div>
          </div>
        </div>
        
        <!-- Loading Indicator -->
        <div v-if="loading" class="px-4 py-3 text-center">
          <div class="inline-flex items-center space-x-2 text-sm text-gray-500">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <span>Chargement...</span>
          </div>
        </div>
        
        <!-- End of List -->
        <div 
          v-if="!hasMore && notifications.length > 0 && !loading"
          class="px-4 py-2 text-center text-xs text-gray-500 border-t border-gray-100"
        >
          Vous avez tout vu !
        </div>
      </div>
    </div>
  </div>
</template>