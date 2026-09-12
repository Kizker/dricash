<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="close"></div>

    <!-- Alert Box -->
    <div class="relative w-full max-w-md bg-white rounded-3xl p-6 shadow-2xl border-2 border-rose-400 z-10 animate-in fade-in zoom-in duration-200">
      
      <!-- Icon & Headline -->
      <div class="flex items-center space-x-3 text-rose-600">
        <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200">
          <AlertOctagon :size="28" class="text-rose-600" />
        </div>
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Peringatan Keras</span>
          <h3 class="text-lg font-extrabold text-slate-900">Target Tabungan Terancam!</h3>
        </div>
      </div>

      <!-- Warning Description -->
      <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
          Pengeluaran sebesar <strong class="text-rose-600 font-sans">{{ formatRupiah(interventionData?.amount) }}</strong> ini akan membuat proyeksi kenaikan kekayaan bulan ini turun ke:
        </p>

        <!-- Stat Gauge Comparison -->
        <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-slate-200 shadow-xs">
          <div class="text-center">
            <span class="text-[10px] text-slate-500 uppercase font-semibold">Target Anda</span>
            <div class="text-lg font-extrabold font-sans text-emerald-600">
              +{{ interventionData?.target_percentage }}%
            </div>
          </div>
          <div class="text-slate-400 font-bold text-lg">➔</div>
          <div class="text-center">
            <span class="text-[10px] text-rose-600 uppercase font-semibold">Proyeksi Baru</span>
            <div class="text-lg font-extrabold font-sans text-rose-600">
              +{{ interventionData?.new_projected_percentage }}%
            </div>
          </div>
        </div>

        <p class="text-[11px] text-slate-500 italic">
          Apakah Anda ingin membatalkan untuk menghemat atau tetap melanjutkan pengeluaran ini?
        </p>
      </div>

      <!-- Actions (Semantic Flat Styling, Clean Text-Only) -->
      <div class="mt-5 space-y-2.5">
        <button
          type="button"
          @click="cancel"
          class="btn-human btn-human-success btn-human-lg w-full text-xs sm:text-sm font-bold"
        >
          <span>Saya Batalkan (Amankan Tabungan)</span>
        </button>

        <button
          type="button"
          @click="proceedOverride"
          class="btn-human btn-human-danger btn-human-md w-full text-xs font-bold"
        >
          <span>Tetap Lanjutkan (Override Target)</span>
        </button>
      </div>
    </div>
  </div>
</template>


<script setup>
import { router } from '@inertiajs/vue3';
import { AlertOctagon, ShieldAlert, AlertTriangle } from 'lucide-vue-next';
import { formatRupiah } from '@/Utils/formatters';

const props = defineProps({
  isOpen: Boolean,
  interventionData: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['close']);

function cancel() {
  emit('close');
}

function close() {
  emit('close');
}

function proceedOverride() {
  if (!props.interventionData?.payload) {
    emit('close');
    return;
  }

  const payload = {
    ...props.interventionData.payload,
    force_override: true,
  };

  router.post(route('quick-entry.store'), payload, {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    }
  });
}
</script>
