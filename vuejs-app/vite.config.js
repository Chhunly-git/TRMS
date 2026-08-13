import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  // បញ្ឈប់ Vite Pre-bundling លើ TanStack Table ដើម និង Table Core
  optimizeDeps: {
    exclude: ['@tanstack/vue-table', '@tanstack/table-core']
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      protocol: 'ws',
      port: 5173,
    },
    watch: {
      usePolling: true,
      interval: 1000,
    }
  }
})