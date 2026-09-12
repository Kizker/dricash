<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 flex flex-col justify-center items-center p-3.5 min-[360px]:p-5 sm:p-8 relative overflow-x-hidden selection:bg-emerald-500 selection:text-white">
    <Head title="Daftar Akun" />
    
    <!-- Ambient Subtle Atmosphere (Calm & Human, No Neon Slop) -->
    <div class="absolute w-96 h-96 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none -top-20 -left-20"></div>
    <div class="absolute w-80 h-80 bg-teal-500/5 rounded-full blur-3xl pointer-events-none -bottom-20 -right-20"></div>

    <div class="w-full max-w-sm sm:max-w-md relative z-10 my-auto py-6">
      
      <!-- Brand & Greeting Header (Clean, Modern & Responsive) -->
      <div class="text-center mb-5 sm:mb-6 flex flex-col items-center">
        <ApplicationLogo variant="full" />
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-2.5 sm:mt-3">
          Daftar Akun Baru
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">
          Mulai kelola keuangan Anda bersama Dricash
        </p>
      </div>

      <!-- Main Sanctuary Card Container -->
      <div class="bg-white rounded-[28px] sm:rounded-3xl p-5 min-[360px]:p-6 sm:p-8 shadow-xl shadow-slate-200/50 border border-slate-200/90">
        
        <form @submit.prevent="submitForm" class="space-y-4">
          
          <!-- Name Input -->
          <div>
            <label for="reg-name" class="block text-xs font-bold text-slate-700 mb-1.5">
              Nama Lengkap
            </label>
            <input
              id="reg-name"
              v-model="form.name"
              type="text"
              autocomplete="name"
              placeholder="Contoh: Andricha Deamitra"
              class="w-full h-12 bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 rounded-2xl px-4 text-sm text-slate-900 placeholder-slate-400 transition font-medium outline-none"
              required
              autofocus
            />
            <p v-if="form.errors.name" class="text-rose-600 text-xs mt-1.5 font-medium flex items-center gap-1">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email Input -->
          <div>
            <label for="reg-email" class="block text-xs font-bold text-slate-700 mb-1.5">
              Alamat Email
            </label>
            <input
              id="reg-email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              placeholder="nama@email.com"
              class="w-full h-12 bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 rounded-2xl px-4 text-sm text-slate-900 placeholder-slate-400 transition font-medium outline-none"
              required
            />
            <p v-if="form.errors.email" class="text-rose-600 text-xs mt-1.5 font-medium flex items-center gap-1">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Starting Savings Input (Recorded as Income) -->
          <div>
            <label for="reg-net-worth" class="block text-xs font-bold text-slate-700 mb-1.5">
              Saldo Tabungan Awal (Pemasukan)
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-sans text-sm font-extrabold">
                Rp
              </span>
              <input
                id="reg-net-worth"
                v-model="formattedInitialNetWorth"
                type="text"
                inputmode="numeric"
                placeholder="10.000.000"
                class="w-full h-12 bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 rounded-2xl pl-12 pr-4 text-sm font-sans font-extrabold text-slate-900 placeholder-slate-400 transition outline-none"
                @input="onNetWorthInput"
                required
              />
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Saldo ini otomatis dicatat sebagai pemasukan pertama di akun Anda.</p>
          </div>

          <!-- Password Input with Show/Hide Toggle -->
          <div>
            <label for="reg-password" class="block text-xs font-bold text-slate-700 mb-1.5">
              Kata Sandi
            </label>
            <div class="relative">
              <input
                id="reg-password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
                class="w-full h-12 bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 rounded-2xl pl-4 pr-12 text-sm text-slate-900 placeholder-slate-400 transition font-medium outline-none"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center text-slate-400 hover:text-slate-600 active:scale-95 transition rounded-xl cursor-pointer"
                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
              >
                <EyeOff v-if="showPassword" :size="18" />
                <Eye v-else :size="18" />
              </button>
            </div>
            <p v-if="form.errors.password" class="text-rose-600 text-xs mt-1.5 font-medium flex items-center gap-1">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Confirm Password Input -->
          <div>
            <label for="reg-password-confirm" class="block text-xs font-bold text-slate-700 mb-1.5">
              Konfirmasi Kata Sandi
            </label>
            <div class="relative">
              <input
                id="reg-password-confirm"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                placeholder="Ulangi kata sandi"
                class="w-full h-12 bg-slate-50/80 border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 rounded-2xl pl-4 pr-12 text-sm text-slate-900 placeholder-slate-400 transition font-medium outline-none"
                required
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center text-slate-400 hover:text-slate-600 active:scale-95 transition rounded-xl cursor-pointer"
                :aria-label="showConfirmPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
              >
                <EyeOff v-if="showConfirmPassword" :size="18" />
                <Eye v-else :size="18" />
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full h-12 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] transition-all text-white font-bold text-sm min-[360px]:text-base flex items-center justify-center shadow-md shadow-emerald-600/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span>{{ form.processing ? 'Mendaftarkan Akun...' : 'Buat Akun DriCash' }}</span>
            </button>
          </div>
        </form>

        <!-- Login Link -->
        <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
          Sudah memiliki akun?
          <Link href="/login" class="text-emerald-600 font-bold hover:text-emerald-700 hover:underline ml-1">
            Masuk Sekarang
          </Link>
        </div>

      </div>

      <!-- Subtle Security Assurance Note -->
      <div class="mt-6 flex items-center justify-center space-x-1.5 text-[11px] text-slate-400 font-medium select-none">
        <ShieldCheck :size="14" class="text-emerald-600/70 shrink-0" />
        <span>Data finansial Anda tersimpan privat & aman</span>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import { Eye, EyeOff, ShieldCheck } from 'lucide-vue-next';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { formatThousands, parseThousands } from '@/Utils/formatters';

const form = useForm({
  name: '',
  email: '',
  initial_net_worth: 10000000,
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const formattedInitialNetWorth = ref(formatThousands(form.initial_net_worth));

function onNetWorthInput(e) {
  const val = parseThousands(e.target.value);
  form.initial_net_worth = val;
  formattedInitialNetWorth.value = formatThousands(val);
}

function submitForm() {
  form.post('/register');
}
</script>

