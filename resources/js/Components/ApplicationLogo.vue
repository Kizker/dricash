<template>
  <div class="inline-flex items-center" :class="customClass">
    <!-- Icon Only Variant (For compact spaces, avatars, and tiny badges - uses logo-dricash.svg) -->
    <template v-if="variant === 'icon'">
      <img
        src="/images/logo-dricash.svg"
        alt="Dricash Emblem"
        class="object-contain select-none"
        :class="iconSizeClass || 'w-7 h-7'"
        @error="(e) => e.target.src = '/images/logo-icon.png'"
      />
    </template>

    <!-- App Icon Badge Variant (Modern squircle container for Auth & Hero) -->
    <template v-else-if="variant === 'badge'">
      <div class="inline-flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-white border border-slate-200/90 shadow-xs select-none transition-transform group-hover:scale-105">
        <img
          src="/images/logo-dricash.svg"
          alt="Dricash Emblem"
          class="w-7 h-7 sm:w-7.5 sm:h-7.5 object-contain select-none"
          @error="(e) => e.target.src = '/images/logo-icon.png'"
        />
      </div>
    </template>

    <!-- Text Only Variant / Sidebar Expanded (Tulisan Dricash saja dengan ketebalan bold) -->
    <template v-else-if="variant === 'sidebar' || variant === 'text' || variant === 'text-bold'">
      <span class="text-xl font-bold tracking-tight text-white select-none" :class="imageClass">
        Dricash
      </span>
    </template>

    <!-- Full Official Brand Logo Variant (Original Uncropped SVG Logo) -->
    <template v-else-if="variant === 'full'">
      <div class="flex items-center justify-center group select-none">
        <img
          src="/images/dricash.svg"
          alt="Dricash"
          class="h-9 sm:h-11 w-auto max-w-[200px] sm:max-w-[240px] object-contain select-none transition-transform group-hover:scale-102"
          :class="imageClass"
        />
      </div>
    </template>

    <!-- White / Dark Variant with Emblem and White Text -->
    <template v-else-if="variant === 'white' || variant === 'dark-sidebar'">
      <div class="flex items-center group select-none shrink-0">
        <img
          src="/images/logo-white.png"
          alt="Dricash"
          class="h-8 w-auto max-w-[160px] object-contain select-none transition-transform group-hover:scale-102"
          :class="imageClass"
          @error="(e) => e.target.src = '/images/dricash.svg'"
        />
      </div>
    </template>

    <!-- Navbar Logo Variant (Header Brand - SVG Logo Utuh) -->
    <template v-else>
      <div class="flex items-center group select-none">
        <img
          src="/images/dricash.svg"
          alt="Dricash"
          class="h-7 sm:h-8.5 w-auto max-w-[140px] sm:max-w-[170px] object-contain select-none transition-transform group-hover:scale-102"
          :class="imageClass"
        />
      </div>
    </template>
  </div>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'navbar', // 'icon' | 'full' | 'navbar'
  },
  iconSizeClass: {
    type: String,
    default: '',
  },
  imageClass: {
    type: String,
    default: '',
  },
  customClass: {
    type: String,
    default: '',
  }
});
</script>
