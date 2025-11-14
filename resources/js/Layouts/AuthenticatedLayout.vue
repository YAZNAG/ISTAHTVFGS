<!-- resources/js/Components/Layout/DashboardLayout.vue -->
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { onClickOutside } from '@vueuse/core'

// Import all Heroicons
import {
  // General
  HomeIcon,
  Squares2X2Icon,
  
  // Purchasing & Markets
  ShoppingCartIcon,
  BuildingOfficeIcon,
  DocumentTextIcon,
  TruckIcon,
  ArchiveBoxArrowDownIcon,
  
  // Stock
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
  
  // Requests
  InboxIcon,
  
  // Technical Sheets
  BookOpenIcon,
  UsersIcon,
  
  // Reports
  ChartBarIcon,
  
  // UI icons
  Bars3Icon,
  ChevronDownIcon,
  BellIcon,
  MagnifyingGlassIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'
import FlashMessages from '@/Components/FlashMessages.vue'
import Notifications from '@/Components/Notifications.vue'

// State management
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const mobileViewport = ref(false)
const userMenuOpen = ref(false)
const notificationMenuOpen = ref(false)

// Get current page and user info
const page = usePage()
const currentPath = computed(() => page.url)
const user = computed(() => page.props.auth.user)

// Optimized menu structure with logical grouping
const menuGroups = [
  {
    label: 'Général',
    items: [
      { 
        name: 'Tableau de bord', 
        title: 'Tableau de bord',
        href: '/dashboard', 
        match: '/dashboard', 
        icon: HomeIcon, 
        bgColor: 'bg-blue-100 text-blue-600' 
      },
      { 
        name: 'Articles',
        title: 'Les Articles',
        href: '/articles', 
        match: '/articles', 
        icon: Squares2X2Icon, 
        bgColor: 'bg-green-100 text-green-600' 
      },
    ]
  },
  {
    label: 'Marchés & Achats',
    items: [
      { 
        name: 'Marchés', 
        title: 'Les Marchés',
        href: '/achats/marches', 
        match: '/achats/marches', 
        icon: ShoppingCartIcon, 
        bgColor: 'bg-purple-100 text-purple-600' 
      },
      { 
        name: 'Fournisseurs',
        title: 'Les Fournisseurs',
        href: '/achats/fournisseurs', 
        match: '/achats/fournisseurs', 
        icon: BuildingOfficeIcon, 
        bgColor: 'bg-orange-100 text-orange-600',
        badge: true 
      },
      { 
        name: 'Bons de Commandes',
        title: 'Les Bons de Commandes',
        href: '/chef-commandes', 
        match: '/chef-commandes', 
        icon: DocumentTextIcon, 
        bgColor: 'bg-teal-100 text-teal-600' 
      },
      { 
        name: 'Bons de Livraison',
        title: 'Les Bons de Livraison',
        href: '/bon-livraisons', 
        match: '/bon-livraisons', 
        icon: TruckIcon, 
        bgColor: 'bg-rose-100 text-rose-600' 
      },
      { 
        name: 'Bons de Réception', 
        title: 'Les Bons de Réception',
        href: '/bon-receptions', 
        match: '/bon-receptions', 
        icon: ArchiveBoxArrowDownIcon, 
        bgColor: 'bg-indigo-100 text-indigo-600' 
      },
      
    ]
  },
  {
    label: 'Gestion du Stock',
    items: [
      { 
        name: 'Entrées Stock', 
        title: 'Les Entrées Stock',
        href: '/stock/entrees', 
        match: '/stock/entrees', 
        icon: ArrowDownTrayIcon, 
        bgColor: 'bg-emerald-100 text-emerald-600' 
      },
      { 
        name: 'Sorties Stock', 
        title: 'Les Sorties Stock',
        href: '/stock/sorties', 
        match: '/stock/sorties', 
        icon: ArrowUpTrayIcon, 
        bgColor: 'bg-amber-100 text-amber-600' 
      },
    ]
  },
  {
    label: 'Demandes',
    items: [
      { 
        name: 'Demandes', 
        title: 'Les Demandes',
        href: '/demandes', 
        match: '/demandes', 
        icon: InboxIcon, 
        bgColor: 'bg-gray-100 text-gray-600' 
      },
      { 
        name: 'Bons de Sortie', 
        title: 'Les Bons de Sortie',
        href: '/bon-sorties', 
        match: '/bon-sorties', 
        icon: ArrowUpTrayIcon, 
        bgColor: 'bg-amber-100 text-amber-600' 
      },
    ]
  },
  {
    label: 'Fiches Techniques',
    items: [
      { 
        name: 'Pédagogiques', 
        title: 'Les Fiches Pédagogiques',
        href: '/fiches-techniques/pedagogique', 
        match: '/fiches-techniques/pedagogique', 
        icon: BookOpenIcon, 
        bgColor: 'bg-violet-100 text-violet-600' 
      },
      { 
        name: 'Collectivité',
        title: 'Les Fiches Collectivité',
        href: '/fiches-techniques/collectivite', 
        match: '/fiches-techniques/collectivite', 
        icon: UsersIcon, 
        bgColor: 'bg-pink-100 text-pink-600' 
      },
    ]
  },
  {
    label: 'Utilisateurs & Rapports',
    items: [
      { 
        name: 'Utilisateurs', 
        title: 'Les Utilisateurs',
        href: '/users', 
        match: '/users', 
        icon: UsersIcon, 
        bgColor: 'bg-cyan-100 text-cyan-600' 
      },
      { 
        name: 'Rapports',
        title: 'Les Rapports',
        href: '/rapports', 
        match: '/rapports', 
        icon: ChartBarIcon, 
        bgColor: 'bg-slate-100 text-slate-600' 
      },
    ]
  },
]

// Rest of the script remains the same...
const userMenuRef = ref(null)
const notificationMenuRef = ref(null)

onClickOutside(userMenuRef, () => {
  userMenuOpen.value = false
})

onClickOutside(notificationMenuRef, () => {
  notificationMenuOpen.value = false
})

const isActive = (matchPath) => {
  return currentPath.value.startsWith(matchPath)
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const toggleSidebarCollapse = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
  notificationMenuOpen.value = false
}

const handleResize = () => {
  mobileViewport.value = window.innerWidth < 768
  if (!mobileViewport.value) {
    sidebarOpen.value = false
  }
}

const pageTitle = computed(() => {
  // Search through all menu groups
  for (const group of menuGroups) {
    for (const item of group.items) {
      if (isActive(item.match)) {
        return item.title
      }
    }
  }
  
  // Fallback for routes not in menu
  const path = currentPath.value
  if (path === '/') return 'Accueil'
  if (path.includes('/profile')) return 'Profil'
  if (path.includes('/settings')) return 'Paramètres'
  if (path.includes('/notifications')) return 'Notifications'
  
  // Default
  return 'Tableau de bord'
})

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

onMounted(() => {
  const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
      e.preventDefault()
      if (mobileViewport.value) {
        toggleSidebar()
      } else {
        toggleSidebarCollapse()
      }
    }
  }
  window.addEventListener('keydown', handleKeydown)
  onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile sidebar overlay -->
    <div 
      v-show="sidebarOpen && mobileViewport" 
      @click="toggleSidebar"
      class="fixed inset-0 bg-gray-900 bg-opacity-50 z-20 transition-opacity"
    ></div>

    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed top-0 left-0 h-full bg-white border-r border-gray-200 z-20 transition-all duration-300',
        {
          'w-64': !sidebarCollapsed || mobileViewport,
          'w-20': sidebarCollapsed && !mobileViewport,
          'translate-x-0': sidebarOpen || !mobileViewport,
          '-translate-x-full': !sidebarOpen && mobileViewport
        }
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between px-4 py-5 border-b border-gray-200">
          <Link href="/" class="flex items-center space-x-2">
            <!-- Logo/Brand -->
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
              <span class="font-bold text-white">I</span>
            </div>
            <span 
              v-show="!sidebarCollapsed || mobileViewport" 
              class="text-xl font-semibold text-gray-900"
            >
              ISTHT
            </span>
          </Link>
          
          <!-- Toggle button (desktop) -->
          <button 
            v-if="!mobileViewport"
            @click="toggleSidebarCollapse"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <ChevronRightIcon 
              :class="[
                'w-5 h-5 text-gray-600 transition-transform',
                { 'rotate-180': sidebarCollapsed }
              ]"
            />
          </button>
        </div>

        <!-- Sidebar Menu -->
        <nav class="flex-1 px-3 py-4 overflow-y-auto">
          <div 
            v-for="(group, groupIndex) in menuGroups" 
            :key="groupIndex"
            class="mb-6"
          >
            <!-- Group Label -->
            <p 
              v-if="group.label && (!sidebarCollapsed || mobileViewport)"
              class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
            >
              {{ group.label }}
            </p>
            
            <!-- Group Items -->
            <div class="space-y-1">
              <div 
                v-for="(item, itemIndex) in group.items" 
                :key="item.href"
              >
                <Link 
                  :href="item.href"
                  class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors"
                  :class="[
                    isActive(item.match) 
                      ? 'bg-blue-50 text-blue-600' 
                      : 'text-gray-700 hover:bg-gray-100',
                    sidebarCollapsed && !mobileViewport ? 'justify-center' : ''
                  ]"
                >
                  <!-- Icon -->
                  <component 
                    :is="item.icon" 
                    :class="[
                      'w-6 h-6 shrink-0 p-1 rounded',
                      isActive(item.match) ? 'text-blue-600' : 'text-gray-500',
                      item.bgColor ? item.bgColor : ''
                    ]"
                  />
                  
                  <!-- Text -->
                  <span 
                    v-show="!sidebarCollapsed || mobileViewport"
                    class="text-sm font-medium"
                  >
                    {{ item.name }}
                  </span>
                  
                  <!-- Badge -->
                  <!-- <span 
                    v-if="item.badge && (!sidebarCollapsed || mobileViewport)"
                    class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"
                  >
                    New
                  </span> -->
                </Link>
              </div>
            </div>
          </div>
        </nav>

        <!-- Sidebar Footer -->
        <div class="border-t border-gray-200 p-4">
          <div class="flex items-center space-x-3" :class="sidebarCollapsed && !mobileViewport ? 'justify-center' : ''">
            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-gray-600">
                {{ user?.name?.charAt(0) || 'U' }}
              </span>
            </div>
            <div v-show="!sidebarCollapsed || mobileViewport" class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name || 'Guest' }}</p>
              <p class="text-xs text-gray-500 truncate">{{ user?.role || 'Unknown' }}</p>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div 
      :class="[
        'flex flex-col min-h-screen transition-margin duration-300',
        {
          'md:ml-64': !sidebarCollapsed,
          'md:ml-20': sidebarCollapsed
        }
      ]"
    >
      <!-- Header -->
      <header class="sticky top-0 z-20 bg-white border-b border-gray-200">
        <div class="flex items-center justify-between px-4 py-4">
          <!-- Left: Mobile menu toggle -->
          <div class="flex items-center space-x-4">
            <button 
              @click="toggleSidebar"
              class="p-2 rounded-lg hover:bg-gray-100 md:hidden"
            >
              <Bars3Icon class="w-5 h-5 text-gray-600" />
            </button>
            
            <!-- Page Title -->
            <h1 class="text-xl font-semibold text-gray-900">
              {{ pageTitle || 'Dashboard' }}
            </h1>
          </div>

          <!-- Right: User menu -->
          <div class="flex items-center space-x-4">

            <!-- Notifications -->
            <Notifications />

            <!-- User Menu -->
            <div class="relative" ref="userMenuRef">
              <button 
                @click="toggleUserMenu"
                class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100"
              >
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                  <span class="text-sm font-medium text-white">
                    {{ user?.name?.charAt(0) || 'U' }}
                  </span>
                </div>
                <span class="hidden md:inline text-sm font-medium text-gray-900">{{ user?.name }}</span>
                <ChevronDownIcon class="w-4 h-4 text-gray-600" />
              </button>
              
              <!-- User Dropdown -->
              <div 
                v-show="userMenuOpen"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-30"
              >
                <Link 
                  href="/profile" 
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <UserCircleIcon class="w-4 h-4 mr-2" />
                  Your Profile
                </Link>
                <div class="border-t border-gray-200 my-1"></div>
                <Link 
                  href="/logout" 
                  method="post"
                  class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full"
                >
                  <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
                  Sign out
                </Link>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content Area -->
      <main class="flex-1 p-4 md:p-6">
        <!-- Flash Messages Component -->
        <FlashMessages />
        
        <!-- Page Content -->
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
          <slot />
        </div>
      </main>

    </div>
  </div>
</template>

<script>
export default {
  name: 'AuthenticatedLayout',
}
</script>

<style scoped>
/* Custom scrollbar for sidebar */
aside::-webkit-scrollbar {
  width: 6px;
}

aside::-webkit-scrollbar-track {
  background: #f3f4f6;
}

aside::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

aside::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>