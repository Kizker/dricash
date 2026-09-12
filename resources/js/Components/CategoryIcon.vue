<template>
  <component :is="iconComponent" :size="size" :class="customClass" />
</template>

<script setup>
import { computed } from 'vue';
import {
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
  PlusCircle,
  MinusCircle,
  ArrowUpRight,
  ArrowDownRight,
  CheckCircle2,
  AlertTriangle,
  Lock,
  Sparkles,
  PieChart,
  Calendar,
  Layers,
  Search,
  SlidersHorizontal,
  ChevronRight,
  Check,
  X,
  Edit2,
  Trash2,
  User,
  LogOut,
  RefreshCw,
  Clock,
  DollarSign
} from 'lucide-vue-next';

const props = defineProps({
  name: {
    type: String,
    default: 'Tag'
  },
  size: {
    type: [Number, String],
    default: 18
  },
  customClass: {
    type: String,
    default: ''
  }
});

const icons = {
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
  PlusCircle,
  MinusCircle,
  ArrowUpRight,
  ArrowDownRight,
  CheckCircle2,
  AlertTriangle,
  Lock,
  Sparkles,
  PieChart,
  Calendar,
  Layers,
  Search,
  SlidersHorizontal,
  ChevronRight,
  Check,
  X,
  Edit2,
  Trash2,
  User,
  LogOut,
  RefreshCw,
  Clock,
  DollarSign
};

const iconComponent = computed(() => {
  if (!props.name) return Tag;
  const normalized = props.name.trim();
  // Check exact case
  if (icons[normalized]) return icons[normalized];
  // Check PascalCase
  const pascal = normalized.charAt(0).toUpperCase() + normalized.slice(1);
  if (icons[pascal]) return icons[pascal];
  return Tag;
});
</script>
