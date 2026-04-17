<template>
  <div class="tickets-container">

    <div class="tickets-header">
      <h1>Tickets y Recibos</h1>
      <p>Consulta y reimprime tickets de ventas, entradas y salidas</p>
    </div>

    <!-- Último ticket de venta -->
    <div class="ultimo-ticket-card" v-if="ultimoTicket">
      <div class="ultimo-label">Último ticket de venta</div>
      <div class="ultimo-info">
        <span class="ultimo-id">#{{ ultimoTicket.VentaId }}</span>
        <span class="ultimo-cliente">{{ ultimoTicket.Cliente }}</span>
        <span class="ultimo-total">${{ formato(ultimoTicket.Total) }}</span>
        <span class="ultimo-fecha">{{ formatearFecha(ultimoTicket.FechaVenta) }}</span>
      </div>
      <div class="ultimo-acciones">
        <button class="btn-reimprimir" @click="abrirTicketVenta(ultimoTicket.VentaId)">
          Reimprimir
        </button>
        <button
          v-if="puedeCancel"
          class="btn-cancelar"
          @click="cancelarVenta(ultimoTicket.VentaId)"
          :disabled="cancelando"
        >
          {{ cancelando ? 'Cancelando…' : 'Cancelar' }}
        </button>
      </div>
    </div>

    <div class="ultimo-ticket-card vacio" v-else-if="!cargandoUltimo">
      <p>No hay ventas registradas en esta sucursal</p>
    </div>

    <!-- Buscador -->
    <div class="buscador-section">
      <h2>Buscar ticket por ID</h2>

      <div class="buscador-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          :class="['tab-btn', { activo: tabActiva === tab.value }]"
          @click="cambiarTab(tab.value)"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="buscador-input-row">
        <input
          type="number"
          v-model="busquedaId"
          :placeholder="placeholderActual"
          class="input-id"
          @keyup.enter="buscar"
          min="1"
        />
        <button class="btn-buscar" @click="buscar" :disabled="cargandoBusqueda">
          {{ cargandoBusqueda ? 'Buscando…' : 'Buscar' }}
        </button>
      </div>

      <p v-if="errorBusqueda" class="error-msg">{{ errorBusqueda }}</p>

      <!-- Botón cancelar venta buscada -->
      <div
        v-if="puedeCancel && ventaEncontradaId && tabActiva === 'venta'"
        class="cancelar-row"
      >
        <p class="cancelar-aviso">Venta #{{ ventaEncontradaId }} encontrada</p>
        <button
          class="btn-cancelar"
          @click="cancelarVenta(ventaEncontradaId)"
          :disabled="cancelando"
        >
          {{ cancelando ? 'Cancelando…' : 'Cancelar venta' }}
        </button>
      </div>
    </div>

    <!-- Modales tickets -->
    <TicketVenta
      v-if="ticketVentaId"
      :ventaId="ticketVentaId"
      @cerrar="ticketVentaId = null"
    />
    <TicketEntrada
      v-if="ticketEntradaId"
      :entradaId="ticketEntradaId"
      @cerrar="ticketEntradaId = null"
    />
    <TicketSalida
      v-if="ticketSalidaId"
      :salidaId="ticketSalidaId"
      @cerrar="ticketSalidaId = null"
    />

    <!-- Confirmación peligrosa -->
    <ConfirmacionPeligrosa ref="confirmRef" />

  </div>
</template>
<script setup>
import ConfirmacionPeligrosa from '../../components/ConfirmacionPeligrosa.vue';
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useToast } from '../../composables/useToast';
import { normalizeRole } from '../../utils/roles';
import TicketVenta   from '../../components/TicketVenta.vue';
import TicketEntrada from '../../components/TicketEntrada.vue';
import TicketSalida  from '../../components/TicketSalida.vue';
const confirmRef = ref(null);
const auth  = useAuthStore();
const toast = useToast();

const puedeCancel = computed(() => {
  const rol = normalizeRole(auth.rol);
  return rol === 'administrador' || rol === 'dueno';
});

const tabs = [
  { value: 'venta',   label: 'Venta'   },
  { value: 'entrada', label: 'Entrada' },
  { value: 'salida',  label: 'Salida'  },
];

const tabActiva         = ref('venta');
const busquedaId        = ref('');
const cargandoBusqueda  = ref(false);
const cargandoUltimo    = ref(true);
const errorBusqueda     = ref('');
const ultimoTicket      = ref(null);
const cancelando        = ref(false);
const ventaEncontradaId = ref(null); // ID de la última venta buscada exitosamente

const ticketVentaId   = ref(null);
const ticketEntradaId = ref(null);
const ticketSalidaId  = ref(null);

const placeholderActual = computed(() => {
  const map = { venta: 'ID de venta', entrada: 'ID de entrada', salida: 'ID de salida' };
  return map[tabActiva.value];
});

const formato = (n) => Number(n || 0).toFixed(2);

function formatearFecha(f) {
  if (!f) return '';
  return new Date(f).toLocaleString('es-MX', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

function cambiarTab(val) {
  tabActiva.value       = val;
  busquedaId.value      = '';
  errorBusqueda.value   = '';
  ventaEncontradaId.value = null;
}

async function cargarUltimoTicket() {
  cargandoUltimo.value = true;
  try {
    const res  = await fetch(`/php/tickets/ultimo_ticket_venta.php?sucursalId=${auth.sucursalId}`);
    const data = await res.json();
    if (data.success && data.ticket) ultimoTicket.value = data.ticket;
  } catch (e) {
    console.error('Error cargando último ticket', e);
  } finally {
    cargandoUltimo.value = false;
  }
}

function abrirTicketVenta(id) {
  ticketVentaId.value = ticketEntradaId.value = ticketSalidaId.value = null;
  setTimeout(() => { ticketVentaId.value = id; }, 50);
}

async function buscar() {
  errorBusqueda.value     = '';
  ventaEncontradaId.value = null;
  const id = parseInt(busquedaId.value);

  if (!id || id < 1) { errorBusqueda.value = 'Ingresa un ID válido'; return; }

  cargandoBusqueda.value = true;
  try {
    if (tabActiva.value === 'venta') {
      const res  = await fetch(`/php/ventas/ticket.php?VentaId=${id}`);
      const data = await res.json();
      if (!data.success || !data.encabezado) {
        errorBusqueda.value = 'No se encontró la venta #' + id;
        return;
      }
      ventaEncontradaId.value = id;
      abrirTicketVenta(id);

    } else if (tabActiva.value === 'entrada') {
      const res  = await fetch('/php/tickets/ticket_entrada.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ entradaId: id }),
      });
      const data = await res.json();
      if (!data.success || !data.encabezado?.EntradaId) {
        errorBusqueda.value = 'No se encontró la entrada #' + id; return;
      }
      ticketVentaId.value = ticketSalidaId.value = null;
      setTimeout(() => { ticketEntradaId.value = id; }, 50);

    } else if (tabActiva.value === 'salida') {
      const res  = await fetch('/php/tickets/ticket_salida.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ salidaId: id }),
      });
      const data = await res.json();
      if (!data.success || !data.encabezado?.SalidaId) {
        errorBusqueda.value = 'No se encontró la salida #' + id; return;
      }
      ticketVentaId.value = ticketEntradaId.value = null;
      setTimeout(() => { ticketSalidaId.value = id; }, 50);
    }
  } catch (e) {
    errorBusqueda.value = 'Error de conexión';
    console.error(e);
  } finally {
    cargandoBusqueda.value = false;
  }
}

async function cancelarVenta(id) {
  const ok = await confirmRef.value.mostrar(
    'Cancelar venta',
    `¿Estás seguro de cancelar la venta #${id}? Se regresará el inventario y no se puede deshacer.`
  );
  if (!ok) return;

  cancelando.value = true;
  try {
    const res  = await fetch('/php/tickets/cancelar_venta.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ventaId: id, empleadoId: auth.empleadoId }),
    });
    const data = await res.json();
    if (data.success) {
      toast.success(data.mensaje);
      ventaEncontradaId.value = null;
      busquedaId.value        = '';
      await cargarUltimoTicket();
    } else {
      toast.error(data.mensaje);
    }
  } catch (e) {
    toast.error('Error de conexión');
  } finally {
    cancelando.value = false;
  }
}

onMounted(cargarUltimoTicket);
</script>

<style scoped>
.tickets-container {
  padding: 32px;
  max-width: 700px;
  margin: 0 auto;
}

.tickets-header {
  margin-bottom: 28px;
}
.tickets-header h1 {
  font-size: 26px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 6px;
}
.tickets-header p {
  color: #64748b;
  margin: 0;
}

/* Último ticket */

.ultimo-acciones {
  display: flex;
  gap: 8px;
  margin-top: 8px;
  width: 100%;
}

.btn-reimprimir {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
  transition: background 0.15s;
}
.btn-reimprimir:hover { background: #1d4ed8; }

.btn-cancelar {
  background: #dc2626;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
  transition: background 0.15s;
}
.btn-cancelar:hover:not(:disabled) { background: #b91c1c; }
.btn-cancelar:disabled { background: #94a3b8; cursor: not-allowed; }

.cancelar-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 12px;
  padding: 10px 14px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
}

.cancelar-aviso {
  color: #dc2626;
  font-size: 13px;
  font-weight: 600;
  margin: 0;
}
.ultimo-ticket-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 24px;
  margin-bottom: 28px;
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.ultimo-ticket-card.vacio {
  justify-content: center;
  color: #94a3b8;
  font-size: 14px;
}
.ultimo-label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  width: 100%;
}
.ultimo-info {
  display: flex;
  gap: 20px;
  flex: 1;
  flex-wrap: wrap;
  align-items: center;
}
.ultimo-id {
  font-size: 18px;
  font-weight: 700;
  color: #2563eb;
}
.ultimo-cliente {
  color: #1e293b;
  font-weight: 500;
}
.ultimo-total {
  color: #16a34a;
  font-weight: 700;
}
.ultimo-fecha {
  color: #64748b;
  font-size: 13px;
}
.btn-reimprimir {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 14px;
  white-space: nowrap;
}
.btn-reimprimir:hover { background: #1d4ed8; }

/* Buscador */
.buscador-section {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
}
.buscador-section h2 {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 16px;
}
.buscador-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}
.tab-btn {
  padding: 8px 18px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f8fafc;
  color: #64748b;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.15s;
}
.tab-btn:hover { background: #f1f5f9; }
.tab-btn.activo {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}
.buscador-input-row {
  display: flex;
  gap: 10px;
}
.input-id {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 15px;
  outline: none;
}
.input-id:focus { border-color: #2563eb; }
.btn-buscar {
  padding: 10px 20px;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
}
.btn-buscar:hover:not(:disabled) { background: #1d4ed8; }
.btn-buscar:disabled { background: #94a3b8; cursor: not-allowed; }

.error-msg {
  color: #dc2626;
  font-size: 13px;
  margin-top: 8px;
}
</style>