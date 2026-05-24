import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // library/* + blog 列表页
                'resources/css/library.css', // library/* + blog 列表页
                'resources/css/modules/library-index.css',
                'resources/css/modules/wiki.css',
                'resources/css/modules/tipitaka.css',
                'resources/css/modules/anthology.css',
                'resources/css/reader.css', // 全站阅读页（待建）
                // 'resources/css/main.css', // 全站阅读页（待建）
                'resources/js/app.js',
                'resources/js/reader.js',
                'resources/js/modules/term-tooltip.js',
            ],
            refresh: true,
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
});
