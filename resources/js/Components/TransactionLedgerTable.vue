<template>
  <div class="bg-white rounded-2xl sm:rounded-3xl p-3.5 min-[360px]:p-4 sm:p-6 shadow-sm border border-slate-200/80">
    
    <!-- Table Header (Conditional) -->
    <div v-if="showHeader" class="pb-3.5 sm:pb-4 border-b border-slate-100">
      <h3 class="text-sm min-[360px]:text-base font-bold text-slate-900">
        {{ title }}
      </h3>
      <p class="text-[10px] min-[360px]:text-[11px] text-slate-400 mt-0.5">
        {{ subtitle }}
      </p>
    </div>

    <!-- 1. MOBILE CARD FEED VIEW (Adaptive, Fluid down to 320px, Human-Centric) -->
    <div class="sm:hidden mt-2 space-y-3">
      <div
        v-for="group in groupedTransactions"
        :key="group.dateKey"
        class="space-y-1"
      >
        <!-- Subtle Date Divider with Temporal Rhythm -->
        <div class="flex items-center justify-between px-1 pt-2 pb-1 border-b border-slate-100/90">
          <span class="text-[10px] min-[360px]:text-[11px] font-bold uppercase tracking-wider text-slate-400">
            {{ group.dateLabel }}
          </span>
          <span class="text-[9px] min-[360px]:text-[10px] text-slate-400 font-sans font-medium">
            {{ group.items.length }} Transaksi
          </span>
        </div>

        <!-- Transaction Feed Cards -->
        <div class="divide-y divide-slate-100/70">
          <div
            v-for="tx in group.items"
            :key="tx.id"
            @click="openDetail(tx)"
            class="py-2.5 px-1 min-[360px]:py-3 min-[360px]:px-1.5 flex items-center justify-between gap-2.5 rounded-2xl hover:bg-slate-50/90 active:bg-slate-100/80 active:scale-[0.99] transition-all cursor-pointer min-h-[56px]"
          >
            <!-- Left: Title & Category / Context -->
            <div class="min-w-0 flex-1">
              <div class="text-xs min-[360px]:text-sm font-bold text-slate-900 truncate">
                {{ tx.description }}
              </div>
              
              <div class="flex items-center space-x-1.5 mt-0.5 flex-wrap gap-y-0.5">
                <span class="text-[10px] min-[360px]:text-[11px] text-slate-500 font-medium truncate max-w-[180px]">
                  {{ tx.category?.name || (tx.obligation ? 'Kewajiban' : 'Umum') }}
                </span>
                
                <span
                  v-if="tx.obligation"
                  class="px-1.5 py-0.2 rounded-md text-[8px] min-[360px]:text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80 shrink-0"
                >
                  Kewajiban
                </span>
                <span
                  v-if="tx.is_growth_overridden"
                  class="px-1.5 py-0.2 rounded-md text-[8px] min-[360px]:text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80 shrink-0"
                >
                  Override
                </span>
              </div>
            </div>

            <!-- Right: Nominal & Payment Method (Green for Income, Red for Expense) -->
            <div class="text-right shrink-0">
              <div 
                class="font-sans font-extrabold text-xs min-[360px]:text-sm tracking-tight whitespace-nowrap"
                :class="tx.type === 'income' ? 'text-emerald-600' : 'text-rose-600'"
              >
                {{ tx.type === 'income' ? '+' : '-' }}{{ formatRupiah(tx.amount) }}
              </div>
              <div class="text-[9px] min-[360px]:text-[10px] text-slate-400 font-sans mt-0.5 truncate max-w-[95px]">
                {{ tx.payment_method || 'Bank' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="transactions.length === 0" class="text-center py-10 px-4">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-3 border border-slate-200/60">
          <ReceiptText :size="22" />
        </div>
        <div class="text-xs font-bold text-slate-700">Belum ada data transaksi</div>
        <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
          Catat pengeluaran atau pemasukan pertamamu untuk mulai memonitor arus kas.
        </p>
      </div>
    </div>

    <!-- 2. DESKTOP/TABLET TABLE VIEW (Hidden on mobile, high info density for larger viewports) -->
    <div class="hidden sm:block overflow-x-auto mt-2">
      <table class="w-full text-left text-xs sm:text-sm">
        <thead>
          <tr class="border-b border-slate-100 text-slate-400 text-[10px] sm:text-[11px] uppercase tracking-wider font-semibold">
            <th class="py-3 px-3.5">Tanggal</th>
            <th class="py-3 px-3.5">Kategori</th>
            <th class="py-3 px-3.5">Keterangan</th>
            <th class="py-3 px-3.5">Metode</th>
            <th class="py-3 px-3.5 text-right">Nominal</th>
            <th v-if="allowActions" class="py-3 px-3.5 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr
            v-for="tx in transactions"
            :key="tx.id"
            @click="openDetail(tx)"
            class="hover:bg-slate-50 transition group cursor-pointer"
          >
            <!-- Tanggal -->
            <td class="py-3.5 px-3.5 whitespace-nowrap font-sans text-slate-600">
              {{ tx.formatted_date || tx.transaction_date }}
            </td>

            <!-- Kategori -->
            <td class="py-3.5 px-3.5 whitespace-nowrap">
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200/80 text-slate-800 font-medium text-[11px] truncate max-w-[140px]">
                {{ tx.category?.name || (tx.obligation ? 'Kewajiban' : 'Umum') }}
              </span>
            </td>

            <!-- Keterangan & Override Badge -->
            <td class="py-3.5 px-3.5">
              <div class="flex items-center space-x-2">
                <span class="font-medium text-slate-900 truncate max-w-xs sm:max-w-md">
                  {{ tx.description }}
                </span>
                <span
                  v-if="tx.is_growth_overridden"
                  class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                  title="Pengeluaran ini melewati peringatan target kekayaan"
                >
                  Override Target
                </span>
                <span
                  v-if="tx.obligation"
                  class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                >
                  Kewajiban
                </span>
              </div>
            </td>

            <!-- Metode -->
            <td class="py-3.5 px-3.5 whitespace-nowrap text-slate-500 font-sans text-[11px]">
              {{ tx.payment_method || 'Bank' }}
            </td>

            <!-- Nominal -->
            <td class="py-3.5 px-3.5 whitespace-nowrap text-right font-sans font-extrabold text-sm">
              <span :class="tx.type === 'income' ? 'text-emerald-600' : 'text-rose-600'">
                {{ tx.type === 'income' ? '+' : '-' }}{{ formatRupiah(tx.amount) }}
              </span>
            </td>

            <!-- Actions -->
            <td v-if="allowActions" class="py-3.5 px-3.5 whitespace-nowrap text-right">
              <button
                type="button"
                @click.stop="deleteTx(tx)"
                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition opacity-0 group-hover:opacity-100 cursor-pointer"
                title="Hapus Transaksi"
              >
                <Trash2 :size="14" />
              </button>
            </td>
          </tr>

          <tr v-if="transactions.length === 0">
            <td :colspan="allowActions ? 6 : 5" class="text-center py-10 text-slate-400">
              Tidak ada data transaksi yang sesuai filter.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls (if paginated) -->
    <div v-if="pagination && pagination.links?.length > 3" class="mt-4 sm:mt-5 pt-3 sm:pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2">
      <div class="text-[11px] sm:text-xs text-slate-500 text-center sm:text-left">
        Menampilkan <strong class="text-slate-900">{{ pagination.from || 0 }}</strong> - <strong class="text-slate-900">{{ pagination.to || 0 }}</strong> dari <strong class="text-slate-900">{{ pagination.total }}</strong> data
      </div>

      <div class="flex items-center space-x-1 flex-wrap justify-center">
        <template v-for="(link, idx) in pagination.links" :key="idx">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            :class="[
              'btn-human btn-human-sm font-sans',
              link.active
                ? 'btn-human-primary font-bold'
                : 'btn-human-secondary font-normal'
            ]"
          />
          <span
            v-else
            v-html="link.label"
            class="px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg text-xs font-sans text-slate-400 opacity-40 inline-flex items-center justify-center"
          />
        </template>
      </div>
    </div>

    <!-- 3. Transaction Detail Bottom Sheet (Progressive Disclosure, Thumb-Zone Friendly) -->
    <div
      v-if="selectedTx"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
    >
      <!-- Backdrop with subtle blur -->
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="closeDetail"
      ></div>

      <!-- Bottom Sheet Card on Mobile, Centered Modal on Desktop -->
      <div
        class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 border border-slate-100 transform transition-all max-h-[90vh] flex flex-col z-10 animate-in fade-in slide-in-from-bottom-4 duration-200"
      >
        <!-- Handle bar on mobile -->
        <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 shrink-0"></div>

        <!-- Header: Category Banner (Clean, without frame/icon) -->
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <span
              class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mb-1 border"
              :class="selectedTx.type === 'income' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
            >
              {{ selectedTx.type === 'income' ? 'Pemasukan (+)' : 'Pengeluaran (-)' }}
            </span>
            <div class="text-base sm:text-lg font-bold text-slate-900 truncate">
              {{ selectedTx.category?.name || (selectedTx.obligation ? 'Kewajiban' : 'Umum') }}
            </div>
          </div>

          <!-- Close Button -->
          <button
            type="button"
            @click="closeDetail"
            class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-500 hover:text-slate-800 flex items-center justify-center transition shrink-0 cursor-pointer"
            title="Tutup"
          >
            <X :size="16" />
          </button>
        </div>

        <!-- Big Nominal Display in Poppins -->
        <div class="mt-4 p-4 rounded-2xl bg-slate-50/90 border border-slate-200/70 text-center">
          <div class="text-xs text-slate-500 font-medium">Nominal Transaksi</div>
          <div
            class="text-2xl sm:text-3xl font-extrabold font-sans tracking-tight mt-1"
            :class="selectedTx.type === 'income' ? 'text-emerald-600' : 'text-rose-600'"
          >
            {{ selectedTx.type === 'income' ? '+' : '-' }}{{ formatRupiah(selectedTx.amount) }}
          </div>
          <div class="text-xs font-semibold text-slate-700 mt-2 break-words">
            {{ selectedTx.description }}
          </div>
        </div>

        <!-- Metadata Details List -->
        <div class="mt-4 space-y-2.5 text-xs">
          <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
            <span class="text-slate-400">Tanggal Transaksi</span>
            <span class="font-semibold text-slate-800 font-sans">
              {{ selectedTx.formatted_date || selectedTx.transaction_date }}
            </span>
          </div>

          <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
            <span class="text-slate-400">Metode Pembayaran</span>
            <span class="font-semibold text-slate-800 font-sans">
              {{ selectedTx.payment_method || 'Lainnya' }}
            </span>
          </div>

          <div v-if="selectedTx.obligation" class="p-2.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-800 text-[11px] leading-relaxed">
            <strong>Kewajiban Bulanan:</strong> Transaksi ini dialokasikan untuk pemenuhan "{{ selectedTx.obligation.name }}".
          </div>

          <div v-if="selectedTx.is_growth_overridden" class="p-2.5 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-[11px] leading-relaxed">
            <strong>Peringatan Target Kekayaan:</strong> Pengeluaran ini telah melewati proteksi target pertumbuhan kekayaan bulanan (Override).
          </div>
        </div>

        <!-- Thumb-Zone Bottom CTA Actions (Sepertiga Bawah) -->
        <div class="mt-6 pt-2 space-y-2 shrink-0">
          <button
            v-if="allowActions"
            type="button"
            @click="confirmDelete(selectedTx)"
            class="w-full min-h-[44px] py-3 rounded-xl bg-rose-50 hover:bg-rose-100 active:scale-[0.98] border border-rose-200 text-rose-700 text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer"
          >
            <Trash2 :size="15" />
            <span>Hapus Transaksi Ini</span>
          </button>

          <button
            type="button"
            @click="closeDetail"
            class="w-full min-h-[44px] py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-[0.98] text-slate-700 text-xs font-bold transition flex items-center justify-center cursor-pointer"
          >
            Tutup
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Trash2, X, ReceiptText } from 'lucide-vue-next';
import { formatRupiah } from '@/Utils/formatters';

const props = defineProps({
  transactions: {
    type: Array,
    default: () => []
  },
  pagination: {
    type: Object,
    default: null
  },
  allowActions: {
    type: Boolean,
    default: false
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  title: {
    type: String,
    default: 'Buku Besar Transaksi'
  },
  subtitle: {
    type: String,
    default: 'Histori pemasukan, pengeluaran harian, dan kewajiban'
  }
});

defineEmits(['quick-income', 'quick-expense']);

const selectedTx = ref(null);

function openDetail(tx) {
  selectedTx.value = tx;
}

function closeDetail() {
  selectedTx.value = null;
}

function confirmDelete(tx) {
  if (!tx) return;
  if (confirm(`Apakah Anda yakin ingin menghapus transaksi "${tx.description}" (${formatRupiah(tx.amount)})?`)) {
    const id = tx.id;
    selectedTx.value = null;
    router.delete(route('transactions.destroy', id), {
      preserveScroll: true
    });
  }
}

function deleteTx(tx) {
  confirmDelete(tx);
}

// Group transactions by date for a humanized temporal flow
const groupedTransactions = computed(() => {
  if (!props.transactions || props.transactions.length === 0) return [];

  const groups = [];
  const map = new Map();

  for (const tx of props.transactions) {
    const rawDate = tx.transaction_date || tx.formatted_date || 'Lainnya';
    if (!map.has(rawDate)) {
      const groupObj = {
        dateKey: rawDate,
        dateLabel: formatHumanDateLabel(rawDate, tx.formatted_date),
        items: []
      };
      map.set(rawDate, groupObj);
      groups.push(groupObj);
    }
    map.get(rawDate).items.push(tx);
  }

  return groups;
});

function formatHumanDateLabel(rawDate, fallback) {
  if (!rawDate) return fallback || 'Transaksi';
  
  const parts = typeof rawDate === 'string' ? rawDate.split('-') : [];
  if (parts.length === 3) {
    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const day = parseInt(parts[2], 10);
    const dateObj = new Date(year, month, day);

    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);

    if (dateObj.getTime() === today.getTime()) {
      return 'Hari Ini';
    }
    if (dateObj.getTime() === yesterday.getTime()) {
      return 'Kemarin';
    }

    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${day} ${monthNames[month]} ${year}`;
  }

  return fallback || rawDate;
}
</script>
