<template>
  <AppLayout>
    <InertiaHead title="Kewajiban" />

    <!-- 1. SIMPLIFIED HEADER (Centered & Clean - Sesuai View Mutasi) -->
    <div class="text-center py-2 min-[360px]:py-3 mb-2 sm:mb-4">
      <div class="flex items-center justify-center gap-1.5">
        <h1 class="text-xl min-[360px]:text-2xl font-black text-slate-900 tracking-tight font-sans">
          Kewajiban
        </h1>
        <span class="px-2 py-0.5 rounded-full text-[10px] min-[360px]:text-[11px] font-sans font-bold bg-amber-500/10 text-amber-700 border border-amber-500/20">
          {{ period.month_name }}
        </span>
      </div>
      <p class="text-xs text-slate-400 mt-0.5 font-medium">
        {{ metrics.count }} kewajiban tercatat
      </p>
    </div>

    <!-- 2. METRIC CARDS: Compact & Cohesive on Mobile (< sm), 3-Box on Desktop (>= sm) -->
    <!-- Mobile View (< sm): Unified Single-Card Summary (Only ~120px vs ~280px) -->
    <div class="sm:hidden bg-white rounded-2xl p-4 shadow-xs border border-slate-200/90 mb-4 space-y-3">
      <!-- Hero Amount & Progress Meta -->
      <div class="flex items-baseline justify-between gap-2">
        <div class="min-w-0">
          <span class="text-[10px] min-[360px]:text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">
            Total Kewajiban
          </span>
          <div class="text-xl min-[360px]:text-2xl font-black font-sans text-slate-900 tracking-tight mt-0.5 truncate">
            {{ formatRupiah(metrics.total_amount) }}
          </div>
        </div>
        <div class="text-right shrink-0">
          <span class="text-xs font-bold font-sans" :class="metrics.paid_count === metrics.count && metrics.count > 0 ? 'text-emerald-600' : 'text-slate-700'">
            {{ paidPercent }}% Terbayar
          </span>
          <span class="text-[10px] text-slate-400 block font-medium">
            {{ metrics.paid_count }} dari {{ metrics.count }} lunas
          </span>
        </div>
      </div>

      <!-- Tactile Segmented Progress Bar -->
      <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden flex p-0.5 gap-0.5">
        <div
          class="h-full bg-emerald-500 rounded-full transition-all duration-500 ease-out"
          :style="{ width: `${paidPercent}%` }"
        ></div>
        <div
          class="h-full bg-amber-500 rounded-full transition-all duration-500 ease-out"
          :style="{ width: `${reservedPercent}%` }"
        ></div>
      </div>

      <!-- Two Compact Stat Badges (Side by Side) -->
      <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-100">
        <!-- Sudah Lunas -->
        <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100/80 min-w-0">
          <div class="flex items-center gap-1 text-[10px] font-semibold text-emerald-700">
            <CheckCircle2 :size="11" class="shrink-0" />
            <span class="truncate">Sudah Lunas</span>
          </div>
          <div class="text-xs min-[360px]:text-sm font-black font-sans text-emerald-700 mt-0.5 truncate">
            {{ formatRupiah(metrics.paid_amount) }}
          </div>
        </div>

        <!-- Dana Terkunci -->
        <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-100/80 min-w-0">
          <div class="flex items-center gap-1 text-[10px] font-semibold text-amber-700">
            <Clock :size="11" class="shrink-0 text-amber-600" />
            <span class="truncate">Dana Terkunci</span>
          </div>
          <div class="text-xs min-[360px]:text-sm font-black font-sans text-amber-700 mt-0.5 truncate">
            {{ formatRupiah(metrics.reserved_amount) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop View (>= sm): 3-Column Responsive Grid -->
    <div class="hidden sm:grid sm:grid-cols-3 gap-4 mb-6">
      <!-- Total Obligation -->
      <div class="glass-panel rounded-2xl p-5 min-w-0">
        <div class="flex items-center space-x-1.5 text-slate-500 text-xs font-semibold uppercase tracking-wider truncate">
          <Lock :size="13" class="text-amber-600 shrink-0" />
          <span class="truncate">Total Kewajiban</span>
        </div>
        <div class="text-2xl font-black font-sans text-slate-900 mt-1 truncate">
          {{ formatRupiah(metrics.total_amount) }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5 truncate">
          {{ metrics.count }} item aktif
        </div>
      </div>

      <!-- Paid Obligation -->
      <div class="glass-panel rounded-2xl p-5 min-w-0">
        <div class="flex items-center space-x-1.5 text-slate-500 text-xs font-semibold uppercase tracking-wider truncate">
          <CheckCircle2 :size="13" class="text-emerald-600 shrink-0" />
          <span class="truncate">Sudah Lunas</span>
        </div>
        <div class="text-2xl font-black font-sans text-emerald-600 mt-1 truncate">
          {{ formatRupiah(metrics.paid_amount) }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5 truncate">
          {{ metrics.paid_count }} dari {{ metrics.count }} lunas
        </div>
      </div>

      <!-- Reserved / Remaining -->
      <div class="glass-panel rounded-2xl p-5 min-w-0">
        <div class="flex items-center space-x-1.5 text-slate-500 text-xs font-semibold uppercase tracking-wider truncate">
          <Clock :size="13" class="text-rose-500 shrink-0" />
          <span class="truncate">Dana Terkunci</span>
        </div>
        <div class="text-2xl font-black font-sans text-amber-600 mt-1 truncate">
          {{ formatRupiah(metrics.reserved_amount) }}
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5 truncate">
          Terkunci otomatis
        </div>
      </div>
    </div>

    <!-- 3. Obligations Table & Checklist -->
    <div class="glass-panel rounded-2xl sm:rounded-3xl p-4 sm:p-6">
      <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-200 gap-2">
        <div>
          <h3 class="text-sm sm:text-base font-bold text-slate-900">
            Daftar Kewajiban
          </h3>
          <p class="text-[10px] min-[360px]:text-[11px] text-slate-400">
            Klik centang untuk tandai lunas
          </p>
        </div>
        
        <button
          @click="openAddModal"
          class="flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition shadow-2xs cursor-pointer min-h-[36px] shrink-0"
        >
          <Plus :size="14" />
          <span>Tambah</span>
        </button>
      </div>

      <!-- 1. Mobile Feed View (< sm screens) -->
      <div class="sm:hidden divide-y divide-slate-100 mt-2">
        <div
          v-for="item in obligations"
          :key="item.id"
          class="py-3 flex items-center justify-between gap-2.5"
        >
          <!-- Checkbox & Info -->
          <div class="flex items-center space-x-2.5 min-w-0 flex-1">
            <button
              @click="togglePayment(item)"
              class="w-8 h-8 rounded-xl border flex items-center justify-center transition-all shrink-0 active:scale-90 shadow-2xs cursor-pointer"
              :class="[
                isPaid(item)
                  ? 'bg-emerald-50 border-2 border-emerald-500 text-emerald-700 shadow-2xs'
                  : 'border-slate-300 hover:border-slate-400 bg-white text-transparent'
              ]"
              title="Klik untuk ubah status lunas"
            >
              <Check v-if="isPaid(item)" :size="16" class="stroke-[3]" />
            </button>

            <div class="min-w-0 flex-1">
              <div class="font-bold text-xs text-slate-900 truncate" :class="{ 'line-through text-slate-400': isPaid(item) }">
                {{ item.name }}
              </div>
              <div class="flex items-center space-x-1.5 mt-0.5 text-[10px] text-slate-500 flex-wrap">
                <span class="font-sans">Tgl {{ item.due_day }}</span>
                <span>•</span>
                <span class="truncate">{{ item.category?.name || 'Kewajiban' }}</span>
              </div>
            </div>
          </div>

          <!-- Nominal & Actions (2-Line Balanced Layout for Ultra-Narrow Screens) -->
          <div class="text-right shrink-0">
            <div class="font-sans font-black text-xs min-[360px]:text-sm text-slate-900 whitespace-nowrap">
              {{ formatRupiah(item.amount) }}
            </div>
            <div class="flex items-center justify-end gap-1 mt-0.5">
              <span class="text-[9px] min-[360px]:text-[10px] font-semibold" :class="isPaid(item) ? 'text-emerald-600' : 'text-amber-600'">
                {{ isPaid(item) ? 'Lunas' : 'Belum Bayar' }}
              </span>
              <button
                @click="editObligation(item)"
                class="p-1 rounded text-slate-400 hover:text-slate-700 active:scale-90 transition cursor-pointer"
                title="Edit"
              >
                <Edit2 :size="12" />
              </button>
              <button
                @click="deleteObligation(item)"
                class="p-1 rounded text-slate-400 hover:text-rose-600 active:scale-90 transition cursor-pointer"
                title="Hapus"
              >
                <Trash2 :size="12" />
              </button>
            </div>
          </div>
        </div>

        <div v-if="obligations.length === 0" class="text-center py-8 text-slate-400 text-xs">
          Belum ada kewajiban bulanan.
        </div>
      </div>

      <!-- 2. Desktop Table View (>= sm screens) -->
      <div class="hidden sm:block overflow-x-auto mt-4">
        <table class="w-full text-left text-xs">
          <thead class="text-[10px] text-slate-500 uppercase tracking-wider bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="py-3 px-3.5 rounded-l-xl text-center">Status</th>
              <th class="py-3 px-3.5">Nama Kewajiban</th>
              <th class="py-3 px-3.5">Kategori</th>
              <th class="py-3 px-3.5">Jatuh Tempo</th>
              <th class="py-3 px-3.5 text-right">Nominal</th>
              <th class="py-3 px-3.5 text-right rounded-r-xl">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="item in obligations"
              :key="item.id"
              class="hover:bg-slate-50 transition group"
            >
              <!-- Checklist Toggle -->
              <td class="py-3.5 px-3.5 text-center">
                <button
                  @click="togglePayment(item)"
                  class="w-6 h-6 rounded-lg border flex items-center justify-center transition-all mx-auto active:scale-90 shadow-2xs cursor-pointer"
                  :class="[
                    isPaid(item)
                      ? 'bg-emerald-50 border-2 border-emerald-500 text-emerald-700 shadow-2xs'
                      : 'border-slate-300 hover:border-slate-400 bg-white text-transparent'
                  ]"
                  title="Klik untuk ubah status lunas"
                >
                  <Check v-if="isPaid(item)" :size="14" class="stroke-[3]" />
                </button>
              </td>

              <!-- Name & Notes -->
              <td class="py-3.5 px-3.5">
                <div class="font-bold text-slate-900 text-sm" :class="{ 'line-through text-slate-400': isPaid(item) }">
                  {{ item.name }}
                </div>
                <div v-if="item.notes" class="text-[11px] text-slate-500 mt-0.5">
                  {{ item.notes }}
                </div>
              </td>

              <!-- Category -->
              <td class="py-3.5 px-3.5">
                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200">
                  <CategoryIcon :name="item.category?.icon || 'Tag'" :size="12" class="text-slate-600" />
                  <span class="text-slate-700 font-medium text-xs">{{ item.category?.name || 'Kewajiban' }}</span>
                </span>
              </td>

              <!-- Due Day -->
              <td class="py-3.5 px-3.5 font-sans text-slate-600">
                Tgl {{ item.due_day }} setiap bulan
              </td>

              <!-- Amount -->
              <td class="py-3.5 px-3.5 text-right font-sans font-bold text-sm text-slate-900">
                {{ formatRupiah(item.amount) }}
              </td>

              <!-- Actions -->
              <td class="py-3.5 px-3.5 text-right space-x-2">
                <button
                  @click="editObligation(item)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition"
                  title="Edit"
                >
                  <Edit2 :size="14" />
                </button>
                <button
                  @click="deleteObligation(item)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                  title="Hapus"
                >
                  <Trash2 :size="14" />
                </button>
              </td>
            </tr>

            <tr v-if="obligations.length === 0">
              <td colspan="6" class="text-center py-12 text-slate-500">
                Belum ada kewajiban bulanan. Klik tombol "+ Tambah Kewajiban Bulanan" di atas.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (Add / Edit) - Adaptive Bottom Sheet on Mobile -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="isModalOpen = false"></div>

      <div class="relative w-full max-w-md bg-white rounded-t-3xl sm:rounded-2xl p-5 sm:p-6 shadow-2xl border-t sm:border border-slate-200 z-10 animate-in slide-in-from-bottom sm:zoom-in duration-150 max-h-[90vh] overflow-y-auto">
        <!-- Pull drag bar on mobile -->
        <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-3"></div>

        <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-100">
          <h3 class="text-sm sm:text-base font-bold text-slate-900">
            {{ editingItem ? 'Edit Kewajiban Bulanan' : 'Tambah Kewajiban Bulanan Baru' }}
          </h3>
          <button @click="isModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer" aria-label="Tutup">
            <X :size="18" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="mt-4 space-y-3.5">
          <div>
            <label class="block text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
              Nama Kewajiban
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Contoh: Sewa Kos, Internet WiFi, Cicilan"
              class="w-full bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 transition outline-none"
              required
            />
          </div>

          <div>
            <label class="block text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
              Nominal Bulanan
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-sans text-sm font-bold">
                Rp
              </span>
              <input
                v-model="formattedAmount"
                type="text"
                inputmode="numeric"
                placeholder="0"
                class="w-full bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl pl-11 pr-3.5 py-2.5 text-sm font-sans font-bold text-slate-900 transition outline-none"
                @input="onAmountInput"
                required
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <div>
              <label class="block text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
                Kategori
              </label>
              <div class="relative">
                <select
                  v-model="form.category_id"
                  class="w-full bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl px-3 py-2 pr-8 text-xs text-slate-900 transition outline-none appearance-none cursor-pointer"
                >
                  <option :value="null">Pilih Kategori...</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
                <div class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 flex items-center">
                  <ChevronDown :size="14" />
                </div>
              </div>
            </div>

            <div>
              <label class="block text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
                Tgl Jatuh Tempo
              </label>
              <input
                v-model.number="form.due_day"
                type="number"
                min="1"
                max="31"
                placeholder="5"
                class="w-full bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl px-3 py-2 text-xs font-sans text-slate-900 transition outline-none"
                required
              />
            </div>
          </div>

          <div>
            <label class="block text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
              Catatan Tambahan
            </label>
            <textarea
              v-model="form.notes"
              rows="2"
              placeholder="Keterangan nomor rekening, ID pelanggan, dll."
              class="w-full bg-slate-50 border border-slate-200 focus:border-amber-500 focus:bg-white rounded-xl px-3.5 py-2 text-xs text-slate-900 transition outline-none"
            ></textarea>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="btn-human btn-human-primary btn-human-lg w-full font-bold"
            >
              {{ form.processing ? 'Menyimpan...' : (editingItem ? 'Perbarui Kewajiban' : 'Simpan Kewajiban') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import { Lock, CheckCircle2, Clock, Plus, Check, Edit2, Trash2, X, ChevronDown, ShieldCheck } from 'lucide-vue-next';
import { formatRupiah, formatThousands, parseThousands } from '@/Utils/formatters';

const props = defineProps({
  obligations: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  metrics: {
    type: Object,
    required: true
  },
  period: {
    type: Object,
    required: true
  }
});

const paidPercent = computed(() => {
  if (!props.metrics?.total_amount || props.metrics.total_amount <= 0) return 0;
  return Math.min(100, Math.round((props.metrics.paid_amount / props.metrics.total_amount) * 100));
});

const reservedPercent = computed(() => {
  if (!props.metrics?.total_amount || props.metrics.total_amount <= 0) return 0;
  return Math.max(0, 100 - paidPercent.value);
});

const isModalOpen = ref(false);
const editingItem = ref(null);
const formattedAmount = ref('');

const form = useForm({
  name: '',
  amount: '',
  category_id: null,
  due_day: 5,
  notes: '',
});

function isPaid(item) {
  const match = props.metrics.checklist?.find(c => c.id === item.id);
  return match?.is_paid || false;
}

function togglePayment(item) {
  router.post(route('obligations.toggle', item.id), {}, {
    preserveScroll: true
  });
}

function onAmountInput(e) {
  const val = parseThousands(e.target.value);
  form.amount = val;
  formattedAmount.value = formatThousands(val);
}

function openAddModal() {
  editingItem.value = null;
  form.reset();
  formattedAmount.value = '';
  form.due_day = 5;
  if (props.categories.length > 0) {
    form.category_id = props.categories[0].id;
  }
  isModalOpen.value = true;
}

function editObligation(item) {
  editingItem.value = item;
  form.name = item.name;
  form.amount = item.amount;
  formattedAmount.value = formatThousands(item.amount);
  form.category_id = item.category_id;
  form.due_day = item.due_day;
  form.notes = item.notes || '';
  isModalOpen.value = true;
}

function deleteObligation(item) {
  if (confirm(`Apakah Anda yakin ingin menghapus kewajiban "${item.name}"?`)) {
    router.delete(route('obligations.destroy', item.id), {
      preserveScroll: true
    });
  }
}

function submitForm() {
  if (editingItem.value) {
    form.put(route('obligations.update', editingItem.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  } else {
    form.post(route('obligations.store'), {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  }
}
</script>
