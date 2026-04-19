// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/library.css',   // library/* + blog 列表页
                'resources/css/reader.css',    // 全站阅读页（待建）
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
