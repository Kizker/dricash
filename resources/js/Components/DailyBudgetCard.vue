<template>
  <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 relative overflow-hidden transition shadow-sm border border-slate-200/80 hover:shadow-md">
    
    <!-- Background Ambient Glow -->
    <div 
      class="absolute -right-12 -top-12 w-48 h-48 rounded-full blur-3xl pointer-events-none opacity-20"
      :class="statusGlowClass"
    ></div>

    <!-- Header Section -->
    <div class="relative z-10 flex items-start justify-between gap-2">
      <div>
        <div class="flex items-center space-x-2 flex-wrap gap-y-1">
          <span class="text-xs sm:text-sm font-semibold text-slate-500 shrink-0">
            Jatah Harian
          </span>
          <span 
            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold border flex items-center space-x-1 shrink-0"
            :class="statusBadgeClass"
          >
            <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass"></span>
            <span>{{ statusLabel }}</span>
          </span>

          <!-- Mode Indicator Badge -->
          <span
            v-if="data.mode === 'manual'"
            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center space-x-1 shrink-0"
          >
            <SlidersHorizontal :size="10" />
            <span>Atur Sendiri</span>
          </span>
          <span
            v-else
            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center space-x-1 shrink-0"
          >
            <Sparkles :size="10" />
            <span>Otomatis (Pintar)</span>
          </span>
        </div>
        
        <div class="mt-1 flex items-baseline flex-wrap gap-x-1.5">
          <h2 class="text-2xl xs:text-3xl sm:text-4xl font-extrabold font-sans tracking-tight text-slate-900 whitespace-nowrap">
            {{ formatRupiah(data.today_budget) }}
          </h2>
          <span class="text-xs font-medium text-slate-400 shrink-0">/ hari ini</span>
        </div>

        <p v-if="data.mode === 'manual'" class="text-[11px] sm:text-xs text-slate-500 mt-0.5">
          Jatah tetap yang Anda tentukan sendiri
        </p>
        <p v-else class="text-[11px] sm:text-xs text-slate-500 mt-0.5">
          Menyesuaikan otomatis dengan sisa uang & tabungan
        </p>
      </div>

      <!-- Button Trigger Settings Modal -->
      <button
        id="daily-budget-open-modal-btn"
        type="button"
        @click="openModal"
        class="inline-flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 active:scale-95 transition cursor-pointer shadow-2xs shrink-0"
        title="Atur Mode & Nominal Jatah Harian"
      >
        <SlidersHorizontal :size="13" />
        <span>Atur</span>
      </button>
    </div>

    <!-- Multi-Tier Visual Progress Bar -->
    <div class="mt-4 sm:mt-5 space-y-2 relative z-10">
      <div class="flex items-center justify-between text-xs font-medium flex-wrap gap-1">
        <span class="text-slate-600">
          Terpakai: <strong class="text-slate-900 font-sans">{{ formatRupiah(data.spent_today) }}</strong>
        </span>
        <span :class="remainingColorClass" class="font-sans font-bold">
          Sisa: {{ formatRupiah(data.remaining_today) }}
        </span>
      </div>

      <!-- Progress Track -->
      <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200 relative">
        <div
          class="h-full rounded-full transition-all duration-700 ease-out shadow-xs"
          :class="progressBarColorClass"
          :style="{ width: `${Math.min(100, data.progress_percentage)}%` }"
        ></div>
      </div>
      
      <div class="flex justify-between items-center text-[9px] sm:text-[10px] text-slate-400 font-sans">
        <span>0%</span>
        <span>70% Batas Waspada</span>
        <span>100% Limit</span>
      </div>
    </div>

    <!-- Mode-Specific Summary Cards -->
    <div class="mt-4 sm:mt-5 pt-3 sm:pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 relative z-10">
      
      <!-- When in Manual Mode -->
      <template v-if="data.mode === 'manual'">
        <!-- Card 1: Active Manual Target -->
        <div class="flex items-center space-x-2.5 sm:space-x-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
          <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center shrink-0 shadow-2xs">
            <SlidersHorizontal :size="16" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500">
              Jatah Manual Tetap
            </div>
            <div class="text-xs sm:text-sm font-sans font-bold text-indigo-700 truncate mt-0.5">
              {{ formatRupiah(data.manual_budget || data.today_budget) }} / hari
            </div>
          </div>
        </div>

        <!-- Card 2: Smart Recommendation Reference -->
        <div class="flex items-center space-x-2.5 sm:space-x-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
          <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
            <Sparkles :size="16" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500">
              Rekomendasi Pintar
            </div>
            <div class="text-xs sm:text-sm font-sans font-bold text-emerald-700 truncate mt-0.5">
              {{ formatRupiah(data.auto_budget ?? data.today_budget) }} / hari
            </div>
          </div>
        </div>
      </template>

      <!-- When in Auto Mode -->
      <template v-else>
        <!-- Rollover Status -->
        <div class="flex items-center space-x-2.5 sm:space-x-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
          <div 
            class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border shadow-2xs"
            :class="data.rollover_delta >= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-600' : 'bg-amber-50 border-amber-200 text-amber-600'"
          >
            <Sparkles v-if="data.rollover_delta >= 0" :size="16" />
            <RefreshCw v-else :size="16" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500">
              {{ data.rollover_delta >= 0 ? 'Sistem Reward' : 'Penyesuaian Defisit' }}
            </div>
            <div 
              class="text-xs sm:text-sm font-sans font-bold truncate mt-0.5"
              :class="data.rollover_delta >= 0 ? 'text-emerald-700' : 'text-amber-700'"
            >
              {{ data.rollover_delta >= 0 ? '+' : '' }}{{ formatRupiah(data.rollover_delta) }} / hari
            </div>
          </div>
        </div>

        <!-- Baseline Comparison -->
        <div class="flex items-center space-x-2.5 sm:space-x-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
          <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center shrink-0 shadow-2xs">
            <Clock :size="16" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-500">
              Jatah Awal Bulan
            </div>
            <div class="text-xs sm:text-sm font-sans font-bold text-slate-800 truncate mt-0.5">
              {{ formatRupiah(data.baseline_allowance) }} / hari
            </div>
          </div>
        </div>
      </template>

    </div>

    <!-- Modal / Bottom Sheet: Atur Mode Jatah Harian -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-hidden"
    >
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
        @click="closeModal"
      ></div>

      <!-- Modal Card -->
      <div
        class="relative w-full sm:max-w-lg bg-white rounded-t-[28px] sm:rounded-3xl shadow-2xl border border-slate-100 z-10 animate-in slide-in-from-bottom sm:zoom-in-95 duration-200 flex flex-col max-h-[92vh] sm:max-h-[88vh] overflow-hidden"
      >
        <!-- Mobile Pull Notch -->
        <div class="pt-3 pb-1 flex justify-center sm:hidden shrink-0">
          <div class="w-10 h-1.5 bg-slate-200 rounded-full"></div>
        </div>

        <!-- Header -->
        <div class="px-4 sm:px-6 pt-2 pb-3.5 sm:pt-4 border-b border-slate-100 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200/70 text-indigo-600 flex items-center justify-center shrink-0 shadow-xs">
              <SlidersHorizontal :size="20" stroke-width="2.2" />
            </div>
            <div class="min-w-0">
              <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-tight truncate">Pengaturan Jatah Harian</h3>
              <p class="text-xs text-slate-500 mt-0.5 leading-snug">Pilih mode perhitungan yang sesuai dengan gaya finansial Anda</p>
            </div>
          </div>
          <button
            type="button"
            @click="closeModal"
            class="w-10 h-10 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:scale-95 transition flex items-center justify-center cursor-pointer shrink-0 ml-2"
            aria-label="Tutup"
          >
            <X :size="19" stroke-width="2.2" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="saveSettings" class="flex flex-col flex-1 min-h-0 overflow-hidden">
          <div class="flex-1 overflow-y-auto overscroll-contain px-4 sm:px-6 pt-4 pb-6 space-y-4">
            
            <!-- Option 1: Auto Mode Card -->
            <div
              id="daily-budget-mode-auto"
              @click="form.daily_budget_mode = 'auto'"
              class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition select-none"
              :class="form.daily_budget_mode === 'auto' ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
            >
              <input
                type="radio"
                name="daily_budget_mode"
                value="auto"
                v-model="form.daily_budget_mode"
                class="sr-only"
              />
              <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mr-3.5 mt-0.5 shadow-2xs">
                <Sparkles :size="18" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-sm font-bold text-slate-900">Mode Otomatis (Rekomendasi Pintar)</span>
                  <div
                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition"
                    :class="form.daily_budget_mode === 'auto' ? 'border-emerald-600 bg-emerald-600' : 'border-slate-300'"
                  >
                    <div v-if="form.daily_budget_mode === 'auto'" class="w-2 h-2 rounded-full bg-white"></div>
                  </div>
                </div>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                  Menyesuaikan otomatis berdasarkan sisa uang tunai yang dimiliki, kewajiban bulanan, dan target tabungan Anda.
                </p>
                <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100/70 text-emerald-800 text-xs font-medium">
                  <span>Estimasi saat ini:</span>
                  <strong class="font-bold">{{ formatRupiah(data.auto_budget ?? data.today_budget) }} / hari</strong>
                </div>
              </div>
            </div>

            <!-- Option 2: Manual Mode Card -->
            <div
              id="daily-budget-mode-manual"
              @click="form.daily_budget_mode = 'manual'"
              class="relative flex items-start p-4 rounded-2xl border-2 cursor-pointer transition select-none"
              :class="form.daily_budget_mode === 'manual' ? 'border-indigo-500 bg-indigo-50/40 ring-2 ring-indigo-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
            >
              <input
                type="radio"
                name="daily_budget_mode"
                value="manual"
                v-model="form.daily_budget_mode"
                class="sr-only"
              />
              <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mr-3.5 mt-0.5 shadow-2xs">
                <SlidersHorizontal :size="18" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-sm font-bold text-slate-900">Mode Atur Sendiri (Nominal Tetap)</span>
                  <div
                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition"
                    :class="form.daily_budget_mode === 'manual' ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300'"
                  >
                    <div v-if="form.daily_budget_mode === 'manual'" class="w-2 h-2 rounded-full bg-white"></div>
                  </div>
                </div>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                  Tentukan sendiri batas pengeluaran jatah harian tetap sesuai target disiplin atau batas belanja Anda.
                </p>
              </div>
            </div>

            <!-- Manual Amount Input (Only when Manual is chosen) -->
            <div
              v-if="form.daily_budget_mode === 'manual'"
              class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200/80 space-y-3 animate-in fade-in zoom-in-95 duration-200"
            >
              <label class="block text-xs font-bold text-indigo-950 uppercase tracking-wider">
                Nominal Jatah Harian (Rp / Hari)
              </label>

              <!-- Currency Input -->
              <div class="relative rounded-xl shadow-xs">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                  <span class="text-slate-400 font-bold text-sm">Rp</span>
                </div>
                <input
                  id="daily-budget-manual-input"
                  type="text"
                  inputmode="numeric"
                  :value="formattedManualAmount"
                  @input="onManualAmountInput"
                  placeholder="Contoh: 50.000"
                  class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3.5 text-base font-bold text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                  required
                />
              </div>

              <!-- Quick Presets -->
              <div>
                <span class="text-[11px] font-semibold text-slate-500 block mb-1.5">Pilihan Cepat:</span>
                <div class="flex flex-wrap gap-1.5">
                  <button
                    type="button"
                    v-for="preset in [25000, 50000, 75000, 100000, 150000]"
                    :key="preset"
                    :data-preset="preset"
                    @click="setPresetAmount(preset)"
                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition active:scale-95 cursor-pointer"
                    :class="form.manual_daily_budget === preset ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                  >
                    {{ formatRupiah(preset) }}
                  </button>
                </div>
              </div>

              <p v-if="validationError" class="text-xs text-rose-600 font-medium">
                {{ validationError }}
              </p>
            </div>

          </div>

          <!-- Footer Actions -->
          <div class="px-4 sm:px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-200/70 rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
            <button
              id="daily-budget-submit-btn"
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 active:scale-95 rounded-xl transition shadow-sm cursor-pointer disabled:opacity-50 disabled:pointer-events-none flex items-center gap-2"
            >
              <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              <span>Simpan Pengaturan</span>
            </button>
          </div>
        </form>

      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Sparkles, RefreshCw, Clock, SlidersHorizontal, X } from 'lucide-vue-next';
import { formatRupiah, formatThousands, parseThousands } from '@/Utils/formatters';

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

const showModal = ref(false);
const validationError = ref('');

const form = useForm({
  daily_budget_mode: props.data.mode || 'auto',
  manual_daily_budget: props.data.manual_budget > 0 ? props.data.manual_budget : null,
});

const formattedManualAmount = ref(
  props.data.manual_budget > 0 ? formatThousands(props.data.manual_budget) : ''
);

// Keep form synced whenever props change
watch(() => props.data, (newData) => {
  if (!showModal.value) {
    form.daily_budget_mode = newData.mode || 'auto';
    form.manual_daily_budget = newData.manual_budget > 0 ? newData.manual_budget : null;
    formattedManualAmount.value = newData.manual_budget > 0 ? formatThousands(newData.manual_budget) : '';
  }
}, { deep: true });

const openModal = () => {
  validationError.value = '';
  form.daily_budget_mode = props.data.mode || 'auto';
  form.manual_daily_budget = props.data.manual_budget > 0 ? props.data.manual_budget : null;
  formattedManualAmount.value = props.data.manual_budget > 0 ? formatThousands(props.data.manual_budget) : '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  validationError.value = '';
};

const onManualAmountInput = (e) => {
  const val = e.target.value;
  const num = parseThousands(val);
  form.manual_daily_budget = num > 0 ? num : null;
  formattedManualAmount.value = num > 0 ? formatThousands(num) : '';
  if (validationError.value) {
    validationError.value = '';
  }
};

const setPresetAmount = (num) => {
  form.manual_daily_budget = num;
  formattedManualAmount.value = formatThousands(num);
  form.daily_budget_mode = 'manual';
  validationError.value = '';
};

const saveSettings = () => {
  if (form.daily_budget_mode === 'manual' && (!form.manual_daily_budget || form.manual_daily_budget <= 0)) {
    validationError.value = 'Silakan masukkan nominal jatah harian manual yang valid.';
    return;
  }

  const endpoint = (typeof route === 'function')
    ? route('daily-budget.settings')
    : (window.route ? window.route('daily-budget.settings') : '/daily-budget/settings');

  form.post(endpoint, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
    },
  });
};

const statusLabel = computed(() => {
  if (props.data.status === 'overbudget') return 'Overbudget';
  if (props.data.status === 'warning') return 'Waspada';
  return 'Aman & Terkendali';
});

const statusGlowClass = computed(() => {
  if (props.data.status === 'overbudget') return 'bg-rose-500';
  if (props.data.status === 'warning') return 'bg-amber-500';
  return 'bg-emerald-500';
});

const statusBadgeClass = computed(() => {
  if (props.data.status === 'overbudget') return 'bg-rose-50 border-rose-200 text-rose-700';
  if (props.data.status === 'warning') return 'bg-amber-50 border-amber-200 text-amber-700';
  return 'bg-emerald-50 border-emerald-200 text-emerald-700';
});

const statusDotClass = computed(() => {
  if (props.data.status === 'overbudget') return 'bg-rose-500 animate-ping';
  if (props.data.status === 'warning') return 'bg-amber-500';
  return 'bg-emerald-500';
});

const progressBarColorClass = computed(() => {
  if (props.data.status === 'overbudget') return 'bg-gradient-to-r from-rose-600 to-rose-400';
  if (props.data.status === 'warning') return 'bg-gradient-to-r from-amber-500 to-amber-400';
  return 'bg-gradient-to-r from-emerald-500 to-teal-400';
});

const remainingColorClass = computed(() => {
  if (props.data.remaining_today < 0) return 'text-rose-600';
  if (props.data.status === 'warning') return 'text-amber-600';
  return 'text-emerald-700';
});
</script>
