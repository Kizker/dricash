<template>
  <div class="min-h-screen bg-[#FBFBF9] text-stone-900 flex flex-col md:flex-row antialiased selection:bg-emerald-600 selection:text-white font-sans">
    
    <!-- Mobile Top Subtle Indicator (Brand & Menu Drawer Trigger, Safe at min-width 320px) -->
    <div class="md:hidden sticky top-0 z-30 bg-[#FBFBF9]/90 backdrop-blur-md border-b border-stone-200/70 px-4 py-2.5 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <Link :href="route('admin.dashboard')" class="flex items-center active:scale-95 transition-transform" title="Dricash">
          <ApplicationLogo variant="navbar" imageClass="h-7 w-auto" />
        </Link>
      </div>

      <!-- Quick Profile / Drawer Trigger (Min 44x44pt Touch Target) -->
      <button
        @click="isMobileDrawerOpen = true"
        class="min-w-[44px] min-h-[44px] rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center active:scale-95 transition-all"
        aria-label="Buka Opsi Akun"
      >
        <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">
          {{ userInitials }}
        </span>
      </button>
    </div>

    <!-- Desktop Collapsible Sidebar (Fluid iOS/macOS Easing, Hardware Accelerated) -->
    <aside
      :class="[
        'hidden md:flex bg-stone-900 text-stone-200 flex-col h-screen sticky top-0 border-r border-stone-800 shrink-0 shadow-xl select-none transition-[width] duration-300 ease-[cubic-bezier(0.2,0,0,1)] will-change-[width] overflow-hidden',
        isSidebarCollapsed ? 'w-[76px]' : 'w-64 lg:w-72'
      ]"
    >
      
      <!-- Brand & Collapse Toggle Header (Constant h-16, No Logo, Fluid Transition) -->
      <div class="h-16 px-5 border-b border-stone-800/80 flex items-center justify-between relative overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)]">
        <!-- Brand Link: Bold Dricash Text (smooth slide and fade) -->
        <Link
          :href="route('admin.dashboard')"
          class="flex items-center active:scale-95 transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] shrink-0 overflow-hidden"
          :class="[
            isSidebarCollapsed
              ? 'max-w-0 opacity-0 -translate-x-3 pointer-events-none'
              : 'max-w-[160px] opacity-100 translate-x-0 pointer-events-auto'
          ]"
          title="Dricash"
        >
          <span class="text-xl font-bold tracking-tight text-white select-none whitespace-nowrap">
            Dricash
          </span>
        </Link>

        <!-- Sidebar Toggle Icon Button (Rides smoothly from right edge into exact center!) -->
        <button
          @click="toggleSidebar"
          class="w-9 h-9 rounded-xl text-stone-400 hover:text-white hover:bg-stone-800 transition-all duration-200 flex items-center justify-center active:scale-95 shrink-0 relative"
          :title="isSidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
          :aria-label="isSidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
        >
          <PanelLeftOpen
            class="transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] absolute"
            :class="isSidebarCollapsed ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-75 pointer-events-none'"
            :size="19"
          />
          <PanelLeftClose
            class="transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] absolute"
            :class="!isSidebarCollapsed ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 rotate-90 scale-75 pointer-events-none'"
            :size="19"
          />
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 p-3 space-y-1.5 overflow-y-auto overflow-x-hidden">
        <!-- Section Header -->
        <div
          :class="[
            'text-[10px] font-bold uppercase tracking-wider text-stone-500 whitespace-nowrap overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)]',
            isSidebarCollapsed ? 'max-h-0 opacity-0 py-0 px-0 -translate-y-1' : 'max-h-8 opacity-100 py-1.5 px-3 translate-y-0'
          ]"
        >
          Menu
        </div>

        <Link
          v-for="item in navItems"
          :key="item.route"
          :href="route(item.route)"
          :title="item.label"
          preserve-scroll
          :class="[
            'h-10 px-3 rounded-lg text-xs font-medium transition-colors duration-150 group relative flex items-center overflow-hidden',
            isCurrentRoute(item.route)
              ? 'bg-stone-800 text-white font-semibold'
              : 'text-stone-400 hover:text-stone-200 hover:bg-stone-800/50'
          ]"
        >
          <!-- Rock-Solid Fixed Icon Container (Never shifts horizontally) -->
          <div class="w-6 h-6 flex items-center justify-center shrink-0">
            <component
              :is="item.icon"
              :size="18"
              :class="isCurrentRoute(item.route) ? 'text-emerald-400' : 'text-stone-400 group-hover:text-stone-200 transition-colors'"
            />
          </div>

          <!-- Smooth Nav Label -->
          <span
            :class="[
              'whitespace-nowrap overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)]',
              isSidebarCollapsed
                ? 'max-w-0 opacity-0 -translate-x-3 ml-0 pointer-events-none'
                : 'max-w-[180px] opacity-100 translate-x-0 ml-3 pointer-events-auto flex-1'
            ]"
          >
            {{ item.label }}
          </span>

          <!-- Badge -->
          <span
            v-if="item.badge"
            :class="[
              'text-[10px] px-2 py-0.5 rounded-full font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shrink-0 whitespace-nowrap overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)]',
              isSidebarCollapsed ? 'max-w-0 opacity-0 scale-75 pointer-events-none ml-0 p-0 border-0' : 'max-w-[60px] opacity-100 scale-100 ml-auto'
            ]"
          >
            {{ item.badge }}
          </span>

          <!-- Active Indicator for Sidebar -->
          <span
            :class="[
              'absolute left-0 top-1/2 -translate-y-1/2 w-1 rounded-r-full bg-emerald-500 transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)]',
              isCurrentRoute(item.route) ? 'opacity-100' : 'opacity-0',
              isSidebarCollapsed ? 'h-4' : 'h-5'
            ]"
          ></span>
        </Link>
      </nav>

      <!-- Footer Actions: Refined Sleek Dock Bar (Anti-AI Slop, Tactile & Balanced) -->
      <div class="border-t border-stone-800/70 p-3 overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)]">
        <div
          :class="[
            'bg-stone-950/60 border border-stone-800/80 rounded-xl transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] flex items-center shadow-inner',
            isSidebarCollapsed ? 'flex-col p-1 gap-1 w-12 mx-auto' : 'p-1.5 gap-1.5 w-full'
          ]"
        >
          <!-- Ke Aplikasi Utama -->
          <Link
            :href="route('dashboard')"
            :class="[
              'rounded-lg text-xs font-medium text-stone-300 hover:text-white hover:bg-stone-800/80 transition-all duration-150 group flex items-center justify-center',
              isSidebarCollapsed ? 'w-10 h-10' : 'flex-1 h-9 px-2.5 gap-2'
            ]"
            title="Ke Aplikasi Utama"
          >
            <div class="w-5 h-5 flex items-center justify-center shrink-0">
              <ArrowLeft :size="16" class="text-stone-400 group-hover:text-stone-200 transition-colors" />
            </div>
            <span
              :class="[
                'whitespace-nowrap overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] font-medium text-xs',
                isSidebarCollapsed ? 'max-w-0 opacity-0 pointer-events-none' : 'max-w-[120px] opacity-100'
              ]"
            >
              Ke Aplikasi
            </span>
          </Link>

          <!-- Subtle Divider -->
          <div
            :class="[
              'bg-stone-800/90 shrink-0 transition-all duration-300',
              isSidebarCollapsed ? 'w-4 h-px my-0.5' : 'w-px h-4 mx-0.5'
            ]"
          ></div>

          <!-- Keluar -->
          <button
            @click="handleLogout"
            :class="[
              'rounded-lg text-xs font-medium text-stone-400 hover:text-rose-400 hover:bg-rose-500/10 transition-all duration-150 group flex items-center justify-center cursor-pointer',
              isSidebarCollapsed ? 'w-10 h-10' : 'h-9 px-3 gap-1.5'
            ]"
            title="Keluar"
            aria-label="Keluar"
          >
            <div class="w-5 h-5 flex items-center justify-center shrink-0">
              <LogOut :size="16" class="text-stone-400 group-hover:text-rose-400 transition-colors" />
            </div>
            <span
              :class="[
                'whitespace-nowrap overflow-hidden transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] font-medium text-xs',
                isSidebarCollapsed ? 'max-w-0 opacity-0 pointer-events-none' : 'max-w-[80px] opacity-100'
              ]"
            >
              Keluar
            </span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area (Naturally resized by flex layout, no stuttering) -->
    <div class="flex-1 flex flex-col min-w-0 pb-24 md:pb-6">

      <!-- Empathetic Global Flash Alert -->
      <div v-if="flashMessage" class="px-3 sm:px-6 lg:px-8 pt-4">
        <div
          :class="[
            'p-4 rounded-2xl flex items-start sm:items-center justify-between text-xs font-semibold shadow-xs transition-all animate-in fade-in slide-in-from-top-2 border',
            flashType === 'success' ? 'bg-emerald-50 text-emerald-900 border-emerald-200/90' :
            flashType === 'error' ? 'bg-rose-50 text-rose-900 border-rose-200/90' :
            'bg-amber-50 text-amber-900 border-amber-200/90'
          ]"
        >
          <div class="flex items-start sm:items-center space-x-3">
            <CheckCircle2 v-if="flashType === 'success'" :size="18" class="text-emerald-600 shrink-0 mt-0.5 sm:mt-0" />
            <AlertTriangle v-else-if="flashType === 'error'" :size="18" class="text-rose-600 shrink-0 mt-0.5 sm:mt-0" />
            <Info v-else :size="18" class="text-amber-600 shrink-0 mt-0.5 sm:mt-0" />
            <span class="leading-relaxed">{{ flashMessage }}</span>
          </div>
          <button
            @click="dismissFlash"
            class="min-w-[36px] min-h-[36px] flex items-center justify-center text-stone-400 hover:text-stone-700 rounded-lg shrink-0 ml-2"
            aria-label="Tutup Pemberitahuan"
          >
            <X :size="15" />
          </button>
        </div>
      </div>

      <!-- Page Slot with Flexible Inner Boxed Width -->
      <main class="flex-1 p-3.5 sm:p-6 lg:p-8 transition-all duration-300">
        <slot />
      </main>

      <!-- Minimal Clean Footer -->
      <footer class="mt-auto border-t border-stone-200/60 bg-transparent px-4 sm:px-8 py-4 text-center text-[11px] text-stone-400">
        © {{ new Date().getFullYear() }} Dricash Admin
      </footer>
    </div>

    <!-- MOBILE ERGONOMIC BOTTOM NAVIGATION DOCK (Thumb-Zone, min 320px safe) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-[#FBFBF9]/95 backdrop-blur-md border-t border-stone-200/80 px-2 py-1.5 shadow-lg shadow-stone-900/10">
      <div class="max-w-md mx-auto grid grid-cols-4 gap-1">
        <Link
          v-for="item in navItems"
          :key="item.route"
          :href="route(item.route)"
          :class="[
            'min-h-[48px] flex flex-col items-center justify-center rounded-xl text-[10px] font-bold transition-all active:scale-95',
            isCurrentRoute(item.route)
              ? 'text-emerald-700 bg-emerald-50/90 font-extrabold'
              : 'text-stone-500 hover:text-stone-800 hover:bg-stone-100/60'
          ]"
        >
          <component :is="item.icon" :size="18" class="mb-0.5" />
          <span class="truncate max-w-[68px]">{{ item.shortLabel || item.label }}</span>
        </Link>

        <!-- 4th Mobile Tab: Quick Drawer Trigger -->
        <button
          @click="isMobileDrawerOpen = true"
          class="min-h-[48px] flex flex-col items-center justify-center rounded-xl text-[10px] font-bold text-stone-500 hover:text-stone-800 hover:bg-stone-100/60 active:scale-95 transition-all"
        >
          <Menu :size="18" class="mb-0.5" />
          <span>Lainnya</span>
        </button>
      </div>
    </nav>

    <!-- MOBILE BOTTOM-SHEET DRAWER (Natural & Thumb-Friendly) -->
    <div
      v-if="isMobileDrawerOpen"
      class="md:hidden fixed inset-0 z-50 flex flex-col justify-end bg-stone-900/60 backdrop-blur-xs transition-opacity animate-in fade-in"
      @click.self="isMobileDrawerOpen = false"
    >
      <div class="bg-white rounded-t-3xl p-5 shadow-2xl border-t border-stone-200/80 space-y-4 max-h-[85vh] overflow-y-auto animate-in slide-in-from-bottom-6 duration-200">
        
        <!-- Sheet Handle -->
        <div class="w-10 h-1.5 bg-stone-300 rounded-full mx-auto mb-1"></div>

        <!-- User Identity Header (Clean & Simple) -->
        <div class="flex items-center space-x-3 px-2 py-1">
          <div class="w-10 h-10 rounded-full bg-stone-100 border border-stone-200 text-stone-700 flex items-center justify-center font-semibold text-sm shrink-0">
            {{ userInitials }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-sm font-semibold text-stone-900 truncate">{{ authUser?.name || 'Administrator' }}</div>
            <div class="text-xs text-stone-500 truncate">{{ authUser?.email }}</div>
          </div>
        </div>

        <!-- Quick Links in Sheet -->
        <div class="space-y-1 pt-2 border-t border-stone-100">
          <Link
            :href="route('dashboard')"
            class="min-h-[44px] w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-medium text-stone-700 hover:bg-stone-100 transition active:scale-[0.98]"
            @click="isMobileDrawerOpen = false"
          >
            <ArrowLeft :size="16" class="text-stone-500" />
            <span>Kembali ke Aplikasi Utama</span>
          </Link>

          <button
            @click="handleLogout"
            class="min-h-[44px] w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 transition active:scale-[0.98]"
          >
            <LogOut :size="16" class="text-rose-500" />
            <span>Keluar dari Panel Admin</span>
          </button>
        </div>

        <!-- Close Drawer Button -->
        <button
          @click="isMobileDrawerOpen = false"
          class="min-h-[44px] w-full py-2.5 text-xs font-bold text-stone-500 hover:text-stone-800 rounded-xl transition"
        >
          Tutup Menu
        </button>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {
  LayoutDashboard,
  Users,
  Tags,
  ArrowLeft,
  LogOut,
  Menu,
  X,
  PanelLeftClose,
  PanelLeftOpen,
  CheckCircle2,
  AlertTriangle,
  Info
} from 'lucide-vue-next';

const page = usePage();
const isMobileDrawerOpen = ref(false);
const dismissedFlash = ref(false);

// Collapsible Desktop Sidebar state with persistent singleton memory
import { isSidebarCollapsed, toggleSidebar } from '@/Utils/adminSidebar';

const authUser = computed(() => page.props.auth?.user);

const userInitials = computed(() => {
  const name = authUser.value?.name || 'Admin';
  return name.split(' ').map(n => n.charAt(0)).slice(0, 2).join('').toUpperCase();
});

const flash = computed(() => page.props.flash || {});

const flashMessage = computed(() => {
  if (dismissedFlash.value) return null;
  return flash.value.success || flash.value.error || flash.value.warning || null;
});

const flashType = computed(() => {
  if (flash.value.success) return 'success';
  if (flash.value.error) return 'error';
  if (flash.value.warning) return 'warning';
  return 'info';
});

function dismissFlash() {
  dismissedFlash.value = true;
}

const navItems = [
  {
    label: 'Ringkasan',
    shortLabel: 'Ringkasan',
    route: 'admin.dashboard',
    icon: LayoutDashboard,
  },
  {
    label: 'Pengguna',
    shortLabel: 'Pengguna',
    route: 'admin.users.index',
    icon: Users,
  },
  {
    label: 'Kategori',
    shortLabel: 'Kategori',
    route: 'admin.categories.index',
    icon: Tags,
  },
];

function isCurrentRoute(routeName) {
  try {
    return route().current(routeName);
  } catch (e) {
    return false;
  }
}

function handleLogout() {
  router.post(route('logout'));
}
</script>
