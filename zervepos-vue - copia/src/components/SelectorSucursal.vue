<template>
  <div v-if="mostrar" class="selector-sucursal">
    <select
      v-model="sucursalIdSeleccionada"
      :disabled="cargando"
      class="select-sucursal"
      @change="onCambio"
    >
      <option :value="0" disabled>
        {{ cargando ? 'Cargando...' : 'Selecciona sucursal' }}
      </option>
      <option
        v-for="s in sucursalStore.sucursales"
        :key="s.SucursalId"
        :value="s.SucursalId"
      >
        {{ s.NombreSucursal }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore }     from '../stores/auth'
import { useSucursalStore } from '../stores/sucursal'
import { useSucursales }    from '../composables/useSucursales'

const auth         = useAuthStore()
const sucursalStore = useSucursalStore()
const { cargando, cargarSucursales, seleccionarSucursal } = useSucursales()

const mostrar = computed(() => auth.rol === 'Dueño' || auth.rol === 'Administrador')
const sucursalIdSeleccionada = ref(sucursalStore.sucursalActiva?.SucursalId || 0)

onMounted(async () => {
  if (mostrar.value && sucursalStore.sucursales.length === 0) {
    await cargarSucursales()
  }
  // Asignar la sucursal activa después de cargar la lista
  if (sucursalStore.sucursalActiva?.SucursalId) {
    sucursalIdSeleccionada.value = sucursalStore.sucursalActiva.SucursalId
  }
})
watch(() => sucursalStore.sucursalActiva, (val) => {
  sucursalIdSeleccionada.value = val?.SucursalId || 0
})

const onCambio = () => {
  const s = sucursalStore.sucursales.find(x => x.SucursalId === sucursalIdSeleccionada.value)
  if (s) seleccionarSucursal(s)
}
</script>

<style scoped>
.selector-sucursal {
  display: flex;
  align-items: center;
}
.select-sucursal {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 0.9rem;
  font-weight: 500;
  min-width: 200px;
  cursor: pointer;
  outline: none;
  transition: all 0.2s ease;
}
.select-sucursal:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.18);
  border-color: rgba(255, 255, 255, 0.4);
}
.select-sucursal:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.select-sucursal option {
  background: #1a3a4a;
  color: #fff;
}
</style>