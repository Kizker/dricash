<template>
  <AppLayout>
    <InertiaHead title="Kekayaan" />

    <!-- 1. SIMPLIFIED HEADER (Centered & Clean - Sesuai View Mutasi) -->
    <div class="text-center py-2 min-[360px]:py-3 mb-2 sm:mb-4">
      <div class="flex items-center justify-center gap-1.5">
        <h1 class="text-xl min-[360px]:text-2xl font-black text-slate-900 tracking-tight font-sans">
          Kekayaan
        </h1>
        <span class="px-2 py-0.5 rounded-full text-[10px] min-[360px]:text-[11px] font-sans font-bold bg-emerald-500/10 text-emerald-700 border border-emerald-500/20">
          {{ period.month_name }}
        </span>
      </div>
      <p class="text-xs text-slate-400 mt-0.5 font-medium">
        Target & Proyeksi Pertumbuhan
      </p>
    </div>

    <!-- 2. MOBILE TABS SWITCHER (Thumb-Friendly 1-Tap Navigation) -->
    <div class="lg:hidden flex items-center p-1 bg-slate-100/90 rounded-2xl mb-4 border border-slate-200/80">
      <button
        type="button"
        @click="activeMobileTab = 'chart'"
        class="flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition text-center cursor-pointer min-h-[38px] active:scale-95 flex items-center justify-center gap-1.5"
        :class="activeMobileTab === 'chart' 
          ? 'bg-white text-slate-900 shadow-2xs font-extrabold' 
          : 'text-slate-500 hover:text-slate-800'"
      >
        <LineChart :size="14" />
        <span>Kurva & Proyeksi</span>
      </button>
      <button
        type="button"
        @click="activeMobileTab = 'settings'"
        class="flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition text-center cursor-pointer min-h-[38px] active:scale-95 flex items-center justify-center gap-1.5"
        :class="activeMobileTab === 'settings' 
          ? 'bg-white text-slate-900 shadow-2xs font-extrabold' 
          : 'text-slate-500 hover:text-slate-800'"
      >
        <Sliders :size="14" />
        <span>Atur Target (+{{ form.target_growth_percentage }}%)</span>
      </button>
    </div>

    <!-- Core Grid: Target Config Form & Real-time Wealth Trajectory -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
      
      <!-- Left Config & Stats (5 Cols on LG - Target Settings Form) -->
      <div 
        class="lg:col-span-5 space-y-4 sm:space-y-6"
        :class="activeMobileTab === 'settings' ? 'block' : 'hidden lg:block'"
      >
        <!-- Target Settings Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-2xs">
          <div class="pb-3 border-b border-slate-100">
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Target Pertumbuhan Bulan Ini</h3>
            <p class="text-xs text-slate-400 mt-0.5">Tentukan target persentase atau nominal tabungan</p>
          </div>

          <form @submit.prevent="submitTarget" class="mt-4 space-y-4">
            <!-- Target Percentage Input (Fluid Wrap on 320px) -->
            <div>
              <div class="flex justify-between items-center mb-1.5">
                <label class="text-[11px] sm:text-xs font-semibold text-slate-600">
                  Target Kenaikan Aset (%)
                </label>
                <span class="text-xs font-sans font-bold text-amber-600">
                  +{{ form.target_growth_percentage }}%
                </span>
              </div>
              
              <div class="flex items-center space-x-2.5 sm:space-x-3">
                <input
                  v-model.number="form.target_growth_percentage"
                  type="range"
                  min="0"
                  max="25"
                  step="0.5"
                  class="w-full accent-amber-500 cursor-pointer"
                />
                <input
                  v-model.number="form.target_growth_percentage"
                  type="number"
                  step="0.1"
                  min="0"
                  max="100"
                  class="w-16 sm:w-20 bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl px-2 py-1.5 text-xs font-sans font-bold text-slate-900 text-center outline-none shrink-0"
                />
              </div>
            </div>

            <!-- Target Savings Override -->
            <div>
              <label class="block text-[11px] sm:text-xs font-semibold text-slate-600 mb-1.5">
                Target Tabungan Minimal (Opsional)
              </label>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-sans text-sm font-bold">
                  Rp
                </span>
                <input
                  v-model="formattedTargetSavings"
                  type="text"
                  inputmode="numeric"
                  placeholder="Dihitung otomatis jika kosong"
                  class="w-full bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl pl-11 pr-3.5 py-2.5 text-xs sm:text-sm font-sans text-slate-900 transition outline-none"
                  @input="onTargetSavingsInput"
                />
              </div>
              <p class="text-[11px] text-slate-400 mt-1">
                Otomatis: <strong class="text-slate-600 font-semibold">{{ formatRupiah(calculatedTargetSavings) }}</strong> (+{{ form.target_growth_percentage }}% dari uang yang dipunya)
              </p>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-[11px] sm:text-xs font-semibold text-slate-600 mb-1.5">
                Catatan Rencana Finansial (Opsional)
              </label>
              <textarea
                v-model="form.notes"
                rows="2"
                placeholder="Rencana alokasi dana tabungan..."
                class="w-full bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl px-3.5 py-2 text-xs text-slate-900 transition outline-none"
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="form.processing"
              class="btn-human btn-human-primary btn-human-lg w-full font-bold"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Target Pertumbuhan' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Right Column: Trajectory Chart & Projections (7 Cols on LG) -->
      <div 
        class="lg:col-span-7 space-y-4 sm:space-y-6"
        :class="activeMobileTab === 'chart' ? 'block' : 'hidden lg:block'"
      >
        <!-- Metrics Summary Card (Unified on Mobile, 3-Box on Desktop) -->
        <!-- Mobile View (< sm) -->
        <div class="sm:hidden bg-white rounded-2xl p-4 shadow-xs border border-slate-200/90 space-y-3">
          <!-- Hero Net Worth -->
          <div class="flex items-baseline justify-between gap-2">
            <div class="min-w-0">
              <span class="text-[10px] min-[360px]:text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">
                Kekayaan Bersih Saat Ini
              </span>
              <div class="text-xl min-[360px]:text-2xl font-black font-sans text-emerald-600 tracking-tight mt-0.5 truncate">
                {{ formatRupiah(metrics.current_net_worth) }}
              </div>
            </div>
            <div class="text-right shrink-0">
              <span class="text-xs font-bold font-sans text-emerald-600 block">
                {{ metrics.current_percentage >= 0 ? '+' : '' }}{{ metrics.current_percentage }}%
              </span>
              <span class="text-[10px] text-slate-400 block font-medium">
                Bulan Ini
              </span>
            </div>
          </div>

          <!-- Two Compact Sub-Stats (Side by Side) -->
          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
            <!-- Saldo Awal Bulan -->
            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 min-w-0">
              <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block truncate">
                Saldo Awal Bulan
              </span>
              <div class="text-xs min-[360px]:text-sm font-black font-sans text-slate-800 mt-0.5 truncate">
                {{ formatRupiah(metrics.starting_net_worth) }}
              </div>
            </div>

            <!-- Proyeksi Akhir -->
            <div 
              class="p-2.5 rounded-xl border min-w-0"
              :class="metrics.is_on_track 
                ? 'bg-emerald-50/70 border-emerald-100/80 text-emerald-800' 
                : 'bg-rose-50/70 border-rose-100/80 text-rose-800'"
            >
              <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold uppercase tracking-wider truncate">
                  Proyeksi
                </span>
                <span class="text-[9px] font-bold">
                  +{{ metrics.projected_percentage }}%
                </span>
              </div>
              <div class="text-xs min-[360px]:text-sm font-black font-sans mt-0.5 truncate">
                {{ formatRupiah(metrics.projected_net_worth) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop View (>= sm) 3-Box Grid -->
        <div class="hidden sm:grid sm:grid-cols-3 gap-2.5 sm:gap-3">
          <div class="glass-panel rounded-2xl p-3.5 sm:p-4 min-w-0">
            <span class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500 truncate block">Saldo Awal Bulan</span>
            <div class="text-base sm:text-lg font-sans font-bold text-slate-900 mt-0.5 truncate">
              {{ formatRupiah(metrics.starting_net_worth) }}
            </div>
          </div>

          <div class="glass-panel rounded-2xl p-3.5 sm:p-4 min-w-0">
            <span class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500 truncate block">Uang Saat Ini</span>
            <div class="text-base sm:text-lg font-sans font-bold text-emerald-600 mt-0.5 truncate">
              {{ formatRupiah(metrics.current_net_worth) }}
            </div>
            <div class="text-[10px] text-slate-500 font-sans truncate">
              {{ metrics.current_percentage >= 0 ? '+' : '' }}{{ metrics.current_percentage }}% bulan ini
            </div>
          </div>

          <div class="glass-panel rounded-2xl p-3.5 sm:p-4 min-w-0">
            <span class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500 truncate block">Proyeksi Akhir Bulan</span>
            <div 
              class="text-base sm:text-lg font-sans font-bold mt-0.5 truncate"
              :class="metrics.is_on_track ? 'text-emerald-600' : 'text-rose-600'"
            >
              {{ formatRupiah(metrics.projected_net_worth) }}
            </div>
            <div class="text-[10px] font-sans truncate" :class="metrics.is_on_track ? 'text-emerald-600' : 'text-rose-600'">
              +{{ metrics.projected_percentage }}% ({{ metrics.is_on_track ? 'Aman' : 'Rendah' }})
            </div>
          </div>
        </div>

        <!-- Trajectory Chart Container -->
        <div class="glass-panel rounded-2xl sm:rounded-3xl p-4 sm:p-6 min-h-[320px]">
          <WealthGrowthChart
            :chartDataRaw="charts.growth"
          />
        </div>

        <!-- Mobile Quick Target Action Card -->
        <div class="lg:hidden p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3">
          <div class="min-w-0">
            <span class="text-xs font-bold text-slate-800 block truncate">Target: +{{ form.target_growth_percentage }}% ({{ formatRupiah(calculatedTargetSavings) }})</span>
            <span class="text-[10px] text-slate-400 block truncate">Target aktif bulan ini</span>
          </div>
          <button
            type="button"
            @click="activeMobileTab = 'settings'"
            class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold shadow-2xs hover:bg-slate-100 active:scale-95 transition shrink-0 cursor-pointer min-h-[34px]"
          >
            Ubah
          </button>
        </div>

      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import WealthGrowthChart from '@/Components/WealthGrowthChart.vue';
import { LineChart, Sliders } from 'lucide-vue-next';
import { formatRupiah, formatThousands, parseThousands } from '@/Utils/formatters';

const activeMobileTab = ref('chart'); // 'chart' | 'settings'

const props = defineProps({
  metrics: {
    type: Object,
    required: true
  },
  cashflow: {
    type: Object,
    required: true
  },
  charts: {
    type: Object,
    required: true
  },
  period: {
    type: Object,
    required: true
  },
  currentTarget: {
    type: Object,
    required: true
  }
});

const form = useForm({
  period_month: props.period.month,
  period_year: props.period.year,
  target_growth_percentage: props.currentTarget.target_growth_percentage,
  target_savings_amount: props.currentTarget.target_savings_amount || '',
  notes: props.currentTarget.notes || '',
});

const formattedTargetSavings = ref(formatThousands(props.currentTarget.target_savings_amount || ''));

function onTargetSavingsInput(e) {
  const val = parseThousands(e.target.value);
  form.target_savings_amount = val > 0 ? val : '';
  formattedTargetSavings.value = val > 0 ? formatThousands(val) : '';
}

const currentMoneyOwned = computed(() => {
  return Number(props.metrics.current_net_worth) > 0 
    ? Number(props.metrics.current_net_worth) 
    : (Number(props.metrics.starting_net_worth) || 0);
});

const calculatedTargetSavings = computed(() => {
  if (form.target_savings_amount && Number(form.target_savings_amount) > 0) {
    return Number(form.target_savings_amount);
  }
  const base = currentMoneyOwned.value > 0 ? currentMoneyOwned.value : (Number(props.cashflow.total_income) || 0);
  return base * (Number(form.target_growth_percentage) / 100);
});

function submitTarget() {
  form.post(route('growth.update'), {
    preserveScroll: true
  });
}
</script>
