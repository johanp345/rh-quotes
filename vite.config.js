import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js','resources/css/app.css'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),

    ],
    build: {
        outDir: 'public/build/',
        manifest: 'manifest.json',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                assetFileNames: 'vendor/quotes-ui/[name]-[hash][extname]',
                entryFileNames: 'vendor/quotes-ui/[name]-[hash].js',
                manualChunks: undefined
            }
        }
    },
});