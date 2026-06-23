import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Modal, ModalLink, renderApp } from '@inertiaui/modal-vue'
import VueApexCharts from 'vue3-apexcharts';

const appName = import.meta.env.VITE_APP_NAME || 'ISTAHT';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: renderApp(App, props) }) 
            .use(plugin)
            .use(ZiggyVue)
            .use(VueApexCharts)
            .component('ModalLink', ModalLink)
            .component('Modal', Modal)
            .mount(el);
    },
    progress: {
        color: '#00AEEF',
    },
});
