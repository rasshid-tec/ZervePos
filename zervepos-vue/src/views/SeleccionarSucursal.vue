<template>
  <div class="fondo"></div>

  <div class="selector-box">
    <h2>¿En qué sucursal trabajarás hoy?</h2>
    <p>{{ nombre }}</p>

    <div v-if="cargando" class="estado">Cargando sucursales...</div>

    <div v-else-if="sucursales.length === 0" class="estado">
      No tienes sucursales asignadas.
    </div>

    <div v-else class="sucursales">
      <button
        v-for="sucursal in sucursales"
        :key="sucursal.SucursalId"
        class="sucursal-btn"
        @click="seleccionar(sucursal)"
      >
        <span class="sucursal-nombre">{{ sucursal.NombreSucursal }}</span>
        <span class="sucursal-ciudad">{{ sucursal.Ciudad }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '../composables/useToast'

const router    = useRouter()
const toast     = useToast()
const sucursales = ref([])
const cargando  = ref(true)
const nombre    = sessionStorage.getItem('usuarioLogueado')
const rol       = sessionStorage.getItem('rolUsuario')

onMounted(async () => {
  await cargarSucursales()
})

async function cargarSucursales() {
  try {
    const respuesta = await fetch('/php/obtener_sucursales.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        UsuarioId: sessionStorage.getItem('usuarioId')
      })
    })

    const data = await respuesta.json()

    if (data.status === 1) {
      sucursales.value = data.sucursales

      // Si solo tiene una sucursal entra directo sin preguntar
      if (sucursales.value.length === 1) {
        seleccionar(sucursales.value[0])
      }
    } else {
      toast.error('No se pudieron cargar las sucursales.')
    }
  } catch (error) {
    toast.error('No se pudo conectar con el servidor.')
  } finally {
    cargando.value = false
  }
}

function seleccionar(sucursal) {
  sessionStorage.setItem('sucursalId',     sucursal.SucursalId)
  sessionStorage.setItem('sucursalNombre', sucursal.NombreSucursal)

  const destino = {
    'Dueño':         '/dueno',
    'Administrador': '/admin'
  }[rol]

  router.push(destino)
}
</script>

<style scoped>
.fondo {
  position: fixed;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
  z-index: 0;
}

.selector-box {
  position: relative;
  z-index: 1;
  width: 480px;
  margin: 0 auto;
  margin-top: 10vh;
  padding: 44px 36px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.07);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: white;
}

h2 {
  text-align: center;
  font-size: 26px;
  font-weight: 700;
  margin-bottom: 8px;
}

p {
  text-align: center;
  color: rgba(255,255,255,0.55);
  font-size: 14px;
  margin-bottom: 32px;
}

.estado {
  text-align: center;
  color: rgba(255,255,255,0.5);
  padding: 20px 0;
}

.sucursales {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sucursal-btn {
  width: 100%;
  padding: 16px 20px;
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 12px;
  background: rgba(255,255,255,0.06);
  color: white;
  cursor: pointer;
  transition: all 0.25s;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
}

.sucursal-btn:hover {
  background: rgba(36, 121, 212, 0.3);
  border-color: rgba(36, 121, 212, 0.6);
  transform: translateY(-1px);
}

.sucursal-nombre {
  font-size: 16px;
  font-weight: 600;
}

.sucursal-ciudad {
  font-size: 13px;
  color: rgba(255,255,255,0.5);
}
</style>