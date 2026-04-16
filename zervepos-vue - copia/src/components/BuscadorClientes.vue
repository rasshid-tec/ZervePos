<template>
  <div class="buscador-wrap">

    <div class="input-contenedor">
      <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input
        ref="inputRef"
        v-model="termino"
        type="text"
        class="input"
        placeholder="Buscar cliente por nombre…"
        autocomplete="off"
        @input="onInput"
        @keydown.escape="cerrarDropdown"
      />
      <span v-if="cargando" class="spinner"></span>
    </div>

    <Transition name="dropdown">
      <div v-if="mostrarDropdown" class="dropdown">

        <div
          v-for="c in clientes"
          :key="c.ClienteId"
          class="dropdown-item"
          @click="seleccionar(c)"
        >
          <div class="item-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
          <div class="item-info">
            <span class="item-nombre">{{ c.NombreCliente }}</span>
            <span class="item-sub">{{ c.Negocio || 'Sin negocio' }}</span>
          </div>
          <div class="item-credito">
            <span class="credito-label">Límite</span>
            <span class="credito-valor">${{ formato(c.creditoPermitido) }}</span>
          </div>
        </div>

        <div v-if="clientes.length === 0 && !cargando" class="dropdown-vacio">
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
import { ref } from 'vue'
import { useToast } from '../composables/useToast'

const emit = defineEmits(['seleccionar'])
const toast = useToast()

const inputRef        = ref(null)
const termino         = ref('')
const clientes        = ref([])
const cargando        = ref(false)
const mostrarDropdown = ref(false)

let timer = null

const formato = (n) => Number(n || 0).toFixed(2)

function onInput() {
  clearTimeout(timer)
  const t = termino.value.trim()

  if (t.length < 2) {
    clientes.value        = []
    mostrarDropdown.value = false
    return
  }

  timer = setTimeout(() => buscar(t), 300)
}

async function buscar(texto) {
  cargando.value        = true
  mostrarDropdown.value = true
  try {
    const res  = await fetch('/php/buscar_clientes.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ busqueda: texto })
    })
    const data = await res.json()
    clientes.value = data.status === 1 ? data.clientes : []
  } catch {
    clientes.value = []
    toast.error('Error al buscar clientes')
  } finally {
    cargando.value = false
  }
}

async function seleccionar(c) {
  // Obtener crédito actualizado al seleccionar
  try {
    const res  = await fetch('/php/obtener_credito_cliente.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ clienteId: c.ClienteId })
    })
    const data = await res.json()
    if (data.status === 1 && data.credito) {
      // Enriquecer el objeto cliente con datos de crédito
      c.LimiteCredito     = Number(data.credito.LimiteCredito)
      c.TotalDeuda        = Number(data.credito.TotalDeuda)
      c.CreditoDisponible = Number(data.credito.CreditoDisponible)
    }
  } catch {
    toast.error('Error al obtener crédito del cliente')
  }

  emit('seleccionar', c)
  cerrarDropdown()
}

function cerrarDropdown() {
  mostrarDropdown.value = false
  clientes.value        = []
  termino.value         = ''
}

defineExpose({ focus: () => inputRef.value?.focus() })
</script>

<style scoped>
.buscador-wrap {
  position: relative;
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

.item-icono {
  width: 34px;
  height: 34px;
  background: #f0fdf4;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #16a34a;
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

.item-sub {
  font-size: 12px;
  color: #94a3b8;
}

.item-credito {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
  flex-shrink: 0;
}

.credito-label {
  font-size: 11px;
  font-weight: 700;
  color: #16a34a;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.credito-valor {
  font-size: 14px;
  font-weight: 700;
  color: #1a2b3c;
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