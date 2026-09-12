<template>
  <AppLayout>
    <InertiaHead title="Mutasi" />

    <!-- 1. PAGE HEADER (Centered, Simple & Human-Centric) -->
    <div class="text-center mb-4 sm:mb-5">
      <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 font-sans">
        Mutasi
      </h1>
      <p class="text-xs text-slate-400 mt-0.5 font-medium">
        {{ totalCount }} transaksi tercatat
      </p>
    </div>

    <!-- 2. SEARCH & FILTER HUB (Lean, Progressive Disclosure & Thumb-Zone Friendly) -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-4 mb-4 sm:mb-5 border border-slate-200/80 shadow-2xs space-y-2.5">
      
      <!-- Top Row: Search Input + Filter Sheet Trigger (Mobile) / Selects (Desktop) -->
      <div class="flex items-center gap-2">
        <!-- Search Input -->
        <div class="relative flex-1 min-w-0">
          <Search :size="15" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 shrink-0" />
          <input
            v-model="filterForm.search"
            @keydown.enter="applyFilters"
            type="text"
            placeholder="Cari keterangan atau metode..."
            class="w-full bg-slate-50 border border-slate-200/90 focus:border-emerald-500 focus:bg-white rounded-xl pl-9 pr-8 py-2 text-xs text-slate-900 placeholder-slate-400 transition outline-none min-h-[42px]"
          />
          <button
            v-if="filterForm.search"
            type="button"
            @click="clearSearch"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 rounded-full cursor-pointer"
            title="Hapus pencarian"
          >
            <X :size="14" />
          </button>
        </div>

        <!-- Mobile Filter Sheet Trigger Button (Sliders / Filter) -->
        <button
          type="button"
          @click="openFilterSheet"
          class="sm:hidden relative flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-bold transition shrink-0 min-h-[42px] cursor-pointer active:scale-95"
          :class="activeFiltersCount > 0 
            ? 'bg-emerald-50 border-emerald-300 text-emerald-800' 
            : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
        >
          <SlidersHorizontal :size="14" />
          <span>Filter</span>
          <span 
            v-if="activeFiltersCount > 0" 
            class="w-4 h-4 rounded-full bg-emerald-600 text-white text-[9px] font-black flex items-center justify-center font-sans"
          >
            {{ activeFiltersCount }}
          </span>
        </button>

        <!-- Desktop Direct Filter Actions -->
        <div class="hidden sm:flex items-center gap-2">
          <!-- Desktop Category Select -->
          <select
            v-model="filterForm.category_id"
            @change="applyFilters"
            class="bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-3 py-2 text-xs text-slate-900 transition outline-none min-h-[42px]"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in sortedCategories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>

          <!-- Desktop Submit Button -->
          <button
            type="button"
            @click="applyFilters"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs font-bold transition min-h-[42px] cursor-pointer shadow-2xs"
          >
            Cari
          </button>
          
          <button
            v-if="hasActiveFilters"
            type="button"
            @click="resetFilters"
            class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition min-h-[42px] cursor-pointer"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Bottom Row: Quick 1-Tap Flow Segment Tabs + Active Filter Badges -->
      <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100/90 flex-wrap">
        
        <!-- Segment Tabs: Semua | Pengeluaran | Pemasukan -->
        <div class="flex items-center p-0.5 bg-slate-100/80 rounded-xl gap-0.5 shrink-0">
          <button
            type="button"
            @click="setTypeFilter('')"
            class="px-2.5 min-[360px]:px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer min-h-[34px] active:scale-95"
            :class="filterForm.type === '' 
              ? 'bg-white text-slate-900 shadow-2xs' 
              : 'text-slate-500 hover:text-slate-800'"
          >
            Semua
          </button>
          <button
            type="button"
            @click="setTypeFilter('expense')"
            class="px-2.5 min-[360px]:px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer min-h-[34px] active:scale-95"
            :class="filterForm.type === 'expense' 
              ? 'bg-white text-rose-700 shadow-2xs' 
              : 'text-slate-500 hover:text-slate-800'"
          >
            Pengeluaran
          </button>
          <button
            type="button"
            @click="setTypeFilter('income')"
            class="px-2.5 min-[360px]:px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer min-h-[34px] active:scale-95"
            :class="filterForm.type === 'income' 
              ? 'bg-white text-emerald-700 shadow-2xs' 
              : 'text-slate-500 hover:text-slate-800'"
          >
            Pemasukan
          </button>
        </div>

        <!-- Active Filter Badges (Category, Date, etc.) -->
        <div v-if="hasActiveFilters" class="flex items-center gap-1.5 flex-wrap">
          <span 
            v-if="selectedCategoryName" 
            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-[10px] min-[360px]:text-[11px] font-semibold border border-emerald-200/80"
          >
            <span class="truncate max-w-[110px]">{{ selectedCategoryName }}</span>
            <button type="button" @click="clearCategory" class="hover:text-emerald-950 cursor-pointer p-0.5">
              <X :size="11" />
            </button>
          </span>

          <span 
            v-if="activeDateLabel" 
            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 text-slate-700 text-[10px] min-[360px]:text-[11px] font-medium border border-slate-200/80"
          >
            <span>{{ activeDateLabel }}</span>
            <button type="button" @click="clearDates" class="hover:text-slate-950 cursor-pointer p-0.5">
              <X :size="11" />
            </button>
          </span>

          <button
            type="button"
            @click="resetFilters"
            class="text-[10px] min-[360px]:text-[11px] font-bold text-slate-500 hover:text-slate-900 py-1 px-1.5 rounded-md transition cursor-pointer"
          >
            Reset
          </button>
        </div>

      </div>

    </div>

    <!-- 3. MAIN TRANSACTIONS CARD (No Duplicate Header) -->
    <TransactionLedgerTable
      :transactions="transactions.data || transactions"
      :pagination="transactions"
      :allowActions="true"
      :showHeader="false"
      @quick-income="openModal('income')"
      @quick-expense="openModal('expense')"
    />

    <!-- 4. MOBILE FILTER BOTTOM SHEET (Human-Centric Tactile Redesign) -->
    <div
      v-if="showFilterSheet"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
    >
      <!-- Backdrop with subtle blur -->
      <div 
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="closeFilterSheet"
      ></div>

      <!-- Bottom Sheet Drawer -->
      <div class="relative w-full sm:max-w-md bg-white rounded-t-[28px] sm:rounded-3xl shadow-2xl p-4 min-[360px]:p-5 sm:p-6 border border-slate-100 transform transition-all z-10 animate-in fade-in slide-in-from-bottom-4 duration-200 flex flex-col max-h-[85vh]">
        <!-- Handle bar on mobile -->
        <div class="sm:hidden w-10 h-1.5 bg-slate-200 rounded-full mx-auto mb-3 shrink-0"></div>

        <!-- Sheet Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
          <div>
            <h3 class="text-base font-bold text-slate-900 font-sans">
              Filter Transaksi
            </h3>
            <p class="text-[11px] text-slate-400">
              Saring riwayat mutasi sesuai kebutuhan
            </p>
          </div>
          <button
            type="button"
            @click="closeFilterSheet"
            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 active:scale-95 flex items-center justify-center text-slate-500 hover:text-slate-800 transition cursor-pointer"
          >
            <X :size="16" />
          </button>
        </div>

        <!-- Filter Form Fields (Scrollable) -->
        <div class="py-3.5 space-y-4 overflow-y-auto min-h-0 flex-1">
          
          <!-- 1. Jenis Transaksi (Segmented Control) -->
          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">
              Jenis Transaksi
            </label>
            <div class="flex items-center p-1 bg-slate-100 rounded-2xl gap-1">
              <button
                type="button"
                @click="tempFilterForm.type = ''"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition text-center cursor-pointer min-h-[38px] active:scale-95"
                :class="tempFilterForm.type === '' 
                  ? 'bg-white text-slate-900 shadow-2xs' 
                  : 'text-slate-500 hover:text-slate-800'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="tempFilterForm.type = 'expense'"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition text-center cursor-pointer min-h-[38px] active:scale-95"
                :class="tempFilterForm.type === 'expense' 
                  ? 'bg-white text-rose-600 shadow-2xs' 
                  : 'text-slate-500 hover:text-slate-800'"
              >
                Pengeluaran
              </button>
              <button
                type="button"
                @click="tempFilterForm.type = 'income'"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition text-center cursor-pointer min-h-[38px] active:scale-95"
                :class="tempFilterForm.type === 'income' 
                  ? 'bg-white text-emerald-600 shadow-2xs' 
                  : 'text-slate-500 hover:text-slate-800'"
              >
                Pemasukan
              </button>
            </div>
          </div>

          <!-- 2. Kategori (Dropdown) -->
          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">
              Kategori
            </label>
            <div class="relative">
              <select
                v-model="tempFilterForm.category_id"
                class="w-full bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200 focus:border-emerald-500 rounded-xl px-3.5 py-2.5 pr-10 text-xs font-medium text-slate-800 transition outline-none min-h-[44px] appearance-none cursor-pointer"
              >
                <option value="">Semua Kategori</option>
                <option v-for="cat in sortedCategories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <div class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 flex items-center">
                <ChevronDown :size="16" />
              </div>
            </div>
          </div>

          <!-- 3. Periode Waktu (Quick Presets + Progressive Disclosure) -->
          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1.5">
              Periode Waktu
            </label>

            <!-- Quick Presets -->
            <div class="grid grid-cols-4 gap-1.5 mb-2">
              <button
                type="button"
                @click="setPeriodPreset('all')"
                class="py-2 px-1 text-center rounded-xl text-xs font-bold transition cursor-pointer min-h-[36px] active:scale-95"
                :class="activePeriodPreset === 'all' 
                  ? 'bg-slate-900 text-white shadow-2xs' 
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'"
              >
                Semua
              </button>
              <button
                type="button"
                @click="setPeriodPreset('this_month')"
                class="py-2 px-1 text-center rounded-xl text-xs font-bold transition cursor-pointer min-h-[36px] active:scale-95"
                :class="activePeriodPreset === 'this_month' 
                  ? 'bg-emerald-600 text-white shadow-2xs' 
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'"
              >
                Bulan Ini
              </button>
              <button
                type="button"
                @click="setPeriodPreset('last_month')"
                class="py-2 px-1 text-center rounded-xl text-xs font-bold transition cursor-pointer min-h-[36px] active:scale-95"
                :class="activePeriodPreset === 'last_month' 
                  ? 'bg-emerald-600 text-white shadow-2xs' 
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'"
              >
                Bulan Lalu
              </button>
              <button
                type="button"
                @click="setPeriodPreset('custom')"
                class="py-2 px-1 text-center rounded-xl text-xs font-bold transition cursor-pointer min-h-[36px] active:scale-95"
                :class="activePeriodPreset === 'custom' 
                  ? 'bg-slate-900 text-white shadow-2xs' 
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'"
              >
                Kustom
              </button>
            </div>

            <!-- Custom Date Inputs (Revealed when Kustom is selected) -->
            <div v-if="activePeriodPreset === 'custom'" class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2 animate-in fade-in duration-200">
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <span class="block text-[10px] font-semibold text-slate-500 mb-1">Dari Tanggal</span>
                  <input
                    v-model="tempFilterForm.date_from"
                    type="date"
                    class="w-full bg-white border border-slate-200 focus:border-emerald-500 rounded-xl px-2.5 py-2 text-xs text-slate-900 transition outline-none min-h-[42px]"
                  />
                </div>
                <div>
                  <span class="block text-[10px] font-semibold text-slate-500 mb-1">Sampai Tanggal</span>
                  <input
                    v-model="tempFilterForm.date_to"
                    type="date"
                    class="w-full bg-white border border-slate-200 focus:border-emerald-500 rounded-xl px-2.5 py-2 text-xs text-slate-900 transition outline-none min-h-[42px]"
                  />
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Sticky Bottom Actions in Thumb Zone -->
        <div class="pt-3 pb-1 border-t border-slate-100 flex items-center gap-2 shrink-0">
          <button
            type="button"
            @click="resetTempFilters"
            class="py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 text-xs font-bold transition min-h-[48px] cursor-pointer"
          >
            Atur Ulang
          </button>
          <button
            type="button"
            @click="applyFilterSheet"
            class="flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs font-bold transition text-center shadow-sm min-h-[48px] cursor-pointer"
          >
            <span>Terapkan Filter</span>
            <span v-if="tempActiveCount > 0" class="ml-1 font-extrabold">({{ tempActiveCount }})</span>
          </button>
        </div>

      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head as InertiaHead, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TransactionLedgerTable from '@/Components/TransactionLedgerTable.vue';
import { Search, SlidersHorizontal, X, ChevronDown } from 'lucide-vue-next';

const props = defineProps({
  transactions: {
    type: Object,
    required: true
  },
  categories: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const sortedCategories = computed(() => {
  return [...props.categories].sort((a, b) => {
    const isOtherA = /lain/i.test(a.name);
    const isOtherB = /lain/i.test(b.name);
    if (isOtherA && !isOtherB) return 1;
    if (!isOtherA && isOtherB) return -1;
    return 0;
  });
});

const totalCount = computed(() => {
  if (props.transactions?.total !== undefined) return props.transactions.total;
  if (Array.isArray(props.transactions?.data)) return props.transactions.data.length;
  if (Array.isArray(props.transactions)) return props.transactions.length;
  return 0;
});

const filterForm = ref({
  search: props.filters?.search || '',
  type: props.filters?.type || '',
  category_id: props.filters?.category_id || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

const showFilterSheet = ref(false);
const tempFilterForm = ref({ ...filterForm.value });
const activePeriodPreset = ref('all');

function formatDateYMD(d) {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function updateActivePreset() {
  const from = tempFilterForm.value.date_from;
  const to = tempFilterForm.value.date_to;
  if (!from && !to) {
    activePeriodPreset.value = 'all';
    return;
  }
  const now = new Date();
  const y = now.getFullYear();
  const m = now.getMonth();
  const thisMonthStart = formatDateYMD(new Date(y, m, 1));
  const thisMonthEnd = formatDateYMD(new Date(y, m + 1, 0));
  if (from === thisMonthStart && to === thisMonthEnd) {
    activePeriodPreset.value = 'this_month';
    return;
  }
  const lastMonthStart = formatDateYMD(new Date(y, m - 1, 1));
  const lastMonthEnd = formatDateYMD(new Date(y, m, 0));
  if (from === lastMonthStart && to === lastMonthEnd) {
    activePeriodPreset.value = 'last_month';
    return;
  }
  activePeriodPreset.value = 'custom';
}

function setPeriodPreset(preset) {
  const now = new Date();
  const y = now.getFullYear();
  const m = now.getMonth();
  
  if (preset === 'this_month') {
    const start = new Date(y, m, 1);
    const end = new Date(y, m + 1, 0);
    tempFilterForm.value.date_from = formatDateYMD(start);
    tempFilterForm.value.date_to = formatDateYMD(end);
    activePeriodPreset.value = 'this_month';
  } else if (preset === 'last_month') {
    const start = new Date(y, m - 1, 1);
    const end = new Date(y, m, 0);
    tempFilterForm.value.date_from = formatDateYMD(start);
    tempFilterForm.value.date_to = formatDateYMD(end);
    activePeriodPreset.value = 'last_month';
  } else if (preset === 'all') {
    tempFilterForm.value.date_from = '';
    tempFilterForm.value.date_to = '';
    activePeriodPreset.value = 'all';
  } else if (preset === 'custom') {
    activePeriodPreset.value = 'custom';
  }
}

const selectedCategoryName = computed(() => {
  if (!filterForm.value.category_id) return '';
  const found = props.categories.find(c => String(c.id) === String(filterForm.value.category_id));
  return found ? found.name : '';
});

const activeDateLabel = computed(() => {
  const from = filterForm.value.date_from;
  const to = filterForm.value.date_to;
  if (!from && !to) return '';
  const now = new Date();
  const y = now.getFullYear();
  const m = now.getMonth();
  const thisMonthStart = formatDateYMD(new Date(y, m, 1));
  const thisMonthEnd = formatDateYMD(new Date(y, m + 1, 0));
  if (from === thisMonthStart && to === thisMonthEnd) return 'Bulan Ini';
  const lastMonthStart = formatDateYMD(new Date(y, m - 1, 1));
  const lastMonthEnd = formatDateYMD(new Date(y, m, 0));
  if (from === lastMonthStart && to === lastMonthEnd) return 'Bulan Lalu';
  return 'Periode Kustom';
});

const activeFiltersCount = computed(() => {
  let count = 0;
  if (filterForm.value.category_id) count++;
  if (filterForm.value.date_from || filterForm.value.date_to) count++;
  return count;
});

const tempActiveCount = computed(() => {
  let count = 0;
  if (tempFilterForm.value.type) count++;
  if (tempFilterForm.value.category_id) count++;
  if (tempFilterForm.value.date_from || tempFilterForm.value.date_to) count++;
  return count;
});

const hasActiveFilters = computed(() => {
  return Boolean(
    filterForm.value.search ||
    filterForm.value.type ||
    filterForm.value.category_id ||
    filterForm.value.date_from ||
    filterForm.value.date_to
  );
});

function applyFilters() {
  router.get(route('transactions.index'), filterForm.value, {
    preserveState: true,
    preserveScroll: true
  });
}

function setTypeFilter(type) {
  filterForm.value.type = type;
  applyFilters();
}

function clearSearch() {
  filterForm.value.search = '';
  applyFilters();
}

function clearCategory() {
  filterForm.value.category_id = '';
  applyFilters();
}

function clearDates() {
  filterForm.value.date_from = '';
  filterForm.value.date_to = '';
  applyFilters();
}

function resetFilters() {
  filterForm.value = {
    search: '',
    type: '',
    category_id: '',
    date_from: '',
    date_to: '',
  };
  applyFilters();
}

function openFilterSheet() {
  tempFilterForm.value = { ...filterForm.value };
  updateActivePreset();
  showFilterSheet.value = true;
}

function closeFilterSheet() {
  showFilterSheet.value = false;
}

function applyFilterSheet() {
  filterForm.value = { ...tempFilterForm.value };
  closeFilterSheet();
  applyFilters();
}

function resetTempFilters() {
  tempFilterForm.value = {
    search: filterForm.value.search,
    type: '',
    category_id: '',
    date_from: '',
    date_to: '',
  };
  activePeriodPreset.value = 'all';
}

function openModal(type) {
  window.dispatchEvent(new KeyboardEvent('keydown', { key: type === 'expense' ? 'e' : 'i' }));
}
</script>
