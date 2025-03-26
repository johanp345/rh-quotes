import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js','resources/css/app.css'],
            refresh: true,
            buildDirectory:"dist"
        }),
        tailwindcss(),
        vue(),

    ],
    build: {
        outDir: 'dist',
        manifest: 'manifest.json',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                // assetFileNames: 'assets/[name]-[hash][extname]',
                // entryFileNames: 'assets/[name]-[hash].js',
                manualChunks: undefined
            }
        }
    },
});