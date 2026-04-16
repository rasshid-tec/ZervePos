<template>
  <div class="ventas-modulo">

    <!-- ============ PANEL IZQUIERDO: CARRITO ============ -->
    <section class="panel-carrito">
      <header class="panel-header">
        <h2>
          Carrito
          <span class="badge">{{ totalItems }}</span>
        </h2>
        <select v-model="nivelGlobal" @change="aplicarNivelGlobal" class="select-nivel">
          <option :value="1">Mayoreo</option>
          <option :value="2">Medio Mayoreo</option>
          <option :value="3">Menudeo</option>
          <option :value="4">Público General</option>
        </select>
      </header>

      <!-- Buscador -->
      <div class="buscador-contenedor">
        <BuscadorProductos
          ref="buscadorRef"
          :nivel-id="nivelGlobal"
          :carrito-ids="carritoIds"
          @seleccionar="agregarAlCarrito"
        />
      </div>

      <!-- Items del carrito -->
      <div class="carrito-items">
        <div v-if="!carrito.length" class="vacio">Sin productos…</div>

        <div v-for="(item, idx) in carrito" :key="item.uid" class="carrito-item">
          <div class="item-info">
            <strong>{{ item.NombreProducto }}</strong>
            <small>{{ item.Marca }}</small>
          </div>

          <select
            :value="item.NivelId"
            @change="cambiarNivel(idx, $event.target.value)"
            class="select-nivel-item"
          >
            <option :value="1">Mayoreo</option>
            <option :value="2">M. Mayoreo</option>
            <option :value="3">Menudeo</option>
            <option :value="4">Público</option>
          </select>

          <div class="cantidad">
            <button @click="modificarCant(idx, -1)">−</button>
            <input
              type="number"
              min="1"
              :max="item.Stock"
              :value="item.Cantidad"
              @change="modificarCant(idx, 0, $event.target.value); $event.target.value = carrito[idx].Cantidad"
              class="input-cantidad-manual"
            />
            <button @click="modificarCant(idx, 1)">+</button>
          </div>

          <div class="precio-col">
            <input
              type="number"
              step="0.01"
              :value="item.PrecioUnitario"
              @change="cambiarPrecio(idx, $event.target.value)"
              class="input-precio"
            />
            <small>c/u</small>
          </div>

          <div class="subtotal">
            ${{ formato(item.PrecioUnitario * item.Cantidad) }}
          </div>

          <button @click="eliminarItem(idx)" class="btn-eliminar">✕</button>
        </div>
      </div>

      <footer class="panel-footer">
        <span>Total</span>
        <strong class="total">${{ formato(total) }}</strong>
      </footer>
    </section>

    <!-- ============ PANEL DERECHO: COBRO ============ -->
    <section class="panel-cobro">
      <h2>Cobro</h2>

      <!-- Cliente -->
      <div class="campo">
        <label>Cliente (opcional)</label>
        <BuscadorClientes @seleccionar="onClienteSeleccionado" />
        <div v-if="clienteSeleccionado" class="cliente-seleccionado">
          <div class="cliente-info">
            <span class="cliente-nombre">{{ clienteSeleccionado.NombreCliente }}</span>
            <div class="cliente-creditos">
              <span class="credito-item">
                Límite: <strong>${{ formato(clienteSeleccionado.LimiteCredito || clienteSeleccionado.creditoPermitido) }}</strong>
              </span>
              <span class="credito-item">
                Deuda: <strong class="deuda">${{ formato(clienteSeleccionado.TotalDeuda || 0) }}</strong>
              </span>
              <span class="credito-item">
                Disponible: <strong class="disponible">${{ formato(clienteSeleccionado.CreditoDisponible || clienteSeleccionado.creditoPermitido) }}</strong>
              </span>
            </div>
          </div>
          <button @click="limpiarCliente" class="btn-eliminar">✕</button>
        </div>
      </div>

      <!-- Métodos de pago -->
      <div class="metodos-pago">
        <div class="metodos-header">
          <h3>Métodos de pago</h3>
          <button @click="agregarMetodo" class="btn-agregar">+ Agregar</button>
        </div>

        <div v-for="(m, idx) in metodos" :key="idx" class="metodo-row">
          <select v-model="m.MetodoPago" @change="validarMetodo(idx)">
            <option v-for="op in opcionesDisponibles(idx)" :key="op" :value="op">{{ op }}</option>
          </select>
          <input
            type="number"
            step="0.01"
            v-model.number="m.MontoPago"
            placeholder="0.00"
          />
          <button @click="recalcularRestante(idx)" title="Tomar restante">↩</button>
          <button @click="eliminarMetodo(idx)" class="btn-eliminar">✕</button>
        </div>
      </div>

      <div class="resumen-cobro">
        <div><span>Pagado</span><strong>${{ formato(pagado) }}</strong></div>
        <div :class="{ falta: restante > 0 }">
          <span>Restante</span><strong>${{ formato(restante) }}</strong>
        </div>
        <div v-if="cambio > 0" class="cambio">
          <span>Cambio</span><strong>${{ formato(cambio) }}</strong>
        </div>
      </div>

      <button
        :disabled="!puedeCobrar || procesando"
        @click="cobrar"
        class="btn-cobrar"
      >
        <span v-if="procesando">Procesando…</span>
        <span v-else-if="restante > 0">Faltan ${{ formato(restante) }}</span>
        <span v-else>Cobrar ${{ formato(total) }}</span>
      </button>

      <p v-if="errorMsg" class="error">{{ errorMsg }}</p>
    </section>

    <!-- ============ TICKET ============ -->
    <Ticket
      v-if="ticketVisible"
      :venta-id="ultimaVentaId"
      @cerrar="ticketVisible = false"
    />

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAuthStore }      from '../stores/auth'
import { useToast }          from '../composables/useToast'
import { useVentasApi }      from '../composables/useVentasApi'
import BuscadorProductos     from '../components/BuscadorProductos.vue'
import BuscadorClientes      from '../components/BuscadorClientes.vue'
import Ticket                from '../components/Ticket.vue'

const auth  = useAuthStore()
const toast = useToast()
const api   = useVentasApi()

/* ── Sesión ──────────────────────────────────────────────────── */
const empleadoId = computed(() => Number(auth.empleadoId))
const sucursalId = computed(() => Number(auth.sucursalId))
const cajaId     = ref(Number(sessionStorage.getItem('cajaId') || 0))

/* ── Carrito ─────────────────────────────────────────────────── */
const buscadorRef = ref(null)
const carrito     = ref([])
const nivelGlobal = ref(4)
let   uidCounter  = 0

const carritoIds = computed(() => carrito.value.map(i => i.ProductoId))

/* ── Cobro ───────────────────────────────────────────────────── */
const clienteSeleccionado = ref(null)
const clienteId           = computed(() => clienteSeleccionado.value?.ClienteId || null)
const metodos             = ref([{ MetodoPago: 'Efectivo', MontoPago: 0 }])
const procesando          = ref(false)
const errorMsg            = ref('')
const ticketVisible       = ref(false)
const ultimaVentaId       = ref(null)

/* ── Computed ────────────────────────────────────────────────── */
const totalItems  = computed(() => carrito.value.reduce((s, i) => s + i.Cantidad, 0))
const total       = computed(() => carrito.value.reduce((s, i) => s + i.PrecioUnitario * i.Cantidad, 0))
const pagado      = computed(() => metodos.value.reduce((s, m) => s + (Number(m.MontoPago) || 0), 0))
const cambio      = computed(() => Math.max(0, pagado.value - total.value))
const restante    = computed(() => Math.max(0, total.value - pagado.value))
const puedeCobrar = computed(() =>
  carrito.value.length > 0 && pagado.value >= total.value && total.value > 0
)

const formato = (n) => Number(n || 0).toFixed(2)

/* ── Watcher métodos de pago ─────────────────────────────────── */
watch(metodos, (vals) => {
  vals.forEach((m, idx) => {
    if (['Tarjeta', 'Transferencia', 'Credito'].includes(m.MetodoPago) && Number(m.MontoPago) > total.value) {
      metodos.value[idx].MontoPago = total.value
    }
  })
}, { deep: true })

/* ── onMounted: obtener cajaId fresco ────────────────────────── */
onMounted(async () => {
  try {
    const res  = await fetch('/php/obtener_caja_activa.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ sucursalId: Number(auth.sucursalId) })
    })
    const data = await res.json()
    if (data.status === 1 && data.CajaId) {
      cajaId.value = Number(data.CajaId)
      sessionStorage.setItem('cajaId', data.CajaId)
    } else {
      toast.warn('No hay caja abierta en esta sucursal')
    }
  } catch {
    toast.error('Error al verificar caja activa')
  }
})

/* ── Carrito: agregar ────────────────────────────────────────── */
function agregarAlCarrito(p) {
  if (Number(p.Stock) <= 0) {
    toast.warn(`"${p.NombreProducto}" sin stock disponible`)
    return
  }
  carrito.value.push({
    uid:            ++uidCounter,
    ProductoId:     p.ProductoId,
    NombreProducto: p.NombreProducto,
    Marca:          p.Marca,
    PrecioCompra:   Number(p.PrecioCompra),
    PrecioUnitario: Number(p.PrecioVenta),
    Cantidad:       1,
    Stock:          Number(p.Stock),
    NivelId:        nivelGlobal.value,
    Descuento:      0,
  })
  buscadorRef.value?.focus()
}

/* ── Carrito: cantidad ───────────────────────────────────────── */
function modificarCant(idx, delta, valorManual = null) {
  const item = carrito.value[idx]
  let nueva

  if (valorManual !== null) {
    nueva = Math.max(1, parseInt(valorManual) || 1)
  } else {
    nueva = Math.max(1, item.Cantidad + delta)
  }

  if (nueva > item.Stock) {
    toast.warn(`Stock máximo disponible: ${item.Stock} unidades`)
    nueva = item.Stock
  }

  item.Cantidad = nueva
}

/* ── Carrito: eliminar ───────────────────────────────────────── */
function eliminarItem(idx) {
  carrito.value.splice(idx, 1)
}

/* ── Carrito: cambiar nivel de un item ──────────────────────── */
async function cambiarNivel(idx, nivelId) {
  const item = carrito.value[idx]
  try {
    const data = await api.obtenerPrecio(item.ProductoId, Number(nivelId))
    if (data.success && data.producto) {
      item.NivelId        = Number(nivelId)
      item.PrecioUnitario = Number(data.producto.PrecioVenta)
    }
  } catch {
    toast.error('Error al recalcular precio')
  }
}

/* ── Carrito: aplicar nivel global a todos ───────────────────── */
async function aplicarNivelGlobal() {
  for (let i = 0; i < carrito.value.length; i++) {
    await cambiarNivel(i, nivelGlobal.value)
  }
}

/* ── Carrito: editar precio manualmente ─────────────────────── */
function cambiarPrecio(idx, valor) {
  const item  = carrito.value[idx]
  const nuevo = Number(valor)
  if (nuevo <= item.PrecioCompra) {
    errorMsg.value = `El precio debe ser mayor a $${formato(item.PrecioCompra)} (precio de compra)`
    return
  }
  item.PrecioUnitario = nuevo
  errorMsg.value = ''
}

/* ── Cobro: cliente ──────────────────────────────────────────── */
function onClienteSeleccionado(c) {
  clienteSeleccionado.value = c
}

function limpiarCliente() {
  clienteSeleccionado.value = null
}

/* ── Cobro: métodos de pago ──────────────────────────────────── */
function agregarMetodo() {
  const metodoExistente = metodos.value.map(m => m.MetodoPago)
  const disponibles = ['Efectivo', 'Tarjeta', 'Transferencia', 'Credito']
    .filter(m => !metodoExistente.includes(m))

  if (disponibles.length === 0) {
    toast.warn('Ya agregaste todos los métodos de pago disponibles')
    return
  }

  metodos.value.push({ MetodoPago: disponibles[0], MontoPago: 0 })
}

function eliminarMetodo(idx) {
  if (metodos.value.length === 1) {
    metodos.value[0] = { MetodoPago: 'Efectivo', MontoPago: 0 }
    return
  }
  metodos.value.splice(idx, 1)
}

function recalcularRestante(idx) {
  const otros = metodos.value
    .filter((_, i) => i !== idx)
    .reduce((s, m) => s + (Number(m.MontoPago) || 0), 0)
  metodos.value[idx].MontoPago = Math.max(0, total.value - otros)
}

function opcionesDisponibles(idx) {
  const usados = metodos.value
    .map((m, i) => i !== idx ? m.MetodoPago : null)
    .filter(Boolean)
  return ['Efectivo', 'Tarjeta', 'Transferencia', 'Credito']
    .filter(op => !usados.includes(op))
}

function validarMetodo(idx) {
  const m = metodos.value[idx]
  if (['Tarjeta', 'Transferencia', 'Credito'].includes(m.MetodoPago) && m.MontoPago > total.value) {
    m.MontoPago = total.value
    toast.warn(`${m.MetodoPago} no puede exceder el total de la venta`)
  }
}

/* ── Cobro: registrar venta ──────────────────────────────────── */
async function cobrar() {
  errorMsg.value   = ''
  procesando.value = true

  const usaCredito = metodos.value.some(m => m.MetodoPago === 'Credito' && m.MontoPago > 0)

  if (usaCredito && !clienteId.value) {
    errorMsg.value   = 'Se requiere cliente para pagos con crédito'
    procesando.value = false
    return
  }

  if (usaCredito && clienteSeleccionado.value) {
    const montoCredito = metodos.value
      .filter(m => m.MetodoPago === 'Credito')
      .reduce((s, m) => s + Number(m.MontoPago), 0)
    const disponible = Number(
      clienteSeleccionado.value.CreditoDisponible ??
      clienteSeleccionado.value.creditoPermitido ?? 0
    )

    if (montoCredito > disponible) {
      errorMsg.value   = `Crédito insuficiente. Disponible: $${formato(disponible)}, requerido: $${formato(montoCredito)}`
      procesando.value = false
      return
    }
  }

  const payload = {
    ClienteId:  clienteId.value || null,
    EmpleadoId: empleadoId.value,
    CajaId:     cajaId.value,
    Detalles: carrito.value.map(i => ({
      ProductoId:     i.ProductoId,
      PrecioUnitario: i.PrecioUnitario,
      Cantidad:       i.Cantidad,
      Descuento:      i.Descuento || 0,
    })),
    Pagos: metodos.value
      .filter(m => Number(m.MontoPago) > 0)
      .map(m => ({
        MetodoPago: m.MetodoPago,
        MontoPago:  Number(m.MontoPago),
        Referencia: m.Referencia || null,
      })),
  }

  try {
    const data = await api.registrarVenta(payload)
    if (!data.success) {
      errorMsg.value = data.error || 'Error al registrar venta'
      return
    }
    ultimaVentaId.value = data.VentaId
    ticketVisible.value = true
    resetVenta()
  } catch (e) {
    errorMsg.value = e.message || 'Error de red al registrar venta'
  } finally {
    procesando.value = false
  }
}

/* ── Reset después de cobrar ─────────────────────────────────── */
function resetVenta() {
  carrito.value             = []
  metodos.value             = [{ MetodoPago: 'Efectivo', MontoPago: 0 }]
  clienteSeleccionado.value = null
  errorMsg.value            = ''
  buscadorRef.value?.focus()
}
</script>

<style scoped>
.ventas-modulo {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 16px;
  height: calc(100vh - 64px);
  padding: 16px;
  background: #f1f5f9;
  font-family: system-ui, sans-serif;
  box-sizing: border-box;
}

.panel-carrito,
.panel-cobro {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.panel-header h2 { margin: 0; font-size: 18px; }

.badge {
  display: inline-block;
  background: #2563eb;
  color: #fff;
  border-radius: 999px;
  padding: 2px 10px;
  font-size: 13px;
  margin-left: 6px;
}

.select-nivel,
.select-nivel-item {
  padding: 6px 10px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  background: #fff;
  font-size: 13px;
}

.buscador-contenedor {
  position: relative;
  margin-bottom: 12px;
  z-index: 10;
}

.carrito-items { flex: 1; overflow-y: auto; }
.vacio { text-align: center; color: #94a3b8; padding: 40px; }

.carrito-item {
  display: grid;
  grid-template-columns: 2fr 1fr 1.2fr 1fr 1fr 30px;
  gap: 8px;
  align-items: center;
  padding: 10px 8px;
  border-bottom: 1px solid #f1f5f9;
}
.item-info strong { display: block; font-size: 14px; }
.item-info small  { color: #64748b; font-size: 12px; }

.cantidad { display: flex; align-items: center; gap: 4px; }
.cantidad button {
  width: 28px; height: 28px;
  border: 1px solid #cbd5e1;
  background: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
}
.cantidad button:hover { background: #f1f5f9; }

.input-cantidad-manual {
  width: 48px;
  text-align: center;
  padding: 4px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 14px;
  color: #1a2b3c;
}
.input-cantidad-manual:focus {
  outline: none;
  border-color: #2563eb;
}

.precio-col { display: flex; flex-direction: column; align-items: center; }
.input-precio {
  width: 80px;
  padding: 4px 6px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  text-align: right;
  font-size: 13px;
}
.precio-col small { color: #94a3b8; font-size: 11px; }

.subtotal { font-weight: 600; text-align: right; font-size: 14px; }

.btn-eliminar {
  background: none;
  border: 0;
  color: #ef4444;
  cursor: pointer;
  font-size: 16px;
}
.btn-eliminar:hover { color: #b91c1c; }

.panel-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  margin-top: 12px;
  border-top: 2px solid #e2e8f0;
  font-size: 18px;
}
.total { font-size: 24px; color: #16a34a; }

.panel-cobro h2 { margin: 0 0 16px; font-size: 18px; }

.campo { margin-bottom: 16px; }
.campo label { display: block; font-size: 12px; color: #64748b; margin-bottom: 4px; }

.cliente-seleccionado {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-top: 8px;
  padding: 10px 12px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
}

.cliente-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.cliente-nombre {
  font-size: 14px;
  font-weight: 600;
  color: #1a2b3c;
}

.cliente-creditos {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.credito-item {
  font-size: 12px;
  color: #64748b;
}

.credito-item .deuda     { color: #ef4444; }
.credito-item .disponible { color: #16a34a; }

.metodos-pago { margin-bottom: 8px; }

.metodos-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}
.metodos-header h3 { margin: 0; font-size: 14px; }

.btn-agregar {
  background: #16a34a;
  color: #fff;
  border: 0;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
}

.metodo-row {
  display: flex;
  gap: 6px;
  margin-bottom: 6px;
  align-items: center;
}
.metodo-row select { flex: 1.2; min-width: 0; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
.metodo-row input  { flex: 1;   min-width: 0; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
.metodo-row button {
  width: 32px; height: 32px;
  flex-shrink: 0;
  border: 1px solid #cbd5e1;
  background: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.resumen-cobro {
  margin: 16px 0;
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
}
.resumen-cobro div {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  font-size: 14px;
}
.resumen-cobro .falta strong    { color: #ef4444; }
.resumen-cobro .cambio strong   { color: #2563eb; }

.btn-cobrar {
  width: 100%;
  padding: 16px;
  background: #16a34a;
  color: #fff;
  border: 0;
  border-radius: 10px;
  font-size: 18px;
  font-weight: 600;
  cursor: pointer;
  margin-top: auto;
  transition: background 0.2s;
}
.btn-cobrar:hover:not(:disabled) { background: #15803d; }
.btn-cobrar:disabled { background: #cbd5e1; cursor: not-allowed; }

.error {
  color: #ef4444;
  text-align: center;
  margin-top: 8px;
  font-size: 13px;
}
</style>