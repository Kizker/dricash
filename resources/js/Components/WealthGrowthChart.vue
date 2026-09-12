<template>
  <div class="w-full h-full flex flex-col">
    <!-- Header & Legend: Fluid Stacking down to 320px -->
    <div class="flex flex-col min-[380px]:flex-row min-[380px]:items-center justify-between gap-2 mb-3 sm:mb-4">
      <!-- Title & Context -->
      <div class="min-w-0">
        <h3 class="text-xs min-[360px]:text-sm font-bold text-slate-900 truncate">
          Kurva Pertumbuhan Kekayaan
        </h3>
        <p class="text-[10px] min-[360px]:text-[11px] text-slate-400 truncate">
          Pergerakan riil vs Target linier
        </p>
      </div>

      <!-- Humanized Minimal Legend -->
      <div class="flex items-center space-x-3 text-[10px] min-[360px]:text-[11px] shrink-0 self-start min-[380px]:self-auto bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
        <div class="flex items-center space-x-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 shadow-2xs"></span>
          <span class="text-slate-700 font-medium">Aktual</span>
        </div>
        <div class="flex items-center space-x-1.5">
          <span class="w-2.5 h-0.5 rounded-xs bg-amber-500 border-t border-dashed border-amber-600 shrink-0"></span>
          <span class="text-slate-700 font-medium">Target</span>
        </div>
      </div>
    </div>

    <!-- Chart Canvas Container -->
    <div class="relative flex-1 min-h-[200px] xs:min-h-[240px] w-full min-w-0 overflow-hidden">
      <Line :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const props = defineProps({
  chartDataRaw: {
    type: Object,
    required: true
  }
});

const chartData = computed(() => {
  return {
    labels: props.chartDataRaw?.labels || [],
    datasets: [
      {
        label: 'Net Worth Aktual',
        data: props.chartDataRaw?.actual || [],
        borderColor: '#059669',
        backgroundColor: 'rgba(5, 150, 105, 0.08)',
        borderWidth: 2.5,
        pointBackgroundColor: '#059669',
        pointBorderColor: '#FFFFFF',
        pointBorderWidth: 2,
        pointRadius: 2.5,
        pointHoverRadius: 6,
        fill: true,
        tension: 0.35,
        spanGaps: false,
      },
      {
        label: 'Target Pertumbuhan',
        data: props.chartDataRaw?.target || [],
        borderColor: 'rgba(217, 119, 6, 0.85)',
        backgroundColor: 'transparent',
        borderWidth: 2,
        borderDash: [5, 4],
        pointRadius: 0,
        fill: false,
        tension: 0,
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
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
      boxPadding: 4,
      usePointStyle: true,
      callbacks: {
        label: function (context) {
          const val = context.parsed.y;
          if (val === null || val === undefined) return '';
          return ` ${context.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(val)}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: false, // Menghilangkan vertical grid lines agar layar mobile tidak sumpek
        drawBorder: false,
      },
      ticks: {
        color: '#94A3B8',
        font: {
          family: "'Poppins', sans-serif",
          size: 9,
          weight: '600',
        },
        maxRotation: 0,
        autoSkip: true,
        maxTicksLimit: 7, // Membatasi label tanggal di mobile agar tidak bertabrakan
      }
    },
    y: {
      grid: {
        color: '#F1F5F9', // Gridline horizontal halus & tenang
        drawBorder: false,
        borderDash: [3, 3],
      },
      ticks: {
        color: '#94A3B8',
        font: {
          family: "'Poppins', sans-serif",
          size: 9,
          weight: '600',
        },
        maxTicksLimit: 5, // Batasi 5 tick vertikal untuk visual yang lapang
        callback: function (val) {
          if (val >= 1_000_000_000) {
            const m = val / 1_000_000_000;
            return `${m.toFixed(m % 1 === 0 ? 0 : 1)} M`;
          }
          if (val >= 1_000_000) {
            const jt = val / 1_000_000;
            // Presisi 1 desimal jika ada pecahan agar tidak terjadi tick duplikat (misal: 36.5 Jt vs 37 Jt)
            return `${jt.toFixed(jt % 1 === 0 ? 0 : 1)} Jt`;
          }
          if (val >= 1_000) {
            return `${(val / 1_000).toFixed(0)} Rb`;
          }
          return `${val}`;
        }
      }
    }
  }
};
</script>

