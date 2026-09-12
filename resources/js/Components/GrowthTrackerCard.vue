<template>
  <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-2xs hover:shadow-xs transition-all duration-200">
    
    <!-- Header: Simple, Clean & Focused -->
    <div class="flex items-center justify-between gap-2">
      <span class="text-xs sm:text-sm font-semibold text-slate-500">
        Total Kekayaan Bersih
      </span>
      <Link
        :href="route('growth.index')"
        class="text-xs font-semibold text-slate-600 hover:text-slate-900 py-1 px-2.5 rounded-lg hover:bg-slate-100 transition-colors shrink-0"
      >
        Atur
      </Link>
    </div>

    <!-- Hero Nominal + Clean Percentage Badge -->
    <div class="mt-1 flex items-baseline justify-between flex-wrap gap-x-2 gap-y-1">
      <h2 class="text-2xl sm:text-3xl font-extrabold font-sans text-slate-900 tracking-tight whitespace-nowrap overflow-hidden text-ellipsis">
        {{ formatRupiah(growth.current_net_worth) }}
      </h2>
      <span 
        class="inline-flex items-center text-[11px] sm:text-xs font-sans font-bold px-2 py-0.5 rounded-md shrink-0"
        :class="growth.current_percentage >= 0 
          ? 'bg-emerald-50 text-emerald-700' 
          : 'bg-rose-50 text-rose-700'"
      >
        {{ growth.current_percentage >= 0 ? '+' : '' }}{{ growth.current_percentage }}% bln ini
      </span>
    </div>

    <!-- 3. Trajectory & Comparison Hub: Clean, Frameless & Iconless -->
    <div class="mt-4 pt-3.5 border-t border-slate-100 space-y-3">
      
      <!-- Target Minimal Row (No frame, No icon) -->
      <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
          <div class="text-[11px] sm:text-xs font-bold text-slate-700 uppercase tracking-wider">Target Minimal</div>
          <div class="text-[10px] sm:text-[11px] text-slate-400">Alokasi tabungan bulan ini</div>
        </div>
        <div class="text-right shrink-0">
          <div class="text-sm sm:text-base font-extrabold font-sans text-amber-600">+{{ growth.target_percentage }}%</div>
          <div class="text-[11px] sm:text-xs font-bold font-sans text-slate-700 whitespace-nowrap">{{ formatRupiah(growth.target_savings_amount) }}</div>
        </div>
      </div>

      <!-- Proyeksi Akhir Row (No frame, No icon) -->
      <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
          <div 
            class="text-[11px] sm:text-xs font-bold uppercase tracking-wider"
            :class="growth.is_on_track ? 'text-emerald-700' : 'text-rose-700'"
          >
            Proyeksi Akhir
          </div>
          <div class="text-[10px] sm:text-[11px] text-slate-400">Estimasi akumulasi akhir bulan</div>
        </div>
        <div class="text-right shrink-0">
          <div 
            class="text-sm sm:text-base font-extrabold font-sans"
            :class="growth.is_on_track ? 'text-emerald-600' : 'text-rose-600'"
          >
            +{{ growth.projected_percentage }}%
          </div>
          <div class="text-[11px] sm:text-xs font-bold font-sans text-slate-700 whitespace-nowrap">{{ formatRupiah(growth.projected_net_worth) }}</div>
        </div>
      </div>

      <!-- Humanized Status Narrative & Progress Track -->
      <div class="space-y-1.5 pt-1">
        <!-- Human Narrative Copy -->
        <div class="flex items-center justify-between text-xs gap-2">
          <span class="text-slate-500 text-[11px] sm:text-xs font-medium truncate">
            {{ growth.is_on_track 
              ? 'Melebihi target' 
              : 'Perlu penghematan' }}
          </span>
          <span 
            class="font-sans font-bold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-full shrink-0"
            :class="growth.is_on_track ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
          >
            {{ surplusLabel }}
          </span>
        </div>

        <!-- Tactile Progress Bar -->
        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden flex items-center">
          <div 
            class="h-full rounded-full transition-all duration-700"
            :class="growth.is_on_track 
              ? 'bg-gradient-to-r from-emerald-500 to-teal-400' 
              : 'bg-gradient-to-r from-rose-500 to-amber-500'"
            :style="{ width: `${progressFillPercentage}%` }"
          ></div>
        </div>

        <!-- Footnote context in human language -->
        <div class="flex justify-between items-center text-[10px] sm:text-[11px] text-slate-400 font-sans pt-0.5">
          <span>Target: <strong class="text-slate-600">+{{ growth.target_percentage }}%</strong></span>
          <span class="font-bold" :class="growth.is_on_track ? 'text-emerald-700' : 'text-rose-700'">
            Proyeksi: +{{ growth.projected_percentage }}%
          </span>
        </div>
      </div>

    </div>

    <!-- 4. Ergonomic Thumb-Zone CTA Link -->
    <div class="mt-3.5 pt-1">
      <Link
        :href="route('growth.index')"
        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50/80 active:scale-[0.98] border border-slate-200/80 hover:border-emerald-300 text-slate-700 hover:text-emerald-800 transition group min-h-[44px]"
      >
        <span class="text-xs font-bold">
          Lihat Kurva Pertumbuhan & Strategi
        </span>
        <ChevronRight :size="16" class="text-slate-400 group-hover:text-emerald-700 group-hover:translate-x-0.5 transition shrink-0" />
      </Link>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { formatRupiah } from '@/Utils/formatters';

const props = defineProps({
  growth: {
    type: Object,
    required: true
  }
});

const surplusLabel = computed(() => {
  if (props.growth.is_on_track) {
    const target = Number(props.growth.target_percentage) || 0;
    const projected = Number(props.growth.projected_percentage) || 0;
    const diff = Math.max(0, projected - target);
    return `+${diff.toFixed(1)}% di atas target`;
  } else {
    const gap = props.growth.gap_amount || 0;
    return gap > 0 ? `Kurang ${formatRupiah(gap)}` : 'Di bawah target';
  }
});

const progressFillPercentage = computed(() => {
  const target = Number(props.growth.target_percentage) || 1;
  const projected = Number(props.growth.projected_percentage) || 0;
  if (target <= 0) return 100;
  const ratio = (projected / target) * 100;
  return Math.min(100, Math.max(8, Math.round(ratio)));
});
</script>
