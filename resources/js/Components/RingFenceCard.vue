<template>
  <div class="bg-white rounded-2xl sm:rounded-3xl p-3.5 xs:p-4 sm:p-5 relative overflow-hidden transition shadow-xs border border-slate-200/90 hover:shadow-sm">
    
    <!-- Header: Simple, Clean & Focused -->
    <div class="flex items-center justify-between gap-2">
      <span class="text-xs sm:text-sm font-semibold text-slate-500">
        Kebutuhan Bulanan
      </span>
      <Link
        :href="route('obligations.index')"
        class="text-xs font-semibold text-slate-600 hover:text-slate-900 py-1 px-2.5 rounded-lg hover:bg-slate-100 transition-colors shrink-0"
      >
        Kelola
      </Link>
    </div>

    <!-- Hero Nominal -->
    <div class="mt-1 flex items-baseline flex-wrap gap-x-1.5">
      <h2 class="text-2xl xs:text-3xl font-extrabold font-sans text-slate-900 tracking-tight whitespace-nowrap">
        {{ formatRupiah(obligations.total_amount) }}
      </h2>
      <span class="text-xs font-medium text-slate-400 shrink-0">/ bulan</span>
    </div>

    <!-- Reassuring Progress & Emotional Peace-of-Mind Summary -->
    <div class="mt-3.5 p-3 xs:p-3.5 rounded-xl sm:rounded-2xl bg-slate-50/90 border border-slate-200/80 space-y-2">
      <!-- Two stats badges -->
      <div class="flex items-center justify-between text-[11px] sm:text-xs gap-1.5">
        <div class="flex items-center space-x-1.5 min-w-0">
          <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
          <span class="text-slate-600 truncate text-[10px] xs:text-[11px] sm:text-xs">
            Dibayar: <strong class="text-emerald-700 font-sans font-extrabold">{{ formatRupiah(obligations.paid_amount) }}</strong>
          </span>
        </div>
        <div class="flex items-center space-x-1.5 min-w-0">
          <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
          <span class="text-slate-600 truncate text-[10px] xs:text-[11px] sm:text-xs">
            Sisa: <strong class="text-amber-700 font-sans font-extrabold">{{ formatRupiah(obligations.reserved_amount) }}</strong>
          </span>
        </div>
      </div>

      <!-- Segmented Bar (Tactile, Soft Transitions) -->
      <div class="w-full h-2 sm:h-2.5 bg-slate-200/90 rounded-full overflow-hidden flex p-0.5 gap-0.5">
        <div
          class="h-full bg-emerald-500 rounded-full transition-all duration-500 ease-out"
          :style="{ width: `${paidPercent}%` }"
        ></div>
        <div
          class="h-full bg-amber-500 rounded-full transition-all duration-500 ease-out"
          :style="{ width: `${reservedPercent}%` }"
        ></div>
      </div>
      
      <!-- Progress Meta & Empathetic Feedback -->
      <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-slate-500 font-sans">
        <span class="font-medium text-slate-600">{{ obligations.paid_count }} dari {{ obligations.count }} lunas</span>
        <span class="font-bold" :class="obligations.paid_count === obligations.count && obligations.count > 0 ? 'text-emerald-700' : 'text-slate-700'">
          {{ paidPercent }}% Terbayar
        </span>
      </div>

      <!-- Reassurance Microcopy -->
      <div v-if="obligations.count > 0" class="pt-1.5 border-t border-slate-200/60 flex items-center space-x-1.5 text-[10px] leading-tight">
        <template v-if="obligations.paid_count === obligations.count">
          <CheckCircle2 :size="12" class="text-emerald-600 shrink-0" />
          <span class="text-emerald-700 font-medium">Semua kebutuhan pokok bulan ini aman terlunasi.</span>
        </template>
        <template v-else>
          <ShieldCheck :size="12" class="text-amber-600 shrink-0" />
          <span class="text-slate-500">Sisa dana terisolasi di rekening, aman dari jatah jajan harian.</span>
        </template>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="!obligations.checklist || obligations.checklist.length === 0"
      class="mt-4 p-4 rounded-xl sm:rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center space-y-2"
    >
      <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-700 mx-auto flex items-center justify-center">
        <Lock :size="18" />
      </div>
      <p class="text-xs text-slate-600 font-medium">Belum ada pos kebutuhan bulanan yang dikunci.</p>
      <Link
        :href="route('obligations.index')"
        class="btn-human btn-human-primary btn-human-sm text-xs font-bold mt-1"
      >
        Tambah Pos Kebutuhan
      </Link>
    </div>

    <!-- Interactive Payment Checklist (NO Scroll Trap, Progressive Disclosure) -->
    <div v-else class="mt-3.5 space-y-2">
      <!-- Obligation Item Card -->
      <div
        v-for="item in visibleChecklist"
        :key="item.id"
        @click="togglePayment(item)"
        role="button"
        :tabindex="0"
        @keydown.enter="togglePayment(item)"
        @keydown.space.prevent="togglePayment(item)"
        class="group p-2.5 xs:p-3 rounded-xl sm:rounded-2xl border transition-all duration-150 cursor-pointer flex items-center justify-between gap-2.5 active:scale-[0.985] min-h-[52px]"
        :class="[
          item.is_paid 
            ? 'bg-emerald-50/30 border-emerald-200/70 hover:bg-emerald-50/50' 
            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/60 shadow-2xs'
        ]"
      >
        <!-- Left: Tactile Checkbox & Structured Content -->
        <div class="flex items-center space-x-2.5 min-w-0 flex-1">
          <!-- Tactile Checkbox Button (Minimum Touch Target Safe) -->
          <div
            class="w-7 h-7 xs:w-8 xs:h-8 rounded-lg xs:rounded-xl border flex items-center justify-center transition-all shrink-0"
            :class="[
              item.is_paid 
                ? 'bg-emerald-500 border-emerald-500 text-white shadow-2xs' 
                : 'border-slate-300 group-hover:border-slate-400 bg-white'
            ]"
          >
            <Check v-if="item.is_paid" :size="14" class="stroke-[3]" />
          </div>

          <!-- Structured Info (Line 1: Full Title, Line 2: Due Date & Category) -->
          <div class="min-w-0 flex-1">
            <!-- Line 1: Dignified Item Name (No Strikethrough) -->
            <div
              class="text-xs xs:text-sm font-semibold truncate"
              :class="item.is_paid ? 'text-slate-700 font-medium' : 'text-slate-900'"
            >
              {{ item.name }}
            </div>

            <!-- Line 2: Date Pill & Category Context -->
            <div class="flex items-center space-x-1.5 text-[10px] text-slate-500 truncate mt-0.5">
              <span class="font-sans px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 border border-slate-200/70 font-medium shrink-0">
                Tgl {{ item.due_day }}
              </span>
              <span class="truncate">{{ item.category_name }}</span>
            </div>
          </div>
        </div>

        <!-- Right: Amount (Extra Bold Poppins) & Status Badge -->
        <div class="text-right shrink-0">
          <div 
            class="text-xs xs:text-sm font-sans font-extrabold whitespace-nowrap"
            :class="item.is_paid ? 'text-emerald-700' : 'text-slate-900'"
          >
            {{ formatRupiah(item.amount) }}
          </div>
          <div class="mt-0.5">
            <span
              v-if="item.is_paid"
              class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80"
            >
              Lunas
            </span>
            <span
              v-else
              class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80"
            >
              Terkunci
            </span>
          </div>
        </div>
      </div>

      <!-- Progressive Disclosure Toggle (Only when checklist > 3) -->
      <button
        v-if="obligations.checklist.length > 3"
        type="button"
        @click="showAll = !showAll"
        class="w-full py-2.5 px-3 rounded-xl bg-slate-50 hover:bg-slate-100/90 border border-slate-200/80 text-slate-700 text-xs font-semibold flex items-center justify-center space-x-1.5 transition-colors active:scale-[0.99] min-h-[44px]"
      >
        <span>{{ showAll ? 'Tampilkan Lebih Sedikit' : `Lihat Semua (${obligations.checklist.length} Kebutuhan)` }}</span>
        <ChevronDown :size="14" class="transition-transform duration-200 text-slate-500" :class="{ 'rotate-180': showAll }" />
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { Lock, Check, ChevronDown, ShieldCheck, CheckCircle2 } from 'lucide-vue-next';
import { formatRupiah } from '@/Utils/formatters';

const props = defineProps({
  obligations: {
    type: Object,
    required: true
  }
});

const showAll = ref(false);
const togglingId = ref(null);

const paidPercent = computed(() => {
  if (!props.obligations?.total_amount || props.obligations.total_amount <= 0) return 0;
  return Math.round((props.obligations.paid_amount / props.obligations.total_amount) * 100);
});

const reservedPercent = computed(() => {
  if (!props.obligations?.total_amount || props.obligations.total_amount <= 0) return 0;
  return Math.round((props.obligations.reserved_amount / props.obligations.total_amount) * 100);
});

// Progressive disclosure: by default show first 3 items, show all when expanded
const visibleChecklist = computed(() => {
  const list = props.obligations?.checklist || [];
  if (showAll.value || list.length <= 3) {
    return list;
  }
  return list.slice(0, 3);
});

function togglePayment(item) {
  if (togglingId.value) return;
  togglingId.value = item.id;
  router.post(route('obligations.toggle', item.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      togglingId.value = null;
    }
  });
}
</script>
