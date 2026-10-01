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

    <!-- 2. PERIOD SELECTOR & EXPENSE LINK TOOLBAR -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 p-2 bg-slate-100/90 rounded-2xl mb-4 border border-slate-200/80">
      <!-- Month & Year Select -->
      <div class="flex items-center gap-1.5 flex-1 min-w-0">
        <div class="relative flex-1 min-w-0">
          <select
            v-model="selectedMonth"
            @change="changePeriod"
            class="w-full appearance-none bg-white border border-slate-200/90 rounded-xl px-2.5 py-1.5 pr-7 text-xs font-bold text-slate-800 focus:border-amber-500 shadow-2xs outline-none cursor-pointer truncate"
          >
            <option v-for="(name, num) in monthNames" :key="num" :value="Number(num)">
              {{ name }}
            </option>
          </select>
          <ChevronDown :size="13" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>

        <div class="relative w-22 shrink-0">
          <select
            v-model="selectedYear"
            @change="changePeriod"
            class="w-full appearance-none bg-white border border-slate-200/90 rounded-xl px-2.5 py-1.5 pr-6 text-xs font-bold text-slate-800 focus:border-amber-500 shadow-2xs outline-none cursor-pointer text-center"
          >
            <option :value="2025">2025</option>
            <option :value="2026">2026</option>
            <option :value="2027">2027</option>
          </select>
          <ChevronDown :size="13" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
        </div>
      </div>

      <!-- Quick Link to Expense Ledger -->
      <Link
        :href="route('transactions.index', { month: period.month, year: period.year, type: 'expense' })"
        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 shadow-2xs transition active:scale-95 shrink-0"
        title="Lihat mutasi pengeluaran bulan ini"
      >
        <ReceiptText :size="13" class="text-amber-600" />
        <span>Buku Pengeluaran</span>
        <ArrowRight :size="11" class="text-slate-400" />
      </Link>
    </div>

    <!-- 3. RINGKASAN KEWAJIBAN & SISA KAS KOTOR (Clean & Minimalist - Anti-Slop) -->
    <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs mb-4 sm:mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Sisa Setelah Seluruh Kewajiban -->
        <div>
          <span class="text-xs text-slate-400 font-medium block">
            Sisa Kas Setelah Kewajiban
          </span>
          <div class="text-2xl sm:text-3xl font-black font-sans tracking-tight mt-0.5" :class="grossRemainingAfterAll >= 0 ? 'text-slate-900' : 'text-rose-600'">
            {{ formatRupiah(grossRemainingAfterAll) }}
          </div>
          <div class="text-xs text-slate-400 font-sans mt-0.5">
            Total kas <span class="text-slate-600 font-medium">{{ formatRupiah(currentMoney) }}</span> dipotong kewajiban <span class="text-slate-600 font-medium">{{ formatRupiah(totalObligations) }}</span>
          </div>
        </div>

        <!-- 3 Metrik Inti (Total, Lunas, Terkunci) dalam Baris Bersih -->
        <div class="grid grid-cols-3 gap-3 sm:gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100 text-left md:text-right shrink-0">
          <div>
            <span class="text-[11px] text-slate-400 block font-medium">Total Kewajiban</span>
            <span class="text-xs sm:text-sm font-bold font-sans text-slate-800 mt-0.5 block">
              {{ formatRupiah(metrics.total_amount) }}
            </span>
            <span class="text-[10px] text-slate-400 font-sans block">
              {{ metrics.count }} item
            </span>
          </div>

          <div>
            <span class="text-[11px] text-slate-400 block font-medium">Sudah Lunas</span>
            <span class="text-xs sm:text-sm font-bold font-sans text-emerald-600 mt-0.5 block">
              {{ formatRupiah(metrics.paid_amount) }}
            </span>
            <span class="text-[10px] text-slate-400 font-sans block">
              {{ metrics.paid_count }} lunas
            </span>
          </div>

          <div>
            <span class="text-[11px] text-slate-400 block font-medium">Dana Terkunci</span>
            <span class="text-xs sm:text-sm font-bold font-sans mt-0.5 block" :class="metrics.reserved_amount > 0 ? 'text-amber-600' : 'text-slate-400'">
              {{ formatRupiah(metrics.reserved_amount) }}
            </span>
            <span class="text-[10px] text-slate-400 font-sans block">
              {{ metrics.reserved_amount > 0 ? 'Belum dibayar' : 'Lunas semua' }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Obligations Table & Checklist -->
    <div class="glass-panel rounded-2xl sm:rounded-3xl p-4 sm:p-6">
      <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-slate-200 gap-2">
        <div>
          <h3 class="text-sm sm:text-base font-bold text-slate-900">
            Daftar Kewajiban
          </h3>
          <p class="text-[10px] min-[360px]:text-[11px] text-slate-400">
            Urutan jatuh tempo terdekat • Item lunas otomatis berpindah ke bawah
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
          v-for="item in sortedObligations"
          :key="item.id"
          class="py-3 px-2 rounded-2xl flex items-center justify-between gap-2.5 transition duration-200"
          :class="isPaid(item) ? 'bg-slate-50/80 border border-slate-200/60 opacity-65' : 'bg-white hover:bg-slate-50/50'"
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
              :title="isPaid(item) ? 'Klik untuk kembalikan ke belum bayar' : 'Klik untuk tandai lunas'"
            >
              <Check v-if="isPaid(item)" :size="16" class="stroke-[3]" />
            </button>

            <div class="min-w-0 flex-1">
              <div
                class="font-bold text-xs truncate"
                :class="isPaid(item) ? 'line-through text-slate-400 font-medium' : 'text-slate-900'"
              >
                {{ item.name }}
              </div>
              <div class="flex items-center space-x-1.5 mt-0.5 text-[10px] text-slate-500 flex-wrap">
                <span class="font-sans font-medium" :class="{ 'line-through text-slate-400': isPaid(item) }">
                  Tgl {{ item.due_day }} {{ getDueMonthName(item) }}
                </span>
                <span
                  v-if="getProximityBadge(item)"
                  class="px-1.5 py-0.2 rounded-full text-[9px] font-bold border"
                  :class="getProximityBadge(item).classes"
                >
                  {{ getProximityBadge(item).text }}
                </span>
                <span>•</span>
                <span class="truncate">{{ item.category?.name || 'Kewajiban' }}</span>
              </div>

              <!-- Cicilan Info (Perhitungan Bulanan & Siklus Tenor) -->
              <div v-if="item.total_installments" class="mt-1 text-[11px] text-slate-500 font-sans flex items-center gap-1.5 flex-wrap">
                <span class="font-medium" :class="isPaid(item) ? 'text-slate-400 line-through' : 'text-slate-700'">
                  Cicilan {{ item.paid_installments }}/{{ item.total_installments }}x
                </span>
                <span>•</span>
                <template v-if="isPaid(item)">
                  <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/80 text-[10px]">
                    Tagihan {{ period.month_name }} Lunas
                  </span>
                  <span v-if="item.remaining_installments > 0" class="text-slate-400 text-[10px]">
                    (Sisa {{ item.remaining_installments }}x lagi)
                  </span>
                  <span v-else class="text-emerald-600 font-bold text-[10px]">
                    🎉 Lunas Total
                  </span>
                </template>
                <template v-else>
                  <span :class="item.remaining_installments === 0 ? 'text-emerald-600 font-bold' : 'text-amber-700 font-medium'">
                    {{ item.remaining_installments === 0 ? 'Lunas Total' : `Sisa ${item.remaining_installments}x lagi lunas` }}
                  </span>
                  <span v-if="item.remaining_amount" class="text-slate-400">
                    (Sisa pokok {{ formatRupiah(item.remaining_amount) }})
                  </span>
                </template>
              </div>

              <!-- Periode Cicilan Range (Bulan & Tahun) -->
              <div v-if="item.period_range" class="mt-0.5 text-[10px] text-slate-500 font-sans flex items-center gap-1 flex-wrap">
                <span class="text-slate-500 flex items-center gap-1">
                  <Calendar :size="10" class="text-slate-400 shrink-0" />
                  <span>{{ item.period_range }}</span>
                </span>
                <span v-if="item.is_before_start" class="px-1.5 py-0.2 rounded text-[8px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                  Belum Mulai
                </span>
                <span v-else-if="item.is_after_end" class="px-1.5 py-0.2 rounded text-[8px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                  Selesai
                </span>
              </div>
            </div>
          </div>

          <!-- Nominal & Actions -->
          <div class="text-right shrink-0">
            <div
              class="font-sans font-black text-xs min-[360px]:text-sm whitespace-nowrap"
              :class="isPaid(item) ? 'line-through text-slate-400' : 'text-slate-900'"
            >
              {{ formatRupiah(item.amount) }}
            </div>
            <div class="flex items-center justify-end gap-1 mt-0.5">
              <span class="text-[9px] min-[360px]:text-[10px] font-semibold" :class="isPaid(item) ? 'text-emerald-600' : 'text-amber-600'">
                {{ isPaid(item) ? (item.paid_at ? `Lunas ${item.paid_at}` : 'Lunas') : 'Belum Bayar' }}
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
            <div v-if="isPaid(item)" class="text-[9px] text-emerald-600 font-sans mt-0.5">
              Tercatat di Pengeluaran ✓
            </div>
          </div>
        </div>

        <div v-if="sortedObligations.length === 0" class="text-center py-8 text-slate-400 text-xs">
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
              v-for="item in sortedObligations"
              :key="item.id"
              class="transition duration-150 group"
              :class="isPaid(item) ? 'bg-slate-50/70 hover:bg-slate-100/70 opacity-70' : 'hover:bg-slate-50'"
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
                  :title="isPaid(item) ? 'Klik untuk kembalikan ke belum bayar' : 'Klik untuk tandai lunas'"
                >
                  <Check v-if="isPaid(item)" :size="14" class="stroke-[3]" />
                </button>
              </td>

              <!-- Name & Notes & Cicilan -->
              <td class="py-3.5 px-3.5">
                <div class="flex items-center gap-2">
                  <span
                    class="font-bold text-sm"
                    :class="isPaid(item) ? 'line-through text-slate-400 font-medium' : 'text-slate-900'"
                  >
                    {{ item.name }}
                  </span>
                  <span
                    v-if="isPaid(item)"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                  >
                    <Check :size="10" class="stroke-[3]" />
                    <span>Lunas {{ item.paid_at ? `(${item.paid_at})` : `(${period.month_name})` }}</span>
                  </span>
                </div>
                <div v-if="item.notes" class="text-[11px] text-slate-400 mt-0.5">
                  {{ item.notes }}
                </div>
                <!-- Cicilan Calculation Info (Clean & Simple) -->
                <div v-if="item.total_installments" class="mt-1 text-xs text-slate-500 font-sans flex items-center gap-1.5 flex-wrap">
                  <span class="font-medium" :class="isPaid(item) ? 'text-slate-400 line-through' : 'text-slate-700'">
                    Cicilan {{ item.paid_installments }}/{{ item.total_installments }}x
                  </span>
                  <span>•</span>
                  <template v-if="isPaid(item)">
                    <span class="text-emerald-700 font-bold">
                      Tagihan {{ period.month_name }} Lunas
                    </span>
                    <span v-if="item.remaining_installments > 0" class="text-slate-400">
                      (Sisa {{ item.remaining_installments }}x lagi lunas)
                    </span>
                    <span v-else class="text-emerald-600 font-bold">
                      🎉 Semua cicilan telah lunas!
                    </span>
                  </template>
                  <template v-else>
                    <span :class="item.remaining_installments === 0 ? 'text-emerald-600 font-bold' : 'text-amber-700 font-medium'">
                      {{ item.remaining_installments === 0 ? 'Lunas Total' : `Sisa ${item.remaining_installments}x lagi lunas` }}
                    </span>
                    <span v-if="item.remaining_amount" class="text-slate-400">
                      (Sisa pokok {{ formatRupiah(item.remaining_amount) }})
                    </span>
                  </template>
                </div>
                <!-- Periode Cicilan Range (Bulan & Tahun) -->
                <div v-if="item.period_range" class="mt-0.5 text-[11px] text-slate-500 font-sans flex items-center gap-1.5 flex-wrap">
                  <span class="text-slate-600 flex items-center gap-1">
                    <Calendar :size="11" class="text-slate-400 shrink-0" />
                    <span>Periode: <strong>{{ item.period_range }}</strong></span>
                  </span>
                  <span v-if="item.is_before_start" class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Belum Mulai
                  </span>
                  <span v-else-if="item.is_after_end" class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    Selesai
                  </span>
                  <span v-else class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Aktif
                  </span>
                </div>
                <div v-if="isPaid(item)" class="text-[10px] text-emerald-600 font-sans mt-0.5 flex items-center gap-1">
                  <span>Tercatat otomatis di Buku Pengeluaran</span>
                </div>
              </td>

              <!-- Category -->
              <td class="py-3.5 px-3.5">
                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200">
                  <CategoryIcon :name="item.category?.icon || 'Tag'" :size="12" class="text-slate-600" />
                  <span class="text-slate-700 font-medium text-xs">{{ item.category?.name || 'Kewajiban' }}</span>
                </span>
              </td>

              <!-- Due Day & Proximity -->
              <td class="py-3.5 px-3.5 font-sans">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span :class="isPaid(item) ? 'line-through text-slate-400' : 'text-slate-800 font-semibold'">
                    Tgl {{ item.due_day }} {{ getDueMonthName(item) }}
                  </span>
                  <!-- Proximity Badge -->
                  <span
                    v-if="getProximityBadge(item)"
                    class="px-2 py-0.5 rounded-full text-[10px] font-sans font-bold inline-flex items-center gap-1 border shadow-2xs"
                    :class="getProximityBadge(item).classes"
                  >
                    <Clock v-if="getProximityBadge(item).showClock" :size="10" />
                    <span>{{ getProximityBadge(item).text }}</span>
                  </span>
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">
                  {{ getDueScheduleSubtitle(item) }}
                </div>
              </td>

              <!-- Amount -->
              <td
                class="py-3.5 px-3.5 text-right font-sans font-bold text-sm"
                :class="isPaid(item) ? 'line-through text-slate-400' : 'text-slate-900'"
              >
                {{ formatRupiah(item.amount) }}
              </td>

              <!-- Actions -->
              <td class="py-3.5 px-3.5 text-right space-x-2">
                <button
                  @click="editObligation(item)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                  title="Edit"
                >
                  <Edit2 :size="14" />
                </button>
                <button
                  @click="deleteObligation(item)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                  title="Hapus"
                >
                  <Trash2 :size="14" />
                </button>
              </td>
            </tr>

            <tr v-if="sortedObligations.length === 0">
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
                  @change="onCategoryChange"
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

          <!-- Skema Cicilan / Tenor Bertahap -->
          <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-3">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input
                type="checkbox"
                v-model="form.has_installments"
                class="rounded border-slate-300 text-amber-600 focus:ring-amber-500 w-4 h-4 cursor-pointer"
              />
              <span class="text-xs font-bold text-slate-700">Skema Cicilan / Tenor Bertahap</span>
            </label>

            <div v-if="form.has_installments" class="space-y-3 pt-1">
              <!-- Baris 1: Total Tenor & Sudah Dibayar -->
              <div class="grid grid-cols-2 gap-2.5">
                <div>
                  <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Total Tenor (Kali)
                  </label>
                  <input
                    v-model.number="form.total_installments"
                    type="number"
                    min="1"
                    max="360"
                    placeholder="Contoh: 12"
                    @input="onTenorOrStartChange"
                    class="w-full bg-white border border-slate-200 focus:border-amber-500 rounded-lg px-3 py-2 text-xs font-sans text-slate-900 transition outline-none"
                    :required="form.has_installments"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 mb-1">
                    Sudah Dibayar (Kali)
                  </label>
                  <input
                    v-model.number="form.paid_installments"
                    type="number"
                    min="0"
                    :max="form.total_installments || 360"
                    placeholder="0"
                    class="w-full bg-white border border-slate-200 focus:border-amber-500 rounded-lg px-3 py-2 text-xs font-sans text-slate-900 transition outline-none"
                  />
                </div>
              </div>

              <!-- Baris 2: Dari Bulan Apa s/d Bulan Apa (Tahun Menyesuaikan) -->
              <div class="p-2.5 bg-white border border-slate-200/90 rounded-xl space-y-2">
                <div class="text-[10px] font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <Calendar :size="12" class="text-amber-600" />
                    <span>Jangka Waktu Cicilan</span>
                  </div>
                  <span class="text-[9px] font-normal text-slate-400">Tahun menyesuaikan</span>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                  <!-- Dari Bulan & Tahun -->
                  <div>
                    <label class="block text-[9px] font-semibold text-slate-500 mb-1">
                      Mulai Dari Bulan & Tahun
                    </label>
                    <div class="grid grid-cols-5 gap-1">
                      <select
                        v-model.number="form.start_month"
                        @change="onTenorOrStartChange"
                        class="col-span-3 bg-slate-50 border border-slate-200 focus:border-amber-500 rounded-lg px-2 py-1.5 text-xs text-slate-800 transition outline-none cursor-pointer"
                      >
                        <option v-for="(name, num) in monthNames" :key="num" :value="Number(num)">
                          {{ name.slice(0, 3) }}
                        </option>
                      </select>
                      <input
                        v-model.number="form.start_year"
                        @input="onTenorOrStartChange"
                        type="number"
                        min="2020"
                        max="2050"
                        class="col-span-2 bg-slate-50 border border-slate-200 focus:border-amber-500 rounded-lg px-1 py-1.5 text-xs font-sans text-slate-800 transition outline-none text-center"
                      />
                    </div>
                  </div>

                  <!-- Hingga Bulan & Tahun (Menyesuaikan Otomatis) -->
                  <div>
                    <div class="flex items-center justify-between mb-1">
                      <label class="block text-[9px] font-semibold text-slate-500">
                        Hingga Bulan & Tahun
                      </label>
                      <span class="text-[8px] font-bold text-emerald-600 bg-emerald-50 px-1 rounded border border-emerald-200/60">Auto</span>
                    </div>
                    <div class="grid grid-cols-5 gap-1">
                      <select
                        v-model.number="form.end_month"
                        @change="onEndPeriodChange"
                        class="col-span-3 bg-slate-50 border border-slate-200 focus:border-amber-500 rounded-lg px-2 py-1.5 text-xs text-slate-800 transition outline-none cursor-pointer"
                      >
                        <option v-for="(name, num) in monthNames" :key="num" :value="Number(num)">
                          {{ name.slice(0, 3) }}
                        </option>
                      </select>
                      <input
                        v-model.number="form.end_year"
                        @input="onEndPeriodChange"
                        type="number"
                        min="2020"
                        max="2050"
                        class="col-span-2 bg-slate-50 border border-slate-200 focus:border-amber-500 rounded-lg px-1 py-1.5 text-xs font-sans text-slate-800 transition outline-none text-center"
                      />
                    </div>
                  </div>
                </div>

                <div v-if="periodSummary" class="text-[10px] text-amber-900 bg-amber-50/80 px-2.5 py-1.5 rounded-lg border border-amber-200/60 flex items-center justify-between">
                  <span>Periode: <strong>{{ periodSummary }}</strong></span>
                  <span class="font-bold text-amber-700">{{ form.total_installments }}x angsuran</span>
                </div>
              </div>

              <!-- Baris 3: Status Sisa & Pokok -->
              <div v-if="form.total_installments" class="text-[10px] text-slate-600 font-sans flex items-center justify-between px-1">
                <span>
                  Sisa: <strong class="text-amber-700">{{ Math.max(0, (form.total_installments || 0) - (form.paid_installments || 0)) }} kali lagi</strong> lunas
                </span>
                <span v-if="form.amount" class="text-slate-400">
                  Est. sisa: {{ formatRupiah(Math.max(0, (form.total_installments || 0) - (form.paid_installments || 0)) * (form.amount || 0)) }}
                </span>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-[11px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
              Catatan Tambahan
            </label>
            <textarea
              v-model="form.notes"
              rows="2"
              placeholder="Keterangan nomor kontrak cicilan, bank, atau ID pelanggan."
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
import { ref, computed, watch } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CategoryIcon from '@/Components/CategoryIcon.vue';
import { Plus, Check, Edit2, Trash2, X, ChevronDown, ReceiptText, ArrowRight, Calendar, Clock } from 'lucide-vue-next';
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
  wealth: {
    type: Object,
    default: () => ({ current_net_worth: 0 })
  },
  period: {
    type: Object,
    required: true
  }
});

const selectedMonth = ref(props.period.month);
const selectedYear = ref(props.period.year);

watch(() => props.period, (newPeriod) => {
  if (newPeriod) {
    selectedMonth.value = newPeriod.month;
    selectedYear.value = newPeriod.year;
  }
}, { deep: true });

const monthNames = {
  1: 'Januari',
  2: 'Februari',
  3: 'Maret',
  4: 'April',
  5: 'Mei',
  6: 'Juni',
  7: 'Juli',
  8: 'Agustus',
  9: 'September',
  10: 'Oktober',
  11: 'November',
  12: 'Desember'
};

function changePeriod() {
  router.get(route('obligations.index'), {
    month: selectedMonth.value,
    year: selectedYear.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
}

const currentMoney = computed(() => {
  return Number(props.wealth?.current_net_worth ?? 0);
});

const totalObligations = computed(() => {
  return Number(props.metrics?.total_amount ?? 0);
});

const grossRemainingAfterAll = computed(() => {
  return currentMoney.value - totalObligations.value;
});

function isPaid(item) {
  if (typeof item.is_paid === 'boolean') {
    return item.is_paid;
  }
  const match = props.metrics.checklist?.find(c => c.id === item.id);
  return Boolean(match?.is_paid);
}

const monthNamesShort = {
  1: 'Jan',
  2: 'Feb',
  3: 'Mar',
  4: 'Apr',
  5: 'Mei',
  6: 'Jun',
  7: 'Jul',
  8: 'Agu',
  9: 'Sep',
  10: 'Okt',
  11: 'Nov',
  12: 'Des'
};

function getDueMonthName(item) {
  if (item.is_before_start && item.start_month) {
    return monthNamesShort[item.start_month] || '';
  }
  return monthNamesShort[props.period.month] || '';
}

function getDueScheduleSubtitle(item) {
  if (isPaid(item)) return 'Lunas untuk periode ini';
  if (item.is_after_end) return 'Masa angsuran telah berakhir';
  if (item.is_before_start) return `Mulai ${monthNames[item.start_month] || ''} ${item.start_year || ''}`;
  if (!item.total_installments && !item.start_month) return 'Rutin setiap bulan';
  return `Tagihan ${monthNames[props.period.month] || ''} ${props.period.year || ''}`;
}

function getProximityBadge(item) {
  if (isPaid(item)) {
    return {
      text: 'Lunas',
      classes: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
      showClock: false
    };
  }

  if (item.is_after_end) {
    return {
      text: 'Selesai',
      classes: 'bg-slate-100 text-slate-500 border-slate-200/80',
      showClock: false
    };
  }

  if (item.is_before_start && item.start_month && item.start_year) {
    const startMName = monthNamesShort[item.start_month] || '';
    return {
      text: `Mulai ${startMName} ${item.start_year}`,
      classes: 'bg-blue-50 text-blue-700 border-blue-200/80',
      showClock: false
    };
  }

  const now = new Date();
  const todayDate = now.getDate();
  const todayMonth = now.getMonth() + 1;
  const todayYear = now.getFullYear();

  const isCurrentPeriodNow = (Number(props.period.month) === todayMonth && Number(props.period.year) === todayYear);

  if (isCurrentPeriodNow) {
    const dueDay = Number(item.due_day ?? 1);
    if (dueDay === todayDate) {
      return {
        text: 'Hari ini',
        classes: 'bg-rose-50 text-rose-700 border-rose-200/90 font-black animate-pulse',
        showClock: true
      };
    }
    if (dueDay === todayDate + 1) {
      return {
        text: 'Besok',
        classes: 'bg-amber-50 text-amber-700 border-amber-300 font-bold',
        showClock: true
      };
    }
    if (dueDay > todayDate) {
      const diff = dueDay - todayDate;
      return {
        text: `${diff} hari lagi`,
        classes: diff <= 3
          ? 'bg-amber-50 text-amber-700 border-amber-200 font-semibold'
          : 'bg-slate-100 text-slate-600 border-slate-200',
        showClock: false
      };
    }
    if (dueDay < todayDate) {
      const diff = todayDate - dueDay;
      return {
        text: `Terlewat ${diff} hr`,
        classes: 'bg-rose-50 text-rose-700 border-rose-300 font-bold',
        showClock: true
      };
    }
  }

  return null;
}

function getObligationSortRank(item) {
  // Tier 1: Belum lunas & aktif periode ini (prioritas pembayaran terdekat)
  if (item.is_within_period && !isPaid(item)) return 1;
  // Tier 2: Belum lunas & belum mulai (mulai bulan mendatang)
  if (item.is_before_start && !isPaid(item)) return 2;
  // Tier 3: Sudah lunas bulan ini (pindah ke bawah & dicoret)
  if (isPaid(item)) return 3;
  // Tier 4: Selesai / telah lewat masa angsuran
  if (item.is_after_end) return 4;
  return 1;
}

function getObligationDueDateKey(item) {
  const currentYear = Number(props.period?.year ?? new Date().getFullYear());
  const currentMonth = Number(props.period?.month ?? (new Date().getMonth() + 1));
  const dueDay = Number(item.due_day ?? 1);

  if (item.is_within_period) {
    return currentYear * 10000 + currentMonth * 100 + dueDay;
  }
  if (item.is_before_start && item.start_year && item.start_month) {
    return Number(item.start_year) * 10000 + Number(item.start_month) * 100 + dueDay;
  }
  if (item.is_after_end && item.end_year && item.end_month) {
    return Number(item.end_year) * 10000 + Number(item.end_month) * 100 + dueDay;
  }
  return currentYear * 10000 + currentMonth * 100 + dueDay;
}

// Urutkan: Jatuh tempo pembayaran cicilan paling dekat di atas, yang sudah lunas berpindah ke bawah
const sortedObligations = computed(() => {
  return [...props.obligations].sort((a, b) => {
    const rankA = getObligationSortRank(a);
    const rankB = getObligationSortRank(b);
    if (rankA !== rankB) {
      return rankA - rankB;
    }
    const dateA = getObligationDueDateKey(a);
    const dateB = getObligationDueDateKey(b);
    if (dateA !== dateB) {
      return dateA - dateB;
    }
    return (a.id ?? 0) - (b.id ?? 0);
  });
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
  has_installments: false,
  total_installments: null,
  paid_installments: 0,
  start_month: props.period.month,
  start_year: props.period.year,
  end_month: null,
  end_year: null,
});

// Hitung bulan & tahun selesai secara otomatis berdasarkan tenor
function computeEndPeriod(startM, startY, tenor) {
  if (!startM || !startY || !tenor || tenor < 1) return null;
  const totalMonths = (startY * 12 + (startM - 1)) + (tenor - 1);
  return {
    month: (totalMonths % 12) + 1,
    year: Math.floor(totalMonths / 12)
  };
}

function onTenorOrStartChange() {
  if (form.start_month && form.start_year && form.total_installments && form.total_installments > 0) {
    const res = computeEndPeriod(form.start_month, form.start_year, form.total_installments);
    if (res) {
      form.end_month = res.month;
      form.end_year = res.year;
    }
  }
}

// Jika user memilih bulan/tahun akhir secara manual, tenor otomatis menyesuaikan
function onEndPeriodChange() {
  if (form.start_month && form.start_year && form.end_month && form.end_year) {
    const startTotal = form.start_year * 12 + (form.start_month - 1);
    const endTotal = form.end_year * 12 + (form.end_month - 1);
    const diff = endTotal - startTotal + 1;
    if (diff > 0) {
      form.total_installments = diff;
    }
  }
}

const periodSummary = computed(() => {
  if (!form.has_installments || !form.start_month || !form.start_year) return null;
  const startName = monthNames[form.start_month] || '';
  if (form.end_month && form.end_year) {
    const endName = monthNames[form.end_month] || '';
    return `${startName} ${form.start_year} s/d ${endName} ${form.end_year}`;
  }
  return `${startName} ${form.start_year}`;
});

function onCategoryChange() {
  if (!form.category_id) return;
  const selected = props.categories.find(c => c.id === form.category_id);
  if (selected && /cicil|pinjam/i.test(selected.name)) {
    form.has_installments = true;
    if (!form.start_month) form.start_month = props.period.month;
    if (!form.start_year) form.start_year = props.period.year;
  }
}

function togglePayment(item) {
  router.post(route('obligations.toggle', item.id), {
    month: props.period.month,
    year: props.period.year,
  }, {
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
  form.has_installments = false;
  form.total_installments = null;
  form.paid_installments = 0;
  form.start_month = props.period.month;
  form.start_year = props.period.year;
  form.end_month = null;
  form.end_year = null;
  formattedAmount.value = '';
  form.due_day = 5;
  if (props.categories.length > 0) {
    form.category_id = props.categories[0].id;
    onCategoryChange();
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
  form.has_installments = Boolean(item.total_installments || item.start_month);
  form.total_installments = item.total_installments || null;
  form.paid_installments = item.paid_installments || 0;
  form.start_month = item.start_month || props.period.month;
  form.start_year = item.start_year || props.period.year;
  form.end_month = item.end_month || null;
  form.end_year = item.end_year || null;

  if (form.has_installments && form.start_month && form.start_year && form.total_installments && (!form.end_month || !form.end_year)) {
    onTenorOrStartChange();
  }
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
  // If not installments, reset installment fields
  if (!form.has_installments) {
    form.total_installments = null;
    form.paid_installments = 0;
    form.start_month = null;
    form.start_year = null;
    form.end_month = null;
    form.end_year = null;
  }

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
