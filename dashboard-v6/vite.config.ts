import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

// https://vite.dev/config/
export default defineConfig({
  base: "/pcd-v2026/",
  server: {
    host: "127.0.0.1",
    port: 4000,
    proxy: {
      "/api": "http://127.0.0.1:8080",
    },
  },
  plugins: [react()],
});
