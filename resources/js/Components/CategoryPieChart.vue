<template>
  <div class="w-full h-full flex flex-col">
    <!-- Header: Fluid Stacking down to 320px -->
    <div class="flex items-center justify-between gap-2 mb-3 sm:mb-4">
      <div class="min-w-0">
        <h3 class="text-xs min-[360px]:text-sm font-bold text-slate-900 truncate">
          Distribusi Pengeluaran
        </h3>
        <p class="text-[10px] min-[360px]:text-[11px] text-slate-400 truncate">
          {{ categoriesData.items?.length || 0 }} Kategori aktif bulan ini
        </p>
      </div>
      
      <!-- Header Total Badge (Clean Font Mono) -->
      <div class="text-[11px] min-[360px]:text-xs font-sans font-bold text-slate-800 shrink-0 bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-200/70">
        {{ formatRupiah(categoriesData.total) }}
      </div>
    </div>

    <!-- Chart & Ranked Categories Container -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center flex-1">
      
      <!-- Doughnut Canvas with Hero Centerpiece -->
      <div class="sm:col-span-5 relative flex items-center justify-center py-2">
        <div class="w-36 h-36 min-[360px]:w-40 min-[360px]:h-40 relative">
          <Doughnut v-if="processedItems.length > 0" :data="chartData" :options="chartOptions" />
          <div v-else class="w-full h-full rounded-full border-2 border-dashed border-slate-200 flex items-center justify-center text-xs text-slate-400">
            Belum ada data
          </div>
          
          <!-- Center Text Overlay -->
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center px-2">
            <span class="text-[8px] min-[360px]:text-[9px] uppercase tracking-wider font-bold text-slate-400">
              Total Biaya
            </span>
            <span class="text-xs min-[360px]:text-sm font-black font-sans text-slate-900 tracking-tight mt-0.5">
              {{ formatCompactRupiah(categoriesData.total) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Ranked Category List (Clean Progressive Disclosure without Scroll Trap) -->
      <div class="sm:col-span-7 space-y-2 min-w-0">
        <div
          v-for="(cat, idx) in displayedItems"
          :key="cat.name"
          class="p-2.5 rounded-xl bg-slate-50/80 hover:bg-slate-50 border border-slate-200/70 transition space-y-1.5"
        >
          <!-- Top Row: Dot, Name, Amount, Percentage -->
          <div class="flex items-center justify-between gap-2 text-xs">
            <div class="flex items-center space-x-2 min-w-0 flex-1">
              <span 
                class="w-2.5 h-2.5 rounded-full shrink-0 shadow-2xs" 
                :style="{ backgroundColor: cat.displayColor }"
              ></span>
              <span class="text-slate-800 font-semibold truncate text-[11px] min-[360px]:text-xs">
                {{ cat.name }}
              </span>
            </div>

            <div class="text-right shrink-0 flex items-baseline space-x-1.5">
              <span class="font-sans font-bold text-slate-900 text-[11px] min-[360px]:text-xs">
                {{ formatRupiah(cat.amount) }}
              </span>
              <span class="text-[9px] min-[360px]:text-[10px] font-sans font-bold px-1.5 py-0.2 rounded-md bg-white border border-slate-200/80 text-slate-600">
                {{ cat.percentage }}%
              </span>
            </div>
          </div>

          <!-- Micro Proportion Progress Bar -->
          <div class="w-full h-1 bg-slate-200/70 rounded-full overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-500"
              :style="{ 
                width: `${Math.min(100, Math.max(2, cat.percentage))}%`,
                backgroundColor: cat.displayColor 
              }"
            ></div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="processedItems.length === 0" class="text-center py-6 text-xs text-slate-400">
          Belum ada pengeluaran tercatat di bulan ini.
        </div>

        <!-- Progressive Disclosure Toggle (Avoids Mobile Scroll Trap) -->
        <button
          v-if="processedItems.length > 3"
          type="button"
          @click="isExpanded = !isExpanded"
          class="w-full py-1.5 text-center text-[11px] font-bold text-slate-500 hover:text-emerald-700 bg-slate-100/70 hover:bg-emerald-50 rounded-xl transition flex items-center justify-center space-x-1 cursor-pointer active:scale-98"
        >
          <span>{{ isExpanded ? 'Tampilkan Lebih Sedikit' : `Lihat Semua (${processedItems.length} Kategori)` }}</span>
          <ChevronDown v-if="!isExpanded" :size="13" />
          <ChevronUp v-else :size="13" />
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Doughnut } from 'vue-chartjs';
import { ChevronDown, ChevronUp } from 'lucide-vue-next';
import { formatRupiah, formatCompactRupiah } from '@/Utils/formatters';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
  categoriesData: {
    type: Object,
    default: () => ({ total: 0, items: [] })
  }
});

const isExpanded = ref(false);

// Curated Harmonious Fintech Color Palette (Distinctive & High Visual Contrast)
const HARMONIOUS_PALETTE = [
  '#F59E0B', // Warm Amber (e.g. Sewa Kos)
  '#0EA5E9', // Sky Blue (e.g. Listrik & Utilitas)
  '#10B981', // Emerald (e.g. Belanja)
  '#EC4899', // Rose Pink (e.g. Hiburan)
  '#8B5CF6', // Purple (e.g. Transport)
  '#F97316', // Orange
  '#14B8A6', // Teal
  '#64748B', // Slate
];

const processedItems = computed(() => {
  const items = props.categoriesData?.items || [];
  return items.map((cat, idx) => {
    // Memberikan warna distinct agar tidak terjadi duplikasi dot warna oranye yang identik
    const displayColor = cat.color && cat.color !== '#F59E0B' && !items.slice(0, idx).some(prev => prev.color === cat.color)
      ? cat.color
      : HARMONIOUS_PALETTE[idx % HARMONIOUS_PALETTE.length];

    return {
      ...cat,
      displayColor
    };
  });
});

const displayedItems = computed(() => {
  if (isExpanded.value || processedItems.value.length <= 3) {
    return processedItems.value;
  }
  return processedItems.value.slice(0, 3);
});

const chartData = computed(() => {
  return {
    labels: processedItems.value.map(i => i.name),
    datasets: [
      {
        data: processedItems.value.map(i => i.amount),
        backgroundColor: processedItems.value.map(i => i.displayColor),
        borderColor: '#FFFFFF',
        borderWidth: 2.5,
        hoverOffset: 4,
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '74%',
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      backgroundColor: '#FFFFFF',
      titleColor: '#0F172A',
      bodyColor: '#475569',
      titleFont: {
        family: "'Poppins', sans-serif",
        weight: '700',
      },
      bodyFont: {
        family: "'Poppins', sans-serif",
        weight: '600',
      },
      borderColor: '#E2E8F0',
      borderWidth: 1,
      padding: 10,
      usePointStyle: true,
      callbacks: {
        label: function (context) {
          const val = context.parsed;
          return ` ${context.label}: Rp ${new Intl.NumberFormat('id-ID').format(val)}`;
        }
      }
    }
  }
};
</script>

