<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6 overflow-hidden">
    <!-- Backdrop -->
    <div
      class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
      @click="close"
    ></div>

    <!-- Modal Box / Mobile Bottom Sheet -->
    <div
      class="relative w-full sm:max-w-md bg-white rounded-t-[28px] sm:rounded-3xl shadow-2xl border border-slate-100 z-10 animate-in slide-in-from-bottom sm:zoom-in-95 duration-200 flex flex-col max-h-[92vh] sm:max-h-[88vh] overflow-hidden"
    >
      <!-- Mobile Pull Notch -->
      <div class="pt-3 pb-1 flex justify-center sm:hidden shrink-0">
        <div class="w-10 h-1.5 bg-slate-200 rounded-full"></div>
      </div>

      <!-- Header -->
      <div class="px-4 sm:px-6 pt-2 pb-3.5 sm:pt-4 border-b border-slate-100 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-200/70 text-emerald-600 flex items-center justify-center shrink-0 shadow-xs">
            <User :size="20" stroke-width="2.2" />
          </div>
          <div class="min-w-0">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-tight truncate">Edit Profil & Finansial</h3>
            <p class="text-xs text-slate-500 mt-0.5 leading-snug">Ubah profil & preferensi finansial</p>
          </div>
        </div>
        <button
          type="button"
          @click="close"
          class="w-10 h-10 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 active:scale-95 transition flex items-center justify-center cursor-pointer shrink-0 ml-2"
          aria-label="Tutup"
        >
          <X :size="19" stroke-width="2.2" />
        </button>
      </div>

      <!-- Form Wrapper -->
      <form @submit.prevent="submitForm" class="flex flex-col flex-1 min-h-0 overflow-hidden">
        <!-- Scrollable Content Body -->
        <div class="flex-1 overflow-y-auto overscroll-contain px-4 sm:px-6 pt-4 pb-6 space-y-5">
          <!-- Profile Photo Tactile Card -->
          <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 transition hover:border-slate-300">
            <div class="flex items-center gap-3.5 sm:gap-4">
              <!-- Avatar Preview with Direct Click Trigger -->
              <div class="relative shrink-0">
                <div
                  @click="$refs.avatarInput?.click()"
                  class="relative group cursor-pointer active:scale-95 transition"
                  title="Ketuk untuk memilih foto baru"
                >
                  <img
                    v-if="avatarPreview || user?.avatar_url"
                    :src="avatarPreview || user?.avatar_url"
                    alt="Foto Profil"
                    class="w-15 h-15 sm:w-18 sm:h-18 rounded-full object-cover ring-2 ring-emerald-500/80 shadow-md transition group-hover:ring-emerald-600"
                  />
                  <div
                    v-else
                    class="w-15 h-15 sm:w-18 sm:h-18 rounded-full bg-gradient-to-tr from-emerald-100 via-teal-100 to-emerald-200 text-emerald-800 font-extrabold text-xl flex items-center justify-center ring-2 ring-emerald-400/60 shadow-xs"
                  >
                    {{ userInitials }}
                  </div>

                  <!-- Camera overlay badge -->
                  <div class="absolute -bottom-0.5 -right-0.5 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center ring-2 ring-white shadow-xs group-hover:bg-emerald-700 transition">
                    <Camera :size="12" stroke-width="2.3" />
                  </div>
                </div>
              </div>

              <!-- Upload Info & Action Buttons -->
              <div class="min-w-0 flex-1">
                <div class="text-sm font-bold text-slate-800">Foto Profil</div>
                <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">JPG, PNG, atau WebP (maks. 5MB)</p>

                <div class="flex items-center gap-2 mt-2.5">
                  <input
                    ref="avatarInput"
                    id="avatar-upload"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleAvatarFile"
                  />
                  <button
                    type="button"
                    @click="$refs.avatarInput?.click()"
                    class="btn-human btn-human-primary btn-human-sm !min-h-[34px] !py-1.5 !px-3 text-xs font-semibold whitespace-nowrap"
                  >
                    <span>Pilih Foto</span>
                  </button>

                  <button
                    v-if="user?.avatar_url || avatarPreview"
                    type="button"
                    @click="removeAvatar"
                    class="btn-human btn-human-danger btn-human-sm !min-h-[34px] !py-1.5 !px-2.5 sm:!px-3 text-xs font-semibold whitespace-nowrap"
                  >
                    <Trash2 :size="13" class="shrink-0" />
                    <span>Hapus</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Section: Identitas Personal -->
          <div class="space-y-2.5">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
              <span>Identitas Personal</span>
            </div>

            <div>
              <label for="profile-name" class="block text-xs font-medium text-slate-700 mb-1.5">
                Nama Lengkap
              </label>
              <input
                id="profile-name"
                v-model="form.name"
                type="text"
                placeholder="Masukkan nama lengkap"
                class="w-full bg-slate-50/70 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/15 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 transition outline-none font-medium"
                required
              />
            </div>
          </div>

          <!-- Section: Preferensi Finansial -->
          <div class="space-y-3 pt-1">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
              <span>Preferensi Finansial</span>
            </div>

            <!-- Mata Uang Dompet -->
            <div>
              <label for="profile-currency" class="block text-xs font-medium text-slate-700 mb-1.5">
                Mata Uang Dompet
              </label>
              <div class="relative">
                <select
                  id="profile-currency"
                  v-model="form.currency"
                  class="w-full appearance-none bg-slate-50/70 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/15 rounded-xl px-3.5 py-2.5 pr-10 text-sm text-slate-900 transition outline-none font-medium cursor-pointer"
                >
                  <option value="IDR">IDR (Rupiah Indonesia - Rp)</option>
                  <option value="USD">USD (US Dollar - $)</option>
                  <option value="SGD">SGD (Singapore Dollar - S$)</option>
                  <option value="MYR">MYR (Malaysian Ringgit - RM)</option>
                </select>
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                  <ChevronDown :size="16" />
                </div>
              </div>
            </div>

            <!-- Hari Gajian -->
            <div>
              <label for="profile-start-day" class="block text-xs font-medium text-slate-700 mb-1">
                Hari Gajian
              </label>
              <div class="flex items-center gap-3">
                <div class="relative shrink-0 w-20 sm:w-24">
                  <input
                    id="profile-start-day"
                    v-model.number="form.monthly_start_day"
                    type="number"
                    min="1"
                    max="28"
                    class="w-full bg-slate-50/70 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/15 rounded-xl px-3 py-2.5 text-sm text-slate-900 text-center font-extrabold transition outline-none"
                    required
                  />
                </div>
                <p class="text-xs text-slate-500 leading-snug">
                  Tanggal <span class="font-semibold text-slate-700">1 – 28</span> setiap bulan
                </p>
              </div>
            </div>

            <!-- Total Tabungan & Kas Awal -->
            <div>
              <label for="profile-net-worth" class="block text-xs font-medium text-slate-700 mb-1">
                Total Tabungan & Kas Awal
              </label>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-extrabold">
                  Rp
                </span>
                <input
                  id="profile-net-worth"
                  v-model="formattedInitialNetWorth"
                  type="text"
                  inputmode="numeric"
                  placeholder="0"
                  class="w-full bg-slate-50/70 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/15 rounded-xl pl-11 pr-3.5 py-2.5 text-sm font-extrabold text-slate-900 transition outline-none"
                  @input="onNetWorthInput"
                  required
                />
              </div>
              <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                Saldo awal sebelum pencatatan bulan ini dimulai.
              </p>
            </div>
          </div>
        </div>

        <!-- Sticky Bottom CTA Footer (Thumb-Zone Ergonomics) -->
        <div class="p-4 sm:p-5 border-t border-slate-100 bg-white/95 backdrop-blur-xs sticky bottom-0 inset-x-0 shrink-0 z-10">
          <button
            type="submit"
            :disabled="form.processing"
            class="btn-human btn-human-primary btn-human-lg w-full min-h-[46px] sm:min-h-[48px] text-sm sm:text-base font-bold shadow-md active:scale-[0.98] transition cursor-pointer"
          >
            <span v-if="form.processing">Menyimpan...</span>
            <span v-else>Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { User, X, Camera, Trash2, ChevronDown } from 'lucide-vue-next';
import { formatThousands, parseThousands } from '@/Utils/formatters';

const props = defineProps({
  isOpen: Boolean,
  user: Object,
});

const emit = defineEmits(['close']);

const avatarInput = ref(null);
const avatarPreview = ref(null);
const formattedInitialNetWorth = ref('');

const form = useForm({
  name: props.user?.name || '',
  currency: props.user?.currency || 'IDR',
  monthly_start_day: props.user?.monthly_start_day || 1,
  initial_net_worth: props.user?.initial_net_worth || 0,
  avatar: null,
  remove_avatar: false,
});

watch(() => props.user, (u) => {
  if (u) {
    form.name = u.name || '';
    form.currency = u.currency || 'IDR';
    form.monthly_start_day = u.monthly_start_day || 1;
    form.initial_net_worth = u.initial_net_worth || 0;
    formattedInitialNetWorth.value = formatThousands(u.initial_net_worth || 0);
    avatarPreview.value = null;
    form.avatar = null;
    form.remove_avatar = false;
  }
}, { immediate: true });

function onNetWorthInput(e) {
  const val = parseThousands(e.target.value);
  form.initial_net_worth = val;
  formattedInitialNetWorth.value = formatThousands(val);
}

const userInitials = computed(() => {
  const name = form.name || props.user?.name || 'User';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

function handleAvatarFile(event) {
  const file = event.target.files?.[0];
  if (file) {
    form.avatar = file;
    form.remove_avatar = false;
    avatarPreview.value = URL.createObjectURL(file);
  }
}

function removeAvatar() {
  form.avatar = null;
  form.remove_avatar = true;
  avatarPreview.value = null;
}

function submitForm() {
  form.post(route('profile.update'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      emit('close');
    }
  });
}

function close() {
  emit('close');
}
</script>
