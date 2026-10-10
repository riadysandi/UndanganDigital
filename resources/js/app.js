import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Platform Undangan Digital';

// Ambil element app dan data-page yang di-render oleh directive @inertia
const el = document.getElementById('app');
let initialPage;
if (el && el.dataset.page) {
    try {
        initialPage = JSON.parse(el.dataset.page);
    } catch (e) {
        console.error('Failed to parse initial page:', e);
    }
}

createInertiaApp({
    page: initialPage,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob('./pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4F46E5',
    },
});
