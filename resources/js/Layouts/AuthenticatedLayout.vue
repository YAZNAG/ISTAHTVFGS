<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b">
      <div class="">
        <div class="flex justify-between h-16 items-center px-4">
          <!-- Left side -->
          <div class="flex items-center space-x-3">
            <!-- Sidebar toggle (mobile) -->
            <button 
              @click="isSidebarOpen = !isSidebarOpen"
              class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            <h1 class="text-lg sm:text-xl font-semibold">Gestion Stock</h1>
          </div>

          <!-- Right side -->
          <div class="flex items-center space-x-4">
            <!-- Active page badge -->
            <span class="hidden sm:block text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
              {{ currentPageTitle }}
            </span>

            <!-- User info -->

            <div class="relative" ref="userMenu">
              <button
                @click="isUserMenuOpen = !isUserMenuOpen"
                class="flex items-center space-x-2 focus:outline-none"
              >
                <span class="text-sm text-gray-700">{{ $page.props.auth.user.name }}</span>
                <div
                  class="h-8 w-8 bg-blue-100 border border-blue-200 text-blue-800 rounded-full flex items-center justify-center font-semibold"
                >
                  {{ $page.props.auth.user.name.charAt(0) }}
                </div>
                <svg
                  class="w-4 h-4 text-gray-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </button>

              <!-- Dropdown -->
              <div
              v-if="isUserMenuOpen"
              class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-100 py-2 z-50"
            >
              <Link
                href="/profile"
                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="isUserMenuOpen = false"
              >
                <UserIcon class="w-5 h-5 mr-2 text-gray-500" />
                Mon Compte
              </Link>

              <Link
                :href="route('logout')"
                method="POST"
                as="button"
                class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                @click="isUserMenuOpen = false"
              >
                <ArrowLeftOnRectangleIcon class="w-5 h-5 mr-2 text-gray-500" />
                Se Déconnecter
              </Link>
            </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <div class="flex flex-1 relative">
      <!-- Sidebar -->
      <aside
        :class="[
          'bg-white shadow-sm w-64 fixed md:static inset-y-0 left-0 transform transition-transform duration-300 ease-in-out z-10',
          isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]"
        class="sidebar-scrollbar"
      >
        <div class="h-full overflow-y-auto py-4">
          <div class="px-4 py-2 border-b border-gray-200 mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Menu Principal</h2>
          </div>

          <!-- Dynamic Navigation -->
          <nav class="px-3">
            <template v-for="(section, sIndex) in sidebarSections" :key="sIndex">
              <div
                v-if="section.label"
                class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 mb-2"
              >
                {{ section.label }}
              </div>

              <ul class="space-y-1">
                <li v-for="item in section.items" :key="item.href">
                  <Link
                    :href="item.href"
                    class="flex items-center px-3 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group"
                    :class="{
                      'bg-blue-50 text-blue-600 border-r-2 border-blue-600 font-semibold': $page.url.startsWith(item.match),
                      'text-gray-600 hover:text-gray-900': !$page.url.startsWith(item.match),
                    }"
                  >
                    <div
                      class="flex items-center justify-center w-8 h-8 rounded-lg mr-3 group-hover:bg-opacity-70 transition-colors"
                      :class="item.bgColor"
                    >
                      <component :is="item.icon" class="w-5 h-5" />
                    </div>
                    <span>{{ item.name }}</span>

                    <span
                      v-if="item.badge && fournisseursCount"
                      class="ml-auto bg-blue-100 text-blue-600 text-xs font-medium px-2 py-1 rounded-full"
                    >
                      {{ fournisseursCount }}
                    </span>
                  </Link>
                </li>
              </ul>
            </template>
          </nav>
        </div>
      </aside>

      <!-- Overlay for mobile -->
      <div
        v-if="isSidebarOpen"
        @click="isSidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"
      ></div>

      <!-- Main content -->
      <main class="flex-1 p-4 md:p-6 lg:p-8 transition-all duration-300">
        <div class="mb-6">
          <!-- Dynamic header -->
          <h2 class="text-xl font-semibold text-gray-800">{{ currentPageTitle }}</h2>
          <p class="text-gray-500 text-sm">{{ currentPageDescription }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 min-h-[calc(100vh-12rem)]">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  HomeIcon,
  CubeIcon,
  TruckIcon,
  ClipboardDocumentListIcon,
  UserGroupIcon,
  ArrowPathIcon,
  UserIcon,
  Cog6ToothIcon,
  DocumentChartBarIcon,
  ArrowUpCircleIcon,
  DocumentIcon,
  ChartBarIcon,
  ArrowLeftOnRectangleIcon
} from '@heroicons/vue/24/outline'
import { onClickOutside } from '@vueuse/core'

const isSidebarOpen = ref(false)
const isUserMenuOpen = ref(false)
const userMenu = ref(null)
const page = usePage()


onClickOutside(userMenu, () => {
  isUserMenuOpen.value = false
})

// Optional supplier count
const fournisseursCount = computed(() => page.props.fournisseursStats?.total || null)

// Sidebar definition
const sidebarSections = [
  {
    label: null,
    items: [
      { name: 'Tableau de bord', href: '/dashboard', match: '/dashboard', icon: HomeIcon, bgColor: 'bg-blue-100 text-blue-600' },
      { name: 'Articles', href: '/articles', match: '/articles', icon: CubeIcon, bgColor: 'bg-green-100 text-green-600' },
    ]
  },
  {
    label: 'Les Marchés',
    items: [
      { name: 'Marchés', href: '/achats/marches', match: '/achats/marches', icon: ClipboardDocumentListIcon, bgColor: 'bg-purple-100 text-purple-600' },
      { name: 'Bons de Commandes', href: '/chef-commandes', match: '/chef-commandes', icon: DocumentChartBarIcon, bgColor: 'bg-teal-100 text-teal-600' },
      { name: 'Bons de Livraison', href: '/bon-livraisons', match: '/bon-livraisons', icon: TruckIcon, bgColor: 'bg-orange-100 text-orange-600' },
      { name: 'Bons de Réception', href: '/bon-receptions', match: '/bon-receptions', icon: DocumentChartBarIcon, bgColor: 'bg-indigo-100 text-indigo-600' 
    },
    ]
  },
  {
    label: 'Gestion des Achats',
    items: [
      { name: 'Fournisseurs', href: '/achats/fournisseurs', match: '/achats/fournisseurs', icon: TruckIcon, bgColor: 'bg-orange-100 text-orange-600', badge: true },
      // { name: 'Bons de Réception', href: '/achats/bon-receptions', match: '/achats/bon-receptions', icon: DocumentChartBarIcon, bgColor: 'bg-teal-100 text-teal-600' },
    ]
  },
  {
    label: 'Gestion du Stock',
    items: [
      { name: 'Entrées Stock', href: '/stock/entrees', match: '/stock/entrees', icon: ArrowPathIcon, bgColor: 'bg-green-100 text-green-600' },
      // { name: 'Inventaire', href: '/inventaire', match: '/inventaire', icon: ClipboardDocumentListIcon, bgColor: 'bg-indigo-100 text-indigo-600' },
      { name: 'Sorties Stock', href: '/stock/sorties', match: '/stock/sorties', icon: ArrowPathIcon, bgColor: 'bg-amber-100 text-amber-600' },
    ]
  },
  {
    label: 'Demmande des Articles',
    items: [
      { name: 'Les Demandes', href: '/demandes', match: '/demandes', icon: ArrowUpCircleIcon, bgColor: 'bg-gray-100 text-gray-600' },
    ]
  },
  {
    label: 'Fiches Techniques',
    items: [
      { name: 'Pédagogiques', href: '/fiches-techniques/pedagogique', match: '/fiches-techniques/pedagogique', icon: DocumentIcon, bgColor: 'bg-purple-100 text-purple-600' },
      { name: 'Collectivité', href: '/fiches-techniques/collectivite', match: '/fiches-techniques/collectivite', icon: DocumentIcon, bgColor: 'bg-rose-100 text-rose-600' },

    ]
  },
  {
    label: 'Gestion des Utilisateurs',
    items: [
      { name: 'Utilisateurs', href: '/users', match: '/users', icon: UserGroupIcon, bgColor: 'bg-blue-100 text-blue-600' },
    ]
  },
  {
    label: 'Rapports',
    items: [
      { name: 'Les Rapports', href: '/rapports', match: '/rapports', icon: ChartBarIcon, bgColor: 'bg-indigo-100 text-indigo-600' },
    ]
  },
]

// Dynamic page titles
const pageTitles = {
  '/dashboard': 'Tableau de Bord',
  '/articles': 'Référentiel des Articles',
  '/achats/fournisseurs': 'Gestion des Fournisseurs',
  '/achats/marches': 'Les Marchés',
  '/chef-commandes': 'Bons de Commandes',
  '/bon-livraisons': 'Bons de Livraison',
  '/bon-receptions': 'Bons de Réception',
  '/stock/entrees': 'Entrées de Stock',
  '/stock/sorties': 'Sorties de Stock',
  '/demandes': 'Mes Demandes',
  '/users': 'Gestion des Utilisateurs',
  '/rapports': 'Rapports',
  '/fiches-techniques/pedagogique': 'Fiches Techniques Pédagogiques',
  '/fiches-techniques/collectivite': 'Fiches Techniques Collectivité',
}

// Dynamic page descriptions
const pageDescriptions = {
  '/dashboard': 'Vue d\'ensemble de votre activité et statistiques',
  '/articles': 'Gestion du catalogue et des références articles',
  '/achats/fournisseurs': 'Gérez vos partenaires fournisseurs et leurs informations',
  '/achats/marches': 'Créez et suivez vos Marchés',
  '/chef-commandes': 'Créez et suivez vos bons de commandes',
  '/bon-livraisons': 'Créez et suivez vos bons de livraison',
  '/bon-receptions': 'Enregistrez et validez les réceptions de marchandises',
  '/achats/bon-receptions': 'Enregistrez et validez les réceptions de marchandises',
  '/stock/entrees': 'Suivez vos entrées de stock',
  '/stock/sorties': 'Suivez vos sorties de stock',
  '/demandes': 'Gérez et suivez vos demandes d\'articles',
  '/users': 'Gérez les utilisateurs de l\'application',
  '/rapports': 'Consultez les rapports',
  '/fiches-techniques/pedagogique': 'Consultez les fiches techniques pédagogiques',
  '/fiches-techniques/collectivite': 'Consultez les fiches techniques collectivité',
}

const currentPageTitle = computed(() => {
  const currentUrl = page.url
  return Object.entries(pageTitles).find(([path]) => currentUrl.startsWith(path))?.[1] || 'Gestion Stock'
})

const currentPageDescription = computed(() => {
  const currentUrl = page.url
  return Object.entries(pageDescriptions).find(([path]) => currentUrl.startsWith(path))?.[1] || 'Interface de gestion de stock et d\'achats'
})
</script>

<style scoped>
.sidebar-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.sidebar-scrollbar::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}
.sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
