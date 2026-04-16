<template>
  <div class="buscador-wrap">

    <!-- Input principal -->
    <div class="input-row">
      <div class="input-contenedor">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          ref="inputRef"
          v-model="termino"
          type="text"
          class="input"
          placeholder="Buscar por nombre, marca, código o SKU…"
          autocomplete="off"
          @input="onInput"
          @keydown.enter.prevent="onEnter"
          @keydown.escape="cerrarDropdown"
        />
        <span v-if="cargando" class="spinner"></span>
      </div>
    </div>

    <!-- Dropdown de resultados -->
    <Transition name="dropdown">
      <div v-if="mostrarDropdown" class="dropdown">

        <div
          v-for="p in resultados"
          :key="p.ProductoId"
          class="dropdown-item"
          :class="{
            'dropdown-item--deshabilitado': esDuplicado(p.ProductoId) || Number(p.Stock) <= 0
          }"
          @click="seleccionar(p)"
        >
          <div class="item-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
          </div>
          <div class="item-info">
            <span class="item-nombre">{{ p.NombreProducto }}</span>
            <span class="item-sub">{{ p.Marca }} · {{ p.CodigoBarras || p.SKU }}</span>
          </div>
          <div class="item-derecha">
            <span class="item-nivel">{{ p.NombreNivel }}</span>
            <span class="item-precio">${{ formato(p.PrecioVenta) }}</span>
            <span v-if="Number(p.Stock) <= 0" class="sin-stock">Sin stock</span>
            <span v-else class="item-stock">{{ p.Stock }} uds</span>
            <span v-if="esDuplicado(p.ProductoId)" class="item-ya">Ya agregado</span>
          </div>
        </div>

        <!-- Sin resultados -->
        <div v-if="resultados.length === 0 && !cargando" class="dropdown-vacio">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            <line x1="8" y1="11" x2="14" y2="11"/>
          </svg>
          <span>Sin resultados para "{{ termino }}"</span>
        </div>

      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useToast } from '../composables/useToast'

const props = defineProps({
  nivelId:    { type: Number, required: true },
  carritoIds: { type: Array,  default: () => [] }
})

const emit = defineEmits(['seleccionar'])

const auth  = useAuthStore()
const toast = useToast()

const inputRef        = ref(null)
const termino         = ref('')
const resultados      = ref([])
const cargando        = ref(false)
const mostrarDropdown = ref(false)

let timerBusqueda = null

const formato     = (n) => Number(n || 0).toFixed(2)
const esDuplicado = (productoId) => props.carritoIds.includes(productoId)

// ── Búsqueda en tiempo real (debounce 300ms) ──────────────────
function onInput() {
  clearTimeout(timerBusqueda)
  const t = termino.value.trim()

  if (t.length < 2) {
    resultados.value      = []
    mostrarDropdown.value = false
    return
  }

  timerBusqueda = setTimeout(() => buscarPorNombre(t), 300)
}

async function buscarPorNombre(texto) {
  cargando.value        = true
  mostrarDropdown.value = true
  try {
    const res  = await fetch('/php/buscar_productos_venta.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        busqueda:   texto,
        sucursalId: Number(auth.sucursalId),
        nivelId:    props.nivelId
      })
    })
    const data = await res.json()
    resultados.value = data.status === 1 ? data.productos : []
  } catch {
    resultados.value = []
    toast.error('Error al buscar productos')
  } finally {
    cargando.value = false
  }
}

// ── Enter: búsqueda exacta por SKU / código de barras ─────────
async function onEnter() {
  const t = termino.value.trim()
  if (!t) return

  if (mostrarDropdown.value && resultados.value.length > 0) return

  cargando.value = true
  try {
    const res  = await fetch('/php/obtener_producto_codigo_venta.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        codigo:     t,
        sucursalId: Number(auth.sucursalId),
        nivelId:    props.nivelId
      })
    })
    const data = await res.json()

    if (data.status === 1 && data.producto) {
      seleccionar(data.producto)
    } else {
      mostrarDropdown.value = true
      resultados.value      = []
      toast.warn(`No se encontró ningún producto con el código "${t}"`)
    }
  } catch {
    toast.error('Error de conexión')
  } finally {
    cargando.value = false
  }
}

// ── Seleccionar producto del dropdown ─────────────────────────
function seleccionar(p) {
  if (esDuplicado(p.ProductoId)) {
    toast.warn(`"${p.NombreProducto}" ya está en el carrito`)
    return
  }
  if (Number(p.Stock) <= 0) {
    toast.warn(`"${p.NombreProducto}" no tiene stock disponible`)
    return
  }
  emit('seleccionar', p)
  cerrarDropdown()
}

function cerrarDropdown() {
  mostrarDropdown.value = false
  resultados.value      = []
  termino.value         = ''
}

// ── Si cambia el nivel global limpiar resultados abiertos ─────
watch(() => props.nivelId, () => {
  resultados.value      = []
  mostrarDropdown.value = false
})

defineExpose({ focus: () => inputRef.value?.focus() })
</script>

<style scoped>
.buscador-wrap {
  position: relative;
  width: 100%;
}

.input-row {
  width: 100%;
}

.input-contenedor {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  width: 16px;
  height: 16px;
  color: #94a3b8;
  pointer-events: none;
}

.input {
  width: 100%;
  padding: 10px 40px 10px 36px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 14px;
  color: #1a2b3c;
  background: #f8fafc;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s, background 0.2s;
}

.input:focus {
  border-color: #2563eb;
  background: #fff;
}

.spinner {
  position: absolute;
  right: 12px;
  width: 16px;
  height: 16px;
  border: 2px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: girar 0.6s linear infinite;
}

@keyframes girar { to { transform: rotate(360deg); } }

.dropdown {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.10);
  z-index: 200;
  overflow: hidden;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  cursor: pointer;
  transition: background 0.15s;
  border-bottom: 1px solid #f1f5f9;
}

.dropdown-item:last-child { border-bottom: none; }
.dropdown-item:hover { background: #f8fafc; }

.dropdown-item--deshabilitado {
  opacity: 0.5;
  cursor: not-allowed;
}

.dropdown-item--deshabilitado:hover { background: #fff; }

.item-icono {
  width: 34px;
  height: 34px;
  background: #eff6ff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #2563eb;
}

.item-icono svg { width: 16px; height: 16px; }

.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.item-nombre {
  font-size: 14px;
  font-weight: 600;
  color: #1a2b3c;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-sub { font-size: 12px; color: #94a3b8; }

.item-derecha {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
  flex-shrink: 0;
}

.item-nivel {
  font-size: 11px;
  font-weight: 700;
  color: #2563eb;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.item-precio {
  font-size: 14px;
  font-weight: 700;
  color: #16a34a;
}

.item-stock {
  font-size: 10px;
  color: #94a3b8;
}

.sin-stock {
  font-size: 10px;
  color: #ef4444;
  font-weight: 600;
}

.item-ya {
  font-size: 10px;
  color: #ef4444;
  font-weight: 600;
}

.dropdown-vacio {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px 14px;
  color: #94a3b8;
  font-size: 13px;
}

.dropdown-vacio svg { width: 16px; height: 16px; flex-shrink: 0; }

.dropdown-enter-active,
.dropdown-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-enter-from,
.dropdown-leave-to { opacity: 0; transform: translateY(-6px); }
</style>