<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { onClickOutside } from '@vueuse/core'
import {
  ArchiveBoxArrowDownIcon,
  ArrowDownTrayIcon,
  ArrowRightOnRectangleIcon,
  ArrowUpTrayIcon,
  ArrowUturnLeftIcon,
  Bars3Icon,
  BookOpenIcon,
  BuildingOfficeIcon,
  ChartBarIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ClipboardDocumentCheckIcon,
  ClipboardDocumentListIcon,
  CubeIcon,
  DocumentTextIcon,
  HomeIcon,
  MagnifyingGlassIcon,
  ShieldCheckIcon,
  ShoppingCartIcon,
  Squares2X2Icon,
  StarIcon,
  TagIcon,
  TruckIcon,
  UserCircleIcon,
  UsersIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import FlashMessages from '@/Components/FlashMessages.vue'
import Notifications from '@/Components/Notifications.vue'
import { usePermission } from '@/Utils/permission'

const { can, canAny } = usePermission()
const page = usePage()

const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const mobileViewport = ref(false)
const userMenuOpen = ref(false)
const menuSearch = ref('')
const favoritePaths = ref([])
const userMenuRef = ref(null)
const hasLogo = ref(true)

const logoSrc = '/images/logo-istaht.png'
const currentPath = computed(() => page.url)
const user = computed(() => page.props.auth.user)
const unreadNotifications = computed(() => Number(page.props.notifications_unread_count || 0))

const menuGroups = [
  {
    label: 'General',
    items: [
      { name: 'Tableau de bord', title: 'Tableau de bord', href: '/dashboard', match: '/dashboard', icon: HomeIcon },
      { name: 'Categories', title: 'Les categories', href: '/categories', match: '/categories', icon: TagIcon, permission: 'list_categories' },
      { name: 'Articles', title: 'Les articles', href: '/articles', match: '/articles', icon: Squares2X2Icon, permission: 'list_articles' },
    ],
  },
  {
    label: 'Marches & achats',
    items: [
      { name: 'Marches', title: 'Les marches', href: '/achats/marches', match: '/achats/marches', icon: ShoppingCartIcon, permission: 'list_marches', badge: 'Suivi' },
      { name: 'Fournisseurs', title: 'Les fournisseurs', href: '/achats/fournisseurs', match: '/achats/fournisseurs', icon: BuildingOfficeIcon, permission: 'list_fournisseurs' },
      { name: 'Bons de commande', title: 'Les bons de commande', href: '/chef-commandes', match: '/chef-commandes', icon: DocumentTextIcon, permission: 'list_chefCommandes', badge: 'Validation' },
      { name: 'Bons de livraison', title: 'Les bons de livraison', href: '/bon-livraisons', match: '/bon-livraisons', icon: TruckIcon, permission: 'list_bonLivraisons' },
      { name: 'Bons de reception', title: 'Les bons de reception', href: '/bon-receptions', match: '/bon-receptions', icon: ArchiveBoxArrowDownIcon, permission: 'list_bonReceptions' },
    ],
  },
  {
    label: 'Stock',
    items: [
      { name: 'Entrees stock', title: 'Les entrees stock', href: '/stock/entrees', match: '/stock/entrees', icon: ArrowDownTrayIcon, permission: 'entree_stocks' },
      { name: 'Articles en stock', title: 'Stock des articles', href: '/stock/articles', match: '/stock/articles', icon: CubeIcon, permission: 'articles_stocks', badge: 'Seuils' },
      { name: 'Sorties stock', title: 'Les sorties stock', href: '/stock/sorties', match: '/stock/sorties', icon: ArrowUpTrayIcon, permission: 'sortie_stocks' },
      { name: 'Retours stock', title: 'Les retours stock', href: '/stock/returns', match: '/stock/returns', icon: ArrowUturnLeftIcon, permission: 'returns_stocks' },
    ],
  },
  {
    label: 'Demandes',
    items: [
      { name: 'Demandes', title: 'Les demandes', href: '/demandes', match: '/demandes', icon: ClipboardDocumentListIcon, permission: 'list_demandes', badge: 'A traiter' },
      { name: 'Bons de sortie', title: 'Les bons de sortie', href: '/bon-sorties', match: '/bon-sorties', icon: ArrowUpTrayIcon, permission: 'list_bonSorties' },
    ],
  },
  {
    label: 'Fiches techniques',
    items: [
      { name: 'Menu collectivite', title: 'Les menus collectivite', href: '/menu-collectivite', match: '/menu-collectivite', icon: ClipboardDocumentListIcon, permission: 'list_menus' },
      { name: 'Pedagogiques', title: 'Les fiches pedagogiques', href: '/fiches-techniques/pedagogique', match: '/fiches-techniques/pedagogique', icon: BookOpenIcon, permission: 'list_ficheTechniques' },
      { name: 'Collectivite', title: 'Les fiches collectivite', href: '/fiches-techniques/collectivite', match: '/fiches-techniques/collectivite', icon: UsersIcon, permission: 'list_ficheTechniques' },
    ],
  },
  {
    label: 'Pilotage',
    items: [
      { name: 'Inventaires', title: 'Les inventaires', href: '/inventaires', match: '/inventaires', icon: ClipboardDocumentCheckIcon, permission: 'list_inventaire' },
      { name: 'Rapports', title: 'Les rapports', href: '/rapports', match: '/rapports', icon: ChartBarIcon, permission: 'list_raports' },
      { name: 'Utilisateurs', title: 'Les utilisateurs', href: '/users', match: '/users', icon: UsersIcon, permission: 'list_utilisateurs' },
      { name: 'Roles & permissions', title: 'Les roles et permissions', href: '/roles', match: '/roles', icon: ShieldCheckIcon, permission: 'list_roles' },
    ],
  },
]

const showItem = (item) => {
  if (!item.permission) return true
  return typeof item.permission === 'string' ? can(item.permission) : canAny(item.permission)
}

const allVisibleItems = computed(() => menuGroups.flatMap(group => group.items.filter(showItem)))

const visibleGroups = computed(() => {
  const needle = menuSearch.value.trim().toLowerCase()

  return menuGroups
    .map(group => ({
      ...group,
      items: group.items
        .filter(showItem)
        .filter(item => !needle || `${group.label} ${item.name} ${item.title}`.toLowerCase().includes(needle)),
    }))
    .filter(group => group.items.length > 0)
})

const favoriteItems = computed(() => allVisibleItems.value.filter(item => favoritePaths.value.includes(item.href)))

const isFavorite = (href) => favoritePaths.value.includes(href)
const isActive = (matchPath) => currentPath.value.startsWith(matchPath)

const pageTitle = computed(() => {
  const item = allVisibleItems.value.find(row => isActive(row.match))
  if (item) return item.title
  if (currentPath.value.includes('/profile')) return 'Profil'
  return 'Tableau de bord'
})

const currentRole = computed(() => page.props.auth.role || 'utilisateur')

const shortcutItems = computed(() => allVisibleItems.value.filter(item => [
  '/achats/marches',
  '/articles',
  '/stock/articles',
  '/bon-receptions',
].includes(item.href)).slice(0, 4))

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('ispitsrk.sidebarCollapsed', sidebarCollapsed.value ? '1' : '0')
}

function toggleUserMenu() {
  userMenuOpen.value = !userMenuOpen.value
}

function toggleFavorite(item) {
  const exists = favoritePaths.value.includes(item.href)
  favoritePaths.value = exists
    ? favoritePaths.value.filter(path => path !== item.href)
    : [...favoritePaths.value, item.href].slice(-6)
}

function handleResize() {
  mobileViewport.value = window.innerWidth < 768
  if (!mobileViewport.value) sidebarOpen.value = false
}

watch(favoritePaths, (paths) => {
  localStorage.setItem('ispitsrk.favoritePaths', JSON.stringify(paths))
}, { deep: true })

onClickOutside(userMenuRef, () => {
  userMenuOpen.value = false
})

onMounted(() => {
  handleResize()
  sidebarCollapsed.value = localStorage.getItem('ispitsrk.sidebarCollapsed') === '1'
  try {
    favoritePaths.value = JSON.parse(localStorage.getItem('ispitsrk.favoritePaths') || '[]')
  } catch {
    favoritePaths.value = []
  }
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<template>
  <div class="app-shell">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <button
        v-if="sidebarOpen && mobileViewport"
        class="fixed inset-0 z-30 bg-istaht-navy/55 backdrop-blur-sm md:hidden"
        aria-label="Fermer le menu"
        @click="toggleSidebar"
      ></button>
    </Transition>

    <aside
      :class="[
        'fixed inset-y-0 left-0 z-40 flex flex-col border-r border-slate-200 bg-white/95 shadow-panel backdrop-blur transition-all duration-300',
        sidebarCollapsed && !mobileViewport ? 'w-20' : 'w-80',
        sidebarOpen || !mobileViewport ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <div class="flex h-20 items-center justify-between border-b border-slate-200 px-4">
        <Link href="/dashboard" class="flex min-w-0 items-center gap-3">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-cyan-100 bg-white shadow-sm">
            <img
              v-if="hasLogo"
              :src="logoSrc"
              class="h-10 w-10 object-contain"
              alt="ISTAHT"
              @error="hasLogo = false"
            />
            <CubeIcon v-else class="h-6 w-6 text-istaht-blue" />
          </span>
          <span v-show="!sidebarCollapsed || mobileViewport" class="min-w-0">
            <span class="block text-base font-bold text-istaht-navy">ISTAHT Tanger</span>
            <span class="block truncate text-xs font-medium text-slate-500">ERP achats, stock et inventaire</span>
          </span>
        </Link>

        <button class="ui-icon-button md:hidden" aria-label="Fermer" @click="toggleSidebar">
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <div v-show="!sidebarCollapsed || mobileViewport" class="border-b border-slate-100 px-4 py-4">
        <div class="relative">
          <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-slate-400" />
          <input
            v-model="menuSearch"
            type="search"
            class="ui-input w-full pl-10"
            placeholder="Recherche menu..."
          />
        </div>

        <div v-if="favoriteItems.length" class="mt-4">
          <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Favoris</p>
          <div class="flex flex-wrap gap-2">
            <Link
              v-for="item in favoriteItems"
              :key="`fav-${item.href}`"
              :href="item.href"
              class="rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-1.5 text-xs font-bold text-istaht-navy transition hover:border-istaht-blue hover:bg-white"
            >
              {{ item.name }}
            </Link>
          </div>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto px-3 py-5">
        <div v-for="group in visibleGroups" :key="group.label" class="mb-5 animate-fade-up">
          <p
            v-show="!sidebarCollapsed || mobileViewport"
            class="mb-2 px-3 text-xs font-bold uppercase tracking-wide text-slate-400"
          >
            {{ group.label }}
          </p>

          <div class="space-y-1">
            <div v-for="item in group.items" :key="item.href" class="group/item relative">
              <Link
                :href="item.href"
                :title="sidebarCollapsed && !mobileViewport ? item.name : undefined"
                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition duration-200"
                :class="isActive(item.match)
                  ? 'bg-istaht-navy text-white shadow-soft'
                  : 'text-slate-600 hover:bg-cyan-50 hover:text-istaht-navy'"
              >
                <span
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition"
                  :class="isActive(item.match)
                    ? 'bg-white/15 text-white'
                    : 'bg-slate-50 text-slate-500 group-hover:bg-white group-hover:text-istaht-blue'"
                >
                  <component :is="item.icon" class="h-5 w-5" />
                </span>
                <span v-show="!sidebarCollapsed || mobileViewport" class="min-w-0 flex-1 truncate">{{ item.name }}</span>
                <span
                  v-if="item.badge && (!sidebarCollapsed || mobileViewport)"
                  class="rounded-full bg-istaht-amber/15 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700"
                >
                  {{ item.badge }}
                </span>
              </Link>

              <button
                v-show="!sidebarCollapsed || mobileViewport"
                type="button"
                class="absolute right-2 top-2.5 rounded-md p-1 text-slate-300 opacity-0 transition hover:bg-white hover:text-istaht-amber group-hover/item:opacity-100"
                :class="{ 'opacity-100 text-istaht-amber': isFavorite(item.href) }"
                :aria-label="isFavorite(item.href) ? 'Retirer des favoris' : 'Ajouter aux favoris'"
                @click.prevent="toggleFavorite(item)"
              >
                <StarIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <div v-if="visibleGroups.length === 0" class="px-3 py-10 text-center text-sm text-slate-500">
          Aucun module ne correspond a votre recherche.
        </div>
      </nav>

      <div class="border-t border-slate-200 p-4">
        <div class="flex items-center gap-3" :class="sidebarCollapsed && !mobileViewport ? 'justify-center' : ''">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-istaht-navy text-sm font-bold text-white">
            {{ user?.name?.charAt(0) || 'U' }}
          </div>
          <div v-show="!sidebarCollapsed || mobileViewport" class="min-w-0">
            <p class="truncate text-sm font-bold text-istaht-navy">{{ user?.name || 'Utilisateur' }}</p>
            <p class="truncate text-xs font-medium capitalize text-slate-500">{{ currentRole }}</p>
          </div>
        </div>
      </div>
    </aside>

    <div
      :class="[
        'min-h-screen transition-all duration-300',
        sidebarCollapsed ? 'md:pl-20' : 'md:pl-80',
      ]"
    >
      <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="flex min-h-16 items-center justify-between gap-3 px-4 md:px-6">
          <div class="flex min-w-0 items-center gap-3">
            <button class="ui-icon-button md:hidden" aria-label="Ouvrir le menu" @click="toggleSidebar">
              <Bars3Icon class="h-5 w-5" />
            </button>

            <button class="ui-icon-button hidden md:inline-flex" aria-label="Reduire le menu" @click="toggleCollapse">
              <ChevronLeftIcon class="h-5 w-5 transition" :class="{ 'rotate-180': sidebarCollapsed }" />
            </button>

            <div class="min-w-0">
              <h1 class="truncate text-lg font-bold text-istaht-navy md:text-xl">{{ pageTitle }}</h1>
              <p class="hidden text-xs font-medium text-slate-500 sm:block">
                Plateforme ERP ISTAHT pour achats, marches, stock et demandes internes
              </p>
            </div>
          </div>

          <div class="hidden flex-1 justify-center lg:flex">
            <div class="flex max-w-xl gap-2 overflow-hidden">
              <Link
                v-for="item in shortcutItems"
                :key="`shortcut-${item.href}`"
                :href="item.href"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-istaht-blue hover:bg-cyan-50 hover:text-istaht-navy"
                :class="{ 'border-istaht-blue bg-cyan-50 text-istaht-navy': isActive(item.match) }"
              >
                {{ item.name }}
              </Link>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <div class="hidden rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-1.5 text-xs font-bold text-istaht-navy sm:block">
              {{ unreadNotifications }} notification(s)
            </div>
            <Notifications />

            <div class="relative" ref="userMenuRef">
              <button
                @click="toggleUserMenu"
                class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-istaht-navy"
              >
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-istaht-navy text-xs font-bold text-white">
                  {{ user?.name?.charAt(0) || 'U' }}
                </span>
                <span class="hidden max-w-40 truncate md:inline">{{ user?.name }}</span>
                <ChevronDownIcon class="h-4 w-4" />
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
                  v-show="userMenuOpen"
                  class="absolute right-0 z-30 mt-3 w-56 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-panel"
                >
                  <Link href="/profile" class="flex items-center gap-2 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-cyan-50 hover:text-istaht-navy">
                    <UserCircleIcon class="h-5 w-5" />
                    Profil
                  </Link>
                  <Link href="/logout" method="post" class="flex items-center gap-2 border-t border-slate-100 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-red-50 hover:text-istaht-red">
                    <ArrowRightOnRectangleIcon class="h-5 w-5" />
                    Se deconnecter
                  </Link>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </header>

      <main class="px-4 py-5 md:px-6 md:py-6">
        <FlashMessages />
        <Transition name="page" mode="out-in">
          <div :key="currentPath" class="app-page">
            <slot />
          </div>
        </Transition>
      </main>
    </div>
  </div>
</template>
