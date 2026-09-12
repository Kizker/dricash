import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from 'ziggy-js';
import { Ziggy } from './ziggy';
import ApplicationLogo from './Components/ApplicationLogo.vue';

// Dynamic Ziggy configuration supporting custom ports and relative paths
const getActiveZiggy = () => {
    const config = { ...Ziggy };
    if (typeof window !== 'undefined') {
        if (window.Ziggy && window.Ziggy.routes) {
            config.routes = { ...config.routes, ...window.Ziggy.routes };
        }
        config.url = window.location.origin;
        config.port = window.location.port ? parseInt(window.location.port, 10) : null;
    }
    return config;
};

const activeZiggy = getActiveZiggy();
if (typeof window !== 'undefined') {
    window.Ziggy = activeZiggy;
    window.route = (name, params, absolute = false, config = activeZiggy) => route(name, params, absolute, config);
}

const appName = import.meta.env.VITE_APP_NAME || 'Dricash';

/**
 * Synchronize current URL with top-level window and persistent session.
 * This is crucial when testing inside iframe-based mobile simulator extensions,
 * responsive dev tools, or embedded frames so the browser's address bar always matches.
 */
const syncTopWindowUrl = (url) => {
    if (typeof window === 'undefined' || !url) return;

    try {
        sessionStorage.setItem('dricash_last_active_url', url);
    } catch (e) {
        // ignore storage errors
    }

    // If running inside an iframe (e.g. Mobile Simulator extension / device wrapper)
    if (window.self !== window.top) {
        try {
            // Same-origin check
            if (window.top && window.top.location && window.top.location.origin === window.location.origin) {
                const targetUrl = url.startsWith('http') ? url : (window.location.origin + (url.startsWith('/') ? url : '/' + url));
                const currentTopPath = window.top.location.pathname + window.top.location.search;
                const newPath = url.startsWith('http') ? (new URL(url)).pathname + (new URL(url)).search : url;

                if (currentTopPath !== newPath) {
                    window.top.history.pushState({ inertia: true, url }, '', targetUrl);
                }
            }
        } catch (e) {
            // Cross-origin iframe fallback: send postMessage
            try {
                window.parent.postMessage({ type: 'DRICASH_NAVIGATE', url }, '*');
            } catch (err) {}
        }
    }
};

// Global Inertia router hooks for synchronization
router.on('navigate', (event) => {
    const pageUrl = event?.detail?.page?.url || window.location.pathname + window.location.search;
    syncTopWindowUrl(pageUrl);
});

router.on('success', (event) => {
    const pageUrl = event?.detail?.page?.url || window.location.pathname + window.location.search;
    syncTopWindowUrl(pageUrl);
});

// Sync popstate (browser back/forward) between top window and iframe
if (typeof window !== 'undefined' && window.self !== window.top) {
    try {
        if (window.top && window.top.addEventListener) {
            window.top.addEventListener('popstate', () => {
                const topPath = window.top.location.pathname + window.top.location.search;
                const currentPath = window.location.pathname + window.location.search;
                if (topPath && topPath !== currentPath) {
                    router.visit(topPath, { preserveState: false, replace: true });
                }
            });
        }
    } catch (e) {}
}

// Ensure initial page is always discovered even if SSR or script tag differences occur
let initialPage = null;
if (typeof document !== 'undefined') {
    const scriptTag = document.querySelector('script[data-page="app"][type="application/json"]');
    if (scriptTag && scriptTag.textContent) {
        try {
            initialPage = JSON.parse(scriptTag.textContent);
        } catch (e) {}
    }
    if (!initialPage) {
        const el = document.getElementById('app');
        if (el && el.dataset && el.dataset.page) {
            try {
                initialPage = JSON.parse(el.dataset.page);
            } catch (e) {}
        }
    }
    // Guarantee script tag exists for @inertiajs/core v2
    if (initialPage && !document.querySelector('script[data-page="app"][type="application/json"]')) {
        try {
            const script = document.createElement('script');
            script.setAttribute('data-page', 'app');
            script.setAttribute('type', 'application/json');
            script.textContent = JSON.stringify(initialPage);
            document.body.appendChild(script);
        } catch (e) {}
    }
}

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : `${appName} - Asisten Keuangan Pintar`,
    page: initialPage || undefined,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });
        
        vueApp.use(plugin);
        vueApp.use(ZiggyVue, activeZiggy);
        
        vueApp.component('InertiaHead', Head);
        vueApp.component('InertiaLink', Link);
        vueApp.component('Head', Head);
        vueApp.component('Link', Link);
        vueApp.component('ApplicationLogo', ApplicationLogo);
        
        vueApp.config.globalProperties.route = (name, params, absolute = false) => route(name, params, absolute, activeZiggy);
        
        // Global helper for IDR currency formatting
        vueApp.config.globalProperties.$formatRupiah = (number) => {
            if (number === null || number === undefined || isNaN(number)) return 'Rp 0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        };

        return vueApp.mount(el);
    },
    progress: {
        color: '#10B981',
        showSpinner: true,
    },
});


