<template>
  <div class="layout">
    <SidebarMenu />
    <div class="layout-body">
      <header class="layout-header">
        <h1 class="welcome-text">Bienvenido, {{ auth.usuario }}</h1>
        <div class="header-derecho">
          <SelectorSucursal />
          <button
            v-if="route.name === 'ventas'"
            class="btn-cerrar-caja"
            @click="router.push('/caja/cerrar')"
          >
            Cerrar Caja
          </button>
          <button class="btn-cambiar-sesion" @click="mostrarCambioSesion = true">
            Cambiar sesión
          </button>
        </div>
      </header>
      <main class="layout-main">
        <RouterView />
      </main>
    </div>

    <CambiarSesionDialog
      :visible="mostrarCambioSesion"
      @update:visible="mostrarCambioSesion = $event"
    />

    <AlertaToast ref="toastRef" />
  </div>
</template>

<script setup>
import { ref, onMounted }      from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SidebarMenu             from '../components/SidebarMenu.vue'
import SelectorSucursal        from '../components/SelectorSucursal.vue'
import CambiarSesionDialog     from '../components/CambiarSesionDialog.vue'
import AlertaToast             from '../components/AlertaToast.vue'
import { useAuthStore }        from '../stores/auth'
import { registrarToast }      from '../composables/useToast'

const auth                = useAuthStore()
const route               = useRoute()
const router              = useRouter()
const mostrarCambioSesion = ref(false)
const toastRef            = ref(null)

onMounted(() => {
  registrarToast(toastRef.value)
})
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
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.welcome-text {
  color: #fff;
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
}
.header-derecho {
  display: flex;
  align-items: center;
  gap: 12px;
}
.btn-cerrar-caja {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.btn-cerrar-caja:hover {
  background: rgba(239, 68, 68, 0.25);
  border-color: rgba(239, 68, 68, 0.5);
}
.btn-cambiar-sesion {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.btn-cambiar-sesion:hover {
  background: rgba(255, 255, 255, 0.18);
  border-color: rgba(255, 255, 255, 0.4);
}
.layout-main {
  padding: 32px;
  flex: 1;
}
</style>