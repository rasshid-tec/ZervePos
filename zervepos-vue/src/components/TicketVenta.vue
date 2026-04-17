<template>
  <div class="ticket-overlay" @click.self="$emit('cerrar')">
    <div class="ticket-modal">

      <div v-if="cargando" class="cargando">Cargando ticket…</div>

      <div v-else-if="error" class="error-msg">{{ error }}</div>

      <div v-else-if="ticket" class="ticket" id="ticket-venta-imprimir">
        <header class="ticket-header">
          <h1>{{ ticket.encabezado.NombreSucursal }}</h1>
          <p>{{ ticket.encabezado.DireccionSucursal }}</p>
          <p>Tel: {{ ticket.encabezado.TelefonoSucursal }}</p>
        </header>

        <div class="ticket-info">
          <div><span>Ticket:</span> #{{ ticket.encabezado.VentaId }}</div>
          <div><span>Fecha:</span> {{ formatearFecha(ticket.encabezado.FechaVenta) }}</div>
          <div><span>Cajero:</span> {{ ticket.encabezado.Cajero }}</div>
          <div><span>Cliente:</span> {{ ticket.encabezado.Cliente }}</div>
        </div>

        <table class="ticket-tabla">
          <thead>
            <tr>
              <th class="left">Producto</th>
              <th>Cant</th>
              <th>P.U.</th>
              <th class="right">Subt.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, i) in ticket.productos" :key="i">
              <td class="left">
                {{ p.NombreProducto }}
                <small v-if="p.Marca">{{ p.Marca }}</small>
              </td>
              <td>{{ p.Cantidad }}</td>
              <td>${{ formato(p.PrecioUnitario) }}</td>
              <td class="right">${{ formato(p.Subtotal) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="ticket-total">
          <span>TOTAL</span>
          <strong>${{ formato(ticket.encabezado.Total) }}</strong>
        </div>

        <div class="ticket-pagos" v-if="ticket.pagos?.length">
          <h3>Pagos</h3>
          <div v-for="(p, i) in ticket.pagos" :key="i" class="pago-row">
            <span>{{ p.MetodoPago }}</span>
            <span>${{ formato(p.MontoPago) }}</span>
          </div>
        </div>

        <footer class="ticket-footer">
          <p>¡Gracias por su compra!</p>
          <p class="small">ZervePOS</p>
        </footer>
      </div>

      <div v-if="!cargando" class="ticket-actions">
        <button @click="imprimir" class="btn-imprimir" :disabled="!ticket">Imprimir</button>
        <button @click="$emit('cerrar')" class="btn-cerrar">Cerrar</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  ventaId: { type: [Number, String], required: true },
});
defineEmits(['cerrar']);

const ticket  = ref(null);
const cargando = ref(true);
const error    = ref('');

const formato = (n) => Number(n || 0).toFixed(2);

function formatearFecha(f) {
  if (!f) return '';
  return new Date(f).toLocaleString('es-MX', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

async function cargarTicket() {
  try {
    const res  = await fetch(`/php/ventas/ticket.php?VentaId=${props.ventaId}`);
    const data = await res.json();
    if (data.success && data.encabezado) {
      ticket.value = data;
    } else {
      error.value = 'No se encontró el ticket #' + props.ventaId;
    }
  } catch (e) {
    error.value = 'Error de conexión';
    console.error(e);
  } finally {
    cargando.value = false;
  }
}

function imprimir() {
  const contenido = document.getElementById('ticket-venta-imprimir');
  if (!contenido) return;
  const ventana = window.open('', '', 'width=400,height=600');
  ventana.document.write(`
    <html>
      <head>
        <title>Ticket #${props.ventaId}</title>
        <style>
          body { font-family: 'Courier New', monospace; font-size: 12px; padding: 10px; max-width: 280px; }
          h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
          h3 { font-size: 13px; margin: 8px 0 4px; border-top: 1px dashed #000; padding-top: 4px; }
          p { margin: 2px 0; text-align: center; }
          .ticket-info { margin: 8px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; }
          .ticket-info div { display: flex; justify-content: space-between; }
          .ticket-info span { font-weight: bold; }
          table { width: 100%; border-collapse: collapse; margin: 4px 0; }
          th, td { padding: 2px; font-size: 11px; text-align: center; }
          .left { text-align: left; }
          .right { text-align: right; }
          small { display: block; color: #555; font-size: 10px; }
          .ticket-total { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; border-top: 1px dashed #000; padding-top: 4px; margin-top: 4px; }
          .pago-row { display: flex; justify-content: space-between; }
          .ticket-footer { text-align: center; margin-top: 8px; border-top: 1px dashed #000; padding-top: 4px; }
          .small { font-size: 10px; }
        </style>
      </head>
      <body>${contenido.innerHTML}</body>
    </html>
  `);
  ventana.document.close();
  ventana.focus();
  ventana.print();
}

onMounted(cargarTicket);
</script>

<style scoped>
.ticket-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
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
  max-height: 90vh;
  overflow-y: auto;
  font-family: 'Courier New', monospace;
}
.cargando, .error-msg { text-align: center; padding: 40px; color: #64748b; }
.error-msg { color: #dc2626; }
.ticket-header { text-align: center; margin-bottom: 12px; }
.ticket-header h1 { font-size: 18px; margin: 0; }
.ticket-header p { margin: 2px 0; font-size: 12px; }
.ticket-info {
  border-top: 1px dashed #000;
  border-bottom: 1px dashed #000;
  padding: 6px 0; margin: 8px 0; font-size: 12px;
}
.ticket-info div { display: flex; justify-content: space-between; }
.ticket-info span { font-weight: bold; }
.ticket-tabla { width: 100%; border-collapse: collapse; }
.ticket-tabla th, .ticket-tabla td { padding: 4px 2px; font-size: 12px; text-align: center; }
.ticket-tabla .left { text-align: left; }
.ticket-tabla .right { text-align: right; }
.ticket-tabla small { display: block; color: #64748b; font-size: 10px; }
.ticket-total {
  display: flex; justify-content: space-between;
  border-top: 1px dashed #000; padding-top: 6px;
  margin-top: 6px; font-size: 16px; font-weight: bold;
}
.ticket-pagos { margin-top: 8px; }
.ticket-pagos h3 { font-size: 13px; margin: 8px 0 4px; border-top: 1px dashed #000; padding-top: 4px; }
.pago-row { display: flex; justify-content: space-between; font-size: 12px; }
.ticket-footer { text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 6px; }
.ticket-footer .small { font-size: 10px; color: #64748b; }
.ticket-actions { display: flex; gap: 8px; margin-top: 16px; }
.btn-imprimir, .btn-cerrar {
  flex: 1; padding: 10px; border: 0;
  border-radius: 8px; cursor: pointer; font-size: 14px;
}
.btn-imprimir { background: #2563eb; color: #fff; }
.btn-imprimir:disabled { background: #94a3b8; cursor: not-allowed; }
.btn-cerrar { background: #f1f5f9; color: #1e293b; }
</style>