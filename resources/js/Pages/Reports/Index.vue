<template>
  <AppLayout>
    <InertiaHead title="Laporan" />

    <!-- 1. SIMPLIFIED HEADER (Centered & Clean - Sesuai View Mutasi) -->
    <div class="text-center py-2 min-[360px]:py-3 mb-2 sm:mb-4">
      <div class="flex items-center justify-center gap-1.5">
        <h1 class="text-xl min-[360px]:text-2xl font-black text-slate-900 tracking-tight font-sans">
          Laporan
        </h1>
        <span class="px-2 py-0.5 rounded-full text-[10px] min-[360px]:text-[11px] font-sans font-bold bg-emerald-500/10 text-emerald-700 border border-emerald-500/20">
          {{ period.month_name }}
        </span>
      </div>
      <p class="text-xs text-slate-400 mt-0.5 font-medium">
        Kinerja & Arus Kas Finansial Bulanan
      </p>
    </div>

    <!-- 2. PERIOD SELECTOR & ACTIONS TOOLBAR -->
    <div class="flex items-center justify-between gap-2 p-1.5 bg-slate-100/90 rounded-2xl mb-4 border border-slate-200/80">
      <div class="flex items-center gap-1.5 flex-1 min-w-0">
        <!-- Month Select -->
        <div class="relative flex-1 min-w-0">
          <select
            v-model="selectedMonth"
            @change="changePeriod"
            class="w-full appearance-none bg-white border border-slate-200/90 rounded-xl px-2.5 py-1.5 pr-7 text-xs font-bold text-slate-800 focus:border-emerald-500 shadow-2xs outline-none cursor-pointer truncate"
          >
            <option v-for="(name, num) in monthNames" :key="num" :value="num">
              {{ name }}
            </option>
          </select>
          <ChevronDown :size="13" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>

        <!-- Year Select -->
        <div class="relative w-20 min-[360px]:w-22 shrink-0">
          <select
            v-model="selectedYear"
            @change="changePeriod"
            class="w-full appearance-none bg-white border border-slate-200/90 rounded-xl px-2.5 py-1.5 pr-6 text-xs font-bold text-slate-800 focus:border-emerald-500 shadow-2xs outline-none cursor-pointer text-center"
          >
            <option :value="2025">2025</option>
            <option :value="2026">2026</option>
            <option :value="2027">2027</option>
          </select>
          <ChevronDown :size="13" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>
      </div>

      <!-- Print Button -->
      <button
        type="button"
        @click="printReport"
        class="h-8 px-2.5 min-[360px]:px-3 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold shadow-2xs hover:text-slate-900 active:scale-95 transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer"
        title="Cetak Laporan"
      >
        <Printer :size="14" class="text-slate-500" />
        <span class="hidden min-[360px]:inline">Cetak</span>
      </button>
    </div>

    <!-- 3. FINANCIAL SUMMARY (Unified Card on Mobile, 4-Box Grid on Desktop) -->
    <!-- Mobile View (< sm): Unified Executive Cashflow Card -->
    <div class="sm:hidden bg-white rounded-2xl p-4 shadow-xs border border-slate-200/90 space-y-3 mb-4">
      <!-- Hero Metric: Net Cash Flow -->
      <div class="flex items-baseline justify-between gap-2">
        <div class="min-w-0">
          <span class="text-[10px] min-[360px]:text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">
            Net Cash Flow (Arus Kas Bersih)
          </span>
          <div 
            class="text-xl min-[360px]:text-2xl font-black font-sans tracking-tight mt-0.5 truncate"
            :class="cashflow.net_cashflow >= 0 ? 'text-emerald-600' : 'text-rose-600'"
          >
            {{ formatRupiah(cashflow.net_cashflow) }}
          </div>
        </div>
        <div class="text-right shrink-0">
          <span 
            class="px-2 py-0.5 rounded-full text-[10px] font-sans font-bold block"
            :class="cashflow.net_cashflow >= 0 
              ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' 
              : 'bg-rose-50 text-rose-700 border border-rose-200'"
          >
            {{ cashflow.net_cashflow >= 0 ? 'Surplus' : 'Defisit' }}
          </span>
          <span class="text-[10px] text-slate-400 block font-medium mt-0.5">
            Tabungan: {{ cashflow.total_income > 0 ? Math.round((cashflow.net_cashflow / cashflow.total_income) * 100) : 0 }}%
          </span>
        </div>
      </div>

      <!-- 2-Tier Sub-Stats in Fluid Grid (No Truncation) -->
      <div class="space-y-1.5 pt-2.5 border-t border-slate-100">
        <!-- Row 1: Pemasukan & Pengeluaran -->
        <div class="grid grid-cols-2 gap-2">
          <!-- Total Pemasukan -->
          <div class="p-2.5 rounded-xl bg-emerald-50/60 border border-emerald-100/80 min-w-0">
            <span class="text-[10px] font-semibold text-emerald-800 uppercase tracking-wider block truncate">
              Pemasukan
            </span>
            <div class="text-xs min-[360px]:text-sm font-black font-sans text-emerald-700 mt-0.5 truncate">
              {{ formatRupiah(cashflow.total_income) }}
            </div>
          </div>

          <!-- Total Pengeluaran -->
          <div class="p-2.5 rounded-xl bg-rose-50/60 border border-rose-100/80 min-w-0">
            <span class="text-[10px] font-semibold text-rose-800 uppercase tracking-wider block truncate">
              Pengeluaran
            </span>
            <div class="text-xs min-[360px]:text-sm font-black font-sans text-rose-700 mt-0.5 truncate">
              {{ formatRupiah(cashflow.total_expenses) }}
            </div>
          </div>
        </div>

        <!-- Row 2: Beban Kewajiban (Full Width) -->
        <div class="p-2.5 rounded-xl bg-amber-50/60 border border-amber-100/80 flex items-center justify-between gap-2 min-w-0">
          <div class="min-w-0">
            <span class="text-[10px] font-semibold text-amber-900 uppercase tracking-wider block truncate">
              Beban Kewajiban
            </span>
            <div class="text-xs min-[360px]:text-sm font-black font-sans text-amber-800 mt-0.5 truncate">
              {{ formatRupiah(obligations.total_amount) }}
            </div>
          </div>
          <span class="text-[10px] font-bold font-sans text-amber-700 px-2 py-0.5 rounded-md bg-amber-100/80 shrink-0">
            {{ cashflow.total_income > 0 ? Math.round((obligations.total_amount / cashflow.total_income) * 100) : 0 }}% Pemasukan
          </span>
        </div>
      </div>
    </div>

    <!-- Desktop View (>= sm): 4 Separate Diagnostic KPI Cards -->
    <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
      <!-- Total Income -->
      <div class="glass-panel rounded-2xl p-4 min-w-0">
        <span class="text-[10px] uppercase font-bold text-slate-500 flex items-center space-x-1 truncate">
          <ArrowUpRight :size="13" class="text-emerald-600 shrink-0" />
          <span class="truncate">Total Pemasukan</span>
        </span>
        <div class="text-xl lg:text-2xl font-black font-sans text-emerald-600 mt-1 truncate">
          {{ formatRupiah(cashflow.total_income) }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5 truncate">
          Arus kas masuk aktif
        </div>
      </div>

      <!-- Total Expenses -->
      <div class="glass-panel rounded-2xl p-4 min-w-0">
        <span class="text-[10px] uppercase font-bold text-slate-500 flex items-center space-x-1 truncate">
          <ArrowDownRight :size="13" class="text-rose-500 shrink-0" />
          <span class="truncate">Total Pengeluaran</span>
        </span>
        <div class="text-xl lg:text-2xl font-black font-sans text-rose-600 mt-1 truncate">
          {{ formatRupiah(cashflow.total_expenses) }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5 truncate">
          Harian + Kewajiban Lunas
        </div>
      </div>

      <!-- Net Cashflow -->
      <div class="glass-panel rounded-2xl p-4 min-w-0">
        <span class="text-[10px] uppercase font-bold text-slate-500 flex items-center space-x-1 truncate">
          <DollarSign :size="13" class="text-amber-600 shrink-0" />
          <span class="truncate">Net Cash Flow (Surplus)</span>
        </span>
        <div 
          class="text-xl lg:text-2xl font-black font-sans mt-1 truncate"
          :class="cashflow.net_cashflow >= 0 ? 'text-emerald-600' : 'text-rose-600'"
        >
          {{ formatRupiah(cashflow.net_cashflow) }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5 font-sans truncate">
          Savings Rate: {{ cashflow.total_income > 0 ? Math.round((cashflow.net_cashflow / cashflow.total_income) * 100) : 0 }}%
        </div>
      </div>

      <!-- Obligation Ratio -->
      <div class="glass-panel rounded-2xl p-4 min-w-0">
        <span class="text-[10px] uppercase font-bold text-slate-500 flex items-center space-x-1 truncate">
          <Lock :size="13" class="text-amber-600 shrink-0" />
          <span class="truncate">Beban Kewajiban</span>
        </span>
        <div class="text-xl lg:text-2xl font-black font-sans text-slate-900 mt-1 truncate">
          {{ formatRupiah(obligations.total_amount) }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5 font-sans truncate">
          {{ cashflow.total_income > 0 ? Math.round((obligations.total_amount / cashflow.total_income) * 100) : 0 }}% dari pemasukan
        </div>
      </div>
    </div>

    <!-- 4. MOBILE SEGMENTED TABS (Chart Switcher: Kurva vs Kategori) -->
    <div class="lg:hidden flex items-center p-1 bg-slate-100/90 rounded-2xl mb-4 border border-slate-200/80">
      <button
        type="button"
        @click="activeChartTab = 'growth'"
        class="flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition text-center cursor-pointer min-h-[38px] active:scale-95 flex items-center justify-center gap-1.5"
        :class="activeChartTab === 'growth' 
          ? 'bg-white text-slate-900 shadow-2xs font-extrabold' 
          : 'text-slate-500 hover:text-slate-800'"
      >
        <LineChart :size="14" />
        <span>Kurva Kekayaan</span>
      </button>
      <button
        type="button"
        @click="activeChartTab = 'categories'"
        class="flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition text-center cursor-pointer min-h-[38px] active:scale-95 flex items-center justify-center gap-1.5"
        :class="activeChartTab === 'categories' 
          ? 'bg-white text-slate-900 shadow-2xs font-extrabold' 
          : 'text-slate-500 hover:text-slate-800'"
      >
        <PieChart :size="14" />
        <span>Distribusi Pengeluaran</span>
      </button>
    </div>

    <!-- 5. CHARTS BREAKDOWN (Mobile Tabbed / Desktop Side-by-Side 12-Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 mb-4 sm:mb-6">
      
      <!-- Left: Net Worth Trajectory -->
      <div 
        class="lg:col-span-7 glass-panel rounded-2xl sm:rounded-3xl p-4 sm:p-6 min-h-[320px] sm:min-h-[360px]"
        :class="activeChartTab === 'growth' ? 'block' : 'hidden lg:block'"
      >
        <WealthGrowthChart
          :chartDataRaw="charts.growth"
        />
      </div>

      <!-- Right: Category Expense Distribution -->
      <div 
        class="lg:col-span-5 glass-panel rounded-2xl sm:rounded-3xl p-4 sm:p-6 min-h-[320px] sm:min-h-[360px]"
        :class="activeChartTab === 'categories' ? 'block' : 'hidden lg:block'"
      >
        <CategoryPieChart
          :categoriesData="charts.categories"
        />
      </div>

    </div>

    <!-- 6. EXECUTIVE WEALTH PLANNER SUMMARY -->
    <div class="glass-panel rounded-2xl sm:rounded-3xl p-4 sm:p-6 border-l-4 border-l-emerald-500">
      <div class="flex items-center space-x-2 text-emerald-700 font-bold text-xs sm:text-sm mb-3">
        <Sparkles :size="16" />
        <span>Rangkuman Eksekutif Wealth Planner</span>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-4 text-xs text-slate-600">
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
          <div class="text-[10px] sm:text-xs text-slate-500 font-semibold mb-0.5">Rata-rata Pengeluaran</div>
          <div class="text-sm sm:text-base font-sans font-bold text-slate-900">
            {{ formatRupiah(period.today_day > 0 ? cashflow.total_expenses / period.today_day : 0) }} / hari
          </div>
        </div>

        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
          <div class="text-[10px] sm:text-xs text-slate-500 font-semibold mb-0.5">Sisa Jatah Harian Rata-rata</div>
          <div class="text-sm sm:text-base font-sans font-bold text-emerald-600">
            {{ formatRupiah(daily_budget.today_budget) }} / hari
          </div>
        </div>

        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
          <div class="text-[10px] sm:text-xs text-slate-500 font-semibold mb-0.5">Status Target Pertumbuhan</div>
          <div 
            class="text-sm sm:text-base font-sans font-bold"
            :class="growth.is_on_track ? 'text-emerald-600' : 'text-rose-600'"
          >
            +{{ growth.projected_percentage }}% ({{ growth.is_on_track ? 'Sesuai Rencana' : 'Perlu Penghematan' }})
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import WealthGrowthChart from '@/Components/WealthGrowthChart.vue';
import CategoryPieChart from '@/Components/CategoryPieChart.vue';
import { 
  ArrowUpRight, 
  ArrowDownRight, 
  DollarSign, 
  Lock, 
  Sparkles, 
  Printer, 
  ChevronDown, 
  LineChart, 
  PieChart 
} from 'lucide-vue-next';
import { formatRupiah } from '@/Utils/formatters';

const props = defineProps({
  metrics: Object,
  period: Object,
  cashflow: Object,
  obligations: Object,
  daily_budget: Object,
  growth: Object,
  charts: Object,
});

const activeChartTab = ref('growth'); // 'growth' | 'categories'

const selectedMonth = ref(props.period?.month || 9);
const selectedYear = ref(props.period?.year || 2026);

const monthNames = {
  1: 'Januari',
  2: 'Februari',
  3: 'Maret',
  4: 'April',
  5: 'Mei',
  6: 'Juni',
  7: 'Juli',
  8: 'Agustus',
  9: 'September',
  10: 'Oktober',
  11: 'November',
  12: 'Desember',
};

function changePeriod() {
  router.get(route('reports.index'), {
    month: selectedMonth.value,
    year: selectedYear.value,
  }, {
    preserveState: true,
    preserveScroll: true
  });
}

function printReport() {
  if (typeof window !== 'undefined') {
    window.print();
  }
}
</script>
