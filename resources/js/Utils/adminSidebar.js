import { ref } from 'vue';

const STORAGE_KEY = 'dricash_sidebar_collapsed';

function getInitialState() {
  if (typeof window !== 'undefined' && window.localStorage) {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved !== null) {
        return saved === 'true';
      }
    } catch (e) {
      // ignore storage errors
    }
  }
  return false;
}

// Module-level reactive singleton: persists state across all Inertia page visits and layouts
export const isSidebarCollapsed = ref(getInitialState());

export function toggleSidebar() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
  if (typeof window !== 'undefined' && window.localStorage) {
    try {
      localStorage.setItem(STORAGE_KEY, isSidebarCollapsed.value ? 'true' : 'false');
    } catch (e) {
      // ignore storage errors
    }
  }
}

export function setSidebarCollapsed(val) {
  isSidebarCollapsed.value = !!val;
  if (typeof window !== 'undefined' && window.localStorage) {
    try {
      localStorage.setItem(STORAGE_KEY, isSidebarCollapsed.value ? 'true' : 'false');
    } catch (e) {
      // ignore storage errors
    }
  }
}
