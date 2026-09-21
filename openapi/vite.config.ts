import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  base: "/protocols/",
  server: {
    // Swagger UI 带 withCredentials 发请求，跨域时后端的 Access-Control-Allow-Origin: *
    // 会被浏览器拒绝。这里代理成同源请求，"Try it out" 就能直接打本地后端。
    proxy: {
      "/api": {
        target: process.env.VITE_API_TARGET ?? "http://127.0.0.1:8000",
        changeOrigin: true,
      },
    },
  },
});
