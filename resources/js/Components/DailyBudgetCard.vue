<template>
  <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 relative overflow-hidden transition shadow-sm border border-slate-200/80 hover:shadow-md">
    
    <!-- Background Ambient Glow -->
    <div 
      class="absolute -right-12 -top-12 w-48 h-48 rounded-full blur-3xl pointer-events-none opacity-20"
      :class="statusGlowClass"
    ></div>

    <!-- Header Section -->
    <div class="relative z-10">
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
      </div>
      
      <div class="mt-1 flex items-baseline flex-wrap gap-x-1.5">
        <h2 class="text-2xl xs:text-3xl sm:text-4xl font-extrabold font-sans tracking-tight text-slate-900 whitespace-nowrap">
          {{ formatRupiah(data.today_budget) }}
        </h2>
        <span class="text-xs font-medium text-slate-400 shrink-0">/ hari ini</span>
      </div>
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

    <!-- Rollover & Reward Delta Card -->
    <div class="mt-4 sm:mt-5 pt-3 sm:pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 relative z-10">
      
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
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Sparkles, RefreshCw, Clock } from 'lucide-vue-next';
import { formatRupiah } from '@/Utils/formatters';

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

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
