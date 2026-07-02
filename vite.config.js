import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('apexcharts') || id.includes('vue3-apexcharts')) {
                        return 'apexcharts';
                    }
                    if (id.includes('node_modules/vue/') || id.includes('node_modules/@vue/')) {
                        return 'vue-vendor';
                    }
                    if (id.includes('node_modules/@inertiajs') || id.includes('node_modules/@inertiaui')) {
                        return 'inertia-vendor';
                    }
                },
            },
        },
    },
});
