<template>
  <AdminLayout>
    <Head title="Dashboard Admin" />

    <div class="space-y-6 w-full max-w-[1600px] mx-auto">

      <!-- 4 Key Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Pengguna -->
        <div class="bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-stone-500">Total Pengguna</span>
            <div class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 flex items-center justify-center">
              <Users :size="16" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold text-stone-900 tracking-tight">{{ metrics.users.total }}</div>
            <div class="text-[11px] text-stone-500 mt-1 flex items-center gap-1.5">
              <span class="text-emerald-700 font-semibold">{{ metrics.users.active }} aktif</span>
              <span>•</span>
              <span>+{{ metrics.users.new_this_month }} bulan ini</span>
            </div>
          </div>
        </div>

        <!-- Card 2: Total Pemasukan -->
        <div class="bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-stone-500">Total Arus Masuk</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
              <ArrowDownLeft :size="16" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold text-stone-900 tracking-tight">{{ formatRupiah(metrics.finances.total_inflow) }}</div>
            <div class="text-[11px] text-stone-500 mt-1">
              Bulan ini: <span class="font-medium text-stone-700">{{ formatRupiah(metrics.finances.this_month_inflow) }}</span>
            </div>
          </div>
        </div>

        <!-- Card 3: Total Pengeluaran -->
        <div class="bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-stone-500">Total Arus Keluar</span>
            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
              <ArrowUpRight :size="16" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold text-stone-900 tracking-tight">{{ formatRupiah(metrics.finances.total_outflow) }}</div>
            <div class="text-[11px] text-stone-500 mt-1">
              Bulan ini: <span class="font-medium text-stone-700">{{ formatRupiah(metrics.finances.this_month_outflow) }}</span>
            </div>
          </div>
        </div>

        <!-- Card 4: Transaksi & Kewajiban -->
        <div class="bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-stone-500">Total Transaksi</span>
            <div class="w-8 h-8 rounded-lg bg-stone-100 text-stone-600 flex items-center justify-center">
              <CreditCard :size="16" />
            </div>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold text-stone-900 tracking-tight">{{ metrics.finances.total_transactions }}</div>
            <div class="text-[11px] text-stone-500 mt-1">
              Kewajiban: <span class="font-medium text-stone-700">{{ formatRupiah(metrics.obligations.total_ring_fenced) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Chart & System Summary Row -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Cashflow Chart (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
              <h2 class="text-sm sm:text-base font-bold text-stone-900">Arus Kas (6 Bulan Terakhir)</h2>
              <p class="text-xs text-stone-500 mt-0.5">Perbandingan pemasukan dan pengeluaran akumulatif platform.</p>
            </div>

            <!-- Legend -->
            <div class="flex items-center space-x-3 text-xs bg-stone-50 px-3 py-1.5 rounded-lg border border-stone-200/70 shrink-0">
              <div class="flex items-center space-x-1.5">
                <span class="w-2.5 h-2.5 rounded-sm bg-emerald-600"></span>
                <span class="text-stone-700 font-medium">Pemasukan</span>
              </div>
              <div class="flex items-center space-x-1.5">
                <span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span>
                <span class="text-stone-700 font-medium">Pengeluaran</span>
              </div>
            </div>
          </div>

          <div class="relative w-full h-[260px]">
            <Bar :data="cashFlowChartData" :options="chartOptions" />
          </div>
        </div>

        <!-- System Summary (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs space-y-5">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-stone-900">Ringkasan Sistem</h2>
            <p class="text-xs text-stone-500 mt-0.5">Parameter data bawaan dan modul terdaftar.</p>
          </div>

          <div class="space-y-2.5">
            <div class="p-3 rounded-lg bg-stone-50 border border-stone-200/60 flex items-center justify-between">
              <div class="flex items-center space-x-2.5">
                <Tags :size="15" class="text-stone-500" />
                <span class="text-xs font-medium text-stone-700">Kategori Bawaan</span>
              </div>
              <span class="text-xs font-bold text-stone-900">{{ metrics.system.default_categories }} template</span>
            </div>

            <div class="p-3 rounded-lg bg-stone-50 border border-stone-200/60 flex items-center justify-between">
              <div class="flex items-center space-x-2.5">
                <Target :size="15" class="text-stone-500" />
                <span class="text-xs font-medium text-stone-700">Target Keuangan</span>
              </div>
              <span class="text-xs font-bold text-stone-900">{{ metrics.system.growth_targets }} target</span>
            </div>

            <div class="p-3 rounded-lg bg-stone-50 border border-stone-200/60 flex items-center justify-between">
              <div class="flex items-center space-x-2.5">
                <ShieldCheck :size="15" class="text-stone-500" />
                <span class="text-xs font-medium text-stone-700">Pos Kewajiban Rutin</span>
              </div>
              <span class="text-xs font-bold text-stone-900">{{ metrics.obligations.active_count }} pos</span>
            </div>
          </div>

          <div class="pt-3 border-t border-stone-100 grid grid-cols-2 gap-2">
            <Link
              :href="route('admin.users.index')"
              class="flex items-center justify-center space-x-1.5 py-2 px-3 rounded-lg border border-stone-200 bg-white hover:bg-stone-50 text-stone-700 text-xs font-semibold transition active:scale-[0.98] shadow-2xs"
            >
              <Users :size="14" class="text-stone-500" />
              <span>Kelola Pengguna</span>
            </Link>
            <Link
              :href="route('admin.categories.index')"
              class="flex items-center justify-center space-x-1.5 py-2 px-3 rounded-lg bg-stone-900 hover:bg-stone-800 text-white text-xs font-semibold transition active:scale-[0.98] shadow-2xs"
            >
              <Tags :size="14" class="text-stone-400" />
              <span>Kategori Master</span>
            </Link>
          </div>
        </div>

      </div>

      <!-- Recent Activity Rows (2 cols) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Pengguna Terkini -->
        <div class="bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs">
          <div class="flex items-center justify-between mb-3 pb-2 border-b border-stone-100">
            <div>
              <h2 class="text-sm font-bold text-stone-900">Pengguna Terkini</h2>
              <p class="text-[11px] text-stone-500">Pendaftaran akun pengguna terbaru</p>
            </div>
            <Link
              :href="route('admin.users.index')"
              class="text-xs text-stone-700 hover:text-stone-900 font-semibold hover:underline"
            >
              Lihat Semua →
            </Link>
          </div>

          <div class="divide-y divide-stone-100">
            <div
              v-for="u in recent_users"
              :key="u.id"
              class="py-3 flex items-center justify-between gap-3"
            >
              <div class="flex items-center space-x-3 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-stone-100 text-stone-700 font-bold text-xs flex items-center justify-center shrink-0 border border-stone-200">
                  {{ u.name.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <div class="text-xs font-semibold text-stone-900 truncate">{{ u.name }}</div>
                  <div class="text-[11px] text-stone-500 truncate">{{ u.email }}</div>
                </div>
              </div>

              <div class="flex items-center space-x-2 shrink-0">
                <span
                  :class="[
                    'text-[10px] font-semibold px-2 py-0.5 rounded border',
                    u.role === 'admin'
                      ? 'bg-purple-50 text-purple-700 border-purple-200'
                      : 'bg-stone-50 text-stone-600 border-stone-200'
                  ]"
                >
                  {{ u.role }}
                </span>
                <span
                  :class="[
                    'w-2 h-2 rounded-full',
                    u.is_active ? 'bg-emerald-500' : 'bg-rose-500'
                  ]"
                  :title="u.is_active ? 'Aktif' : 'Ditangguhkan'"
                ></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Transaksi Terkini -->
        <div class="bg-white rounded-xl p-5 border border-stone-200/80 shadow-2xs">
          <div class="flex items-center justify-between mb-3 pb-2 border-b border-stone-100">
            <div>
              <h2 class="text-sm font-bold text-stone-900">Transaksi Terkini</h2>
              <p class="text-[11px] text-stone-500">Pencatatan transaksi terbaru di platform</p>
            </div>
          </div>

          <div class="divide-y divide-stone-100">
            <div
              v-for="tx in recent_transactions"
              :key="tx.id"
              class="py-3 flex items-center justify-between gap-3"
            >
              <div class="min-w-0 flex-1">
                <div class="flex items-center space-x-2">
                  <span
                    :class="[
                      'text-[10px] font-medium px-2 py-0.5 rounded border',
                      tx.type === 'income' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-stone-50 text-stone-600 border-stone-200/80'
                    ]"
                  >
                    {{ tx.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                  </span>
                  <span class="text-xs font-medium text-stone-800 truncate">
                    {{ tx.description }}
                  </span>
                </div>
                <div class="text-[11px] text-stone-400 mt-0.5 truncate">
                  {{ tx.user?.name || 'Pengguna' }} • {{ formatDate(tx.transaction_date) }}
                </div>
              </div>

              <div
                :class="[
                  'text-xs font-semibold shrink-0',
                  tx.type === 'income' ? 'text-emerald-700' : 'text-stone-800'
                ]"
              >
                {{ tx.type === 'income' ? '+' : '-' }}{{ formatRupiah(tx.amount) }}
              </div>
            </div>

            <div v-if="recent_transactions.length === 0" class="py-8 text-center text-stone-400 text-xs">
              Belum ada transaksi tercatat di sistem.
            </div>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  Users,
  ArrowDownLeft,
  ArrowUpRight,
  ShieldCheck,
  Tags,
  Target,
  CreditCard
} from 'lucide-vue-next';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
  metrics: Object,
  chart_data: Array,
  recent_users: Array,
  recent_transactions: Array,
});

function formatRupiah(val) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
  } catch (e) {
    return dateStr;
  }
}

const cashFlowChartData = computed(() => {
  const labels = props.chart_data.map(d => d.label);
  const inflows = props.chart_data.map(d => d.inflow);
  const outflows = props.chart_data.map(d => d.outflow);

  return {
    labels,
    datasets: [
      {
        label: 'Pemasukan',
        backgroundColor: '#059669', // Emerald
        borderRadius: 4,
        data: inflows,
      },
      {
        label: 'Pengeluaran',
        backgroundColor: '#E11D48', // Rose
        borderRadius: 4,
        data: outflows,
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      backgroundColor: '#1C1917',
      titleColor: '#F5F5F4',
      bodyColor: '#E7E5E4',
      padding: 10,
      cornerRadius: 8,
      boxPadding: 4,
      callbacks: {
        label: function(context) {
          return `${context.dataset.label}: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.raw)}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: {
        color: '#78716C',
        font: { size: 11 }
      }
    },
    y: {
      grid: { color: '#F5F5F4' },
      ticks: {
        color: '#78716C',
        font: { size: 10 },
        callback: function(value) {
          if (value >= 1000000) {
            return (value / 1000000).toFixed(0) + ' Jt';
          }
          if (value >= 1000) {
            return (value / 1000).toFixed(0) + ' Rb';
          }
          return value;
        }
      }
    }
  }
};
</script>
