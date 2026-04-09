<template>
  <div class="layout">
    <SidebarMenu />
    <div class="layout-body">
      <header class="layout-header">
        <h1 class="welcome-text">Bienvenido, {{ auth.usuario }}</h1>
      </header>
      <main class="layout-main">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import SidebarMenu from '../components/SidebarMenu.vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

.layout-body {
  margin-left: 240px;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #f0f2f5;
  min-height: 100vh;
}

.layout-header {
  background: #1a3a4a;
  padding: 18px 32px;
}

.welcome-text {
  color: #fff;
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
}

.layout-main {
  padding: 32px;
  flex: 1;
}
</style>