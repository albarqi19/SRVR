import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    outDir: 'dist',
    rollupOptions: {
      input: {
        main: 'mosque_admin_dashboard.html'
      }
    }
  },
  server: {
    port: 3000,
    open: true
  }
})
