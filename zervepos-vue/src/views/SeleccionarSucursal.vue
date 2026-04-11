<template>
  <div class="fondo">
    <div class="contenedor">

      <div class="encabezado">
        <span class="brand-icon">⚡</span>
        <h1 class="brand-name">ZervePOS</h1>
      </div>

      <h2 class="titulo">Selecciona una sucursal</h2>
      <p class="subtitulo">Bienvenido, <strong>{{ auth.usuario }}</strong>. ¿Con qué sucursal vas a trabajar hoy?</p>

      <div v-if="cargando" class="estado">
        <span class="spinner"></span>
        <p>Cargando sucursales...</p>
      </div>

      <div v-else-if="sucursalStore.sucursales.length === 0" class="estado error-msg">
        <p>No se encontraron sucursales disponibles.</p>
        <button @click="cargarSucursales" class="btn-reintentar">Reintentar</button>
      </div>

      <div v-else class="cards-grid">
        <button
          v-for="sucursal in sucursalStore.sucursales"
          :key="sucursal.sucursalId"
          class="card"
          @click="seleccionar(sucursal)"
        >
          <span class="card-icon">🏪</span>
          <div class="card-info">
            <h3 class="card-nombre">{{ sucursal.nombre }}</h3>
            <p class="card-direccion">{{ sucursal.direccion }}</p>
          </div>
          <span class="card-arrow">→</span>
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useSucursalStore } from '../stores/sucursal'
import { useSucursales } from '../composables/useSucursales'

const router = useRouter()
const auth = useAuthStore()
const sucursalStore = useSucursalStore()
const { cargando, cargarSucursales, seleccionarSucursal } = useSucursales()

function seleccionar(sucursal) {
  seleccionarSucursal(sucursal)
  router.push('/app/dashboard')
}

onMounted(() => {
  cargarSucursales()
})
</script>

<style scoped>
.fondo {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f2535 0%, #1a3a4a 60%, #0f2535 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
}

.contenedor {
  width: 100%;
  max-width: 680px;
}

.encabezado {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 32px;
}

.brand-icon {
  font-size: 1.8rem;
}

.brand-name {
  color: #fff;
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0;
}

.titulo {
  color: #fff;
  font-size: 1.4rem;
  font-weight: 700;
  text-align: center;
  margin: 0 0 8px;
}

.subtitulo {
  color: rgba(255, 255, 255, 0.6);
  text-align: center;
  font-size: 0.95rem;
  margin: 0 0 36px;
}

.subtitulo strong {
  color: rgba(255, 255, 255, 0.9);
}

.cards-grid {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.card {
  display: flex;
  align-items: center;
  gap: 16px;
  width: 100%;
  padding: 20px 24px;
  background: rgba(255, 255, 255, 0.07);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 14px;
  cursor: pointer;
  text-align: left;
  color: white;
  transition: background 0.2s, transform 0.15s, border-color 0.2s;
}

.card:hover {
  background: rgba(255, 255, 255, 0.13);
  border-color: #4fc3f7;
  transform: translateY(-2px);
}

.card-icon {
  font-size: 1.8rem;
  flex-shrink: 0;
}

.card-info {
  flex: 1;
}

.card-nombre {
  font-size: 1rem;
  font-weight: 700;
  margin: 0 0 4px;
  color: #fff;
}

.card-direccion {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.55);
  margin: 0;
}

.card-arrow {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.4);
  transition: color 0.2s, transform 0.2s;
}

.card:hover .card-arrow {
  color: #4fc3f7;
  transform: translateX(4px);
}

.estado {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: rgba(255, 255, 255, 0.6);
  padding: 40px 0;
}

.error-msg {
  color: #ff7070;
}

.btn-reintentar {
  padding: 10px 24px;
  background: transparent;
  border: 1px solid #ff7070;
  border-radius: 8px;
  color: #ff7070;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.15s;
}

.btn-reintentar:hover {
  background: rgba(255, 80, 80, 0.15);
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid rgba(255, 255, 255, 0.2);
  border-top-color: #4fc3f7;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>