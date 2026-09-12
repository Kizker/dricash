<template>
  <AppLayout>
    <Head title="Beranda" />

    <!-- 1. BRImo Hero Green Header & Saldo Utama Section (Human-Centric Fluid Mobile Design) -->
    <div class="brimo-hero-header rounded-2xl min-[360px]:rounded-3xl p-4 min-[360px]:p-5 sm:p-7 text-white mb-4 sm:mb-6 relative">
      
      <!-- Top Row: User Greeting & Quick Action Icons -->
      <div class="flex items-center justify-between gap-2.5 relative z-10">
        <button
          type="button"
          @click="openProfileMenu"
          class="flex items-center space-x-2.5 min-w-0 flex-1 text-left cursor-pointer group active:opacity-85 transition"
          title="Buka Menu Profil"
          aria-label="Buka Profil Akun"
        >
          <img
            v-if="authUser?.avatar_url"
            :src="authUser.avatar_url"
            alt="Avatar"
            class="w-10 h-10 min-[360px]:w-11 min-[360px]:h-11 rounded-full object-cover border-2 border-white/85 shadow-sm shrink-0 group-hover:border-white transition"
          />
          <div
            v-else
            class="w-10 h-10 min-[360px]:w-11 min-[360px]:h-11 rounded-full bg-white/20 backdrop-blur-md border-2 border-white/40 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm group-hover:border-white transition"
          >
            {{ userFirstName.charAt(0) }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[10px] min-[360px]:text-[11px] text-emerald-100 font-medium truncate">
              <span>{{ greetingTime }}</span>
              <span class="hidden min-[380px]:inline font-sans font-medium text-emerald-200"> • {{ metrics.period.month_name }}</span>
            </div>
            <h1 class="text-sm min-[360px]:text-base sm:text-lg font-black text-white truncate tracking-tight group-hover:text-emerald-50 transition">
              Halo, {{ userFirstName }} 👋
            </h1>
          </div>
        </button>
      </div>

      <!-- Centerpiece: Saldo Utama / Net Worth Card -->
      <div class="mt-4 pt-3.5 border-t border-white/15 relative z-10">
        <!-- Label & Status with Inline Eye Privacy Toggle -->
        <div class="flex items-center space-x-2">
          <span class="text-[10px] min-[360px]:text-[11px] uppercase tracking-wider font-bold text-emerald-100">
            Total Kekayaan Bersih
          </span>
          <span class="px-1.5 py-0.5 rounded-md text-[8px] min-[360px]:text-[9px] bg-white/20 text-white font-bold tracking-wider">
            UTAMA
          </span>
          <!-- Inline Eye Icon -->
          <button
            type="button"
            @click="isBalanceVisible = !isBalanceVisible"
            class="text-emerald-100/80 hover:text-white active:scale-90 transition p-1 flex items-center justify-center cursor-pointer"
            :title="isBalanceVisible ? 'Sembunyikan Saldo' : 'Tampilkan Saldo'"
            aria-label="Toggle Saldo Visibility"
          >
            <Eye v-if="isBalanceVisible" :size="15" />
            <EyeOff v-else :size="15" />
          </button>
        </div>

        <!-- Large Fluid Balance Typography -->
        <div class="text-2xl min-[360px]:text-3xl sm:text-4xl font-extrabold font-sans tracking-tight text-white mt-1 leading-tight whitespace-nowrap">
          {{ isBalanceVisible ? formatRupiah(metrics.growth.current_net_worth) : 'Rp ••••••••••' }}
        </div>

        <!-- Growth Indicator & Context -->
        <div class="mt-1.5 flex items-center flex-wrap gap-1.5 text-[10px] min-[360px]:text-[11px] text-emerald-100 font-medium">
          <span class="bg-emerald-900/40 text-emerald-200 px-2 py-0.5 rounded-lg border border-emerald-400/20 font-bold font-sans">
            {{ metrics.growth.current_percentage >= 0 ? '+' : '' }}{{ metrics.growth.current_percentage }}% bulan ini
          </span>
          <span class="opacity-75">·</span>
          <span class="truncate">Hari ke-{{ metrics.period.today_day }} (Sisa {{ metrics.period.days_remaining }} hari)</span>
        </div>

        <!-- Thumb-Zone Call to Action (CTA) Buttons (Full Width 50/50 Tactile Buttons) -->
        <div class="grid grid-cols-2 gap-2 min-[360px]:gap-2.5 mt-3.5">
          <button
            type="button"
            @click="openIncomeModal"
            class="btn-hero-inflow active:scale-[0.97] transition-all flex items-center justify-center h-11 min-[360px]:h-12 rounded-xl min-[360px]:rounded-2xl font-bold text-xs min-[360px]:text-sm shadow-sm cursor-pointer"
          >
            <span>Pemasukan</span>
          </button>
          <button
            type="button"
            @click="openSpendModal"
            class="btn-hero-outflow active:scale-[0.97] transition-all flex items-center justify-center h-11 min-[360px]:h-12 rounded-xl min-[360px]:rounded-2xl font-bold text-xs min-[360px]:text-sm shadow-sm cursor-pointer"
          >
            <span>Pengeluaran</span>
          </button>
        </div>

        <!-- Inflow, Outflow & Sisa Arus Kas Ribbon (Fluid Responsive & Human-Centric) -->
        <div class="mt-3.5 pt-3 border-t border-white/12 space-y-2">
          
          <!-- Inflow & Outflow 2-Column Summary -->
          <div class="grid grid-cols-2 gap-2 min-[360px]:gap-2.5">
            <!-- Inflow Pod -->
            <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md rounded-xl min-[360px]:rounded-2xl p-2.5 border border-white/15 min-w-0 transition">
              <div class="text-[9px] min-[360px]:text-[10px] uppercase font-bold text-emerald-100 flex items-center space-x-1 truncate">
                <ArrowDownLeft :size="12" class="text-emerald-300 shrink-0" />
                <span class="truncate">Pemasukan</span>
              </div>
              <div class="text-[11px] min-[360px]:text-xs sm:text-sm font-bold text-white mt-1 truncate tracking-tight font-sans">
                {{ isBalanceVisible ? formatRupiah(metrics.cashflow.total_income) : '••••••' }}
              </div>
            </div>

            <!-- Outflow Pod -->
            <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md rounded-xl min-[360px]:rounded-2xl p-2.5 border border-white/15 min-w-0 transition">
              <div class="text-[9px] min-[360px]:text-[10px] uppercase font-bold text-rose-100 flex items-center space-x-1 truncate">
                <ArrowUpRight :size="12" class="text-rose-300 shrink-0" />
                <span class="truncate">Pengeluaran</span>
              </div>
              <div class="text-[11px] min-[360px]:text-xs sm:text-sm font-bold text-white mt-1 truncate tracking-tight font-sans">
                {{ isBalanceVisible ? formatRupiah(metrics.cashflow.total_expenses) : '••••••' }}
              </div>
            </div>
          </div>

          <!-- Sisa Arus Kas (Tactile Surplus Ribbon with Safe Retention Bar) -->
          <div class="bg-black/20 backdrop-blur-md rounded-xl min-[360px]:rounded-2xl p-2.5 min-[360px]:p-3 border border-white/15 min-w-0">
            <div class="flex items-center justify-between gap-2">
              <div class="min-w-0 flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0 shadow-2xs"></span>
                <span class="text-[10px] min-[360px]:text-[11px] font-bold text-emerald-100 uppercase tracking-wider truncate">
                  Sisa Arus Kas
                </span>
                <span class="hidden min-[400px]:inline-block px-1.5 py-0.2 rounded text-[8px] bg-emerald-400/20 text-emerald-200 font-bold">
                  Surplus
                </span>
              </div>
              
              <div class="text-right shrink-0 flex items-baseline space-x-1.5">
                <span class="text-xs min-[360px]:text-sm font-black text-white tracking-tight font-sans">
                  {{ isBalanceVisible ? formatRupiah(metrics.cashflow.total_income - metrics.cashflow.total_expenses) : '••••••' }}
                </span>
                <span class="text-[9px] min-[360px]:text-[10px] font-bold text-emerald-300 bg-emerald-950/60 px-1.5 py-0.5 rounded-md border border-emerald-400/30">
                  {{ netCashflowPercent }}%
                </span>
              </div>
            </div>

            <!-- Micro Retention Progress Line -->
            <div class="mt-2 w-full h-1.5 bg-white/10 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-emerald-400 to-teal-300 rounded-full transition-all duration-500"
                :style="{ width: `${Math.min(100, Math.max(0, netCashflowPercent))}%` }"
              ></div>
            </div>
          </div>

        </div>

      </div>

    </div>

    <!-- 2. Mobile Focus Switcher (Pill Tray for progressive disclosure on small screens - Text Only, Tanpa Icon) -->
    <div class="lg:hidden flex items-center space-x-1.5 overflow-x-auto pb-2 mb-4 scrollbar-none select-none -mx-3.5 px-3.5 sm:mx-0 sm:px-0">
      <button
        v-for="tab in mobileFocusTabs"
        :key="tab.id"
        type="button"
        @click="activeMobileTab = tab.id"
        class="btn-human btn-human-sm whitespace-nowrap shrink-0 px-3.5 py-1.5"
        :class="activeMobileTab === tab.id
          ? 'btn-human-primary border-2 border-emerald-400 font-bold'
          : 'btn-human-secondary text-slate-600 font-semibold'"
      >
        <span>{{ tab.label }}</span>
      </button>
    </div>

    <!-- 4. Core Financial Coach & Pos Keuangan Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
      
      <!-- Left Column (5 Cols on LG): Dynamic Daily Budgeting & Ring-Fencing Checklist -->
      <div 
        class="lg:col-span-5 min-w-0 space-y-4 sm:space-y-6"
        :class="{ 'hidden lg:block': activeMobileTab !== 'all' && activeMobileTab !== 'daily' && activeMobileTab !== 'obligations' }"
      >
        
        <!-- 1. Kalkulator Jatah Harian Card -->
        <DailyBudgetCard
          v-if="activeMobileTab === 'all' || activeMobileTab === 'daily'"
          :data="metrics.daily_budget"
        />

        <!-- 2. Ring-Fencing Kebutuhan Bulanan Checklist -->
        <RingFenceCard
          v-if="activeMobileTab === 'all' || activeMobileTab === 'obligations'"
          :obligations="metrics.obligations"
        />

      </div>

      <!-- Right Column (7 Cols on LG): Growth Tracker, Charts & Visualizations -->
      <div 
        class="lg:col-span-7 min-w-0 space-y-4 sm:space-y-6"
        :class="{ 'hidden lg:block': activeMobileTab !== 'all' && activeMobileTab !== 'growth' && activeMobileTab !== 'charts' }"
      >
        
        <!-- 3. Growth Tracker & Target Gauge Card -->
        <GrowthTrackerCard
          v-if="activeMobileTab === 'all' || activeMobileTab === 'growth'"
          :growth="metrics.growth"
        />

        <!-- 4. Wealth Growth Chart & Category Breakdown Stacked -->
        <div v-if="activeMobileTab === 'all' || activeMobileTab === 'charts'" class="grid grid-cols-1 gap-4 sm:gap-6">
          
          <!-- Net Worth Line Chart -->
          <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm border border-slate-200/80">
            <WealthGrowthChart
              :chartDataRaw="metrics.charts.growth"
            />
          </div>

          <!-- Category Doughnut Distribution Chart -->
          <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm border border-slate-200/80">
            <CategoryPieChart
              :categoriesData="metrics.charts.categories"
            />
          </div>

        </div>

      </div>

    </div>

    <!-- 5. Bottom Section: Recent Transactions Ledger (BRImo Mutasi Feed) -->
    <div class="mt-6 sm:mt-8">
      <TransactionLedgerTable
        :transactions="metrics.recent_transactions"
        :allowActions="true"
        @quick-income="openIncomeModal"
        @quick-expense="openSpendModal"
      />
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DailyBudgetCard from '@/Components/DailyBudgetCard.vue';
import RingFenceCard from '@/Components/RingFenceCard.vue';
import GrowthTrackerCard from '@/Components/GrowthTrackerCard.vue';
import WealthGrowthChart from '@/Components/WealthGrowthChart.vue';
import CategoryPieChart from '@/Components/CategoryPieChart.vue';
import TransactionLedgerTable from '@/Components/TransactionLedgerTable.vue';
import {
  ArrowDownLeft,
  ArrowUpRight,
  Eye,
  EyeOff
} from 'lucide-vue-next';
import { formatRupiah } from '@/Utils/formatters';

const props = defineProps({
  metrics: {
    type: Object,
    required: true
  },
  categories: {
    type: Array,
    default: () => []
  }
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const isBalanceVisible = ref(true);

const userFirstName = computed(() => {
  const name = authUser.value?.name || 'Teman';
  return name.split(' ')[0];
});

const greetingTime = computed(() => {
  const hour = new Date().getHours();
  if (hour >= 4 && hour < 11) return 'Selamat Pagi';
  if (hour >= 11 && hour < 15) return 'Selamat Siang';
  if (hour >= 15 && hour < 18) return 'Selamat Sore';
  return 'Selamat Malam';
});



const netCashflowPercent = computed(() => {
  const inc = props.metrics?.cashflow?.total_income || 0;
  const exp = props.metrics?.cashflow?.total_expenses || 0;
  if (inc <= 0) return 0;
  const net = inc - exp;
  return Math.max(0, Math.round((net / inc) * 100));
});

const activeMobileTab = ref('all');

const mobileFocusTabs = [
  { id: 'all', label: 'Semua Ikhtisar' },
  { id: 'daily', label: 'Jatah Harian' },
  { id: 'obligations', label: 'Kewajiban' },
  { id: 'growth', label: 'Target Kekayaan' },
  { id: 'charts', label: 'Grafik & Analisis' },
];

function openSpendModal() {
  window.dispatchEvent(new KeyboardEvent('keydown', { key: 'e' }));
}

function openIncomeModal() {
  window.dispatchEvent(new KeyboardEvent('keydown', { key: 'i' }));
}

function openProfileMenu() {
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('open-profile-menu'));
  }
}
</script>

