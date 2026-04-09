import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      // Intercepta cualquier petición que empiece con /php
      '/php': {
        target: 'http://localhost', // Apunta a tu XAMPP
        changeOrigin: true,
        secure: false
      }
    }
  }
})