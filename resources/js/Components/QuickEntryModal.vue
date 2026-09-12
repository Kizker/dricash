<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="close"></div>

    <!-- Modal / Bottom Sheet Box (Fluid down to 320px) -->
    <div class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl p-4 min-[360px]:p-5 sm:p-6 shadow-2xl border-t sm:border border-slate-200 z-10 animate-in slide-in-from-bottom sm:zoom-in duration-200 max-h-[92vh] overflow-y-auto overscroll-contain">
      
      <!-- Mobile Pull Drag Indicator -->
      <div class="sm:hidden w-10 h-1.5 bg-slate-200 rounded-full mx-auto mb-2.5"></div>

      <!-- Header with Type Selector Tabs -->
      <div class="flex items-center justify-between pb-3 border-b border-slate-100 gap-2">
        <div class="flex items-center p-1 rounded-2xl bg-slate-100 border border-slate-200/80">
          <button
            type="button"
            @click="setType('expense')"
            class="px-3.5 min-[360px]:px-4 py-1.5 rounded-xl text-xs min-[360px]:text-sm font-bold transition-all cursor-pointer active:scale-95"
            :class="form.type === 'expense' 
              ? 'bg-rose-600 text-white shadow-xs' 
              : 'text-slate-600 hover:text-slate-900'"
          >
            <span>Pengeluaran</span>
          </button>
          <button
            type="button"
            @click="setType('income')"
            class="px-3.5 min-[360px]:px-4 py-1.5 rounded-xl text-xs min-[360px]:text-sm font-bold transition-all cursor-pointer active:scale-95"
            :class="form.type === 'income' 
              ? 'bg-emerald-600 text-white shadow-xs' 
              : 'text-slate-600 hover:text-slate-900'"
          >
            <span>Pemasukan</span>
          </button>
        </div>

        <button 
          type="button"
          @click="close" 
          class="w-9 h-9 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition flex items-center justify-center cursor-pointer shrink-0" 
          aria-label="Tutup"
        >
          <X :size="18" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="mt-3.5 space-y-3.5">
        
        <!-- Nominal Input (Fluid, High Contrast) -->
        <div>
          <label class="block text-[10px] min-[360px]:text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
            Nominal Transaksi
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-sans text-base min-[360px]:text-lg font-bold">
              Rp
            </span>
            <input
              v-model="formattedAmount"
              type="text"
              inputmode="numeric"
              placeholder="0"
              class="w-full bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 rounded-2xl pl-11 pr-4 py-2.5 min-[360px]:py-3 text-xl min-[360px]:text-2xl font-black font-sans text-slate-900 placeholder-slate-300 transition outline-none"
              @input="onAmountInput"
              required
              autofocus
            />
          </div>

          <!-- Quick Amount Chips (Horizontally Scrollable Pill Tray - Zero Jagged Wrapping on 320px) -->
          <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 pt-2 scrollbar-none select-none -mx-1 px-1">
            <button
              v-for="chip in quickChips"
              :key="chip.val"
              type="button"
              @click="addAmount(chip.val)"
              class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 active:scale-95 text-slate-700 text-xs font-sans font-bold shrink-0 border border-slate-200/80 transition cursor-pointer"
            >
              +{{ chip.label }}
            </button>
            <button
              v-if="form.amount > 0"
              type="button"
              @click="clearAmount"
              class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 active:scale-95 text-rose-700 text-xs font-bold shrink-0 border border-rose-200 transition cursor-pointer"
            >
              Reset
            </button>
          </div>
        </div>

        <!-- Live Growth Target Intervention Indicator (Real-time feedback) -->
        <div v-if="form.type === 'expense' && preCheck.checked && form.amount > 0" class="transition-all">
          <div 
            v-if="preCheck.breaches_target"
            class="p-2.5 min-[360px]:p-3 rounded-xl min-[360px]:rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start space-x-2"
          >
            <AlertTriangle :size="16" class="text-amber-600 shrink-0 mt-0.5" />
            <div class="min-w-0">
              <p class="font-bold text-amber-900">Perhatian: Target Pertumbuhan Terancam!</p>
              <p class="mt-0.5 text-amber-700 leading-relaxed text-[11px]">
                Pengeluaran ini memproyeksikan pertumbuhan kekayaan turun ke <span class="font-bold font-sans">{{ preCheck.new_projected_percentage }}%</span> (Target: {{ preCheck.target_percentage }}%).
              </p>
            </div>
          </div>
          <div 
            v-else
            class="p-2.5 min-[360px]:p-3 rounded-xl min-[360px]:rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-center space-x-2"
          >
            <CheckCircle2 :size="15" class="text-emerald-600 shrink-0" />
            <span class="text-[11px]">Target pertumbuhan tetap aman di <strong class="font-sans">{{ preCheck.new_projected_percentage }}%</strong>.</span>
          </div>
        </div>

        <!-- Category Grid (Clean 2-Col Fluid, 'Lain/Lainnya' Always Last, No Text Truncation) -->
        <div>
          <div class="flex justify-between items-center mb-1.5">
            <label class="block text-[10px] min-[360px]:text-[11px] font-bold uppercase tracking-wider text-slate-400">
              Pilih Kategori
            </label>
            <span class="text-[10px] text-slate-400">
              {{ filteredCategories.length }} opsi
            </span>
          </div>
          <div class="grid grid-cols-2 gap-1.5 min-[360px]:gap-2 max-h-44 overflow-y-auto pr-1">
            <button
              v-for="cat in filteredCategories"
              :key="cat.id"
              type="button"
              @click="form.category_id = cat.id"
              class="p-2 min-[360px]:p-2.5 rounded-xl min-[360px]:rounded-2xl flex items-center space-x-2 text-left transition min-h-[46px] cursor-pointer active:scale-95"
              :class="form.category_id === cat.id
                ? (form.type === 'expense' 
                    ? 'bg-rose-50 border-2 border-rose-500 text-rose-950 font-bold shadow-xs' 
                    : 'bg-emerald-50 border-2 border-emerald-500 text-emerald-950 font-bold shadow-xs')
                : 'bg-slate-50/80 hover:bg-slate-100/90 border border-slate-200/80 text-slate-700 font-medium'"
            >
              <span 
                class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
                :style="{ backgroundColor: `${cat.color}20`, color: cat.color }"
              >
                <CategoryIcon :name="cat.icon" :size="13" />
              </span>
              <span class="text-[11px] min-[360px]:text-xs leading-tight line-clamp-2 min-w-0">
                {{ cat.name }}
              </span>
            </button>
          </div>
        </div>

        <!-- Description, Date & Payment Method -->
        <div class="space-y-2.5">
          <div>
            <label class="block text-[10px] min-[360px]:text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
              Keterangan
            </label>
            <input
              v-model="form.description"
              type="text"
              placeholder="Contoh: Makan Siang Nasi Padang, Kopi"
              class="w-full bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-3.5 py-2 text-xs min-[360px]:text-sm text-slate-900 placeholder-slate-400 transition outline-none"
              required
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div>
              <label class="block text-[10px] min-[360px]:text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                Tanggal
              </label>
              <input
                v-model="form.transaction_date"
                type="date"
                class="w-full bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-3 py-2 text-xs font-sans text-slate-900 transition outline-none"
                required
              />
            </div>

            <div>
              <label class="block text-[10px] min-[360px]:text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                Metode Pembayaran
              </label>
              <select
                v-model="form.payment_method"
                class="w-full bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white rounded-xl px-3 py-2 text-xs text-slate-900 transition outline-none"
              >
                <option value="Bank">Bank</option>
                <option value="E-Wallet">E-Wallet</option>
                <option value="Cash / Tunai">Cash / Tunai</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Submit Button (High-Contrast Thumb-Zone Action Button) -->
        <div class="pt-1">
          <button
            type="submit"
            :disabled="form.processing || !form.amount || !form.category_id"
            class="w-full h-12 rounded-2xl font-bold text-sm text-white transition-all flex items-center justify-center cursor-pointer shadow-sm active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed"
            :class="form.type === 'expense' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'"
          >
            <span>{{ form.processing ? 'Menyimpan...' : `Simpan ${form.type === 'expense' ? 'Pengeluaran' : 'Pemasukan'}` }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { X, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';
import CategoryIcon from '@/Components/CategoryIcon.vue';

const props = defineProps({
  isOpen: Boolean,
  initialType: {
    type: String,
    default: 'expense'
  },
  categories: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close']);

const todayStr = new Date().toISOString().split('T')[0];

const form = useForm({
  type: props.initialType,
  amount: '',
  category_id: null,
  transaction_date: todayStr,
  description: '',
  payment_method: 'Bank',
  force_override: false,
});

const formattedAmount = ref('');

const quickChips = [
  { label: '50rb', val: 50000 },
  { label: '100rb', val: 100000 },
  { label: '250rb', val: 250000 },
  { label: '500rb', val: 500000 },
  { label: '1jt', val: 1000000 },
  { label: '2.5jt', val: 2500000 },
  { label: '5jt', val: 5000000 },
];

const preCheck = ref({
  checked: false,
  breaches_target: false,
  new_projected_percentage: 0,
  target_percentage: 0,
});

let debounceTimer = null;

// Filter categories by active type AND guarantee that any category with 'lain' or 'lainnya' is placed at the very end
const filteredCategories = computed(() => {
  let list = [];
  if (form.type === 'income') {
    list = props.categories.filter(c => c.type === 'income');
  } else {
    list = props.categories.filter(c => c.type === 'expense' || c.type === 'obligation');
  }

  // Sort so that any category with 'lain' or 'lainnya' in its name is placed at the very end
  return [...list].sort((a, b) => {
    const isOtherA = /lain/i.test(a.name);
    const isOtherB = /lain/i.test(b.name);
    if (isOtherA && !isOtherB) return 1;
    if (!isOtherA && isOtherB) return -1;
    return 0;
  });
});

// Auto-select first category if available
watch(filteredCategories, (cats) => {
  if (cats.length > 0 && (!form.category_id || !cats.find(c => c.id === form.category_id))) {
    form.category_id = cats[0].id;
  }
}, { immediate: true });

watch(() => props.initialType, (val) => {
  setType(val);
});

function setType(type) {
  form.type = type;
  const cats = filteredCategories.value;
  if (cats.length > 0) {
    form.category_id = cats[0].id;
  }
  checkIntervention();
}

function onAmountInput(e) {
  const raw = e.target.value.replace(/\D/g, '');
  const num = parseInt(raw, 10) || 0;
  form.amount = num;
  formattedAmount.value = num > 0 ? new Intl.NumberFormat('id-ID').format(num) : '';
  checkIntervention();
}

function addAmount(val) {
  const current = Number(form.amount) || 0;
  const updated = current + val;
  form.amount = updated;
  formattedAmount.value = new Intl.NumberFormat('id-ID').format(updated);
  checkIntervention();
}

function clearAmount() {
  form.amount = 0;
  formattedAmount.value = '';
  preCheck.value.checked = false;
}

function checkIntervention() {
  clearTimeout(debounceTimer);
  if (form.type !== 'expense' || !form.amount || form.amount <= 0) {
    preCheck.value.checked = false;
    return;
  }

  debounceTimer = setTimeout(async () => {
    try {
      const res = await axios.post(route('quick-entry.check'), {
        amount: form.amount,
        date: form.transaction_date,
      });
      preCheck.value = {
        checked: true,
        breaches_target: res.data.breaches_target,
        new_projected_percentage: res.data.new_projected_percentage,
        target_percentage: res.data.target_percentage,
      };
    } catch (err) {
      console.error(err);
    }
  }, 350);
}

function submitForm() {
  form.post(route('quick-entry.store'), {
    preserveScroll: true,
    onSuccess: () => {
      close();
      form.reset('amount', 'description');
      formattedAmount.value = '';
      preCheck.value.checked = false;
    }
  });
}

function close() {
  emit('close');
}
</script>
