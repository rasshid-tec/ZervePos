<template>
  <div class="ticket-overlay" @click.self="$emit('cerrar')">
    <div class="ticket-modal">

      <div v-if="cargando" class="cargando">Cargando ticket…</div>
      <div v-else-if="error" class="error-msg">{{ error }}</div>

      <div v-else-if="ticket" class="ticket" id="ticket-salida-imprimir">
        <header class="ticket-header">
          <h1>{{ ticket.encabezado.NombreSucursal }}</h1>
          <p>Tel: {{ ticket.encabezado.TelefonoSucursal }}</p>
          <p class="tipo-badge">SALIDA — {{ ticket.encabezado.Tipo }}</p>
        </header>

        <div class="ticket-info">
          <div><span>Folio:</span> #{{ ticket.encabezado.SalidaId }}</div>
          <div><span>Fecha:</span> {{ formatearFecha(ticket.encabezado.Fecha) }}</div>
          <div><span>Empleado:</span> {{ ticket.encabezado.Empleado }}</div>
          <div v-if="ticket.encabezado.Motivo"><span>Motivo:</span> {{ ticket.encabezado.Motivo }}</div>
          <div v-if="ticket.encabezado.SucursalDestino"><span>Destino:</span> {{ ticket.encabezado.SucursalDestino }}
          </div>
        </div>
        <table class="ticket-tabla">
          <thead>
            <tr>
              <th class="left">Producto</th>
              <th>Cant</th>
              <th class="right">P.C.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, i) in ticket.detalle" :key="i">
              <td class="left">
                {{ d.NombreProducto }}
                <small v-if="d.Marca">{{ d.Marca }}</small>
              </td>
              <td>{{ d.Cantidad }}</td>
              <td class="right">${{ Number(d.PrecioCompra || 0).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>


        <footer class="ticket-footer">
          <p>ZervePOS</p>
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
  salidaId: { type: [Number, String], required: true },
});
defineEmits(['cerrar']);

const ticket = ref(null);
const cargando = ref(true);
const error = ref('');

function formatearFecha(f) {
  if (!f) return '';
  return new Date(f).toLocaleString('es-MX', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}

async function cargarTicket() {
  try {
    const res = await fetch('/php/tickets/ticket_salida.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ salidaId: props.salidaId }),
    });
    const data = await res.json();

    console.log('Respuesta ticket:', data); // ← agrega esta líneaD
    if (data.success && data.encabezado?.SalidaId) {
      ticket.value = data;
    } else {
      error.value = 'No se encontró la salida #' + props.salidaId;
    }
  } catch (e) {
    error.value = 'Error de conexión';
    console.error(e);
  } finally {
    cargando.value = false;
  }
}

function imprimir() {
  const contenido = document.getElementById('ticket-salida-imprimir');
  if (!contenido) return;
  const ventana = window.open('', '', 'width=400,height=600');
  ventana.document.write(`
    <html>
      <head>
        <title>Salida #${props.salidaId}</title>
        <style>
          body { font-family: 'Courier New', monospace; font-size: 12px; padding: 10px; max-width: 280px; }
          h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
          p { margin: 2px 0; text-align: center; }
          .tipo-badge { font-weight: bold; margin-top: 4px; }
          .ticket-info { margin: 8px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 4px 0; }
          .ticket-info div { display: flex; justify-content: space-between; }
          .ticket-info span { font-weight: bold; }
          table { width: 100%; border-collapse: collapse; margin: 4px 0; }
          th, td { padding: 2px; font-size: 11px; text-align: center; }
          .left { text-align: left; }
          small { display: block; color: #555; font-size: 10px; }
          .ticket-footer { text-align: center; margin-top: 8px; border-top: 1px dashed #000; padding-top: 4px; }
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
  max-height: 90vh;
  overflow-y: auto;
  font-family: 'Courier New', monospace;
}

.cargando,
.error-msg {
  text-align: center;
  padding: 40px;
  color: #64748b;
}

.error-msg {
  color: #dc2626;
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

.tipo-badge {
  font-weight: 700;
  font-size: 13px !important;
  margin-top: 6px !important;
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

.ticket-tabla {
  width: 100%;
  border-collapse: collapse;
}

.ticket-tabla th,
.ticket-tabla td {
  padding: 4px 2px;
  font-size: 12px;
  text-align: center;
}

.ticket-tabla .left {
  text-align: left;
}

.ticket-tabla small {
  display: block;
  color: #64748b;
  font-size: 10px;
}

.ticket-footer {
  text-align: center;
  margin-top: 10px;
  border-top: 1px dashed #000;
  padding-top: 6px;
  font-size: 11px;
  color: #64748b;
}

.ticket-actions {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.btn-imprimir,
.btn-cerrar {
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

.btn-imprimir:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.btn-cerrar {
  background: #f1f5f9;
  color: #1e293b;
}
</style>