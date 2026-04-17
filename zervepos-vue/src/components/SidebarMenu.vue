<template>
  <aside class="sidebar">

    <div class="sidebar-brand">
      <span class="brand-icon" v-html="ICONS.bolt"></span>
      <span class="brand-name">ZervePOS</span>
    </div>

    <nav class="sidebar-nav">
      <RouterLink
        v-for="item in menuItems"
        :key="item.route"
        :to="item.route"
        class="nav-item"
        active-class="nav-item--active"
      >
        <span class="nav-icon" v-html="ICONS[item.icon]"></span>
        <span class="nav-label">{{ item.label }}</span>
      </RouterLink>
    </nav>

    <div class="sidebar-footer">
      <button class="btn-logout" @click="logout">
        <span class="nav-icon" v-html="ICONS.logout"></span>
        <span>Cerrar Sesión</span>
      </button>
    </div>

  </aside>
</template>

<script setup>
import { useMenu } from '../composables/useMenu'
import { ICONS } from '../utils/icons'

const { menuItems, logout } = useMenu()
</script>

<style scoped>
.sidebar {
  width: 240px;
  min-height: 100vh;
  background: #1a3a4a;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 100;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 22px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-icon {
  color: #f97316;
  display: flex;
  align-items: center;
}

.brand-name {
  color: #fff;
  font-size: 1.15rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.sidebar-nav {
  flex: 1;
  padding: 14px 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: rgba(255, 255, 255, 0.72);
  text-decoration: none;
  font-size: 0.95rem;
  transition: background 0.15s, color 0.15s;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.07);
  color: #fff;
}

.nav-item--active {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
  border-left-color: #4fc3f7;
}

.nav-icon {
  width: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.nav-label {
  white-space: nowrap;
}

.sidebar-footer {
  padding: 14px 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-logout {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 7px;
  color: rgba(255, 255, 255, 0.72);
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}

.btn-logout:hover {
  background: rgba(255, 80, 80, 0.15);
  color: #ff7070;
  border-color: #ff7070;
}
</style>