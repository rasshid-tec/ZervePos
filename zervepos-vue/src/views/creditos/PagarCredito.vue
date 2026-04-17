<template>
  <div class="pagar-credito">

    <div class="page-header">
      <div class="page-header__left">
        <button class="btn-back" @click="$router.push('/app/creditos')">←</button>
        <div>
          <span class="breadcrumb">
            <RouterLink to="/app/creditos" class="breadcrumb__link">Créditos</RouterLink>
            <span class="breadcrumb__sep">›</span>
            <span>Pagar</span>
          </span>
          <h1 class="page-titulo">Pagar Crédito</h1>
        </div>
      </div>
    </div>

    <!-- Buscador cliente -->
    <div class="card buscador-card">
      <div class="card__header">
        <div class="card__icono card__icono--azul">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
        </div>
        <div>
          <h2 class="card__titulo">Buscar Cliente</h2>
          <p class="card__subtitulo">Busca el cliente para registrar su pago</p>
        </div>
      </div>
      <div class="card__body">
        <BuscadorClientes @seleccionar="onClienteSeleccionado" />
        <div v-if="clienteSeleccionado" class="cliente-seleccionado">
          <div class="cliente-avatar">
            {{ clienteSeleccionado.NombreCliente.charAt(0).toUpperCase() }}
          </div>
          <div class="cliente-datos">
            <span class="cliente-nombre">{{ clienteSeleccionado.NombreCliente }}</span>
            <span class="cliente-negocio">{{ clienteSeleccionado.Negocio || 'Sin negocio' }}</span>

          </div>
          <button v-if="creditos.length > 0" @click="verEstadoCuenta" class="btn-estado-cuenta">
  Estado de cuenta
</button>

          <button @click="limpiarCliente" class="btn-limpiar">✕</button>
        </div>
      </div>
    </div>

    <!-- Sin cliente -->
    <div v-if="!clienteSeleccionado" class="empty-state">
      <div class="empty-state__icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
        </svg>
      </div>
      <h3 class="empty-state__titulo">Ningún cliente seleccionado</h3>
      <p class="empty-state__desc">Busca un cliente para ver sus créditos y registrar un pago</p>
    </div>

    <template v-else>

      <!-- Cargando créditos -->
      <div v-if="cargando" class="estado-carga">
        <span class="spinner"></span>
        <p>Cargando créditos...</p>
      </div>

      <!-- Sin créditos -->
      <div v-else-if="creditos.length === 0" class="empty-state">
        <div class="empty-state__icono empty-state__icono--verde">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
          </svg>
        </div>
        <h3 class="empty-state__titulo">Sin créditos activos</h3>
        <p class="empty-state__desc">Este cliente no tiene créditos pendientes de pago</p>
      </div>

      <!-- Lista créditos -->
      <div v-else class="layout-pago">

        <!-- Panel izquierdo: créditos -->
        <div class="creditos-panel">
          <h3 class="panel-titulo">Selecciona un crédito</h3>
          <div v-for="c in creditos" :key="c.CreditoId" class="credito-card"
            :class="{ 'credito-card--activo': creditoSeleccionado?.CreditoId === c.CreditoId }"
            @click="seleccionarCredito(c)">
            <div class="credito-card__header">
              <span class="credito-badge">Crédito #{{ c.CreditoId }}</span>
              <span class="credito-fecha">{{ c.FechaCredito }}</span>
            </div>
            <div class="credito-card__body">
              <div class="credito-dato">
                <span class="credito-dato__label">Venta</span>
                <span class="credito-dato__valor">#{{ c.VentaId }}</span>
              </div>
              <div class="credito-dato">
                <span class="credito-dato__label">Original</span>
                <span class="credito-dato__valor">${{ formato(c.MontoTotal) }}</span>
              </div>
              <div class="credito-dato">
                <span class="credito-dato__label">Pendiente</span>
                <span class="credito-dato__valor rojo">${{ formato(c.SaldoActual) }}</span>
              </div>
            </div>
            <div class="barra-wrap">
              <div class="barra-progreso" :style="{ width: porcentajePagado(c) + '%' }"></div>
            </div>
            <p class="credito-pct">{{ porcentajePagado(c) }}% pagado</p>
          </div>
        </div>

        <!-- Panel derecho: formulario de pago -->
        <div class="pago-panel">
          <div v-if="!creditoSeleccionado" class="empty-state empty-state--small">
            <div class="empty-state__icono">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="2" y="5" width="20" height="14" rx="2" />
                <line x1="2" y1="10" x2="22" y2="10" />
              </svg>
            </div>
            <h3 class="empty-state__titulo">Selecciona un crédito</h3>
            <p class="empty-state__desc">Haz clic en un crédito para registrar el pago</p>
          </div>

          <template v-else>
            <div class="card pago-card">
              <div class="card__header">
                <div class="card__icono card__icono--verde">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2" />
                    <line x1="2" y1="10" x2="22" y2="10" />
                  </svg>
                </div>
                <div>
                  <h2 class="card__titulo">Registrar Pago</h2>
                  <p class="card__subtitulo">Crédito #{{ creditoSeleccionado.CreditoId }}</p>
                </div>
              </div>

              <div class="card__body">

                <!-- Resumen crédito seleccionado -->
                <div class="resumen-credito">
                  <div class="resumen-item">
                    <span class="resumen-label">Saldo pendiente</span>
                    <span class="resumen-valor rojo">${{ formato(creditoSeleccionado.SaldoActual) }}</span>
                  </div>
                  <button class="btn-pagar-todo" @click="pagarTotal">
                    Pagar todo
                  </button>
                </div>

                <!-- Método de pago -->
                <div class="campo">
                  <label>Método de pago</label>
                  <select v-model="metodoPago" class="input">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                </div>

                <!-- Monto -->
                <div class="campo">
                  <label>Monto a pagar</label>
                  <div class="input-wrap">
                    <span class="input-prefix">$</span>
                    <input v-model.number="montoPago" type="number" min="0.01" step="0.01" class="input input--prefix"
                      placeholder="0.00" />
                  </div>
                </div>

                <!-- Efectivo recibido -->
                <div v-if="metodoPago === 'Efectivo'" class="campo">
                  <label>Efectivo recibido</label>
                  <div class="input-wrap">
                    <span class="input-prefix">$</span>
                    <input v-model.number="efectivoRecibido" type="number" min="0" step="0.01"
                      class="input input--prefix" placeholder="0.00" />
                  </div>
                </div>

                <!-- Resumen pago -->
                <div class="resumen-pago">
                  <div class="resumen-pago__row">
                    <span>Monto a pagar</span>
                    <strong>${{ formato(montoPago) }}</strong>
                  </div>
                  <div v-if="metodoPago === 'Efectivo'" class="resumen-pago__row">
                    <span>Efectivo recibido</span>
                    <strong>${{ formato(efectivoRecibido) }}</strong>
                  </div>
                  <div v-if="metodoPago === 'Efectivo' && cambio > 0" class="resumen-pago__row cambio">
                    <span>Cambio</span>
                    <strong>${{ formato(cambio) }}</strong>
                  </div>
                  <div class="resumen-pago__row saldo">
                    <span>Saldo restante</span>
                    <strong :class="saldoRestante <= 0 ? 'verde' : 'rojo'">
                      ${{ formato(Math.max(0, saldoRestante)) }}
                    </strong>
                  </div>
                </div>

                <!-- Error -->
                <p v-if="errorMsg" class="error-msg">{{ errorMsg }}</p>

                <!-- Botón pagar -->
                <button class="btn-cobrar" :disabled="!puedePagar || procesando" @click="registrarPago">
                  <span v-if="procesando">Procesando...</span>
                  <span v-else>Registrar Pago ${{ formato(montoPago) }}</span>
                </button>

              </div>
            </div>
          </template>
        </div>

      </div>

    </template>

    <!-- Ticket de pago -->
    <div v-if="ticketVisible" class="ticket-overlay" @click.self="ticketVisible = false">
      <div class="ticket-modal">
        <div class="ticket" id="ticket-pago">
          <header class="ticket-header">
            <h1>ZervePOS</h1>
            <p>Comprobante de Pago</p>
          </header>
          <div class="ticket-info">
            <div><span>Pago #</span>{{ pagoRealizado?.PagoId }}</div>
            <div><span>Fecha:</span> {{ pagoRealizado?.Fecha }}</div>
            <div><span>Cliente:</span> {{ clienteSeleccionado?.NombreCliente }}</div>
            <div><span>Crédito #:</span> {{ pagoRealizado?.CreditoId }}</div>
          </div>
          <div class="ticket-total">
            <span>Monto pagado</span>
            <strong>${{ formato(pagoRealizado?.MontoPago) }}</strong>
          </div>
          <div class="ticket-info">
            <div><span>Método:</span> {{ pagoRealizado?.MetodoPago }}</div>
            <div v-if="pagoRealizado?.MetodoPago === 'Efectivo'">
              <span>Efectivo:</span> ${{ formato(pagoRealizado?.EfectivoRecibido) }}
            </div>
            <div v-if="pagoRealizado?.MetodoPago === 'Efectivo'">
              <span>Cambio:</span> ${{ formato(pagoRealizado?.Cambio) }}
            </div>
            <div><span>Saldo restante:</span> ${{ formato(pagoRealizado?.SaldoRestante) }}</div>
          </div>
          <footer class="ticket-footer">
            <p>¡Gracias por su pago!</p>
            <p class="small">ZervePOS</p>
          </footer>
        </div>
        <div class="ticket-actions">
          <button @click="imprimir" class="btn-imprimir">🖨 Imprimir</button>
          <button @click="ticketVisible = false" class="btn-cerrar-ticket">Cerrar</button>
        </div>
      </div>
    </div>
<!-- Modal Estado de Cuenta -->
<div v-if="estadoCuentaVisible" class="ticket-overlay" @click.self="estadoCuentaVisible = false">
  <div class="estado-cuenta-modal">

    <div v-if="cargandoEstado" class="cargando">
      <span class="spinner"></span>
      <p>Cargando estado de cuenta...</p>
    </div>

    <template v-else-if="estadoCuenta">
      <header class="ec-header">
        <h2>Estado de Cuenta</h2>
        <p>{{ estadoCuenta.encabezado.NombreCliente }}</p>
        <p v-if="estadoCuenta.encabezado.Negocio" class="ec-negocio">
          {{ estadoCuenta.encabezado.Negocio }}
        </p>
      </header>

      <div class="ec-resumen">
        <div class="ec-resumen-item">
          <span class="ec-resumen-label">Límite de crédito</span>
          <span class="ec-resumen-valor">${{ formato(estadoCuenta.encabezado.LimiteCredito) }}</span>
        </div>
        <div class="ec-resumen-item">
          <span class="ec-resumen-label">Créditos activos</span>
          <span class="ec-resumen-valor">{{ estadoCuenta.encabezado.TotalCreditos }}</span>
        </div>
      </div>

      <div class="ec-creditos" id="estado-cuenta-imprimir">

        <header class="ec-print-header">
          <h2>Estado de Cuenta</h2>
          <p>{{ estadoCuenta.encabezado.NombreCliente }}</p>
          <p v-if="estadoCuenta.encabezado.Negocio">{{ estadoCuenta.encabezado.Negocio }}</p>
        </header>

        <table class="ec-tabla">
          <thead>
            <tr>
              <th>#</th>
              <th>Venta</th>
              <th>Fecha</th>
              <th>Original</th>
              <th>Pagado</th>
              <th class="right">Saldo</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in estadoCuenta.creditos" :key="c.CreditoId">
              <td>{{ c.CreditoId }}</td>
              <td>#{{ c.VentaId }}</td>
              <td>{{ c.FechaCredito }}</td>
              <td>${{ formato(c.MontoTotal) }}</td>
              <td class="verde">${{ formato(c.TotalPagado) }}</td>
              <td class="right rojo">${{ formato(c.SaldoActual) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="ec-total">
          <span>TOTAL ADEUDO</span>
          <strong class="rojo">${{ formato(estadoCuenta.encabezado.TotalAdeudo) }}</strong>
        </div>

        <footer class="ec-footer">
          <p>ZervePOS</p>
        </footer>
      </div>

    </template>

    <div class="ticket-actions">
      <button @click="imprimirEstadoCuenta" class="btn-imprimir" :disabled="cargandoEstado">
        Imprimir
      </button>
      <button @click="estadoCuentaVisible = false" class="btn-cerrar-ticket">
        Cerrar
      </button>
    </div>

  </div>
</div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import BuscadorClientes from '../../components/BuscadorClientes.vue'
import { useToast } from '../../composables/useToast'
import { useAuthStore } from '../../stores/auth'


const estadoCuentaVisible = ref(false)
const estadoCuenta = ref(null)
const cargandoEstado = ref(false)
const route = useRoute()
const toast = useToast()
const auth = useAuthStore()

const clienteSeleccionado = ref(null)
const creditos = ref([])
const creditoSeleccionado = ref(null)
const cargando = ref(false)
const procesando = ref(false)
const errorMsg = ref('')
const metodoPago = ref('Efectivo')
const montoPago = ref(0)
const efectivoRecibido = ref(0)
const ticketVisible = ref(false)
const pagoRealizado = ref(null)

const formato = (n) => Number(n || 0).toFixed(2)

/* ── Watchers ────────────────────────────────────────────────── */
watch(montoPago, (val) => {
  errorMsg.value = ''
  if (!creditoSeleccionado.value) return

  const saldo = Number(creditoSeleccionado.value.SaldoActual)

  if (val > saldo) {
    montoPago.value = saldo
    toast.warn(`El monto no puede exceder el saldo pendiente ($${formato(saldo)})`)
    return
  }

  if (metodoPago.value !== 'Efectivo' && val > 0 && val !== saldo) {
    montoPago.value = saldo
    toast.warn(`Con ${metodoPago.value} solo puedes pagar el monto exacto ($${formato(saldo)})`)
  }
})

watch(metodoPago, () => {
  errorMsg.value = ''
  montoPago.value = 0
  efectivoRecibido.value = 0
})

/* ── Computed ────────────────────────────────────────────────── */
const cambio = computed(() => {
  if (metodoPago.value !== 'Efectivo') return 0
  return Math.max(0, Number(efectivoRecibido.value) - Number(montoPago.value))
})

const saldoRestante = computed(() => {
  if (!creditoSeleccionado.value) return 0
  return Number(creditoSeleccionado.value.SaldoActual) - Number(montoPago.value)
})

const puedePagar = computed(() => {
  if (!montoPago.value || montoPago.value <= 0) return false
  if (montoPago.value > Number(creditoSeleccionado.value?.SaldoActual)) return false
  if (metodoPago.value === 'Efectivo' && Number(efectivoRecibido.value) < Number(montoPago.value)) return false
  if (metodoPago.value !== 'Efectivo' && montoPago.value !== Number(creditoSeleccionado.value?.SaldoActual)) return false
  return true
})

const porcentajePagado = (c) => {
  if (!c.MontoTotal) return 0
  return Math.min(100, Math.round(((c.MontoTotal - c.SaldoActual) / c.MontoTotal) * 100))
}

/* ── Funciones ───────────────────────────────────────────────── */
async function verEstadoCuenta() {
  estadoCuentaVisible.value = true
  cargandoEstado.value = true
  try {
    const res = await fetch('/php/creditos/estado_cuenta.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ clienteId: clienteSeleccionado.value.ClienteId }),
    })
    const data = await res.json()
    if (data.success) {
      estadoCuenta.value = data
    } else {
      toast.error('Error al cargar estado de cuenta')
      estadoCuentaVisible.value = false
    }
  } catch {
    toast.error('Error de conexión')
    estadoCuentaVisible.value = false
  } finally {
    cargandoEstado.value = false
  }
}
function imprimirEstadoCuenta() {
  const contenido = document.getElementById('estado-cuenta-imprimir')
  if (!contenido) return
  const ventana = window.open('', '', 'width=500,height=700')
  ventana.document.write(`
    <html>
      <head>
        <title>Estado de Cuenta — ${estadoCuenta.value?.encabezado?.NombreCliente}</title>
        <style>
          body { font-family: 'Courier New', monospace; font-size: 12px; padding: 10px; max-width: 400px; }
          h2 { font-size: 16px; text-align: center; margin: 0 0 4px; }
          p { margin: 2px 0; text-align: center; font-size: 12px; }
          table { width: 100%; border-collapse: collapse; margin: 12px 0; }
          th { font-size: 10px; text-align: left; border-bottom: 1px solid #000; padding: 3px 2px; }
          td { font-size: 11px; padding: 3px 2px; border-bottom: 1px dashed #ccc; }
          .right { text-align: right; }
          .rojo { color: #dc2626; }
          .verde { color: #16a34a; }
          .ec-total { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; border-top: 2px solid #000; padding-top: 6px; margin-top: 4px; }
          .ec-footer { text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px; font-size: 10px; }
          .ec-print-header { display: none; }
        </style>
      </head>
      <body>${contenido.innerHTML}</body>
    </html>
  `)
  ventana.document.close()
  ventana.focus()
  ventana.print()
}

async function onClienteSeleccionado(c) {
  clienteSeleccionado.value = c
  creditoSeleccionado.value = null
  await cargarCreditos(c.ClienteId)
}

async function cargarCreditos(clienteId) {
  cargando.value = true
  creditos.value = []
  try {
    const res = await fetch('/php/obtener_creditos_cliente.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ clienteId })
    })
    const data = await res.json()
    if (data.status === 1) {
      creditos.value = data.creditos
    } else {
      toast.error('Error al cargar créditos')
    }
  } catch {
    toast.error('Error de conexión')
  } finally {
    cargando.value = false
  }
}

function seleccionarCredito(c) {
  creditoSeleccionado.value = c
  montoPago.value = 0
  efectivoRecibido.value = 0
  errorMsg.value = ''
  metodoPago.value = 'Efectivo'
}

function pagarTotal() {
  montoPago.value = Number(creditoSeleccionado.value.SaldoActual)
  efectivoRecibido.value = Number(creditoSeleccionado.value.SaldoActual)
}

function limpiarCliente() {
  clienteSeleccionado.value = null
  creditoSeleccionado.value = null
  creditos.value = []
  montoPago.value = 0
  efectivoRecibido.value = 0
  errorMsg.value = ''
}

async function registrarPago() {
  errorMsg.value = ''
  procesando.value = true
  try {
    const res = await fetch('/php/registrar_pago_credito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        creditoId: creditoSeleccionado.value.CreditoId,
        empleadoId: Number(auth.empleadoId),
        cajaId: Number(sessionStorage.getItem('cajaId') || 0), // ← agregar
        montoPago: Number(montoPago.value),
        metodoPago: metodoPago.value,
        efectivoRecibido: metodoPago.value === 'Efectivo' ? Number(efectivoRecibido.value) : null,
      })
    })
    const data = await res.json()
    if (data.status === 1) {
      pagoRealizado.value = {
        ...data.pago,
        EfectivoRecibido: efectivoRecibido.value,
        Cambio: cambio.value,
        SaldoRestante: Math.max(0, saldoRestante.value),
      }
      ticketVisible.value = true
      await cargarCreditos(clienteSeleccionado.value.ClienteId)
      creditoSeleccionado.value = null
      montoPago.value = 0
      efectivoRecibido.value = 0
    } else {
      errorMsg.value = data.mensaje || 'Error al registrar pago'
    }
  } catch {
    toast.error('Error de conexión')
  } finally {
    procesando.value = false
  }
}

function imprimir() {
  const contenido = document.getElementById('ticket-pago')
  if (!contenido) return
  const ventana = window.open('', '', 'width=400,height=500')
  ventana.document.write(`
    <html>
      <head>
        <title>Comprobante de Pago</title>
        <style>
          body { font-family: 'Courier New', monospace; font-size: 12px; padding: 10px; max-width: 280px; }
          h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
          p { margin: 2px 0; text-align: center; }
          .ticket-info { margin: 8px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; }
          .ticket-info div { display: flex; justify-content: space-between; }
          .ticket-info span { font-weight: bold; }
          .ticket-total { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; border-top: 1px dashed #000; padding-top: 4px; margin-top: 4px; }
          .ticket-footer { text-align: center; margin-top: 8px; border-top: 1px dashed #000; padding-top: 4px; }
          .small { font-size: 10px; }
        </style>
      </head>
      <body>${contenido.innerHTML}</body>
    </html>
  `)
  ventana.document.close()
  ventana.focus()
  ventana.print()
}

onMounted(async () => {
  const clienteId = route.query.clienteId
  const nombre = route.query.nombre
  if (clienteId && nombre) {
    clienteSeleccionado.value = {
      ClienteId: Number(clienteId),
      NombreCliente: nombre,
      Negocio: '',
    }
    await cargarCreditos(Number(clienteId))
  }
})
</script>

<style scoped>
.estado-cuenta-modal {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  max-width: 560px;
  width: 95%;
  max-height: 90vh;
  overflow-y: auto;
}

.ec-header {
  text-align: center;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}
.ec-header h2 { font-size: 18px; font-weight: 700; color: #1a2b3c; margin: 0 0 4px; }
.ec-header p  { font-size: 14px; color: #64748b; margin: 0; }
.ec-negocio   { font-size: 12px !important; color: #94a3b8 !important; }

.ec-resumen {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
}
.ec-resumen-item {
  flex: 1;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.ec-resumen-label { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
.ec-resumen-valor { font-size: 16px; font-weight: 700; color: #1a2b3c; }

.ec-print-header { display: none; }

.ec-tabla {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 16px;
}
.ec-tabla th {
  font-size: 11px;
  color: #64748b;
  font-weight: 700;
  text-transform: uppercase;
  padding: 6px 4px;
  border-bottom: 2px solid #e2e8f0;
  text-align: left;
}
.ec-tabla td {
  font-size: 13px;
  padding: 8px 4px;
  border-bottom: 1px solid #f1f5f9;
  color: #1a2b3c;
}
.ec-tabla .right { text-align: right; }
.ec-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 4px;
  border-top: 2px solid #1a2b3c;
  font-size: 15px;
  font-weight: 700;
  color: #1a2b3c;
}
.ec-footer {
  text-align: center;
  margin-top: 12px;
  padding-top: 8px;
  border-top: 1px dashed #e2e8f0;
  font-size: 11px;
  color: #94a3b8;
}
.btn-estado-cuenta {
  background: #2563eb;
  color: #fff !important;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  min-width: 140px;
  min-height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-estado-cuenta:hover {
  background: #1d4ed8;
}

.pagar-credito {
  max-width: 1100px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.page-header__left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.btn-back {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.1rem;
  color: #1a2b3c;
}

.btn-back:hover {
  background: #f1f5f9;
}

.breadcrumb {
  font-size: 0.78rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 0.2rem;
}

.breadcrumb__link {
  color: #2563eb;
  text-decoration: none;
}

.breadcrumb__sep {
  color: #cbd5e1;
}

.page-titulo {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1a2b3c;
  margin: 0;
}

.card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  margin-bottom: 1.25rem;
  overflow: visible;
}

.card__header {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 1.1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}

.card__icono {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card__icono svg {
  width: 18px;
  height: 18px;
}

.card__icono--azul {
  background: #1a2b3c;
  color: #fff;
}

.card__icono--verde {
  background: #16a34a;
  color: #fff;
}

.card__titulo {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a2b3c;
  margin: 0;
}

.card__subtitulo {
  font-size: 0.78rem;
  color: #94a3b8;
  margin: 0;
}

.card__body {
  padding: 1.25rem;
}

.cliente-seleccionado {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  margin-top: 0.875rem;
  padding: 0.75rem 1rem;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  flex-wrap: wrap;
}

.cliente-avatar {
  width: 36px;
  height: 36px;
  background: #1a2b3c;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  font-weight: 700;
  flex-shrink: 0;
}

.cliente-datos {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.cliente-nombre {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1a2b3c;
}

.cliente-negocio {
  font-size: 0.78rem;
  color: #94a3b8;
}

.btn-limpiar {
  background: none;
  border: 0;
  color: #ef4444;
  cursor: pointer;
  font-size: 16px;
}

.layout-pago {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
  align-items: start;
}

.panel-titulo {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1a2b3c;
  margin: 0 0 0.875rem;
}

.credito-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.credito-card:hover {
  border-color: #2563eb;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
}

.credito-card--activo {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
}

.credito-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.credito-badge {
  background: #eff6ff;
  color: #2563eb;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
}

.credito-card--activo .credito-badge {
  background: #2563eb;
  color: #fff;
}

.credito-fecha {
  font-size: 0.75rem;
  color: #94a3b8;
}

.credito-card__body {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.credito-dato {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.credito-dato__label {
  font-size: 0.68rem;
  color: #94a3b8;
  font-weight: 600;
  text-transform: uppercase;
}

.credito-dato__valor {
  font-size: 0.875rem;
  font-weight: 700;
  color: #1a2b3c;
}

.credito-dato__valor.rojo {
  color: #ef4444;
}

.barra-wrap {
  height: 6px;
  background: #f1f5f9;
  border-radius: 999px;
  overflow: hidden;
  margin-bottom: 4px;
}

.barra-progreso {
  height: 100%;
  background: #16a34a;
  border-radius: 999px;
  transition: width 0.3s;
}

.credito-pct {
  font-size: 0.72rem;
  color: #94a3b8;
  margin: 0;
  text-align: right;
}

.pago-card {
  margin-bottom: 0;
}

.resumen-credito {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.875rem 1rem;
  background: #fef2f2;
  border-radius: 8px;
  margin-bottom: 1rem;
  border: 1px solid #fecaca;
}

.resumen-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.resumen-label {
  font-size: 0.72rem;
  color: #94a3b8;
  font-weight: 600;
  text-transform: uppercase;
}

.resumen-valor {
  font-size: 1.1rem;
  font-weight: 700;
}

.resumen-valor.rojo {
  color: #ef4444;
}

.btn-pagar-todo {
  background: #1a2b3c;
  color: #fff;
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-pagar-todo:hover {
  background: #2563eb;
}

.campo {
  margin-bottom: 1rem;
}

.campo label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 0.4rem;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-prefix {
  position: absolute;
  left: 0.75rem;
  font-size: 0.9rem;
  color: #64748b;
  pointer-events: none;
}

.input {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #1a2b3c;
  background: #f8fafc;
  outline: none;
  transition: border-color 0.2s;
  box-sizing: border-box;
}

.input--prefix {
  padding-left: 1.5rem;
}

.input:focus {
  border-color: #2563eb;
  background: #fff;
}

.resumen-pago {
  background: #f8fafc;
  border-radius: 8px;
  padding: 0.875rem 1rem;
  margin-bottom: 1rem;
}

.resumen-pago__row {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  padding: 3px 0;
  color: #1a2b3c;
}

.resumen-pago__row.cambio strong {
  color: #2563eb;
}

.resumen-pago__row.saldo {
  border-top: 1px dashed #e2e8f0;
  margin-top: 6px;
  padding-top: 6px;
  font-weight: 700;
  font-size: 0.95rem;
}

.verde {
  color: #16a34a;
}

.rojo {
  color: #ef4444;
}

.error-msg {
  color: #ef4444;
  font-size: 0.875rem;
  margin: 0 0 1rem;
  padding: 0.75rem 1rem;
  background: #fef2f2;
  border-radius: 8px;
  border-left: 3px solid #ef4444;
}

.btn-cobrar {
  width: 100%;
  padding: 0.875rem;
  background: #16a34a;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cobrar:hover:not(:disabled) {
  background: #15803d;
}

.btn-cobrar:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  gap: 0.75rem;
  text-align: center;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.empty-state--small {
  padding: 2.5rem 1.5rem;
  margin-bottom: 0;
  border: none;
  background: #f8fafc;
}

.empty-state__icono {
  width: 64px;
  height: 64px;
  background: #eff6ff;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
}

.empty-state__icono--verde {
  background: #f0fdf4;
  color: #16a34a;
}

.empty-state__icono svg {
  width: 32px;
  height: 32px;
}

.empty-state__titulo {
  font-size: 1rem;
  font-weight: 700;
  color: #1a2b3c;
  margin: 0;
}

.empty-state__desc {
  font-size: 0.875rem;
  color: #94a3b8;
  margin: 0;
  max-width: 280px;
  line-height: 1.6;
}

.estado-carga {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 3rem;
  color: #64748b;
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.ticket-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.ticket-modal {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  max-width: 380px;
  width: 90%;
  font-family: 'Courier New', monospace;
}

.ticket-header {
  text-align: center;
  margin-bottom: 12px;
}

.ticket-header h1 {
  font-size: 18px;
  margin: 0;
}

.ticket-header p {
  margin: 2px 0;
  font-size: 12px;
}

.ticket-info {
  border-top: 1px dashed #000;
  border-bottom: 1px dashed #000;
  padding: 6px 0;
  margin: 8px 0;
  font-size: 12px;
}

.ticket-info div {
  display: flex;
  justify-content: space-between;
}

.ticket-info span {
  font-weight: bold;
}

.ticket-total {
  display: flex;
  justify-content: space-between;
  border-top: 1px dashed #000;
  padding-top: 6px;
  margin-top: 6px;
  font-size: 16px;
  font-weight: bold;
}

.ticket-footer {
  text-align: center;
  margin-top: 10px;
  border-top: 1px dashed #000;
  padding-top: 6px;
}

.ticket-footer .small {
  font-size: 10px;
  color: #64748b;
}

.ticket-actions {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.btn-imprimir,
.btn-cerrar-ticket {
  flex: 1;
  padding: 10px;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
}

.btn-imprimir {
  background: #2563eb;
  color: #fff;
}

.btn-cerrar-ticket {
  background: #f1f5f9;
  color: #1e293b;
}
</style>