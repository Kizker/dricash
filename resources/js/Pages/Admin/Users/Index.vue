<template>
  <AdminLayout>
    <Head title="Manajemen Pengguna" />

    <div class="space-y-6 sm:space-y-8 w-full max-w-[1720px] mx-auto transition-all duration-300">
      
      <!-- 2. MAIN CONTAINER WITH SEARCH & FILTERS -->
      <div class="bg-white rounded-3xl border border-stone-200/80 shadow-[0_4px_24px_-6px_rgba(28,25,23,0.04)] overflow-hidden">
        
        <!-- Controls Bar -->
        <div class="p-3.5 sm:p-4 border-b border-stone-200/70 bg-stone-50/50 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          
          <!-- Search Field -->
          <div class="relative flex-1 max-w-md">
            <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 pointer-events-none" />
            <input
              v-model="searchQuery"
              @input="handleSearch"
              type="text"
              placeholder="Cari nama atau email pengguna..."
              class="w-full min-h-[40px] pl-9.5 pr-8 py-2 bg-white border border-stone-200/90 rounded-xl text-xs font-medium text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 shadow-2xs transition"
            />
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-400 hover:text-stone-700 flex items-center justify-center transition cursor-pointer"
              title="Hapus pencarian"
              aria-label="Hapus pencarian"
            >
              <X :size="12" />
            </button>
          </div>

          <!-- Filter Dropdowns Group -->
          <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            
            <!-- Role Filter Dropdown -->
            <div class="relative group flex-1 sm:flex-initial min-w-[135px]">
              <!-- Prefix Icon -->
              <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center">
                <Shield
                  :size="13"
                  :class="selectedRole !== 'all' ? 'text-purple-600' : 'text-stone-400 group-hover:text-stone-600 transition'"
                />
              </div>

              <!-- Styled Native Select -->
              <select
                v-model="selectedRole"
                @change="applyFilters"
                :class="[
                  'w-full appearance-none cursor-pointer min-h-[40px] pl-8.5 pr-8 py-2 text-xs font-semibold rounded-xl border transition-all duration-150 shadow-2xs outline-none',
                  selectedRole !== 'all'
                    ? 'bg-purple-50/90 border-purple-300 text-purple-950 font-bold ring-2 ring-purple-500/10'
                    : 'bg-white border-stone-200/90 text-stone-700 hover:border-stone-300 hover:bg-stone-50/70 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10'
                ]"
              >
                <option value="all">Semua Role</option>
                <option value="admin">Administrator ({{ stats.admins }})</option>
                <option value="user">Pengguna ({{ stats.regular_users }})</option>
              </select>

              <!-- Suffix Chevron -->
              <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none flex items-center">
                <ChevronDown
                  :size="13"
                  :class="selectedRole !== 'all' ? 'text-purple-600' : 'text-stone-400 group-hover:text-stone-600 transition'"
                />
              </div>
            </div>

            <!-- Status Filter Dropdown -->
            <div class="relative group flex-1 sm:flex-initial min-w-[135px]">
              <!-- Prefix Status Dot -->
              <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none flex items-center">
                <span
                  v-if="selectedStatus === 'active'"
                  class="w-2 h-2 rounded-full bg-emerald-500 shadow-2xs ring-2 ring-emerald-200/70"
                ></span>
                <span
                  v-else-if="selectedStatus === 'suspended'"
                  class="w-2 h-2 rounded-full bg-rose-500 shadow-2xs ring-2 ring-rose-200/70"
                ></span>
                <span
                  v-else
                  class="w-2 h-2 rounded-full bg-stone-300 group-hover:bg-stone-400 transition"
                ></span>
              </div>

              <!-- Styled Native Select -->
              <select
                v-model="selectedStatus"
                @change="applyFilters"
                :class="[
                  'w-full appearance-none cursor-pointer min-h-[40px] pl-8 pr-8 py-2 text-xs font-semibold rounded-xl border transition-all duration-150 shadow-2xs outline-none',
                  selectedStatus === 'active'
                    ? 'bg-emerald-50/90 border-emerald-300 text-emerald-950 font-bold ring-2 ring-emerald-500/10'
                    : selectedStatus === 'suspended'
                    ? 'bg-rose-50/90 border-rose-300 text-rose-950 font-bold ring-2 ring-rose-500/10'
                    : 'bg-white border-stone-200/90 text-stone-700 hover:border-stone-300 hover:bg-stone-50/70 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10'
                ]"
              >
                <option value="all">Semua Status</option>
                <option value="active">Status: Aktif ({{ stats.total - stats.suspended }})</option>
                <option value="suspended">Status: Nonaktif ({{ stats.suspended }})</option>
              </select>

              <!-- Suffix Chevron -->
              <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none flex items-center">
                <ChevronDown
                  :size="13"
                  :class="[
                    selectedStatus === 'active' ? 'text-emerald-600' :
                    selectedStatus === 'suspended' ? 'text-rose-600' :
                    'text-stone-400 group-hover:text-stone-600 transition'
                  ]"
                />
              </div>
            </div>

            <!-- Tactile Reset Button -->
            <button
              v-if="hasActiveFilters"
              @click="resetFilters"
              class="min-h-[40px] px-3 py-2 rounded-xl text-xs font-semibold text-stone-600 hover:text-stone-900 bg-white hover:bg-stone-100 active:bg-stone-200/80 border border-stone-200/90 shadow-2xs transition-all active:scale-95 flex items-center gap-1.5 cursor-pointer shrink-0"
              title="Reset semua filter dan pencarian"
            >
              <RotateCcw :size="12" class="text-stone-400" />
              <span>Reset</span>
            </button>
          </div>

        </div>

        <!-- 3A. DESKTOP VIEW: TACTILE TABLE (Visible md and up) -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-stone-200/70 bg-stone-50/60 text-[10px] font-extrabold uppercase tracking-wider text-stone-500">
                <th class="py-3.5 px-5">Pengguna</th>
                <th class="py-3.5 px-4">Role</th>
                <th class="py-3.5 px-4">Status</th>
                <th class="py-3.5 px-4">Aktivitas Finansial</th>
                <th class="py-3.5 px-4">Terdaftar</th>
                <th class="py-3.5 px-5 text-right">Tindakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 text-xs">
              <tr
                v-for="user in users.data"
                :key="user.id"
                class="hover:bg-stone-50/60 transition"
              >
                <!-- User Profile -->
                <td class="py-3.5 px-5">
                  <div class="flex items-center space-x-3.5">
                    <img
                      v-if="user.avatar_url"
                      :src="user.avatar_url"
                      :alt="user.name"
                      class="w-9 h-9 rounded-2xl object-cover border border-stone-200 shrink-0"
                    />
                    <div
                      v-else
                      class="w-9 h-9 rounded-2xl bg-stone-100 text-stone-700 font-bold text-xs flex items-center justify-center shrink-0 border border-stone-200"
                    >
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <div class="font-bold text-stone-900 truncate flex items-center gap-1.5">
                        <span>{{ user.name }}</span>
                        <span v-if="user.id === $page.props.auth.user.id" class="text-[9px] px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold">
                          Anda
                        </span>
                      </div>
                      <div class="text-[11px] text-stone-400 truncate">{{ user.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Role Badge -->
                <td class="py-3.5 px-4">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-md font-bold text-[10px] uppercase tracking-wider border',
                      user.role === 'admin'
                        ? 'bg-purple-50 text-purple-700 border-purple-200'
                        : 'bg-stone-100 text-stone-600 border-stone-200'
                    ]"
                  >
                    {{ user.role === 'admin' ? 'Admin' : 'User' }}
                  </span>
                </td>

                <!-- Status Badge -->
                <td class="py-3.5 px-4">
                  <span
                    :class="[
                      'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] border',
                      user.is_active
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                        : 'bg-rose-50 text-rose-700 border-rose-200'
                    ]"
                  >
                    <span
                      :class="['w-1.5 h-1.5 rounded-full', user.is_active ? 'bg-emerald-500' : 'bg-rose-500']"
                    ></span>
                    <span>{{ user.is_active ? 'Aktif' : 'Ditangguhkan' }}</span>
                  </span>
                </td>

                <!-- Financial Stats -->
                <td class="py-3.5 px-4">
                  <div class="text-[11px] text-stone-600 space-y-0.5">
                    <div><strong class="text-stone-900">{{ user.transactions_count }}</strong> transaksi</div>
                    <div class="text-stone-400">{{ user.obligations_count }} kewajiban • {{ user.growth_targets_count }} target</div>
                  </div>
                </td>

                <!-- Joined Date -->
                <td class="py-3.5 px-4 text-stone-400 text-[11px]">
                  {{ user.created_at }}
                </td>

                <!-- Action Buttons -->
                <td class="py-3.5 px-5 text-right">
                  <div class="flex items-center justify-end space-x-1.5">
                    <!-- Toggle Suspend -->
                    <button
                      v-if="user.id !== $page.props.auth.user.id"
                      @click="toggleUserStatus(user)"
                      :class="[
                        'p-2 rounded-xl text-xs font-semibold transition border cursor-pointer active:scale-95',
                        user.is_active
                          ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border-amber-200'
                          : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200'
                      ]"
                      :title="user.is_active ? 'Tangguhkan Akun' : 'Aktifkan Kembali Akun'"
                    >
                      <UserX v-if="user.is_active" :size="15" />
                      <UserCheck v-else :size="15" />
                    </button>

                    <!-- Reset Password -->
                    <button
                      @click="openResetPasswordModal(user)"
                      class="p-2 rounded-xl text-stone-600 bg-stone-100 hover:bg-stone-200 border border-stone-200 text-xs font-semibold transition cursor-pointer active:scale-95"
                      title="Reset Kata Sandi"
                    >
                      <KeyRound :size="15" />
                    </button>

                    <!-- Change Role -->
                    <button
                      v-if="user.id !== $page.props.auth.user.id"
                      @click="openRoleModal(user)"
                      class="p-2 rounded-xl text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-xs font-semibold transition cursor-pointer active:scale-95"
                      title="Ubah Role Akun"
                    >
                      <Shield :size="15" />
                    </button>

                    <!-- Delete Account -->
                    <button
                      v-if="user.id !== $page.props.auth.user.id"
                      @click="confirmDeleteUser(user)"
                      class="p-2 rounded-xl text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-xs font-semibold transition cursor-pointer active:scale-95"
                      title="Hapus Akun"
                    >
                      <Trash2 :size="15" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="users.data.length === 0">
                <td colspan="6" class="py-14 text-center text-stone-400">
                  <Users :size="36" class="mx-auto text-stone-300 mb-2" />
                  <div class="font-bold text-stone-700 text-sm">Tidak menemukan pengguna yang sesuai</div>
                  <div class="text-xs text-stone-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status.</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 3B. MOBILE VIEW: HUMAN MEMBER CARDS (Safe for 320px screens) -->
        <div class="block md:hidden divide-y divide-stone-100">
          <div
            v-for="user in users.data"
            :key="user.id"
            class="p-4 space-y-3 hover:bg-stone-50/50 transition"
          >
            <!-- Card Header: Avatar, Name, Email, Badges -->
            <div class="flex items-start justify-between gap-2.5">
              <div class="flex items-center space-x-3 min-w-0">
                <img
                  v-if="user.avatar_url"
                  :src="user.avatar_url"
                  :alt="user.name"
                  class="w-10 h-10 rounded-2xl object-cover border border-stone-200 shrink-0"
                />
                <div
                  v-else
                  class="w-10 h-10 rounded-2xl bg-stone-100 text-stone-800 font-bold text-sm flex items-center justify-center shrink-0 border border-stone-200"
                >
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <div class="font-bold text-stone-900 text-xs sm:text-sm truncate flex items-center gap-1.5">
                    <span>{{ user.name }}</span>
                    <span v-if="user.id === $page.props.auth.user.id" class="text-[9px] px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold">
                      Anda
                    </span>
                  </div>
                  <div class="text-[11px] text-stone-400 truncate">{{ user.email }}</div>
                </div>
              </div>

              <!-- Status Pill on Card -->
              <span
                :class="[
                  'inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] border shrink-0',
                  user.is_active
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-rose-50 text-rose-700 border-rose-200'
                ]"
              >
                <span :class="['w-1.5 h-1.5 rounded-full', user.is_active ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </span>
            </div>

            <!-- Card Body: Activity Pills -->
            <div class="flex flex-wrap items-center gap-1.5 text-[10px] text-stone-600 pt-1">
              <span class="px-2 py-0.5 rounded-md bg-stone-100 font-medium">
                {{ user.role === 'admin' ? 'Administrator' : 'User Biasa' }}
              </span>
              <span class="px-2 py-0.5 rounded-md bg-stone-100 font-medium">
                {{ user.transactions_count }} transaksi
              </span>
              <span class="px-2 py-0.5 rounded-md bg-stone-100 font-medium">
                {{ user.obligations_count }} kewajiban
              </span>
              <span class="text-stone-400 text-[10px] ml-auto">
                Bergabung: {{ user.created_at }}
              </span>
            </div>

            <!-- Card Actions (Generous 44x44pt Touch Targets in Thumb-Zone) -->
            <div class="pt-2 border-t border-stone-100 flex items-center justify-end space-x-2">
              
              <!-- Toggle Active -->
              <button
                v-if="user.id !== $page.props.auth.user.id"
                @click="toggleUserStatus(user)"
                :class="[
                  'min-h-[44px] min-w-[44px] px-3 rounded-xl text-xs font-bold flex items-center justify-center space-x-1 border active:scale-95 transition',
                  user.is_active
                    ? 'text-amber-800 bg-amber-50 border-amber-200'
                    : 'text-emerald-800 bg-emerald-50 border-emerald-200'
                ]"
              >
                <UserX v-if="user.is_active" :size="15" />
                <UserCheck v-else :size="15" />
                <span class="text-[11px]">{{ user.is_active ? 'Tangguhkan' : 'Aktifkan' }}</span>
              </button>

              <!-- Reset Password -->
              <button
                @click="openResetPasswordModal(user)"
                class="min-h-[44px] min-w-[44px] px-3 rounded-xl text-stone-700 bg-stone-100 hover:bg-stone-200 border border-stone-200 text-xs font-bold flex items-center justify-center space-x-1 active:scale-95 transition"
                title="Reset Sandi"
              >
                <KeyRound :size="15" />
                <span class="text-[11px]">Sandi</span>
              </button>

              <!-- Change Role -->
              <button
                v-if="user.id !== $page.props.auth.user.id"
                @click="openRoleModal(user)"
                class="min-h-[44px] min-w-[44px] p-2.5 rounded-xl text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-xs font-bold flex items-center justify-center active:scale-95 transition"
                title="Ubah Role"
              >
                <Shield :size="16" />
              </button>

              <!-- Delete User -->
              <button
                v-if="user.id !== $page.props.auth.user.id"
                @click="confirmDeleteUser(user)"
                class="min-h-[44px] min-w-[44px] p-2.5 rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-xs font-bold flex items-center justify-center active:scale-95 transition"
                title="Hapus Pengguna"
              >
                <Trash2 :size="16" />
              </button>

            </div>

          </div>

          <div v-if="users.data.length === 0" class="py-12 text-center text-stone-400 p-4">
            <Users :size="32" class="mx-auto text-stone-300 mb-2" />
            <div class="font-bold text-stone-700 text-sm">Tidak ada pengguna yang cocok</div>
            <div class="text-xs text-stone-400 mt-1">Coba sesuaikan kata kunci filter.</div>
          </div>
        </div>

        <!-- 4. PAGINATION (Ergonomic & Readable) -->
        <div v-if="users.links && users.links.length > 3" class="p-4 border-t border-stone-200/70 flex flex-col sm:flex-row items-center justify-between gap-3 bg-stone-50/40">
          <div class="text-xs text-stone-500 text-center sm:text-left">
            Menampilkan <span class="font-bold text-stone-800">{{ users.from || 0 }}</span> - <span class="font-bold text-stone-800">{{ users.to || 0 }}</span> dari <span class="font-bold text-stone-800">{{ users.total }}</span> pengguna
          </div>
          <div class="flex items-center flex-wrap justify-center gap-1">
            <Link
              v-for="(link, i) in users.links"
              :key="i"
              :href="link.url || '#'"
              v-html="link.label"
              :class="[
                'min-h-[36px] min-w-[36px] px-3 py-1.5 rounded-xl text-xs font-bold flex items-center justify-center transition active:scale-95',
                link.active ? 'bg-emerald-700 text-white shadow-xs' : 'bg-white border border-stone-200 text-stone-700 hover:bg-stone-100',
                !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
              ]"
            />
          </div>
        </div>

      </div>

    </div>

    <!-- MODAL 1: RESET KATA SANDI (Bottom-Sheet on Mobile, Center on Desktop) -->
    <div v-if="isResetModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-stone-900/60 backdrop-blur-xs animate-in fade-in">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 animate-in slide-in-from-bottom-6 sm:zoom-in-95 duration-200">
        
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
            <KeyRound :size="18" class="text-emerald-600" />
            <span>Atur Ulang Kata Sandi</span>
          </h3>
          <button @click="isResetModalOpen = false" class="min-h-[36px] min-w-[36px] flex items-center justify-center text-stone-400 hover:text-stone-600 rounded-lg">
            <X :size="18" />
          </button>
        </div>

        <p class="text-xs text-stone-500 mb-5 leading-relaxed">
          Tetapkan kata sandi baru untuk <strong class="text-stone-900">{{ activeUser?.name }}</strong> ({{ activeUser?.email }}). 
          Tindakan ini aman dan tidak mempengaruhi data transaksi pengguna.
        </p>

        <form @submit.prevent="submitResetPassword" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-stone-700 mb-1.5">Kata Sandi Baru</label>
            <input
              v-model="resetForm.password"
              type="password"
              placeholder="Minimal 8 karakter"
              class="w-full min-h-[44px] px-3.5 bg-stone-50 border border-stone-200 rounded-2xl text-xs font-medium focus:outline-none focus:border-emerald-600 focus:bg-white transition"
              required
            />
            <p v-if="resetForm.errors.password" class="text-rose-600 text-[11px] mt-1">{{ resetForm.errors.password }}</p>
          </div>

          <div>
            <label class="block text-xs font-bold text-stone-700 mb-1.5">Ulangi Kata Sandi Baru</label>
            <input
              v-model="resetForm.password_confirmation"
              type="password"
              placeholder="Ketik kembali kata sandi di atas"
              class="w-full min-h-[44px] px-3.5 bg-stone-50 border border-stone-200 rounded-2xl text-xs font-medium focus:outline-none focus:border-emerald-600 focus:bg-white transition"
              required
            />
          </div>

          <div class="pt-3 flex items-center justify-end space-x-2">
            <button
              type="button"
              @click="isResetModalOpen = false"
              class="min-h-[44px] px-4 py-2 rounded-2xl text-xs font-bold text-stone-600 hover:bg-stone-100 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="resetForm.processing"
              class="min-h-[44px] px-5 py-2 rounded-2xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs active:scale-[0.98] disabled:opacity-50"
            >
              {{ resetForm.processing ? 'Menyimpan...' : 'Simpan Sandi Baru' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: UBAH ROLE AKUN (Bottom-Sheet on Mobile, Center on Desktop) -->
    <div v-if="isRoleModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-stone-900/60 backdrop-blur-xs animate-in fade-in">
      <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 animate-in slide-in-from-bottom-6 sm:zoom-in-95 duration-200">
        
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
            <Shield :size="18" class="text-purple-600" />
            <span>Tingkat Akses & Role Akun</span>
          </h3>
          <button @click="isRoleModalOpen = false" class="min-h-[36px] min-w-[36px] flex items-center justify-center text-stone-400 hover:text-stone-600 rounded-lg">
            <X :size="18" />
          </button>
        </div>

        <p class="text-xs text-stone-500 mb-5 leading-relaxed">
          Ubah tingkat hak akses untuk akun <strong class="text-stone-900">{{ activeUser?.name }}</strong>:
        </p>

        <form @submit.prevent="submitChangeRole" class="space-y-4">
          <div class="space-y-2.5">
            <label class="flex items-center p-3.5 rounded-2xl border border-stone-200 cursor-pointer hover:bg-stone-50 transition">
              <input type="radio" v-model="roleForm.role" value="user" class="text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
              <div class="ml-3">
                <div class="text-xs font-bold text-stone-900">Pengguna Biasa</div>
                <div class="text-[11px] text-stone-500">Hanya dapat mengelola pembukuan pribadinya</div>
              </div>
            </label>

            <label class="flex items-center p-3.5 rounded-2xl border border-purple-200 bg-purple-50/40 cursor-pointer hover:bg-purple-50 transition">
              <input type="radio" v-model="roleForm.role" value="admin" class="text-purple-600 focus:ring-purple-500 h-4 w-4" />
              <div class="ml-3">
                <div class="text-xs font-bold text-purple-900">Administrator Sistem</div>
                <div class="text-[11px] text-purple-700">Dapat mengakses Panel Admin dan mengelola data master</div>
              </div>
            </label>
          </div>

          <div class="pt-3 flex items-center justify-end space-x-2">
            <button
              type="button"
              @click="isRoleModalOpen = false"
              class="min-h-[44px] px-4 py-2 rounded-2xl text-xs font-bold text-stone-600 hover:bg-stone-100 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="roleForm.processing"
              class="min-h-[44px] px-5 py-2 rounded-2xl text-xs font-bold bg-purple-600 hover:bg-purple-700 text-white transition shadow-xs active:scale-[0.98] disabled:opacity-50"
            >
              {{ roleForm.processing ? 'Menyimpan...' : 'Perbarui Hak Akses' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  Users,
  Search,
  KeyRound,
  Shield,
  Trash2,
  UserCheck,
  UserX,
  X,
  ChevronDown,
  RotateCcw
} from 'lucide-vue-next';

const props = defineProps({
  users: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '', role: 'all', status: 'all' }),
  },
  stats: {
    type: Object,
    required: true,
  },
});

const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'all');
const selectedStatus = ref(props.filters.status || 'all');

const hasActiveFilters = computed(() => {
  return selectedRole.value !== 'all' || selectedStatus.value !== 'all' || !!searchQuery.value;
});

function clearSearch() {
  searchQuery.value = '';
  applyFilters();
}

function resetFilters() {
  searchQuery.value = '';
  selectedRole.value = 'all';
  selectedStatus.value = 'all';
  applyFilters();
}

let searchTimeout = null;
function handleSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 350);
}

function applyFilters() {
  router.get(
    route('admin.users.index'),
    {
      search: searchQuery.value || undefined,
      role: selectedRole.value !== 'all' ? selectedRole.value : undefined,
      status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
}

// User Actions
function toggleUserStatus(user) {
  const action = user.is_active ? 'menangguhkan (nonaktifkan)' : 'mengaktifkan kembali';
  if (confirm(`Apakah Anda yakin ingin ${action} akun ${user.name}?`)) {
    router.post(route('admin.users.toggle-status', user.id));
  }
}

function confirmDeleteUser(user) {
  if (confirm(`PERINGATAN: Menghapus akun ${user.name} akan menghapus seluruh data transaksi dan catatan keuangannya. Lanjutkan?`)) {
    router.delete(route('admin.users.destroy', user.id));
  }
}

// Modals State
const isResetModalOpen = ref(false);
const isRoleModalOpen = ref(false);
const activeUser = ref(null);

const resetForm = useForm({
  password: '',
  password_confirmation: '',
});

const roleForm = useForm({
  role: 'user',
});

function openResetPasswordModal(user) {
  activeUser.value = user;
  resetForm.reset();
  resetForm.clearErrors();
  isResetModalOpen.value = true;
}

function submitResetPassword() {
  if (!activeUser.value) return;
  resetForm.post(route('admin.users.reset-password', activeUser.value.id), {
    onSuccess: () => {
      isResetModalOpen.value = false;
      resetForm.reset();
    },
  });
}

function openRoleModal(user) {
  activeUser.value = user;
  roleForm.role = user.role;
  isRoleModalOpen.value = true;
}

function submitChangeRole() {
  if (!activeUser.value) return;
  roleForm.put(route('admin.users.update-role', activeUser.value.id), {
    onSuccess: () => {
      isRoleModalOpen.value = false;
    },
  });
}
</script>
