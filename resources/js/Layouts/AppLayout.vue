<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 flex flex-col selection:bg-emerald-500 selection:text-white antialiased overflow-x-hidden w-full max-w-full">
    
    <!-- Top Header Navigation (Adaptive Mobile & Desktop) -->
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl shadow-xs">
      <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14 sm:h-16">
          
          <!-- Logo & Brand -->
          <div class="flex items-center space-x-3 sm:space-x-8">
            <Link :href="route('dashboard')" class="flex items-center active:scale-95 transition-transform">
              <ApplicationLogo variant="navbar" />
            </Link>

            <!-- Desktop Nav Links (Hidden on Mobile) -->
            <nav class="hidden md:flex items-center space-x-1">
              <Link
                v-for="item in navLinks"
                :key="item.route"
                :href="route(item.route)"
                :class="[
                  'px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center space-x-2',
                  isActive(item.route, item.pathPrefix, item.component)
                    ? 'bg-slate-100 text-slate-900 border border-slate-200 shadow-xs'
                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
                ]"
              >
                <component :is="item.icon" :size="15" :class="isActive(item.route, item.pathPrefix, item.component) ? 'text-emerald-600' : 'text-slate-400'" />
                <span>{{ item.label }}</span>
              </Link>

              <!-- Desktop Admin Panel Entry -->
              <Link
                v-if="authUser?.is_admin"
                :href="route('admin.dashboard')"
                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 shadow-2xs flex items-center space-x-1.5 transition ml-2"
              >
                <Shield :size="14" class="text-purple-600" />
                <span>Panel Admin</span>
              </Link>
            </nav>
          </div>

          <!-- Right Action Bar & Profile -->
          <div class="flex items-center space-x-2 sm:space-x-3">
            
            <!-- Desktop Quick Entry Sticky Buttons (Clean Text-Only Flat Style) -->
            <div class="hidden md:flex items-center space-x-1.5 bg-slate-100/90 p-1 rounded-2xl border border-slate-200/80">
              <button
                @click="openQuickEntry('income')"
                class="btn-human btn-human-success btn-human-sm !min-h-[34px] !py-1 !px-3.5 font-bold"
              >
                <span>Pemasukan</span>
              </button>
              <button
                @click="openQuickEntry('expense')"
                class="btn-human btn-human-danger btn-human-sm !min-h-[34px] !py-1 !px-3.5 font-bold"
              >
                <span>Pengeluaran</span>
              </button>
            </div>

            <!-- Profile & Settings Dropdown Trigger -->
            <div class="relative">
              <button
                @click="isProfileOpen = !isProfileOpen"
                class="flex items-center space-x-2 p-1 sm:p-1.5 rounded-xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200 active:scale-95 cursor-pointer"
                aria-label="Menu Profil"
              >
                <!-- Avatar or Initials -->
                <img
                  v-if="authUser?.avatar_url"
                  :src="authUser.avatar_url"
                  alt="Avatar"
                  class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-xs"
                />
                <div
                  v-else
                  class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-100 to-teal-100 text-emerald-800 flex items-center justify-center font-bold text-xs border border-emerald-300 shadow-xs"
                >
                  {{ userInitials }}
                </div>

                <div class="hidden lg:block text-left">
                  <div class="text-xs font-bold text-slate-800 truncate max-w-[120px]">
                    {{ authUser?.name || 'User' }}
                  </div>
                  <div class="text-[10px] text-slate-500 font-sans">
                    {{ authUser?.currency || 'IDR' }}
                  </div>
                </div>
              </button>

              <!-- Desktop Backdrop to close dropdown when clicking outside -->
              <div
                v-if="isProfileOpen"
                class="hidden md:block fixed inset-0 z-40"
                @click="isProfileOpen = false"
              ></div>

              <!-- Desktop Profile Dropdown Menu (Anchored & Elevated) -->
              <div
                v-if="isProfileOpen"
                class="hidden md:block absolute right-0 mt-2 w-72 bg-white rounded-2xl p-2.5 shadow-2xl border border-slate-200/90 z-50 animate-in fade-in zoom-in-95 duration-150"
              >
                <!-- Desktop Identity Card -->
                <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-100 mb-2 flex items-center space-x-3">
                  <img
                    v-if="authUser?.avatar_url"
                    :src="authUser.avatar_url"
                    alt="Avatar"
                    class="w-9 h-9 rounded-full object-cover border border-slate-200 shrink-0"
                  />
                  <div
                    v-else
                    class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-200"
                  >
                    {{ userInitials }}
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-slate-900 truncate">{{ authUser?.name }}</div>
                    <div class="text-[11px] text-slate-500 truncate">{{ authUser?.email }}</div>
                    <div class="text-[9px] font-bold text-emerald-700 tracking-wider uppercase mt-0.5">
                      {{ authUser?.currency || 'IDR' }} • Personal
                    </div>
                  </div>
                </div>

                <div class="space-y-1">
                  <!-- Admin Panel Entry -->
                  <Link
                    v-if="authUser?.is_admin"
                    :href="route('admin.dashboard')"
                    @click="isProfileOpen = false"
                    class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-bold transition flex items-center justify-between cursor-pointer bg-purple-50 hover:bg-purple-100 text-purple-950 border border-purple-200 mb-1.5 group"
                  >
                    <div class="flex items-center space-x-2.5 min-w-0">
                      <div class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                        <Shield :size="14" />
                      </div>
                      <div class="min-w-0">
                        <div class="font-extrabold truncate">Panel CMS Admin</div>
                        <div class="text-[9px] text-purple-700 font-medium">Kelola sistem & user</div>
                      </div>
                    </div>
                    <ChevronRight :size="14" class="text-purple-600 shrink-0 group-hover:translate-x-0.5 transition-transform" />
                  </Link>

                  <!-- Pertumbuhan Kekayaan -->
                  <Link
                    :href="route('growth.index')"
                    @click="isProfileOpen = false"
                    class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-bold transition flex items-center justify-between cursor-pointer group"
                    :class="isActive('growth.index', '/growth', 'Growth/Index') 
                      ? 'bg-emerald-50 text-emerald-800 font-extrabold' 
                      : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100/80'"
                  >
                    <div class="flex items-center space-x-2.5 min-w-0">
                      <div 
                        class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition"
                        :class="isActive('growth.index', '/growth', 'Growth/Index') 
                          ? 'bg-emerald-600 text-white' 
                          : 'bg-emerald-50 text-emerald-700 group-hover:bg-emerald-100'"
                      >
                        <TrendingUp :size="14" />
                      </div>
                      <span class="truncate">Pertumbuhan Kekayaan</span>
                    </div>
                    <ChevronRight :size="14" :class="isActive('growth.index', '/growth', 'Growth/Index') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-slate-600'" class="transition shrink-0" />
                  </Link>

                  <!-- Edit Profil -->
                  <button
                    @click="openSettings"
                    class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 transition flex items-center justify-between cursor-pointer group"
                  >
                    <div class="flex items-center space-x-2.5 min-w-0">
                      <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 group-hover:bg-slate-200 transition">
                        <User :size="14" />
                      </div>
                      <span class="truncate">Edit Profil</span>
                    </div>
                    <ChevronRight :size="14" class="text-slate-400 group-hover:text-slate-600 transition shrink-0" />
                  </button>

                  <!-- Keluar -->
                  <button
                    @click="logout"
                    class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-bold text-rose-600 hover:bg-rose-50 transition flex items-center justify-between cursor-pointer group"
                  >
                    <div class="flex items-center space-x-2.5 min-w-0">
                      <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover:bg-rose-100 transition">
                        <LogOut :size="14" />
                      </div>
                      <span class="truncate">Keluar</span>
                    </div>
                    <ChevronRight :size="14" class="text-rose-400 group-hover:text-rose-600 transition shrink-0" />
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </header>

    <!-- Global Flash Notification Toast -->
    <div v-if="flashMessage" class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 mt-3 w-full">
      <div
        class="p-3 sm:p-3.5 rounded-2xl flex items-center justify-between text-xs font-medium border shadow-md animate-in slide-in-from-top duration-300"
        :class="flashClass"
      >
        <div class="flex items-center space-x-2.5 min-w-0 pr-2">
          <Sparkles v-if="flashType === 'success'" :size="16" class="text-emerald-600 shrink-0" />
          <AlertTriangle v-else :size="16" class="text-amber-600 shrink-0" />
          <span class="truncate">{{ flashMessage }}</span>
        </div>
        <button @click="dismissFlash" class="p-1 text-slate-400 hover:text-slate-700 transition shrink-0">
          <X :size="14" />
        </button>
      </div>
    </div>

    <!-- Main Page Content Slot (Aligned perfectly with header max-w-7xl, bottom padding adjusted) -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 pb-24 md:pb-12">
      <slot />
    </main>

    <!-- Mobile Sticky Bottom Navigation Dock (Mobile Only, Completely Hidden on Desktop) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] px-2 sm:px-4 pb-safe select-none transition-all">
      <div class="flex items-center justify-around h-16 max-w-lg mx-auto">
        
        <!-- 1. Beranda (Home) Tab -->
        <Link
          :href="route('dashboard')"
          class="flex flex-col items-center justify-center w-14 h-full relative transition-all active:scale-95 cursor-pointer"
          :class="isActive('dashboard', '/dashboard', 'Dashboard') ? 'text-emerald-600 font-bold' : 'text-slate-500 hover:text-slate-900'"
        >
          <LayoutDashboard :size="20" :class="isActive('dashboard', '/dashboard', 'Dashboard') ? 'stroke-[2.5]' : 'stroke-2'" />
          <span class="text-[10px] font-bold mt-1 tracking-tight">Beranda</span>
        </Link>

        <!-- 2. Mutasi Kas Tab -->
        <Link
          :href="route('transactions.index')"
          class="flex flex-col items-center justify-center w-14 h-full relative transition-all active:scale-95 cursor-pointer"
          :class="isActive('transactions.index', '/transactions', 'Transactions/Index') ? 'text-emerald-600 font-bold' : 'text-slate-500 hover:text-slate-900'"
        >
          <Receipt :size="20" :class="isActive('transactions.index', '/transactions', 'Transactions/Index') ? 'stroke-[2.5]' : 'stroke-2'" />
          <span class="text-[10px] font-bold mt-1 tracking-tight">Mutasi</span>
        </Link>

        <!-- 3. Center Elevated Quick Add FAB (Elevated Flat CTA) -->
        <div class="relative -top-3">
          <button
            type="button"
            @click="openQuickActionSheet"
            class="btn-human-fab w-13 h-13 rounded-full flex items-center justify-center focus:outline-none cursor-pointer"
            title="Catat Cepat"
            aria-label="Catat Transaksi Cepat"
          >
            <Plus :size="24" class="stroke-[3]" />
          </button>
        </div>

        <!-- 4. Kewajiban (Ring-Fence) Tab -->
        <Link
          :href="route('obligations.index')"
          class="flex flex-col items-center justify-center w-14 h-full relative transition-all active:scale-95 cursor-pointer"
          :class="isActive('obligations.index', '/obligations', 'Obligations/Index') ? 'text-amber-600 font-bold' : 'text-slate-500 hover:text-slate-900'"
        >
          <Lock :size="20" :class="isActive('obligations.index', '/obligations', 'Obligations/Index') ? 'stroke-[2.5]' : 'stroke-2'" />
          <span class="text-[10px] font-bold mt-1 tracking-tight">Kewajiban</span>
        </Link>

        <!-- 5. Financial Reports (Laporan) Tab -->
        <Link
          :href="route('reports.index')"
          class="flex flex-col items-center justify-center w-14 h-full relative transition-all active:scale-95 cursor-pointer"
          :class="isActive('reports.index', '/reports', 'Reports/Index') ? 'text-emerald-600 font-bold' : 'text-slate-500 hover:text-slate-900'"
        >
          <BarChart3 :size="20" :class="isActive('reports.index', '/reports', 'Reports/Index') ? 'stroke-[2.5]' : 'stroke-2'" />
          <span class="text-[10px] font-bold mt-1 tracking-tight">Laporan</span>
        </Link>

      </div>
    </nav>

    <!-- Quick Action Sheet Modal (Human-Centric & Anti-AI Slop Minimalist Drawer) -->
    <div v-if="isQuickSheetOpen" class="fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="isQuickSheetOpen = false"></div>

      <!-- Action Drawer Content (Compact, Balanced Hierarchy & Tactile) -->
      <div class="relative w-full max-w-sm bg-white rounded-t-[28px] md:rounded-3xl p-4 min-[360px]:p-5 shadow-2xl border-t md:border border-slate-200/90 z-10 animate-in slide-in-from-bottom duration-200">
        <!-- Mobile Pull Handle -->
        <div class="w-10 h-1 bg-slate-200 rounded-full mx-auto mb-3 md:hidden"></div>
        
        <!-- Header: Centered & Minimalist (Mutasi-style, Superior Hierarchy) -->
        <div class="text-center mb-3.5">
          <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Catat Transaksi</h3>
          <p class="text-xs text-slate-400 mt-0.5">Pilih jenis transaksi</p>
        </div>

        <!-- 2 Clean Tactile Action Choices (Balanced Size & Proportions) -->
        <div class="grid grid-cols-2 gap-2.5 mb-2.5">
          <button
            type="button"
            @click="triggerAction('expense')"
            class="h-12 rounded-xl bg-rose-50/90 hover:bg-rose-100/90 active:scale-95 transition-all flex items-center justify-center cursor-pointer border border-rose-200/70 shadow-2xs group"
          >
            <span class="text-xs min-[360px]:text-sm font-bold text-rose-700 group-hover:text-rose-800">Pengeluaran</span>
          </button>

          <button
            type="button"
            @click="triggerAction('income')"
            class="h-12 rounded-xl bg-emerald-50/90 hover:bg-emerald-100/90 active:scale-95 transition-all flex items-center justify-center cursor-pointer border border-emerald-200/70 shadow-2xs group"
          >
            <span class="text-xs min-[360px]:text-sm font-bold text-emerald-800 group-hover:text-emerald-900">Pemasukan</span>
          </button>
        </div>

        <!-- Tactile Secondary Pill Dismiss Button -->
        <button
          type="button"
          @click="isQuickSheetOpen = false"
          class="w-full h-10 rounded-xl bg-slate-100 hover:bg-slate-200/80 active:scale-95 transition text-slate-600 font-semibold text-xs flex items-center justify-center cursor-pointer"
        >
          Batal
        </button>
      </div>
    </div>

    <!-- Mobile Profile & Account Bottom Sheet (Human-Centric & Thumb-Zone Optimized down to 320px) -->
    <div v-if="isProfileOpen" class="md:hidden fixed inset-0 z-50 flex items-end justify-center p-0">
      <!-- Backdrop with Natural Focus Blur -->
      <div 
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200" 
        @click="isProfileOpen = false"
      ></div>

      <!-- Sheet Drawer Container -->
      <div 
        class="relative w-full max-w-lg bg-white rounded-t-[28px] p-5 pb-8 shadow-[0_-12px_40px_rgba(0,0,0,0.18)] border-t border-slate-200/90 z-10 animate-in slide-in-from-bottom duration-200 max-h-[85vh] overflow-y-auto"
      >
        <!-- Pull Handle Bar Indicator -->
        <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto mb-3.5"></div>

        <!-- Sheet Header -->
        <div class="flex items-center justify-between pb-3 mb-3.5 border-b border-slate-100">
          <div>
            <h3 class="text-sm font-bold text-slate-800 tracking-tight">Akun Saya</h3>
            <p class="text-[11px] text-slate-500">Kelola identitas & preferensi dompet</p>
          </div>
          <button 
            type="button"
            @click="isProfileOpen = false"
            class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center transition active:scale-90 cursor-pointer"
            aria-label="Tutup Menu"
          >
            <X :size="16" />
          </button>
        </div>

        <!-- User Identity Sanctuary Card (Warm & Tactile) -->
        <div class="p-3.5 rounded-2xl bg-gradient-to-br from-emerald-50/80 to-teal-50/40 border border-emerald-100/90 mb-4 flex items-center space-x-3.5 shadow-2xs">
          <div class="relative shrink-0">
            <img
              v-if="authUser?.avatar_url"
              :src="authUser.avatar_url"
              alt="Avatar"
              class="w-12 h-12 rounded-full object-cover border-2 border-emerald-500 shadow-xs"
            />
            <div
              v-else
              class="w-12 h-12 rounded-full bg-emerald-600 text-white font-bold text-sm flex items-center justify-center border-2 border-white shadow-xs"
            >
              {{ userInitials }}
            </div>
            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full" title="Aktif"></div>
          </div>

          <div class="min-w-0 flex-1">
            <h4 class="text-sm font-bold text-slate-900 truncate">
              {{ authUser?.name || 'Pengguna' }}
            </h4>
            <p class="text-xs text-slate-600 truncate mt-0.5 font-normal">
              {{ authUser?.email }}
            </p>
            <div class="flex items-center space-x-1.5 mt-1">
              <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-600/10 text-emerald-800 border border-emerald-600/20 tracking-wide uppercase">
                {{ authUser?.currency || 'IDR' }} • Personal
              </span>
            </div>
          </div>
        </div>

        <!-- Action Rows (Simple & Clean Single-Line Items) -->
        <div class="space-y-2">
          <!-- Admin Panel Mobile Entry -->
          <Link
            v-if="authUser?.is_admin"
            :href="route('admin.dashboard')"
            @click="isProfileOpen = false"
            class="w-full h-12 px-3.5 sm:px-4 rounded-2xl bg-purple-50 hover:bg-purple-100 border border-purple-200 active:scale-[0.98] transition flex items-center justify-between text-left cursor-pointer group shadow-2xs"
          >
            <div class="flex items-center space-x-3 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                <Shield :size="16" class="stroke-[2.2]" />
              </div>
              <div class="min-w-0">
                <div class="text-xs min-[360px]:text-sm font-black text-purple-950 truncate">Panel CMS Admin</div>
                <div class="text-[10px] text-purple-700">Kontrol penuh platform Dricash</div>
              </div>
            </div>
            <ChevronRight :size="16" class="text-purple-600 group-hover:translate-x-0.5 transition-transform shrink-0" />
          </Link>

          <!-- 1. Pertumbuhan Kekayaan -->
          <Link
            :href="route('growth.index')"
            @click="isProfileOpen = false"
            class="w-full h-12 px-3.5 sm:px-4 rounded-2xl border active:scale-[0.98] transition flex items-center justify-between text-left cursor-pointer group shadow-2xs"
            :class="isActive('growth.index', '/growth', 'Growth/Index') 
              ? 'bg-emerald-50/90 border-emerald-300 text-emerald-900 font-extrabold' 
              : 'bg-slate-50 hover:bg-slate-100 border-slate-200/80 text-slate-800'"
          >
            <div class="flex items-center space-x-3 min-w-0">
              <div 
                class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition"
                :class="isActive('growth.index', '/growth', 'Growth/Index') 
                  ? 'bg-emerald-600 text-white' 
                  : 'bg-emerald-100/80 text-emerald-700 group-hover:bg-emerald-200'"
              >
                <TrendingUp :size="16" class="stroke-[2.2]" />
              </div>
              <span class="text-xs min-[360px]:text-sm font-bold truncate">Pertumbuhan Kekayaan</span>
            </div>
            <ChevronRight :size="16" :class="isActive('growth.index', '/growth', 'Growth/Index') ? 'text-emerald-600' : 'text-slate-400 group-hover:text-slate-600'" class="transition shrink-0" />
          </Link>

          <!-- 2. Edit Profil -->
          <button
            type="button"
            @click="openSettings"
            class="w-full h-12 px-3.5 sm:px-4 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200/80 active:scale-[0.98] transition flex items-center justify-between text-left cursor-pointer group shadow-2xs"
          >
            <div class="flex items-center space-x-3 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-slate-200/70 text-slate-700 flex items-center justify-center shrink-0 group-hover:bg-slate-300/80 transition">
                <User :size="16" class="stroke-[2.2]" />
              </div>
              <span class="text-xs min-[360px]:text-sm font-bold text-slate-800 truncate">Edit Profil</span>
            </div>
            <ChevronRight :size="16" class="text-slate-400 group-hover:text-slate-600 transition shrink-0" />
          </button>

          <!-- 3. Keluar -->
          <button
            type="button"
            @click="logout"
            class="w-full h-12 px-3.5 sm:px-4 rounded-2xl bg-rose-50/70 hover:bg-rose-100/80 border border-rose-200/80 active:scale-[0.98] transition flex items-center justify-between text-left cursor-pointer group shadow-2xs"
          >
            <div class="flex items-center space-x-3 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 group-hover:bg-rose-200 transition">
                <LogOut :size="16" class="stroke-[2.2]" />
              </div>
              <span class="text-xs min-[360px]:text-sm font-bold text-rose-600 truncate">Keluar</span>
            </div>
            <ChevronRight :size="16" class="text-rose-400 group-hover:text-rose-600 transition shrink-0" />
          </button>
        </div>

        <!-- Bottom Dismiss Button for Effortless Thumb Reach -->
        <div class="mt-4 pt-3 border-t border-slate-100">
          <button
            type="button"
            @click="isProfileOpen = false"
            class="w-full h-11 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs active:scale-95 transition flex items-center justify-center cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Global Modals -->
    <QuickEntryModal
      :isOpen="isQuickEntryOpen"
      :initialType="quickEntryType"
      :categories="categoriesList"
      @close="isQuickEntryOpen = false"
    />

    <InterventionAlertModal
      :isOpen="isInterventionOpen"
      :interventionData="interventionData"
      @close="isInterventionOpen = false"
    />

    <ProfileSettingsModal
      :isOpen="isSettingsOpen"
      :user="authUser"
      @close="isSettingsOpen = false"
    />

  </div>
</template>


<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import {
  LayoutDashboard,
  Lock,
  TrendingUp,
  Receipt,
  BarChart3,
  PlusCircle,
  MinusCircle,
  Plus,
  User,
  LogOut,
  Sparkles,
  AlertTriangle,
  X,
  ChevronRight,
  Shield
} from 'lucide-vue-next';
import QuickEntryModal from '@/Components/QuickEntryModal.vue';
import InterventionAlertModal from '@/Components/InterventionAlertModal.vue';
import ProfileSettingsModal from '@/Components/ProfileSettingsModal.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const categoriesList = computed(() => page.props.categories || []);

const navLinks = [
  { label: 'Beranda', route: 'dashboard', pathPrefix: '/dashboard', component: 'Dashboard', icon: LayoutDashboard },
  { label: 'Mutasi', route: 'transactions.index', pathPrefix: '/transactions', component: 'Transactions/Index', icon: Receipt },
  { label: 'Kewajiban', route: 'obligations.index', pathPrefix: '/obligations', component: 'Obligations/Index', icon: Lock },
  { label: 'Laporan', route: 'reports.index', pathPrefix: '/reports', component: 'Reports/Index', icon: BarChart3 },
  { label: 'Kekayaan', route: 'growth.index', pathPrefix: '/growth', component: 'Growth/Index', icon: TrendingUp },
];

function isActive(routeName, pathPrefix, componentName) {
  // 1. Direct Inertia Component check (always accurate on full browser reload and client navigation)
  if (componentName && page.component === componentName) {
    return true;
  }

  // 2. Ziggy route check
  if (typeof route === 'function') {
    try {
      if (route().current(routeName) || route().current(routeName + '.*')) return true;
    } catch (e) {
      // ignore
    }
  }

  // 3. Inertia page.url or window.location.pathname check
  const currentUrl = page.url || (typeof window !== 'undefined' ? window.location.pathname : '');
  if (pathPrefix) {
    if (pathPrefix === '/dashboard') {
      return currentUrl === '/' || currentUrl === '/dashboard' || currentUrl.startsWith('/dashboard?') || currentUrl.startsWith('/dashboard/');
    }
    return currentUrl === pathPrefix || currentUrl.startsWith(pathPrefix + '/') || currentUrl.startsWith(pathPrefix + '?');
  }
  return false;
}

const userInitials = computed(() => {
  const name = authUser.value?.name || 'User';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

// Modals State
const isQuickEntryOpen = ref(false);
const isQuickSheetOpen = ref(false);
const quickEntryType = ref('expense');
const isInterventionOpen = ref(false);
const isSettingsOpen = ref(false);
const isProfileOpen = ref(false);
const interventionData = ref(null);

// Flash Messages State
const customFlash = ref(null);
const flashMessage = computed(() => {
  if (customFlash.value) return customFlash.value;
  return page.props.flash?.success || page.props.flash?.error || page.props.flash?.warning;
});

const flashType = computed(() => {
  if (page.props.flash?.success) return 'success';
  if (page.props.flash?.error) return 'error';
  return 'warning';
});

const flashClass = computed(() => {
  if (flashType.value === 'success') {
    return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-800';
  }
  return 'bg-rose-500/10 border-rose-500/30 text-rose-800';
});

// Watch for incoming backend intervention alerts
watch(() => page.props.flash?.intervention, (data) => {
  if (data) {
    interventionData.value = data;
    isInterventionOpen.value = true;
  }
}, { immediate: true });

function openQuickEntry(type = 'expense') {
  quickEntryType.value = type;
  isQuickEntryOpen.value = true;
}

function openQuickActionSheet() {
  isQuickSheetOpen.value = true;
}

function triggerAction(type) {
  isQuickSheetOpen.value = false;
  openQuickEntry(type);
}

function openSettings() {
  isProfileOpen.value = false;
  isSettingsOpen.value = true;
}

function dismissFlash() {
  customFlash.value = null;
  if (page.props.flash) {
    page.props.flash.success = null;
    page.props.flash.error = null;
    page.props.flash.warning = null;
  }
}

function logout() {
  router.post(route('logout'));
}

// Global Event Listeners for Keyboard Shortcuts (e.g. 'e' for expense, 'i' for income)
if (typeof window !== 'undefined') {
  window.addEventListener('keydown', (e) => {
    // Only if not focused on an input/textarea
    if (['input', 'textarea', 'select'].includes(document.activeElement?.tagName?.toLowerCase())) {
      return;
    }
    if (e.key === 'e' || e.key === 'E') {
      e.preventDefault();
      openQuickEntry('expense');
    } else if (e.key === 'i' || e.key === 'I') {
      e.preventDefault();
      openQuickEntry('income');
    }
  });

  window.addEventListener('open-profile-menu', () => {
    isProfileOpen.value = true;
  });

  window.addEventListener('open-profile-settings', () => {
    isSettingsOpen.value = true;
  });
}
</script>
