<template>
  <AdminLayout>
    <Head title="Kategori Master" />

    <div class="space-y-6 sm:space-y-8 w-full max-w-[1440px] mx-auto pb-24 sm:pb-8 transition-all duration-300">
      
      <!-- 1. TABS & PRIMARY ACTION -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <!-- Segmented Track (Tab Tipe Pos Finansial) -->
        <div class="bg-stone-200/50 p-1.5 rounded-2xl border border-stone-200/80 inline-flex flex-wrap sm:flex-nowrap gap-1 w-full sm:w-auto">
          <!-- Tab: Pengeluaran Harian -->
          <button
            @click="activeTab = 'expense'"
            :class="[
              'min-h-[44px] sm:min-h-[38px] px-3.5 py-1.5 rounded-xl text-xs transition-all duration-150 flex items-center justify-center space-x-2 flex-1 sm:flex-initial cursor-pointer active:scale-95',
              activeTab === 'expense'
                ? 'bg-white text-stone-900 font-bold shadow-xs border border-stone-200/80'
                : 'text-stone-600 hover:text-stone-900 hover:bg-stone-200/40 font-medium'
            ]"
          >
            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
            <span>Pengeluaran Harian</span>
            <span
              :class="[
                'text-[10px] px-1.5 py-0.5 rounded-md font-bold',
                activeTab === 'expense' ? 'bg-rose-50 text-rose-700' : 'bg-stone-200/80 text-stone-600'
              ]"
            >
              {{ counts.expense }}
            </span>
          </button>

          <!-- Tab: Pemasukan -->
          <button
            @click="activeTab = 'income'"
            :class="[
              'min-h-[44px] sm:min-h-[38px] px-3.5 py-1.5 rounded-xl text-xs transition-all duration-150 flex items-center justify-center space-x-2 flex-1 sm:flex-initial cursor-pointer active:scale-95',
              activeTab === 'income'
                ? 'bg-white text-stone-900 font-bold shadow-xs border border-stone-200/80'
                : 'text-stone-600 hover:text-stone-900 hover:bg-stone-200/40 font-medium'
            ]"
          >
            <span class="w-2 h-2 rounded-full bg-emerald-600 shrink-0"></span>
            <span>Pemasukan</span>
            <span
              :class="[
                'text-[10px] px-1.5 py-0.5 rounded-md font-bold',
                activeTab === 'income' ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-200/80 text-stone-600'
              ]"
            >
              {{ counts.income }}
            </span>
          </button>

          <!-- Tab: Kewajiban Bulanan -->
          <button
            @click="activeTab = 'obligation'"
            :class="[
              'min-h-[44px] sm:min-h-[38px] px-3.5 py-1.5 rounded-xl text-xs transition-all duration-150 flex items-center justify-center space-x-2 flex-1 sm:flex-initial cursor-pointer active:scale-95',
              activeTab === 'obligation'
                ? 'bg-white text-stone-900 font-bold shadow-xs border border-stone-200/80'
                : 'text-stone-600 hover:text-stone-900 hover:bg-stone-200/40 font-medium'
            ]"
          >
            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
            <span>Kewajiban Bulanan</span>
            <span
              :class="[
                'text-[10px] px-1.5 py-0.5 rounded-md font-bold',
                activeTab === 'obligation' ? 'bg-amber-50 text-amber-700' : 'bg-stone-200/80 text-stone-600'
              ]"
            >
              {{ counts.obligation }}
            </span>
          </button>
        </div>

        <!-- Desktop Primary CTA: Tactile Add Category Button -->
        <button
          @click="openCreateModal"
          class="hidden sm:inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-xs hover:shadow-sm active:scale-[0.98] transition-all cursor-pointer shrink-0"
        >
          <Plus :size="16" />
          <span>Tambah Kategori Baru</span>
        </button>
      </div>

      <!-- 3. BALANCED 2-3 COLUMN EDITORIAL CARD GRID (No more cramped 4-column truncation) -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 transition-all duration-300">
        
        <div
          v-for="cat in filteredCategories"
          :key="cat.id"
          class="bg-white p-3.5 sm:p-4.5 rounded-2xl border border-stone-200/80 shadow-[0_2px_8px_-2px_rgba(28,25,23,0.03)] hover:shadow-[0_8px_20px_-4px_rgba(28,25,23,0.06)] hover:border-stone-300 transition-all duration-200 flex items-center justify-between group"
        >
          <!-- Left: Icon & Full Readable Title -->
          <div class="flex items-center space-x-3 sm:space-x-3.5 min-w-0 pr-1.5 flex-1">
            <!-- Tactile Icon Container with Tinted Surface -->
            <div
              class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0 border border-stone-200/60 shadow-2xs transition-transform duration-200 group-hover:scale-105"
              :style="{ backgroundColor: hexToRgba(cat.color, 0.12), color: cat.color }"
            >
              <component :is="resolveIcon(cat.icon)" :size="19" />
            </div>

            <!-- Natural Multi-line Typography (Never Truncates Awkwardly) -->
            <div class="min-w-0 flex-1">
              <h3 class="text-xs sm:text-sm font-bold text-stone-900 leading-snug break-words">
                {{ cat.name }}
              </h3>
              <div class="text-[10px] sm:text-[11px] text-stone-400 mt-0.5 sm:mt-1 flex items-center gap-1.5 font-medium truncate">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full inline-block shrink-0" :style="{ backgroundColor: cat.color }"></span>
                <span class="text-stone-500 truncate">{{ getCategoryTypeDescription(cat.type) }}</span>
              </div>
            </div>
          </div>

          <!-- Right: Tactile Action Buttons (Comfortable Touch Target) -->
          <div class="flex items-center space-x-0.5 sm:space-x-1 shrink-0">
            <button
              @click="openEditModal(cat)"
              class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-stone-400 hover:text-stone-700 hover:bg-stone-100/80 rounded-lg transition active:scale-90 cursor-pointer"
              title="Ubah Kategori"
              aria-label="Ubah Kategori"
            >
              <Pencil :size="14" />
            </button>
            <button
              @click="confirmDelete(cat)"
              class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center text-stone-400 hover:text-rose-600 hover:bg-rose-50/80 rounded-lg transition active:scale-90 cursor-pointer"
              title="Hapus Kategori"
              aria-label="Hapus Kategori"
            >
              <Trash2 :size="14" />
            </button>
          </div>
        </div>

        <!-- Empathetic Empty State -->
        <div
          v-if="filteredCategories.length === 0"
          class="col-span-full bg-white p-10 sm:p-14 rounded-2xl border border-stone-200/80 text-center text-stone-400 shadow-2xs"
        >
          <Tags :size="40" class="mx-auto text-stone-300 mb-3" />
          <h3 class="font-bold text-stone-800 text-sm sm:text-base">Belum Ada Kategori di Kelompok Ini</h3>
          <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">
            Sentuh tombol "Tambah Kategori Baru" untuk menambahkan template pos finansial default bagi pengguna.
          </p>
          <button
            @click="openCreateModal"
            class="mt-4 inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-white text-xs font-semibold transition cursor-pointer"
          >
            <Plus :size="14" />
            <span>Tambah Kategori Sekarang</span>
          </button>
        </div>

      </div>

    </div>

    <!-- 4. MOBILE THUMB-ZONE FLOATING ACTION BUTTON (48x48 Ergonomic Circle) -->
    <div class="sm:hidden fixed bottom-20 right-4 z-30">
      <button
        @click="openCreateModal"
        class="w-12 h-12 rounded-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-lg shadow-emerald-950/25 flex items-center justify-center active:scale-90 transition-all cursor-pointer"
        title="Tambah Kategori Baru"
        aria-label="Tambah Kategori Baru"
      >
        <Plus :size="22" />
      </button>
    </div>

    <!-- 5. HUMAN-CENTRIC MODAL / BOTTOM SHEET (Bottom Sheet on Mobile, Centered on Desktop) -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-stone-900/60 backdrop-blur-xs transition-opacity"
      @click.self="isModalOpen = false"
    >
      <div
        class="bg-white rounded-t-3xl sm:rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-stone-200 max-h-[90vh] overflow-y-auto transform transition-all"
      >
        <!-- Mobile Native Drag Handle Bar -->
        <div class="sm:hidden w-10 h-1.5 bg-stone-300 rounded-full mx-auto mb-4 shrink-0"></div>

        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
          <div>
            <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
              <Tags :size="18" class="text-emerald-600" />
              <span>{{ isEditing ? 'Perbarui Kategori Master' : 'Tambah Kategori Master Baru' }}</span>
            </h3>
            <p class="text-[11px] text-stone-500 mt-0.5">
              Template ini akan tersedia untuk seluruh pengguna baru platform Dricash.
            </p>
          </div>
          <button
            @click="isModalOpen = false"
            class="min-h-[40px] min-w-[40px] flex items-center justify-center text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-xl transition cursor-pointer"
            aria-label="Tutup Dialog"
          >
            <X :size="18" />
          </button>
        </div>

        <form @submit.prevent="submitCategory" class="space-y-5 pt-4">
          
          <!-- Type Toggle (Tactile Segmented Buttons) -->
          <div>
            <label class="block text-xs font-bold text-stone-700 mb-1.5">Tipe Pos Finansial</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                type="button"
                @click="form.type = 'expense'"
                :class="[
                  'min-h-[44px] py-2 px-2 text-center rounded-xl text-xs font-bold border transition-all cursor-pointer active:scale-95',
                  form.type === 'expense'
                    ? 'bg-rose-50 border-rose-300 text-rose-800 shadow-2xs'
                    : 'bg-stone-50 border-stone-200/80 text-stone-600 hover:bg-stone-100'
                ]"
              >
                Pengeluaran
              </button>
              <button
                type="button"
                @click="form.type = 'income'"
                :class="[
                  'min-h-[44px] py-2 px-2 text-center rounded-xl text-xs font-bold border transition-all cursor-pointer active:scale-95',
                  form.type === 'income'
                    ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs'
                    : 'bg-stone-50 border-stone-200/80 text-stone-600 hover:bg-stone-100'
                ]"
              >
                Pemasukan
              </button>
              <button
                type="button"
                @click="form.type = 'obligation'"
                :class="[
                  'min-h-[44px] py-2 px-2 text-center rounded-xl text-xs font-bold border transition-all cursor-pointer active:scale-95',
                  form.type === 'obligation'
                    ? 'bg-amber-50 border-amber-300 text-amber-800 shadow-2xs'
                    : 'bg-stone-50 border-stone-200/80 text-stone-600 hover:bg-stone-100'
                ]"
              >
                Kewajiban
              </button>
            </div>
          </div>

          <!-- Name Input -->
          <div>
            <label class="block text-xs font-bold text-stone-700 mb-1.5">Nama Kategori</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Contoh: Belanja Bulanan, Gaji Pokok"
              class="w-full min-h-[44px] px-3.5 bg-stone-50 border border-stone-200/90 rounded-xl text-xs font-medium focus:outline-none focus:border-emerald-600 focus:bg-white transition"
              required
            />
            <p v-if="form.errors.name" class="text-rose-600 text-[11px] mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- Color Preset Chips (Generous 40x40 Touch Targets) -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-bold text-stone-700">Warna Aksen</label>
              <span class="text-[11px] font-mono text-stone-500">{{ form.color }}</span>
            </div>
            <div class="flex items-center flex-wrap gap-2.5 mb-2">
              <button
                v-for="color in colorPresets"
                :key="color"
                type="button"
                @click="form.color = color"
                class="w-9 h-9 sm:w-8 sm:h-8 rounded-xl transition-transform active:scale-90 flex items-center justify-center border shadow-2xs cursor-pointer"
                :style="{ backgroundColor: color, borderColor: form.color === color ? '#1C1917' : 'transparent' }"
                :aria-label="`Pilih warna ${color}`"
              >
                <Check v-if="form.color === color" :size="15" class="text-white drop-shadow-sm" />
              </button>
            </div>
          </div>

          <!-- Icon Selector Grid -->
          <div>
            <label class="block text-xs font-bold text-stone-700 mb-1.5">Simbol / Ikon</label>
            <div class="grid grid-cols-6 sm:grid-cols-8 gap-2 max-h-36 overflow-y-auto p-2.5 bg-stone-50 rounded-xl border border-stone-200/90">
              <button
                v-for="iconName in availableIcons"
                :key="iconName"
                type="button"
                @click="form.icon = iconName"
                :class="[
                  'min-h-[42px] p-2 rounded-xl flex items-center justify-center transition-all border active:scale-95 cursor-pointer',
                  form.icon === iconName
                    ? 'bg-stone-900 text-white border-stone-900 shadow-xs'
                    : 'bg-white text-stone-600 border-stone-200/80 hover:bg-stone-100'
                ]"
                :title="iconName"
              >
                <component :is="resolveIcon(iconName)" :size="18" />
              </button>
            </div>
          </div>

          <!-- Real-Time Tactile Preview -->
          <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200/80 flex items-center space-x-3.5">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 border border-stone-200/60 shadow-2xs"
              :style="{ backgroundColor: hexToRgba(form.color, 0.15), color: form.color }"
            >
              <component :is="resolveIcon(form.icon)" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
              <div class="text-xs font-bold text-stone-900 truncate">{{ form.name || 'Nama Kategori Pratinjau' }}</div>
              <div class="text-[11px] text-stone-500 capitalize mt-0.5">
                {{ getCategoryTypeDescription(form.type) }} • Ikon: {{ form.icon }}
              </div>
            </div>
          </div>

          <!-- Form Buttons (Min 44x44pt on Mobile) -->
          <div class="pt-3 flex items-center justify-end space-x-2.5 border-t border-stone-100">
            <button
              type="button"
              @click="isModalOpen = false"
              class="min-h-[44px] px-4 py-2 rounded-xl text-xs font-semibold text-stone-600 hover:bg-stone-100 transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="min-h-[44px] px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white transition shadow-xs active:scale-[0.98] disabled:opacity-50 cursor-pointer"
            >
              {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Perbarui Kategori' : 'Simpan Kategori') }}
            </button>
          </div>

        </form>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
  Tags,
  Plus,
  Pencil,
  Trash2,
  Check,
  X,
  Wallet,
  Briefcase,
  TrendingUp,
  Gift,
  Home,
  CreditCard,
  Zap,
  Wifi,
  ShieldCheck,
  Utensils,
  Car,
  ShoppingCart,
  Coffee,
  Film,
  Tv,
  Tag,
  Heart,
  GraduationCap,
  Sparkles,
  Award,
  Smartphone,
  Plane
} from 'lucide-vue-next';

const props = defineProps({
  categories: {
    type: Array,
    required: true,
  },
  counts: {
    type: Object,
    required: true,
  },
});

const activeTab = ref('expense');

const filteredCategories = computed(() => {
  return props.categories.filter(c => c.type === activeTab.value);
});

function getCategoryTypeDescription(type) {
  if (type === 'expense') return 'Pos Pengeluaran Harian';
  if (type === 'income') return 'Pos Arus Masuk';
  if (type === 'obligation') return 'Pos Kewajiban Rutin';
  return 'Pos Finansial';
}

// Icon Mapping
const iconMap = {
  Wallet,
  Briefcase,
  TrendingUp,
  Gift,
  Home,
  CreditCard,
  Zap,
  Wifi,
  ShieldCheck,
  Utensils,
  Car,
  ShoppingCart,
  Coffee,
  Film,
  Tv,
  Tag,
  Heart,
  GraduationCap,
  Sparkles,
  Award,
  Smartphone,
  Plane,
};

const availableIcons = Object.keys(iconMap);

function resolveIcon(name) {
  return iconMap[name] || Tag;
}

const colorPresets = [
  '#059669', // Emerald
  '#0891B2', // Cyan
  '#2563EB', // Blue
  '#7C3AED', // Purple
  '#DB2777', // Pink
  '#D97706', // Amber Ochre
  '#EA580C', // Warm Orange
  '#DC2626', // Terracotta Red
  '#E11D48', // Rose
  '#475569', // Slate
];

function hexToRgba(hex, alpha = 0.15) {
  if (!hex || !hex.startsWith('#')) return 'rgba(5, 150, 105, 0.15)';
  const c = hex.substring(1);
  const r = parseInt(c.substring(0, 2), 16) || 0;
  const g = parseInt(c.substring(2, 4), 16) || 0;
  const b = parseInt(c.substring(4, 6), 16) || 0;
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// Modal Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentCategoryId = ref(null);

const form = useForm({
  name: '',
  type: 'expense',
  icon: 'Tag',
  color: '#059669',
});

function openCreateModal() {
  isEditing.value = false;
  currentCategoryId.value = null;
  form.reset();
  form.type = activeTab.value;
  form.color = activeTab.value === 'expense' ? '#E11D48' : activeTab.value === 'income' ? '#059669' : '#D97706';
  form.icon = 'Tag';
  isModalOpen.value = true;
}

function openEditModal(category) {
  isEditing.value = true;
  currentCategoryId.value = category.id;
  form.name = category.name;
  form.type = category.type;
  form.icon = category.icon;
  form.color = category.color;
  isModalOpen.value = true;
}

function submitCategory() {
  if (isEditing.value) {
    form.put(route('admin.categories.update', currentCategoryId.value), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('admin.categories.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
}

function confirmDelete(category) {
  if (confirm(`Hapus template kategori master '${category.name}'? (Pengguna yang telah menggunakan kategori ini tidak akan kehilangan riwayat transaksi)`)) {
    router.delete(route('admin.categories.destroy', category.id));
  }
}
</script>
