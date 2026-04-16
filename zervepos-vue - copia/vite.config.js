import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
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